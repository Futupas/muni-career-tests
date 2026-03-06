<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$score = 0;
$userAnswers = [];

foreach ($testData['questions'] as $q) {
    $qId = $q['id'];
    $val = $_POST["q_$qId"] ?? '';
    // Store truncated question and answer
    $userAnswers[mb_strimwidth($q['text'], 0, $n, "...")] = $val;
    
    if (in_array(trim(mb_strtolower($val)), $testData['answers'][$qId])) {
        $score++;
    }
}

// Calculate level based on JSON table
$levelText = "Невизначений рівень";
foreach ($testData['levels'] as $lvl) {
    if ($score <= $lvl['max']) {
        $levelText = $lvl['label'];
        break;
    }
}

$packedResult['score'] = $score;
$packedResult['result_name'] = "Результат КОТ: " . $levelText;
$packedResult['result_description'] = "Кількість правильних відповідей: " . $score . " з 50.";
$packedResult['user_answers'] = $userAnswers;
?>
