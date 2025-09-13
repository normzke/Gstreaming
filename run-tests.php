<?php
/**
 * Master Test Runner
 * Executes all test suites for the GStreaming platform
 */

echo "=== GStreaming Master Test Runner ===\n";
echo "Starting comprehensive system tests...\n\n";

$testResults = [];
$startTime = microtime(true);

// Test 1: Database Migration
echo "1. Running Database Migrations...\n";
$migrationStart = microtime(true);
$migrationOutput = shell_exec('php database/run-migrations.php run 2>&1');
$migrationTime = microtime(true) - $migrationStart;

if (strpos($migrationOutput, 'All migrations completed successfully') !== false) {
    echo "✅ Database migrations completed successfully\n";
    $testResults['migrations'] = 'PASS';
} else {
    echo "❌ Database migrations failed\n";
    echo "Output: $migrationOutput\n";
    $testResults['migrations'] = 'FAIL';
}

// Test 2: Core System Tests
echo "\n2. Running Core System Tests...\n";
$systemStart = microtime(true);
$systemOutput = shell_exec('php tests/test-suite.php 2>&1');
$systemTime = microtime(true) - $systemStart;

if (strpos($systemOutput, 'All tests passed!') !== false) {
    echo "✅ Core system tests passed\n";
    $testResults['system'] = 'PASS';
} else if (strpos($systemOutput, 'Some tests failed') !== false) {
    echo "⚠️  Core system tests completed with warnings\n";
    $testResults['system'] = 'WARN';
} else {
    echo "❌ Core system tests failed\n";
    echo "Output: $systemOutput\n";
    $testResults['system'] = 'FAIL';
}

// Test 3: API Tests
echo "\n3. Running API Tests...\n";
$apiStart = microtime(true);
$apiOutput = shell_exec('php tests/api-tests.php http://localhost:4000/GStreaming 2>&1');
$apiTime = microtime(true) - $apiStart;

if (strpos($apiOutput, 'All API tests passed!') !== false) {
    echo "✅ API tests passed\n";
    $testResults['api'] = 'PASS';
} else if (strpos($apiOutput, 'Some API tests failed') !== false) {
    echo "⚠️  API tests completed with warnings\n";
    $testResults['api'] = 'WARN';
} else {
    echo "❌ API tests failed\n";
    echo "Output: $apiOutput\n";
    $testResults['api'] = 'FAIL';
}

// Test 4: Deployment Tests
echo "\n4. Running Deployment Tests...\n";
$deploymentStart = microtime(true);
$deploymentOutput = shell_exec('php tests/deployment-test.php 2>&1');
$deploymentTime = microtime(true) - $deploymentStart;

if (strpos($deploymentOutput, 'System is ready for deployment!') !== false) {
    echo "✅ Deployment tests passed - system ready\n";
    $testResults['deployment'] = 'PASS';
} else if (strpos($deploymentOutput, 'System has minor issues but may be deployable') !== false) {
    echo "⚠️  Deployment tests completed with warnings\n";
    $testResults['deployment'] = 'WARN';
} else {
    echo "❌ Deployment tests failed\n";
    echo "Output: $deploymentOutput\n";
    $testResults['deployment'] = 'FAIL';
}

// Calculate total time
$totalTime = microtime(true) - $startTime;

// Display final results
echo "\n=== FINAL TEST RESULTS ===\n";
echo "Total execution time: " . round($totalTime, 2) . " seconds\n\n";

$passCount = 0;
$warnCount = 0;
$failCount = 0;

foreach ($testResults as $test => $result) {
    $status = match($result) {
        'PASS' => '✅ PASS',
        'WARN' => '⚠️  WARN',
        'FAIL' => '❌ FAIL',
        default => '❓ UNKNOWN'
    };
    
    echo "$test: $status\n";
    
    switch ($result) {
        case 'PASS':
            $passCount++;
            break;
        case 'WARN':
            $warnCount++;
            break;
        case 'FAIL':
            $failCount++;
            break;
    }
}

echo "\n=== SUMMARY ===\n";
echo "✅ Passed: $passCount\n";
echo "⚠️  Warnings: $warnCount\n";
echo "❌ Failed: $failCount\n";
echo "Total Tests: " . count($testResults) . "\n";

// Overall status
if ($failCount === 0 && $warnCount === 0) {
    echo "\n🎉 ALL TESTS PASSED! System is ready for production deployment.\n";
    $overallStatus = 'READY';
} else if ($failCount === 0) {
    echo "\n⚠️  Tests completed with warnings. Review issues before deployment.\n";
    $overallStatus = 'WARNING';
} else {
    echo "\n🚨 TESTS FAILED! System is not ready for deployment.\n";
    $overallStatus = 'NOT_READY';
}

// Generate comprehensive report
$reportContent = generateTestReport($testResults, $totalTime, $overallStatus, [
    'migrations' => $migrationOutput,
    'system' => $systemOutput,
    'api' => $apiOutput,
    'deployment' => $deploymentOutput
]);

$reportFile = 'test-report-' . date('Y-m-d-H-i-s') . '.txt';
file_put_contents($reportFile, $reportContent);
echo "\n📄 Comprehensive test report saved to: $reportFile\n";

// Exit with appropriate code
exit($failCount > 0 ? 1 : 0);

function generateTestReport($testResults, $totalTime, $overallStatus, $outputs) {
    $report = "GStreaming Comprehensive Test Report\n";
    $report .= "Generated: " . date('Y-m-d H:i:s') . "\n";
    $report .= "========================================\n\n";
    
    $report .= "OVERALL STATUS: $overallStatus\n";
    $report .= "Total Execution Time: " . round($totalTime, 2) . " seconds\n\n";
    
    $report .= "TEST RESULTS SUMMARY:\n";
    $passCount = count(array_filter($testResults, fn($r) => $r === 'PASS'));
    $warnCount = count(array_filter($testResults, fn($r) => $r === 'WARN'));
    $failCount = count(array_filter($testResults, fn($r) => $r === 'FAIL'));
    
    $report .= "✅ Passed: $passCount\n";
    $report .= "⚠️  Warnings: $warnCount\n";
    $report .= "❌ Failed: $failCount\n";
    $report .= "Total: " . count($testResults) . "\n\n";
    
    $report .= "INDIVIDUAL TEST RESULTS:\n";
    foreach ($testResults as $test => $result) {
        $status = match($result) {
            'PASS' => '✅ PASS',
            'WARN' => '⚠️  WARN',
            'FAIL' => '❌ FAIL',
            default => '❓ UNKNOWN'
        };
        $report .= "$test: $status\n";
    }
    
    $report .= "\nDETAILED OUTPUTS:\n";
    $report .= "==================\n\n";
    
    foreach ($outputs as $test => $output) {
        $report .= "--- $test OUTPUT ---\n";
        $report .= $output . "\n\n";
    }
    
    $report .= "RECOMMENDATIONS:\n";
    if ($overallStatus === 'READY') {
        $report .= "• System is ready for production deployment\n";
        $report .= "• All critical tests passed\n";
        $report .= "• No issues found\n";
    } else if ($overallStatus === 'WARNING') {
        $report .= "• Review warnings before deployment\n";
        $report .= "• Consider fixing minor issues\n";
        $report .= "• System may be deployable with caution\n";
    } else {
        $report .= "• DO NOT DEPLOY - critical issues found\n";
        $report .= "• Fix all failed tests before deployment\n";
        $report .= "• Review system configuration\n";
    }
    
    return $report;
}
?>
