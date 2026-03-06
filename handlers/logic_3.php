<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$scores = ['R'=>0, 'I'=>0, 'S'=>0, 'K'=>0, 'P'=>0, 'A'=>0];
$userAnswers = [];

foreach ($testData['questions'] as $q) {
    $val = $_POST["q_" . $q['id']] ?? null;
    if ($val && isset($scores[$val])) {
        $scores[$val]++;
        // Find the text for this choice
        // $text = ($q['a']['type'] == $val) ? $q['a']['text'] : $q['b']['text'];
        $text = $val . ' --- ' . $q['a']['type'] . ' --- ' . $q['a']['text'] . '---' . $q['b']['text'] . ' --- ' . $q['options'][0]['text'];
        $userAnswers["Пара " . $q['id']] = mb_strimwidth($text, 0, $n, "...");
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
