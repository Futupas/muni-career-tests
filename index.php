<?php
$testsFile = __DIR__ . '/tests/tests.json';
$tests = [];
if (file_exists($testsFile)) {
    $tests = json_decode(file_get_contents($testsFile), true);
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тести на профорієнтацію</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        const theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    </script>
    <style>
        .spoiler-content { display: none; }
        .card:hover { transform: translateY(-5px); transition: 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-body-tertiary">

<div class="container py-5">
    <div class="mb-4">
        <h1>Доступні тести</h1>
    </div>

    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php foreach ($tests as $test): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($test['name']); ?></h5>
                        <p class="card-text">
                            <button class="btn btn-sm btn-link p-0 text-decoration-none" onclick="toggleSpoiler('desc-<?php echo $test['slug']; ?>')">
                                Показати опис ▼
                            </button>
                        </p>
                        <div id="desc-<?php echo $test['slug']; ?>" class="spoiler-content mb-3 text-muted">
                            <?php echo htmlspecialchars($test['description']); ?>
                        </div>
                        <a href="/test/<?php echo $test['slug']; ?>" class="btn btn-primary">Пройти тест</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Spoiler Toggle
    function toggleSpoiler(id) {
        const el = document.getElementById(id);
        el.style.display = el.style.display === 'block' ? 'none' : 'block';
    }
</script>
</body>
</html>
