<?php
$scores = [
    'R' => 0,
    'I' => 0,
    'S' => 0,
    'K' => 0,
    'P' => 0,
    'A' => 0
];

foreach ($_POST as $key => $val) {
    if (strpos($key, 'q_') === 0 && isset($scores[$val])) {
        $scores[$val]++;
    }
}

arsort($scores);
$topKey = array_key_first($scores);
$res = $testData['results'][$topKey] ?? null;

$packedResult['result_key'] = $topKey;
$packedResult['result_name'] = $res['name'] ?? 'Unknown';
$packedResult['result_description'] = $res['description'] ?? '';
$packedResult['scores'] = $scores;
?>
