<?php
$score = 0;
$userCorrectAnswers = [];

foreach ($testData['answers'] as $qId => $validAnswers) {
    $userAnswer = isset($_POST["q_$qId"]) ? trim(mb_strtolower($_POST["q_$qId"])) : '';
    
    if (in_array($userAnswer, $validAnswers)) {
        $score++;
        $userCorrectAnswers[] = (int)$qId;
    }
}

$levelLabel = '';
foreach ($testData['levels'] as $level) {
    if ($score <= $level['max']) {
        $levelLabel = $level['label'];
        break;
    }
}

$packedResult['score'] = $score;
$packedResult['result_name'] = "Рівень інтелекту: " . $levelLabel;
$packedResult['result_description'] = "Кількість правильних відповідей: " . $score . " з 50.";

// Calculate Sub-scales
$subScores = [];
foreach ($testData['subscales'] as $scaleName => $questionIds) {
    $scaleScore = 0;
    $scaleTotal = count($questionIds);
    foreach ($questionIds as $id) {
        if (in_array($id, $userCorrectAnswers)) {
            $scaleScore++;
        }
    }
    $subScores[$scaleName] = "$scaleScore / $scaleTotal";
}

$packedResult['subscales_scores'] = $subScores;
?>
