<?php
require 'db.php';

// Authentication Check
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== $_ENV['ADMIN_USER'] || $_SERVER['PHP_AUTH_PW'] !== $_ENV['ADMIN_PASS']) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Access Denied');
}

// ----------------------------------------------------------------------------------------------------
// 1. Pagination & Sorting Setup
// ----------------------------------------------------------------------------------------------------
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50; // Items per page
if ($limit < 1 || $limit > 200) $limit = 50; // Sanity check for limit

$offset = ($page - 1) * $limit;

$allowedSorts = ['id', 'test_slug', 'user_name', 'user_age', 'submission_time'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'id';
$dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
$nextDir = $dir === 'ASC' ? 'DESC' : 'ASC';

// ----------------------------------------------------------------------------------------------------
// 2. Search Parameters
// ----------------------------------------------------------------------------------------------------
$searchName = $_GET['search_name'] ?? '';
$searchTestType = $_GET['search_test_type'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$whereClauses = [];
$queryParams = [];

if ($searchName) {
    $whereClauses[] = "user_name LIKE :searchName";
    $queryParams[':searchName'] = '%' . $searchName . '%';
}

if ($searchTestType) {
    $whereClauses[] = "test_slug LIKE :searchTestType";
    $queryParams[':searchTestType'] = '%' . $searchTestType . '%';
}

if ($startDate) {
    $whereClauses[] = "submission_time >= :startDate";
    $queryParams[':startDate'] = $startDate . ' 00:00:00'; // Start of the day
}

if ($endDate) {
    $whereClauses[] = "submission_time <= :endDate";
    $queryParams[':endDate'] = $endDate . ' 23:59:59'; // End of the day
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = ' WHERE ' . implode(' AND ', $whereClauses);
}

// ----------------------------------------------------------------------------------------------------
// 3. Fetch Data
// ----------------------------------------------------------------------------------------------------

// Count total rows with applied filters
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM test_results" . $whereSql);
$totalStmt->execute($queryParams);
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Ensure current page is within bounds
if ($page < 1) $page = 1;
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
if ($totalPages == 0) $page = 1; // If no results, default to page 1

$offset = ($page - 1) * $limit; // Recalculate offset in case page was adjusted

// Fetch results with applied filters, sorting, and pagination
$sql = "SELECT * FROM test_results" . $whereSql . " ORDER BY $sort $dir LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach ($queryParams as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$results = $stmt->fetchAll();

// ----------------------------------------------------------------------------------------------------
// Helper function to build URL parameters
// ----------------------------------------------------------------------------------------------------
function getQueryParams(array $override = []): string {
    $params = $_GET;
    foreach ($override as $key => $value) {
        if ($value === null) {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    // Remove 'page' if it's 1 and not overridden
    if (!isset($override['page']) && isset($params['page']) && $params['page'] == 1) {
        unset($params['page']);
    }
    return http_build_query($params);
}

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
        .table-sort-link { text-decoration: none; color: inherit; display: block; padding: .5rem .5rem;}
        .table-sort-link:hover { text-decoration: underline; }
    </style>
</head>
<body class="bg-body-tertiary">
<div class="container-fluid py-4">
    <h2 class="mb-4">Admin Panel - Test Results</h2>

    <!-- Search and Pagination Controls -->
    <div class="card mb-4">
        <div class="card-header">
            Filters and Pagination
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <!-- Search by Name -->
                <div class="col-md-3 col-lg-2">
                    <label for="search_name" class="form-label">Search Name</label>
                    <input type="text" class="form-control" id="search_name" name="search_name" value="<?= htmlspecialchars($searchName) ?>" placeholder="e.g., John Doe">
                </div>

                <!-- Search by Test Type -->
                <div class="col-md-3 col-lg-2">
                    <label for="search_test_type" class="form-label">Search Test Type</label>
                    <input type="text" class="form-control" id="search_test_type" name="search_test_type" value="<?= htmlspecialchars($searchTestType) ?>" placeholder="e.g., personality_test">
                </div>

                <!-- Search by Date Range -->
                <div class="col-md-3 col-lg-2">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                </div>

                <!-- Items per page -->
                <div class="col-md-3 col-lg-2">
                    <label for="limit" class="form-label">Items per page</label>
                    <select class="form-select" id="limit" name="limit">
                        <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                        <option value="200" <?= $limit == 200 ? 'selected' : '' ?>>200</option>
                    </select>
                </div>
                
                <div class="col-md-12 col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                    <a href="admin.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <!-- End Search and Pagination Controls -->

    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>
                        <a class="table-sort-link" href="?<?= getQueryParams(['sort' => 'id', 'dir' => ($sort == 'id' && $dir == 'ASC') ? 'DESC' : 'ASC', 'page' => 1]) ?>">
                            ID <?= ($sort == 'id') ? ($dir == 'ASC' ? '↑' : '↓') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a class="table-sort-link" href="?<?= getQueryParams(['sort' => 'test_slug', 'dir' => ($sort == 'test_slug' && $dir == 'ASC') ? 'DESC' : 'ASC', 'page' => 1]) ?>">
                            Test <?= ($sort == 'test_slug') ? ($dir == 'ASC' ? '↑' : '↓') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a class="table-sort-link" href="?<?= getQueryParams(['sort' => 'user_name', 'dir' => ($sort == 'user_name' && $dir == 'ASC') ? 'DESC' : 'ASC', 'page' => 1]) ?>">
                            Name <?= ($sort == 'user_name') ? ($dir == 'ASC' ? '↑' : '↓') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a class="table-sort-link" href="?<?= getQueryParams(['sort' => 'user_age', 'dir' => ($sort == 'user_age' && $dir == 'ASC') ? 'DESC' : 'ASC', 'page' => 1]) ?>">
                            Age <?= ($sort == 'user_age') ? ($dir == 'ASC' ? '↑' : '↓') : '' ?>
                        </a>
                    </th>
                    <th style="width: 30%;">Result (JSON)</th>
                    <th>IP</th>
                    <th>
                        <a class="table-sort-link" href="?<?= getQueryParams(['sort' => 'submission_time', 'dir' => ($sort == 'submission_time' && $dir == 'ASC') ? 'DESC' : 'ASC', 'page' => 1]) ?>">
                            Date <?= ($sort == 'submission_time') ? ($dir == 'ASC' ? '↑' : '↓') : '' ?>
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4">No results found matching your criteria.</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($results as $row): ?>
                <?php 
                    $fullData = [
                        'id' => $row['id'], 
                        'test_slug' => $row['test_slug'], 
                        'user_name' => $row['user_name'], 
                        'user_age' => $row['user_age'], 
                        'ip' => $row['ip_address'], 
                        'date' => $row['submission_time'], 
                        'result' => json_decode($row['result_json'], true)
                    ];
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
                        <button 
                            class="btn btn-sm btn-outline-danger" 
                            onclick="confirmDelete(<?= $row['id'] ?>, '<?= addslashes(htmlspecialchars($row['user_name'])) ?>', <?= $row['user_age'] ?>, '<?= addslashes(htmlspecialchars($row['test_slug'])) ?>', '<?= $row['submission_time'] ?>')">
                            delete
                        </button>

                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Navigation -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= getQueryParams(['page' => 1]) ?>" aria-label="First">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= getQueryParams(['page' => $page - 1]) ?>" aria-label="Previous">
                    <span aria-hidden="true">&lsaquo;</span>
                </a>
            </li>

            <?php
            // Display a limited number of page links around the current page
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);

            if ($startPage > 1) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }

            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= getQueryParams(['page' => $i]) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php
            if ($endPage < $totalPages) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            ?>

            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= getQueryParams(['page' => $page + 1]) ?>" aria-label="Next">
                    <span aria-hidden="true">&rsaquo;</span>
                </a>
            </li>
            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= getQueryParams(['page' => $totalPages]) ?>" aria-label="Last">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
    <!-- End Pagination Navigation -->
    
    <div class="text-center mt-3 text-muted">
        Total Results: <?= $totalRows ?>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

function confirmDelete(id, name, age, slug, date) {
    if (confirm(`Ви 100% впевнені, що хочете назавжди видалити результат #${id} (${name}, ${age} років, тест: ${slug}, дата: ${date})? Цю дію неможливо скасувати.`)) {
        window.location.href = 'delete.php?id=' + id;
    }
}

</script>
</body>
</html>
