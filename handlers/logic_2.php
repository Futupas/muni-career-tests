<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$scores = [];
$userAnswers = [];
$elevatedScales = [];
$highScales = [];

// 1. Process User Answers
foreach ($testData['questions'] as $q) {
    $qId = $q['id'];
    $val = $_POST["q_$qId"] ?? null;
    if ($val !== null) {
        $userAnswers[mb_strimwidth($q['text'], 0, $n, "...")] = ($val == '1' ? 'Так' : 'Ні');
    }
}

// 2. Calculate Scores
foreach ($testData['scales'] as $code => $scale) {
    $scores[$code] = 0;
    if (isset($scale['yes'])) {
        foreach ($scale['yes'] as $id) {
            if (($_POST["q_$id"] ?? '') == '1') $scores[$code]++;
        }
    }
    if (isset($scale['no'])) {
        foreach ($scale['no'] as $id) {
            if (($_POST["q_$id"] ?? '') == '0') $scores[$code]++;
        }
    }
    
    // Determine elevated/high status
    if (!in_array($code, ['L', 'F'])) {
        if ($scores[$code] >= 5) $elevatedScales[] = $code;
        if ($scores[$code] >= 8) $highScales[] = $code;
    }
}

// 3. Map Interpretations and Combinations
$resultsDescription = [];
foreach ($scores as $code => $score) {
    if (isset($testData['scales'][$code])) {
        $resultsDescription[] = $testData['scales'][$code]['name'] . ": " . $score;
    }
}

$combinations = [];
foreach ($testData['combinations'] as $combo) {
    $match = true;
    if (isset($combo['requires'])) {
        foreach ($combo['requires'] as $req) {
            if (!in_array($req, $elevatedScales)) $match = false;
        }
    }
    if (isset($combo['requires_high'])) {
        foreach ($combo['requires_high'] as $req) {
            if (!in_array($req, $highScales)) $match = false;
        }
    }
    if ($match) $combinations[] = $combo['name'] . ": " . $combo['description'];
}

$packedResult['user_answers'] = $userAnswers;
$packedResult['scores'] = $scores;
$packedResult['result_name'] = "Результати ІТО";
$packedResult['result_description'] = implode("; ", $resultsDescription);
$packedResult['combinations'] = $combinations;
?>
