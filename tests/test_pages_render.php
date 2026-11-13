<?php
/**
 * Test public and admin page rendering
 */
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/index.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// 1. Render Public Homepage
ob_start();
require dirname(__DIR__) . '/index.php';
$publicHtml = ob_get_clean();

echo "Public Page Render Size: " . strlen($publicHtml) . " bytes\n";
echo "Public Page Contains 'Girish Pawar': " . (strpos($publicHtml, 'Girish Pawar') !== false ? "YES" : "NO") . "\n";
echo "Public Page Contains 'AgriShield': " . (strpos($publicHtml, 'AgriShield') !== false ? "YES" : "NO") . "\n";
echo "Public Page Contains 'AURA AI': " . (strpos($publicHtml, 'AURA AI') !== false ? "YES" : "NO") . "\n";
echo "Public Page Zero Admin Edit Leaks: " . (strpos($publicHtml, 'admin-auth-check') === false ? "YES" : "NO") . "\n";

// 2. Render Admin Login Page
ob_start();
$_SERVER['REQUEST_URI'] = '/admin/login.php';
require dirname(__DIR__) . '/admin/login.php';
$loginHtml = ob_get_clean();

echo "Admin Login Render Size: " . strlen($loginHtml) . " bytes\n";
echo "Admin Login Contains 'Owner Authentication': " . (strpos($loginHtml, 'Owner Authentication') !== false ? "YES" : "NO") . "\n";
echo "Admin Login Contains CSRF field: " . (strpos($loginHtml, 'name="csrf_token"') !== false ? "YES" : "NO") . "\n";
