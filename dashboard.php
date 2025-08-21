<?php
require_once 'includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Expense Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">Trip Expense Tracker</div>
        <div class="nav-user">
            <img src="<?php echo $_SESSION['user_picture']; ?>" alt="Profile" class="profile-pic">
            <span><?php echo $_SESSION['user_name']; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="trip-selector">
            <select id="current-trip">
                <option value="">Select a Trip</option>
            </select>
            <button id="new-trip-btn" class="btn-primary">New Trip</button>
        </div>

        <div id="no-trip" class="empty-state">
            <h2>Welcome to Trip Expense Tracker!</h2>
            <p>Create your first trip to start tracking expenses with friends</p>
        </div>

        <div id="trip-dashboard" style="display:none;">
            <div class="summary-cards">
                <div class="card budget">
                    <h3>Trip Budget</h3>
                    <div class="amount" id="trip-budget">$0.00</div>
                </div>
                <div class="card spent">
                    <h3>Total Spent</h3>
                    <div class="amount" id="total-spent">$0.00</div>
                </div>
                <div class="card remaining">
                    <h3>Remaining</h3>
                    <div class="amount" id="remaining-budget">$0.00</div>
                </div>
                <div class="card my-share">
                    <h3>My Share</h3>
                    <div class="amount" id="my-share">$0.00</div>
                </div>
            </div>

            <div class="main-content">
                <div class="left-panel">
                    <div class="add-expense">
                        <h2>Add Expense</h2>
                        <form id="expense-form">
                            <select id="category" required>
                                <option value="">Select Category</option>
                            </select>
                            <select id="subcategory" required>
                                <option value="">Select Subcategory</option>
                            </select>
                            <input type="number" id="amount" placeholder="Amount" step="0.01" required>
                            <input type="text" id="description" placeholder="Description" required>
                            <input type="date" id="date" required>
                            <div class="split-options">
                                <label><input type="radio" name="split" value="equal" checked> Split Equally</label>
                                <label><input type="radio" name="split" value="custom"> Custom Split</label>
                            </div>
                            <button type="submit">Add Expense</button>
                        </form>
                    </div>

                    <div class="trip-members">
                        <h2>Trip Members</h2>
                        <div id="members-list"></div>
                        <input type="email" id="invite-email" placeholder="Email to invite">
                        <button id="invite-btn">Invite</button>
                    </div>
                </div>

                <div class="right-panel">
                    <div class="expense-chart">
                        <h2>Expense Breakdown</h2>
                        <canvas id="expenseChart" width="300" height="300"></canvas>
                    </div>

                    <div class="recent-expenses">
                        <h2>Recent Expenses</h2>
                        <div id="expenses-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trip Modal -->
    <div id="trip-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Create New Trip</h2>
            <form id="trip-form">
                <input type="text" id="trip-name" placeholder="Trip Name" required>
                <textarea id="trip-description" placeholder="Description"></textarea>
                <input type="date" id="start-date" required>
                <input type="date" id="end-date" required>
                <select id="currency" required>
                    <option value="USD">USD ($)</option>
                    <option value="EUR">EUR (€)</option>
                    <option value="GBP">GBP (£)</option>
                    <option value="JPY">JPY (¥)</option>
                    <option value="AUD">AUD (A$)</option>
                    <option value="CAD">CAD (C$)</option>
                    <option value="INR">INR (₹)</option>
                    <option value="THB">THB (฿)</option>
                    <option value="VND">VND (₫)</option>
                </select>
                <input type="number" id="budget" placeholder="Budget" step="0.01" required>
                <button type="submit">Create Trip</button>
            </form>
        </div>
    </div>

    <script src="js/trip-dashboard.js"></script>
</body>
</html>