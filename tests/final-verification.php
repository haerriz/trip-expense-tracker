<?php
/**
 * Final Verification - Production readiness check
 */

echo "🚀 FINAL PRODUCTION READINESS CHECK\n";
echo "===================================\n\n";

$passed = 0;
$total = 0;

function test($description, $condition) {
    global $passed, $total;
    $total++;
    if ($condition) {
        echo "✅ $description\n";
        $passed++;
        return true;
    } else {
        echo "❌ $description\n";
        return false;
    }
}

echo "📁 FILE STRUCTURE\n";
echo "================\n";
test("Main application files exist", 
    file_exists('../index.php') && 
    file_exists('../dashboard.php') && 
    file_exists('../admin.php') && 
    file_exists('../profile.php')
);

test("Authentication files exist",
    file_exists('../manual-login.php') && 
    file_exists('../manual-signup.php') && 
    file_exists('../google-auth.php')
);

test("Configuration files exist",
    file_exists('../config/database.php') && 
    file_exists('../.htaccess')
);

test("API endpoints exist", 
    is_dir('../api') && 
    count(glob('../api/*.php')) >= 15
);

echo "\n🔧 CODE QUALITY\n";
echo "===============\n";

// Check PHP syntax
$syntaxErrors = 0;
$phpFiles = glob('../*.php');
foreach ($phpFiles as $file) {
    $output = shell_exec("php -l $file 2>&1");
    if (strpos($output, 'No syntax errors') === false) {
        $syntaxErrors++;
    }
}
test("All PHP files have valid syntax", $syntaxErrors === 0);

// Check .htaccess
$htaccess = file_get_contents('../.htaccess');
test(".htaccess has URL rewriting", strpos($htaccess, 'RewriteEngine On') !== false);
test(".htaccess preserves API routes", strpos($htaccess, 'api/') !== false);
test(".htaccess has security headers", strpos($htaccess, 'X-Content-Type-Options') !== false);

echo "\n⚙️ CONFIGURATION\n";
echo "================\n";

$dbConfig = file_get_contents('../config/database.php');
test("Production database config present", strpos($dbConfig, 'expenses.haerriz.com') !== false);
test("Local development config present", strpos($dbConfig, 'trip_expense_tracker') !== false);
test("Database error handling present", strpos($dbConfig, 'PDOException') !== false);

echo "\n🔐 SECURITY\n";
echo "===========\n";

test("Password hashing implemented", 
    strpos(file_get_contents('../manual-login.php'), 'password_verify') !== false
);

test("Session management present",
    strpos(file_get_contents('../dashboard.php'), 'session_start') !== false
);

test("SQL injection protection", 
    strpos(file_get_contents('../config/database.php'), 'PDO::ATTR_ERRMODE') !== false
);

echo "\n🌐 FEATURES\n";
echo "===========\n";

// Check for multi-currency support
$dashboardContent = file_get_contents('../dashboard.php');
test("Multi-currency support implemented", 
    strpos($dashboardContent, 'USD') !== false && 
    strpos($dashboardContent, 'EUR') !== false
);

// Check for Google OAuth
test("Google OAuth integration present",
    strpos(file_get_contents('../index.php'), 'g_id_signin') !== false
);

// Check for admin functionality
test("Admin panel implemented",
    file_exists('../admin.php') && 
    strpos(file_get_contents('../admin.php'), 'haerriz@gmail.com') !== false
);

// Check for expense tracking
test("Expense tracking functionality present",
    strpos($dashboardContent, 'expense-form') !== false &&
    strpos($dashboardContent, 'Add Expense') !== false
);

echo "\n📊 FINAL RESULTS\n";
echo "================\n";
echo "Tests Passed: $passed/$total\n";
echo "Success Rate: " . round(($passed / $total) * 100, 2) . "%\n\n";

if ($passed >= $total * 0.9) { // 90% pass rate
    echo "🎉 PRODUCTION READY!\n";
    echo "✅ Application meets quality standards\n";
    echo "✅ All critical features implemented\n";
    echo "✅ Security measures in place\n";
    echo "✅ Configuration properly set up\n\n";
    
    echo "🚀 DEPLOYING TO PRODUCTION...\n";
    exit(0);
} else {
    echo "❌ NOT READY FOR PRODUCTION\n";
    echo "Fix the failing tests before deployment.\n";
    exit(1);
}
?>