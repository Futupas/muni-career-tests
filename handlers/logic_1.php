<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$userAnswers = [];
$selectedIds = [];
foreach ($testData['questions'] as $block) {
    $val = $_POST['q_' . $block['id']] ?? null;
    if ($val) {
        $selectedIds[] = $val;
        // Map value to text
        foreach($block['options'] as $opt) {
            if($opt['value'] == $val) {
                $userAnswers[$block['name']] = mb_strimwidth($opt['text'], 0, $n, "...");
            }
        }
    }
}
sort($selectedIds);
$key = implode('', $selectedIds);
$res = $testData['results'][$key] ?? null;

$packedResult['user_answers'] = $userAnswers;
$packedResult['result_key'] = $key;
$packedResult['result_name'] = $res['name'] ?? 'Unknown';
$packedResult['result_description'] = $res['description'] ?? '';
$packedResult['result_professions'] = $res['professions'] ?? '';
?>
