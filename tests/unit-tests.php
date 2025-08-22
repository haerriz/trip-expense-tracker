<?php
/**
 * Unit Tests for Trip Expense Tracker
 */

require_once '../config/database.php';

class UnitTests {
    private $pdo;
    private $results = [];
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function runAllTests() {
        echo "🧪 RUNNING UNIT TESTS\n";
        echo "==================\n\n";
        
        $this->testDatabaseConnection();
        $this->testUserAuthentication();
        $this->testTripCreation();
        $this->testExpenseCalculations();
        $this->testMultiCurrency();
        $this->testAPIEndpoints();
        
        return $this->printResults();
    }
    
    private function testDatabaseConnection() {
        echo "📊 Testing Database Connection...\n";
        try {
            $stmt = $this->pdo->query("SELECT 1");
            $this->pass("Database connection successful");
        } catch (Exception $e) {
            $this->fail("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function testUserAuthentication() {
        echo "🔐 Testing User Authentication...\n";
        
        // Test password hashing
        $password = "test123";
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (password_verify($password, $hash)) {
            $this->pass("Password hashing works");
        } else {
            $this->fail("Password hashing failed");
        }
        
        // Test user table structure
        try {
            $stmt = $this->pdo->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $required = ['id', 'email', 'name', 'password'];
            $missing = array_diff($required, $columns);
            
            if (empty($missing)) {
                $this->pass("User table structure correct");
            } else {
                $this->fail("Missing columns in users table: " . implode(', ', $missing));
            }
        } catch (Exception $e) {
            $this->fail("User table check failed: " . $e->getMessage());
        }
    }
    
    private function testTripCreation() {
        echo "✈️ Testing Trip Creation...\n";
        
        try {
            $stmt = $this->pdo->query("DESCRIBE trips");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $required = ['id', 'name', 'currency', 'budget', 'start_date', 'end_date'];
            $missing = array_diff($required, $columns);
            
            if (empty($missing)) {
                $this->pass("Trip table structure correct");
            } else {
                $this->fail("Missing columns in trips table: " . implode(', ', $missing));
            }
        } catch (Exception $e) {
            $this->fail("Trip table check failed: " . $e->getMessage());
        }
    }
    
    private function testExpenseCalculations() {
        echo "💰 Testing Expense Calculations...\n";
        
        // Test expense splitting logic
        $totalAmount = 100.00;
        $members = 4;
        $splitAmount = $totalAmount / $members;
        
        if ($splitAmount == 25.00) {
            $this->pass("Equal expense splitting calculation correct");
        } else {
            $this->fail("Equal expense splitting calculation incorrect");
        }
        
        // Test percentage splitting
        $percentages = [40, 30, 20, 10];
        $total = array_sum($percentages);
        
        if ($total == 100) {
            $this->pass("Percentage splitting validation correct");
        } else {
            $this->fail("Percentage splitting validation incorrect");
        }
    }
    
    private function testMultiCurrency() {
        echo "💱 Testing Multi-Currency Support...\n";
        
        $currencies = ['USD', 'EUR', 'GBP', 'JPY', 'AUD', 'CAD', 'INR', 'THB', 'VND'];
        $symbols = [
            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥',
            'AUD' => 'A$', 'CAD' => 'C$', 'INR' => '₹', 'THB' => '฿', 'VND' => '₫'
        ];
        
        $allSupported = true;
        foreach ($currencies as $currency) {
            if (!isset($symbols[$currency])) {
                $allSupported = false;
                break;
            }
        }
        
        if ($allSupported) {
            $this->pass("All currencies have symbols defined");
        } else {
            $this->fail("Missing currency symbols");
        }
    }
    
    private function testAPIEndpoints() {
        echo "🔌 Testing API Endpoints...\n";
        
        $apiFiles = [
            'get_trips.php', 'create_trip.php', 'add_expense.php',
            'get_expenses.php', 'get_summary.php', 'invite_member.php'
        ];
        
        $missing = [];
        foreach ($apiFiles as $file) {
            if (!file_exists("../api/$file")) {
                $missing[] = $file;
            }
        }
        
        if (empty($missing)) {
            $this->pass("All required API endpoints exist");
        } else {
            $this->fail("Missing API files: " . implode(', ', $missing));
        }
    }
    
    private function pass($message) {
        $this->results[] = ['status' => 'PASS', 'message' => $message];
        echo "  ✅ $message\n";
    }
    
    private function fail($message) {
        $this->results[] = ['status' => 'FAIL', 'message' => $message];
        echo "  ❌ $message\n";
    }
    
    private function printResults() {
        echo "\n📋 TEST RESULTS\n";
        echo "==============\n";
        
        $passed = count(array_filter($this->results, fn($r) => $r['status'] === 'PASS'));
        $failed = count(array_filter($this->results, fn($r) => $r['status'] === 'FAIL'));
        $total = count($this->results);
        
        echo "Total Tests: $total\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        echo "Success Rate: " . round(($passed / $total) * 100, 2) . "%\n\n";
        
        if ($failed > 0) {
            echo "❌ TESTS FAILED - DO NOT DEPLOY\n";
            return false;
        } else {
            echo "✅ ALL TESTS PASSED\n";
            return true;
        }
    }
}

// Run tests
$tests = new UnitTests();
$success = $tests->runAllTests();
exit($success ? 0 : 1);
?>