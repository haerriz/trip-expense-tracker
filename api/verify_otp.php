<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $otp = $_POST['otp'];
    $userId = $_SESSION['user_id'];
    
    // Check if OTP is valid
    $sessionOtp = $_SESSION['otp'] ?? '';
    $sessionPhone = $_SESSION['otp_phone'] ?? '';
    $otpTime = $_SESSION['otp_time'] ?? 0;
    
    // OTP expires after 5 minutes
    if (time() - $otpTime > 300) {
        echo json_encode(['success' => false, 'message' => 'OTP expired']);
        exit;
    }
    
    if ($otp !== $sessionOtp || $phone !== $sessionPhone) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
        exit;
    }
    
    // Update user phone verification status
    try {
        $stmt = $pdo->prepare("UPDATE users SET phone = ?, phone_verified = 1 WHERE id = ?");
        $stmt->execute([$phone, $userId]);
        
        // Clear OTP from session
        unset($_SESSION['otp'], $_SESSION['otp_phone'], $_SESSION['otp_time']);
        
        echo json_encode(['success' => true, 'message' => 'Phone verified successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>