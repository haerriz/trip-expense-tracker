-- Add archived column to categories table for soft delete protection
ALTER TABLE categories ADD COLUMN IF NOT EXISTS archived BOOLEAN DEFAULT FALSE;
ALTER TABLE categories ADD COLUMN IF NOT EXISTS subcategories TEXT;

-- Update get categories to exclude archived ones
-- This will be handled in the API, not in SQL