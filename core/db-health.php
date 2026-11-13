<?php
/**
 * Database Health & Connectivity Diagnostic Endpoint
 * Girish Pawar — Personal Developer Portfolio & CMS
 * 
 * Phase 3 Component: Inspects real database connectivity, connected database name,
 * server version, and table accessibility.
 * 
 * Access Policy:
 * - CLI: Permitted.
 * - HTTP Web: STRICTLY RESTRICTED to authenticated owner sessions (Auth::check()).
 *   Unauthorized requests receive a silent HTTP 403 Forbidden.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$isCli = (php_sapi_name() === 'cli');

// Enforce security authorization on web requests
if (!$isCli) {
    if (!Auth::check()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'error' => 'Forbidden: Administrative authentication required.']);
        exit;
    }
}

// Canonical 12 Tables List
$canonicalTables = [
    'admin_users', 'profile', 'skill_categories', 'skills', 
    'projects', 'experience_claims', 'internships', 'education', 
    'certifications', 'social_links', 'inquiries', 'settings'
];

$healthReport = [
    'timestamp'            => date('Y-m-d H:i:s'),
    'environment'          => defined('APP_ENV') ? APP_ENV : 'unknown',
    'config_source'        => Env::getSource(),
    'has_credentials'      => Env::hasConfiguredCredentials(),
    'connection_status'    => 'DISCONNECTED',
    'is_fallback_mode'     => Database::isFallback(),
    'pdo_available'        => false,
    'server_version'       => null,
    'connected_database'   => null,
    'character_set'        => null,
    'canonical_tables'     => [
        'expected'  => count($canonicalTables),
        'present'   => 0,
        'missing'   => [],
        'details'   => []
    ],
    'diagnostic_messages'  => []
];

// Attempt PDO connection
$pdo = Database::getConnection();

if ($pdo instanceof PDO) {
    $healthReport['pdo_available'] = true;
    $healthReport['connection_status'] = 'CONNECTED';
    $healthReport['is_fallback_mode'] = false;

    try {
        // Connected database name
        $dbStmt = $pdo->query("SELECT DATABASE() AS current_db");
        $healthReport['connected_database'] = $dbStmt->fetchColumn() ?: 'unknown';

        // Server version
        $healthReport['server_version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

        // Character set
        $csStmt = $pdo->query("SELECT @@character_set_connection AS cs, @@collation_connection AS coll");
        $csInfo = $csStmt->fetch();
        $healthReport['character_set'] = ($csInfo['cs'] ?? '') . ' / ' . ($csInfo['coll'] ?? '');

        // Verify presence and accessibility of all 12 canonical tables
        $existingTablesStmt = $pdo->query("SHOW TABLES");
        $allDbTables = $existingTablesStmt->fetchAll(PDO::FETCH_COLUMN);

        $presentCount = 0;
        foreach ($canonicalTables as $tableName) {
            if (in_array($tableName, $allDbTables, true)) {
                $presentCount++;
                try {
                    $cntStmt = $pdo->query("SELECT COUNT(*) FROM `{$tableName}`");
                    $rowCount = (int)$cntStmt->fetchColumn();
                    $healthReport['canonical_tables']['details'][$tableName] = [
                        'status'    => 'ACCESSIBLE',
                        'row_count' => $rowCount
                    ];
                } catch (Throwable $te) {
                    $healthReport['canonical_tables']['details'][$tableName] = [
                        'status' => 'QUERY_ERROR',
                        'error'  => $te->getMessage()
                    ];
                }
            } else {
                $healthReport['canonical_tables']['missing'][] = $tableName;
                $healthReport['canonical_tables']['details'][$tableName] = [
                    'status' => 'MISSING'
                ];
            }
        }
        $healthReport['canonical_tables']['present'] = $presentCount;

        if ($presentCount === count($canonicalTables)) {
            $healthReport['diagnostic_messages'][] = "All 12 canonical tables are installed and verified.";
        } else {
            $missingList = implode(', ', $healthReport['canonical_tables']['missing']);
            $healthReport['diagnostic_messages'][] = "Missing canonical tables: {$missingList}. Execute core/schema.sql to install.";
        }

    } catch (Throwable $e) {
        $healthReport['diagnostic_messages'][] = "Diagnostic inspection error: " . $e->getMessage();
    }
} else {
    $healthReport['connection_status'] = 'OFFLINE';
    $healthReport['pdo_available'] = false;
    $healthReport['diagnostic_messages'][] = Database::getErrorMessage() ?: "Unable to connect to MySQL.";
    
    if (Database::isFallback()) {
        $healthReport['diagnostic_messages'][] = "Application is currently operating under local JSON fallback mode.";
    }
}

// Output Format
if ($isCli) {
    echo "=================================================================\n";
    echo "  GIRISH PAWAR PORTFOLIO — DATABASE HEALTH CHECK (PHASE 3)      \n";
    echo "=================================================================\n";
    echo "Timestamp:          " . $healthReport['timestamp'] . "\n";
    echo "Environment:        " . strtoupper($healthReport['environment']) . "\n";
    echo "Config Source:      " . $healthReport['config_source'] . "\n";
    echo "Configured Creds:   " . ($healthReport['has_credentials'] ? "YES" : "NO (Placeholders detected)") . "\n";
    echo "Connection Status:  " . $healthReport['connection_status'] . "\n";
    echo "Fallback Mode:      " . ($healthReport['is_fallback_mode'] ? "ACTIVE (data/portfolio_store.json)" : "INACTIVE (Direct MySQL)") . "\n";
    echo "PDO Available:      " . ($healthReport['pdo_available'] ? "YES" : "NO") . "\n";
    
    if ($healthReport['pdo_available']) {
        echo "Database Name:      " . $healthReport['connected_database'] . "\n";
        echo "Server Version:     " . $healthReport['server_version'] . "\n";
        echo "Character Set:      " . $healthReport['character_set'] . "\n";
        echo "Canonical Tables:   " . $healthReport['canonical_tables']['present'] . "/" . $healthReport['canonical_tables']['expected'] . " Present\n";
        echo "\nTable Breakdown:\n";
        foreach ($healthReport['canonical_tables']['details'] as $tbl => $info) {
            echo sprintf("  %-20s: %-12s (Rows: %s)\n", $tbl, $info['status'], $info['row_count'] ?? 'N/A');
        }
    }
    
    echo "\nDiagnostics:\n";
    foreach ($healthReport['diagnostic_messages'] as $msg) {
        echo "  - " . $msg . "\n";
    }
    echo "=================================================================\n";
    exit($healthReport['pdo_available'] ? 0 : 1);
} else {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($healthReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}
