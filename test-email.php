<?php
require_once 'config/smtp.php';

$testEmail = 'test@example.com'; // Replace with your test email
$subject = 'Test Email from Haerriz Trip Finance';
$body = '<h2>Email Test Successful!</h2><p>Your SMTP configuration is working correctly.</p>';

if (sendEmail($testEmail, $subject, $body)) {
    echo "✅ Email sent successfully to $testEmail";
} else {
    echo "❌ Failed to send email";
}
?>