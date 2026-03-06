<?php
$scores = [];
$detailedResults = [];

// Calculate score for each scale
foreach ($testData['scales'] as $code => $scaleData) {
    $score = 0;
    foreach ($scaleData['keys'] as $qId) {
        if (isset($_POST["q_$qId"]) && $_POST["q_$qId"] === '1') {
            $score++;
        }
    }
    
    // Determine level string
    $levelText = "";
    foreach ($testData['levels'] as $lvl) {
        if ($score >= $lvl['min'] && $score <= $lvl['max']) {
            $levelText = $lvl['text'];
            break;
        }
    }

    $scores[$code] = $score;
    
    $detailedResults[] = [
        'name' => $scaleData['name'],
        'score' => $score,
        'level' => $levelText,
        'description' => $scaleData['description']
    ];
}

$packedResult['result_name'] = "Результати: Типи мислення";
$packedResult['scores'] = $scores;
$packedResult['details'] = $detailedResults;

// Create a simple string description for quick overview
$descStrings = [];
foreach ($detailedResults as $res) {
    $descStrings[] = $res['name'] . ": " . $res['score'] . " (" . $res['level'] . ")";
}
$packedResult['result_description'] = implode("; ", $descStrings);

?>
