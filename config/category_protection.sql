-- Add archived column to categories table for soft delete protection
ALTER TABLE categories ADD COLUMN archived BOOLEAN DEFAULT FALSE;
ALTER TABLE categories ADD COLUMN subcategories TEXT;

-- Update existing categories with subcategories
UPDATE categories SET subcategories = 'Restaurant,Street Food,Groceries,Drinks,Snacks' WHERE name = 'Food & Drinks';
UPDATE categories SET subcategories = 'Flight,Train,Bus,Taxi,Rental Car,Fuel,Parking' WHERE name = 'Transportation';
UPDATE categories SET subcategories = 'Hotel,Hostel,Airbnb,Camping,Guesthouse' WHERE name = 'Accommodation';
UPDATE categories SET subcategories = 'Tours,Museums,Adventure Sports,Nightlife,Events' WHERE name = 'Activities';
UPDATE categories SET subcategories = 'Souvenirs,Clothes,Electronics,Gifts' WHERE name = 'Shopping';
UPDATE categories SET subcategories = 'Medical,Insurance,Lost Items,Emergency Transport' WHERE name = 'Emergency';
UPDATE categories SET subcategories = 'Tips,Fees,Miscellaneous' WHERE name = 'Other';