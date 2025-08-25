<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Dashboard - Manage Expenses & Budget | Haerriz Trip Finance</title>
    <meta name="description" content="Manage your trip expenses, create budgets, split costs with friends, and track spending with real-time analytics. Add expenses, invite members, and monitor your travel budget.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#2196F3">
    <link rel="canonical" href="https://expenses.haerriz.com/dashboard.php">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Trip Dashboard - Haerriz Trip Finance">
    <meta property="og:description" content="Manage your trip expenses and budgets with real-time analytics and group collaboration.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://expenses.haerriz.com/dashboard.php">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Trip Dashboard",
      "description": "Manage trip expenses, budgets, and group spending with real-time analytics.",
      "url": "https://expenses.haerriz.com/dashboard.php",
      "isPartOf": {
        "@type": "WebSite",
        "name": "Haerriz Trip Finance",
        "url": "https://expenses.haerriz.com"
      }
    }
    </script>
    
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Pure Materialize Design - No custom CSS -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/avatar.js"></script>
    <script>
        window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
        window.userEmail = '<?php echo $_SESSION['user_email']; ?>';
    </script>
</head>
<body class="grey lighten-4">
    <nav class="blue darken-1">
        <div class="nav-wrapper">
            <a href="#" class="brand-logo">
                <i class="material-icons left">flight_takeoff</i>Trip Finance
            </a>
            <ul class="right">
                <li><span class="white-text hide-on-small-only"><?php echo $_SESSION['user_name']; ?></span></li>
                <?php if ($_SESSION['user_email'] === 'haerriz@gmail.com'): ?>
                    <li><a href="admin.php" class="btn red darken-1 waves-effect waves-light">Admin</a></li>
                <?php endif; ?>
                <li><a href="profile.php" class="btn blue waves-effect waves-light">Profile</a></li>
                <li><a href="logout.php" class="btn red waves-effect waves-light">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 20px;">
        <div class="row">
            <div class="col s12 m4">
                <div class="input-field">
                    <select id="current-trip">
                        <option value="">Select a Trip</option>
                    </select>
                    <label>Choose Trip</label>
                </div>
            </div>
            <div class="col s12 m8">
                <div class="right-align">
                    <a id="new-trip-btn" class="btn waves-effect waves-light blue">
                        <i class="material-icons left">add</i>New Trip
                    </a>
                    <a id="invitations-btn" class="btn waves-effect waves-light orange">
                        <i class="material-icons left">mail</i>Invitations <span id="invitation-count" class="new badge" data-badge-caption="">0</span>
                    </a>
                    <a id="export-pdf" class="btn-small waves-effect waves-light grey">
                        <i class="material-icons left">picture_as_pdf</i>PDF
                    </a>
                    <a id="export-excel" class="btn-small waves-effect waves-light grey">
                        <i class="material-icons left">table_chart</i>CSV
                    </a>
                    <a id="export-xlsx" class="btn-small waves-effect waves-light grey">
                        <i class="material-icons left">description</i>XLSX
                    </a>
                    <a id="email-report" class="btn-small waves-effect waves-light grey">
                        <i class="material-icons left">email</i>Email
                    </a>
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

        <div id="no-trip" class="center-align" style="margin-top: 50px;">
            <i class="material-icons large grey-text">flight_takeoff</i>
            <h4 class="grey-text">Welcome to Trip Expense Tracker!</h4>
            <p class="grey-text">Create your first trip to start tracking expenses with friends</p>
        </div>

        <div id="trip-dashboard" style="display:none;">
            <div class="row">
                <div class="col s6 m3">
                    <div class="card blue lighten-4 hoverable" onclick="editBudget()" style="cursor: pointer;">
                        <div class="card-content center-align">
                            <i class="material-icons blue-text">account_balance_wallet</i>
                            <span class="card-title">Trip Budget</span>
                            <h5 id="trip-budget" class="blue-text">$0.00</h5>
                            <small class="grey-text">Click to edit</small>
                        </div>
                    </div>
                </div>
                <div class="col s6 m3">
                    <div class="card red lighten-4">
                        <div class="card-content center-align">
                            <i class="material-icons red-text">trending_up</i>
                            <span class="card-title">Total Spent</span>
                            <h5 id="total-spent" class="red-text">$0.00</h5>
                        </div>
                    </div>
                </div>
                <div class="col s6 m3">
                    <div class="card green lighten-4">
                        <div class="card-content center-align">
                            <i class="material-icons green-text">savings</i>
                            <span class="card-title">Remaining</span>
                            <h5 id="remaining-budget" class="green-text">$0.00</h5>
                        </div>
                    </div>
                </div>
                <div class="col s6 m3">
                    <div class="card orange lighten-4">
                        <div class="card-content center-align">
                            <i class="material-icons orange-text">person</i>
                            <span class="card-title">My Share</span>
                            <h5 id="my-share" class="orange-text">$0.00</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col s12 l6">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">
                                <i class="material-icons left">add_circle</i>Add Expense
                            </span>
                            <form id="expense-form">
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
                                            <input name="split" type="radio" value="full" />
                                            <span>Full Expense on Me</span>
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
                                    <div class="split-mode-toggle">
                                        <p>
                                            <label>
                                                <input name="split-mode" type="radio" value="currency" checked />
                                                <span>Currency Amount</span>
                                            </label>
                                        </p>
                                        <p>
                                            <label>
                                                <input name="split-mode" type="radio" value="percentage" />
                                                <span>Percentage</span>
                                            </label>
                                        </p>
                                    </div>
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
                                <div class="chat-features">
                                    <button class="chat-feature-btn" id="emoji-btn">
                                        <i class="material-icons tiny">sentiment_satisfied</i> Emoji
                                    </button>
                                    <button class="chat-feature-btn" id="clear-chat-btn">
                                        <i class="material-icons tiny">clear_all</i> Clear
                                    </button>
                                </div>
                            </span>
                            <div class="trip-chat__online" id="online-members"></div>
                            <div class="trip-chat__status">
                                <div id="typing-indicator" class="trip-chat__typing">
                                    <span id="typing-user"></span> is typing
                                    <span class="typing-dots">
                                        <span></span><span></span><span></span>
                                    </span>
                                </div>
                                <div id="online-status">Connected</div>
                            </div>
                            <div id="chat-messages" class="trip-chat__messages" style="position: relative;">
                                <button id="scroll-to-bottom" class="btn chat-scroll-to-bottom">
                                    <i class="material-icons">keyboard_arrow_down</i>
                                </button>
                            </div>
                            <div class="trip-chat__input">
                                <button class="btn-flat chat-attachment-btn" id="attachment-btn" title="Attach file">
                                    <i class="material-icons">attach_file</i>
                                </button>
                                <div class="input-field">
                                    <input type="text" id="chat-message" maxlength="500" placeholder="Type a message...">
                                    <label for="chat-message"></label>
                                </div>
                                <button id="send-message" class="btn chat-send-btn" disabled>
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
                <div class="budget-creation-options">
                    <p>
                        <label>
                            <input name="create-budget-type" type="radio" value="with-budget" checked />
                            <span>Set Budget</span>
                        </label>
                    </p>
                    <p>
                        <label>
                            <input name="create-budget-type" type="radio" value="no-budget" />
                            <span>No Budget</span>
                        </label>
                    </p>
                </div>
                <div class="input-field" id="create-budget-field">
                    <input type="number" id="budget" class="validate" step="0.01" min="0">
                    <label for="budget">Budget Amount</label>
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
    
    <!-- Edit Expense Modal -->
    <div id="edit-expense-modal" class="modal">
        <div class="modal-content">
            <h4>Edit Expense</h4>
            <form id="edit-expense-form">
                <input type="hidden" id="edit-expense-id">
                <div class="input-field">
                    <select id="edit-category" required>
                        <option value="">Select Category</option>
                    </select>
                    <label>Category</label>
                </div>
                <div class="input-field">
                    <select id="edit-subcategory" required>
                        <option value="">Select Subcategory</option>
                    </select>
                    <label>Subcategory</label>
                </div>
                <div class="input-field">
                    <input type="number" id="edit-amount" step="0.01" required>
                    <label for="edit-amount">Amount</label>
                </div>
                <div class="input-field">
                    <input type="text" id="edit-description" required>
                    <label for="edit-description">Description</label>
                </div>
                <div class="input-field">
                    <input type="date" id="edit-date" required>
                    <label for="edit-date">Date</label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="edit-expense-form" class="btn waves-effect waves-light">
                <i class="material-icons left">save</i>Update Expense
            </button>
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cancel</a>
        </div>
    </div>
    
    <!-- Edit Budget Modal -->
    <div id="edit-budget-modal" class="modal">
        <div class="modal-content">
            <h4>Edit Trip Budget</h4>
            <form id="edit-budget-form">
                <div class="budget-options">
                    <p>
                        <label>
                            <input name="budget-type" type="radio" value="with-budget" checked />
                            <span>Set Budget Amount</span>
                        </label>
                    </p>
                    <p>
                        <label>
                            <input name="budget-type" type="radio" value="no-budget" />
                            <span>No Budget (Unlimited)</span>
                        </label>
                    </p>
                </div>
                <div class="input-field" id="budget-amount-field">
                    <input type="number" id="edit-budget-amount" step="0.01" min="0">
                    <label for="edit-budget-amount">Budget Amount</label>
                </div>
                <div id="no-budget-info" style="display:none;">
                    <div class="card-panel orange lighten-4">
                        <i class="material-icons left">info</i>
                        <span>No budget mode allows unlimited expenses without budget tracking.</span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="edit-budget-form" class="btn waves-effect waves-light">
                <i class="material-icons left">save</i>Update Budget
            </button>
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cancel</a>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        // Initialize Materialize components
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all select dropdowns
            M.FormSelect.init(document.querySelectorAll('select'));
            
            // Initialize all modals
            M.Modal.init(document.querySelectorAll('.modal'));
            
            // Initialize all tooltips
            M.Tooltip.init(document.querySelectorAll('[data-tooltip]'));
            
            // Initialize all textareas
            M.textareaAutoResize(document.querySelectorAll('textarea'));
            
            // Initialize date picker
            M.Datepicker.init(document.querySelectorAll('.datepicker'));
            
            // Update labels for pre-filled inputs
            M.updateTextFields();
        });
    </script>
    <script src="js/trip-dashboard.js"></script>
    <script src="js/enhanced-chat.js"></script>
</body>
</html>