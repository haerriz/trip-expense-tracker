<?php
// Check if running on production
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'expenses.haerriz.com') {
    // Production configuration
    $host = 'localhost';
    $dbname = 'u434561653_expenses';
    $username = 'u434561653_expenses';
    $password = 'P+ImTaJxU$2h';
} else {
    // Local development configuration
    $host = 'localhost';
    $dbname = 'trip_expense_tracker';
    $username = 'root';
    $password = 'admin@123';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create all tables if they don't exist (for production)
    if ($_SERVER['HTTP_HOST'] === 'expenses.haerriz.com') {
        // Users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            password VARCHAR(255) NOT NULL,
            picture TEXT,
            phone_verified TINYINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Trips table
        $pdo->exec("CREATE TABLE IF NOT EXISTS trips (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            start_date DATE,
            end_date DATE,
            budget DECIMAL(10,2),
            currency VARCHAR(3) DEFAULT 'USD',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Categories table
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            subcategories TEXT
        )");
        
        // Trip members table
        $pdo->exec("CREATE TABLE IF NOT EXISTS trip_members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            trip_id INT,
            user_id INT,
            status ENUM('pending', 'accepted', 'rejected') DEFAULT 'accepted',
            invited_by INT,
            invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            joined_at TIMESTAMP NULL
        )");
        
        // Expenses table
        $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            trip_id INT,
            category VARCHAR(255),
            subcategory VARCHAR(255),
            amount DECIMAL(10,2),
            description TEXT,
            date DATE,
            paid_by INT,
            split_type ENUM('equal', 'custom') DEFAULT 'equal',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Chat messages table
        $pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            trip_id INT,
            user_id INT,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Expense splits table
        $pdo->exec("CREATE TABLE IF NOT EXISTS expense_splits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            expense_id INT,
            user_id INT,
            amount DECIMAL(10,2)
        )");
        
        // Auto-migrate trip_members table
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM trip_members LIKE 'status'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("ALTER TABLE trip_members ADD COLUMN status ENUM('pending', 'accepted', 'rejected') DEFAULT 'accepted'");
                $pdo->exec("ALTER TABLE trip_members ADD COLUMN invited_by INT");
                $pdo->exec("ALTER TABLE trip_members ADD COLUMN invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
                $pdo->exec("ALTER TABLE trip_members MODIFY COLUMN joined_at TIMESTAMP NULL");
                $pdo->exec("UPDATE trip_members SET status = 'accepted' WHERE status IS NULL");
            }
        } catch (Exception $e) {
            // Migration failed, continue anyway
        }
        
        // Insert default categories if empty
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO categories (name, subcategories) VALUES 
                ('Food & Drinks', 'Restaurant,Street Food,Groceries,Drinks,Snacks'),
                ('Transportation', 'Flight,Train,Bus,Taxi,Rental Car,Fuel,Parking'),
                ('Accommodation', 'Hotel,Hostel,Airbnb,Camping,Guesthouse'),
                ('Activities', 'Tours,Museums,Adventure Sports,Nightlife,Events'),
                ('Shopping', 'Souvenirs,Clothes,Electronics,Gifts'),
                ('Emergency', 'Medical,Insurance,Lost Items,Emergency Transport'),
                ('Other', 'Tips,Fees,Miscellaneous')");
        }
    }
} catch(PDOException $e) {
    // Return JSON error for API calls
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }
    die("Connection failed: " . $e->getMessage());
}
?>