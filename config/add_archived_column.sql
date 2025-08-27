-- Add archived and subcategories columns safely
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'categories' 
     AND table_schema = DATABASE() 
     AND column_name = 'archived') > 0,
    'SELECT "archived column already exists"',
    'ALTER TABLE categories ADD COLUMN archived BOOLEAN DEFAULT FALSE'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'categories' 
     AND table_schema = DATABASE() 
     AND column_name = 'subcategories') > 0,
    'SELECT "subcategories column already exists"',
    'ALTER TABLE categories ADD COLUMN subcategories TEXT'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing categories with subcategories if they don't have them
UPDATE categories SET subcategories = 'Restaurant,Street Food,Groceries,Drinks,Snacks' WHERE name = 'Food & Drinks' AND (subcategories IS NULL OR subcategories = '');
UPDATE categories SET subcategories = 'Flight,Train,Bus,Taxi,Rental Car,Fuel,Parking' WHERE name = 'Transportation' AND (subcategories IS NULL OR subcategories = '');
UPDATE categories SET subcategories = 'Hotel,Hostel,Airbnb,Camping,Guesthouse' WHERE name = 'Accommodation' AND (subcategories IS NULL OR subcategories = '');
UPDATE categories SET subcategories = 'Tours,Museums,Adventure Sports,Nightlife,Events' WHERE name = 'Activities' AND (subcategories IS NULL OR subcategories = '');
UPDATE categories SET subcategories = 'Souvenirs,Clothes,Electronics,Gifts' WHERE name = 'Shopping' AND (subcategories IS NULL OR subcategories = '');
UPDATE categories SET subcategories = 'Medical,Insurance,Lost Items,Emergency Transport' WHERE name = 'Emergency' AND (subcategories IS NULL OR subcategories = '');
UPDATE categories SET subcategories = 'Tips,Fees,Miscellaneous' WHERE name = 'Other' AND (subcategories IS NULL OR subcategories = '');