# Auto Deployment Setup for expenses.haerriz.com

## Hostinger Webhook Configuration

**Webhook URL:** `https://webhooks.hostinger.com/deploy/690aca6bb4927669891374cbba8493fc`

## GitHub Repository Setup

1. **Repository:** https://github.com/haerriz/trip-expense-tracker
2. **Webhook Setup:** https://github.com/haerriz/trip-expense-tracker/settings/hooks/new

### GitHub Webhook Configuration:
- **Payload URL:** `https://webhooks.hostinger.com/deploy/690aca6bb4927669891374cbba8493fc`
- **Content Type:** `application/json`
- **Events:** Push events (main branch)
- **Active:** ✓ Checked

## Deployment Process

1. **Push to main branch** → Triggers webhook
2. **Hostinger receives webhook** → Pulls latest code
3. **Auto-deployment** → Updates expenses.haerriz.com

## Files for Deployment

- `.github/workflows/deploy.yml` - GitHub Actions workflow
- `deploy.md` - This documentation
- All application files will be deployed automatically

## Testing Deployment

Push any change to main branch and check:
- GitHub Actions tab for workflow execution
- expenses.haerriz.com for updated content
- Hostinger deployment logs

## Production Environment

- **Domain:** expenses.haerriz.com
- **SSL:** Auto-configured by Hostinger
- **Database:** Configure in production environment
- **Email:** Update SMTP settings for haerriz.com domain