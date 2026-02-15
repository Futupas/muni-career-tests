<?php
require 'db.php';

$slug = $_GET['slug'] ?? '';
$testsIndex = json_decode(file_get_contents(__DIR__ . '/tests/tests.json'), true);
$currentTestFile = null;

// Find the filename based on slug
foreach ($testsIndex as $t) {
    if ($t['slug'] === $slug) {
        $currentTestFile = $t['filename'];
        break;
    }
}

if (!$currentTestFile || !file_exists(__DIR__ . '/tests/' . $currentTestFile)) {
    die("Тест не знайдено.");
}

// Load Test Data
$testData = json_decode(file_get_contents(__DIR__ . '/tests/' . $currentTestFile), true);
$submissionSuccess = false;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];
    
    // Calculate Result
    // For this specific test type, we collect the ID of the selected option from each pair
    // and sort them or concatenate them to match the keys in "results" (e.g., "1357")
    $selectedIds = [];
    foreach ($testData['questions'] as $block) {
        if (isset($_POST['q_' . $block['id']])) {
            $selectedIds[] = $_POST['q_' . $block['id']];
        }
    }
    
    // Sort to ensure key matches "1357" regardless of order (though usually questions are linear)
    sort($selectedIds);
    $resultKey = implode('', $selectedIds);
    
    // Find result in JSON
    $resultData = $testData['results'][$resultKey] ?? null;
    
    $resultName = $resultData ? $resultData['name'] : 'Unknown';
    $resultDesc = $resultData ? $resultData['description'] : 'Результат не визначено';
    $resultProf = $resultData ? $resultData['recommendedProfessions'] : '';

    // Save to DB
    $stmt = $pdo->prepare("INSERT INTO test_results (test_type_id, test_slug, user_name, user_age, result_key, result_explanation, result_profession, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $testData['type'],
        $slug,
        $name,
        $age,
        $resultName, // Storing Result Name (e.g., "Хранитель") as key summary
        $resultDesc,
        $resultProf,
        $_SERVER['REMOTE_ADDR']
    ]);
    
    $submissionSuccess = true;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($testData['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .option-label { cursor: pointer; height: 100%; transition: 0.2s; border: 1px solid var(--bs-border-color); }
        .option-input:checked + .option-label {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
        }
    </style>
</head>
<body class="bg-body-tertiary">

<div class="container py-5" style="max-width: 800px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="index.php" class="btn btn-outline-secondary">← На головну</a>
        <button class="btn btn-outline-primary" id="themeToggle">🌓 Тема</button>
    </div>

    <?php if ($submissionSuccess): ?>
        <!-- Success Message -->
        <div class="card text-center border-success mb-3 shadow">
            <div class="card-body py-5">
                <h2 class="card-title text-success mb-3">Дякуємо за вашу відповідь!</h2>
                <p class="card-text lead">Ваші результати успішно збережено та будуть опрацьовані.</p>
                <hr>
                <a href="index.php" class="btn btn-primary mt-3">Пройти інший тест</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Test Form -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0"><?php echo htmlspecialchars($testData['name']); ?></h2>
            </div>
            <div class="card-body">
                <p class="mb-4 text-muted"><?php echo htmlspecialchars($testData['manual']); ?></p>
                
                <form method="POST" action="" class="needs-validation" novalidate>
                    <!-- Personal Info -->
                    <div class="row g-3 mb-4 border-bottom pb-4">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Ваше ім'я</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-4">
                            <label for="age" class="form-label">Вік</label>
                            <input type="number" class="form-control" id="age" name="age" required>
                        </div>
                    </div>

                    <!-- Questions -->
                    <?php foreach ($testData['questions'] as $block): ?>
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary"><?php echo htmlspecialchars($block['name']); ?></h5>
                            <div class="row g-3">
                                <?php foreach ($block['questions'] as $q): ?>
                                    <div class="col-md-6">
                                        <input type="radio" class="btn-check option-input" 
                                               name="q_<?php echo $block['id']; ?>" 
                                               id="opt_<?php echo $q['id']; ?>" 
                                               value="<?php echo $q['id']; ?>" required>
                                        <label class="card card-body option-label" for="opt_<?php echo $q['id']; ?>">
                                            <?php echo htmlspecialchars($q['text']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Отримати результат</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Simple Validation
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()

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
