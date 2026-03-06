<?php
$n = (int)($_ENV['QUESTION_TRUNCATE_LEN'] ?? 50);
$scores = [];
$userAnswers = [];
$elevatedScales = [];

// 1. Calculate Scores based on keys in JSON
foreach ($testData['scales'] as $code => $scale) {
    $currentScore = 0;
    if (isset($scale['yes'])) {
        foreach ($scale['yes'] as $id) {
            if (isset($_POST["q_$id"]) && $_POST["q_$id"] == '1') $currentScore++;
        }
    }
    if (isset($scale['no'])) {
        foreach ($scale['no'] as $id) {
            if (isset($_POST["q_$id"]) && $_POST["q_$id"] == '0') $currentScore++;
        }
    }
    
    // Determine level from JSON
    $level = "Низький рівень";
    if ($currentScore >= 8) $level = "Дезадаптивні властивості";
    elseif ($currentScore >= 5) $level = "Акцентуаційні риси";
    elseif ($currentScore >= 3) $level = "Гармонійна особистість";
             
    $scores[$code] = [
        'name' => $scale['name'],
        'score' => $currentScore,
        'level' => $level
    ];
    
    if ($currentScore >= 5) $elevatedScales[] = $code;
}

// 2. Populate User Answers
foreach ($testData['questions'] as $q) {
    $qId = $q['id'];
    $val = $_POST["q_$qId"] ?? null;
    if ($val !== null) {
        $userAnswers[mb_strimwidth($q['text'], 0, $n, "...")] = ($val == '1' ? 'Так' : 'Ні');
    }
}

// 3. Logic for Combinations defined in JSON
$matchedCombinations = [];
foreach ($testData['combinations'] as $combo) {
    $match = true;
    foreach ($combo['requires'] as $req) {
        if (!in_array($req, $elevatedScales)) $match = false;
    }
    if ($match) $matchedCombinations[] = $combo;
}

// 4. Pack results using JSON data
$packedResult['scores'] = $scores;
$packedResult['combinations'] = $matchedCombinations;
$packedResult['user_answers'] = $userAnswers;
?>
