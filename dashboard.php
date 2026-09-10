<?php
require_once __DIR__ . '/config.php';
require_auth();

$user = $_SESSION['hunter_user'] ?? 'leorio_paradinight';
$userInfo = get_user_by_username($user);
$fullName = $userInfo['full_name'] ?? $user;
$avatar = $userInfo['avatar_badge'] ?? '🪪';
$license = $userInfo['license_number'] ?? '#LIC-UNKNOWN';
$rank = $userInfo['title'] ?? ($_SESSION['hunter_rank'] ?? 'Standard Hunter');
$nen = $userInfo['nen_type'] ?? ($_SESSION['nen_type'] ?? 'Unknown Affinity');
$myBalance = get_user_balance($user);
$allUsers = get_all_users();
$activePage = 'dashboard';

// Public Declared Wealth Estimates (Dashboard Display Only — Fake Money to make all candidates look rich)
$fakeWealth = [
    'chrollo_lucilfer'   => '98,400,000 Ⓙ',
    'hisoka_morow'      => '95,200,000 Ⓙ',
    'illumi_zoldyck'    => '92,600,000 Ⓙ',
    'killua_zoldyck'    => '88,900,000 Ⓙ',
    'kurapika_kurta'    => '85,300,000 Ⓙ',
    'feitan_portor'     => '82,100,000 Ⓙ',
    'machi_komacine'    => '78,500,000 Ⓙ',
    'gon_freecss'       => '74,800,000 Ⓙ',
    'hanzo_ninja'       => '71,200,000 Ⓙ',
    'pokkle_archer'     => '67,900,000 Ⓙ',
    'ponzu_bees'        => '65,400,000 Ⓙ',
    'leorio_paradinight'=> '63,800,000 Ⓙ',
    'zepile_appraiser'  => '61,500,000 Ⓙ',
    'tonpa_crusher'     => '59,200,000 Ⓙ',
    'milluki_zoldyck'   => '58,400,000 Ⓙ'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HXH-C10 // Hunter Association Inner Command Terminal</title>
    <link rel="stylesheet" href="assets/css/hunter_style.css">
</head>
<body class="page-dashboard">
    <canvas id="nen-canvas"></canvas>

    <div class="app-wrapper">
        <?php include __DIR__ . '/nav_header.php'; ?>

        <main class="main-content" style="align-items: flex-start; padding-top: 1.5rem;">
            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem; width: 100%; max-width: 1250px; margin: 0 auto;">
                
                <!-- Session Warning Banner -->
                <div style="grid-column: 1 / -1; background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.35); padding: 0.75rem 1.25rem; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.3rem;">⚠️</span>
                        <span style="color: #fde68a; font-size: 0.85rem; line-height: 1.4;">
                            <strong>HUNTER EXAM NOTICE:</strong> Do <strong>NOT</strong> log out during your expedition. Logging out will immediately reset your Jenny in-hand, P2P transactions, and unlocked portal progress back to zero.
                        </span>
                    </div>
                    <span style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--hunter-gold); background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.3); padding: 0.25rem 0.6rem; border-radius: 4px; white-space: nowrap;">
                        PERSISTENT_SESSION_ACTIVE
                    </span>
                </div>

                <!-- Panel 1: Hunter Identity & Money in Hand -->
                <div class="dashboard-panel highlight">
                    <div class="panel-title">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="font-size: 1.6rem;"><?= htmlspecialchars($avatar) ?></span>
                            <div>
                                <span style="font-weight: 800; color: var(--text-primary); font-size: 1.05rem;"><?= htmlspecialchars($fullName) ?></span>
                                <span style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--nen-green); display: block;">@<?= htmlspecialchars($user) ?></span>
                            </div>
                        </div>
                        <span class="license-tag" style="margin-bottom: 0; font-size: 0.72rem;">
                            <?= htmlspecialchars($license) ?>
                        </span>
                    </div>
                    <div class="prop-row">
                        <span class="prop-label">Active Hunter Identity</span>
                        <span class="prop-value" style="color: var(--nen-green); font-weight: 700;">@<?= htmlspecialchars($user) ?></span>
                    </div>
                    <div class="prop-row">
                        <span class="prop-label">Clearance Division</span>
                        <span class="prop-value gold"><?= htmlspecialchars($rank) ?></span>
                    </div>
                    <div class="prop-row">
                        <span class="prop-label">Nen Aura Affinity</span>
                        <span class="prop-value" style="color: var(--killua-cyan);"><?= htmlspecialchars($nen) ?></span>
                    </div>
                    <div class="prop-row" style="background: rgba(0,0,0,0.3); padding: 0.65rem 0.5rem; border-radius: 6px; margin-top: 0.25rem;">
                        <span class="prop-label" style="font-weight: 700; color: var(--text-primary);">💰 Real In-Hand Jenny (Physical Pouch):</span>
                        <span class="prop-value <?= ($myBalance > 0) ? 'gold' : '' ?>" style="font-size: 1.15rem; font-weight: 900;">
                            <?= number_format($myBalance) ?> <?= JENNY_SYMBOL ?> (<?= JENNY_NAME ?>)
                        </span>
                    </div>
                </div>

                <!-- Panel 2: In-Universe Hunter Transmission -->
                <div class="dashboard-panel">
                    <div class="panel-title">
                        <span>📟 HUNTER EXAM TRANSMISSION</span>
                        <span style="color: var(--hunter-gold); font-size: 0.8rem; font-family: var(--font-mono);">ENCRYPTED COMMS // CH-10</span>
                    </div>
                    <div style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.65;">
                        <p style="color: var(--text-primary); font-weight: 600; margin-bottom: 0.5rem;">
                            Attention, Candidate Paradinight:
                        </p>
                        <p>
                            Whispers inside the Hunter Association reveal a critical security flaw in the <strong>Profile</strong> portal—candidate records lack access validation, allowing anyone who directly references another hunter's file to slip right into their identity and account.
                        </p>
                    </div>
                </div>

                <!-- Panel 3: Complete Users List Directory (All 15 Players) -->
                <div class="dashboard-panel" id="hunter-directory" style="grid-column: 1 / -1;">
                    <div class="panel-title">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 1.3rem;">👥</span>
                            <div>
                                <span style="font-weight: 800; font-size: 1.1rem; color: var(--text-primary);">SECTOR 10 HUNTER REGISTRY & WEALTH LEADERBOARD</span>
                                <span style="font-family: var(--font-mono); font-size: 0.78rem; color: var(--nen-green); display: block;">OFFICIAL HUNTER ROSTER & CANDIDATE DOSSIERS</span>
                            </div>
                        </div>
                        <span style="color: var(--nen-green); font-family: var(--font-mono); font-size: 0.82rem; background: rgba(0,255,136,0.1); border: 1px solid var(--border-color); padding: 0.25rem 0.6rem; border-radius: 4px;">
                            <?= count($allUsers) ?> REGISTERED PLAYERS
                        </span>
                    </div>
                    
                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 1.25rem; line-height: 1.5;">
                        Public Hunter Registry with declared net worth and asset valuations. Actual liquid pouch funds must be inspected directly through individual account dossiers.
                    </p>

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
                                    <th style="text-align: right; width: 170px;">Declared Net Worth</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $idx = 1; foreach ($allUsers as $cand): ?>
                                    <tr class="<?= ($cand['username'] === $user) ? 'active-row' : '' ?>">
                                        <td style="font-family: var(--font-mono); color: var(--text-muted); font-size: 0.85rem;">
                                            <?= sprintf('%02d', $idx++) ?>
                                        </td>
                                        <td style="font-size: 1.5rem; text-align: center;">
                                            <?= htmlspecialchars($cand['avatar_badge']) ?>
                                        </td>
                                        <td style="font-weight: 700; color: var(--text-primary);">
                                            <?= htmlspecialchars($cand['full_name']) ?>
                                            <?php if ($cand['username'] === $user): ?>
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
                                        <td style="text-align: right;">
                                            <span style="font-family: var(--font-mono); font-size: 0.88rem; font-weight: 700; color: var(--hunter-gold);">
                                                <?= htmlspecialchars($fakeWealth[$cand['username']] ?? '10,000,000 Ⓙ') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Panel 4: Quick Module Navigation Tiles -->
                <div class="dashboard-panel" style="grid-column: 1 / -1;">
                    <div class="panel-title">
                        <span>🧭 SECTOR 10 SUBDOMAIN MODULES</span>
                        <span style="color: var(--killua-cyan); font-size: 0.8rem; font-family: var(--font-mono);">ACCESS_GRANTED</span>
                    </div>
                    
                    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-top: 1rem;">
                        
                        <!-- Module 1: Profile Inspector -->
                        <div class="module-card">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🪪</div>
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.3rem;">
                                Profile Dossier Inspector
                            </h3>
                            <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.4; margin-bottom: 1rem;">
                                Inspect your Hunter license and real physical pouch balance via <code style="color: var(--nen-green);">profile.php</code>.
                            </p>
                            <a href="profile.php" class="btn-submit" style="text-decoration: none; font-size: 0.8rem; padding: 0.6rem 1rem;">
                                <span>MY PROFILE</span>
                                <span>🔍</span>
                            </a>
                        </div>

                        <!-- Module 2: Player Wallet & Trade -->
                        <div class="module-card">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🎒</div>
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.3rem;">
                                Nen Bank & P2P Trade
                            </h3>
                            <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.4; margin-bottom: 1rem;">
                                Manage active game pouch and execute direct in-game currency transfers between candidates.
                            </p>
                            <a href="wallet.php" class="btn-submit" style="text-decoration: none; font-size: 0.8rem; padding: 0.6rem 1rem;">
                                <span>OPEN BANK</span>
                                <span>🤝</span>
                            </a>
                        </div>

                        <!-- Module 3: Item Merchant -->
                        <div class="module-card">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🛒</div>
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.3rem;">
                                Greed Island Merchant
                            </h3>
                            <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.4; margin-bottom: 1rem;">
                                Spend in-game Jenny to unlock confidential intelligence dossiers (1,000 Ⓙ) and relics.
                            </p>
                            <a href="shop.php" class="btn-submit" style="text-decoration: none; font-size: 0.8rem; padding: 0.6rem 1rem; background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: #000;">
                                <span>OPEN SHOP</span>
                                <span>🛒</span>
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </main>

        <footer class="hunter-footer">
            <div>
                <span>HUNTER NETWORK SECTOR 10 // INNER COMMAND</span>
            </div>
            <div>
                <span>STATUS: <span style="color: var(--nen-green);">PORTAL_ONLINE</span></span>
            </div>
        </footer>
    </div>

    <script src="assets/js/audio_effects.js"></script>
    <script src="assets/js/nen_gatekeeper.js?v=<?= time() ?>"></script>
</body>
</html>
