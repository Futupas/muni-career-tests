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
}

$packedResult['user_answers'] = $userAnswers;
$packedResult['scores'] = $scores;
?>
