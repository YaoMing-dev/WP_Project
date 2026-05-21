<?php
// Error capture wrapper for wp-apply-kickzone.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "\n[PHP_ERROR $errno] $errstr in $errfile:$errline\n";
    return false;
});

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err) {
        echo "\n[FATAL] {$err['type']}: {$err['message']} in {$err['file']}:{$err['line']}\n";
    }
});

echo "=== WRAPPER START ===\n";
ob_implicit_flush(true);

// Test basic echo works
echo "TEST_ECHO_OK\n";
flush();

// Simulate first part of main script to find where it dies
echo "Loading file...\n";
flush();

// Actually just include it
include '/work/wp-apply-kickzone.php';

echo "\n=== WRAPPER END ===\n";
