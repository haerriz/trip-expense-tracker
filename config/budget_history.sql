-- Table to log all budget changes (initial, increase, decrease) for each trip
CREATE TABLE IF NOT EXISTS budget_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    user_id INT NOT NULL,
    change_type ENUM('initial','increase','decrease') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    new_budget DECIMAL(12,2) NOT NULL,
    reason VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Add initial budget for existing trips
INSERT INTO budget_history (trip_id, user_id, change_type, amount, new_budget, reason, created_at)
SELECT id, created_by, 'initial', budget, budget, 'Initial budget', created_at FROM trips WHERE budget IS NOT NULL;
