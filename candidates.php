<?php
/**
 * HXH-C10 // Forward to friends.php table view
 */
require_once __DIR__ . '/config.php';
require_auth();
header('Location: friends.php');
exit;
