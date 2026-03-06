<?php
require 'db.php';

// Authentication Check
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== $_ENV['ADMIN_USER'] || $_SERVER['PHP_AUTH_PW'] !== $_ENV['ADMIN_PASS']) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Access Denied');
}

// 2. Pagination & Sorting Setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;
$allowedSorts = ['id', 'test_slug', 'user_name', 'user_age', 'submission_time'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'id';
$dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
$nextDir = $dir === 'ASC' ? 'DESC' : 'ASC';

// 3. Fetch Data
$totalStmt = $pdo->query("SELECT COUNT(*) FROM test_results");
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$stmt = $pdo->prepare("SELECT * FROM test_results ORDER BY $sort $dir LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .json-spoiler { max-height: 50px; overflow: hidden; position: relative; cursor: pointer; background: rgba(255,255,255,0.05); padding: 5px; border-radius: 4px; transition: 0.3s; }
        .json-spoiler.expanded { max-height: none; overflow: visible; }
        pre { margin: 0; font-size: 0.85em; white-space: pre-wrap; }
    </style>
</head>
<body class="bg-body-tertiary">
<div class="container-fluid py-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th><th>Test</th><th>Name</th><th>Age</th><th style="width: 30%;">Result (JSON)</th><th>IP</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                <?php 
                    $fullData = ['id' => $row['id'], 'test_slug' => $row['test_slug'], 'user_name' => $row['user_name'], 'user_age' => $row['user_age'], 'ip' => $row['ip_address'], 'date' => $row['submission_time'], 'result' => json_decode($row['result_json'], true)];
                    $jsonString = json_encode($fullData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['test_slug']) ?></td>
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td><?= $row['user_age'] ?></td>
                    <td>
                        <div class="json-spoiler" onclick="this.classList.toggle('expanded')">
                            <pre><?= htmlspecialchars($jsonString) ?></pre>
                        </div>
                    </td>
                    <td><?= $row['ip_address'] ?></td>
                    <td><?= $row['submission_time'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary copy-btn" data-json='<?= htmlspecialchars($jsonString, ENT_QUOTES) ?>'>copy</button>
                        <button class="btn btn-sm btn-outline-info download-btn" data-json='<?= htmlspecialchars($jsonString, ENT_QUOTES) ?>' data-id="<?= $row['id'] ?>">download</button>
                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Ви 100% впевнені?')">delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
'use strict';

// Logic for COPY and DOWNLOAD
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.onclick = () => {
        const json = btn.getAttribute('data-json');
        btn.innerText = 'copying';
        navigator.clipboard.writeText(json).then(() => {
            btn.innerText = 'copied';
            setTimeout(() => { btn.innerText = 'copy'; }, 2000);
        }).catch(() => { btn.innerText = 'failed'; });
    };
});

document.querySelectorAll('.download-btn').forEach(btn => {
    btn.onclick = () => {
        const json = btn.getAttribute('data-json');
        const id = btn.getAttribute('data-id');
        const blob = new Blob([json], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'result_' + id + '.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };
});
</script>
</body>
</html>
