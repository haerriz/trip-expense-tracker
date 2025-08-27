-- Table to log all budget changes (initial, increase, decrease) for each trip
-- Table already exists, so only insert initial budgets if needed
INSERT INTO budget_history (trip_id, previous_budget, new_budget, adjustment_amount, adjustment_type, reason, adjusted_by, created_at)
SELECT id, NULL, budget, budget, 'set', 'Initial budget', created_by, created_at FROM trips WHERE budget IS NOT NULL;
