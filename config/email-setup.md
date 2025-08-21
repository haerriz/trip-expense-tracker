# Email Configuration for haerriz.com

## SMTP Settings (Hostinger)
- **Host**: smtp.hostinger.com
- **Port**: 465
- **Encryption**: SSL/TLS
- **Username**: noreply@haerriz.com
- **Password**: [Your email password]

## DNS Records Required
Add these CNAME records to haerriz.com DNS:

```
Type: CNAME
Host: autodiscover
Points to: autodiscover.mail.hostinger.com
TTL: 300

Type: CNAME  
Host: autoconfig
Points to: autoconfig.mail.hostinger.com
TTL: 300
```

## Setup Steps
1. Create email account: noreply@haerriz.com in Hostinger panel
2. Update password in `/config/smtp.php`
3. Add DNS CNAME records
4. Test email functionality

## Backup Configuration
- Gmail SMTP as fallback
- Port 587 with TLS encryption
- Requires app-specific password