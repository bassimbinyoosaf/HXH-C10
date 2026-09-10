<?php
require_once __DIR__ . '/config.php';

// If already logged in, redirect to the inner dashboard
if (is_authenticated()) {
    header('Location: dashboard.php');
    exit;
}

$error_msg = null;
$error_code = $_GET['error'] ?? null;

if ($error_code && isset($ERROR_MESSAGES[$error_code])) {
    $error_msg = $ERROR_MESSAGES[$error_code];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_msg = $ERROR_MESSAGES['EMPTY_CREDENTIALS'];
    } elseif ($username === STAGE1_USER && $password === STAGE1_PASS) {
        // Successful Stage 1 Authentication
        $_SESSION['authenticated'] = true;
        $_SESSION['hunter_user'] = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['hunter_rank'] = 'Double-Star Hunter Candidate (Zodiac Medical)';
        $_SESSION['nen_type'] = 'Emission (Remote Ultrasound)';
        $_SESSION['stage1_cleared'] = true;
        unset($_SESSION['stage2_flag_verified']);
        unset($_SESSION['stage2_clue_unlocked']);
        unset($_SESSION['stage3_master_unlocked']);
        unset($_SESSION['purchased_items']);
        unset($_SESSION['player_trades']);
        unset($_SESSION['player_balances']);
        
        if (!headers_sent()) {
            setcookie('hunter_pouch', '', time() - 3600, '/');
        }
        unset($_COOKIE['hunter_pouch']);

        init_session_game_state();

        header('Location: dashboard.php');
        exit;
    } else {
        $error_msg = $ERROR_MESSAGES['INVALID_NEN'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HXH-C10 // Hunter Association Secret Clearance Gate</title>
    <meta name="description" content="Official clearance portal for the 289th Hunter Examination & Zodiac Expeditions. High-security Nen verification gateway.">
    <link rel="stylesheet" href="assets/css/hunter_style.css">
</head>
<body class="page-login">
    <!-- Canvas for dynamic Nen Aura Particles -->
    <canvas id="nen-canvas"></canvas>

    <div class="app-wrapper">
        <!-- Top Navigation -->
        <header class="hunter-navbar">
            <a href="index.php" class="brand-logo" id="brand-logo">
                <img src="assets/images/logo.png" alt="Hunter × Hunter Logo" class="hunter-logo-img">
                <span class="brand-title">HUNTER <span>ASSOCIATION</span></span>
            </a>
            <div class="nav-status">
                <span class="status-dot"></span>
                <span>SYSTEM: GATEWAY_ONLINE</span>
                <span style="color: var(--hunter-gold); margin-left: 0.5rem;">[SECTOR 10]</span>
            </div>
        </header>

        <!-- Main Login Card -->
        <main class="main-content">
            <div class="hunter-card" id="login-container">
                <div class="card-header">
                    <img src="assets/images/logo.png" alt="Hunter × Hunter" class="hunter-card-logo">
                    <h1 class="card-title">PROJECT HXH-C10</h1>
                </div>

                <?php if ($error_msg): ?>
                    <div class="alert-box alert-error" id="error-banner">
                        <span><?= htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <script>
                        window.addEventListener('DOMContentLoaded', () => {
                            if (window.HunterAudio) window.HunterAudio.playError();
                        });
                    </script>
                <?php endif; ?>

                <div class="alert-box alert-warning" style="margin-bottom: 1.25rem; background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.4); color: #fde68a; font-size: 0.8rem; padding: 0.65rem 0.85rem; border-radius: 8px; display: flex; align-items: center; gap: 0.5rem; line-height: 1.4;">
                    <span style="font-size: 1.15rem;">⚠️</span>
                    <span><strong>EXAM NOTICE:</strong> Do <strong>NOT</strong> log out during your challenge! Logging out resets candidate Jenny currency, player trades, and unlocked gateway progress to zero.</span>
                </div>

                <form method="POST" action="index.php" id="hunter-login-form" autocomplete="off">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <span>Hunter Codename / User</span>
                        </label>
                        <div class="input-container">
                            <span class="input-icon">👤</span>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-input" 
                                placeholder="e.g. hunter_candidate" 
                                required
                                autofocus
                                spellcheck="false"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <span>Nen Passphrase</span>
                        </label>
                        <div class="input-container">
                            <span class="input-icon">🔑</span>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="••••••••••••" 
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submit-btn">
                        <span>VERIFY CLEARANCE</span>
                        <span>⚡</span>
                    </button>
                </form>

                <div class="card-footer-info">
                    <span>SECURITY: <span class="gate-code">EN-RESTRICTED</span></span>
                    <span>GATE: <span class="gate-code">#C10-ZODIAC</span></span>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="hunter-footer">
            <div>
                <span>© <?= HUNTER_EXAM_YEAR ?> — All Rights Reserved</span>
            </div>
            <div>
                <span>PROTOCOL: <span style="color: var(--nen-green);">NEN_SHA256_ACTIVE</span></span>
            </div>
        </footer>
    </div>

    <!-- External Audio Engine -->
    <script src="assets/js/audio_effects.js"></script>
    
    <!-- External Nen Gatekeeper & Roster Sync Logic (Inspect for Debug Leaks) -->
    <script src="assets/js/nen_gatekeeper.js?v=<?= time() ?>"></script>
</body>
</html>
