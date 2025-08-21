<?php
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tripId = $_POST['trip_id'] ?? '';
        $email = $_POST['email'] ?? $_SESSION['user_email'];
        
        if (empty($tripId)) {
            echo json_encode(['success' => false, 'message' => 'Trip ID required']);
            exit;
        }
        
        // Get trip data
        $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
        $stmt->execute([$tripId]);
        $trip = $stmt->fetch();
        
        if (!$trip) {
            echo json_encode(['success' => false, 'message' => 'Trip not found']);
            exit;
        }
        
        // Get expenses
        $stmt = $pdo->prepare("
            SELECT e.*, u.name as paid_by_name 
            FROM expenses e 
            JOIN users u ON e.paid_by = u.id 
            WHERE e.trip_id = ? 
            ORDER BY e.date DESC
        ");
        $stmt->execute([$tripId]);
        $expenses = $stmt->fetchAll();
        
        $total = array_sum(array_column($expenses, 'amount'));
        
        // Create email content
        $subject = "Trip Report: " . $trip['name'];
        $message = "Trip Report for: " . $trip['name'] . "\n\n";
        $message .= "Total Expenses: $" . number_format($total, 2) . "\n\n";
        $message .= "Expense Details:\n";
        
        foreach ($expenses as $expense) {
            $message .= "- " . $expense['category'] . ": $" . number_format($expense['amount'], 2);
            $message .= " (Paid by: " . $expense['paid_by_name'] . ")\n";
        }
        
        $headers = "From: noreply@expenses.haerriz.com\r\n";
        $headers .= "Reply-To: noreply@expenses.haerriz.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Simple mail function (may not work on all servers)
        if (mail($email, $subject, $message, $headers)) {
            echo json_encode(['success' => true, 'message' => 'Report sent successfully']);
        } else {
            // Fallback: return report data for client-side handling
            echo json_encode([
                'success' => false, 
                'message' => 'Email service not available. Here is your report:',
                'report_data' => [
                    'trip_name' => $trip['name'],
                    'total' => $total,
                    'expenses' => $expenses
                ]
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>