<?php
/**
 * Environment Configuration Template (Placeholders Only)
 * Girish Pawar — Personal Developer Portfolio & CMS
 * 
 * INSTRUCTIONS:
 * 1. For Local Development: Copy this file to `core/env.local.php` and configure for XAMPP.
 * 2. For InfinityFree Production: Copy this file to `core/env.production.php` and enter
 *    your actual vPanel database credentials.
 * 3. Never commit `env.local.php` or `env.production.php` to version control.
 */

return [
    // Environment Mode: 'local' for development, 'production' for live hosting
    'APP_ENV'       => 'production',
    'APP_DEBUG'     => false,
    'APP_URL'       => 'https://YOUR-CUSTOM-DOMAIN.com',

    // InfinityFree Database Credentials (from vPanel -> MySQL Databases)
    'DB_HOST'       => 'sqlXXX.infinityfree.com', // e.g. sql300.epizy.com or sql123.infinityfree.com
    'DB_PORT'       => '3306',
    'DB_NAME'       => 'if0_XXXXXXXX_girish_portfolio', // Full assigned database name with account prefix
    'DB_USER'       => 'if0_XXXXXXXX',                  // vPanel database username
    'DB_PASS'       => 'YOUR_VPANEL_PASSWORD_HERE',     // vPanel hosting password
    'DB_CHARSET'    => 'utf8mb4',
];
