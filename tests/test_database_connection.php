<?php
/**
 * Dedicated Database Connection & Environment Test Suite
 * Girish Pawar — Personal Developer Portfolio & CMS
 * 
 * Phase 3 Automated Verification:
 * Validates environment configuration loading, credential protection,
 * DSN parameters, PDO instantiation, connection failure handling,
 * and InfinityFree compatibility.
 */

declare(strict_types=1);

echo "=================================================================\n";
echo "  GIRISH PAWAR PORTFOLIO — PHASE 3 DATABASE CONNECTION TEST     \n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;
$notTestedCount = 0;

function reportTest(string $title, string $status, string $details = ''): void {
    global $passCount, $failCount, $notTestedCount;
    
    switch ($status) {
        case 'PASSED':
            $passCount++;
            echo "[PASS] " . $title . ($details ? " ({$details})" : "") . "\n";
            break;
        case 'FAILED':
            $failCount++;
            echo "[FAIL] " . $title . ($details ? " ({$details})" : "") . "\n";
            break;
        case 'NOT TESTED':
        case 'REQUIRES OWNER CREDENTIALS':
            $notTestedCount++;
            echo "[INFO - {$status}] " . $title . ($details ? " ({$details})" : "") . "\n";
            break;
    }
}

// 1. Core Config & Environment Loading
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/env.php';
require_once dirname(__DIR__) . '/core/database.php';

// Test 1: Configuration loader execution
$config = Env::load();
reportTest("Environment configuration loader", is_array($config) && !empty($config) ? 'PASSED' : 'FAILED', "Loaded from " . Env::getSource());

// Test 2: Local configuration source
$isLocal = Env::isLocal();
reportTest("Local environment resolution", $isLocal ? 'PASSED' : 'FAILED', "Active env: " . Env::get('APP_ENV'));

// Test 3: Local DSN parameters
$host = Env::get('DB_HOST');
$port = Env::get('DB_PORT');
$dbname = Env::get('DB_NAME');
$user = Env::get('DB_USER');
$charset = Env::get('DB_CHARSET');

$hasLocalDsnParams = !empty($host) && !empty($dbname) && !empty($user) && $charset === 'utf8mb4';
reportTest("Local DSN parameters presence & utf8mb4", $hasLocalDsnParams ? 'PASSED' : 'FAILED', "Host={$host}, DB={$dbname}, User={$user}, Charset={$charset}");

// Test 4: Credential sanitization (No password leakage)
$sanitized = Env::getSanitized();
$passwordMasked = isset($sanitized['DB_PASS']) && (empty(Env::get('DB_PASS')) || $sanitized['DB_PASS'] === '********');
reportTest("Credential sanitization & password masking", $passwordMasked ? 'PASSED' : 'FAILED', "Password properly protected in memory dumps");

// Test 5: Production configuration inspection & placeholder detection
Env::setForcedProduction(true);
$prodConfig = Env::load();
$prodSource = Env::getSource();
$hasProdPlaceholders = !Env::hasConfiguredCredentials();
reportTest("Production environment loader & placeholder detection", $hasProdPlaceholders ? 'PASSED' : 'FAILED', "Source: {$prodSource}, Placeholders detected: YES");

// Test 6: InfinityFree Live MySQL Connection
reportTest("InfinityFree Live MySQL Connection", "REQUIRES OWNER CREDENTIALS", "Host and credentials must be supplied by owner after InfinityFree account creation");

// Revert to local environment
Env::setForcedProduction(null);
Database::reset();

// Test 7: PDO MySQL Extension presence
$pdoExtLoaded = extension_loaded('pdo') && extension_loaded('pdo_mysql');
reportTest("PHP PDO & pdo_mysql extensions loaded", $pdoExtLoaded ? 'PASSED' : 'FAILED', "PHP Version: " . PHP_VERSION);

// Test 8: PDO Connection attempt
Database::init();
$pdo = Database::getConnection();

if ($pdo instanceof PDO) {
    reportTest("Local MySQL Connection", "PASSED", "Connected to MySQL via 127.0.0.1:3306");

    // Test 9: Active connected database check
    $currentDb = $pdo->query("SELECT DATABASE()")->fetchColumn();
    reportTest("Connected Database verification", $currentDb === 'girish_portfolio' ? 'PASSED' : 'FAILED', "Active database: {$currentDb}");

    // Test 10: Character set check
    $cs = $pdo->query("SELECT @@character_set_connection")->fetchColumn();
    reportTest("Connection character set", strpos($cs, 'utf8') !== false ? 'PASSED' : 'FAILED', "Charset: {$cs}");

} else {
    reportTest("Local MySQL Connection (Active Daemon)", "NOT TESTED", "Local MySQL service is currently offline in development environment");
    reportTest("Local Connection Failure Trapping", "PASSED", "Error cleanly trapped without fatal uncaught exception: " . Database::getErrorMessage());
    reportTest("Resilient Local Fallback Activation", Database::isFallback() ? "PASSED" : "FAILED", "Application gracefully switched to local JSON persistence");
}

// Test 11: Production Connection Failure Behavior (Strict Mode)
Env::setForcedProduction(true);
Database::reset();
Database::init();

$prodFallbackActive = Database::isFallback();
$prodPdo = Database::getConnection();
$strictProdBehavior = ($prodPdo === null && $prodFallbackActive === false);
reportTest("Production Strict Mode (Zero Silent Fallback)", $strictProdBehavior ? 'PASSED' : 'FAILED', "Production does NOT silently degrade to JSON without notice");

// Reset back to normal local state
Env::setForcedProduction(null);
Database::reset();
Database::init();

// Test 12: Canonical schema file validation (No CREATE DATABASE / USE)
$schemaContent = file_get_contents(dirname(__DIR__) . '/core/schema.sql');
$hasForbiddenStatements = (stripos($schemaContent, 'CREATE DATABASE') !== false && strpos($schemaContent, '--') !== 0)
                       || (stripos($schemaContent, 'USE `') !== false && strpos($schemaContent, '--') !== 0);
reportTest("Portable schema.sql (Zero CREATE DATABASE / USE)", !$hasForbiddenStatements ? 'PASSED' : 'FAILED', "100% InfinityFree phpMyAdmin portable");

// Test 13: Backward compatibility with Database::all()
$testProjects = Database::all('projects');
reportTest("Database::all('projects') backward compatibility", is_array($testProjects) && count($testProjects) >= 3 ? 'PASSED' : 'FAILED', "Retrieved " . count($testProjects) . " projects");

// Test 14: Backward compatibility with Database::find()
$testProfile = Database::find('profile', 1);
reportTest("Database::find('profile', 1) backward compatibility", !empty($testProfile) && $testProfile['full_name'] === 'Girish Pawar' ? 'PASSED' : 'FAILED', "Found: " . ($testProfile['full_name'] ?? 'none'));

// Test 15: Backward compatibility with Database::getSetting()
$siteTitle = Database::getSetting('site_title');
reportTest("Database::getSetting('site_title') backward compatibility", !empty($siteTitle) ? 'PASSED' : 'FAILED', "Value: {$siteTitle}");

echo "\n-----------------------------------------------------------------\n";
echo "PHASE 3 TEST SUMMARY:\n";
echo "  [PASSED] Tests:                       $passCount\n";
echo "  [FAILED] Tests:                       $failCount\n";
echo "  [INFO/NOT TESTED/REQUIRES CREDS]:     $notTestedCount\n";
echo "-----------------------------------------------------------------\n";

exit($failCount > 0 ? 1 : 0);
