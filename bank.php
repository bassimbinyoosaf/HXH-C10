<?php
/**
 * HXH-C10 // Backward compatibility redirect for wallet.php
 */
require_once __DIR__ . '/config.php';
require_auth();
header('Location: wallet.php');
exit;
