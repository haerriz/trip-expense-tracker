<?php
require_once 'includes/auth.php';
require_once 'includes/admin.php';
requireLogin();
requireMasterAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Manage Categories & Users | Haerriz Trip Finance</title>
    <meta name="description" content="Master admin panel for managing expense categories, users, trips, and system statistics. Administrative control for Haerriz Trip Finance.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#f44336">
    <link rel="canonical" href="https://expenses.haerriz.com/admin.php">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Admin Panel",
      "description": "Administrative control panel for managing categories, users, and system statistics.",
      "url": "https://expenses.haerriz.com/admin.php",
      "isPartOf": {
        "@type": "WebSite",
        "name": "Haerriz Trip Finance",
        "url": "https://expenses.haerriz.com"
      }
    }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon.svg">
    <link rel="manifest" href="/manifest.json">
    
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0CMW9MRBRE"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-0CMW9MRBRE');
    </script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="grey lighten-4">
    <nav class="red darken-1">
        <div class="nav-wrapper">
            <a href="#" class="brand-logo">
                <i class="material-icons left">admin_panel_settings</i>Master Admin
            </a>
            <ul class="right">
                <li style="display: flex; align-items: center; gap: 8px; padding: 0 15px;">
                    <img src="<?php echo $_SESSION['user_picture'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name']) . '&size=32&background=D32F2F&color=fff'; ?>" 
                         alt="Profile" class="circle" style="width: 32px; height: 32px;">
                    <span class="white-text hide-on-small-only"><?php echo $_SESSION['user_name']; ?></span>
                </li>
                <li><a href="dashboard.php" class="btn blue waves-effect waves-light">Dashboard</a></li>
                <li><a href="logout.php" class="btn red darken-2 waves-effect waves-light">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col s12">
                <ul class="tabs">
                    <li class="tab col s3"><a href="#categories" class="active">Categories</a></li>
                    <li class="tab col s3"><a href="#users">Users</a></li>
                    <li class="tab col s3"><a href="#trips">All Trips</a></li>
                    <li class="tab col s3"><a href="#stats">Statistics</a></li>
                </ul>
            </div>
        </div>

        <!-- Categories Tab -->
        <div id="categories" class="col s12">
            <div class="row">
                <div class="col s12 m6">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">Add Category</span>
                            <form id="category-form">
                                <div class="input-field">
                                    <input type="text" id="category-name" required>
                                    <label for="category-name">Category Name</label>
                                </div>
                                <div class="input-field">
                                    <textarea id="subcategories" class="materialize-textarea" placeholder="Enter subcategories separated by commas"></textarea>
                                    <label for="subcategories">Subcategories</label>
                                </div>
                                <button type="submit" class="btn">Add Category</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">Existing Categories</span>
                            <div id="categories-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div id="users" class="col s12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">All Users</span>
                    <div id="users-list"></div>
                </div>
            </div>
        </div>

        <!-- Trips Tab -->
        <div id="trips" class="col s12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">All Trips</span>
                    <div id="trips-list"></div>
                </div>
            </div>
        </div>

        <!-- Statistics Tab -->
        <div id="stats" class="col s12">
            <div class="admin-stats-row">
                <div class="admin-stats-col">
                    <div class="admin-stats-panel">
                        <h4 id="total-users">0</h4>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="admin-stats-col">
                    <div class="admin-stats-panel">
                        <h4 id="total-trips">0</h4>
                        <p>Total Trips</p>
                    </div>
                </div>
                <div class="admin-stats-col">
                    <div class="admin-stats-panel">
                        <h4 id="total-expenses">$0</h4>
                        <p>Total Expenses</p>
                    </div>
                </div>
                <div class="admin-stats-col">
                    <div class="admin-stats-panel">
                        <h4 id="active-trips">0</h4>
                        <p>Active Trips</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col s12 m6">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">Expenses by Category</span>
                            <div class="admin-chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">User Registration Trend</span>
                            <div class="admin-chart-container">
                                <canvas id="userChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="edit-category-modal" class="modal">
        <div class="modal-content">
            <h4>Edit Category</h4>
            <form id="edit-category-form">
                <input type="hidden" id="edit-category-id">
                <div class="input-field">
                    <input type="text" id="edit-category-name" required>
                    <label for="edit-category-name">Category Name</label>
                </div>
                <div class="input-field">
                    <textarea id="edit-subcategories" class="materialize-textarea" placeholder="Enter subcategories separated by commas"></textarea>
                    <label for="edit-subcategories">Subcategories</label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="edit-category-form" class="btn waves-effect waves-light">
                <i class="material-icons left">save</i>Update Category
            </button>
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cancel</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>