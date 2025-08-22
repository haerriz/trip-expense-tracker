<?php
/**
 * Simple Tests - Basic functionality verification
 */

class SimpleTests {
    private $results = [];
    
    public function runAllTests() {
        echo "🧪 RUNNING SIMPLE VERIFICATION TESTS\n";
        echo "===================================\n\n";
        
        $this->testFileStructure();
        $this->testDatabaseConnection();
        $this->testPHPSyntax();
        $this->testConfigFiles();
        
        return $this->printResults();
    }
    
    private function testFileStructure() {
        echo "📁 Testing File Structure...\n";
        
        $requiredFiles = [
            '../index.php' => 'Main index file',
            '../dashboard.php' => 'Dashboard page',
            '../admin.php' => 'Admin page',
            '../profile.php' => 'Profile page',
            '../logout.php' => 'Logout script',
            '../config/database.php' => 'Database config',
            '../.htaccess' => 'URL rewriting rules'
        ];
        
        foreach ($requiredFiles as $file => $description) {
            if (file_exists($file)) {
                $this->pass("$description exists");
            } else {
                $this->fail("$description missing");
            }
        }
        
        // Check API directory
        if (is_dir('../api') && count(glob('../api/*.php')) > 10) {
            $this->pass("API directory with endpoints exists");
        } else {
            $this->fail("API directory incomplete");
        }
    }
    
    private function testDatabaseConnection() {
        echo "🗄️ Testing Database Connection...\n";
        
        try {
            require_once '../config/database.php';
            global $pdo;
            
            if ($pdo instanceof PDO) {
                $this->pass("Database connection established");
                
                // Test basic query
                $stmt = $pdo->query("SELECT 1");
                if ($stmt) {
                    $this->pass("Database queries working");
                } else {
                    $this->fail("Database queries not working");
                }
                
                // Check required tables
                $tables = ['users', 'trips', 'expenses', 'categories'];
                foreach ($tables as $table) {
                    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() > 0) {
                        $this->pass("Table '$table' exists");
                    } else {
                        $this->fail("Table '$table' missing");
                    }
                }
            } else {
                $this->fail("Database connection failed");
            }
        } catch (Exception $e) {
            $this->fail("Database error: " . $e->getMessage());
        }
    }
    
    private function testPHPSyntax() {
        echo "🔍 Testing PHP Syntax...\n";
        
        $phpFiles = [
            '../index.php',
            '../dashboard.php', 
            '../admin.php',
            '../profile.php',
            '../config/database.php'
        ];
        
        foreach ($phpFiles as $file) {
            if (file_exists($file)) {
                $output = shell_exec("php -l $file 2>&1");
                if (strpos($output, 'No syntax errors') !== false) {
                    $this->pass("PHP syntax valid: " . basename($file));
                } else {
                    $this->fail("PHP syntax error in " . basename($file));
                }
            }
        }
    }
    
    private function testConfigFiles() {
        echo "⚙️ Testing Configuration...\n";
        
        // Test .htaccess content
        if (file_exists('../.htaccess')) {
            $htaccess = file_get_contents('../.htaccess');
            if (strpos($htaccess, 'RewriteEngine On') !== false) {
                $this->pass(".htaccess has URL rewriting enabled");
            } else {
                $this->fail(".htaccess missing URL rewriting");
            }
            
            if (strpos($htaccess, 'api/') !== false) {
                $this->pass(".htaccess preserves API routes");
            } else {
                $this->fail(".htaccess doesn't preserve API routes");
            }
        }
        
        // Test database config
        $dbConfig = file_get_contents('../config/database.php');
        if (strpos($dbConfig, 'expenses.haerriz.com') !== false) {
            $this->pass("Production database config present");
        } else {
            $this->fail("Production database config missing");
        }
        
        if (strpos($dbConfig, 'trip_expense_tracker') !== false) {
            $this->pass("Local database config present");
        } else {
            $this->fail("Local database config missing");
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
        echo "\n📋 VERIFICATION RESULTS\n";
        echo "======================\n";
        
        $passed = count(array_filter($this->results, fn($r) => $r['status'] === 'PASS'));
        $failed = count(array_filter($this->results, fn($r) => $r['status'] === 'FAIL'));
        $total = count($this->results);
        
        echo "Total Tests: $total\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        echo "Success Rate: " . round(($passed / $total) * 100, 2) . "%\n\n";
        
        if ($failed > 0) {
            echo "❌ VERIFICATION FAILED\n";
            return false;
        } else {
            echo "✅ ALL VERIFICATIONS PASSED\n";
            return true;
        }
    }
}

$tests = new SimpleTests();
$success = $tests->runAllTests();
exit($success ? 0 : 1);
?>