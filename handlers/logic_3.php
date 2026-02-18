<?php
$score = 0;
foreach ($testData['questions'] as $q) {
    $ans = $_POST['q_' . $q['id']] ?? '';
    if (in_array($q['id'], $testData['scoring_key'][$ans] ?? [])) $score++;
}
$packedResult['score'] = $score;
foreach ($testData['results'] as $range => $data) {
    list($min, $max) = explode('-', $range);
    if ($score >= $min && $score <= $max) {
        $packedResult['result_name'] = $data['name'];
        $packedResult['result_description'] = $data['description'];
        break;
    }
}
