<?php
/**
 * HXH-C10 // In-Game P2P Jenny Transfer & Trade API Endpoint
 * 
 * [SECURITY VULNERABILITY: BOLA / IDOR via Parameter Manipulation]
 * This endpoint trusts the client-supplied `from_user` parameter rather than
 * validating session authority against $_SESSION['hunter_user'].
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

$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $input = $json;
    }
}

// VULNERABILITY: from_user is taken directly from client request or active session!
$fromUser = ltrim(trim($input['from_user'] ?? $input['sender'] ?? $_SESSION['hunter_user'] ?? ''), '@');
$toUser = ltrim(trim($input['to_user'] ?? $input['recipient'] ?? $input['to'] ?? ''), '@');
$amount = (int)($input['amount'] ?? 0);
$memo = trim($input['memo'] ?? 'P2P In-Game Jenny Trade');

if ($amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'INVALID_AMOUNT', 'message' => $ERROR_MESSAGES['INVALID_AMOUNT']]);
    exit;
}

if (empty($fromUser) || empty($toUser)) {
    echo json_encode(['success' => false, 'error' => 'MISSING_FIELDS', 'message' => 'Both source and destination player codenames are required.']);
    exit;
}

$result = execute_player_trade($fromUser, $toUser, $amount, $memo);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'message' => "Successfully transferred " . number_format($amount) . " Ⓙ in-game currency from '$fromUser' to '$toUser'.",
        'transferred_amount' => $amount,
        'from_user' => $fromUser,
        'to_user' => $toUser,
        'current_user_balance' => $result['new_balance']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error'],
        'message' => $result['message']
    ]);
}
exit;
