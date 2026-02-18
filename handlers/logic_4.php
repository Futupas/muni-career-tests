<?php
$blockId = $testData['questions'][0]['id'];
$val = $_POST['q_' . $blockId] ?? '';
$res = $testData['results'][$val] ?? null;

$packedResult['result_key'] = $val;
$packedResult['result_name'] = $res['name'] ?? 'Unknown';
$packedResult['result_description'] = $res['description'] ?? 'N/A';
$packedResult['result_professions'] = $res['recommendedProfessions'] ?? 'N/A';
