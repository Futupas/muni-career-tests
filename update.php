<?php
// Prevent timeouts for large downloads
set_time_limit(300);

// 1. Load Environment Variables directly
// We do this manually to rely on the file on disk, not external helpers
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
} else {
    die("Error: .env file not found.");
}

// 2. HTTP Basic Authentication
// Checks credentials against values found in .env
$validUser = $_ENV['UPDATE_USER'] ?? null;
$validPass = $_ENV['UPDATE_PASS'] ?? null;
$downloadUrl = $_ENV['UPDATE_URL'] ?? null;

if (!$validUser || !$validPass || !$downloadUrl) {
    die("Error: Missing UPDATE_USER, UPDATE_PASS, or UPDATE_URL in .env file.");
}

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== $validUser || 
    $_SERVER['PHP_AUTH_PW'] !== $validPass) {
    
    header('WWW-Authenticate: Basic realm="System Update"');
    header('HTTP/1.0 401 Unauthorized');
    die('Access Denied');
}

// 3. The Update Logic
$tempZip = __DIR__ . '/../temp_update.zip';

echo "<h3>System Update</h3>";
echo "Downloading update from: " . htmlspecialchars($downloadUrl) . "...<br>";

// Download the ZIP file
$zipData = @file_get_contents($downloadUrl);

if ($zipData === false) {
    die("<h4 style='color:red'>Failed to download file. Check URL and server internet connection.</h4>");
}

if (file_put_contents($tempZip, $zipData) === false) {
    die("<h4 style='color:red'>Failed to save ZIP to disk. Check write permissions.</h4>");
}

echo "Download complete. Extracting...<br>";

// Unzip and Overwrite EVERYTHING
$zip = new ZipArchive;
if ($zip->open($tempZip) === TRUE) {
    
    // extractTo overwrites existing files by default
    if ($zip->extractTo(__DIR__)) {
        $zip->close();
        echo "<h2 style='color:green'>SUCCESS</h2>";
        echo "All files have been updated.";
    } else {
        $zip->close();
        echo "<h2 style='color:red'>Extraction Failed</h2>";
        echo "Could not extract files. Check folder permissions.";
    }
    
} else {
    echo "<h2 style='color:red'>Invalid ZIP</h2>";
    echo "Could not open the downloaded file. It might be corrupt.";
}

// Cleanup
if (file_exists($tempZip)) {
    unlink($tempZip);
}
?>
