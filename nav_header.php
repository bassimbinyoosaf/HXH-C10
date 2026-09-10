<?php
/**
 * HXH-C10 // Reusable Navbar & Subdomain Menubar Component
 */
if (!defined('JENNY_SYMBOL')) {
    require_once __DIR__ . '/config.php';
}

$activePage = $activePage ?? '';
$sessionUser = $_SESSION['hunter_user'] ?? 'Unknown Hunter';
$headerBalance = get_user_balance($sessionUser);
?>
<!-- Top Navigation Header -->
<header class="hunter-navbar">
    <a href="dashboard.php" class="brand-logo">
        <img src="assets/images/logo.png" alt="Hunter × Hunter Logo" class="hunter-logo-img">
        <span class="brand-title">HUNTER NETWORK <span>// SECTOR 10</span></span>
    </a>
    <div class="nav-links" style="display: flex; gap: 1.25rem; align-items: center;">
        <span class="nav-status">
            <span class="status-dot"></span>
            <span>LOGGED AS: <strong style="color: var(--nen-green);">@<?= htmlspecialchars($sessionUser) ?></strong></span>
        </span>
        <span class="nav-status" style="background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.3); padding: 0.35rem 0.75rem; border-radius: var(--radius-sm);">
            <span style="color: var(--hunter-gold);">💰 IN-HAND: <strong><?= number_format($headerBalance) ?> <?= JENNY_SYMBOL ?></strong></span>
        </span>
        <a href="logout.php" style="color: #f87171; text-decoration: none; font-family: var(--font-mono); font-size: 0.8rem; border: 1px solid rgba(239,68,68,0.3); padding: 0.35rem 0.75rem; border-radius: var(--radius-sm); transition: all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.15)'" onmouseout="this.style.background='transparent'">
            LOGOUT 🚪
        </a>
    </div>
</header>

<!-- Menubar Navigation -->
<nav class="subdomain-menubar-wrapper" aria-label="Portal Navigation">
    <div class="subdomain-menubar">
        <a href="dashboard.php" class="subdomain-tab <?= ($activePage === 'dashboard') ? 'active' : '' ?>">
            <span class="tab-icon">🏠</span>
            <span class="tab-label">Home</span>
        </a>

        <a href="profile.php" class="subdomain-tab <?= ($activePage === 'profile') ? 'active' : '' ?>">
            <span class="tab-icon">👤</span>
            <span class="tab-label">Profile</span>
        </a>

        <a href="wallet.php" class="subdomain-tab <?= ($activePage === 'wallet') ? 'active' : '' ?>">
            <span class="tab-icon">💳</span>
            <span class="tab-label">Bank</span>
        </a>

        <a href="shop.php" class="subdomain-tab <?= ($activePage === 'shop') ? 'active' : '' ?>">
            <span class="tab-icon">🛒</span>
            <span class="tab-label">Shop</span>
        </a>
    </div>
</nav>

