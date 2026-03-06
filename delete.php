<?php
require 'db.php';

// Authentication check
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== $_ENV['ADMIN_USER'] || $_SERVER['PHP_AUTH_PW'] !== $_ENV['ADMIN_PASS']) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Access Denied');
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM test_results WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: admin.php");
?>
