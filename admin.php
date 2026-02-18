<?php
session_start();
require 'db.php';

// 1. Authentication Check
if (isset($_POST['password'])) {
    if ($_POST['password'] === $_ENV['ADMIN_PASS']) {
        $_SESSION['is_admin'] = true;
    } else {
        $error = "Невірний пароль";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="uk" data-bs-theme="dark">
    <head>
        <!-- <?php echo getenv('CREDITS') ?: 'Futupas - https://futupas.github.io'; ?> -->
        <meta charset="UTF-8" />
        <title>Admin Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    </head>
    <body class="d-flex align-items-center justify-content-center vh-100 bg-body-tertiary">
        <form method="POST" class="card p-4 shadow" style="width: 300px;">
            <h3 class="mb-3 text-center">Admin Access</h3>
            <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// 2. Pagination & Sorting Setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Allowlist for sorting to prevent SQL Injection
$allowedSorts = ['id', 'test_slug', 'user_name', 'user_age', 'submission_time'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'id';
$dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';

// Toggle direction for links
$nextDir = $dir === 'ASC' ? 'DESC' : 'ASC';

// 3. Fetch Data
// Get Total Count
$totalStmt = $pdo->query("SELECT COUNT(*) FROM test_results");
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Get Rows
$sql = "SELECT * FROM test_results ORDER BY $sort $dir LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="uk" data-bs-theme="dark">
<!-- <?php echo getenv('CREDITS') ?: 'Futupas - https://futupas.github.io'; ?> -->
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .json-spoiler {
            max-height: 50px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            background: rgba(255,255,255,0.05);
            padding: 5px;
            border-radius: 4px;
            transition: 0.3s;
        }
        .json-spoiler:hover {
            background: rgba(255,255,255,0.1);
        }
        .json-spoiler.expanded {
            max-height: none;
            overflow: visible;
        }
        .json-spoiler::after {
            content: '... click to expand';
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--bs-body-bg);
            padding: 0 5px;
            font-size: 0.8em;
            color: var(--bs-secondary);
        }
        .json-spoiler.expanded::after {
            content: '';
        }
        pre { margin: 0; font-size: 0.85em; white-space: pre-wrap; }
    </style>
</head>
<body class="bg-body-tertiary">

<nav class="navbar navbar-expand-lg border-bottom mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <div class="d-flex">
            <span class="navbar-text me-3">Total: <?= $totalRows ?></span>
            <a href="?logout=1" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th><a href="?sort=id&dir=<?= $nextDir ?>" class="text-white text-decoration-none">ID <?= $sort=='id'?($dir=='ASC'?'↑':'↓'):'' ?></a></th>
                    <th><a href="?sort=test_slug&dir=<?= $nextDir ?>" class="text-white text-decoration-none">Test <?= $sort=='test_slug'?($dir=='ASC'?'↑':'↓'):'' ?></a></th>
                    <th><a href="?sort=user_name&dir=<?= $nextDir ?>" class="text-white text-decoration-none">Name <?= $sort=='user_name'?($dir=='ASC'?'↑':'↓'):'' ?></a></th>
                    <th><a href="?sort=user_age&dir=<?= $nextDir ?>" class="text-white text-decoration-none">Age <?= $sort=='user_age'?($dir=='ASC'?'↑':'↓'):'' ?></a></th>
                    <th style="width: 40%;">Result (JSON)</th>
                    <th>IP</th>
                    <th><a href="?sort=submission_time&dir=<?= $nextDir ?>" class="text-white text-decoration-none">Date <?= $sort=='submission_time'?($dir=='ASC'?'↑':'↓'):'' ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['test_slug']) ?></td>
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td><?= $row['user_age'] ?></td>
                    <td>
                        <div class="json-spoiler" onclick="this.classList.toggle('expanded')">
                            <pre><?php 
                                // Pretty print the JSON
                                $decoded = json_decode($row['result_json'], true);
                                echo htmlspecialchars(json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); 
                            ?></pre>
                        </div>
                    </td>
                    <td><?= $row['ip_address'] ?></td>
                    <td><?= $row['submission_time'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&sort=<?= $sort ?>&dir=<?= $dir ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

</body>
</html>
