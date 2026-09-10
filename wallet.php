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
    }
}

$activeUsername = $_SESSION['hunter_user'] ?? 'leorio_paradinight';
$activePlayer = get_user_by_username($activeUsername);

if (!$activePlayer) {
    $activeUsername = 'leorio_paradinight';
    $_SESSION['hunter_user'] = $activeUsername;
    $activePlayer = get_user_by_username($activeUsername);
}

$activeBalance = (int)($activePlayer['balance'] ?? 0);
$recentTrades = get_user_trades($activeUsername);
$allPlayers = get_all_users();
$activePage = 'wallet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HXH-C10 // Nen Bank & P2P Trade Console — <?= htmlspecialchars($activePlayer['full_name']) ?></title>
    <link rel="stylesheet" href="assets/css/hunter_style.css">
</head>
<body class="page-dashboard">
    <canvas id="nen-canvas"></canvas>

    <div class="app-wrapper">
        <?php include __DIR__ . '/nav_header.php'; ?>

        <main class="main-content" style="align-items: flex-start; padding-top: 1.5rem;">
            <div style="width: 100%; max-width: 1150px; margin: 0 auto;">
                
                <!-- Top Player Balance & Inventory Overview -->
                <div class="dashboard-panel <?= ($activeBalance > 0) ? 'highlight' : '' ?>" style="margin-bottom: 2rem;">
                    <div class="panel-title">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 2rem;"><?= htmlspecialchars($activePlayer['avatar_badge']) ?></span>
                            <div>
                                <div style="font-size: 1.3rem; font-weight: 800; color: var(--text-primary);">
                                    <?= htmlspecialchars($activePlayer['full_name']) ?>
                                </div>
                                <div style="font-family: var(--font-mono); font-size: 0.82rem; color: var(--nen-green);">
                                    @<?= htmlspecialchars($activePlayer['username']) ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <span class="license-tag" style="margin-bottom: 0;">
                                <?= htmlspecialchars($activePlayer['license_number']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-top: 1rem;">
                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-secondary); font-family: var(--font-mono);">
                                Player Affiliation
                            </div>
                            <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-primary); margin-top: 0.3rem;">
                                <?= htmlspecialchars($activePlayer['title']) ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-secondary); font-family: var(--font-mono);">
                                Nen Hatsu Type
                            </div>
                            <div style="font-size: 1.05rem; font-family: var(--font-mono); color: var(--killua-cyan); margin-top: 0.3rem;">
                                <?= htmlspecialchars($activePlayer['nen_type']) ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-secondary); font-family: var(--font-mono);">
                                Active Jenny in Pouch (Money in Hand)
                            </div>
                            <div style="font-size: 1.6rem; font-weight: 900; color: var(--hunter-gold); margin-top: 0.3rem;" id="user-balance-display">
                                <?= number_format($activeBalance) ?> <?= JENNY_SYMBOL ?>
                                <?php if ($activeBalance > 0): ?>
                                    <span style="font-size: 0.72rem; background: rgba(251,191,36,0.2); border: 1px solid var(--hunter-gold); padding: 0.2rem 0.5rem; border-radius: 4px; margin-left: 0.5rem;">
                                        💰 LIQUID FUNDS AVAILABLE
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid" style="grid-template-columns: 1.2fr 1fr; gap: 2rem;">
                    <!-- Direct P2P Transfer / Trade Form -->
                    <div class="dashboard-panel">
                        <div class="panel-title">
                            <span>🤝 P2P DIRECT JENNY TRANSFER</span>
                            <span style="color: var(--nen-green); font-family: var(--font-mono); font-size: 0.8rem;">EXECUTE_TRADE</span>
                        </div>

                        <div id="transfer-alert" style="display: none;" class="alert-box"></div>

                        <form id="wire-transfer-form">
                            <!-- SENDER (Locked to current active session user) -->
                            <div class="form-group">
                                <label for="from_user" class="form-label">
                                    <span>Source Player (Debited Account)</span>
                                </label>
                                <div class="input-container">
                                    <span class="input-icon">👤</span>
                                    <input 
                                        type="text" 
                                        id="from_user" 
                                        name="from_user" 
                                        class="form-input" 
                                        value="<?= htmlspecialchars($activeUsername) ?>" 
                                        readonly
                                        style="background: rgba(0,0,0,0.5); color: var(--nen-green); font-weight: 700;"
                                    >
                                </div>
                                <span style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono);">
                                    Active sender: @<?= htmlspecialchars($activeUsername) ?>
                                </span>
                            </div>

                            <!-- RECIPIENT -->
                            <div class="form-group">
                                <label for="to_user" class="form-label">
                                    <span>Destination Player (Recipient Hunter)</span>
                                    <span class="glyph">SELECT_CANDIDATE</span>
                                </label>
                                <div class="input-container">
                                    <span class="input-icon">🎯</span>
                                    <select 
                                        id="to_user" 
                                        name="to_user" 
                                        class="form-input" 
                                        required
                                    >
                                        <option value="" disabled>-- Select Destination Candidate --</option>
                                        <?php foreach ($allPlayers as $player): ?>
                                            <?php 
                                                $isMe = ($player['username'] === $activeUsername);
                                                $isDefaultTarget = ($activeUsername === 'milluki_zoldyck' && $player['username'] === 'leorio_paradinight');
                                            ?>
                                            <option 
                                                value="<?= htmlspecialchars($player['username']) ?>"
                                                <?= $isMe ? 'disabled' : '' ?>
                                                <?= $isDefaultTarget ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($player['avatar_badge']) ?> <?= htmlspecialchars($player['full_name']) ?> (@<?= htmlspecialchars($player['username']) ?>) <?= $isMe ? '[CURRENT SENDER]' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- AMOUNT -->
                            <div class="form-group">
                                <label for="amount" class="form-label">
                                    <span>In-Game Jenny Amount (<?= JENNY_SYMBOL ?>)</span>
                                </label>
                                <div class="input-container">
                                    <span class="input-icon">💰</span>
                                    <input 
                                        type="number" 
                                        id="amount" 
                                        name="amount" 
                                        class="form-input" 
                                        min="1" 
                                        value="<?= ($activeBalance > 0) ? $activeBalance : '1000' ?>" 
                                        required
                                    >
                                </div>
                            </div>

                            <!-- MEMO -->
                            <div class="form-group">
                                <label for="memo" class="form-label">
                                    <span>Trade Memo / Note</span>
                                </label>
                                <div class="input-container">
                                    <span class="input-icon">📝</span>
                                    <input 
                                        type="text" 
                                        id="memo" 
                                        name="memo" 
                                        class="form-input" 
                                        value="Greed Island P2P Fund Transfer" 
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn-submit" id="btn-transfer-submit">
                                <span>SEND <?= JENNY_SYMBOL ?> AS @<?= htmlspecialchars($activeUsername) ?></span>
                                <span>⚡</span>
                            </button>
                        </form>
                    </div>

                    <!-- Trade Log & Quick Navigation -->
                    <div class="dashboard-panel">
                        <div class="panel-title">
                            <span>📜 PLAYER TRADE ACTIVITY</span>
                            <span style="color: var(--text-muted); font-family: var(--font-mono); font-size: 0.78rem;">@<?= htmlspecialchars($activeUsername) ?></span>
                        </div>

                        <div id="tx-history-container">
                            <?php if (empty($recentTrades)): ?>
                                <div style="color: var(--text-muted); font-family: var(--font-mono); font-size: 0.85rem; padding: 1.5rem 1rem; text-align: center;">
                                    No P2P trades recorded yet for @<?= htmlspecialchars($activeUsername) ?>.
                                </div>
                            <?php else: ?>
                                <div class="tx-list">
                                    <?php foreach ($recentTrades as $tx): ?>
                                        <div class="tx-item">
                                            <div class="tx-header">
                                                <span class="tx-users"><?= htmlspecialchars($tx['from_user']) ?> ➔ <?= htmlspecialchars($tx['to_user']) ?></span>
                                                <span class="tx-amount <?= ($tx['to_user'] === $activeUsername) ? 'credit' : 'debit' ?>">
                                                    <?= ($tx['to_user'] === $activeUsername) ? '+' : '-' ?><?= number_format($tx['amount']) ?> <?= JENNY_SYMBOL ?>
                                                </span>
                                            </div>
                                            <div class="tx-meta">
                                                <span><?= htmlspecialchars($tx['memo']) ?></span>
                                                <span><?= htmlspecialchars($tx['timestamp']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px dashed rgba(255,255,255,0.08); display: flex; flex-direction: column; gap: 0.75rem;">
                            <a href="dashboard.php#hunter-directory" class="btn-table-action" style="text-align: center; justify-content: center; padding: 0.7rem;">
                                <span>← Back to Hunter Roster</span>
                            </a>
                            <a href="shop.php" class="btn-submit" style="text-decoration: none; background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: #000; font-size: 0.85rem; padding: 0.75rem 1rem;">
                                <span>Visit Item Merchant (Spend Jenny)</span>
                                <span>🛒</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <footer class="hunter-footer">
            <div>
                <span>HUNTER NETWORK // USER WALLET CONSOLE</span>
            </div>
            <div>
                <span>ACTIVE: <span style="color: var(--nen-green);">@<?= htmlspecialchars($activeUsername) ?></span></span>
            </div>
        </footer>
    </div>

    <script src="assets/js/audio_effects.js"></script>
    <script src="assets/js/nen_gatekeeper.js?v=<?= time() ?>"></script>
    <script>
        document.getElementById('wire-transfer-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-transfer-submit');
            const alertBox = document.getElementById('transfer-alert');
            const fromUser = document.getElementById('from_user').value.trim();
            const toUser = document.getElementById('to_user').value.trim();
            const amount = parseInt(document.getElementById('amount').value);
            const memo = document.getElementById('memo').value.trim();

            btn.disabled = true;
            btn.innerHTML = '<span>EXECUTING P2P TRADE...</span>';
            alertBox.style.display = 'none';

            try {
                const response = await fetch('api/transfer.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        from_user: fromUser,
                        to_user: toUser,
                        amount: amount,
                        memo: memo
                    })
                });

                const data = await response.json();

                alertBox.style.display = 'flex';
                if (data.success) {
                    alertBox.className = 'alert-box alert-success';
                    alertBox.innerHTML = `<span>✅ ${data.message}</span>`;
                    if (window.HunterAudio) window.HunterAudio.playSuccess();
                    setTimeout(() => {
                        location.reload();
                    }, 1200);
                } else {
                    alertBox.className = 'alert-box alert-error';
                    alertBox.innerHTML = `<span>❌ ${data.message || data.error}</span>`;
                    if (window.HunterAudio) window.HunterAudio.playError();
                }
            } catch (err) {
                alertBox.style.display = 'flex';
                alertBox.className = 'alert-box alert-error';
                alertBox.innerHTML = '<span>⛔ Error transmitting in-game trade.</span>';
                if (window.HunterAudio) window.HunterAudio.playError();
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<span>SEND <?= JENNY_SYMBOL ?> AS @${fromUser}</span><span>⚡</span>`;
            }
        });
    </script>
</body>
</html>
