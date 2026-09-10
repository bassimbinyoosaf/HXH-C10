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
$activePage = 'shop';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HXH-C10 // Greed Island Spell & Clue Merchant</title>
    <link rel="stylesheet" href="assets/css/hunter_style.css">
</head>
<body class="page-dashboard">
    <canvas id="nen-canvas"></canvas>

    <div class="app-wrapper">
        <?php include __DIR__ . '/nav_header.php'; ?>

        <main class="main-content" style="align-items: flex-start; padding-top: 1.5rem;">
            <div style="width: 100%; max-width: 1100px; margin: 0 auto;">
                
                <!-- Header Banner -->
                <div class="dashboard-panel" style="margin-bottom: 1.5rem;">
                    <div class="panel-title">
                        <span>🛒 GREED ISLAND SPELL CARD & CLUE MERCHANT</span>
                        <span style="color: var(--hunter-gold); font-family: var(--font-mono); font-size: 0.85rem;">
                            POUCH: <strong id="user-wallet-val"><?= number_format($myBalance) ?> <?= JENNY_SYMBOL ?></strong>
                        </span>
                    </div>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6;">
                        Rare Greed Island spell cards, confidential Hunter intelligence dossiers, and expedition relics traded directly using in-game Jenny (Ⓙ).
                    </p>
                </div>

                <?php if ($currentUser === 'milluki_zoldyck'): ?>
                    <!-- Zoldyck Merchant Blacklist Warning -->
                    <div class="alert-box alert-error" style="margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem; border-left: 4px solid #ef4444; background: rgba(239, 68, 68, 0.12);">
                        <span style="font-size: 1.6rem;">⛔</span>
                        <div>
                            <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 0.25rem; color: #fca5a5;">
                                DIRECT MERCHANT PROCUREMENT RESTRICTED FOR @milluki_zoldyck
                            </div>
                            <div style="font-size: 0.85rem; line-height: 1.5; color: rgba(255,255,255,0.85);">
                                Operatives registered under the <strong>Zoldyck Family Estate</strong> are strictly blacklisted from purchasing Hunter Association intelligence dossiers directly.
                                <div style="margin-top: 0.4rem; color: #93c5fd;">
                                    💡 <em>You must transfer your <strong>10,000 Ⓙ</strong> via the <strong><a href="wallet.php" style="color: #60a5fa; text-decoration: underline; font-weight: 700;">Bank portal</a></strong> to an authorized candidate (e.g. <strong>@leorio_paradinight</strong>), then purchase the clue under their profile.</em>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="shop-alert" style="display: none;" class="alert-box"></div>

                <!-- Shop Items Grid -->
                <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 2rem;">
                    
                    <!-- Item 1: Standard Clue (1,000 Jenny) -->
                    <div class="dashboard-panel <?= in_array('clue_standard', $purchased) ? 'highlight' : '' ?>">
                        <div class="panel-title">
                            <span>📜 STAGE 2 EXPEDITION CLUE</span>
                            <span style="color: var(--nen-green); font-family: var(--font-mono); font-size: 0.82rem;">1,000 <?= JENNY_SYMBOL ?></span>
                        </div>
                        
                        <p style="color: var(--text-secondary); font-size: 0.88rem; line-height: 1.5; margin-bottom: 1.25rem;">
                            Classified intelligence regarding Greed Island spell mechanics, P2P player trade loopholes, and the Stage 2 flag.
                        </p>

                        <div class="prop-row">
                            <span class="prop-label">Item Codename:</span>
                            <span class="prop-value">greed_island_intel_dossier</span>
                        </div>
                        <div class="prop-row">
                            <span class="prop-label">In-Game Price:</span>
                            <span class="prop-value" style="color: var(--hunter-gold);">1,000 <?= JENNY_SYMBOL ?></span>
                        </div>
                        <div class="prop-row">
                            <span class="prop-label">Status:</span>
                            <span class="prop-value" id="status-clue-standard">
                                <?= in_array('clue_standard', $purchased) ? '✅ UNLOCKED' : ($currentUser === 'milluki_zoldyck' ? '⛔ BLACKLISTED BUYER' : '🔒 LOCKED (REQUIRES IN-GAME JENNY)') ?>
                            </span>
                        </div>

                        <div id="content-clue-standard" style="margin-top: 1.25rem; display: none;">
                            <div class="terminal-box selectable-flag" style="white-space: pre-wrap; font-size: 0.8rem; color: #6ee7b7;"></div>
                        </div>

                        <?php if ($currentUser === 'milluki_zoldyck'): ?>
                            <div style="margin-top: 1.5rem; background: rgba(239, 68, 68, 0.12); border: 1px dashed rgba(239, 68, 68, 0.4); padding: 0.85rem; border-radius: 8px; text-align: center; color: #fca5a5; font-family: var(--font-mono); font-size: 0.8rem;">
                                ⛔ PROCUREMENT BLACKLISTED
                                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.25rem;">Transfer funds to @leorio_paradinight via Bank first</div>
                            </div>
                        <?php elseif (!in_array('clue_standard', $purchased)): ?>
                            <button 
                                class="btn-submit" 
                                style="margin-top: 1.5rem;" 
                                onclick="purchaseClue('clue_standard', this)"
                            >
                                <span>BUY CLUE FOR 1,000 Ⓙ</span>
                                <span>⚡</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Item 2: Master Clue (20,000 Jenny) -->
                    <div class="dashboard-panel <?= in_array('clue_master', $purchased) ? 'highlight' : '' ?>">
                        <div class="panel-title">
                            <span>👑 DARK CONTINENT MASTER CLUE</span>
                            <span style="color: var(--hunter-gold); font-family: var(--font-mono); font-size: 0.82rem;">20,000 <?= JENNY_SYMBOL ?></span>
                        </div>
                        
                        <p style="color: var(--text-secondary); font-size: 0.88rem; line-height: 1.5; margin-bottom: 1.25rem;">
                            The legendary artifact recovered from the Uncharted Waters. Requires an immense fortune of 20,000 Jenny that no single registered player currently possesses in their pouch.
                        </p>

                        <div class="prop-row">
                            <span class="prop-label">Item Codename:</span>
                            <span class="prop-value gold">dark_continent_master_relic</span>
                        </div>
                        <div class="prop-row">
                            <span class="prop-label">In-Game Price:</span>
                            <span class="prop-value gold">20,000 <?= JENNY_SYMBOL ?></span>
                        </div>
                        <div class="prop-row">
                            <span class="prop-label">Status:</span>
                            <?php 
                            $isStage2Verified = isset($_SESSION['stage2_flag_verified']) && $_SESSION['stage2_flag_verified'] === true;
                            $masterStatusText = '🔒 LOCKED (INSUFFICIENT IN-GAME POUCH)';
                            $masterStatusColor = '#f87171';
                            if (in_array('clue_master', $purchased)) {
                                $masterStatusText = '✅ UNLOCKED';
                                $masterStatusColor = 'var(--nen-green)';
                            } elseif ($currentUser === 'milluki_zoldyck') {
                                $masterStatusText = '⛔ BLACKLISTED BUYER';
                                $masterStatusColor = '#f87171';
                            } elseif (!$isStage2Verified) {
                                $masterStatusText = '🔒 LOCKED (AUTHENTICATE STAGE 2 FLAG IN GATEWAY FIRST)';
                                $masterStatusColor = '#f87171';
                            } elseif ($myBalance >= 20000) {
                                $masterStatusText = '⚡ READY FOR PROCUREMENT (' . number_format($myBalance) . ' Ⓙ IN POUCH)';
                                $masterStatusColor = 'var(--hunter-gold)';
                            }
                            ?>
                            <span class="prop-value" id="status-clue-master" style="color: <?= $masterStatusColor ?>;">
                                <?= $masterStatusText ?>
                            </span>
                        </div>

                        <div id="content-clue-master" style="margin-top: 1.25rem; display: none;">
                            <div class="terminal-box selectable-flag" style="white-space: pre-wrap; font-size: 0.8rem; color: #fbbf24;"></div>
                        </div>

                        <?php if ($currentUser === 'milluki_zoldyck'): ?>
                            <div style="margin-top: 1.5rem; background: rgba(239, 68, 68, 0.12); border: 1px dashed rgba(239, 68, 68, 0.4); padding: 0.85rem; border-radius: 8px; text-align: center; color: #fca5a5; font-family: var(--font-mono); font-size: 0.8rem;">
                                ⛔ PROCUREMENT BLACKLISTED
                                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.25rem;">Transfer funds to @leorio_paradinight via Bank first</div>
                            </div>
                        <?php elseif (!in_array('clue_master', $purchased)): ?>
                            <button 
                                class="btn-submit" 
                                style="margin-top: 1.5rem; background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: #000;" 
                                onclick="purchaseClue('clue_master', this)"
                            >
                                <span>ATTEMPT MASTER PURCHASE (20,000 Ⓙ)</span>
                                <span>🗝️</span>
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </main>

        <footer class="hunter-footer">
            <div>
                <span>GREED ISLAND MERCHANT // IN-GAME ITEM EXCHANGE</span>
            </div>
            <div>
                <span>STATUS: <span style="color: var(--hunter-gold);">MARKET_ONLINE</span></span>
            </div>
        </footer>
    </div>

    <script src="assets/js/audio_effects.js"></script>
    <script src="assets/js/nen_gatekeeper.js?v=<?= time() ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const purchasedItems = <?= json_encode(array_values($purchased)) ?>;
            purchasedItems.forEach(async (clueId) => {
                try {
                    const res = await fetch(`api/get_clue.php?clue_id=${encodeURIComponent(clueId)}`);
                    const data = await res.json();
                    if (data.success && data.clue_content) {
                        const contentBox = document.getElementById(`content-${clueId.replace('_', '-')}`);
                        if (contentBox) {
                            const termBox = contentBox.querySelector('.terminal-box');
                            if (termBox) termBox.innerText = data.clue_content;
                            contentBox.style.display = 'block';
                        }
                    }
                } catch (e) {}
            });
        });

        async function purchaseClue(clueId, btn) {
            const alertBox = document.getElementById('shop-alert');
            alertBox.style.display = 'none';
            btn.disabled = true;

            try {
                const response = await fetch('api/buy_clue.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ clue_id: clueId })
                });

                const data = await response.json();
                alertBox.style.display = 'flex';

                if (data.success) {
                    alertBox.className = 'alert-box alert-success';
                    alertBox.innerHTML = `<span>✅ ${data.message}</span>`;
                    
                    const newBalFormatted = `${Number(data.new_balance).toLocaleString()} <?= JENNY_SYMBOL ?>`;
                    const walletValEl = document.getElementById('user-wallet-val');
                    if (walletValEl) {
                        walletValEl.innerText = newBalFormatted;
                    }
                    
                    const contentBox = document.getElementById(`content-${clueId.replace('_', '-')}`);
                    if (contentBox) {
                        if (data.clue_content) {
                            const termBox = contentBox.querySelector('.terminal-box');
                            if (termBox) termBox.innerText = data.clue_content;
                        }
                        contentBox.style.display = 'block';
                    }
                    const statusEl = document.getElementById(`status-${clueId.replace('_', '-')}`);
                    if (statusEl) {
                        statusEl.innerText = '✅ UNLOCKED';
                        statusEl.className = 'prop-value';
                        statusEl.style.color = 'var(--nen-green)';
                    }
                    btn.style.display = 'none';

                    if (window.HunterAudio) window.HunterAudio.playSuccess();
                } else {
                    alertBox.className = 'alert-box alert-error';
                    alertBox.innerHTML = `<span>❌ ${data.message || data.error}</span>`;
                    if (window.HunterAudio) window.HunterAudio.playError();
                }
            } catch (err) {
                alertBox.style.display = 'flex';
                alertBox.className = 'alert-box alert-error';
                alertBox.innerHTML = '<span>⛔ Error communicating with the Merchant.</span>';
                if (window.HunterAudio) window.HunterAudio.playError();
            } finally {
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
