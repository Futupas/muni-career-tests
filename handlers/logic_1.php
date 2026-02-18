<?php
$selectedIds = [];
foreach ($testData['questions'] as $block) {
    if (isset($_POST['q_' . $block['id']])) $selectedIds[] = $_POST['q_' . $block['id']];
}
sort($selectedIds);
$key = implode('', $selectedIds);
$res = $testData['results'][$key] ?? null;
$packedResult['result_key'] = $key;
$packedResult['result_name'] = $res['name'] ?? 'Unknown';
$packedResult['result_description'] = $res['description'] ?? '';
$packedResult['result_professions'] = $res['recommendedProfessions'] ?? '';
