<?php
$scores = [];
$warnings = [];
$elevatedScales = [];
$highScales = [];
$professions = [];
$matchedCombinations = [];

// Calculate scores
foreach ($testData['scales'] as $code => $scale) {
    $currentScore = 0;
    if (isset($scale['yes'])) {
        foreach ($scale['yes'] as $id) {
            if (($_POST["q_$id"] ?? '') == '1') $currentScore++;
        }
    }
    if (isset($scale['no'])) {
        foreach ($scale['no'] as $id) {
            if (($_POST["q_$id"] ?? '') == '0') $currentScore++;
        }
    }
    $scores[$code] = $currentScore;

    // Track elevated (>=5) and high (>=8) scores for main scales (excluding L and F)
    if (!in_array($code, ['L', 'F'])) {
        if ($currentScore >= 5) {
            $elevatedScales[] = $code;
            if (isset($scale['professions'])) {
                $professions[] = $scale['name'] . ": " . $scale['professions'];
            }
        }
        if ($currentScore >= 8) {
            $highScales[] = $code;
        }
    }
}

// Validity checks
if ($scores['L'] > 5) $warnings[] = "Високий показник нещирості (шкала L). Дані можуть бути недостовірними.";
if ($scores['F'] > 5) $warnings[] = "Висока схильність підкреслювати проблеми (шкала F). Дані можуть бути недостовірними.";

// Check Combinations
foreach ($testData['combinations'] as $combo) {
    $match = true;
    
    if (isset($combo['requires'])) {
        foreach ($combo['requires'] as $reqCode) {
            if (!in_array($reqCode, $elevatedScales)) {
                $match = false;
                break;
            }
        }
    }

    if (isset($combo['requires_high'])) {
        foreach ($combo['requires_high'] as $reqCode) {
            if (!in_array($reqCode, $highScales)) {
                $match = false;
                break;
            }
        }
    }

    if ($match) {
        $matchedCombinations[] = $combo['name'] . " - " . $combo['description'];
    }
}

// Pack results
$packedResult['scores'] = $scores;
$packedResult['warnings'] = $warnings;
$packedResult['combinations'] = $matchedCombinations;
$packedResult['professions'] = $professions;

$packedResult['result_name'] = "Результати ІТО";

// Build concise description text
$descParts = [];
foreach ($testData['scales'] as $code => $scale) {
    $descParts[] = "{$scale['name']}: {$scores[$code]}";
}
$packedResult['result_description'] = implode("; ", $descParts);

?>
