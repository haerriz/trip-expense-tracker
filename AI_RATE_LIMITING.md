# AI Rate Limiting System

## Overview
The expense tracker now includes comprehensive rate limiting to prevent API abuse and control costs. Each user has daily limits for AI API calls.

## Current Limits

| AI Provider | Daily Limit | Purpose |
|-------------|-------------|---------|
| **ChatGPT** | 10 requests | General AI suggestions |
| **Claude** | 15 requests | Alternative AI provider |
| **Gemini** | 20 requests | Google AI provider |
| **Total** | 30 requests | Combined across all providers |

## How It Works

### Database Tracking
- Usage is tracked in the `ai_api_usage` table
- Records are stored per user, per provider, per day
- Automatic cleanup of old records (kept for 30 days)

### Rate Limiting Logic
1. **Check Limits**: Before each AI call, verify user hasn't exceeded limits
2. **Provider Priority**: ChatGPT → Claude → Gemini (fallback order)
3. **Usage Recording**: Successful calls are recorded immediately
4. **Error Handling**: Clear messages when limits are exceeded

### API Endpoints

#### Check Usage Stats
```javascript
POST /api/suggest_expenses.php
{
    "action": "usage_stats"
}
```

Response:
```json
{
    "success": true,
    "usage": {
        "total": {"used": 5, "limit": 30},
        "chatgpt": {"used": 2, "limit": 10},
        "claude": {"used": 3, "limit": 15},
        "gemini": {"used": 0, "limit": 20}
    }
}
```

#### AI Status
```javascript
POST /api/suggest_expenses.php
{
    "action": "ai_status"
}
```

#### Rate Limited Response
When limits are exceeded:
```json
{
    "success": false,
    "message": "Daily AI request limit exceeded",
    "limit_exceeded": true,
    "limit_info": {
        "reason": "Daily AI request limit exceeded",
        "limit": 30,
        "used": 30,
        "reset_time": "tomorrow"
    }
}
```

## Admin Functions

### Update Rate Limits (Code Only)
```php
$rateLimiter = new RateLimiter($pdo);
$rateLimiter->updateLimits([
    'chatgpt' => 20,  // Increase ChatGPT limit
    'total' => 50     // Increase total limit
]);
```

### Reset User Usage (Code Only)
```php
$rateLimiter->resetUserUsage($userId, '2024-01-01');
```

### View Current Limits
```php
$limits = $rateLimiter->getLimits();
// Returns: ['chatgpt' => 10, 'claude' => 15, 'gemini' => 20, 'total' => 30]
```

## Database Schema

```sql
CREATE TABLE ai_api_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_provider VARCHAR(50) NOT NULL,
    request_count INT DEFAULT 1,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_provider_date (user_id, api_provider, date)
);
```

## Benefits

### Cost Control
- Prevents runaway API costs
- Fair usage across all users
- Predictable monthly expenses

### Abuse Prevention
- Stops malicious users from excessive API calls
- Protects against DoS-style attacks
- Maintains service availability

### User Experience
- Clear error messages with reset times
- Usage tracking for transparency
- Gradual limit increases possible

## Configuration

Limits can be adjusted in `/config/rate_limiter.php`:

```php
private $limits = [
    'chatgpt' => 10,      // Adjust as needed
    'claude' => 15,       // Based on your budget
    'gemini' => 20,       // Google's generous limits
    'total' => 30         // Safety net
];
```

## Monitoring

### Daily Usage Reports
```sql
SELECT
    DATE(date) as day,
    api_provider,
    SUM(request_count) as total_requests,
    COUNT(DISTINCT user_id) as unique_users
FROM ai_api_usage
WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(date), api_provider
ORDER BY day DESC, api_provider;
```

### Top Users
```sql
SELECT
    u.name,
    u.email,
    SUM(au.request_count) as total_requests,
    COUNT(DISTINCT au.api_provider) as providers_used
FROM ai_api_usage au
JOIN users u ON au.user_id = u.id
WHERE au.date = CURDATE()
GROUP BY au.user_id
ORDER BY total_requests DESC
LIMIT 10;
```

## Future Enhancements

- **Hourly Limits**: Prevent burst usage
- **Tiered Limits**: Premium users get higher limits
- **Admin Dashboard**: Web interface for limit management
- **Usage Analytics**: Charts and reports
- **Auto-scaling**: Dynamic limits based on API costs