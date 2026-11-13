<?php
/**
 * Centralized Environment Configuration Manager
 * Girish Pawar — Personal Developer Portfolio & CMS
 * 
 * Manages dual-environment resolution (Local XAMPP vs InfinityFree Production)
 * without requiring operating system environment variables.
 */

declare(strict_types=1);

if (defined('ENV_LOADED')) {
    return;
}
define('ENV_LOADED', true);

class Env {
    private static ?array $config = null;
    private static string $sourceFile = '';
    private static ?bool $forcedProduction = null;

    /**
     * Force environment mode (Useful for test suites)
     */
    public static function setForcedProduction(?bool $isProd): void {
        self::$forcedProduction = $isProd;
        self::$config = null;
    }

    /**
     * Load environment configuration safely
     */
    public static function load(): array {
        if (self::$config !== null) {
            return self::$config;
        }

        $coreDir = __DIR__;
        $localFile = $coreDir . '/env.local.php';
        $prodFile = $coreDir . '/env.production.php';
        $sampleFile = $coreDir . '/env.sample.php';

        // Check if forced via test / parameter
        if (self::$forcedProduction === true) {
            if (file_exists($prodFile)) {
                self::$config = require $prodFile;
                self::$sourceFile = 'env.production.php';
            } else {
                self::$config = require $sampleFile;
                self::$sourceFile = 'env.sample.php';
            }
            return self::$config;
        } elseif (self::$forcedProduction === false) {
            if (file_exists($localFile)) {
                self::$config = require $localFile;
                self::$sourceFile = 'env.local.php';
            }
            return self::$config ?? [];
        }

        // Determine if current execution context is local
        $isLocalContext = false;
        if (php_sapi_name() === 'cli') {
            // CLI defaults to local if env.local.php exists
            $isLocalContext = file_exists($localFile);
        } else {
            $host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
            $isLocalContext = in_array($host, ['localhost', '127.0.0.1', '::1', 'test'], true)
                           || str_ends_with($host, '.local')
                           || str_ends_with($host, '.test');
        }

        $loadedData = null;

        if ($isLocalContext && file_exists($localFile)) {
            $loadedData = require $localFile;
            self::$sourceFile = 'env.local.php';
        } elseif (file_exists($prodFile)) {
            $loadedData = require $prodFile;
            self::$sourceFile = 'env.production.php';
        } elseif (file_exists($localFile)) {
            $loadedData = require $localFile;
            self::$sourceFile = 'env.local.php';
        } elseif (file_exists($sampleFile)) {
            $loadedData = require $sampleFile;
            self::$sourceFile = 'env.sample.php';
        } else {
            $loadedData = [
                'APP_ENV'    => 'local',
                'APP_DEBUG'  => true,
                'APP_URL'    => 'http://localhost/craft-portfolio',
                'DB_HOST'    => '127.0.0.1',
                'DB_PORT'    => '3306',
                'DB_NAME'    => 'girish_portfolio',
                'DB_USER'    => 'root',
                'DB_PASS'    => '',
                'DB_CHARSET' => 'utf8mb4',
            ];
            self::$sourceFile = 'internal_defaults';
        }

        self::$config = is_array($loadedData) ? $loadedData : [];
        return self::$config;
    }

    /**
     * Retrieve a configuration parameter
     */
    public static function get(string $key, $default = null) {
        $cfg = self::load();
        return $cfg[$key] ?? $default;
    }

    /**
     * Is currently running in production environment
     */
    public static function isProduction(): bool {
        return strtolower((string)self::get('APP_ENV', 'local')) === 'production';
    }

    /**
     * Is currently running in local development environment
     */
    public static function isLocal(): bool {
        return !self::isProduction();
    }

    /**
     * Is debug mode enabled
     */
    public static function isDebug(): bool {
        return (bool)self::get('APP_DEBUG', self::isLocal());
    }

    /**
     * Name of the file from which configuration was loaded
     */
    public static function getSource(): string {
        self::load();
        return self::$sourceFile;
    }

    /**
     * Checks if credentials are fully configured or contain unreplaced placeholders
     */
    public static function hasConfiguredCredentials(): bool {
        $host = (string)self::get('DB_HOST', '');
        $name = (string)self::get('DB_NAME', '');
        $user = (string)self::get('DB_USER', '');
        $pass = (string)self::get('DB_PASS', '');

        if ($host === '' || $name === '' || $user === '') {
            return false;
        }

        // Detect placeholder strings
        if (str_contains($host, 'sqlXXX') 
            || str_contains($name, 'XXXXXXXX') 
            || str_contains($user, 'XXXXXXXX') 
            || $pass === 'YOUR_VPANEL_PASSWORD_HERE'
            || $pass === 'ENTER_PRODUCTION_PASSWORD_HERE') {
            return false;
        }

        return true;
    }

    /**
     * Return all configuration parameters with password masked
     */
    public static function getSanitized(): array {
        $cfg = self::load();
        $safe = $cfg;
        if (isset($safe['DB_PASS'])) {
            $safe['DB_PASS'] = strlen((string)$safe['DB_PASS']) > 0 ? '********' : '';
        }
        $safe['_source_file'] = self::$sourceFile;
        return $safe;
    }
}
