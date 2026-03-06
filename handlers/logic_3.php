<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$scores = ['R'=>0, 'I'=>0, 'S'=>0, 'K'=>0, 'P'=>0, 'A'=>0];
$userAnswers = [];

foreach ($testData['questions'] as $q) {
    $qId = $q['id'];
    $choice = $_POST["q_$qId"] ?? null; // 'a' or 'b'
    
    if ($choice) {
        $selectedOption = $q[$choice]; // gets the array ['text' => '...', 'type' => '...']
        $type = $selectedOption['type'];
        
        $scores[$type]++;
        $userAnswers["Пара $qId"] = mb_strimwidth($selectedOption['text'], 0, $n, "...");
    }
}

arsort($scores);
$topTypeKey = array_key_first($scores);
$res = $testData['types_info'][$topTypeKey] ?? null;

$packedResult['user_answers'] = $userAnswers;
$packedResult['result_name'] = $res['name'] ?? 'Невідомо';
$packedResult['result_description'] = $res['desc'] ?? '';
$packedResult['scores'] = $scores;
?>
