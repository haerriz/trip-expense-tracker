<?php

class MultiAIService {
    private $providers = [];
    private $fallbackOrder = ['chatgpt', 'claude', 'gemini'];

    public function __construct() {
        $this->initializeProviders();
    }

    private function initializeProviders() {
        // ChatGPT (OpenAI)
        $this->providers['chatgpt'] = [
            'enabled' => !empty(getenv('OPENAI_API_KEY')),
            'api_key' => getenv('OPENAI_API_KEY'),
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'model' => 'gpt-4',
            'headers' => [
                'Authorization: Bearer ' . getenv('OPENAI_API_KEY'),
                'Content-Type: application/json'
            ]
        ];

        // Claude (Anthropic)
        $this->providers['claude'] = [
            'enabled' => !empty(getenv('ANTHROPIC_API_KEY')),
            'api_key' => getenv('ANTHROPIC_API_KEY'),
            'endpoint' => 'https://api.anthropic.com/v1/messages',
            'model' => 'claude-3-5-sonnet-20241022',
            'headers' => [
                'x-api-key: ' . getenv('ANTHROPIC_API_KEY'),
                'anthropic-version: 2023-06-01',
                'content-type: application/json'
            ]
        ];

        // Gemini (Google)
        $this->providers['gemini'] = [
            'enabled' => !empty(getenv('GOOGLE_AI_API_KEY')),
            'api_key' => getenv('GOOGLE_AI_API_KEY'),
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent',
            'model' => 'gemini-pro',
            'headers' => [
                'Content-Type: application/json'
            ]
        ];
    }

    public function generateSuggestions(string $prompt, string $type = 'general'): array {
        $responses = [];
        $errors = [];

        // Try all enabled providers
        foreach ($this->fallbackOrder as $provider) {
            if ($this->providers[$provider]['enabled']) {
                $result = $this->callProvider($provider, $prompt, $type);
                if (isset($result['success']) && $result['success']) {
                    $responses[$provider] = $result['response'];
                } else {
                    $errors[$provider] = $result['error'] ?? 'Unknown error';
                }
            }
        }

        // If we have multiple successful responses, combine them
        if (count($responses) > 1) {
            return $this->combineResponses($responses, $type);
        }

        // If we have at least one successful response, return it
        if (!empty($responses)) {
            $provider = array_key_first($responses);
            return [
                'success' => true,
                'response' => $responses[$provider],
                'provider' => $provider,
                'fallback_used' => count($errors) > 0
            ];
        }

        // All providers failed
        return [
            'error' => 'All AI providers failed: ' . implode(', ', $errors),
            'provider_errors' => $errors
        ];
    }

    private function callProvider(string $provider, string $prompt, string $type): array {
        $config = $this->providers[$provider];

        switch ($provider) {
            case 'chatgpt':
                return $this->callChatGPT($config, $prompt, $type);
            case 'claude':
                return $this->callClaude($config, $prompt, $type);
            case 'gemini':
                return $this->callGemini($config, $prompt, $type);
            default:
                return ['error' => 'Unknown provider: ' . $provider];
        }
    }

    private function callChatGPT(array $config, string $prompt, string $type): array {
        $systemPrompt = $this->getSystemPrompt($type);

        $payload = [
            'model' => $config['model'],
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 1024,
            'temperature' => 0.7
        ];

        return $this->makeAPIRequest($config['endpoint'], $config['headers'], $payload, 'chatgpt');
    }

    private function callClaude(array $config, string $prompt, string $type): array {
        $systemPrompt = $this->getSystemPrompt($type);

        $payload = [
            'model' => $config['model'],
            'max_tokens' => 1024,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        return $this->makeAPIRequest($config['endpoint'], $config['headers'], $payload, 'claude');
    }

    private function callGemini(array $config, string $prompt, string $type): array {
        $systemPrompt = $this->getSystemPrompt($type);

        $fullPrompt = $systemPrompt . "\n\n" . $prompt;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1024
            ]
        ];

        $endpoint = $config['endpoint'] . '?key=' . $config['api_key'];

        return $this->makeAPIRequest($endpoint, $config['headers'], $payload, 'gemini');
    }

    private function makeAPIRequest(string $endpoint, array $headers, array $payload, string $provider): array {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => "API request failed with code: $httpCode for $provider"];
        }

        $result = json_decode($response, true);

        return $this->parseResponse($result, $provider);
    }

    private function parseResponse(array $result, string $provider): array {
        switch ($provider) {
            case 'chatgpt':
                if (isset($result['choices'][0]['message']['content'])) {
                    return ['success' => true, 'response' => $result['choices'][0]['message']['content']];
                }
                break;

            case 'claude':
                if (isset($result['content'][0]['text'])) {
                    return ['success' => true, 'response' => $result['content'][0]['text']];
                }
                break;

            case 'gemini':
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    return ['success' => true, 'response' => $result['candidates'][0]['content']['parts'][0]['text']];
                }
                break;
        }

        return ['error' => "Invalid response format from $provider"];
    }

    private function getSystemPrompt(string $type): string {
        switch ($type) {
            case 'expense_suggestion':
                return 'You are an expert financial advisor specializing in travel expense management. Provide practical, actionable suggestions for expense tracking and budget optimization. Focus on realistic amounts and helpful recommendations.';

            case 'budget_advisory':
                return 'You are a travel budget consultant. Provide clear, actionable advice on budget management, spending patterns, and financial planning for trips. Be specific and practical.';

            case 'receipt_analysis':
                return 'You are an expert at analyzing receipts and expense documents. Extract accurate information about amounts, vendors, dates, and categorize expenses appropriately.';

            default:
                return 'You are a helpful AI assistant specializing in travel and expense management.';
        }
    }

    private function combineResponses(array $responses, string $type): array {
        // For JSON responses, try to combine them intelligently
        if ($type === 'expense_suggestion') {
            return $this->combineExpenseSuggestions($responses);
        }

        // For other types, return the first successful response but note that multiple AIs were used
        $firstProvider = array_key_first($responses);
        return [
            'success' => true,
            'response' => $responses[$firstProvider],
            'provider' => $firstProvider,
            'multi_ai_used' => true,
            'ai_count' => count($responses)
        ];
    }

    private function combineExpenseSuggestions(array $responses): array {
        $combinedSuggestions = [];
        $usedSuggestions = [];

        foreach ($responses as $provider => $response) {
            $parsed = json_decode($response, true);
            if (is_array($parsed)) {
                foreach ($parsed as $suggestion) {
                    // Avoid duplicates by checking category + subcategory
                    $key = ($suggestion['category'] ?? '') . '-' . ($suggestion['subcategory'] ?? '');
                    if (!in_array($key, $usedSuggestions)) {
                        $suggestion['ai_source'] = $provider;
                        $combinedSuggestions[] = $suggestion;
                        $usedSuggestions[] = $key;
                    }
                }
            }
        }

        // Limit to top 8 suggestions
        $combinedSuggestions = array_slice($combinedSuggestions, 0, 8);

        return [
            'success' => true,
            'response' => json_encode($combinedSuggestions),
            'provider' => 'multi_ai',
            'multi_ai_used' => true,
            'ai_count' => count($responses)
        ];
    }

    public function getProviderStatus(): array {
        $status = [];
        foreach ($this->providers as $name => $config) {
            $status[$name] = [
                'enabled' => $config['enabled'],
                'has_api_key' => !empty($config['api_key'])
            ];
        }
        return $status;
    }
}

// Helper function for backward compatibility
function callMultiAI(string $prompt, string $type = 'general'): array {
    static $aiService = null;
    if ($aiService === null) {
        $aiService = new MultiAIService();
    }
    return $aiService->generateSuggestions($prompt, $type);
}

?>