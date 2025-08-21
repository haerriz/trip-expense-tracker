<?php
require_once 'includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Haerriz Trip Finance</title>
    <meta name="description" content="Manage your trip expenses, split costs with friends, and track your travel budget with visual analytics.">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/avatar.js"></script>
    <script>
        window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
    </script>
</head>
<body class="dashboard-page">
    <nav class="navbar">
        <div class="nav-wrapper">
            <a href="#" class="navbar__brand brand-logo">
                <i class="material-icons">flight_takeoff</i>
                Trip Finance
            </a>
            <ul class="navbar__menu right">
                <li class="navbar__user">
                    <a href="profile.php">
                        <img src="<?php echo $_SESSION['user_picture']; ?>" alt="Profile" class="navbar__avatar circle">
                    </a>
                    <span class="navbar__name hide-on-small-only"><?php echo $_SESSION['user_name']; ?></span>
                    <a href="profile.php" class="btn-small blue hide-on-small-only">Profile</a>
                    <a href="logout.php" class="navbar__logout btn-small red">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-page__container container">
        <div class="trip-selector">
            <div class="row">
                <div class="col s12 m6 l4">
                    <div class="input-field">
                        <select id="current-trip" class="trip-selector__select">
                            <option value="">Select a Trip</option>
                        </select>
                        <label>Choose Trip</label>
                    </div>
                </div>
                <div class="col s12 m6 l8">
                    <div class="trip-selector__actions">
                        <button id="new-trip-btn" class="btn waves-effect waves-light">
                            <i class="material-icons left">add</i>New Trip
                        </button>
                        <button id="invitations-btn" class="btn waves-effect waves-light orange">
                            <i class="material-icons left">mail</i>Invitations <span id="invitation-count" class="badge white-text">0</span>
                        </button>
                        <div class="export-buttons">
                            <button id="export-pdf" class="btn-small waves-effect waves-light grey">
                                <i class="material-icons left">picture_as_pdf</i>PDF
                            </button>
                            <button id="export-excel" class="btn-small waves-effect waves-light grey">
                                <i class="material-icons left">table_chart</i>CSV
                            </button>
                            <button id="export-xlsx" class="btn-small waves-effect waves-light grey">
                                <i class="material-icons left">description</i>XLSX
                            </button>
                            <button id="email-report" class="btn-small waves-effect waves-light grey">
                                <i class="material-icons left">email</i>Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Invitations Modal -->
        <div id="invitations-modal" class="modal">
            <div class="modal-content">
                <h4><i class="material-icons left">mail</i>Trip Invitations</h4>
                <div id="invitations-list"></div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat">Close</a>
            </div>
        </div>

        <div id="no-trip" class="empty-state center-align">
            <i class="material-icons large">flight_takeoff</i>
            <h4>Welcome to Trip Expense Tracker!</h4>
            <p>Create your first trip to start tracking expenses with friends</p>
        </div>

        <div id="trip-dashboard" style="display:none;">
            <div class="summary-cards row">
                <div class="col s6 m3">
                    <div class="card summary-card summary-card--budget">
                        <div class="card-content">
                            <span class="summary-card__title">Trip Budget</span>
                            <div class="summary-card__amount" id="trip-budget">$0.00</div>
                        </div>
                    </div>
                </div>
                <div class="col s6 m3">
                    <div class="card summary-card summary-card--spent">
                        <div class="card-content">
                            <span class="summary-card__title">Total Spent</span>
                            <div class="summary-card__amount" id="total-spent">$0.00</div>
                        </div>
                    </div>
                </div>
                <div class="col s6 m3">
                    <div class="card summary-card summary-card--remaining">
                        <div class="card-content">
                            <span class="summary-card__title">Remaining</span>
                            <div class="summary-card__amount" id="remaining-budget">$0.00</div>
                        </div>
                    </div>
                </div>
                <div class="col s6 m3">
                    <div class="card summary-card summary-card--share">
                        <div class="card-content">
                            <span class="summary-card__title">My Share</span>
                            <div class="summary-card__amount" id="my-share">$0.00</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-content row">
                <div class="col s12 l6">
                    <div class="card expense-form">
                        <div class="card-content">
                            <span class="card-title">
                                <i class="material-icons left">add_circle</i>Add Expense
                            </span>
                            <form id="expense-form" class="expense-form__form">
                                <div class="input-field">
                                    <select id="category" class="validate" required>
                                        <option value="">Select Category</option>
                                    </select>
                                    <label>Category</label>
                                </div>
                                <div class="input-field">
                                    <select id="subcategory" class="validate" required>
                                        <option value="">Select Subcategory</option>
                                    </select>
                                    <label>Subcategory</label>
                                </div>
                                <div class="input-field">
                                    <input type="number" id="amount" class="validate" step="0.01" required>
                                    <label for="amount">Amount</label>
                                </div>
                                <div class="input-field">
                                    <input type="text" id="description" class="validate" required>
                                    <label for="description">Description</label>
                                </div>
                                <div class="input-field">
                                    <input type="date" id="date" class="validate" required>
                                    <label for="date">Date</label>
                                </div>
                                <div class="expense-form__split">
                                    <p>
                                        <label>
                                            <input name="split" type="radio" value="equal" checked />
                                            <span>Split Equally</span>
                                        </label>
                                    </p>
                                    <p>
                                        <label>
                                            <input name="split" type="radio" value="custom" />
                                            <span>Custom Split</span>
                                        </label>
                                    </p>
                                </div>
                                <div id="custom-split-section" style="display:none;">
                                    <div id="member-splits"></div>
                                </div>
                                <button type="submit" class="btn waves-effect waves-light">
                                    <i class="material-icons left">add</i>Add Expense
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card trip-members">
                        <div class="card-content">
                            <span class="card-title">
                                <i class="material-icons left">group</i>Trip Members
                            </span>
                            <div id="members-list" class="trip-members__list"></div>
                            <div class="trip-members__invite">
                                <div class="input-field">
                                    <input type="email" id="invite-email" class="validate">
                                    <label for="invite-email">Email to invite</label>
                                </div>
                                <button id="invite-btn" class="btn-small waves-effect waves-light">
                                    <i class="material-icons left">person_add</i>Invite
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col s12 l6">
                    <div class="card expense-chart">
                        <div class="card-content">
                            <span class="card-title">
                                <i class="material-icons left">pie_chart</i>Expense Breakdown
                            </span>
                            <canvas id="expenseChart" class="expense-chart__canvas"></canvas>
                        </div>
                    </div>

                    <div class="card recent-expenses">
                        <div class="card-content">
                            <span class="card-title">
                                <i class="material-icons left">receipt</i>Recent Expenses
                            </span>
                            <div id="expenses-list" class="recent-expenses__list"></div>
                        </div>
                    </div>
                    
                    <div class="card trip-chat">
                        <div class="card-content">
                            <span class="card-title">
                                <i class="material-icons left">chat</i>Group Chat
                                <span class="online-indicator"></span>
                            </span>
                            <div class="trip-chat__online" id="online-members"></div>
                            <div id="chat-messages" class="trip-chat__messages"></div>
                            <div class="trip-chat__status">
                                <div id="typing-indicator" class="trip-chat__typing">
                                    <span id="typing-user"></span> is typing
                                    <span class="typing-dots">
                                        <span></span><span></span><span></span>
                                    </span>
                                </div>
                                <div id="online-status">Connected</div>
                            </div>
                            <div class="trip-chat__input">
                                <div class="input-field">
                                    <input type="text" id="chat-message" maxlength="500">
                                    <label for="chat-message">Type a message...</label>
                                </div>
                                <button id="send-message" class="btn-small waves-effect waves-light">
                                    <i class="material-icons">send</i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trip Modal -->
    <div id="trip-modal" class="modal">
        <div class="modal-content">
            <h4>Create New Trip</h4>
            <form id="trip-form" class="trip-modal__form">
                <div class="input-field">
                    <input type="text" id="trip-name" class="validate" required>
                    <label for="trip-name">Trip Name</label>
                </div>
                <div class="input-field">
                    <textarea id="trip-description" class="materialize-textarea"></textarea>
                    <label for="trip-description">Description</label>
                </div>
                <div class="input-field">
                    <input type="date" id="start-date" class="validate" required>
                    <label for="start-date">Start Date</label>
                </div>
                <div class="input-field">
                    <input type="date" id="end-date" class="validate" required>
                    <label for="end-date">End Date</label>
                </div>
                <div class="input-field">
                    <select id="currency" class="validate" required>
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
                    <label>Currency</label>
                </div>
                <div class="input-field">
                    <input type="number" id="budget" class="validate" step="0.01" required>
                    <label for="budget">Budget</label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="trip-form" class="btn waves-effect waves-light">
                <i class="material-icons left">add</i>Create Trip
            </button>
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cancel</a>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="js/trip-dashboard.js"></script>
</body>
</html>