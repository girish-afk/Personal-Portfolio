<?php
/**
 * Admin Secure Logout
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';

Auth::logout();
header('Location: login.php');
exit;
