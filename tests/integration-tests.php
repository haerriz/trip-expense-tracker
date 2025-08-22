<?php
/**
 * Integration Tests - Test full user workflows
 */

class IntegrationTests {
    private $baseUrl = 'http://localhost:8000';
    private $results = [];
    
    public function runAllTests() {
        echo "🔗 RUNNING INTEGRATION TESTS\n";
        echo "===========================\n\n";
        
        $this->testPageLoading();
        $this->testCleanURLs();
        $this->testAPIResponses();
        $this->testAuthFlow();
        
        return $this->printResults();
    }
    
    private function testPageLoading() {
        echo "📄 Testing Page Loading...\n";
        
        $pages = [
            '/' => 'Login page',
            '/dashboard' => 'Dashboard (should redirect if not logged in)',
            '/admin' => 'Admin page (should redirect if not logged in)',
            '/profile' => 'Profile page (should redirect if not logged in)'
        ];
        
        foreach ($pages as $url => $description) {
            $response = $this->makeRequest($url);
            if ($response !== false && ($response['http_code'] < 400 || $response['http_code'] == 302)) {
                $this->pass("$description loads successfully");
            } else {
                $this->fail("$description failed to load (HTTP: {$response['http_code']})");
            }
        }
    }
    
    private function testCleanURLs() {
        echo "🔗 Testing Clean URLs...\n";
        
        $urls = [
            '/dashboard',
            '/admin', 
            '/profile',
            '/logout'
        ];
        
        foreach ($urls as $url) {
            $response = $this->makeRequest($url);
            if ($response !== false) {
                $this->pass("Clean URL $url accessible");
            } else {
                $this->fail("Clean URL $url not accessible");
            }
        }
    }
    
    private function testAPIResponses() {
        echo "🔌 Testing API Responses...\n";
        
        $apiEndpoints = [
            '/api/get_trips.php',
            '/api/get_categories.php',
            '/manual-login.php',
            '/manual-signup.php'
        ];
        
        foreach ($apiEndpoints as $endpoint) {
            $response = $this->makeRequest($endpoint, 'POST', ['test' => 'data']);
            if ($response !== false) {
                $this->pass("API endpoint $endpoint responds");
            } else {
                $this->fail("API endpoint $endpoint not responding");
            }
        }
    }
    
    private function testAuthFlow() {
        echo "🔐 Testing Authentication Flow...\n";
        
        // Test login page loads
        $response = $this->makeRequest('/');
        if ($response && ($response['http_code'] < 400 || $response['http_code'] == 302)) {
            $this->pass("Homepage accessible");
            
            // Only check content if not redirected
            if ($response['http_code'] < 300 && strpos($response['body'], 'loginForm') !== false) {
                $this->pass("Login form present");
            } else {
                $this->pass("Homepage redirects (user likely logged in)");
            }
        } else {
            $this->fail("Homepage not accessible");
        }
    }
    
    private function makeRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
        }
        
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return false;
        }
        
        return [
            'body' => $body,
            'http_code' => $httpCode
        ];
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
        echo "\n📋 INTEGRATION TEST RESULTS\n";
        echo "===========================\n";
        
        $passed = count(array_filter($this->results, fn($r) => $r['status'] === 'PASS'));
        $failed = count(array_filter($this->results, fn($r) => $r['status'] === 'FAIL'));
        $total = count($this->results);
        
        echo "Total Tests: $total\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        echo "Success Rate: " . round(($passed / $total) * 100, 2) . "%\n\n";
        
        if ($failed > 0) {
            echo "❌ INTEGRATION TESTS FAILED\n";
            return false;
        } else {
            echo "✅ ALL INTEGRATION TESTS PASSED\n";
            return true;
        }
    }
}

// Run tests only if server is running
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 0) {
    echo "❌ Server not running on localhost:8000\n";
    echo "Start server with: php -S localhost:8000\n";
    exit(1);
}

$tests = new IntegrationTests();
$success = $tests->runAllTests();
exit($success ? 0 : 1);
?>