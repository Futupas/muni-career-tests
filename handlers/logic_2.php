<?php
$scores = [];
$validityWarning = "";

// Initialize scores for all scales
foreach ($testData['scales'] as $code => $scaleData) {
    $scores[$code] = 0;
}

// Calculate scores based on keys
foreach ($testData['scales'] as $code => $scaleData) {
    // Check 'yes' keys
    if (isset($scaleData['keys']['yes'])) {
        foreach ($scaleData['keys']['yes'] as $qId) {
            if (isset($_POST["q_$qId"]) && $_POST["q_$qId"] == '1') {
                $scores[$code]++;
            }
        }
    }
    // Check 'no' keys
    if (isset($scaleData['keys']['no'])) {
        foreach ($scaleData['keys']['no'] as $qId) {
            if (isset($_POST["q_$qId"]) && $_POST["q_$qId"] == '0') {
                $scores[$code]++;
            }
        }
    }
}

// Check Validity
if ($scores['L'] > 5) {
    $validityWarning .= "<div class='alert alert-warning'>Увага: Високий показник за шкалою 'Правдивість'. Результати можуть бути недостовірні (нещирість).</div>";
}
if ($scores['F'] > 5) {
    $validityWarning .= "<div class='alert alert-warning'>Увага: Високий показник за шкалою 'Агравація'. Результати можуть бути недостовірні (схильність підкреслювати проблеми).</div>";
}

$packedResult['result_name'] = "Індивідуально-типологічний профіль";
$packedResult['result_description'] = $validityWarning;

$packedResult['result_description'] .= "<table class='table table-bordered'>";
$packedResult['result_description'] .= "<tr><th>Шкала</th><th>Бал</th><th>Рівень</th><th>Опис</th></tr>";

// Order of display logic (excluding L and F for the main table usually, but keeping them for complete picture is fine or separating them)
// Let's display main scales I-VIII
$mainScales = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];

foreach ($mainScales as $code) {
    $score = $scores[$code];
    $info = $testData['scales'][$code];
    
    $levelText = "";
    if ($score <= 2) $levelText = "Низький (гіпоемотивність/нещирість)"; // 0-1 implies special condition
    elseif ($score <= 4) $levelText = $testData['levels']['norm'];
    elseif ($score <= 7) $levelText = $testData['levels']['accent'];
    else $levelText = $testData['levels']['high'];

    $packedResult['result_description'] .= "<tr>";
    $packedResult['result_description'] .= "<td>{$info['name']}</td>";
    $packedResult['result_description'] .= "<td><strong>{$score}</strong></td>";
    $packedResult['result_description'] .= "<td>{$levelText}</td>";
    $packedResult['result_description'] .= "<td>{$info['description']}</td>";
    $packedResult['result_description'] .= "</tr>";
}
$packedResult['result_description'] .= "</table>";

// Add specific text for combined types logic if needed, 
// for now, we provide the scale breakdown as the primary output.
?>
