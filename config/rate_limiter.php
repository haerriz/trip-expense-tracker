<?php

class RateLimiter {
    private $pdo;
    private $limits = [
        'chatgpt' => 10,      // 10 requests per day
        'claude' => 15,       // 15 requests per day
        'gemini' => 20,       // 20 requests per day
        'total' => 30         // 30 total AI requests per day across all providers
    ];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Check if user can make an AI API request
     */
    public function canMakeRequest(int $userId, string $provider = null): array {
        $today = date('Y-m-d');

        // Check total daily limit
        $totalUsage = $this->getTotalUsage($userId, $today);
        if ($totalUsage >= $this->limits['total']) {
            return [
                'allowed' => false,
                'reason' => 'Daily AI request limit exceeded',
                'limit' => $this->limits['total'],
                'used' => $totalUsage,
                'reset_time' => 'tomorrow'
            ];
        }

        // Check provider-specific limit if specified
        if ($provider && isset($this->limits[$provider])) {
            $providerUsage = $this->getProviderUsage($userId, $provider, $today);
            if ($providerUsage >= $this->limits[$provider]) {
                return [
                    'allowed' => false,
                    'reason' => "Daily {$provider} request limit exceeded",
                    'limit' => $this->limits[$provider],
                    'used' => $providerUsage,
                    'reset_time' => 'tomorrow'
                ];
            }
        }

        return ['allowed' => true];
    }

    /**
     * Record an AI API request
     */
    public function recordRequest(int $userId, string $provider): bool {
        $today = date('Y-m-d');

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ai_api_usage (user_id, api_provider, request_count, date)
                VALUES (?, ?, 1, ?)
                ON DUPLICATE KEY UPDATE request_count = request_count + 1
            ");
            return $stmt->execute([$userId, $provider, $today]);
        } catch (Exception $e) {
            error_log("Rate limiter error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total AI usage for user today
     */
    private function getTotalUsage(int $userId, string $date): int {
        $stmt = $this->pdo->prepare("
            SELECT SUM(request_count) as total
            FROM ai_api_usage
            WHERE user_id = ? AND date = ?
        ");
        $stmt->execute([$userId, $date]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get usage for specific provider today
     */
    private function getProviderUsage(int $userId, string $provider, string $date): int {
        $stmt = $this->pdo->prepare("
            SELECT request_count
            FROM ai_api_usage
            WHERE user_id = ? AND api_provider = ? AND date = ?
        ");
        $stmt->execute([$userId, $provider, $date]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get user's current usage stats
     */
    public function getUsageStats(int $userId): array {
        $today = date('Y-m-d');

        $stats = [
            'total' => [
                'used' => $this->getTotalUsage($userId, $today),
                'limit' => $this->limits['total']
            ]
        ];

        foreach (array_keys($this->limits) as $provider) {
            if ($provider !== 'total') {
                $stats[$provider] = [
                    'used' => $this->getProviderUsage($userId, $provider, $today),
                    'limit' => $this->limits[$provider]
                ];
            }
        }

        return $stats;
    }

    /**
     * Reset usage for a specific user (admin function)
     */
    public function resetUserUsage(int $userId, string $date = null): bool {
        $date = $date ?: date('Y-m-d');

        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM ai_api_usage
                WHERE user_id = ? AND date = ?
            ");
            return $stmt->execute([$userId, $date]);
        } catch (Exception $e) {
            error_log("Reset usage error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update rate limits (admin function)
     */
    public function updateLimits(array $newLimits): void {
        foreach ($newLimits as $provider => $limit) {
            if (isset($this->limits[$provider])) {
                $this->limits[$provider] = (int) $limit;
            }
        }
    }

    /**
     * Get current rate limits
     */
    public function getLimits(): array {
        return $this->limits;
    }
}

// Helper function for easy access
function checkRateLimit(PDO $pdo, int $userId, string $provider = null): array {
    static $rateLimiter = null;
    if ($rateLimiter === null) {
        $rateLimiter = new RateLimiter($pdo);
    }
    return $rateLimiter->canMakeRequest($userId, $provider);
}

function recordApiUsage(PDO $pdo, int $userId, string $provider): bool {
    static $rateLimiter = null;
    if ($rateLimiter === null) {
        $rateLimiter = new RateLimiter($pdo);
    }
    return $rateLimiter->recordRequest($userId, $provider);
}

function getUserApiStats(PDO $pdo, int $userId): array {
    static $rateLimiter = null;
    if ($rateLimiter === null) {
        $rateLimiter = new RateLimiter($pdo);
    }
    return $rateLimiter->getUsageStats($userId);
}

?>