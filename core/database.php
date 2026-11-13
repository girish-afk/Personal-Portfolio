<?php
/**
 * Centralized Database Abstraction Layer with PDO MySQL & Resilient Fallback
 * Girish Pawar — Personal Developer Portfolio & CMS
 * 
 * Phase 3 Architecture:
 * - Direct PDO MySQL connection using correct DSN (`dbname` parameter required)
 * - Environment-aware fallback (Safe local fallback for offline development, strict error handling in production)
 * - Zero CREATE DATABASE or USE statements (100% InfinityFree & phpMyAdmin compatible)
 * - Reusable singleton connection via Database::getConnection()
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $pdo = null;
    private static bool $isFallback = false;
    private static bool $initialized = false;
    private static ?string $errorMessage = null;
    private static string $jsonStorePath = DATA_PATH . '/portfolio_store.json';
    private static string $errorLogPath = DATA_PATH . '/database_errors.log';

    /**
     * Get or initialize PDO Connection or Environment Fallback
     */
    public static function init(): void {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        // Check if database credentials are configured
        if (!Env::hasConfiguredCredentials()) {
            $msg = "Database credentials are not yet configured or contain unreplaced placeholders.";
            self::$errorMessage = $msg;
            self::log("WARNING: " . $msg);

            if (Env::isProduction()) {
                self::$isFallback = false;
                self::$pdo = null;
                return;
            } else {
                self::$isFallback = true;
                self::ensureJsonStore();
                return;
            }
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::ATTR_TIMEOUT            => 3,
            ];

            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            self::$isFallback = false;
            self::$errorMessage = null;

            // Auto-check tables if on local development
            if (Env::isLocal()) {
                self::ensureTables();
            }

        } catch (Throwable $e) {
            self::$errorMessage = $e->getMessage();
            self::log("Connection Error: " . self::sanitizeErrorMessage($e->getMessage()));

            if (Env::isProduction()) {
                // Strict production handling: never silently degrade to JSON without admin knowledge
                self::$pdo = null;
                self::$isFallback = false;
            } else {
                // Local development handling: allow offline mock development
                self::$pdo = null;
                self::$isFallback = true;
                self::ensureJsonStore();
            }
        }
    }

    /**
     * Reusable, Centralized PDO Connection
     */
    public static function getConnection(): ?PDO {
        self::init();
        return self::$pdo;
    }

    /**
     * Is currently operating in JSON fallback mode
     */
    public static function isFallback(): bool {
        self::init();
        return self::$isFallback;
    }

    /**
     * Get last connection or query error message
     */
    public static function getErrorMessage(): ?string {
        return self::$errorMessage;
    }

    /**
     * Reset internal connection state (Useful for testing)
     */
    public static function reset(): void {
        self::$pdo = null;
        self::$isFallback = false;
        self::$initialized = false;
        self::$errorMessage = null;
    }

    /**
     * Ensure tables exist when connected locally
     */
    private static function ensureTables(): void {
        if (!self::$pdo) return;

        try {
            $stmt = self::$pdo->query("SHOW TABLES LIKE 'profile'");
            if ($stmt->rowCount() === 0) {
                $schemaFile = CORE_PATH . '/schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    self::$pdo->exec($sql);
                }
                self::seedMysql();
            }
        } catch (Throwable $e) {
            self::log("Table verification error: " . $e->getMessage());
        }
    }

    /**
     * Seed initial records if database is empty
     */
    public static function seedMysql(): void {
        if (!self::$pdo) return;

        require_once CORE_PATH . '/migrate.php';
        $migrator = new PortfolioMigrator();
        $migrator->run();
    }

    /**
     * Re-attempt MySQL Connection and sync JSON data into MySQL
     */
    public static function syncJsonToMysql(): bool {
        require_once CORE_PATH . '/migrate.php';
        $migrator = new PortfolioMigrator();
        $result = $migrator->run();

        if ($result['status'] === 'SUCCESS') {
            self::reset();
            self::init();
            return true;
        }

        self::$errorMessage = implode(' | ', $result['errors'] ?? ['Migration failed.']);
        return false;
    }

    // ========================================================================
    // Unified High-Level Data Query API (Fully Backward-Compatible)
    // ========================================================================

    public static function all(string $table, array $orderBy = []): array {
        self::init();

        if (!self::$isFallback && self::$pdo) {
            try {
                $sql = "SELECT * FROM `{$table}`";
                if (!empty($orderBy)) {
                    $orderParts = [];
                    foreach ($orderBy as $col => $dir) {
                        $orderParts[] = "`{$col}` " . (strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC');
                    }
                    $sql .= " ORDER BY " . implode(', ', $orderParts);
                }
                $stmt = self::$pdo->query($sql);
                $rows = $stmt->fetchAll();
                return self::unserializeRows($table, $rows);
            } catch (Throwable $e) {
                self::handleQueryException($e, "all", $table);
            }
        }

        $store = self::getJsonStore();
        $records = $store[$table] ?? [];
        if (!empty($orderBy) && !empty($records)) {
            usort($records, function ($a, $b) use ($orderBy) {
                foreach ($orderBy as $col => $dir) {
                    $valA = $a[$col] ?? 0;
                    $valB = $b[$col] ?? 0;
                    if ($valA == $valB) continue;
                    $cmp = ($valA < $valB) ? -1 : 1;
                    return strtoupper($dir) === 'DESC' ? -$cmp : $cmp;
                }
                return 0;
            });
        }
        return self::unserializeRows($table, $records);
    }

    public static function find(string $table, int $id): ?array {
        self::init();

        if (!self::$isFallback && self::$pdo) {
            try {
                $stmt = self::$pdo->prepare("SELECT * FROM `{$table}` WHERE `id` = ? LIMIT 1");
                $stmt->execute([$id]);
                $res = $stmt->fetch();
                return $res ? self::unserializeRow($table, $res) : null;
            } catch (Throwable $e) {
                self::handleQueryException($e, "find", $table);
            }
        }

        $store = self::getJsonStore();
        $records = $store[$table] ?? [];
        foreach ($records as $r) {
            if (isset($r['id']) && (int)$r['id'] === $id) {
                return self::unserializeRow($table, $r);
            }
        }
        return null;
    }

    public static function whereFirst(string $table, array $conditions): ?array {
        self::init();

        if (!self::$isFallback && self::$pdo) {
            try {
                $clauses = [];
                $params = [];
                foreach ($conditions as $k => $v) {
                    $clauses[] = "`{$k}` = ?";
                    $params[] = $v;
                }
                $sql = "SELECT * FROM `{$table}` WHERE " . implode(' AND ', $clauses) . " LIMIT 1";
                $stmt = self::$pdo->prepare($sql);
                $stmt->execute($params);
                $res = $stmt->fetch();
                return $res ? self::unserializeRow($table, $res) : null;
            } catch (Throwable $e) {
                self::handleQueryException($e, "whereFirst", $table);
            }
        }

        $store = self::getJsonStore();
        $records = $store[$table] ?? [];
        foreach ($records as $r) {
            $match = true;
            foreach ($conditions as $k => $v) {
                if (!isset($r[$k]) || $r[$k] != $v) {
                    $match = false;
                    break;
                }
            }
            if ($match) return self::unserializeRow($table, $r);
        }
        return null;
    }

    public static function where(string $table, array $conditions, array $orderBy = []): array {
        self::init();

        if (!self::$isFallback && self::$pdo) {
            try {
                $clauses = [];
                $params = [];
                foreach ($conditions as $k => $v) {
                    $clauses[] = "`{$k}` = ?";
                    $params[] = $v;
                }
                $sql = "SELECT * FROM `{$table}`";
                if (!empty($clauses)) {
                    $sql .= " WHERE " . implode(' AND ', $clauses);
                }
                if (!empty($orderBy)) {
                    $orderParts = [];
                    foreach ($orderBy as $col => $dir) {
                        $orderParts[] = "`{$col}` " . (strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC');
                    }
                    $sql .= " ORDER BY " . implode(', ', $orderParts);
                }
                $stmt = self::$pdo->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll();
                return self::unserializeRows($table, $rows);
            } catch (Throwable $e) {
                self::handleQueryException($e, "where", $table);
            }
        }

        $store = self::getJsonStore();
        $records = $store[$table] ?? [];
        $results = [];
        foreach ($records as $r) {
            $match = true;
            foreach ($conditions as $k => $v) {
                if (!isset($r[$k]) || $r[$k] != $v) {
                    $match = false;
                    break;
                }
            }
            if ($match) $results[] = $r;
        }

        if (!empty($orderBy) && !empty($results)) {
            usort($results, function ($a, $b) use ($orderBy) {
                foreach ($orderBy as $col => $dir) {
                    $valA = $a[$col] ?? 0;
                    $valB = $b[$col] ?? 0;
                    if ($valA == $valB) continue;
                    $cmp = ($valA < $valB) ? -1 : 1;
                    return strtoupper($dir) === 'DESC' ? -$cmp : $cmp;
                }
                return 0;
            });
        }

        return self::unserializeRows($table, $results);
    }

    public static function insert(string $table, array $data): int {
        self::init();

        $serializedData = self::serializeRow($table, $data);

        if (!self::$isFallback && self::$pdo) {
            try {
                $cols = array_keys($serializedData);
                $placeholders = array_fill(0, count($cols), '?');
                $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = self::$pdo->prepare($sql);
                $stmt->execute(array_values($serializedData));
                return (int) self::$pdo->lastInsertId();
            } catch (Throwable $e) {
                self::handleQueryException($e, "insert", $table);
            }
        }

        $store = self::getJsonStore();
        if (!isset($store[$table])) {
            $store[$table] = [];
        }

        $maxId = 0;
        foreach ($store[$table] as $r) {
            if (isset($r['id']) && $r['id'] > $maxId) {
                $maxId = (int)$r['id'];
            }
        }
        $newId = $maxId + 1;
        $data['id'] = $newId;
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $store[$table][] = $data;
        self::saveJsonStore($store);
        return $newId;
    }

    public static function update(string $table, array $data, $conditions): bool {
        self::init();

        if (is_numeric($conditions)) {
            $conditions = ['id' => (int)$conditions];
        }

        $serializedData = self::serializeRow($table, $data);

        if (!self::$isFallback && self::$pdo) {
            try {
                $sets = [];
                $params = [];
                foreach ($serializedData as $col => $val) {
                    $sets[] = "`{$col}` = ?";
                    $params[] = $val;
                }
                $where = [];
                foreach ($conditions as $col => $val) {
                    $where[] = "`{$col}` = ?";
                    $params[] = $val;
                }
                $sql = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE " . implode(' AND ', $where);
                $stmt = self::$pdo->prepare($sql);
                return $stmt->execute($params);
            } catch (Throwable $e) {
                self::handleQueryException($e, "update", $table);
            }
        }

        $store = self::getJsonStore();
        $records = $store[$table] ?? [];
        $updated = false;

        foreach ($records as $idx => $r) {
            $match = true;
            foreach ($conditions as $k => $v) {
                if (!isset($r[$k]) || $r[$k] != $v) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                foreach ($data as $k => $v) {
                    $records[$idx][$k] = $v;
                }
                if (isset($records[$idx]['updated_at'])) {
                    $records[$idx]['updated_at'] = date('Y-m-d H:i:s');
                }
                $updated = true;
            }
        }

        if ($updated) {
            $store[$table] = $records;
            self::saveJsonStore($store);
        }
        return $updated;
    }

    public static function delete(string $table, $conditions): bool {
        self::init();

        if (is_numeric($conditions)) {
            $conditions = ['id' => (int)$conditions];
        }

        if (!self::$isFallback && self::$pdo) {
            try {
                $where = [];
                $params = [];
                foreach ($conditions as $col => $val) {
                    $where[] = "`{$col}` = ?";
                    $params[] = $val;
                }
                $sql = "DELETE FROM `{$table}` WHERE " . implode(' AND ', $where);
                $stmt = self::$pdo->prepare($sql);
                return $stmt->execute($params);
            } catch (Throwable $e) {
                self::handleQueryException($e, "delete", $table);
            }
        }

        $store = self::getJsonStore();
        $records = $store[$table] ?? [];
        $newRecords = [];
        $deleted = false;

        foreach ($records as $r) {
            $match = true;
            foreach ($conditions as $k => $v) {
                if (!isset($r[$k]) || $r[$k] != $v) {
                    $match = false;
                    break;
                }
            }
            if (!$match) {
                $newRecords[] = $r;
            } else {
                $deleted = true;
            }
        }

        if ($deleted) {
            $store[$table] = array_values($newRecords);
            self::saveJsonStore($store);
        }
        return $deleted;
    }

    public static function getSetting(string $key, ?string $default = null): ?string {
        $row = self::whereFirst('settings', ['setting_key' => $key]);
        if ($row && isset($row['setting_value'])) {
            return $row['setting_value'];
        }
        $store = self::getJsonStore();
        if (isset($store['settings'][$key])) {
            return (string)$store['settings'][$key];
        }
        return $default;
    }

    public static function updateSetting(string $key, string $value): bool {
        self::init();
        if (!self::$isFallback && self::$pdo) {
            try {
                $stmt = self::$pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
                return $stmt->execute([$key, $value]);
            } catch (Throwable $e) {
                self::handleQueryException($e, "updateSetting", "settings");
            }
        }
        $store = self::getJsonStore();
        if (!isset($store['settings'])) $store['settings'] = [];
        $store['settings'][$key] = $value;
        return self::saveJsonStore($store);
    }

    // ========================================================================
    // Helpers & Serialization
    // ========================================================================

    private static function handleQueryException(Throwable $e, string $operation, string $table): void {
        $msg = sprintf("Query Error [%s on %s]: %s", $operation, $table, $e->getMessage());
        self::$errorMessage = $msg;
        self::log($msg);

        if (Env::isProduction()) {
            throw new RuntimeException("Database error during operation '{$operation}'. Service error has been logged safely.");
        }
    }

    private static function log(string $msg): void {
        $entry = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), self::sanitizeErrorMessage($msg));
        @file_put_contents(self::$errorLogPath, $entry, FILE_APPEND);
    }

    private static function sanitizeErrorMessage(string $msg): string {
        // Strip out passwords if they appear in PDO DSN messages
        if (defined('DB_PASS') && strlen(DB_PASS) > 0) {
            $msg = str_replace(DB_PASS, '********', $msg);
        }
        return $msg;
    }

    private static function serializeRow(string $table, array $data): array {
        foreach (['headline_titles', 'tech_stack', 'screenshots'] as $col) {
            if (isset($data[$col]) && is_array($data[$col])) {
                $data[$col] = json_encode($data[$col], JSON_UNESCAPED_SLASHES);
            }
        }
        return $data;
    }

    private static function unserializeRow(string $table, array $row): array {
        foreach (['headline_titles', 'tech_stack', 'screenshots'] as $col) {
            if (isset($row[$col]) && is_string($row[$col])) {
                $decoded = json_decode($row[$col], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $row[$col] = $decoded;
                }
            }
        }
        return $row;
    }

    private static function unserializeRows(string $table, array $rows): array {
        return array_map(fn($r) => self::unserializeRow($table, $r), $rows);
    }

    private static function ensureJsonStore(): void {
        if (file_exists(self::$jsonStorePath)) {
            return;
        }

        $seedFile = CORE_PATH . '/seed.php';
        if (!file_exists($seedFile)) return;
        $seed = require $seedFile;

        $store = [
            'admin_users' => [
                [
                    'id' => 1,
                    'username' => $seed['admin_user']['username'],
                    'email' => $seed['admin_user']['email'],
                    'password_hash' => password_hash($seed['admin_user']['password'], PASSWORD_DEFAULT),
                    'last_login' => null,
                    'failed_login_attempts' => 0,
                    'lockout_until' => null,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'profile' => [
                array_merge(['id' => 1], $seed['profile'])
            ],
            'skill_categories' => [],
            'skills' => [],
            'projects' => [],
            'experience_claims' => [],
            'internships' => [],
            'education' => [],
            'certifications' => [],
            'social_links' => [],
            'inquiries' => [],
            'settings' => $seed['settings']
        ];

        $catId = 1;
        $catMap = [];
        foreach ($seed['skill_categories'] as $c) {
            $c['id'] = $catId;
            $catMap[$c['slug']] = $catId;
            $store['skill_categories'][] = $c;
            $catId++;
        }

        $skillId = 1;
        foreach ($seed['skills'] as $s) {
            $s['id'] = $skillId++;
            $s['category_id'] = $catMap[$s['category']] ?? 1;
            unset($s['category']);
            $store['skills'][] = $s;
        }

        $projId = 1;
        foreach ($seed['projects'] as $p) {
            $p['id'] = $projId++;
            $p['created_at'] = date('Y-m-d H:i:s');
            $store['projects'][] = $p;
        }

        $expId = 1;
        foreach ($seed['experience_claims'] as $e) {
            $e['id'] = $expId++;
            $store['experience_claims'][] = $e;
        }

        $intId = 1;
        foreach ($seed['internships'] as $i) {
            $i['id'] = $intId++;
            $store['internships'][] = $i;
        }

        $eduId = 1;
        foreach ($seed['education'] as $ed) {
            $ed['id'] = $eduId++;
            $store['education'][] = $ed;
        }

        $certId = 1;
        foreach ($seed['certifications'] as $cert) {
            $cert['id'] = $certId++;
            $store['certifications'][] = $cert;
        }

        $socId = 1;
        foreach ($seed['social_links'] as $soc) {
            $soc['id'] = $socId++;
            $store['social_links'][] = $soc;
        }

        file_put_contents(self::$jsonStorePath, json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public static function getJsonStore(): array {
        self::ensureJsonStore();
        $content = @file_get_contents(self::$jsonStorePath);
        return json_decode($content ?: '{}', true) ?: [];
    }

    public static function saveJsonStore(array $data): bool {
        return (bool) file_put_contents(self::$jsonStorePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
