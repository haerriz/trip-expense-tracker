<?php
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
    
    // Store OTP in session with timestamp
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_phone'] = $phone;
    $_SESSION['otp_time'] = time();
    
    // Try completely free SMS services (no API keys required)
    $message = "Your Haerriz Trip Finance OTP is: $otp. Valid for 5 minutes.";
    $sent = false;
    
    // Method 1: Free SMS Gateway (no API key) - works globally
    if (!$sent) {
        $sent = sendFreeSMS($phone, $message);
    }
    
    // Method 2: Email as SMS (carrier gateways) - completely free
    if (!$sent) {
        $sent = sendSMSViaEmail($phone, $message);
    }
    
    // Always return success for demo purposes and show OTP
    echo json_encode([
        'success' => true, 
        'message' => $sent ? 'OTP sent to your phone' : 'SMS service unavailable. Your OTP is: ' . $otp,
        'demo_otp' => $otp, // Show OTP for demo/testing
        'phone' => $phone
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function sendFreeSMS($phone, $message) {
    // Free SMS using httpSMS.com (no registration required)
    $url = 'https://httpSMS.com/send';
    
    $data = [
        'phone' => $phone,
        'message' => $message,
        'key' => 'free' // Free tier
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

function sendSMSViaEmail($phone, $message) {
    // SMS via email gateways (completely free)
    $carriers = [
        // US Carriers
        'verizon' => '@vtext.com',
        'att' => '@txt.att.net',
        'tmobile' => '@tmomail.net',
        'sprint' => '@messaging.sprintpcs.com',
        // International
        'vodafone' => '@vodafone.net',
        'orange' => '@orange.net'
    ];
    
    $sent = false;
    foreach ($carriers as $carrier => $gateway) {
        $to = $phone . $gateway;
        $subject = 'OTP Verification';
        
        $headers = [
            'From: noreply@haerriz.com',
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        if (mail($to, $subject, $message, implode("\r\n", $headers))) {
            $sent = true;
            break;
        }
    }
    
    return $sent;
}
?>