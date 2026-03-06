<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$results = [];
$userAnswers = [];

// Populate User Answers
foreach ($testData['questions'] as $q) {
    $qId = $q['id'];
    $val = $_POST["q_$qId"] ?? '0';
    $userAnswers[mb_strimwidth($q['text'], 0, $n, "...")] = ($val == '1' ? 'Так' : 'Ні');
}

// Calculate Scores and map to interpretations from JSON
foreach ($testData['scales'] as $code => $scaleData) {
    $score = 0;
    foreach ($scaleData['keys'] as $qId) {
        if (($_POST["q_$qId"] ?? '') == '1') $score++;
    }
    
    // Determine level based on the JSON levels array
    $levelText = "";
    foreach ($testData['levels'] as $lvl) {
        if ($score >= $lvl['min'] && $score <= $lvl['max']) {
            $levelText = $lvl['text'];
            break;
        }
    }
    
    $results[$code] = [
        'name' => $scaleData['name'],
        'score' => $score,
        'level' => $levelText,
        'description' => $scaleData['description']
    ];
}

$packedResult['user_answers'] = $userAnswers;
$packedResult['results'] = $results;
?>
