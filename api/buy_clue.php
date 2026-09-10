<?php
/**
 * HXH-C10 // In-Game Item & Clue Merchant Purchase API
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!is_authenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'UNAUTHORIZED', 'message' => $ERROR_MESSAGES['UNAUTHORIZED']]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'METHOD_NOT_ALLOWED', 'message' => 'Only POST requests are supported.']);
    exit;
}

$currentUser = $_SESSION['hunter_user'] ?? '';
$clueId = trim($_POST['clue_id'] ?? '');

// Block Milluki Zoldyck from purchasing clues directly
if ($currentUser === 'milluki_zoldyck') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'MERCHANT_BLACKLISTED',
        'message' => '⛔ [PURCHASE RESTRICTED] Zoldyck estate operatives are strictly blacklisted from direct merchant procurement. You must transfer your Jenny via the Bank portal to an authorized Hunter candidate (e.g. @leorio_paradinight) to purchase intelligence items.'
    ]);
    exit;
}

$CLUES = [
    'clue_standard' => [
        'title' => 'Greed Island Secret Intelligence Dossier',
        'price' => CLUE_STANDARD_PRICE, // 1000 Jenny
        'content' => "========================================================================\n" .
                     "  CONFIDENTIAL HUNTER INTEL // STAGE 2 CLEARED\n" .
                     "========================================================================\n" .
                     "[+] EXPLOIT VERIFIED: P2P Game Money IDOR Hijacking Successful\n" .
                     "[+] TARGET DRAINED: Milluki Zoldyck (10,000 Ⓙ)\n" .
                     "[+] STAGE 2 FLAG: " . FLAG_STAGE2 . "\n" .
                     "[+] GATEWAY STATUS: UNLOCKED (gateway.php is now accessible!)\n" .
                     "------------------------------------------------------------------------\n" .
                     "[*] MISSION DIRECTIVE:\n" .
                     "    Navigate to the Greed Island Gateway portal (gateway.php) and\n" .
                     "    authenticate the Gateway Cipher with your Stage 2 Flag to energize\n" .
                     "    the Smart Ring network and unlock the next stage!\n" .
                     "========================================================================"
    ],
    'clue_master' => [
        'title' => 'Dark Continent Ancient Gate Key & Relic',
        'price' => CLUE_MASTER_PRICE, // 20000 Jenny
        'content' => "========================================================================\n" .
                     "  👑 LEGENDARY ARTIFACT // DARK CONTINENT MASTER RELIC UNLOCKED\n" .
                     "========================================================================\n" .
                     "[+] EXPLOIT VERIFIED: Client-Side Cookie State Tampering Successful\n" .
                     "[+] ITEM PROCURED: Dark Continent Master Key (20,000 Ⓙ Transacted)\n" .
                     "------------------------------------------------------------------------\n" .
                     "[*] ANCIENT RELIC INSCRIPTION // NEN OVERRIDE SIGNAL:\n" .
                     "    \"The latent aura frequencies within this key have synchronized with\n" .
                     "     the remote Gateway relay network. An automated override broadcast has\n" .
                     "     been transmitted to sector gateway.php.\n\n" .
                     "     The deepest core chamber is now auto-unlocked.\"\n\n" .
                     "[*] RECON DIRECTIVE:\n" .
                     "    Return to the Gateway Portal (gateway.php) to observe the live automated\n" .
                     "    core breach transmission and retrieve the REAL FINAL FLAG!\n" .
                     "========================================================================"
    ]
];

if (!isset($CLUES[$clueId])) {
    echo json_encode(['success' => false, 'error' => 'INVALID_ITEM', 'message' => 'Unknown game item requested.']);
    exit;
}

// Stage 3 Master Relic purchase strictly requires Stage 2 Flag verification at gateway.php
if ($clueId === 'clue_master') {
    if (!isset($_SESSION['stage2_flag_verified']) || $_SESSION['stage2_flag_verified'] !== true) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'STAGE2_FLAG_NOT_VERIFIED',
            'message' => '⛔ [PORTAL LOCKED] You must authenticate the Stage 2 Flag in the Gateway Portal (gateway.php) before attempting to procure the Dark Continent Master Relic!'
        ]);
        exit;
    }
}

$targetClue = $CLUES[$clueId];
$price = $targetClue['price'];

$result = execute_item_purchase($currentUser, $clueId, $price, $targetClue['title']);

if ($result['success']) {
    if ($clueId === 'clue_standard') {
        $_SESSION['stage2_clue_unlocked'] = true;
    } elseif ($clueId === 'clue_master') {
        $_SESSION['stage3_master_unlocked'] = true;
    }
    echo json_encode([
        'success' => true,
        'message' => "Successfully unlocked '{$targetClue['title']}'!",
        'clue_title' => $targetClue['title'],
        'clue_content' => $targetClue['content'],
        'new_balance' => $result['new_balance'],
        'pouch_token' => $result['pouch_token'] ?? null
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error'],
        'message' => $result['message']
    ]);
}
exit;
