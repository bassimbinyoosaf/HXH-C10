<?php
/**
 * HXH-C10 // Dynamic Clue Retrieval API
 * Fetches decrypted clue contents dynamically from the database
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!is_authenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'UNAUTHORIZED', 'message' => $ERROR_MESSAGES['UNAUTHORIZED']]);
    exit;
}

$currentUser = $_SESSION['hunter_user'] ?? '';
$clueId = trim($_GET['clue_id'] ?? '');

$purchased = get_user_purchases($currentUser);
if (!in_array($clueId, $purchased)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'NOT_PURCHASED', 'message' => 'Clue not yet unlocked by current player.']);
    exit;
}

$clueContent = '';
if ($clueId === 'clue_standard') {
    $flag = get_challenge_flag('stage2');
    $clueContent = "========================================================================\n" .
                   "  CONFIDENTIAL HUNTER INTEL // STAGE 2 CLEARED\n" .
                   "========================================================================\n" .
                   "[+] EXPLOIT VERIFIED: P2P Game Money IDOR Hijacking Successful\n" .
                   "[+] TARGET DRAINED: Milluki Zoldyck (10,000 Ⓙ)\n" .
                   "[+] STAGE 2 FLAG: " . $flag . "\n" .
                   "[+] GATEWAY STATUS: UNLOCKED (gateway.php is now accessible!)\n" .
                   "------------------------------------------------------------------------\n" .
                   "[*] MISSION DIRECTIVE:\n" .
                   "    Navigate to the Greed Island Gateway portal (gateway.php) and\n" .
                   "    authenticate the Gateway Cipher with your Stage 2 Flag to energize\n" .
                   "    the Smart Ring network and unlock the next stage!\n" .
                   "========================================================================";
} elseif ($clueId === 'clue_master') {
    $clueContent = "========================================================================\n" .
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
                   "========================================================================";
} else {
    echo json_encode(['success' => false, 'error' => 'INVALID_ITEM', 'message' => 'Unknown clue item.']);
    exit;
}

echo json_encode(['success' => true, 'clue_id' => $clueId, 'clue_content' => $clueContent]);
exit;
