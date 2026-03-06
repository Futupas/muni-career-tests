<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$scores = [];
$userAnswers = [];

foreach ($testData['questions'] as $q) {
    $qId = $q['id'];
    $val = $_POST["q_$qId"] ?? null;
    if ($val !== null) {
        $userAnswers[$q['text']] = ($val == '1' ? 'Так' : 'Ні');
    }
}

foreach ($testData['scales'] as $code => $scale) {
    $scores[$code] = 0;
    foreach ($scale['keys'] as $keyType => $qIds) {
        foreach ($qIds as $qId) {
            $val = $_POST["q_$qId"] ?? null;
            if ($val !== null && (($keyType == 'yes' && $val == '1') || ($keyType == 'no' && $val == '0'))) {
                $scores[$code]++;
            }
        }
    }
}

$packedResult['user_answers'] = $userAnswers;
$packedResult['scores'] = $scores;
?>
