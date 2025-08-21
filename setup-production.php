<?php
// Production database setup
header('Content-Type: application/json');

try {
    // Production database connection
    $host = 'localhost';
    $dbname = 'u434561653_expenses';
    $username = 'u434561653_expenses';
    $password = 'P+ImTaJxU$2h';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
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
    
    // Create trips table
    $pdo->exec("CREATE TABLE IF NOT EXISTS trips (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        start_date DATE,
        end_date DATE,
        budget DECIMAL(10,2),
        currency VARCHAR(3) DEFAULT 'USD',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
    
    // Create trip_members table
    $pdo->exec("CREATE TABLE IF NOT EXISTS trip_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trip_id INT,
        user_id INT,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trip_id) REFERENCES trips(id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        UNIQUE KEY unique_member (trip_id, user_id)
    )");
    
    // Create categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        subcategories TEXT
    )");
    
    // Create expenses table
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trip_id) REFERENCES trips(id),
        FOREIGN KEY (paid_by) REFERENCES users(id)
    )");
    
    // Create expense_splits table
    $pdo->exec("CREATE TABLE IF NOT EXISTS expense_splits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        expense_id INT,
        user_id INT,
        amount DECIMAL(10,2),
        FOREIGN KEY (expense_id) REFERENCES expenses(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Insert default categories
    $pdo->exec("INSERT IGNORE INTO categories (name, subcategories) VALUES 
        ('Food & Drinks', 'Restaurant,Street Food,Groceries,Drinks,Snacks'),
        ('Transportation', 'Flight,Train,Bus,Taxi,Rental Car,Fuel,Parking'),
        ('Accommodation', 'Hotel,Hostel,Airbnb,Camping,Guesthouse'),
        ('Activities', 'Tours,Museums,Adventure Sports,Nightlife,Events'),
        ('Shopping', 'Souvenirs,Clothes,Electronics,Gifts'),
        ('Emergency', 'Medical,Insurance,Lost Items,Emergency Transport'),
        ('Other', 'Tips,Fees,Miscellaneous')");
    
    echo json_encode([
        'success' => true,
        'message' => 'Database setup completed successfully',
        'tables_created' => [
            'users', 'trips', 'trip_members', 'categories', 'expenses', 'expense_splits'
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>