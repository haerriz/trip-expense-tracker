<?php
// Test script for multi-AI integration with provided API keys
require_once 'config/multi_ai_service.php';

echo "🔄 Testing Multi-AI Integration with Your API Keys\n";
echo "================================================\n\n";

// Test provider status
$aiService = new MultiAIService();
$status = $aiService->getProviderStatus();

echo "AI Provider Status:\n";
foreach ($status as $provider => $info) {
    $statusText = $info['enabled'] ? '✅ ENABLED' : '❌ DISABLED';
    $keyText = $info['has_api_key'] ? ' (API key found)' : ' (no API key)';
    echo "- $provider: $statusText$keyText\n";
}

echo "\n";

$enabledProviders = array_filter($status, fn($p) => $p['enabled']);

if (!empty($enabledProviders)) {
    echo "🧪 Testing AI Response (Expense Suggestion):\n";
    echo "-------------------------------------------\n";

    $testPrompt = 'Suggest 2 realistic expenses for a 3-day business trip to New York. Return JSON array with: [{"category": "Transportation", "subcategory": "Taxi", "estimated_amount": 45.00, "description": "Airport transfer", "priority": "high"}, ...]';

    $response = callMultiAI($testPrompt, 'expense_suggestion');

    if (isset($response['error'])) {
        echo "❌ AI Error: " . $response['error'] . "\n";
        if (isset($response['provider_errors'])) {
            echo "Provider-specific errors:\n";
            foreach ($response['provider_errors'] as $provider => $error) {
                if (str_contains($error, 'billing_not_active')) {
                    echo "  - $provider: ❌ Billing not active - Please add payment method to OpenAI account\n";
                } elseif (str_contains($error, 'quota') || str_contains($error, 'RESOURCE_EXHAUSTED')) {
                    echo "  - $provider: ❌ Free tier quota exceeded - Upgrade to paid plan\n";
                } else {
                    echo "  - $provider: $error\n";
                }
            }
        }
    } else {
        echo "✅ AI Success!\n";
        echo "📊 Provider used: " . ($response['provider'] ?? 'unknown') . "\n";
        echo "🔄 Multi-AI used: " . (isset($response['multi_ai_used']) && $response['multi_ai_used'] ? 'Yes (' . ($response['ai_count'] ?? 0) . ' AIs)' : 'No') . "\n";
        echo "💬 Response preview: " . substr($response['response'], 0, 150) . "...\n\n";

        // Try to parse and display the JSON
        $parsed = json_decode($response['response'], true);
        if (is_array($parsed)) {
            echo "📋 Parsed Suggestions:\n";
            foreach ($parsed as $i => $suggestion) {
                if (is_array($suggestion)) {
                    echo "  " . ($i + 1) . ". {$suggestion['category']} > {$suggestion['subcategory']}: \${$suggestion['estimated_amount']} - {$suggestion['description']}\n";
                }
            }
        }
    }

    echo "\n🧪 Testing Budget Advisory:\n";
    echo "-------------------------\n";

    $budgetPrompt = 'Trip budget: $1000, Spent: $650, Days remaining: 2. Provide budget advice in JSON: {"status": "caution", "message": "Stay under $175/day", "daily_limit": 175, "projected_total": 1000, "suggestions": ["Skip expensive meals", "Use public transport"]}';

    $budgetResponse = callMultiAI($budgetPrompt, 'budget_advisory');

    if (isset($budgetResponse['error'])) {
        echo "❌ Budget AI Error: " . $budgetResponse['error'] . "\n";
    } else {
        echo "✅ Budget AI Success!\n";
        echo "📊 Provider: " . ($budgetResponse['provider'] ?? 'unknown') . "\n";
        echo "💬 Response: " . substr($budgetResponse['response'], 0, 100) . "...\n";
    }

} else {
    echo "⚠️  No AI providers enabled. Please check your environment variables:\n";
    echo "   - OPENAI_API_KEY\n";
    echo "   - GOOGLE_AI_API_KEY\n";
    echo "   - ANTHROPIC_API_KEY (optional)\n";
}

echo "\n📚 Setup documentation: MULTI_AI_SETUP.md\n";
echo "🔗 Test the API at: /api/suggest_expenses.php\n";
?>