<?php
$val = $_POST['q_1'] ?? '';
$res = $testData['results'][$val] ?? null;
$packedResult['result_key'] = $val;
$packedResult['result_name'] = $res['name'] ?? 'Unknown';
$packedResult['result_description'] = $res['description'] ?? '';
$packedResult['result_professions'] = $res['recommendedProfessions'] ?? '';
