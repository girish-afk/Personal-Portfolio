<?php
/**
 * Database Abstraction Layer with PDO MySQL & Resilient Persistence
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $pdo = null;
    private static bool $isFallback = false;
    private static ?string $errorMessage = null;
    private static string $jsonStorePath = DATA_PATH . '/portfolio_store.json';

    /**
     * Get or initialize PDO Connection or Fallback
     */
    public static function init(): void {
        if (self::$pdo !== null || self::$isFallback) {
            return;
        }

        try {
            // First attempt to connect to MySQL server
            $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 2, // 2-second timeout to avoid stalling if MySQL is down
            ];

            $tempPdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Check / Create Database
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $tempPdo->exec("USE `" . DB_NAME . "`");
            
            self::$pdo = $tempPdo;
            self::$isFallback = false;

            // Check if tables are installed, if not, execute schema and seed
            self::ensureTablesAndSeed();

        } catch (Throwable $e) {
            // MySQL unavailable or connection refused
            self::$isFallback = true;
            self::$errorMessage = $e->getMessage();
            self::ensureJsonStore();
        }
    }

    /**
     * Is currently operating in JSON fallback mode
     */
    public static function isFallback(): bool {
        self::init();
        return self::$isFallback;
    }

    /**
     * Get error message if any
     */
    public static function getErrorMessage(): ?string {
        return self::$errorMessage;
    }

    /**
     * Direct PDO connection (if available)
     */
    public static function getConnection(): ?PDO {
        self::init();
        return self::$pdo;
    }

    /**
     * Ensure MySQL tables and initial seed data exist
     */
    private static function ensureTablesAndSeed(): void {
        if (!self::$pdo) return;

        // Check if `profile` table exists
        $stmt = self::$pdo->query("SHOW TABLES LIKE 'profile'");
        if ($stmt->rowCount() === 0) {
            // Read and execute schema
            $schemaFile = CONFIG_PATH . '/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                self::$pdo->exec($sql);
            }
            // Seed initial data
            self::seedMysql();
        }
    }

    /**
     * Seed MySQL database with default content
     */
    public static function seedMysql(): void {
        if (!self::$pdo) return;

        $seedFile = CONFIG_PATH . '/seed.php';
        if (!file_exists($seedFile)) return;
        $seed = require $seedFile;

        // 1. Admin user
        $adminStmt = self::$pdo->query("SELECT COUNT(*) FROM admin_users");
        if ($adminStmt->fetchColumn() == 0) {
            $hashedPass = password_hash($seed['admin_user']['password'], PASSWORD_DEFAULT);
            $stmt = self::$pdo->prepare("INSERT INTO admin_users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$seed['admin_user']['username'], $seed['admin_user']['email'], $hashedPass]);
        }

        // 2. Profile
        $profStmt = self::$pdo->query("SELECT COUNT(*) FROM profile");
        if ($profStmt->fetchColumn() == 0) {
            $p = $seed['profile'];
            $stmt = self::$pdo->prepare("INSERT INTO profile 
                (full_name, primary_title, headline_titles, bio_short, bio_full, avatar_url, location, email, phone, available_for_hire, resume_url, approach_text, learning_text, career_goals) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $p['full_name'],
                $p['primary_title'],
                json_encode($p['headline_titles']),
                $p['bio_short'],
                $p['bio_full'],
                $p['avatar_url'],
                $p['location'],
                $p['email'],
                $p['phone'],
                $p['available_for_hire'],
                $p['resume_url'],
                $p['approach_text'],
                $p['learning_text'],
                $p['career_goals']
            ]);
        }

        // 3. Skill categories
        $catStmt = self::$pdo->query("SELECT COUNT(*) FROM skill_categories");
        if ($catStmt->fetchColumn() == 0) {
            $catMap = [];
            $stmt = self::$pdo->prepare("INSERT INTO skill_categories (name, slug, sort_order) VALUES (?, ?, ?)");
            foreach ($seed['skill_categories'] as $cat) {
                $stmt->execute([$cat['name'], $cat['slug'], $cat['sort_order']]);
                $catMap[$cat['slug']] = self::$pdo->lastInsertId();
            }

            // 4. Skills
            $skillStmt = self::$pdo->prepare("INSERT INTO skills (category_id, name, proficiency_level, proficiency_percent, icon_class, sort_order, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $order = 1;
            foreach ($seed['skills'] as $skill) {
                $catId = $catMap[$skill['category']] ?? 1;
                $skillStmt->execute([
                    $catId,
                    $skill['name'],
                    $skill['proficiency_level'],
                    $skill['proficiency_percent'],
                    $skill['icon_class'],
                    $order++,
                    $skill['is_featured']
                ]);
            }
        }

        // 5. Projects
        $projStmt = self::$pdo->query("SELECT COUNT(*) FROM projects");
        if ($projStmt->fetchColumn() == 0) {
            $stmt = self::$pdo->prepare("INSERT INTO projects 
                (title, slug, category, short_description, cover_image, logo_url, tech_stack, github_url, demo_url, status, development_date, key_features, challenges, solutions, results, case_study_content, screenshots, is_featured, is_visible, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($seed['projects'] as $p) {
                $stmt->execute([
                    $p['title'],
                    $p['slug'],
                    $p['category'],
                    $p['short_description'],
                    $p['cover_image'],
                    $p['logo_url'],
                    json_encode($p['tech_stack']),
                    $p['github_url'],
                    $p['demo_url'],
                    $p['status'],
                    $p['development_date'],
                    $p['key_features'],
                    $p['challenges'],
                    $p['solutions'],
                    $p['results'],
                    $p['case_study_content'],
                    json_encode($p['screenshots']),
                    $p['is_featured'],
                    $p['is_visible'],
                    $p['sort_order']
                ]);
            }
        }

        // 6. Experience Claims
        $expStmt = self::$pdo->query("SELECT COUNT(*) FROM experience_claims");
        if ($expStmt->fetchColumn() == 0) {
            $stmt = self::$pdo->prepare("INSERT INTO experience_claims (area, duration, description, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($seed['experience_claims'] as $exp) {
                $stmt->execute([$exp['area'], $exp['duration'], $exp['description'], $exp['sort_order']]);
            }
        }

        // 7. Internships
        $intStmt = self::$pdo->query("SELECT COUNT(*) FROM internships");
        if ($intStmt->fetchColumn() == 0) {
            $stmt = self::$pdo->prepare("INSERT INTO internships (company, location, domain, technologies, role_description, start_date, end_date, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($seed['internships'] as $int) {
                $stmt->execute([$int['company'], $int['location'], $int['domain'], $int['technologies'], $int['role_description'], $int['start_date'], $int['end_date'], $int['sort_order']]);
            }
        }

        // 8. Education
        $eduStmt = self::$pdo->query("SELECT COUNT(*) FROM education");
        if ($eduStmt->fetchColumn() == 0) {
            $stmt = self::$pdo->prepare("INSERT INTO education (degree, institution, score, duration, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($seed['education'] as $edu) {
                $stmt->execute([$edu['degree'], $edu['institution'], $edu['score'], $edu['duration'], $edu['description'], $edu['sort_order']]);
            }
        }

        // 9. Certifications
        $certStmt = self::$pdo->query("SELECT COUNT(*) FROM certifications");
        if ($certStmt->fetchColumn() == 0) {
            $stmt = self::$pdo->prepare("INSERT INTO certifications (title, certificate_type, issuing_organization, issue_date, rank_achievement, credential_id, verification_url, image_url, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($seed['certifications'] as $c) {
                $stmt->execute([$c['title'], $c['certificate_type'], $c['issuing_organization'], $c['issue_date'], $c['rank_achievement'], $c['credential_id'], $c['verification_url'], $c['image_url'], $c['description'], $c['sort_order']]);
            }
        }

        // 10. Social Links
        $socStmt = self::$pdo->query("SELECT COUNT(*) FROM social_links");
        if ($socStmt->fetchColumn() == 0) {
            $stmt = self::$pdo->prepare("INSERT INTO social_links (platform, display_label, url, icon_class, is_visible, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($seed['social_links'] as $s) {
                $stmt->execute([$s['platform'], $s['display_label'], $s['url'], $s['icon_class'], $s['is_visible'], $s['sort_order']]);
            }
        }

        // 11. Settings
        $setStmt = self::$pdo->query("SELECT COUNT(*) FROM settings");
        if ($setStmt->fetchColumn() == 0) {
            $stmt = self::$pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($seed['settings'] as $k => $v) {
                $stmt->execute([$k, $v]);
            }
        }
    }

    /**
     * Ensure JSON fallback data store exists and is seeded
     */
    private static function ensureJsonStore(): void {
        if (file_exists(self::$jsonStorePath)) {
            return;
        }

        $seedFile = CONFIG_PATH . '/seed.php';
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

        // Categories
        $catId = 1;
        $catMap = [];
        foreach ($seed['skill_categories'] as $c) {
            $c['id'] = $catId;
            $catMap[$c['slug']] = $catId;
            $store['skill_categories'][] = $c;
            $catId++;
        }

        // Skills
        $skillId = 1;
        foreach ($seed['skills'] as $s) {
            $s['id'] = $skillId++;
            $s['category_id'] = $catMap[$s['category']] ?? 1;
            unset($s['category']);
            $store['skills'][] = $s;
        }

        // Projects
        $projId = 1;
        foreach ($seed['projects'] as $p) {
            $p['id'] = $projId++;
            $p['created_at'] = date('Y-m-d H:i:s');
            $store['projects'][] = $p;
        }

        // Experience Claims
        $expId = 1;
        foreach ($seed['experience_claims'] as $e) {
            $e['id'] = $expId++;
            $store['experience_claims'][] = $e;
        }

        // Internships
        $intId = 1;
        foreach ($seed['internships'] as $i) {
            $i['id'] = $intId++;
            $store['internships'][] = $i;
        }

        // Education
        $eduId = 1;
        foreach ($seed['education'] as $ed) {
            $ed['id'] = $eduId++;
            $store['education'][] = $ed;
        }

        // Certifications
        $certId = 1;
        foreach ($seed['certifications'] as $cert) {
            $cert['id'] = $certId++;
            $store['certifications'][] = $cert;
        }

        // Social Links
        $socId = 1;
        foreach ($seed['social_links'] as $soc) {
            $soc['id'] = $socId++;
            $store['social_links'][] = $soc;
        }

        file_put_contents(self::$jsonStorePath, json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Read JSON store
     */
    public static function getJsonStore(): array {
        self::ensureJsonStore();
        $content = @file_get_contents(self::$jsonStorePath);
        return json_decode($content ?: '{}', true) ?: [];
    }

    /**
     * Write JSON store
     */
    public static function saveJsonStore(array $data): bool {
        return (bool) file_put_contents(self::$jsonStorePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    // ==========================================
    // Unified High-Level Data Query API
    // ==========================================

    /**
     * Fetch all records from a table
     */
    public static function all(string $table, array $orderBy = []): array {
        self::init();

        if (!self::$isFallback && self::$pdo) {
            $sql = "SELECT * FROM `{$table}`";
            if (!empty($orderBy)) {
                $orderParts = [];
                foreach ($orderBy as $col => $dir) {
                    $orderParts[] = "`{$col}` " . (strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC');
                }
                $sql .= " ORDER BY " . implode(', ', $orderParts);
            }
            try {
                $stmt = self::$pdo->query($sql);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                // If query fails, fall back to json
            }
        }

        // JSON Fallback
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
        return $records;
    }

    /**
     * Find a single record by ID
     */
    public static function find(string $table, int $id): ?array {
        self::init();

        if (!self::$isFallback && self::$pdo) {
            try {
                $stmt = self::$pdo->prepare("SELECT * FROM `{$table}` WHERE `id` = ? LIMIT 1");
                $stmt->execute([$id]);
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return $res ?: null;
            } catch (Throwable $e) {}
        }

        $store = self::getJsonStore();
        $records = $store[$table] ?? [];
        foreach ($records as $r) {
            if (isset($r['id']) && (int)$r['id'] === $id) {
                return $r;
            }
        }
        return null;
    }

    /**
     * Find first record matching conditions
     */
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
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                return $res ?: null;
            } catch (Throwable $e) {}
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
            if ($match) return $r;
        }
        return null;
    }

    /**
     * Find all records matching conditions
     */
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
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {}
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

        return $results;
    }

    /**
     * Insert a record and return ID
     */
    public static function insert(string $table, array $data): int {
        self::init();

        if (!self::$isFallback && self::$pdo) {
            try {
                $cols = array_keys($data);
                $placeholders = array_fill(0, count($cols), '?');
                $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = self::$pdo->prepare($sql);
                $stmt->execute(array_values($data));
                return (int) self::$pdo->lastInsertId();
            } catch (Throwable $e) {}
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

    /**
     * Update records matching conditions or ID
     */
    public static function update(string $table, array $data, $conditions): bool {
        self::init();

        if (is_numeric($conditions)) {
            $conditions = ['id' => (int)$conditions];
        }

        if (!self::$isFallback && self::$pdo) {
            try {
                $sets = [];
                $params = [];
                foreach ($data as $col => $val) {
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
            } catch (Throwable $e) {}
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

    /**
     * Delete records matching conditions or ID
     */
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
            } catch (Throwable $e) {}
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
}
