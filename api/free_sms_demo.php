<?php
// Completely free SMS demo using browser notification and console
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $userId = $_SESSION['user_id'];
    
    // Generate OTP
    $otp = sprintf('%06d', mt_rand(100000, 999999));
    
    // Store in session
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_phone'] = $phone;
    $_SESSION['otp_time'] = time();
    
    // Log OTP for demo (in production, this would be sent via SMS)
    
    // Return OTP directly for demo
    echo json_encode([
        'success' => true,
        'message' => "Demo Mode: Your OTP is $otp",
        'otp' => $otp,
        'phone' => $phone,
        'demo' => true,
        'instructions' => 'In production, this OTP would be sent via SMS. For demo, it is shown here.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>