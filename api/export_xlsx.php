<?php
require_once '../includes/auth.php';
requireLogin();

$tripId = $_GET['trip_id'];

// Get trip data
$stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
$stmt->execute([$tripId]);
$trip = $stmt->fetch();

// Get expenses
$stmt = $pdo->prepare("
    SELECT e.*, u.name as paid_by_name 
    FROM expenses e 
    JOIN users u ON e.paid_by = u.id 
    WHERE e.trip_id = ? 
    ORDER BY e.date DESC
");
$stmt->execute([$tripId]);
$expenses = $stmt->fetchAll();

// Simple XLSX generation using XML
$filename = 'trip-expenses-' . $trip['name'] . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create temporary directory
$tempDir = sys_get_temp_dir() . '/xlsx_' . uniqid();
mkdir($tempDir);
mkdir($tempDir . '/_rels');
mkdir($tempDir . '/xl');
mkdir($tempDir . '/xl/worksheets');

// Create [Content_Types].xml
file_put_contents($tempDir . '/[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>');

// Create _rels/.rels
file_put_contents($tempDir . '/_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

// Create xl/workbook.xml
file_put_contents($tempDir . '/xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<sheets>
<sheet name="Expenses" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>
</sheets>
</workbook>');

// Create worksheet data
$sheetData = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<sheetData>
<row r="1">
<c r="A1" t="inlineStr"><is><t>Trip: ' . htmlspecialchars($trip['name']) . '</t></is></c>
</row>
<row r="3">
<c r="A3" t="inlineStr"><is><t>Date</t></is></c>
<c r="B3" t="inlineStr"><is><t>Category</t></is></c>
<c r="C3" t="inlineStr"><is><t>Subcategory</t></is></c>
<c r="D3" t="inlineStr"><is><t>Description</t></is></c>
<c r="E3" t="inlineStr"><is><t>Amount</t></is></c>
<c r="F3" t="inlineStr"><is><t>Paid By</t></is></c>
</row>';

$row = 4;
foreach ($expenses as $expense) {
    $sheetData .= '<row r="' . $row . '">
<c r="A' . $row . '" t="inlineStr"><is><t>' . $expense['date'] . '</t></is></c>
<c r="B' . $row . '" t="inlineStr"><is><t>' . htmlspecialchars($expense['category']) . '</t></is></c>
<c r="C' . $row . '" t="inlineStr"><is><t>' . htmlspecialchars($expense['subcategory']) . '</t></is></c>
<c r="D' . $row . '" t="inlineStr"><is><t>' . htmlspecialchars($expense['description']) . '</t></is></c>
<c r="E' . $row . '"><v>' . $expense['amount'] . '</v></c>
<c r="F' . $row . '" t="inlineStr"><is><t>' . htmlspecialchars($expense['paid_by_name']) . '</t></is></c>
</row>';
    $row++;
}

$total = array_sum(array_column($expenses, 'amount'));
$sheetData .= '<row r="' . ($row + 1) . '">
<c r="D' . ($row + 1) . '" t="inlineStr"><is><t>TOTAL</t></is></c>
<c r="E' . ($row + 1) . '"><v>' . $total . '</v></c>
</row>';

$sheetData .= '</sheetData></worksheet>';

file_put_contents($tempDir . '/xl/worksheets/sheet1.xml', $sheetData);

// Create ZIP file
$zip = new ZipArchive();
$zipFile = $tempDir . '.xlsx';
$zip->open($zipFile, ZipArchive::CREATE);

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempDir));
foreach ($files as $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($tempDir) + 1);
        $zip->addFile($filePath, $relativePath);
    }
}
$zip->close();

// Output file
readfile($zipFile);

// Cleanup
function deleteDir($dir) {
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? deleteDir($path) : unlink($path);
        }
        rmdir($dir);
    }
}

deleteDir($tempDir);
unlink($zipFile);
?>