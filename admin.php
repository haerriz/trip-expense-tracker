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
    <title>Master Admin - Haerriz Trip Finance</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar red">
        <div class="nav-wrapper">
            <a href="#" class="brand-logo">
                <i class="material-icons">admin_panel_settings</i>
                Master Admin
            </a>
            <ul class="right">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
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
            <div class="row">
                <div class="col s12 m3">
                    <div class="card-panel center">
                        <h4 id="total-users">0</h4>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="col s12 m3">
                    <div class="card-panel center">
                        <h4 id="total-trips">0</h4>
                        <p>Total Trips</p>
                    </div>
                </div>
                <div class="col s12 m3">
                    <div class="card-panel center">
                        <h4 id="total-expenses">$0</h4>
                        <p>Total Expenses</p>
                    </div>
                </div>
                <div class="col s12 m3">
                    <div class="card-panel center">
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
                            <canvas id="categoryChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">User Registration Trend</span>
                            <canvas id="userChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>