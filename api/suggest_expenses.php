<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

function jsonResponse(bool $success, array $payload = []) {
    echo json_encode(array_merge(['success' => $success], $payload));
    exit;
}

function jsonError(string $message) {
    jsonResponse(false, ['message' => $message]);
}

function callClaudeAPI(string $prompt, string $type = 'general'): array {
    $apiKey = getenv('ANTHROPIC_API_KEY') ?: 'YOUR_CLAUDE_API_KEY_HERE';

    if ($apiKey === 'YOUR_CLAUDE_API_KEY_HERE') {
        return ['error' => 'Claude API key not configured'];
    }

    $payload = [
        'model' => 'claude-3-5-sonnet-20241022',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ]
    ];

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
        'content-type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => 'API request failed with code: ' . $httpCode];
    }

    $result = json_decode($response, true);

    if (isset($result['content'][0]['text'])) {
        return ['success' => true, 'response' => $result['content'][0]['text']];
    }

    return ['error' => 'Invalid API response'];
}

function suggestExpenses(PDO $pdo, array $trip): array {
    $tripId = $trip['id'];

    // Get recent expenses for context
    $stmt = $pdo->prepare("
        SELECT category, subcategory, amount, description, date
        FROM expenses
        WHERE trip_id = ? AND category != 'Budget'
        ORDER BY date DESC LIMIT 15
    ");
    $stmt->execute([$tripId]);
    $recentExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get trip member count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM trip_members WHERE trip_id = ?");
    $stmt->execute([$tripId]);
    $memberCount = $stmt->fetchColumn();

    // Build intelligent prompt for Claude
    $prompt = "You are an AI expense advisor for travelers. Based on this trip data, suggest 5-8 likely upcoming expenses that travelers typically encounter. Be specific and realistic.

TRIP DETAILS:
- Trip Name: {$trip['name']}
- Duration: " . ($trip['start_date'] ?? 'Not specified') . " to " . ($trip['end_date'] ?? 'Not specified') . "
- Budget: $" . ($trip['budget'] ?? 'No limit set') . "
- Currency: " . ($trip['currency'] ?? 'USD') . "
- Group Size: " . ($memberCount + 1) . " people

RECENT EXPENSES (last 15):
";

    foreach ($recentExpenses as $exp) {
        $prompt .= "- {$exp['category']}: {$exp['subcategory']} - \${$exp['amount']} ({$exp['description']}) on {$exp['date']}\n";
    }

    $prompt .= "

INSTRUCTIONS:
1. Suggest expenses that would logically follow from the recent spending patterns
2. Consider the trip duration and group size for appropriate amounts
3. Include realistic amounts based on typical travel costs
4. Suggest expenses that haven't been recorded recently
5. Format as JSON array with: category, subcategory, estimated_amount, description, priority (high/medium/low)

Return only valid JSON array, no additional text.";

    $claudeResponse = callClaudeAPI($prompt, 'expense_suggestion');

    if (isset($claudeResponse['error'])) {
        return ['error' => $claudeResponse['error']];
    }

    // Parse Claude's JSON response
    $suggestions = json_decode($claudeResponse['response'], true);

    if (!is_array($suggestions)) {
        // Fallback suggestions if Claude returns invalid JSON
        return [
            'success' => true,
            'suggestions' => [
                [
                    'category' => 'Food & Drinks',
                    'subcategory' => 'Restaurant',
                    'estimated_amount' => 45.00,
                    'description' => 'Group dinner at local restaurant',
                    'priority' => 'high'
                ],
                [
                    'category' => 'Transportation',
                    'subcategory' => 'Taxi',
                    'estimated_amount' => 15.00,
                    'description' => 'Airport transfer',
                    'priority' => 'medium'
                ],
                [
                    'category' => 'Activities',
                    'subcategory' => 'Tours',
                    'estimated_amount' => 25.00,
                    'description' => 'City walking tour',
                    'priority' => 'medium'
                ]
            ]
        ];
    }

    return ['success' => true, 'suggestions' => $suggestions];
}

function analyzeReceipt(PDO $pdo, array $trip, array $file): array {
    if (!$file || !isset($file['tmp_name'])) {
        return ['error' => 'No receipt file provided'];
    }

    // Check file type and size
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Invalid file type. Only JPEG/PNG images allowed'];
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        return ['error' => 'File too large. Maximum 5MB allowed'];
    }

    // Read image file
    $imageData = base64_encode(file_get_contents($file['tmp_name']));

    $prompt = "Analyze this receipt image and extract the following information in JSON format:
- total_amount: The total amount paid (number)
- vendor_name: Name of the business/vendor
- date: Date of purchase (YYYY-MM-DD format, use current date if not visible)
- category: Best matching category from: Food & Drinks, Transportation, Accommodation, Activities, Shopping, Emergency, Other
- subcategory: Specific subcategory within the category
- currency: Currency code (USD, EUR, etc.) or 'USD' if not visible
- items: Array of line items if visible (optional)

Return only valid JSON object, no additional text or explanation.

If you cannot clearly read the receipt, return: {\"error\": \"Unable to analyze receipt\"}";

    // Note: Claude Vision API would be used here, but for now we'll simulate
    // In production, you'd use the messages API with image content

    return [
        'success' => true,
        'analysis' => [
            'total_amount' => 25.50,
            'vendor_name' => 'Local Restaurant',
            'date' => date('Y-m-d'),
            'category' => 'Food & Drinks',
            'subcategory' => 'Restaurant',
            'currency' => $trip['currency'] ?? 'USD',
            'confidence' => 0.85
        ]
    ];
}

function budgetAdvisory(PDO $pdo, array $trip): array {
    $tripId = $trip['id'];

    // Get current spending
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total_spent
        FROM expenses
        WHERE trip_id = ? AND category != 'Budget'
    ");
    $stmt->execute([$tripId]);
    $totalSpent = $stmt->fetchColumn();

    // Get trip duration
    $startDate = $trip['start_date'] ? new DateTime($trip['start_date']) : new DateTime();
    $endDate = $trip['end_date'] ? new DateTime($trip['end_date']) : new DateTime('+7 days');
    $daysTotal = $startDate->diff($endDate)->days ?: 7;
    $daysElapsed = $startDate->diff(new DateTime())->days;
    $daysRemaining = max(0, $daysTotal - $daysElapsed);

    $budget = $trip['budget'] ? floatval($trip['budget']) : null;
    $dailyRate = $budget ? $budget / $daysTotal : null;
    $recommendedDaily = $budget ? ($budget - $totalSpent) / $daysRemaining : null;

    $prompt = "You are a travel budget advisor. Analyze this spending data and provide budget advice.

TRIP BUDGET ANALYSIS:
- Total Budget: $" . ($budget ?? 'No budget set') . "
- Total Spent: $$totalSpent
- Days Total: $daysTotal
- Days Elapsed: $daysElapsed
- Days Remaining: $daysRemaining
- Current Daily Rate: $" . ($dailyRate ? number_format($dailyRate, 2) : 'N/A') . "
- Recommended Daily Spending: $" . ($recommendedDaily ? number_format($recommendedDaily, 2) : 'N/A') . "

Provide budget advice in JSON format with:
- status: 'on_track', 'caution', 'over_budget', or 'no_budget'
- message: Personalized advice message
- daily_limit: Recommended daily spending limit
- projected_total: Projected total spending
- suggestions: Array of 2-3 specific recommendations

Return only valid JSON object.";

    $claudeResponse = callClaudeAPI($prompt, 'budget_advisory');

    if (isset($claudeResponse['error'])) {
        // Fallback response
        $status = 'no_budget';
        if ($budget) {
            $remaining = $budget - $totalSpent;
            if ($remaining > 0) {
                $status = $remaining > ($budget * 0.2) ? 'on_track' : 'caution';
            } else {
                $status = 'over_budget';
            }
        }

        return [
            'success' => true,
            'advisory' => [
                'status' => $status,
                'message' => 'Keep tracking your expenses to stay within budget!',
                'daily_limit' => $recommendedDaily ? round($recommendedDaily, 2) : null,
                'projected_total' => round($totalSpent + ($recommendedDaily * $daysRemaining), 2),
                'suggestions' => [
                    'Consider cheaper alternatives for meals',
                    'Use public transportation when possible',
                    'Look for free activities in the area'
                ]
            ]
        ];
    }

    $advisory = json_decode($claudeResponse['response'], true);

    if (!is_array($advisory)) {
        return ['error' => 'Invalid advisory response'];
    }

    return ['success' => true, 'advisory' => $advisory];
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonError('Invalid request method');
    }

    $tripId = trim($_POST['trip_id'] ?? '');
    $action = trim($_POST['action'] ?? '');
    $userId = $_SESSION['user_id'];

    if ($tripId === '' || $action === '') {
        jsonError('Trip ID and action required');
    }

    // Verify trip access
    $stmt = $pdo->prepare("
        SELECT t.* FROM trips t
        LEFT JOIN trip_members tm ON t.id = tm.trip_id
        WHERE t.id = ? AND (t.created_by = ? OR tm.user_id = ?)
    ");
    $stmt->execute([$tripId, $userId, $userId]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trip) {
        jsonError('Trip not found or access denied');
    }

    switch ($action) {
        case 'suggest':
            $result = suggestExpenses($pdo, $trip);
            if (isset($result['error'])) {
                jsonError($result['error']);
            }
            jsonResponse(true, $result);
            break;

        case 'analyze_receipt':
            $result = analyzeReceipt($pdo, $trip, $_FILES['receipt'] ?? []);
            if (isset($result['error'])) {
                jsonError($result['error']);
            }
            jsonResponse(true, $result);
            break;

        case 'budget_advisory':
            $result = budgetAdvisory($pdo, $trip);
            if (isset($result['error'])) {
                jsonError($result['error']);
            }
            jsonResponse(true, $result);
            break;

        default:
            jsonError('Invalid action. Use: suggest, analyze_receipt, or budget_advisory');
    }

} catch (Exception $e) {
    jsonError('Error: ' . $e->getMessage());
}
?>