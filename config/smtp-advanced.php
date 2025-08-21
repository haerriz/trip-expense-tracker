<?php
// Advanced SMTP with PHPMailer-like functionality
function sendEmailAdvanced($to, $subject, $body, $attachments = []) {
    $config = [
        'host' => 'smtp.hostinger.com',
        'port' => 465,
        'username' => 'haerriz@haerriz.com',
        'password' => 'Admin@123',
        'encryption' => 'ssl'
    ];
    
    // Create socket connection
    $socket = fsockopen('ssl://' . $config['host'], $config['port'], $errno, $errstr, 30);
    
    if (!$socket) {
        return sendEmailFallback($to, $subject, $body);
    }
    
    // SMTP conversation
    $commands = [
        "EHLO haerriz.com\r\n",
        "AUTH LOGIN\r\n",
        base64_encode($config['username']) . "\r\n",
        base64_encode($config['password']) . "\r\n",
        "MAIL FROM: <{$config['username']}>\r\n",
        "RCPT TO: <$to>\r\n",
        "DATA\r\n"
    ];
    
    foreach ($commands as $command) {
        fwrite($socket, $command);
        $response = fgets($socket, 512);
        
        if (substr($response, 0, 1) == '5') {
            fclose($socket);
            return sendEmailFallback($to, $subject, $body);
        }
    }
    
    // Email headers and body
    $email = "Subject: $subject\r\n";
    $email .= "From: Haerriz Trip Finance <{$config['username']}>\r\n";
    $email .= "Content-Type: text/html; charset=UTF-8\r\n";
    $email .= "\r\n$body\r\n.\r\n";
    
    fwrite($socket, $email);
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
    
    return true;
}

function sendEmailFallback($to, $subject, $body) {
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Haerriz Trip Finance <haerriz@haerriz.com>',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($to, $subject, $body, implode("\r\n", $headers));
}
?>