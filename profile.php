<?php
require_once __DIR__ . '/config.php';
require_auth();

$paramUser = trim($_GET['user'] ?? $_GET['username'] ?? $_GET['id'] ?? $_GET['name'] ?? $_GET['player'] ?? $_GET['u'] ?? '');
if (!empty($paramUser)) {
    $paramUser = ltrim($paramUser, '@');
    $target = get_user_by_username($paramUser);
    if ($target) {
        $_SESSION['hunter_user'] = $target['username'];
        $_SESSION['hunter_rank'] = $target['title'];
        $_SESSION['nen_type'] = $target['nen_type'];
        header("Location: profile.php");
        exit;
    }
}

$currentUser = $_SESSION['hunter_user'] ?? 'leorio_paradinight';
$profile = get_user_by_username($currentUser);
$targetBalance = $profile ? (int)$profile['balance'] : 0;
$activePage = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HXH-C10 // Player Profile — <?= htmlspecialchars($currentUser) ?></title>
    <link rel="stylesheet" href="assets/css/hunter_style.css">
</head>
<body class="page-dashboard">
    <canvas id="nen-canvas"></canvas>

    <div class="app-wrapper">
        <?php include __DIR__ . '/nav_header.php'; ?>

        <main class="main-content" style="align-items: flex-start; padding-top: 1.5rem;">
            <div style="width: 100%; max-width: 850px; margin: 0 auto;">

                <?php if ($profile): ?>
                    <!-- Complete Player Profile & Dossier View -->
                    <div class="dashboard-panel <?= ($targetBalance > 0) ? 'highlight' : '' ?>">
                        <div class="panel-title">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <span style="font-size: 2.2rem;"><?= htmlspecialchars($profile['avatar_badge']) ?></span>
                                <div>
                                    <div style="font-size: 1.45rem; font-weight: 800; color: var(--text-primary);">
                                        <?= htmlspecialchars($profile['full_name']) ?>
                                    </div>
                                    <div style="font-family: var(--font-mono); font-size: 0.85rem; color: var(--nen-green);">
                                        @<?= htmlspecialchars($profile['username']) ?>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="license-tag" style="margin-bottom: 0;">
                                    <?= htmlspecialchars($profile['license_number']) ?>
                                </span>
                            </div>
                        </div>

                        <div style="margin-top: 1.5rem;">
                            <div class="prop-row">
                                <span class="prop-label">Affiliation / Title:</span>
                                <span class="prop-value"><?= htmlspecialchars($profile['title']) ?></span>
                            </div>
                            <div class="prop-row">
                                <span class="prop-label">Nen Aura Classification:</span>
                                <span class="prop-value" style="color: var(--killua-cyan);"><?= htmlspecialchars($profile['nen_type']) ?></span>
                            </div>
                            
                            <!-- In-Game Jenny in Player Pouch (Money in Hand) -->
                            <div class="prop-row" style="background: rgba(0,0,0,0.35); padding: 0.95rem 0.85rem; border-radius: 8px; margin: 0.75rem 0; border: 1px solid <?= ($targetBalance > 0) ? 'var(--hunter-gold)' : 'rgba(255,255,255,0.08)' ?>;">
                                <span class="prop-label" style="font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.4rem; font-size: 0.92rem;">
                                    <span>💰 In-Game Jenny in Player Pouch:</span>
                                </span>
                                <span class="prop-value <?= ($targetBalance > 0) ? 'gold' : '' ?>" style="font-size: 1.25rem; font-weight: 900;">
                                    <?= number_format($profile['balance']) ?> <?= JENNY_SYMBOL ?> (<?= JENNY_NAME ?>)
                                    <?php if ($targetBalance > 0): ?>
                                        <span style="font-size: 0.72rem; background: rgba(251,191,36,0.25); border: 1px solid var(--hunter-gold); color: #fff; padding: 0.2rem 0.6rem; border-radius: 4px; margin-left: 0.5rem; letter-spacing: 0.04em;">
                                            💰 LIQUID FUNDS
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="prop-row">
                                <span class="prop-label">Hunter Registry Date:</span>
                                <span class="prop-value" style="color: var(--text-muted); font-size: 0.8rem;"><?= htmlspecialchars($profile['created_at']) ?></span>
                            </div>
                        </div>

                        <div style="margin-top: 1.5rem;">
                            <h4 style="font-size: 0.85rem; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 0.5rem; font-family: var(--font-mono);">
                                📝 Character Lore & Player Bio:
                            </h4>
                            <div class="terminal-box" style="font-size: 0.85rem; line-height: 1.6;">
                                <?= htmlspecialchars($profile['bio']) ?>
                            </div>
                        </div>

                        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                            <a href="wallet.php" class="btn-submit" style="text-decoration: none; font-size: 0.88rem; padding: 0.8rem 1.2rem; flex: 1;">
                                <span>OPEN WALLET & TRADES</span>
                                <span>🎒</span>
                            </a>
                            <a href="dashboard.php" class="nav-link-btn" style="padding: 0.8rem 1.2rem; display: flex; align-items: center; justify-content: center;">
                                <span>← Dashboard</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="dashboard-panel">
                        <div class="alert-box alert-error">
                            <span>⛔ [PLAYER 404] No registered player found for character codename: "<?= htmlspecialchars($paramUser) ?>".</span>
                        </div>
                        <a href="profile.php" class="btn-submit" style="text-decoration: none; width: auto; display: inline-flex;">
                            <span>Return to Profile</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer class="hunter-footer">
            <div>
                <span>HUNTER NETWORK // USER PROFILE</span>
            </div>
            <div>
                <span>USER: <span style="color: var(--nen-green);">@<?= htmlspecialchars($currentUser) ?></span></span>
            </div>
        </footer>
    </div>

    <script src="assets/js/audio_effects.js"></script>
    <script src="assets/js/nen_gatekeeper.js?v=<?= time() ?>"></script>
    <script>
        // Clean URL query parameters from history so browser back/forward buttons maintain active session
        if (window.location.search) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>
</html>
