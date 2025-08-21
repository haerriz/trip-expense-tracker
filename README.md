# Trip Expense Tracker 🎒

A web-based expense tracking application for backpackers and tour groups with multi-currency support and group expense splitting.

## Features ✨

- **Trip Management**: Create trips with budget, dates, and currency
- **Group Expenses**: Invite friends and split expenses automatically
- **Multi-Currency**: Support for 9 major currencies (USD, EUR, GBP, JPY, AUD, CAD, INR, THB, VND)
- **Detailed Categories**: Backpacker-focused categories with subcategories
- **Visual Analytics**: Pie charts showing expense breakdown
- **Manual & Google Auth**: Login with email/password or Google OAuth
- **Responsive Design**: Works on mobile devices during trips

## Setup Instructions 🚀

1. **Database Setup:**
   ```bash
   mysql -u root -p < config/setup.sql
   ```

2. **Start PHP Server:**
   ```bash
   php -S localhost:8000
   ```

3. **Access Application:**
   Open http://localhost:8000 in your browser

4. **Optional - Google OAuth Setup:**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create OAuth 2.0 credentials
   - Replace `YOUR_GOOGLE_CLIENT_ID` in `index.html`

## Usage 📱

1. **Create Account**: Sign up with email/password
2. **Create Trip**: Set name, dates, budget, and currency
3. **Invite Friends**: Add members by email
4. **Track Expenses**: Add expenses with categories and subcategories
5. **View Analytics**: See expense breakdown and individual shares

## Categories & Subcategories 🏷️

- **Food & Drinks**: Restaurant, Street Food, Groceries, Drinks, Snacks
- **Transportation**: Flight, Train, Bus, Taxi, Rental Car, Fuel, Parking
- **Accommodation**: Hotel, Hostel, Airbnb, Camping, Guesthouse
- **Activities**: Tours, Museums, Adventure Sports, Nightlife, Events
- **Shopping**: Souvenirs, Clothes, Electronics, Gifts
- **Emergency**: Medical, Insurance, Lost Items, Emergency Transport
- **Other**: Tips, Fees, Miscellaneous

## File Structure 📁

```
/
├── api/                 # REST API endpoints
├── config/             # Database configuration
├── css/                # Stylesheets
├── includes/           # PHP includes
├── js/                 # JavaScript files
├── index.html          # Login page
├── dashboard.php       # Main dashboard
├── manual-login.php    # Manual authentication
├── manual-signup.php   # User registration
└── README.md          # This file
```

## Database Configuration 🗄️

- **Database**: trip_expense_tracker
- **Default credentials**: root/admin@123
- **Tables**: users, trips, trip_members, expenses, expense_splits, categories

## Technologies Used 💻

- **Backend**: PHP, MySQL
- **Frontend**: HTML5, CSS3, JavaScript, jQuery
- **Charts**: Chart.js
- **Authentication**: Manual + Google OAuth
- **Responsive**: Mobile-first design

## Contributing 🤝

Feel free to fork this repository and submit pull requests for improvements!

## License 📄

MIT License - feel free to use for personal or commercial projects.