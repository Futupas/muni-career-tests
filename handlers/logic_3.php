<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$scores = ['R'=>0, 'I'=>0, 'S'=>0, 'K'=>0, 'P'=>0, 'A'=>0];
$userAnswers = [];

foreach ($testData['questions'] as $q) {
    $qId = $q['id'];
    $choiceText = $_POST["q_$qId"] ?? null;
    
    if ($choiceText) {
        // Iterate through options to find the matching type code
        foreach ($q['options'] as $option) {
            if ($option['text'] === $choiceText) {
                $type = $option['value'];
                $scores[$type]++;
                $userAnswers["Пара $qId"] = mb_strimwidth('[' . $type . '] ' . $choiceText, 0, $n, "...");
                break;
            }
        }
    }
}

arsort($scores);
$topTypeKey = array_key_first($scores);
$res = $testData['results'][$topTypeKey] ?? null;

$packedResult['user_answers'] = $userAnswers;
$packedResult['result_name'] = $res['name'] ?? 'Невідомо';
$packedResult['result_description'] = $res['description'] ?? '';
$packedResult['scores'] = $scores;
?>
