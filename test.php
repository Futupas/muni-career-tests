<?php
require 'db.php';

$slug = $_GET['slug'] ?? '';
$testsIndex = json_decode(file_get_contents(__DIR__ . '/tests/tests.json'), true);
$currentTestFile = null;

foreach ($testsIndex as $t) {
    if ($t['slug'] === $slug) {
        $currentTestFile = $t['filename'];
        break;
    }
}

if (!$currentTestFile || !file_exists(__DIR__ . '/tests/' . $currentTestFile)) {
    die('Тест не знайдено. <a href="/">На головну</a>');
}

$testData = json_decode(file_get_contents(__DIR__ . '/tests/' . $currentTestFile), true);
$type = $testData['type'];
$submissionSuccess = false;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];

    // --- Validation Check ---
    $expectedQuestions = count($testData['questions']);
    $receivedAnswers = 0;
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'q_') === 0 && !empty($val)) $receivedAnswers++;
    }

    if (empty($name) || $age < 14 || $receivedAnswers < $expectedQuestions) {
        $errorMessage = "Будь ласка, заповніть всі поля та дайте відповіді на всі запитання.";
    } else {
        $packedResult = [
            'test_slug' => $slug,
            'test_name' => $testData['name'],
            'test_type' => $type
        ];

        $logicFile = __DIR__ . "/handlers/logic_{$type}.php";
        if (file_exists($logicFile)) {
            include $logicFile;
        }

        $userAnswers = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'q_') === 0) {
                $userAnswers[$key] = $value;
            }
        }
        $packedResult['user_answers'] = $userAnswers;
        
        $jsonString = json_encode($packedResult, JSON_UNESCAPED_UNICODE);

        $stmt = $pdo->prepare("INSERT INTO test_results (test_slug, user_name, user_age, result_json, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$slug, $name, $age, $jsonString, $_SERVER['REMOTE_ADDR']]);
        
        $submissionSuccess = true;
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<!-- <?php echo getenv('CREDITS') ?: 'Futupas - https://futupas.github.io'; ?> -->
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($testData['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script>
        document.documentElement.setAttribute('data-bs-theme', window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    </script>
    <style>
        .option-label { cursor: pointer; height: 100%; transition: 0.2s; border: 1px solid var(--bs-border-color); border-radius: 8px; }
        
        /* Visual for Selected */
        .btn-check:checked + .option-label { 
            background-color: var(--bs-primary) !important; 
            color: white !important; 
            border-color: var(--bs-primary) !important; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* HIGHLIGHT MISSING FIELDS: When form is validated and input is invalid */
        .was-validated .btn-check:invalid + .option-label {
            border-color: #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.05);
        }

        /* Also highlight standard inputs */
        .was-validated .form-control:invalid {
            border-color: #dc3545;
        }
    </style>
</head>
<body class="bg-body-tertiary">
<div class="container py-5" style="max-width: 800px;">
    <div class="mb-4"><a href="/" class="btn btn-outline-secondary">← На головну</a></div>

    <?php if ($submissionSuccess): ?>
        <div class="card text-center border-success shadow"><div class="card-body py-5">
            <h2 class="text-success">Дякуємо!</h2>
            <p class="lead">Ваші відповіді збережено.</p>
            <a href="/" class="btn btn-primary mt-3">До списку тестів</a>
        </div></div>
    <?php else: ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger shadow-sm"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white"><h2 class="h4 mb-0"><?php echo htmlspecialchars($testData['name']); ?></h2></div>
            <div class="card-body">
                <p class="mb-4 text-muted"><?php echo nl2br(htmlspecialchars($testData['manual'])); ?></p>
                
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="row g-3 mb-4 border-bottom pb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Ім'я</label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Вік</label>
                            <input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>" required min="14" />
                        </div>
                    </div>
                    <?php 
                        $viewFile = __DIR__ . "/handlers/view_{$type}.php";
                        if (file_exists($viewFile)) include $viewFile; 
                    ?>
                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">Надіслати відповіді</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Standard Bootstrap validation script
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                    // Scroll to the first error
                    const firstError = form.querySelector(':invalid');
                    if (firstError) {
                        firstError.closest('.mb-4').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
</body>
</html>
