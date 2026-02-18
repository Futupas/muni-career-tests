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
    die("Тест не знайдено.");
}

$testData = json_decode(file_get_contents(__DIR__ . '/tests/' . $currentTestFile), true);
$submissionSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];
    
    $resultKey = '';
    $resultData = null;
    $packedResult = [];

    // --- TYPE 1: Combination (e.g. Karnaukh "1357") ---
    if ($testData['type'] == 1) {
        $selectedIds = [];
        foreach ($testData['questions'] as $block) {
            if (isset($_POST['q_' . $block['id']])) {
                $selectedIds[] = $_POST['q_' . $block['id']];
            }
        }
        sort($selectedIds);
        $resultKey = implode('', $selectedIds);
        $resultData = $testData['results'][$resultKey] ?? null;
    } 
    // --- TYPE 2: Category Summation (e.g. Holland) ---
    elseif ($testData['type'] == 2) {
        $scores = [];
        foreach ($testData['questions'] as $block) {
            foreach ($block['questions'] as $q) {
                $inputName = 'q_' . $block['id']; 
                if (isset($_POST[$inputName]) && $_POST[$inputName] == $q['value']) {
                    $val = $q['value'];
                    if (!isset($scores[$val])) $scores[$val] = 0;
                    $scores[$val]++;
                }
            }
        }
        if (!empty($scores)) {
            arsort($scores); 
            $resultKey = array_key_first($scores);
            $resultData = $testData['results'][$resultKey] ?? null;
        }
    }
    // --- TYPE 3: Scoring Keys (e.g. Potemkina) ---
    elseif ($testData['type'] == 3) {
        $score = 0;
        foreach ($testData['questions'] as $q) {
            $inputName = 'q_' . $q['id'];
            if (isset($_POST[$inputName])) {
                $userAnswer = $_POST[$inputName]; 
                if (in_array($q['id'], $testData['scoring_key'][$userAnswer])) {
                    $score++;
                }
            }
        }
        $resultKey = (string)$score; 
        foreach ($testData['results'] as $range => $data) {
            [$min, $max] = explode('-', $range);
            if ($score >= (int)$min && $score <= (int)$max) {
                $resultData = $data;
                break;
            }
        }
    }
    // --- TYPE 4: Direct Selection (e.g. Psychogeometry) ---
    elseif ($testData['type'] == 4) {
        $inputName = 'q_' . $testData['questions'][0]['id'];
        if (isset($_POST[$inputName])) {
            $resultKey = $_POST[$inputName];
            $resultData = $testData['results'][$resultKey] ?? null;
        }
    }
    // --- TYPE 5: Text Input / Exercise ---
    elseif ($testData['type'] == 5) {
        $answers = [];
        foreach ($testData['questions'] as $q) {
            $inputName = 'q_' . $q['id'];
            $answers[$q['text']] = htmlspecialchars($_POST[$inputName] ?? '');
        }
        $resultKey = 'saved';
        $resultData = [
            'name' => 'Вправа завершена',
            'description' => 'Відповіді збережено.',
            'recommendedProfessions' => ''
        ];
        $packedResult['user_answers'] = $answers;
    }

    // Pack Result for DB
    $packedResult = array_merge($packedResult, [
        'test_slug' => $slug,
        'test_name' => $testData['name'], // Saving Test Name in DB JSON
        'result_key' => $resultKey,
        'result_name' => $resultData ? $resultData['name'] : 'Unknown',
        'result_description' => $resultData ? $resultData['description'] : 'N/A',
        'result_professions' => $resultData['recommendedProfessions'] ?? 'N/A',
        'test_type' => $testData['type']
    ]);

    $jsonString = json_encode($packedResult, JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("INSERT INTO test_results (test_slug, user_name, user_age, result_json, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$slug, $name, $age, $jsonString, $_SERVER['REMOTE_ADDR']]);
    
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
    <script>
        // Apply browser theme immediately
        const theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    </script>
    <style>
        .option-label { cursor: pointer; height: 100%; transition: 0.2s; border: 1px solid var(--bs-border-color); }
        .option-input:checked + .option-label { background-color: var(--bs-primary); color: white; border-color: var(--bs-primary); }
    </style>
</head>
<body class="bg-body-tertiary">
<div class="container py-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="index.php" class="btn btn-outline-secondary">← На головну</a>
    </div>

    <?php if ($submissionSuccess): ?>
        <div class="card text-center border-success mb-3 shadow">
            <div class="card-body py-5">
                <h2 class="card-title text-success mb-3">Дякуємо!</h2>
                <p class="card-text lead">Ваші результати успішно збережено в базі даних.</p>
                <a href="index.php" class="btn btn-primary mt-3">До списку тестів</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0"><?php echo htmlspecialchars($testData['name']); ?></h2>
            </div>
            <div class="card-body">
                <p class="mb-4 text-muted"><?php echo nl2br(htmlspecialchars($testData['manual'])); ?></p>
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="row g-3 mb-4 border-bottom pb-4">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Ваше ім'я</label>
                            <input type="text" class="form-control" id="name" name="name" required />
                        </div>
                        <div class="col-md-4">
                            <label for="age" class="form-label">Вік</label>
                            <input type="number" class="form-control" id="age" name="age" required min="14" max="100" />
                        </div>
                    </div>

                    <?php if ($testData['type'] == 5): ?>
                        <?php foreach ($testData['questions'] as $q): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold"><?php echo htmlspecialchars($q['text']); ?></label>
                                <textarea class="form-control" name="q_<?php echo $q['id']; ?>" rows="4" required></textarea>
                            </div>
                        <?php endforeach; ?>

                    <?php elseif ($testData['type'] == 3): ?>
                        <div class="mb-4">
                            <?php foreach ($testData['questions'] as $q): ?>
                                <div class="row mb-2 align-items-center border-bottom pb-2">
                                    <div class="col-md-8"><?php echo htmlspecialchars($q['text']); ?></div>
                                    <div class="col-md-4 text-end">
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="q_<?php echo $q['id']; ?>" id="y_<?php echo $q['id']; ?>" value="yes" required>
                                            <label class="btn btn-outline-success btn-sm" for="y_<?php echo $q['id']; ?>">Так</label>
                                            <input type="radio" class="btn-check" name="q_<?php echo $q['id']; ?>" id="n_<?php echo $q['id']; ?>" value="no" required>
                                            <label class="btn btn-outline-danger btn-sm" for="n_<?php echo $q['id']; ?>">Ні</label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php else: ?>
                        <?php foreach ($testData['questions'] as $block): ?>
                            <div class="mb-4">
                                <?php if(!empty($block['name'])): ?><h5 class="mb-3 text-primary"><?php echo htmlspecialchars($block['name']); ?></h5><?php endif; ?>
                                <div class="row g-3">
                                    <?php foreach ($block['questions'] as $q): ?>
                                        <div class="<?php echo ($testData['type'] == 4) ? 'col-md-2' : 'col-md-6'; ?>">
                                            <input type="radio" class="btn-check option-input" 
                                                   name="q_<?php echo $block['id']; ?>" 
                                                   id="opt_<?php echo $block['id'] . '_' . $q['value']; ?>" 
                                                   value="<?php echo $q['value']; ?>" required>
                                            <label class="card card-body option-label text-center h-100 d-flex align-items-center justify-content-center" for="opt_<?php echo $block['id'] . '_' . $q['value']; ?>">
                                                <?php if(isset($q['img'])): ?>
                                                    <div class="fs-1 mb-2"><?php echo $q['img']; ?></div>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($q['text']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Отримати результат</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault(); event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false)
        })
    })();
</script>
</body>
</html>
