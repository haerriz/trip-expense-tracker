CREATE DATABASE IF NOT EXISTS trip_expense_tracker;
USE trip_expense_tracker;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(255) UNIQUE,
    email VARCHAR(255) UNIQUE,
    name VARCHAR(255),
    password VARCHAR(255),
    picture VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trips (
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
);

CREATE TABLE IF NOT EXISTS trip_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT,
    user_id INT,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_member (trip_id, user_id)
);

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT,
    paid_by INT,
    category VARCHAR(100),
    subcategory VARCHAR(100),
    amount DECIMAL(10,2),
    description TEXT,
    date DATE,
    split_type ENUM('equal', 'custom') DEFAULT 'equal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id),
    FOREIGN KEY (paid_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS expense_splits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_id INT,
    user_id INT,
    amount DECIMAL(10,2),
    FOREIGN KEY (expense_id) REFERENCES expenses(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

INSERT IGNORE INTO categories (name) VALUES 
('Food & Drinks'),
('Transportation'),
('Accommodation'),
('Activities'),
('Shopping'),
('Emergency'),
('Other');