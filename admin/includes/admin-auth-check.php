<?php
/**
 * Admin Authentication Gatekeeper
 * Strictly verifies owner session on all private admin routes.
 */

require_once dirname(__DIR__, 2) . '/core/config.php';
require_once dirname(__DIR__, 2) . '/core/database.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

Auth::requireAuth();
