<?php
$packedResult['user_answers'] = [];
foreach ($testData['questions'] as $q) {
    $packedResult['user_answers'][$q['text']] = $_POST['q_' . $q['id']] ?? '';
}
