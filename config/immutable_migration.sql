-- Migration for Immutable Expense Tracking System
USE trip_expense_tracker;

-- Add immutable flag to expenses table
ALTER TABLE expenses ADD COLUMN is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE expenses ADD COLUMN replaced_by INT DEFAULT NULL;
ALTER TABLE expenses ADD COLUMN replacement_reason VARCHAR(255) DEFAULT NULL;

-- Create budget history table
CREATE TABLE IF NOT EXISTS budget_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    previous_budget DECIMAL(10,2),
    new_budget DECIMAL(10,2),
    adjustment_amount DECIMAL(10,2),
    adjustment_type ENUM('increase', 'decrease', 'set') NOT NULL,
    reason VARCHAR(255),
    adjusted_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id),
    FOREIGN KEY (adjusted_by) REFERENCES users(id)
);

-- Create expense history table for tracking changes
CREATE TABLE IF NOT EXISTS expense_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_expense_id INT NOT NULL,
    trip_id INT NOT NULL,
    paid_by INT NOT NULL,
    category VARCHAR(100),
    subcategory VARCHAR(100),
    amount DECIMAL(10,2),
    description TEXT,
    date DATE,
    split_type ENUM('equal', 'custom') DEFAULT 'equal',
    change_reason VARCHAR(255),
    changed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (original_expense_id) REFERENCES expenses(id),
    FOREIGN KEY (trip_id) REFERENCES trips(id),
    FOREIGN KEY (paid_by) REFERENCES users(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

-- Add indexes for performance
CREATE INDEX idx_expenses_active ON expenses(is_active);
CREATE INDEX idx_budget_history_trip ON budget_history(trip_id);
CREATE INDEX idx_expense_history_original ON expense_history(original_expense_id);