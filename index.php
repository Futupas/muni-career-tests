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
    <style>
        .spoiler-content { display: none; }
        .card:hover { transform: translateY(-5px); transition: 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-body-tertiary">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Доступні тести XXXXXX</h1>
        <button class="btn btn-outline-primary" id="themeToggle">🌓 Тема</button>
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
                        <a href="test.php?slug=<?php echo $test['slug']; ?>" class="btn btn-primary">Пройти тест</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 1. Function to determine the preferred theme
    const getPreferredTheme = () => {
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme) {
            return storedTheme;
        }
        // Check system preference (media query)
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    // 2. Function to apply the theme
    const setTheme = function (theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Optional: Change button text/icon based on theme
        const btn = document.getElementById('themeToggle');
        if(btn) {
            btn.innerHTML = theme === 'dark' ? '☀️ Світла тема' : '🌑 Темна тема';
            btn.className = theme === 'dark' ? 'btn btn-outline-light' : 'btn btn-outline-dark';
        }
    };

    // 3. Apply theme immediately on load
    setTheme(getPreferredTheme());

    // 4. Listen for System Changes (if user changes OS theme while page is open)
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
        // Only apply system change if user hasn't manually overridden it in localStorage
        if (!localStorage.getItem('theme')) {
            setTheme(event.matches ? 'dark' : 'light');
        }
    });

    // 5. Toggle Button Logic
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            setTheme(newTheme);
        });
    }
</script>
</body>
</html>
