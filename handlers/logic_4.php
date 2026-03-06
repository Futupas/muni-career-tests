<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$score = 0;
$userAnswers = [];

foreach ($testData['questions'] as $q) {
    $qId = $q['id'];
    $val = $_POST["q_$qId"] ?? '';
    $userAnswers[mb_strimwidth($q['text'], 0, $n, "...")] = $val;
    
    if (in_array(trim(mb_strtolower($val)), $testData['answers'][$qId])) {
        $score++;
    }
}

$packedResult['user_answers'] = $userAnswers;
$packedResult['score'] = $score;
?>
