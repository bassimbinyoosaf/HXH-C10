<?php
/**
 * HXH-C10 Built-in PHP Server Router
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// If the requested resource exists as a file, serve it directly
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    // Correct MIME types
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mimes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'txt' => 'text/plain',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'json' => 'application/json',
        'php' => 'text/html'
    ];

    if ($ext !== 'php' && isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        readfile($file);
        return true;
    }
    
    if ($ext === 'php') {
        require $file;
        return true;
    }
}

// Fallback to index.php
require __DIR__ . '/index.php';
return true;
