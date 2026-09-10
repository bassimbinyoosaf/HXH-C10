<?php
require_once __DIR__ . '/config.php';
require_auth();

// Security Headers to prevent sniffing/caching of classified intelligence
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$currentUser = $_SESSION['hunter_user'] ?? 'leorio_paradinight';
$myBalance = get_user_balance($currentUser);
$purchased = get_user_purchases($currentUser);
$isUnlocked = in_array('clue_standard', $purchased);
$isMasterUnlocked = in_array('clue_master', $purchased) || (isset($_SESSION['stage3_master_unlocked']) && $_SESSION['stage3_master_unlocked'] === true);
$activePage = 'gateway';

// If clue is not unlocked, return HTTP 403 Forbidden
if (!$isUnlocked) {
    http_response_code(403);
}

// Handle Flag Submission
$flagSubmitted = false;
$flagSuccess = false;
$submittedFlag = '';
$errorMessage = '';

if ($isUnlocked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedFlag = trim($_POST['stage_flag'] ?? '');
    $flagSubmitted = true;
    
    if ($submittedFlag === FLAG_STAGE2) {
        $flagSuccess = true;
        $_SESSION['stage2_flag_verified'] = true;
    } else {
        $errorMessage = '⛔ [INVALID CIPHER] Incorrect Flag submitted. Please enter the exact flag obtained from the Stage 2 Intelligence Dossier.';
    }
}

$alreadyVerified = isset($_SESSION['stage2_flag_verified']) && $_SESSION['stage2_flag_verified'] === true;

// Issue / Maintain digitized Greed Island Smart Ring Pouch cookie upon Gateway Flag Verification
if ($flagSuccess) {
    // Initial Spawn of digitized Smart Ring Pouch cookie (0 initial bonus)
    $pouchData = [
        'user' => $currentUser,
        'balance' => 0,
        'role' => 'hunter_candidate',
        'smart_ring' => 'GREED_ISLAND_GATEWAY_SYNC_V1',
        'tamper_proof' => 'false'
    ];
    $pouchToken = base64_encode(json_encode($pouchData));
    if (!headers_sent()) {
        setcookie('hunter_pouch', $pouchToken, [
            'expires' => time() + 86400,
            'path' => '/',
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
    }
    $_COOKIE['hunter_pouch'] = $pouchToken;
} elseif ($alreadyVerified) {
    if (!isset($_COOKIE['hunter_pouch'])) {
        $pouchData = [
            'user' => $currentUser,
            'balance' => 0,
            'role' => 'hunter_candidate',
            'smart_ring' => 'GREED_ISLAND_GATEWAY_SYNC_V1',
            'tamper_proof' => 'false'
        ];
        $pouchToken = base64_encode(json_encode($pouchData));
        if (!headers_sent()) {
            setcookie('hunter_pouch', $pouchToken, [
                'expires' => time() + 86400,
                'path' => '/',
                'httponly' => false,
                'samesite' => 'Lax'
            ]);
        }
        $_COOKIE['hunter_pouch'] = $pouchToken;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isUnlocked ? 'HXH-C10 // Greed Island Expedition Gateway' : '403 Forbidden // Gateway Locked' ?></title>
    <link rel="stylesheet" href="assets/css/hunter_style.css">
    <script>
        // Inline immediate protection guard against source inspection
        document.addEventListener('contextmenu', e => { e.preventDefault(); return false; });
        document.addEventListener('keydown', e => {
            const k = e.key.toLowerCase();
            if (e.keyCode === 123 || ( (e.ctrlKey || e.metaKey) && (['u', 's', 'p'].includes(k) || ((e.shiftKey || e.altKey) && ['i', 'j', 'c', 'k', 'e'].includes(k))) )) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof showNenSecurityToast === 'function') {
                    showNenSecurityToast("⛔ [NEN ENCRYPTION BARRIER] Source code inspection is blocked on classified pages!");
                }
                return false;
            }
        }, true);
    </script>
</head>
<body class="page-dashboard page-flag-protected">
    <canvas id="nen-canvas"></canvas>

    <div class="app-wrapper">
        <?php include __DIR__ . '/nav_header.php'; ?>

        <main class="main-content" style="align-items: flex-start; padding-top: 1.5rem;">
            <div style="width: 100%; max-width: 900px; margin: 0 auto;">

                <?php if (!$isUnlocked): ?>
                    <!-- 403 Forbidden: Gateway Locked -->
                    <div class="dashboard-panel" style="border-color: #ef4444; box-shadow: 0 0 25px rgba(239, 68, 68, 0.2);">
                        <div class="panel-title">
                            <span style="color: #f87171;">⛔ 403 FORBIDDEN // GATEWAY ACCESS DENIED</span>
                            <span style="color: #f87171; font-family: var(--font-mono); font-size: 0.8rem; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); padding: 0.2rem 0.5rem; border-radius: 4px;">
                                CLEARANCE_REQUIRED
                            </span>
                        </div>

                        <div class="alert-box alert-error" style="margin: 1.25rem 0;">
                            <span>🚫 <strong>[ACCESS REJECTED]</strong> The Greed Island Gateway portal is protected by a Nen forcefield. Candidate <code>@<?= htmlspecialchars($currentUser) ?></code> does not hold the required intelligence clearance.</span>
                        </div>

                        <div class="terminal-box" style="font-size: 0.85rem; line-height: 1.6; color: #fca5a5; border-left: 3px solid #ef4444;">
                            <span style="color: #ef4444; font-weight: 700;">[SECURITY FIREWALL LOG]</span><br>
                            HTTP Status: 403 Forbidden<br>
                            Required Item: <code>greed_island_intel_dossier</code> (Stage 2 Clue)<br>
                            Authorization: MISSING // Clue not yet purchased from the Item Merchant.
                        </div>

                        <p style="color: var(--text-secondary); font-size: 0.88rem; margin: 1.25rem 0; line-height: 1.5;">
                            To unlock this Gateway, visit the <strong>Shop</strong>, acquire the necessary funds through the P2P Bank exchange, and purchase the <strong>Stage 2 Expedition Clue (1,000 Ⓙ)</strong>.
                        </p>

                        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                            <a href="shop.php" class="btn-submit" style="text-decoration: none; padding: 0.8rem 1.4rem; background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: #000; font-size: 0.88rem;">
                                <span>Visit Shop & Unlock Clue (1,000 Ⓙ)</span>
                                <span>🛒</span>
                            </a>
                            <a href="dashboard.php" class="nav-link-btn" style="padding: 0.8rem 1.2rem; display: flex; align-items: center;">
                                <span>← Back to Dashboard</span>
                            </a>
                        </div>
                    </div>

                <?php else: ?>

                    <?php if ($isMasterUnlocked): ?>
                        <!-- FINAL VICTORY: DARK CONTINENT MASTER PORTAL AUTO-UNLOCKED VIA SECOND CLUE -->
                        <div class="dashboard-panel highlight" style="background: linear-gradient(135deg, rgba(251, 191, 36, 0.14) 0%, rgba(13, 22, 17, 0.95) 100%); border: 2px solid var(--hunter-gold); box-shadow: 0 0 40px rgba(251, 191, 36, 0.35); margin-bottom: 2rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                                <span style="font-size: 2.5rem;">👑</span>
                                <div>
                                    <h2 style="color: var(--hunter-gold); font-size: 1.4rem; font-weight: 800; margin: 0; text-transform: uppercase;">
                                        GATEWAY AUTO-OVERRIDE ACTIVE // REAL MASTER FLAG
                                    </h2>
                                    <span style="font-family: var(--font-mono); font-size: 0.82rem; color: #fde68a;">
                                        ⚡ DARK CONTINENT MASTER KEY NEN PULSE SYNCHRONIZED
                                    </span>
                                </div>
                            </div>

                            <div class="alert-box alert-success" style="margin-bottom: 1.25rem; background: rgba(251, 191, 36, 0.15); border-color: var(--hunter-gold); color: #fef08a;">
                                <span>🗝️ <strong>[AUTO-UNLOCK COMPLETE]</strong> The Ancient Key acquired from the Item Merchant has remotely breached the deepest Gateway vault!</span>
                            </div>

                            <div class="terminal-box selectable-flag" style="white-space: pre-wrap; font-size: 0.85rem; color: #fbbf24; line-height: 1.6; border-left: 4px solid var(--hunter-gold);">========================================================================
  🌌 DARK CONTINENT EXPEDITION // REAL MASTER FLAG BROADCAST
========================================================================
[+] SYSTEM STATUS: CORE CHAMBER AUTO-UNLOCKED VIA MASTER KEY
[+] CANDIDATE AUTHENTICATED: @<?= htmlspecialchars($currentUser) ?> (Triple-Star Pioneer)
[+] REMOTE TRIGGER: Verified against Greed Island Relay Node

[+] 🏆 REAL FINAL MASTER FLAG:
    <?= FLAG_STAGE3 . "\n" ?>
------------------------------------------------------------------------
[*] CONGRATULATIONS HUNTER! You have conquered all stages of HXH-C10!
    • Stage 1: Source Recon & Archive Dump Discovery
    • Stage 2: Broken Object-Level Authorization (IDOR) & P2P Fund Drain
    • Stage 3: Client-Side Cookie State Tampering & Remote Gateway Core Breach
========================================================================</div>
                        </div>
                    <?php endif; ?>

                    <!-- 200 OK: Gateway Unlocked -->
                    <div class="dashboard-panel highlight" style="margin-bottom: 2rem;">
                        <div class="panel-title">
                            <span style="color: var(--nen-green);">🌌 GREED ISLAND EXPEDITION GATEWAY</span>
                            <span style="color: var(--nen-green); font-family: var(--font-mono); font-size: 0.8rem; background: rgba(0,255,136,0.15); border: 1px solid var(--nen-green); padding: 0.2rem 0.5rem; border-radius: 4px;">
                                200 OK // UNLOCKED 🌟
                            </span>
                        </div>

                        <div class="alert-box alert-success" style="margin: 1.25rem 0;">
                            <span>✅ <strong>[CLEARANCE VERIFIED]</strong> Stage 2 Clue confirmed in candidate inventory. Greed Island Portal transmission channel is fully energized!</span>
                        </div>

                        <!-- Flag Authentication Form & Gateway Password Verification -->
                        <?php if ($flagSuccess || $alreadyVerified): ?>
                            <!-- Success Screen: Well Done & Next Flag Directive -->
                            <div class="dashboard-panel" style="background: rgba(0, 255, 136, 0.08); border: 2px solid var(--nen-green); box-shadow: 0 0 30px rgba(0,255,136,0.25); margin: 1.5rem 0;">
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                                    <span style="font-size: 2.2rem;">🏆</span>
                                    <div>
                                        <h3 style="color: var(--nen-green); font-size: 1.35rem; font-weight: 800; margin: 0;">
                                            WELL DONE, HUNTER!
                                        </h3>
                                        <span style="font-family: var(--font-mono); font-size: 0.82rem; color: #a7f3d0;">
                                            STAGE 2 CIPHER VERIFIED: <code class="selectable-flag" style="color: #fff; font-weight: 700;"><?= htmlspecialchars(FLAG_STAGE2) ?></code>
                                        </span>
                                    </div>
                                </div>

                                <div class="terminal-box" style="white-space: pre-wrap; font-size: 0.85rem; color: #6ee7b7; line-height: 1.6; border-left: 3px solid var(--hunter-gold);">========================================================================
  🌟 GATEWAY AUTHENTICATION SUCCESSFUL // STAGE 2 COMPLETED
========================================================================
[+] VERIFIED HUNTER: @<?= htmlspecialchars($currentUser) ?> (License Clearance Validated)
[+] EXPLOIT ACCOMPLISHED: Milluki Zoldyck's P2P Bank Pouch Drained via IDOR
[+] STAGE 1 & 2 CLEARANCE RECORDED!
------------------------------------------------------------------------
[*] GREED ISLAND SMART RING // SYSTEM SYNCHRONIZATION:
    To prepare for the expedition, your physical assets have been converted
    into a digitized Nen seal bound directly to your local vessel.
    The transmission frequency is encoded for candidate transport.

[*] EXPEDITION RECON DIRECTIVE:
    The Item Merchant in sector shop.php holds the legendary
    Dark Continent Master Relic priced at 20,000 Ⓙ.
    However, the entire network's circulating Jenny cannot meet this demand.

    Inspect the local seal bestowed upon your vessel. If a Hunter can
    decipher and reshape their digitized aura reserves, the unreachable
    artifact will be yours.
========================================================================</div>

                                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                                    <a href="dashboard.php" class="nav-link-btn" style="padding: 0.8rem 1.2rem; display: flex; align-items: center;">
                                        <span>← Back to Dashboard</span>
                                    </a>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- Flag Input Password Field Form -->
                            <div class="dashboard-panel" style="background: rgba(13,22,17,0.85); border: 1px solid var(--border-color); margin: 1.5rem 0;">
                                <div class="panel-title">
                                    <span>🔐 GATEWAY PORTAL CIPHER AUTHENTICATION</span>
                                    <span style="color: var(--hunter-gold); font-family: var(--font-mono); font-size: 0.8rem;">FLAG REQUIRED</span>
                                </div>

                                <p style="color: var(--text-secondary); font-size: 0.88rem; line-height: 1.6; margin-bottom: 1.25rem;">
                                    Please enter the <strong>First Flag</strong> received from the Stage 2 Intelligence Dossier clue to authenticate your clearance and unlock the next stage directive.
                                </p>

                                <?php if (!empty($errorMessage)): ?>
                                    <div class="alert-box alert-error" style="margin-bottom: 1.25rem;">
                                        <span><?= htmlspecialchars($errorMessage) ?></span>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="gateway.php" style="display: flex; flex-direction: column; gap: 1rem;">
                                    <div>
                                        <label for="stage_flag" style="display: block; font-family: var(--font-mono); font-size: 0.82rem; color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">
                                            🔑 STAGE 2 FLAG CIPHER:
                                        </label>
                                        <div style="position: relative;">
                                            <input 
                                                type="password" 
                                                id="stage_flag" 
                                                name="stage_flag" 
                                                class="form-input" 
                                                placeholder="Enter First Flag (SSP{...})" 
                                                required 
                                                autocomplete="off"
                                                style="width: 100%; font-family: var(--font-mono); letter-spacing: 0.05em; padding-right: 3rem;"
                                            >
                                            <button 
                                                type="button" 
                                                onclick="togglePasswordVisibility()" 
                                                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.1rem; color: var(--text-muted);"
                                                title="Toggle Visibility"
                                            >
                                                👁️
                                            </button>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-submit" style="margin-top: 0.5rem; font-size: 0.9rem; padding: 0.85rem 1.5rem; font-weight: 800;">
                                        <span>AUTHENTICATE FLAG & UNLOCK PORTAL</span>
                                        <span>⚡</span>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- JoyStation Console Status -->
                        <div style="margin-top: 1.75rem;">
                            <h4 style="font-size: 0.9rem; text-transform: uppercase; color: var(--text-primary); margin-bottom: 0.75rem; font-family: var(--font-mono);">
                                🎮 JoyStation Greed Island Console Status:
                            </h4>
                            <div class="prop-row">
                                <span class="prop-label">Expedition Candidate:</span>
                                <span class="prop-value" style="color: var(--nen-green);">@<?= htmlspecialchars($currentUser) ?></span>
                            </div>
                            <div class="prop-row">
                                <span class="prop-label">Pouch Reserves:</span>
                                <span class="prop-value gold"><?= number_format($myBalance) ?> <?= JENNY_SYMBOL ?></span>
                            </div>
                            <div class="prop-row">
                                <span class="prop-label">Portal State:</span>
                                <span class="prop-value" style="color: var(--nen-green); font-weight: 800;">
                                    <?= ($flagSuccess || $alreadyVerified) ? 'PORTAL ACTIVE // READY FOR INSERTION 🚀' : 'AWAITING CIPHER AUTHENTICATION 🔒' ?>
                                </span>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>

            </div>
        </main>

        <footer class="hunter-footer">
            <div>
                <span>GREED ISLAND PORTAL // GATEWAY GATEKEEPER</span>
            </div>
            <div>
                <span>STATUS: <span style="color: <?= $isUnlocked ? 'var(--nen-green)' : '#ef4444' ?>;"><?= $isUnlocked ? 'ONLINE' : 'LOCKED' ?></span></span>
            </div>
        </footer>
    </div>

    <script src="assets/js/audio_effects.js"></script>
    <script src="assets/js/nen_gatekeeper.js?v=<?= time() ?>"></script>
    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('stage_flag');
            if (input) {
                input.type = (input.type === 'password') ? 'text' : 'password';
            }
        }
    </script>
</body>
</html>
