<?php
/**
 * HXH-C10: Database Connection & Game Persistence Layer
 * First-class MySQL / MariaDB PDO with robust automatic fallback
 */

define('DATA_DIR', __DIR__ . '/data');
define('JSON_FALLBACK_PATH', DATA_DIR . '/hunter_game_store.json');

function get_db_handle() {
    static $handle = null;
    if ($handle !== null) {
        return $handle;
    }

    // 1. First-Class MySQL PDO Connection
    if (extension_loaded('pdo_mysql')) {
        $host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
        $port = getenv('DB_PORT') ?: (defined('DB_PORT') ? DB_PORT : '3306');
        $name = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : 'hxh_c10_db');
        $user = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : 'root');
        $pass = getenv('DB_PASS') ?: (defined('DB_PASS') ? DB_PASS : '');

        try {
            // Attempt to connect to MySQL server
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 2
            ]);

            // Ensure database exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$name}`");

            // Check if users table exists, if not initialize schema
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() === 0) {
                $sqlPath = __DIR__ . '/database.sql';
                if (file_exists($sqlPath)) {
                    $sql = file_get_contents($sqlPath);
                    $pdo->exec($sql);
                }
            }

            $handle = ['type' => 'mysql', 'pdo' => $pdo];
            return $handle;
        } catch (Throwable $e) {
            // MySQL unavailable locally; proceed to standalone fallback
        }
    }

    // 2. Standalone JSON Game Storage Fallback
    $handle = ['type' => 'json'];
    return $handle;
}

// -----------------------------------------------------------------------------
// Session-Scoped Multi-Tenant Game State (100% Collision-Free for All Players)
// -----------------------------------------------------------------------------

function init_session_game_state() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    if (!isset($_SESSION['player_balances']) || !is_array($_SESSION['player_balances'])) {
        $_SESSION['player_balances'] = [
            'leorio_paradinight' => 0,
            'gon_freecss' => 0,
            'killua_zoldyck' => 0,
            'kurapika_kurta' => 0,
            'milluki_zoldyck' => 10000,
            'hisoka_morow' => 0,
            'tonpa_crusher' => 0,
            'hanzo_ninja' => 0,
            'pokkle_archer' => 0,
            'ponzu_bees' => 0,
            'illumi_zoldyck' => 0,
            'chrollo_lucilfer' => 0,
            'feitan_portor' => 0,
            'machi_komacine' => 0,
            'zepile_appraiser' => 0,
        ];
    }
    if (!isset($_SESSION['player_trades']) || !is_array($_SESSION['player_trades'])) {
        $_SESSION['player_trades'] = [];
    }
    if (!isset($_SESSION['purchased_items']) || !is_array($_SESSION['purchased_items'])) {
        $_SESSION['purchased_items'] = [];
    }
}

function get_all_users() {
    init_session_game_state();
    $db = get_db_handle();
    $users = [];
    if ($db['type'] === 'mysql') {
        $stmt = $db['pdo']->query("SELECT username, full_name, title, nen_type, license_number, avatar_badge, bio, created_at FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll();
    } else {
        $store = load_json_store();
        $users = array_values($store['users']);
    }

    // Overlay session-isolated balance
    foreach ($users as &$u) {
        $u['balance'] = get_user_balance($u['username']);
    }
    return $users;
}

function get_user_by_username($username) {
    init_session_game_state();
    $username = ltrim(trim((string)$username), '@');
    $db = get_db_handle();
    $user = null;
    if ($db['type'] === 'mysql') {
        $stmt = $db['pdo']->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch() ?: null;
    } else {
        $store = load_json_store();
        $user = $store['users'][$username] ?? null;
    }

    if ($user) {
        $user['balance'] = get_user_balance($username);
    }
    return $user;
}

function get_pouch_cookie_data() {
    // The digitized Smart Ring pouch cookie is ONLY active and recognized once the player submits the Stage 2 flag in gateway.php
    if (!isset($_SESSION['stage2_flag_verified']) || $_SESSION['stage2_flag_verified'] !== true) {
        return null;
    }

    $raw = '';
    if (isset($_COOKIE['hunter_pouch'])) {
        $raw = trim((string)$_COOKIE['hunter_pouch']);
    } elseif (isset($_SERVER['HTTP_HUNTER_POUCH'])) {
        $raw = trim((string)$_SERVER['HTTP_HUNTER_POUCH']);
    }

    if ($raw === '') {
        return null;
    }

    // 1. Try direct base64 decode
    $decoded = base64_decode($raw, true);
    if ($decoded !== false) {
        $json = json_decode($decoded, true);
        if (is_array($json) && isset($json['balance']) && is_numeric($json['balance'])) {
            return $json;
        }
    }

    // 2. Try URL-decoded base64 decode (e.g. if cookie was URL-encoded)
    $urlDecoded = urldecode($raw);
    $decoded = base64_decode($urlDecoded, true);
    if ($decoded !== false) {
        $json = json_decode($decoded, true);
        if (is_array($json) && isset($json['balance']) && is_numeric($json['balance'])) {
            return $json;
        }
    }

    // 3. Try direct JSON decode (if user pasted raw JSON directly)
    $json = json_decode($raw, true);
    if (is_array($json) && isset($json['balance']) && is_numeric($json['balance'])) {
        return $json;
    }

    // 4. Try URL-decoded JSON decode
    $json = json_decode($urlDecoded, true);
    if (is_array($json) && isset($json['balance']) && is_numeric($json['balance'])) {
        return $json;
    }

    return null;
}

function get_user_balance($username) {
    init_session_game_state();
    $username = ltrim(trim((string)$username), '@');

    $baseBalance = 0;
    if (isset($_SESSION['player_balances'][$username])) {
        $baseBalance = (int)$_SESSION['player_balances'][$username];
    } else {
        $baseBalance = ($username === 'milluki_zoldyck') ? 10000 : 0;
    }

    // Check if player has a digitized Greed Island Smart Ring Pouch cookie (additive bonus)
    $pouch = get_pouch_cookie_data();
    if ($pouch && isset($pouch['balance']) && is_numeric($pouch['balance'])) {
        $cookieUser = isset($pouch['user']) ? strtolower(ltrim(trim((string)$pouch['user']), '@')) : '';
        $targetUser = strtolower($username);
        $sessionUser = isset($_SESSION['hunter_user']) ? strtolower(ltrim(trim((string)$_SESSION['hunter_user']), '@')) : '';

        // If cookie user is empty, or matches current target user, or matches active session user
        if (empty($cookieUser) || $cookieUser === $targetUser || ($sessionUser !== '' && $cookieUser === $sessionUser)) {
            return $baseBalance + (int)$pouch['balance'];
        }
    }

    return $baseBalance;
}

function execute_player_trade($fromUser, $toUser, $amount, $memo = 'P2P In-Game Jenny Trade') {
    init_session_game_state();
    $fromUser = ltrim(trim((string)$fromUser), '@');
    $toUser = ltrim(trim((string)$toUser), '@');

    $sender = get_user_by_username($fromUser);
    if (!$sender) {
        return ['success' => false, 'error' => 'SENDER_NOT_FOUND', 'message' => "Source player '$fromUser' does not exist."];
    }
    $recipient = get_user_by_username($toUser);
    if (!$recipient) {
        return ['success' => false, 'error' => 'RECIPIENT_NOT_FOUND', 'message' => "Recipient player '$toUser' does not exist."];
    }

    $senderBal = get_user_balance($fromUser);
    if ($senderBal < $amount) {
        return [
            'success' => false,
            'error' => 'INSUFFICIENT_FUNDS',
            'message' => "Player '$fromUser' only has " . number_format($senderBal) . " Ⓙ in their pouch. Cannot transfer " . number_format($amount) . " Ⓙ."
        ];
    }

    $recipientBal = get_user_balance($toUser);

    // Perform session-isolated atomic balance update
    $_SESSION['player_balances'][$fromUser] = $senderBal - $amount;
    $_SESSION['player_balances'][$toUser] = $recipientBal + $amount;

    $tradeRecord = [
        'id' => count($_SESSION['player_trades']) + 1,
        'from_user' => $fromUser,
        'to_user' => $toUser,
        'amount' => $amount,
        'memo' => $memo,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    $_SESSION['player_trades'][] = $tradeRecord;

    return ['success' => true, 'new_balance' => get_user_balance($_SESSION['hunter_user'] ?? $toUser)];
}

function execute_item_purchase($username, $itemId, $price, $itemTitle) {
    init_session_game_state();
    if ($username === 'milluki_zoldyck') {
        return [
            'success' => false,
            'error' => 'MERCHANT_BLACKLISTED',
            'message' => '⛔ [PURCHASE RESTRICTED] Zoldyck estate operatives are strictly blacklisted from direct merchant procurement. You must transfer your Jenny via the Bank portal to an authorized Hunter candidate (e.g. @leorio_paradinight) to purchase intelligence items.'
        ];
    }

    $bal = get_user_balance($username);
    if ($bal < $price) {
        return [
            'success' => false,
            'error' => 'INSUFFICIENT_FUNDS',
            'message' => "Insufficient in-game Jenny! Required: " . number_format($price) . " Ⓙ | Available in pouch: " . number_format($bal) . " Ⓙ."
        ];
    }

    $baseBal = (int)($_SESSION['player_balances'][$username] ?? 0);
    $pouch = get_pouch_cookie_data();
    $cookieBal = 0;
    if ($pouch && isset($pouch['balance']) && is_numeric($pouch['balance'])) {
        $cookieUser = isset($pouch['user']) ? strtolower(ltrim(trim((string)$pouch['user']), '@')) : '';
        $targetUser = strtolower($username);
        $sessionUser = isset($_SESSION['hunter_user']) ? strtolower(ltrim(trim((string)$_SESSION['hunter_user']), '@')) : '';
        if (empty($cookieUser) || $cookieUser === $targetUser || ($sessionUser !== '' && $cookieUser === $sessionUser)) {
            $cookieBal = (int)$pouch['balance'];
        }
    }

    // Deduct from base in-hand balance first, then remaining from cookie balance
    if ($baseBal >= $price) {
        $newBaseBal = $baseBal - $price;
        $newCookieBal = $cookieBal;
    } else {
        $remaining = $price - $baseBal;
        $newBaseBal = 0;
        $newCookieBal = max(0, $cookieBal - $remaining);
    }

    $_SESSION['player_balances'][$username] = $newBaseBal;

    // Only update digitized pouch cookie if player has verified the Stage 2 flag in gateway
    $pouchToken = null;
    if (isset($_SESSION['stage2_flag_verified']) && $_SESSION['stage2_flag_verified'] === true) {
        if (isset($_COOKIE['hunter_pouch']) || $itemId === 'clue_master') {
            $pouchData = [
                'user' => $username,
                'balance' => $newCookieBal,
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

    $newTotalBal = $newBaseBal + $newCookieBal;

    $_SESSION['purchased_items'][] = [
        'username' => $username,
        'item_id' => $itemId,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    $_SESSION['player_trades'][] = [
        'id' => count($_SESSION['player_trades']) + 1,
        'from_user' => $username,
        'to_user' => 'GREED_ISLAND_MERCHANT',
        'amount' => $price,
        'memo' => "Item Purchase: " . $itemTitle,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    return [
        'success' => true, 
        'new_balance' => $newTotalBal,
        'pouch_token' => $pouchToken
    ];
}

function get_user_trades($username) {
    init_session_game_state();
    $res = [];
    $trades = $_SESSION['player_trades'] ?? [];
    foreach (array_reverse($trades) as $t) {
        if ($t['from_user'] === $username || $t['to_user'] === $username) {
            $res[] = $t;
            if (count($res) >= 10) break;
        }
    }
    return $res;
}

function get_user_purchases($username) {
    init_session_game_state();
    $items = [];
    $purchased = $_SESSION['purchased_items'] ?? [];
    foreach ($purchased as $p) {
        if ($p['username'] === $username) {
            $items[] = $p['item_id'];
        }
    }
    return array_unique($items);
}

function dev_reset_balances() {
    init_session_game_state();
    $_SESSION['player_balances'] = [
        'leorio_paradinight' => 0,
        'gon_freecss' => 0,
        'killua_zoldyck' => 0,
        'kurapika_kurta' => 0,
        'milluki_zoldyck' => 10000,
        'hisoka_morow' => 0,
        'tonpa_crusher' => 0,
        'hanzo_ninja' => 0,
        'pokkle_archer' => 0,
        'ponzu_bees' => 0,
        'illumi_zoldyck' => 0,
        'chrollo_lucilfer' => 0,
        'feitan_portor' => 0,
        'machi_komacine' => 0,
        'zepile_appraiser' => 0,
    ];
    $_SESSION['player_trades'] = [];
    $_SESSION['purchased_items'] = [];
    unset($_SESSION['stage2_flag_verified']);
    unset($_SESSION['stage2_clue_unlocked']);

    if (!headers_sent()) {
        setcookie('hunter_pouch', '', time() - 3600, '/');
    }
    unset($_COOKIE['hunter_pouch']);

    $db = get_db_handle();
    if ($db['type'] === 'mysql') {
        try {
            $pdo = $db['pdo'];
            $pdo->exec("UPDATE users SET balance = 0 WHERE username != 'milluki_zoldyck'");
            $pdo->exec("UPDATE users SET balance = 10000 WHERE username = 'milluki_zoldyck'");
            $pdo->exec("DELETE FROM game_trades");
            $pdo->exec("DELETE FROM purchased_items");
        } catch (Throwable $e) {}
    }
    return true;
}

// -----------------------------------------------------------------------------
// Challenge Flags Retrieval & Persistence (Dynamic Database / JSON Layer)
// -----------------------------------------------------------------------------

function get_challenge_flag($flagKey) {
    $defaultFlags = [
        'stage2' => 'SSP{MILLUKI_BANK_IDOR_DRAINED_98A41}',
        'stage3' => 'SSP{WEB_HUNTER_LICENSE_DA91FC72}'
    ];
    $flagKey = trim((string)$flagKey);
    $db = get_db_handle();

    if ($db['type'] === 'mysql') {
        try {
            $stmt = $db['pdo']->prepare("SELECT flag_value FROM challenge_flags WHERE flag_key = :key LIMIT 1");
            $stmt->execute([':key' => $flagKey]);
            $row = $stmt->fetch();
            if ($row && !empty($row['flag_value'])) {
                return $row['flag_value'];
            }
        } catch (Throwable $e) {}
    } else {
        $store = load_json_store();
        if (isset($store['flags'][$flagKey]) && !empty($store['flags'][$flagKey])) {
            return $store['flags'][$flagKey];
        }
    }

    return $defaultFlags[$flagKey] ?? '';
}

function set_challenge_flag($flagKey, $flagValue, $description = '') {
    $flagKey = trim((string)$flagKey);
    $flagValue = trim((string)$flagValue);
    $db = get_db_handle();

    if ($db['type'] === 'mysql') {
        try {
            $stmt = $db['pdo']->prepare("INSERT INTO challenge_flags (flag_key, flag_value, description) VALUES (:key, :val, :desc) ON DUPLICATE KEY UPDATE flag_value = :val2, description = :desc2");
            $stmt->execute([
                ':key' => $flagKey, 
                ':val' => $flagValue, 
                ':desc' => $description, 
                ':val2' => $flagValue, 
                ':desc2' => $description
            ]);
        } catch (Throwable $e) {}
    }

    $store = load_json_store();
    if (!isset($store['flags']) || !is_array($store['flags'])) {
        $store['flags'] = [];
    }
    $store['flags'][$flagKey] = $flagValue;
    save_json_store($store);
    return true;
}

// -----------------------------------------------------------------------------
// Fallback JSON Engine Helper
// -----------------------------------------------------------------------------
function load_json_store() {
    if (!is_dir(DATA_DIR)) @mkdir(DATA_DIR, 0777, true);
    if (!file_exists(JSON_FALLBACK_PATH)) {
        init_default_json_data();
    }
    $data = json_decode(file_get_contents(JSON_FALLBACK_PATH), true);
    if (!is_array($data) || !isset($data['users'])) {
        init_default_json_data();
        $data = json_decode(file_get_contents(JSON_FALLBACK_PATH), true);
    }
    
    // Ensure flags key is initialized in active store
    if (!isset($data['flags']) || !is_array($data['flags']) || !isset($data['flags']['stage2']) || !isset($data['flags']['stage3'])) {
        if (!isset($data['flags']) || !is_array($data['flags'])) {
            $data['flags'] = [];
        }
        if (!isset($data['flags']['stage2'])) {
            $data['flags']['stage2'] = 'SSP{MILLUKI_BANK_IDOR_DRAINED_98A41}';
        }
        if (!isset($data['flags']['stage3'])) {
            $data['flags']['stage3'] = 'SSP{WEB_HUNTER_LICENSE_DA91FC72}';
        }
        save_json_store($data);
    }

    return $data;
}

function save_json_store($data) {
    file_put_contents(JSON_FALLBACK_PATH, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function init_default_json_data() {
    $candidates = [
        ['username' => 'leorio_paradinight', 'full_name' => 'Leorio Paradinight', 'title' => 'Medical Student & Zodiac ("Boar")', 'nen_type' => 'Emission (Warp Punch)', 'license_number' => '#287-0403-9912', 'balance' => 0, 'bio' => 'Aspiring physician working tirelessly to pay medical school tuition fees. Wallet currently empty.', 'avatar_badge' => '🩺', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'gon_freecss', 'full_name' => 'Gon Freecss', 'title' => 'Rookie Hunter / Enhancer', 'nen_type' => 'Enhancement (Jajanken)', 'license_number' => '#287-0405-1104', 'balance' => 0, 'bio' => 'Traveled from Whale Island in search of his father Ging. Spent all his in-game Jenny on fishing bait.', 'avatar_badge' => '🎣', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'killua_zoldyck', 'full_name' => 'Killua Zoldyck', 'title' => 'Ex-Assassin Heir / Transmuter', 'nen_type' => 'Transmutation (Godspeed Lightning)', 'license_number' => '#288-0001-7723', 'balance' => 0, 'bio' => 'Blew his entire game balance at the confectionery shop purchasing limited edition ChocoRobo-kun snacks.', 'avatar_badge' => '⚡', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'kurapika_kurta', 'full_name' => 'Kurapika', 'title' => 'Blacklist Hunter & Nostrade Boss', 'nen_type' => 'Conjuration / Specialization (Emperor Time)', 'license_number' => '#287-0404-5510', 'balance' => 0, 'bio' => 'Last survivor of the Kurta Clan. Allocated all trade pouch assets into Southernpiece auction intelligence.', 'avatar_badge' => '⛓️', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'milluki_zoldyck', 'full_name' => 'Milluki Zoldyck', 'title' => 'Zoldyck Estate Hacker & Otaku Collector', 'nen_type' => 'Manipulation (Remote Drone Bot)', 'license_number' => '#ZOLDYCK-TECH-902', 'balance' => 10000, 'bio' => 'Second son of the Zoldyck family. Hoards an emergency reserve of 10,000 Ⓙ in his in-game player pouch to purchase ultra-rare anime figurines and Greed Island memory cards.', 'avatar_badge' => '💻', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'hisoka_morow', 'full_name' => 'Hisoka Morow', 'title' => 'Grim Reaper Magician', 'nen_type' => 'Transmutation (Bungee Gum)', 'license_number' => '#287-0044-8832', 'balance' => 0, 'bio' => 'Zero interest in in-game currency. Only seeks unripe fruit to duel to the death.', 'avatar_badge' => '🃏', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'tonpa_crusher', 'full_name' => 'Tonpa', 'title' => 'Rookie Crusher Veteran', 'nen_type' => 'Unknown (Lethargy)', 'license_number' => 'UNLICENSED-EXAM-35X', 'balance' => 0, 'bio' => 'Participated in 35 consecutive Hunter exams. Bankrupted his game inventory purchasing industrial laxatives.', 'avatar_badge' => '🥫', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'hanzo_ninja', 'full_name' => 'Hanzo', 'title' => 'Cloud Hidden Shinobi', 'nen_type' => 'Transmutation (Ninjutsu Arts)', 'license_number' => '#287-0294-3321', 'balance' => 0, 'bio' => 'Elite ninja pursuing the Secret Scroll of the Hermit. Carries only survival shurikens and rations.', 'avatar_badge' => '🥷', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'pokkle_archer', 'full_name' => 'Pokkle', 'title' => 'Beast Hunter / Archer', 'nen_type' => 'Emission (Rainbow Arrows)', 'license_number' => '#287-0053-4412', 'balance' => 0, 'bio' => 'Specializes in undiscovered creature classification. Expended all capital on NGL exploration equipment.', 'avatar_badge' => '🏹', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'ponzu_bees', 'full_name' => 'Ponzu', 'title' => 'Chemical & Bee Specialist', 'nen_type' => 'Manipulation (Bee Signals)', 'license_number' => 'APPLICANT-#246', 'balance' => 0, 'bio' => 'Carries neurotoxin bees inside her hat. Zero Jenny remaining after apiary laboratory upgrades.', 'avatar_badge' => '🐝', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'illumi_zoldyck', 'full_name' => 'Illumi Zoldyck', 'title' => 'Assassin Operative / Needle Master', 'nen_type' => 'Manipulation (Needle Control)', 'license_number' => '#287-0301-9988', 'balance' => 0, 'bio' => 'Transfers all contract assassination bounties directly to the Kukuroo Mountain family vault.', 'avatar_badge' => '🪡', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'chrollo_lucilfer', 'full_name' => 'Chrollo Lucilfer', 'title' => 'Phantom Troupe Head (Spider #0)', 'nen_type' => 'Specialization (Skill Hunter)', 'license_number' => '#METEOR-CITY-0000', 'balance' => 0, 'bio' => 'We steal everything we want. Currency holds no value to the Spider, only rare artifacts and stolen Nen.', 'avatar_badge' => '📖', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'feitan_portor', 'full_name' => 'Feitan Portor', 'title' => 'Phantom Troupe Interrogator (#2)', 'nen_type' => 'Transmutation (Rising Sun / Pain Packer)', 'license_number' => '#SPIDER-0002-XX', 'balance' => 0, 'bio' => 'Master of torture and interrogation. Possesses no in-game money in the civilized world.', 'avatar_badge' => '☂️', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'machi_komacine', 'full_name' => 'Machi Komacine', 'title' => 'Nen Stitch Specialist (#3)', 'nen_type' => 'Transmutation (Nen Threads)', 'license_number' => '#SPIDER-0003-XX', 'balance' => 0, 'bio' => 'Charges exorbitant rates for re-attaching severed limbs (e.g. Hisoka), but stored all funds in Meteor City caches.', 'avatar_badge' => '🧵', 'created_at' => '2026-09-01 10:00:00'],
        ['username' => 'zepile_appraiser', 'full_name' => 'Zepile', 'title' => 'Yorknew Antique & Treasure Appraiser', 'nen_type' => 'Natural Genius / Wooden Box Artisan', 'license_number' => '#YORKNEW-APPRAISER', 'balance' => 0, 'bio' => 'Fell into severe debt pawning his Hunter License to finance bidding wars at the Southernpiece Auction.', 'avatar_badge' => '🏺', 'created_at' => '2026-09-01 10:00:00']
    ];

    $uMap = [];
    foreach ($candidates as $c) {
        $uMap[$c['username']] = $c;
    }

    $initial = [
        'users' => $uMap,
        'game_trades' => [],
        'purchased_items' => [],
        'flags' => [
            'stage2' => 'SSP{MILLUKI_BANK_IDOR_DRAINED_98A41}',
            'stage3' => 'SSP{WEB_HUNTER_LICENSE_DA91FC72}'
        ]
    ];

    save_json_store($initial);
}
