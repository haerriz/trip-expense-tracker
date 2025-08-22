<?php
/**
 * Stress Tests - Test system under load
 */

class StressTests {
    private $baseUrl = 'http://localhost:8000';
    private $results = [];
    
    public function runAllTests() {
        echo "🔥 RUNNING STRESS TESTS\n";
        echo "======================\n\n";
        
        $this->testConcurrentRequests();
        $this->testDatabaseLoad();
        $this->testMemoryUsage();
        $this->testResponseTimes();
        
        return $this->printResults();
    }
    
    private function testConcurrentRequests() {
        echo "⚡ Testing Concurrent Requests...\n";
        
        $urls = ['/', '/dashboard', '/admin', '/profile'];
        $requests = 20;
        $startTime = microtime(true);
        
        $multiHandle = curl_multi_init();
        $curlHandles = [];
        
        // Create multiple curl handles
        for ($i = 0; $i < $requests; $i++) {
            $url = $urls[$i % count($urls)];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_multi_add_handle($multiHandle, $ch);
            $curlHandles[] = $ch;
        }
        
        // Execute all requests
        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);
        
        // Check results
        $successful = 0;
        foreach ($curlHandles as $ch) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode < 400) {
                $successful++;
            }
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }
        
        curl_multi_close($multiHandle);
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        if ($successful >= $requests * 0.9) { // 90% success rate
            $this->pass("Concurrent requests: $successful/$requests successful in " . round($duration, 2) . "s");
        } else {
            $this->fail("Concurrent requests: Only $successful/$requests successful");
        }
    }
    
    private function testDatabaseLoad() {
        echo "🗄️ Testing Database Load...\n";
        
        try {
            require_once '../config/database.php';
            global $pdo;
            
            $startTime = microtime(true);
            $queries = 50;
            
            for ($i = 0; $i < $queries; $i++) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $stmt->fetch();
            }
            
            $endTime = microtime(true);
            $duration = $endTime - $startTime;
            $avgTime = ($duration / $queries) * 1000; // ms per query
            
            if ($avgTime < 100) { // Less than 100ms per query
                $this->pass("Database load test: $queries queries in " . round($duration, 2) . "s (avg: " . round($avgTime, 2) . "ms)");
            } else {
                $this->fail("Database queries too slow: " . round($avgTime, 2) . "ms average");
            }
        } catch (Exception $e) {
            $this->fail("Database load test failed: " . $e->getMessage());
        }
    }
    
    private function testMemoryUsage() {
        echo "💾 Testing Memory Usage...\n";
        
        $startMemory = memory_get_usage(true);
        
        // Simulate heavy operations
        $data = [];
        for ($i = 0; $i < 1000; $i++) {
            $data[] = str_repeat('x', 1000);
        }
        
        $peakMemory = memory_get_peak_usage(true);
        $memoryUsed = $peakMemory - $startMemory;
        $memoryMB = $memoryUsed / 1024 / 1024;
        
        // Clean up
        unset($data);
        
        if ($memoryMB < 50) { // Less than 50MB
            $this->pass("Memory usage acceptable: " . round($memoryMB, 2) . "MB peak");
        } else {
            $this->fail("High memory usage: " . round($memoryMB, 2) . "MB peak");
        }
    }
    
    private function testResponseTimes() {
        echo "⏱️ Testing Response Times...\n";
        
        $pages = [
            '/' => 'Homepage',
            '/dashboard' => 'Dashboard',
            '/admin' => 'Admin',
            '/profile' => 'Profile'
        ];
        
        foreach ($pages as $url => $name) {
            $times = [];
            
            // Test 5 times for average
            for ($i = 0; $i < 5; $i++) {
                $startTime = microtime(true);
                $this->makeRequest($url);
                $endTime = microtime(true);
                $times[] = ($endTime - $startTime) * 1000; // Convert to ms
            }
            
            $avgTime = array_sum($times) / count($times);
            
            if ($avgTime < 1000) { // Less than 1 second
                $this->pass("$name response time: " . round($avgTime, 2) . "ms average");
            } else {
                $this->fail("$name too slow: " . round($avgTime, 2) . "ms average");
            }
        }
    }
    
    private function makeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
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
        echo "\n📋 STRESS TEST RESULTS\n";
        echo "=====================\n";
        
        $passed = count(array_filter($this->results, fn($r) => $r['status'] === 'PASS'));
        $failed = count(array_filter($this->results, fn($r) => $r['status'] === 'FAIL'));
        $total = count($this->results);
        
        echo "Total Tests: $total\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        echo "Success Rate: " . round(($passed / $total) * 100, 2) . "%\n\n";
        
        if ($failed > 0) {
            echo "❌ STRESS TESTS FAILED\n";
            return false;
        } else {
            echo "✅ ALL STRESS TESTS PASSED\n";
            return true;
        }
    }
}

// Check if server is running
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 0) {
    echo "❌ Server not running on localhost:8000\n";
    exit(1);
}

$tests = new StressTests();
$success = $tests->runAllTests();
exit($success ? 0 : 1);
?>