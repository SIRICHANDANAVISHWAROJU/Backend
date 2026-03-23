<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request";
    exit;
}

$survey_name = $_POST['survey_name'];

if (!isset($_FILES['file'])) {
    echo "No file uploaded";
    exit;
}

$file = $_FILES['file'];

$handle = fopen($file['tmp_name'], "r");

if (!$handle) {
    echo "Cannot read file";
    exit;
}

$rowNumber = 0;

echo "<h2>Survey: $survey_name</h2>";

echo "<form method='POST' action='submit_answers.php'>";

while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

    $rowNumber++;

    if ($rowNumber == 1) {
        continue; // skip header
    }

    $question = $row[0];
    $correct = $row[1];

    $options = [];
    $options[] = $correct;

    for ($i = 2; $i < count($row); $i++) {
        $options[] = $row[$i];
    }

    shuffle($options); // mix answers

    echo "<p><b>$rowNumber. $question</b></p>";

    foreach ($options as $option) {
        echo "<input type='radio' name='q$rowNumber' value='$option' required> $option <br>";
    }

    echo "<br>";

}

fclose($handle);

echo "<input type='submit' value='Submit Answers'>";
echo "</form>";

?>