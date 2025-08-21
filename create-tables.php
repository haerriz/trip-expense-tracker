<?php
header('Content-Type: text/html');

try {
    // Production database connection
    $pdo = new PDO("mysql:host=localhost;dbname=u434561653_expenses", 'u434561653_expenses', 'P+ImTaJxU$2h');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Creating Database Tables...</h2>";
    
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
    echo "<p>✅ Users table created</p>";
    
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p>✅ Trips table created</p>";
    
    // Create trip_members table
    $pdo->exec("CREATE TABLE IF NOT EXISTS trip_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trip_id INT,
        user_id INT,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p>✅ Trip members table created</p>";
    
    // Create categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        subcategories TEXT
    )");
    echo "<p>✅ Categories table created</p>";
    
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p>✅ Expenses table created</p>";
    
    // Create expense_splits table
    $pdo->exec("CREATE TABLE IF NOT EXISTS expense_splits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        expense_id INT,
        user_id INT,
        amount DECIMAL(10,2)
    )");
    echo "<p>✅ Expense splits table created</p>";
    
    // Create chat messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trip_id INT,
        user_id INT,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p>✅ Chat messages table created</p>";
    
    // Insert default categories
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $pdo->exec("INSERT INTO categories (name, subcategories) VALUES 
            ('Food & Drinks', 'Restaurant,Street Food,Groceries,Drinks,Snacks'),
            ('Transportation', 'Flight,Train,Bus,Taxi,Rental Car,Fuel,Parking'),
            ('Accommodation', 'Hotel,Hostel,Airbnb,Camping,Guesthouse'),
            ('Activities', 'Tours,Museums,Adventure Sports,Nightlife,Events'),
            ('Shopping', 'Souvenirs,Clothes,Electronics,Gifts'),
            ('Emergency', 'Medical,Insurance,Lost Items,Emergency Transport'),
            ('Other', 'Tips,Fees,Miscellaneous')");
        echo "<p>✅ Default categories inserted</p>";
    }
    
    echo "<h3>🎉 Database setup completed successfully!</h3>";
    echo "<p><a href='index.html'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error: " . $e->getMessage() . "</h3>";
}
?>