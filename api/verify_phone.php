<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $userId = $_SESSION['user_id'];
    
    // Using free SMS API service (textbelt.com - 1 free SMS per day per IP)
    $message = "Your Haerriz Trip Finance verification code is: " . rand(100000, 999999);
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://textbelt.com/text",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'phone' => $phone,
            'message' => $message,
            'key' => 'textbelt' // Free tier
        ]),
        CURLOPT_RETURNTRANSFER => true
    ]);
    
    $response = curl_exec($curl);
    curl_close($curl);
    
    $result = json_decode($response, true);
    
    if ($result['success']) {
        // Store verification code in session for demo
        $_SESSION['phone_verification_code'] = substr($message, -6);
        echo json_encode(['success' => true, 'message' => 'Verification code sent']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send SMS']);
    }
} else {
    echo json_encode(['success' => false]);
}
?>