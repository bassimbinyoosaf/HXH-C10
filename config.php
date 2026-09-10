<?php
/**
 * HXH-C10: Hunter x Hunter CTF Challenge - Master Configuration
 * Project 10: Hunter Association Clearance Gate & In-Game Economy
 */

// Prevent direct script execution if accessed strangely
if (session_status() === PHP_SESSION_NONE) {
    session_name('HXH_NEN_SESSION');
    session_start();
}

// Disable browser caching so back/forward buttons always fetch current live session
if (!headers_sent()) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

// MySQL Database Credentials (Configurable via ENV for Docker or standalone)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'hxh_c10_db');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');

require_once __DIR__ . '/db.php';

// Stage 1 Leaked Credentials
if (!defined('STAGE1_USER')) define('STAGE1_USER', 'leorio_paradinight');
if (!defined('STAGE1_PASS')) define('STAGE1_PASS', 'dragon'); 

// Challenge Flags & System Metadata (Loaded dynamically from Database)
if (!defined('CHALLENGE_NAME')) define('CHALLENGE_NAME', 'HXH-C10: Greed Island & Hunter Network');
if (!defined('CHALLENGE_VERSION')) define('CHALLENGE_VERSION', '2.1.0-MYSQL');
if (!defined('HUNTER_EXAM_YEAR')) define('HUNTER_EXAM_YEAR', '289th Hunter Examination & Greed Island System');
if (!defined('FLAG_STAGE2')) define('FLAG_STAGE2', get_challenge_flag('stage2'));
if (!defined('FLAG_STAGE3')) define('FLAG_STAGE3', get_challenge_flag('stage3'));

// In-Game Currency & Item Price Constants
if (!defined('JENNY_NAME')) define('JENNY_NAME', 'Jenny');
if (!defined('JENNY_SYMBOL')) define('JENNY_SYMBOL', 'Ⓙ');
if (!defined('CLUE_STANDARD_PRICE')) define('CLUE_STANDARD_PRICE', 1000);
if (!defined('CLUE_MASTER_PRICE')) define('CLUE_MASTER_PRICE', 20000);

// In-Game Lore & Trade Error Messages
$ERROR_MESSAGES = [
    'INVALID_NEN' => '⛔ [ACCESS REJECTED] Incompatible Ten/Ren frequency. Your Hunter credentials do not match our database.',
    'EMPTY_CREDENTIALS' => '⚠️ [TEN WARNING] You cannot enter the Hunter Gate with empty aura signatures.',
    'UNAUTHORIZED' => '🚫 [SYSTEM LOCKOUT] Unauthorized player presence detected in Sector 10. Session expired.',
    'RATE_LIMIT' => '⌛ [BLOODLUST DETECTED] Overwhelming Ren detected. Cooldown enforced.',
    'INSUFFICIENT_FUNDS' => '💸 [TRADE DECLINED] Insufficient Jenny in sender player pouch.',
    'INVALID_AMOUNT' => '⚠️ [TRADE WARNING] Transfer amount must be greater than 0 Ⓙ.',
    'USER_NOT_FOUND' => '❓ [PLAYER ERROR] Destination or source Hunter character codename not found.'
];

// Helper: Check authentication status
function is_authenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true && isset($_SESSION['hunter_user']);
}

// Helper: Apply IDOR user account switch directly into session
function apply_idor_user_switch($username) {
    if (!empty($username)) {
        $targetUser = get_user_by_username($username);
        if ($targetUser) {
            $_SESSION['hunter_user'] = $targetUser['username'];
            $_SESSION['hunter_rank'] = $targetUser['title'];
            $_SESSION['nen_type'] = $targetUser['nen_type'];
            return $targetUser;
        }
    }
    return null;
}

// Helper: Require authentication & handle universal URL parameter user switching (IDOR)
function require_auth() {
    if (!is_authenticated()) {
        header('Location: index.php?error=UNAUTHORIZED');
        exit;
    }

    // Global IDOR handler: if ?user= or ?username= or ?player= is passed, switch active session
    $paramUser = trim($_GET['user'] ?? $_GET['username'] ?? $_GET['id'] ?? $_GET['name'] ?? $_GET['player'] ?? $_GET['u'] ?? '');
    if (!empty($paramUser)) {
        $paramUser = ltrim($paramUser, '@');
        apply_idor_user_switch($paramUser);
    }
}
