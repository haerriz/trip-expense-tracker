<?php
header('Content-Type: application/json');

// Simple signup without database for testing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input && isset($input['email']) && isset($input['password'])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Test signup successful',
            'data' => $input
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid input data'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method'
    ]);
}
?>