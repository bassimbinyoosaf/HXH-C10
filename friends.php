<?php
require_once __DIR__ . '/config.php';
require_auth();

$currentUser = $_SESSION['hunter_user'] ?? 'leorio_paradinight';
$candidates = get_all_users();
$myBalance = get_user_balance($currentUser);
$activePage = 'directory';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HXH-C10 // Hunter Network — Friends & Players Directory</title>
    <link rel="stylesheet" href="assets/css/hunter_style.css">
</head>
<body class="page-dashboard">
    <canvas id="nen-canvas"></canvas>

    <div class="app-wrapper">
        <?php include __DIR__ . '/nav_header.php'; ?>

        <main class="main-content" style="align-items: flex-start; padding-top: 1.5rem;">
            <div style="width: 100%; max-width: 1200px; margin: 0 auto;">
                
                <div class="dashboard-panel" style="margin-bottom: 1.5rem;">
                    <div class="panel-title">
                        <span>👥 HUNTER GUILD & PLAYERS DIRECTORY</span>
                        <span style="color: var(--nen-green); font-family: var(--font-mono); font-size: 0.85rem;">15 REGISTERED PLAYERS</span>
                    </div>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6;">
                        Complete network directory of registered players and Greed Island hunters. Inspect player dossiers or jump directly into their account console.
                    </p>
                </div>

                <!-- Friends & Players Table -->
                <div class="dashboard-panel" style="padding: 1.25rem;">
                    <div class="table-container">
                        <table class="hunter-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="width: 60px; text-align: center;">Avatar</th>
                                    <th>Character Name</th>
                                    <th>Username Codename</th>
                                    <th>Affiliation / Title</th>
                                    <th>Nen Hatsu</th>
                                    <th style="text-align: center; width: 140px;">Dossier</th>
                                    <th style="text-align: center; width: 160px;">Account Jump</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $idx = 1; foreach ($candidates as $cand): ?>
                                    <tr class="<?= ($cand['username'] === $currentUser) ? 'active-row' : '' ?>">
                                        <td style="font-family: var(--font-mono); color: var(--text-muted); font-size: 0.85rem;">
                                            <?= sprintf('%02d', $idx++) ?>
                                        </td>
                                        <td style="font-size: 1.5rem; text-align: center;">
                                            <?= htmlspecialchars($cand['avatar_badge']) ?>
                                        </td>
                                        <td style="font-weight: 700; color: var(--text-primary);">
                                            <?= htmlspecialchars($cand['full_name']) ?>
                                            <?php if ($cand['username'] === $currentUser): ?>
                                                <span style="font-size: 0.68rem; background: rgba(0,255,136,0.15); border: 1px solid var(--nen-green); color: var(--nen-green); padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.4rem;">
                                                    YOU
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code style="color: var(--nen-green); font-size: 0.85rem;">@<?= htmlspecialchars($cand['username']) ?></code>
                                        </td>
                                        <td style="font-size: 0.82rem; color: var(--text-secondary);">
                                            <?= htmlspecialchars($cand['title']) ?>
                                        </td>
                                        <td style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--killua-cyan);">
                                            <?= htmlspecialchars($cand['nen_type']) ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="profile.php?username=<?= urlencode($cand['username']) ?>" class="btn-table-action" style="padding: 0.35rem 0.65rem;">
                                                <span>DOSSIER</span>
                                                <span>🔍</span>
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="wallet.php?username=<?= urlencode($cand['username']) ?>" class="btn-table-action" style="background: rgba(251,191,36,0.15); border-color: var(--hunter-gold); color: var(--hunter-gold); padding: 0.35rem 0.65rem;">
                                                <span>JUMP</span>
                                                <span>➔</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>

        <footer class="hunter-footer">
            <div>
                <span>HUNTER NETWORK // GUILD PLAYERS DIRECTORY</span>
            </div>
            <div>
                <span>SWITCH_IDOR_TARGET: <span style="color: var(--nen-green);">wallet.php?username=&lt;username&gt;</span></span>
            </div>
        </footer>
    </div>

    <script src="assets/js/audio_effects.js"></script>
    <script src="assets/js/nen_gatekeeper.js?v=<?= time() ?>"></script>
</body>
</html>
