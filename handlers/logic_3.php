<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$scores = ['R'=>0, 'I'=>0, 'S'=>0, 'K'=>0, 'P'=>0, 'A'=>0];
$userAnswers = [];

foreach ($testData['questions'] as $q) {
    $qId = $q['id'];
    $val = $_POST["q_$qId"] ?? null;
    
    if ($val) {
        // Increment score for the chosen type
        $scores[$val]++;
        
        // Find the text associated with the chosen type in this pair
        $text = ($q['a']['type'] === $val) ? $q['a']['text'] : $q['b']['text'];
        
        $userAnswers["Пара $qId"] = mb_strimwidth($text, 0, $n, "...");
    }
}

arsort($scores);
$topTypeKey = array_key_first($scores);
$res = $testData['results'][$topTypeKey] ?? null;

$packedResult['user_answers'] = $userAnswers;
$packedResult['result_name'] = $res['name'] ?? 'Unknown';
$packedResult['result_description'] = $res['description'] ?? '';
$packedResult['scores'] = $scores;
?>
