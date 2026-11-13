<?php
/**
 * Safe, Idempotent JSON-to-MySQL Data Migration Engine
 * Girish Pawar — Personal Developer Portfolio & CMS
 * 
 * Phase 2 Component: Migrates existing data from data/portfolio_store.json into MySQL
 * with atomic transaction handling, duplicate prevention, and zero data loss.
 * 
 * Usage:
 *   CLI: php core/migrate.php [--dry-run] [--force] [--host=...] [--db=...] [--user=...] [--pass=...]
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    // If accessed via web, strictly require admin authentication
    require_once __DIR__ . '/config.php';
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (!Auth::check()) {
        http_response_code(403);
        die('Forbidden: Administrative access required to execute database migrations.');
    }
} else {
    require_once __DIR__ . '/config.php';
}

class PortfolioMigrator {
    private ?PDO $pdo = null;
    private string $jsonPath;
    private bool $isDryRun = false;
    private array $options = [];
    private array $report = [
        'status' => 'PENDING',
        'started_at' => '',
        'completed_at' => '',
        'dry_run' => false,
        'json_source' => '',
        'json_valid' => false,
        'tables_migrated' => [],
        'total_inserted' => 0,
        'total_skipped' => 0,
        'errors' => []
    ];

    public function __construct(string $jsonPath = '', array $cliArgs = []) {
        $this->jsonPath = $jsonPath ?: (dirname(__DIR__) . '/data/portfolio_store.json');
        $this->parseArgs($cliArgs);
        $this->report['started_at'] = date('Y-m-d H:i:s');
        $this->report['json_source'] = $this->jsonPath;
        $this->report['dry_run'] = $this->isDryRun;
    }

    private function parseArgs(array $args): void {
        foreach ($args as $arg) {
            if ($arg === '--dry-run') {
                $this->isDryRun = true;
            } elseif (strpos($arg, '--host=') === 0) {
                $this->options['host'] = substr($arg, 7);
            } elseif (strpos($arg, '--port=') === 0) {
                $this->options['port'] = substr($arg, 7);
            } elseif (strpos($arg, '--db=') === 0 || strpos($arg, '--name=') === 0) {
                $this->options['dbname'] = substr($arg, strpos($arg, '=') + 1);
            } elseif (strpos($arg, '--user=') === 0) {
                $this->options['user'] = substr($arg, 7);
            } elseif (strpos($arg, '--pass=') === 0) {
                $this->options['pass'] = substr($arg, 7);
            }
        }
    }

    /**
     * Establish direct PDO MySQL connection with exact parameters
     */
    public function connect(): bool {
        if ($this->isDryRun) {
            return true;
        }

        $host = $this->options['host'] ?? (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
        $port = $this->options['port'] ?? (defined('DB_PORT') ? DB_PORT : '3306');
        $dbname = $this->options['dbname'] ?? (defined('DB_NAME') ? DB_NAME : 'girish_portfolio');
        $user = $this->options['user'] ?? (defined('DB_USER') ? DB_USER : 'root');
        $pass = $this->options['pass'] ?? (defined('DB_PASS') ? DB_PASS : '');
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

        try {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $dbname, $charset);
            $pdoOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5
            ];

            $this->pdo = new PDO($dsn, $user, $pass, $pdoOptions);
            return true;
        } catch (Throwable $e) {
            $this->report['errors'][] = "MySQL Connection Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Execute complete safe migration
     */
    public function run(): array {
        $this->log("=================================================================");
        $this->log("  GIRISH PAWAR PORTFOLIO — SAFE DATA MIGRATION ENGINE (PHASE 2) ");
        $this->log("=================================================================");
        $this->log("Mode: " . ($this->isDryRun ? "DRY RUN (Simulated, No Changes)" : "LIVE MIGRATION"));
        $this->log("Source: " . $this->jsonPath);

        // 1. Read & Validate JSON store
        if (!file_exists($this->jsonPath)) {
            $err = "Source file does not exist: {$this->jsonPath}";
            $this->report['errors'][] = $err;
            $this->report['status'] = 'FAILED';
            $this->log("[ERROR] {$err}");
            return $this->report;
        }

        $rawJson = file_get_contents($this->jsonPath);
        $data = json_decode($rawJson ?: '', true);

        if (!is_array($data)) {
            $err = "Source JSON is malformed or invalid: " . json_last_error_msg();
            $this->report['errors'][] = $err;
            $this->report['status'] = 'FAILED';
            $this->log("[ERROR] {$err}");
            return $this->report;
        }

        $this->report['json_valid'] = true;
        $this->log("[OK] Source JSON decoded successfully. Validating required entities...");

        // 2. Validate essential entities
        $requiredEntities = [
            'admin_users', 'profile', 'skill_categories', 'skills', 
            'projects', 'experience_claims', 'internships', 
            'education', 'certifications', 'social_links', 'settings'
        ];

        foreach ($requiredEntities as $entity) {
            if (!isset($data[$entity])) {
                $err = "Required entity '{$entity}' missing from JSON store.";
                $this->report['errors'][] = $err;
                $this->report['status'] = 'FAILED';
                $this->log("[ERROR] {$err}");
                return $this->report;
            }
        }

        // Verify admin password hash is intact
        $adminUser = $data['admin_users'][0] ?? null;
        if (!$adminUser || empty($adminUser['password_hash']) || strpos($adminUser['password_hash'], '$2') !== 0) {
            $err = "Admin user record is missing or contains an invalid Bcrypt password hash.";
            $this->report['errors'][] = $err;
            $this->report['status'] = 'FAILED';
            $this->log("[ERROR] {$err}");
            return $this->report;
        }

        $this->log("[OK] Pre-flight validation passed. Admin credentials and entities confirmed.");

        if ($this->isDryRun) {
            $this->simulateMigration($data);
            $this->report['status'] = 'DRY_RUN_PASSED';
            $this->report['completed_at'] = date('Y-m-d H:i:s');
            $this->printSummary();
            return $this->report;
        }

        // Connect to MySQL
        if (!$this->connect()) {
            $this->report['status'] = 'FAILED';
            $this->report['completed_at'] = date('Y-m-d H:i:s');
            $this->log("[ERROR] Cannot connect to MySQL. See errors above.");
            $this->printSummary();
            return $this->report;
        }

        // 3. Execute Migration within an Atomic Database Transaction
        try {
            $this->pdo->beginTransaction();

            $this->migrateAdminUsers($data['admin_users'] ?? []);
            $this->migrateProfile($data['profile'] ?? []);
            $this->migrateSkillCategories($data['skill_categories'] ?? []);
            $this->migrateSkills($data['skills'] ?? []);
            $this->migrateProjects($data['projects'] ?? []);
            $this->migrateExperienceClaims($data['experience_claims'] ?? []);
            $this->migrateInternships($data['internships'] ?? []);
            $this->migrateEducation($data['education'] ?? []);
            $this->migrateCertifications($data['certifications'] ?? []);
            $this->migrateSocialLinks($data['social_links'] ?? []);
            $this->migrateInquiries($data['inquiries'] ?? []);
            $this->migrateSettings($data['settings'] ?? []);

            $this->pdo->commit();
            $this->report['status'] = 'SUCCESS';
            $this->log("\n[SUCCESS] Transaction committed. All records migrated safely into MySQL!");

        } catch (Throwable $e) {
            if ($this->pdo && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
                $this->log("\n[ROLLBACK] Transaction rolled back due to error.");
            }
            $this->report['errors'][] = "Migration Failure: " . $e->getMessage();
            $this->report['status'] = 'FAILED';
            $this->log("[ERROR] " . $e->getMessage());
        }

        $this->report['completed_at'] = date('Y-m-d H:i:s');
        $this->printSummary();
        return $this->report;
    }

    /**
     * Dry Run Simulation
     */
    private function simulateMigration(array $data): void {
        $this->log("\n--- SIMULATING DATA MIGRATION ---");
        foreach ($data as $table => $rows) {
            $count = is_array($rows) ? count($rows) : 0;
            $this->report['tables_migrated'][$table] = [
                'total_in_json' => $count,
                'simulated_insert' => $count,
                'simulated_skip' => 0
            ];
            $this->report['total_inserted'] += $count;
            $this->log(sprintf("  [SIMULATED] %-20s: %d record(s) queued for migration", $table, $count));
        }
    }

    // ========================================================================
    // Entity Migrators (Idempotent & Duplicate-Safe)
    // ========================================================================

    private function migrateAdminUsers(array $users): void {
        $table = 'admin_users';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `admin_users` WHERE `username` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `admin_users` 
            (`id`, `username`, `email`, `password_hash`, `last_login`, `failed_login_attempts`, `lockout_until`, `created_at`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($users as $u) {
            $checkStmt->execute([$u['username']]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $insertStmt->execute([
                    $u['id'] ?? null,
                    $u['username'],
                    $u['email'],
                    $u['password_hash'],
                    $u['last_login'] ?? null,
                    $u['failed_login_attempts'] ?? 0,
                    $u['lockout_until'] ?? null,
                    $u['created_at'] ?? date('Y-m-d H:i:s')
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($users), $inserted, $skipped);
    }

    private function migrateProfile(array $profiles): void {
        $table = 'profile';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `profile` WHERE `id` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `profile` 
            (`id`, `full_name`, `primary_title`, `headline_titles`, `bio_short`, `bio_full`, `avatar_url`, `location`, `email`, `phone`, `available_for_hire`, `resume_url`, `approach_text`, `learning_text`, `career_goals`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($profiles as $p) {
            $id = $p['id'] ?? 1;
            $checkStmt->execute([$id]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $headlines = is_array($p['headline_titles']) ? json_encode($p['headline_titles'], JSON_UNESCAPED_SLASHES) : $p['headline_titles'];
                $insertStmt->execute([
                    $id,
                    $p['full_name'],
                    $p['primary_title'] ?? 'Web Developer',
                    $headlines,
                    $p['bio_short'] ?? '',
                    $p['bio_full'] ?? '',
                    $p['avatar_url'] ?? 'assets/img/girish-avatar.svg',
                    $p['location'] ?? 'Maharashtra, India',
                    $p['email'] ?? '',
                    $p['phone'] ?? '',
                    $p['available_for_hire'] ?? 1,
                    $p['resume_url'] ?? 'uploads/resume/Girish_Pawar_Resume.pdf',
                    $p['approach_text'] ?? null,
                    $p['learning_text'] ?? null,
                    $p['career_goals'] ?? null
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($profiles), $inserted, $skipped);
    }

    private function migrateSkillCategories(array $categories): void {
        $table = 'skill_categories';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `skill_categories` WHERE `slug` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `skill_categories` (`id`, `name`, `slug`, `sort_order`) VALUES (?, ?, ?, ?)");

        foreach ($categories as $c) {
            $checkStmt->execute([$c['slug']]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $insertStmt->execute([
                    $c['id'] ?? null,
                    $c['name'],
                    $c['slug'],
                    $c['sort_order'] ?? 0
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($categories), $inserted, $skipped);
    }

    private function migrateSkills(array $skills): void {
        $table = 'skills';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `skills` WHERE `category_id` = ? AND `name` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `skills` 
            (`id`, `category_id`, `name`, `proficiency_level`, `proficiency_percent`, `icon_class`, `sort_order`, `is_featured`, `is_visible`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($skills as $s) {
            $catId = (int)($s['category_id'] ?? 1);
            $checkStmt->execute([$catId, $s['name']]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $insertStmt->execute([
                    $s['id'] ?? null,
                    $catId,
                    $s['name'],
                    $s['proficiency_level'] ?? 'Proficient',
                    $s['proficiency_percent'] ?? 85,
                    $s['icon_class'] ?? 'fas fa-code',
                    $s['sort_order'] ?? 0,
                    $s['is_featured'] ?? 1,
                    $s['is_visible'] ?? 1
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($skills), $inserted, $skipped);
    }

    private function migrateProjects(array $projects): void {
        $table = 'projects';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `projects` WHERE `slug` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `projects` 
            (`id`, `title`, `slug`, `category`, `short_description`, `cover_image`, `logo_url`, `tech_stack`, `github_url`, `demo_url`, `status`, `development_date`, `key_features`, `challenges`, `solutions`, `results`, `case_study_content`, `screenshots`, `sort_order`, `is_featured`, `is_visible`, `created_at`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($projects as $p) {
            $checkStmt->execute([$p['slug']]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $techStack = is_array($p['tech_stack']) ? json_encode($p['tech_stack'], JSON_UNESCAPED_SLASHES) : $p['tech_stack'];
                $screenshots = is_array($p['screenshots'] ?? null) ? json_encode($p['screenshots'], JSON_UNESCAPED_SLASHES) : ($p['screenshots'] ?? null);

                $insertStmt->execute([
                    $p['id'] ?? null,
                    $p['title'],
                    $p['slug'],
                    $p['category'],
                    $p['short_description'],
                    $p['cover_image'] ?? 'assets/img/projects/agrishield.svg',
                    $p['logo_url'] ?? '',
                    $techStack,
                    $p['github_url'] ?? '',
                    $p['demo_url'] ?? '',
                    $p['status'] ?? 'Active',
                    $p['development_date'] ?? null,
                    $p['key_features'] ?? null,
                    $p['challenges'] ?? null,
                    $p['solutions'] ?? null,
                    $p['results'] ?? null,
                    $p['case_study_content'] ?? null,
                    $screenshots,
                    $p['sort_order'] ?? 0,
                    $p['is_featured'] ?? 1,
                    $p['is_visible'] ?? 1,
                    $p['created_at'] ?? date('Y-m-d H:i:s')
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($projects), $inserted, $skipped);
    }

    private function migrateExperienceClaims(array $claims): void {
        $table = 'experience_claims';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `experience_claims` WHERE `area` = ? AND `duration` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `experience_claims` (`id`, `area`, `duration`, `description`, `sort_order`, `is_visible`) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($claims as $c) {
            $checkStmt->execute([$c['area'], $c['duration']]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $insertStmt->execute([
                    $c['id'] ?? null,
                    $c['area'],
                    $c['duration'],
                    $c['description'] ?? null,
                    $c['sort_order'] ?? 0,
                    $c['is_visible'] ?? 1
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($claims), $inserted, $skipped);
    }

    private function migrateInternships(array $internships): void {
        $table = 'internships';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `internships` WHERE `company` = ? AND `domain` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `internships` 
            (`id`, `company`, `location`, `domain`, `technologies`, `role_description`, `start_date`, `end_date`, `sort_order`, `is_visible`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($internships as $i) {
            $checkStmt->execute([$i['company'], $i['domain']]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $insertStmt->execute([
                    $i['id'] ?? null,
                    $i['company'],
                    $i['location'],
                    $i['domain'],
                    $i['technologies'],
                    $i['role_description'],
                    $i['start_date'] ?? null,
                    $i['end_date'] ?? null,
                    $i['sort_order'] ?? 0,
                    $i['is_visible'] ?? 1
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($internships), $inserted, $skipped);
    }

    private function migrateEducation(array $education): void {
        $table = 'education';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `education` WHERE `degree` = ? AND `institution` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `education` (`id`, `degree`, `institution`, `score`, `duration`, `description`, `sort_order`, `is_visible`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($education as $e) {
            $checkStmt->execute([$e['degree'], $e['institution']]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $insertStmt->execute([
                    $e['id'] ?? null,
                    $e['degree'],
                    $e['institution'],
                    $e['score'],
                    $e['duration'],
                    $e['description'] ?? null,
                    $e['sort_order'] ?? 0,
                    $e['is_visible'] ?? 1
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($education), $inserted, $skipped);
    }

    private function migrateCertifications(array $certs): void {
        $table = 'certifications';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `certifications` WHERE `title` = ? AND `issuing_organization` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `certifications` 
            (`id`, `title`, `certificate_type`, `issuing_organization`, `issue_date`, `rank_achievement`, `credential_id`, `verification_url`, `image_url`, `description`, `sort_order`, `is_visible`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($certs as $c) {
            $checkStmt->execute([$c['title'], $c['issuing_organization']]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $insertStmt->execute([
                    $c['id'] ?? null,
                    $c['title'],
                    $c['certificate_type'] ?? 'Certificate of Participation',
                    $c['issuing_organization'],
                    $c['issue_date'],
                    $c['rank_achievement'] ?? null,
                    $c['credential_id'] ?? null,
                    $c['verification_url'] ?? null,
                    $c['image_url'] ?? 'assets/img/ieee-certificate.svg',
                    $c['description'] ?? null,
                    $c['sort_order'] ?? 0,
                    $c['is_visible'] ?? 1
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($certs), $inserted, $skipped);
    }

    private function migrateSocialLinks(array $links): void {
        $table = 'social_links';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `social_links` WHERE `platform` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `social_links` (`id`, `platform`, `display_label`, `url`, `icon_class`, `is_visible`, `sort_order`) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($links as $l) {
            $checkStmt->execute([$l['platform']]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $insertStmt->execute([
                    $l['id'] ?? null,
                    $l['platform'],
                    $l['display_label'] ?? $l['platform'],
                    $l['url'],
                    $l['icon_class'] ?? 'fas fa-link',
                    $l['is_visible'] ?? 1,
                    $l['sort_order'] ?? 0
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($links), $inserted, $skipped);
    }

    private function migrateInquiries(array $inquiries): void {
        $table = 'inquiries';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `inquiries` WHERE `email` = ? AND `created_at` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `inquiries` (`id`, `name`, `email`, `subject`, `message`, `ip_address`, `is_read`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($inquiries as $inq) {
            $checkStmt->execute([$inq['email'], $inq['created_at'] ?? '']);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $insertStmt->execute([
                    $inq['id'] ?? null,
                    $inq['name'],
                    $inq['email'],
                    $inq['subject'],
                    $inq['message'],
                    $inq['ip_address'] ?? null,
                    $inq['is_read'] ?? 0,
                    $inq['created_at'] ?? date('Y-m-d H:i:s')
                ]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($inquiries), $inserted, $skipped);
    }

    private function migrateSettings(array $settings): void {
        $table = 'settings';
        $inserted = 0; $skipped = 0;
        $checkStmt = $this->pdo->prepare("SELECT id FROM `settings` WHERE `setting_key` = ? LIMIT 1");
        $insertStmt = $this->pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)");

        foreach ($settings as $key => $val) {
            $checkStmt->execute([$key]);
            if ($checkStmt->fetch()) {
                $skipped++;
            } else {
                $insertStmt->execute([$key, (string)$val]);
                $inserted++;
            }
        }
        $this->recordTableResult($table, count($settings), $inserted, $skipped);
    }

    private function recordTableResult(string $table, int $total, int $inserted, int $skipped): void {
        $this->report['tables_migrated'][$table] = [
            'total_in_json' => $total,
            'inserted' => $inserted,
            'skipped_existing' => $skipped
        ];
        $this->report['total_inserted'] += $inserted;
        $this->report['total_skipped'] += $skipped;
        $this->log(sprintf("  %-20s: %2d total | %2d inserted | %2d preserved/skipped", $table, $total, $inserted, $skipped));
    }

    private function log(string $msg): void {
        echo $msg . "\n";
    }

    private function printSummary(): void {
        $this->log("\n-----------------------------------------------------------------");
        $this->log("MIGRATION SUMMARY:");
        $this->log("Status:         " . $this->report['status']);
        $this->log("Total Inserted: " . $this->report['total_inserted']);
        $this->log("Total Skipped:  " . $this->report['total_skipped']);
        if (!empty($this->report['errors'])) {
            $this->log("Errors Encountered (" . count($this->report['errors']) . "):");
            foreach ($this->report['errors'] as $err) {
                $this->log("  - " . $err);
            }
        }
        $this->log("-----------------------------------------------------------------\n");
    }
}

// CLI Execution entrypoint
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $migrator = new PortfolioMigrator('', $argv ?? []);
    $result = $migrator->run();
    exit($result['status'] === 'FAILED' ? 1 : 0);
}
