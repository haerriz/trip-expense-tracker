<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/trip-preferences.php';

header('Cache-Control: private, no-store');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function abortRequest(int $status, string $message): void
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    exit($message);
}

function isoDate($value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
        abortRequest(400, 'Use a valid calendar date.');
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        abortRequest(400, 'Use a valid calendar date.');
    }
    return $value;
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'POST'], true)) {
    abortRequest(405, 'Method not allowed.');
}
$tripId = $_GET['trip_id'] ?? null;
requireTripAccess($tripId);
$tripId = (int) $tripId;
$userId = (int) $_SESSION['user_id'];
$_SESSION['trip_tools_csrf'] ??= bin2hex(random_bytes(32));
$csrf = $_SESSION['trip_tools_csrf'];
$post = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($post && (!is_string($_POST['csrf'] ?? null) || !hash_equals($csrf, $_POST['csrf']))) {
    abortRequest(403, 'Refresh the page before submitting again.');
}
$link = '/trip-tools.php?trip_id=' . $tripId;
if (($_GET['clear'] ?? null) === '1' && (int) ($_SESSION['trip_tools_filter']['trip'] ?? 0) === $tripId) {
    unset($_SESSION['trip_tools_filter']);
    session_write_close();
    header('Location: ' . $link, true, 303);
    exit;
}
if ($post && ($_POST['action'] ?? '') === 'filter') {
    $newFrom = isoDate($_POST['from'] ?? null);
    $newTo = isoDate($_POST['to'] ?? null);
    if ($newFrom !== '' && $newTo !== '' && $newFrom > $newTo) {
        abortRequest(400, 'The start date must come before the end date.');
    }
    $newTerm = $_POST['expense_search'] ?? '';
    if (!is_string($newTerm) || !mb_check_encoding($newTerm, 'UTF-8') || mb_strlen($newTerm) > 100) {
        abortRequest(400, 'Use at most 100 characters for expense search.');
    }
    $_SESSION['trip_tools_filter'] = ['trip' => $tripId, 'term' => trim($newTerm)];
    session_write_close();
    header('Location: ' . $link . '&' . http_build_query(['from' => $newFrom, 'to' => $newTo]), true, 303);
    exit;
}
$term = (int) ($_SESSION['trip_tools_filter']['trip'] ?? 0) === $tripId ? (string) $_SESSION['trip_tools_filter']['term'] : '';
$notice = $_SESSION['trip_tools_notice'] ?? '';
unset($_SESSION['trip_tools_notice']);
session_write_close();

$tripQuery = $pdo->prepare('SELECT name, currency, created_by FROM trips WHERE id = ?');
$tripQuery->execute([$tripId]);
$trip = $tripQuery->fetch(PDO::FETCH_ASSOC);
if (!$trip) {
    abortRequest(404, 'Trip unavailable.');
}
$owner = (int) $trip['created_by'] === $userId;
$memberQuery = $pdo->prepare("SELECT DISTINCT u.id,u.name FROM users u WHERE u.id = ? OR EXISTS (SELECT 1 FROM trip_members tm WHERE tm.trip_id = ? AND tm.user_id = u.id AND (tm.status = 'accepted' OR tm.status IS NULL)) ORDER BY u.name, u.id");
$memberQuery->execute([(int) $trip['created_by'], $tripId]);
$members = $memberQuery->fetchAll(PDO::FETCH_ASSOC);
$memberIds = array_map('intval', array_column($members, 'id'));
$defaultPath = tripPreferencePath($tripId, 'split-default');
$weights = readTripPreference($defaultPath);
if ($post && ($_POST['action'] ?? '') === 'defaults') {
    if (!$owner) abortRequest(403, 'Only the trip owner can save defaults.');
    $entered = $_POST['weight'] ?? null;
    if (!is_array($entered) || count($entered) > count($members)) abortRequest(422, 'Invalid split weights.');
    $saved = [];
    foreach ($entered as $id => $weight) {
        if (!in_array((int) $id, $memberIds, true) || !is_string($weight) ||
            ($weight !== '' && (!ctype_digit($weight) || (int) $weight > 10000))) {
            abortRequest(422, 'Use a whole-number weight from 0 to 10,000 for each trip member.');
        }
        if ((int) $weight > 0) $saved[(int) $id] = (int) $weight;
    }
    if (!saveTripPreference($defaultPath, $saved)) abortRequest(503, 'Could not save the trip default.');
    header('Location: ' . $link . '#split-defaults', true, 303);
    exit;
}
$storage = dirname(__DIR__) . '/private-receipts/' . $tripId;
$itemsId = $_GET['items'] ?? null;
$receiptItems = [];
$itemExpense = null;
if ($itemsId !== null) {
    if (!is_string($itemsId) || !ctype_digit($itemsId) || (int) $itemsId <= 0 || (int) $itemsId > 2147483647) abortRequest(400, 'Invalid expense.');
    $itemQuery = $pdo->prepare("SELECT paid_by, amount, description FROM expenses WHERE id = ? AND trip_id = ? AND category <> 'Budget'");
    $itemQuery->execute([(int) $itemsId, $tripId]);
    $itemExpense = $itemQuery->fetch(PDO::FETCH_ASSOC);
    if (!$itemExpense) abortRequest(404, 'Expense unavailable.');
    $canEditItems = $owner || (int) $itemExpense['paid_by'] === $userId;
    $itemPath = tripPreferencePath($tripId, 'expense-' . (int) $itemsId . '-items');
    $receiptItems = readTripPreference($itemPath);
    if ($post && ($_POST['action'] ?? '') === 'items') {
        if (!$canEditItems) abortRequest(403, 'Only the payer or trip owner can edit items.');
        $posted = $_POST['item'] ?? null;
        if (!is_array($posted) || count($posted) > 20) abortRequest(422, 'Add up to 20 line items.');
        $valid = [];
        foreach ($posted as $row) {
            if (!is_array($row)) abortRequest(422, 'Invalid receipt line.');
            $label = is_string($row['label'] ?? null) ? trim($row['label']) : '';
            $amount = $row['amount'] ?? '';
            $member = $row['member'] ?? '';
            if ($label === '' && $amount === '' && $member === '') continue;
            if ($label === '' || !mb_check_encoding($label, 'UTF-8') || mb_strlen($label) > 120
                || !is_string($amount) || !preg_match('/^[0-9]{1,8}(?:[.][0-9]{1,2})?$/D', $amount)
                || !is_string($member) || ($member !== '' && (!ctype_digit($member) || !in_array((int) $member, $memberIds, true)))) {
                abortRequest(422, 'Each line needs a description, a valid amount and, optionally, a trip member.');
            }
            $valid[] = ['label' => $label, 'cents' => (int) round((float) $amount * 100), 'member' => (int) $member];
        }
        if (!saveTripPreference($itemPath, $valid)) abortRequest(503, 'Could not save receipt items.');
        header('Location: ' . $link . '&items=' . (int) $itemsId . '#itemization', true, 303);
        exit;
    }
}
$view = isset($_GET['receipt']);
$upload = $post && ($_POST['action'] ?? '') === 'receipt';

if ($view || $upload) {
    $id = $view ? $_GET['receipt'] : ($_POST['expense'] ?? null);
    if (!is_string($id) || !ctype_digit($id) || (int) $id <= 0 || (int) $id > 2147483647) {
        abortRequest(400, 'Invalid expense.');
    }
    $id = (int) $id;
    $expenseQuery = $pdo->prepare("SELECT paid_by FROM expenses WHERE id = ? AND trip_id = ? AND category <> 'Budget'");
    $expenseQuery->execute([$id, $tripId]);
    $expense = $expenseQuery->fetch(PDO::FETCH_ASSOC);
    if (!$expense) {
        abortRequest(404, 'Expense unavailable.');
    }
    $receipt = $storage . '/' . $id . '.jpg';
    if ($view) {
        if (is_link($receipt) || !is_file($receipt)) {
            abortRequest(404, 'Receipt unavailable.');
        }
        $handle = fopen($receipt, 'rb');
        if (!$handle) {
            abortRequest(404, 'Receipt unavailable.');
        }
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="expense-' . $id . '-receipt.jpg"');
        if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
            fpassthru($handle);
        }
        fclose($handle);
        exit;
    }

    if (!$owner && (int) $expense['paid_by'] !== $userId) {
        abortRequest(403, 'Only the payer or trip owner can attach a receipt.');
    }
    if (file_exists($receipt) || is_link($receipt)) {
        abortRequest(409, 'This expense already has a receipt.');
    }
    $file = $_FILES['receipt'] ?? [];
    $source = $file['tmp_name'] ?? '';
    $size = $file['size'] ?? 0;
    if (($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_string($source) || !is_uploaded_file($source)
        || $size < 1 || $size > 5 * 1024 * 1024) {
        abortRequest(422, 'Choose a JPEG or PNG image no larger than 5 MB.');
    }

    $info = @getimagesize($source);
    if (!$info || $info[0] < 1 || $info[1] < 1 || $info[0] > 5000 || $info[1] > 5000
        || $info[0] * $info[1] > 10000000 || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        abortRequest(422, 'Choose a valid JPEG or PNG up to 10 megapixels.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($source);
    if ($mime !== ($info[2] === IMAGETYPE_JPEG ? 'image/jpeg' : 'image/png')) {
        abortRequest(422, 'The receipt image type does not match its contents.');
    }
    if (!function_exists('imagecreatetruecolor')) {
        abortRequest(503, 'Receipt uploads are temporarily unavailable.');
    }
    $image = $info[2] === IMAGETYPE_JPEG ? @imagecreatefromjpeg($source) : @imagecreatefrompng($source);
    if (!$image) {
        abortRequest(422, 'The receipt image could not be opened.');
    }
    $scale = min(1, 2400 / max($info[0], $info[1]));
    $width = max(1, (int) round($info[0] * $scale));
    $height = max(1, (int) round($info[1] * $scale));
    $clean = imagecreatetruecolor($width, $height);
    imagefill($clean, 0, 0, imagecolorallocate($clean, 255, 255, 255));
    imagecopyresampled($clean, $image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
    if (is_link(dirname($storage)) || is_link($storage) ||
        (!is_dir($storage) && !@mkdir($storage, 0700, true) && !is_dir($storage))) {
        abortRequest(503, 'Receipt storage is temporarily unavailable.');
    }
    $temporary = tempnam($storage, 'receipt-');
    if (!$temporary || dirname($temporary) !== $storage) {
        if ($temporary) {
            @unlink($temporary);
        }
        abortRequest(503, 'Receipt storage is temporarily unavailable.');
    }
    $saved = false;
    $lock = null;
    try {
        if (imagejpeg($clean, $temporary, 88) && filesize($temporary) > 0) {
            $lockPath = $storage . '/.receipt-lock';
            $lock = !is_link($lockPath) ? fopen($lockPath, 'c') : false;
            if ($lock && flock($lock, LOCK_EX) && !file_exists($receipt) && !is_link($receipt)) {
                $saved = rename($temporary, $receipt);
            }
        }
    } finally {
        if (is_resource($lock)) {
            fclose($lock);
        }
        if (is_file($temporary)) {
            unlink($temporary);
        }
    }
    if (!$saved) {
        abortRequest(is_file($receipt) ? 409 : 503, 'Receipt could not be saved. Check whether one already exists.');
    }
    session_start();
    $_SESSION['trip_tools_notice'] = 'Receipt saved.';
    session_write_close();
    header('Location: ' . $link . '#expenses', true, 303);
    exit;
}

$from = isoDate($_GET['from'] ?? null);
$to = isoDate($_GET['to'] ?? null);
if ($from && $to && $from > $to) {
    abortRequest(400, 'The start date must come before the end date.');
}
$where = "e.trip_id = ? AND e.category <> 'Budget'";
$params = [$tripId];
if ($from !== '') {
    $where .= ' AND e.date >= ?';
    $params[] = $from;
}
if ($to !== '') {
    $where .= ' AND e.date <= ?';
    $params[] = $to;
}
if ($term !== '') {
    $like = '%' . strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    $where .= " AND (e.description LIKE ? ESCAPE '!' OR e.category LIKE ? ESCAPE '!' OR e.subcategory LIKE ? ESCAPE '!')";
    array_push($params, $like, $like, $like);
}
$pageInput = $_GET['page'] ?? '1';
if (!is_string($pageInput) || !ctype_digit($pageInput) || (int) $pageInput < 1 || (int) $pageInput > 10000) {
    abortRequest(400, 'Invalid results page.');
}
$rows = static function (string $sql, array $values) use ($pdo): array {
    $statement = $pdo->prepare($sql);
    $statement->execute($values);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
};
$categories = $rows("SELECT e.category AS label, SUM(e.amount) AS total, COUNT(*) AS items FROM expenses e WHERE $where GROUP BY e.category ORDER BY total DESC", $params);
$months = $rows("SELECT DATE_FORMAT(e.date, '%Y-%m') AS label, SUM(e.amount) AS total FROM expenses e WHERE $where GROUP BY label ORDER BY label DESC LIMIT 12", $params);
$total = $rows("SELECT SUM(e.amount) AS total, COUNT(*) AS items FROM expenses e WHERE $where", $params)[0];
$pageSize = 25;
$totalPages = max(1, (int) ceil((int) $total['items'] / $pageSize));
$page = min((int) $pageInput, $totalPages);
$offset = ($page - 1) * $pageSize;
$pageLink = static function (int $number) use ($link, $from, $to): string {
    return $link . '&' . http_build_query(['from' => $from, 'to' => $to, 'page' => $number]) . '#expenses';
};
$expenses = $rows("SELECT e.id, e.date, e.category, e.amount, e.description, e.paid_by, u.name AS payer FROM expenses e LEFT JOIN users u ON e.paid_by = u.id WHERE $where ORDER BY e.date DESC, e.id DESC LIMIT $pageSize OFFSET $offset", $params);
$search = '';
$messages = [];
if ($post && ($_POST['action'] ?? '') === 'chat') {
    $search = is_string($_POST['query'] ?? null) ? trim($_POST['query']) : '';
    if (mb_strlen($search) < 2 || mb_strlen($search) > 100) {
        abortRequest(422, 'Enter a search between 2 and 100 characters.');
    }
    $term = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    $messages = $rows("SELECT cm.message, cm.created_at, u.name FROM chat_messages cm LEFT JOIN users u ON u.id = cm.user_id WHERE cm.trip_id = ? AND cm.message LIKE ? ESCAPE '!' ORDER BY cm.created_at DESC, cm.id DESC LIMIT 50", [$tripId, $term]);
}
$money = static function ($value) use ($trip): string {
    return h($trip['currency'] ?: 'USD') . ' ' . number_format((float) $value, 2);
};
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Trip tools · Haerriz Trip Finance</title>
<style>
:root{color-scheme:light dark;font:16px system-ui,sans-serif}body{margin:0;background:Canvas;color:CanvasText}
main{max-width:68rem;margin:auto;padding:1.25rem}h1{margin:.5rem 0 1rem}h2{margin-top:0}
a{color:LinkText}.panel{border:1px solid GrayText;border-radius:12px;padding:1rem;margin:1rem 0}
.flex{display:flex;gap:.75rem;flex-wrap:wrap;align-items:end}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr));gap:1rem}
label{display:grid;gap:.3rem}input,button{font:inherit;padding:.4rem;max-width:100%}button{cursor:pointer}
.scroll{overflow:auto}table{border-collapse:collapse;width:100%}th,td{text-align:left;border-bottom:1px solid GrayText;padding:.55rem;vertical-align:top}
td form{min-width:13rem}article{border-top:1px solid GrayText;padding:.65rem 0;overflow-wrap:anywhere}article p{white-space:pre-wrap;margin:.3rem 0}
</style>
</head>
<body><main>
<nav><a href="/dashboard.php">Trip dashboard</a> · <a href="#expenses">Receipts</a> · <a href="#chat">Search chat</a></nav>
<h1><?= h($trip['name']) ?></h1>
<?php if ($notice): ?><p role="status"><?= h($notice) ?></p><?php endif; ?>
<section class="panel">
<h2>Spending report</h2>
<form method="post" class="flex">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>">
<input type="hidden" name="action" value="filter">
<label>From<input type="date" name="from" value="<?= h($from) ?>"></label>
<label>To<input type="date" name="to" value="<?= h($to) ?>"></label>
<label>Search expenses<input type="search" name="expense_search" value="<?= h($term) ?>" maxlength="100" placeholder="Description or category"></label>
<button>Apply filters</button><a href="<?= h($link . '&clear=1') ?>">Clear filters</a>
</form>
<p><strong><?= $money($total['total']) ?></strong> across <?= (int) $total['items'] ?> expenses. Budget adjustments are excluded.</p>
<div class="grid">
<div><h3>By category</h3><table><tbody>
<?php foreach ($categories as $row): ?><tr><th scope="row"><?= h($row['label']) ?> (<?= (int) $row['items'] ?>)</th><td><?= $money($row['total']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<div><h3>By month</h3><table><tbody>
<?php foreach ($months as $row): ?><tr><th scope="row"><?= h($row['label']) ?></th><td><?= $money($row['total']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
</div>
</section>
<section class="panel" id="split-defaults">
<h2>Saved split default</h2>
<p>Weights are relative shares. For example, 2 and 1 split the expense two-thirds and one-third. A zero excludes that person. Choose “Use saved split” when adding an expense.</p>
<?php if ($owner): ?>
<form method="post" action="<?= h($link) ?>" class="grid">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="defaults">
<?php foreach ($members as $member): $id = (int) $member['id']; ?>
<label><?= h($member['name']) ?><input type="number" name="weight[<?= $id ?>]" value="<?= (int) ($weights[$id] ?? 0) ?>" min="0" max="10000" step="1"></label>
<?php endforeach; ?>
<button>Save split default</button>
</form>
<?php else: ?><p>The trip owner manages the saved split.</p><?php endif; ?>
</section>
<?php if ($itemExpense): ?>
<section class="panel" id="itemization">
<h2>Receipt items</h2><p><?= h($itemExpense['description']) ?></p>
<p>Item total <?= $money(array_sum(array_column($receiptItems, 'cents')) / 100) ?> · Expense total <?= $money($itemExpense['amount']) ?>. Item edits leave balances unchanged.</p>
<form method="post" action="<?= h($link . '&items=' . (int) $itemsId) ?>">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="items">
<div class="scroll"><table><thead><tr><th>Item description</th><th>Line amount</th><th>Traveller (optional)</th></tr></thead><tbody>
<?php for ($i = 0, $limit = $canEditItems ? min(20, max(5, count($receiptItems) + 3)) : count($receiptItems); $i < $limit; $i++): $item = $receiptItems[$i] ?? []; ?>
<tr><td><input aria-label="Item <?= $i + 1 ?> description" name="item[<?= $i ?>][label]" maxlength="120" value="<?= h($item['label'] ?? '') ?>" <?= $canEditItems ? '' : 'readonly' ?>></td>
<td><input aria-label="Item <?= $i + 1 ?> amount" name="item[<?= $i ?>][amount]" type="number" min="0" max="99999999.99" step=".01" value="<?= isset($item['cents']) ? h(number_format($item['cents'] / 100, 2, '.', '')) : '' ?>" <?= $canEditItems ? '' : 'readonly' ?>></td>
<td><select aria-label="Item <?= $i + 1 ?> traveller" name="item[<?= $i ?>][member]" <?= $canEditItems ? '' : 'disabled' ?>><option value="">Unassigned</option>
<?php foreach ($members as $member): ?><option value="<?= (int) $member['id'] ?>" <?= (int) ($item['member'] ?? 0) === (int) $member['id'] ? 'selected' : '' ?>><?= h($member['name']) ?></option><?php endforeach; ?>
</select></td></tr><?php endfor; ?>
</tbody></table></div>
<?php if ($canEditItems): ?><p>To remove a line, clear its description, amount, and traveller.</p><button>Save receipt items</button><?php endif; ?>
</form></section>
<?php endif; ?>
<section class="panel" id="expenses">
<h2>Expense receipts</h2><p>Showing <?= count($expenses) ?> of <?= (int) $total['items'] ?> matching expenses. Add one JPEG or PNG per expense, up to 5 MB and 10 megapixels.</p>
<div class="scroll"><table><thead><tr><th>Date</th><th>Expense</th><th>Paid by</th><th>Amount</th><th>Receipt</th></tr></thead><tbody>
<?php foreach ($expenses as $row): $id = (int) $row['id']; $path = $storage . '/' . $id . '.jpg'; ?>
<tr><td><?= h($row['date']) ?></td><td><?= h($row['description'] ?: $row['category']) ?></td><td><?= h($row['payer'] ?? '') ?></td><td><?= $money($row['amount']) ?></td><td>
<a href="<?= h($link . '&items=' . $id . '#itemization') ?>">Receipt items</a><br>
<?php if (!is_link($path) && is_file($path)): ?>
<a href="<?= h($link . '&receipt=' . $id) ?>">Download receipt</a>
<?php elseif ($owner || (int) $row['paid_by'] === $userId): ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="receipt">
<input type="hidden" name="expense" value="<?= $id ?>"><label>Choose image<input type="file" name="receipt" accept="image/jpeg,image/png" required></label>
<button>Attach receipt</button></form>
<?php else: ?>No receipt<?php endif; ?>
</td></tr>
<?php endforeach; ?>
<?php if (!$expenses): ?><tr><td colspan="5">No expenses in this range.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if ($totalPages > 1): ?>
<nav class="flex" aria-label="Expense result pages">
<?php if ($page > 1): ?><a href="<?= h($pageLink($page - 1)) ?>">Previous</a><?php endif; ?>
<span>Page <?= $page ?> of <?= $totalPages ?></span>
<?php if ($page < $totalPages): ?><a href="<?= h($pageLink($page + 1)) ?>">Next</a><?php endif; ?>
</nav>
<?php endif; ?>
</section>
<section class="panel" id="chat">
<h2>Search trip chat</h2>
<form method="post" action="#chat" class="flex">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="chat">
<label>Message text<input type="search" name="query" value="<?= h($search) ?>" minlength="2" maxlength="100" required></label><button>Search</button>
</form>
<?php if ($search !== ''): ?><p><?= count($messages) ?> shown, newest first (up to 50).</p><?php endif; ?>
<?php foreach ($messages as $row): ?><article><strong><?= h($row['name'] ?? 'Trip member') ?></strong> · <time><?= h($row['created_at']) ?></time><p><?= h($row['message']) ?></p></article><?php endforeach; ?>
</section>
</main></body></html>