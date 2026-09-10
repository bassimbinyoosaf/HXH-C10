<?php
/**
 * HXH-C10: Hunter Session Termination
 */
require_once __DIR__ . '/config.php';

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear digitized smart ring pouch cookie on logout
if (!headers_sent()) {
    setcookie('hunter_pouch', '', time() - 3600, '/');
}
unset($_COOKIE['hunter_pouch']);

session_destroy();

header('Location: index.php');
exit;
