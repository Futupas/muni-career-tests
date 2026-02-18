<?php
$scores = [];
foreach ($testData['questions'] as $block) {
    $val = $_POST['q_' . $block['id']] ?? null;
    if ($val) {
        $scores[$val] = ($scores[$val] ?? 0) + 1;
    }
}
arsort($scores);
$key = array_key_first($scores);
$res = $testData['results'][$key] ?? null;
$packedResult['result_key'] = $key;
$packedResult['result_name'] = $res['name'] ?? 'Unknown';
$packedResult['result_description'] = $res['description'] ?? '';
$packedResult['result_professions'] = $res['recommendedProfessions'] ?? '';
