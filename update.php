<?php
// Prevent timeouts during download/move operations
set_time_limit(600);
ini_set('memory_limit', '256M');

// ==========================================
// 1. LOAD CONFIGURATION
// ==========================================
$baseDir = __DIR__;
$envPath = $baseDir . '/.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

$authUser   = $_ENV['UPDATE_USER'] ?? null;
$authPass   = $_ENV['UPDATE_PASS'] ?? null;
$updateUrl  = $_ENV['UPDATE_URL']  ?? null;
// The folder name expected inside the ZIP
$sourceName = $_ENV['UPDATE_FOLDER_NAME'] ?? 'muni-career-tests-master';

// ==========================================
// 2. AUTHENTICATION
// ==========================================
if (!$authUser || !$authPass || !$updateUrl) {
    die("Error: Missing configuration in .env");
}

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== $authUser || 
    $_SERVER['PHP_AUTH_PW'] !== $authPass) {
    header('WWW-Authenticate: Basic realm="System Update"');
    header('HTTP/1.0 401 Unauthorized');
    die('Access Denied');
}

// ==========================================
// 3. HELPER FUNCTIONS
// ==========================================

/**
 * Recursively moves files and directories from source to destination.
 * Overwrites existing files. Merges directories.
 */
function recursiveMove($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst); // Ensure destination folder exists

    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;

            // PROTECT CRITICAL FILES
            // Do not overwrite .env or this update script itself
            if ($file === '.env' || $file === 'update.php') {
                continue;
            }

            if (is_dir($srcFile)) {
                // If it's a directory, recurse into it
                recursiveMove($srcFile, $dstFile);
            } else {
                // If it's a file, move it (overwrite)
                // We use copy+unlink because rename() often fails across partitions or on Windows
                if (copy($srcFile, $dstFile)) {
                    unlink($srcFile);
                } else {
                    echo "Failed to move: $dstFile <br>";
                }
            }
        }
    }
    closedir($dir);
    
    // Remove the source directory now that it is empty
    @rmdir($src);
}

/**
 * Recursively deletes a directory (used for cleanup if something goes wrong)
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . DIRECTORY_SEPARATOR . $object))
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                else
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
            }
        }
        rmdir($dir);
    }
}

// ==========================================
// 4. MAIN EXECUTION FLOW
// ==========================================

echo "<body style='font-family: sans-serif; background: #222; color: #fff; padding: 20px;'>";
echo "<h2>🚀 System Update</h2><pre>";

$zipFile = $baseDir . '/update_pkg.zip';
$extractedPath = $baseDir . '/' . $sourceName;

try {
    // --- STEP 1: DOWNLOAD ---
    echo "1. Downloading package from URL...\n";
    $data = @file_get_contents($updateUrl);
    if ($data === false) throw new Exception("Download failed. Check URL.");
    file_put_contents($zipFile, $data);

    // --- STEP 2: UNZIP ---
    echo "2. Unzipping archive...\n";
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo($baseDir);
        $zip->close();
    } else {
        throw new Exception("Could not open ZIP file.");
    }

    // Check if the folder exists
    if (!is_dir($extractedPath)) {
        throw new Exception("Expected folder '$sourceName' was not found in the zip file.<br>Check UPDATE_FOLDER_NAME in .env");
    }

    // --- STEP 3: RECURSIVE MOVE (MERGE) ---
    echo "3. Moving files and merging folders...\n";
    recursiveMove($extractedPath, $baseDir);

    echo "<h3 style='color: #4caf50'>✔ SUCCESS: Update complete.</h3>";

} catch (Exception $e) {
    echo "<h3 style='color: #f44336'>✖ ERROR: " . $e->getMessage() . "</h3>";
} finally {
    // --- STEP 4: CLEANUP ---
    echo "4. Cleanup...\n";
    if (file_exists($zipFile)) unlink($zipFile);
    
    // Use rrmdir just in case recursiveMove left scraps behind
    if (is_dir($extractedPath)) rrmdir($extractedPath);
}

echo "</pre></body>";
?>
