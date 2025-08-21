<?php
require_once '../../includes/auth.php';
require_once '../../includes/admin.php';
requireLogin();
requireMasterAdmin();

header('Content-Type: application/json');

try {
    $type = $_GET['type'] ?? '';
    
    if ($type === 'categories') {
        // Get expenses by category
        $stmt = $pdo->prepare("
            SELECT category, SUM(amount) as total_amount 
            FROM expenses 
            GROUP BY category 
            ORDER BY total_amount DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $labels = [];
        $values = [];
        
        foreach ($results as $row) {
            $labels[] = $row['category'];
            $values[] = floatval($row['total_amount']);
        }
        
        echo json_encode([
            'success' => true,
            'labels' => $labels,
            'values' => $values
        ]);
        
    } elseif ($type === 'users') {
        // Get user registration trend (last 12 months)
        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as user_count
            FROM users 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $labels = [];
        $values = [];
        
        foreach ($results as $row) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $values[] = intval($row['user_count']);
        }
        
        echo json_encode([
            'success' => true,
            'labels' => $labels,
            'values' => $values
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid chart type']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>