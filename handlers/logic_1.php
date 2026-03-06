<?php
$parts = [];
// Iterate through expected question blocks (ids 1 to 4)
// We use the count of questions to be safe, but since I wrote the JSON, I know there are 4.
foreach ($testData['questions'] as $block) {
    if (isset($_POST["q_" . $block['id']])) {
        $parts[] = $_POST["q_" . $block['id']];
    }
}

// Sort the selected values numerically to form the key (e.g., 1, 3, 5, 7 -> "1357")
sort($parts);
$key = implode('', $parts);

// Lookup the result
$result = $testData['results'][$key] ?? null;

if ($result) {
    $packedResult['result_name'] = $result['name'];
    $packedResult['result_description'] = $result['description'];
    $packedResult['result_professions'] = $result['professions'];
} else {
    // Fallback if something goes wrong or no key matches
    $packedResult['result_name'] = "Результат не визначено";
    $packedResult['result_description'] = "На жаль, для вашої комбінації відповідей ($key) немає опису. Перевірте правильність заповнення тесту.";
    $packedResult['result_professions'] = "";
}
?>
