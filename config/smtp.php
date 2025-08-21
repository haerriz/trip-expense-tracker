<?php
function sendEmail($to, $subject, $body, $attachments = []) {
    // SMTP Configuration with Hostinger for haerriz.com
    $smtpConfigs = [
        [
            'host' => 'smtp.hostinger.com',
            'port' => 465,
            'username' => 'haerriz@haerriz.com',
            'password' => 'Admin@123',
            'encryption' => 'ssl',
            'from_name' => 'Haerriz Trip Finance'
        ],
        [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => 'backup@gmail.com',
            'password' => 'YOUR_GMAIL_APP_PASSWORD',
            'encryption' => 'tls',
            'from_name' => 'Haerriz Trip Finance'
        ]
    ];
    
    foreach ($smtpConfigs as $config) {
        if (trySendEmail($to, $subject, $body, $config, $attachments)) {
            return true;
        }
    }
    
    return false;
}

function trySendEmail($to, $subject, $body, $config, $attachments) {
    try {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $config['from_name'] . ' <' . $config['username'] . '>',
            'Reply-To: ' . $config['username'],
            'X-Mailer: PHP/' . phpversion(),
            'X-Priority: 3',
            'X-MSMail-Priority: Normal'
        ];
        
        // Simple mail() function fallback
        return mail($to, $subject, $body, implode("\r\n", $headers));
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}
?>