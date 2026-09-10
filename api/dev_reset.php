<?php
/**
 * HXH-C10 // DEV Temporary Balance & Session Reset Endpoint
 * [DEV BUTTON - REMINDER TO DELETE BEFORE FINAL DEPLOYMENT]
 */
require_once __DIR__ . '/../config.php';

// 1. Reset all database candidate balances, transaction logs, and purchased clues
dev_reset_balances();

// 2. Clear and destroy active session completely
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// 3. If called via JSON API, respond with JSON confirmation
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'DEV: Full game state & session reset! Redirecting to initial main page.',
        'milluki_balance' => 10000,
        'leorio_balance' => 0
    ]);
    exit;
}

// 4. By default, redirect user directly back to initial main page
header("Location: ../index.php");
exit;

