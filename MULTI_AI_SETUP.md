# Multi-AI Integration Setup

Your expense tracker now supports multiple AI providers for enhanced suggestions and analysis. This provides redundancy, better responses, and combines insights from different AI models.

## Supported AI Providers

1. **ChatGPT (OpenAI GPT-4)** - Primary AI for detailed analysis
2. **Claude (Anthropic)** - Alternative AI with strong reasoning
3. **Gemini (Google)** - Google's AI for additional perspectives

## Environment Variables Required

Add these to your production environment (`.env` file or server environment):

```bash
# OpenAI (ChatGPT)
OPENAI_API_KEY=sk-your-openai-api-key-here

# Anthropic (Claude)
ANTHROPIC_API_KEY=sk-ant-your-anthropic-key-here

# Google AI (Gemini)
GOOGLE_AI_API_KEY=your-google-ai-api-key-here
```

## How It Works

### Fallback System
- The system tries providers in this order: ChatGPT → Claude → Gemini
- If the first AI fails, it automatically tries the next one
- You only need at least one API key for the system to work

### Response Combination
- For expense suggestions, responses from multiple AIs are intelligently combined
- Duplicates are removed, and the best suggestions are selected
- Results include which AI(s) were used

### API Endpoints

The existing API endpoints now use multi-AI:

- `POST /api/suggest_expenses.php` with `action=suggest`
- `POST /api/suggest_expenses.php` with `action=budget_advisory`
- `POST /api/suggest_expenses.php` with `action=analyze_receipt`
- `POST /api/suggest_expenses.php` with `action=ai_status` (new)

## Getting API Keys

### OpenAI (ChatGPT)
1. Go to [OpenAI Platform](https://platform.openai.com/api-keys)
2. Sign up/Login
3. Create a new API key
4. Copy the key starting with `sk-`

### Anthropic (Claude)
1. Go to [Anthropic Console](https://console.anthropic.com/)
2. Sign up/Login
3. Create a new API key
4. Copy the key starting with `sk-ant-`

### Google AI (Gemini)
1. Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Sign in with Google account
3. Create a new API key
4. Copy the key

## Testing

You can check which AI providers are active:

```javascript
fetch('/api/suggest_expenses.php', {
    method: 'POST',
    body: new FormData([['action', 'ai_status']])
})
.then(response => response.json())
.then(data => console.log(data.providers));
```

## Benefits

- **Redundancy**: If one AI service is down, others automatically take over
- **Better Results**: Multiple AI perspectives provide more comprehensive suggestions
- **Cost Optimization**: Use the most cost-effective AI for different tasks
- **Future-Proof**: Easy to add new AI providers as they become available

## Cost Considerations

- **OpenAI**: ~$0.03 per 1K tokens (GPT-4)
- **Anthropic**: ~$0.015 per 1K tokens (Claude 3.5)
- **Google**: ~$0.002 per 1K characters (Gemini Pro)

The system uses minimal tokens per request, keeping costs low.