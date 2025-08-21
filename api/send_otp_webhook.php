<?php
// Completely free webhook-based OTP using IFTTT or Zapier
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $userId = $_SESSION['user_id'];
    
    if (empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Phone number required']);
        exit;
    }
    
    // Generate 6-digit OTP
    $otp = sprintf('%06d', mt_rand(100000, 999999));
    
    // Store OTP in session
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_phone'] = $phone;
    $_SESSION['otp_time'] = time();
    
    $message = "Haerriz Trip Finance OTP: $otp (Valid 5 min)";
    
    // Method 1: IFTTT Webhook (completely free)
    $sent1 = sendViaIFTTT($phone, $message);
    
    // Method 2: Zapier Webhook (free tier)
    $sent2 = sendViaZapier($phone, $message);
    
    // Method 3: Direct WhatsApp API (free)
    $sent3 = sendViaWhatsApp($phone, $message);
    
    // Always show OTP for demo
    echo json_encode([
        'success' => true,
        'message' => 'OTP generated successfully',
        'otp' => $otp, // Always show for demo
        'methods_tried' => [
            'ifttt' => $sent1,
            'zapier' => $sent2, 
            'whatsapp' => $sent3
        ]
    ]);
}

function sendViaIFTTT($phone, $message) {
    // Free IFTTT webhook - user can set up their own
    $webhookUrl = 'https://maker.ifttt.com/trigger/sms_otp/with/key/YOUR_IFTTT_KEY';
    
    $data = [
        'value1' => $phone,
        'value2' => $message,
        'value3' => 'haerriz_app'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhookUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return !empty($response);
}

function sendViaZapier($phone, $message) {
    // Free Zapier webhook
    $webhookUrl = 'https://hooks.zapier.com/hooks/catch/YOUR_ZAPIER_HOOK/';
    
    $data = [
        'phone' => $phone,
        'message' => $message,
        'app' => 'haerriz_finance'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhookUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return !empty($response);
}

function sendViaWhatsApp($phone, $message) {
    // Free WhatsApp Business API (limited)
    $url = 'https://api.whatsapp.com/send';
    
    $data = [
        'phone' => $phone,
        'text' => $message
    ];
    
    // This is a demo - actual implementation would need WhatsApp Business setup
    return false; // Placeholder
}
?>