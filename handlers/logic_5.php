<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$scores = [];
$userAnswers = [];

foreach ($testData['questions'] as $q) {
    $qId = $q['id'];
    $val = $_POST["q_$qId"] ?? '0';
    $userAnswers[mb_strimwidth($q['text'], 0, $n, "...")] = ($val == '1' ? 'Так' : 'Ні');
}

foreach ($testData['scales'] as $code => $scaleData) {
    $scores[$code] = 0;
    foreach ($scaleData['keys'] as $qId) {
        if (($_POST["q_$qId"] ?? '') == '1') $scores[$code]++;
    }
    
    // Map score to description from JSON
    $levelText = "";
    foreach ($testData['levels'] as $lvl) {
        if ($scores[$code] >= $lvl['min'] && $scores[$code] <= $lvl['max']) {
            $levelText = $lvl['text'];
            break;
        }
    }
    $scores[$code] = ['score' => $scores[$code], 'level' => $levelText];
}

$packedResult['user_answers'] = $userAnswers;
$packedResult['result_data'] = $scores;
$packedResult['result_name'] = "Результати: Типи мислення";
$packedResult['result_description'] = "Обробку завершено. Деталі у result_data.";
?>
