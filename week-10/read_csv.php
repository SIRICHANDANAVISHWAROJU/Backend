<?php
header("Content-Type: application/json");

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}

// Check file upload
if (!isset($_FILES['file'])) {
    echo json_encode([
        "status" => false,
        "message" => "No file uploaded"
    ]);
    exit;
}

$file = $_FILES['file'];

// Validate file type (basic check)
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($fileExtension !== 'csv') {
    echo json_encode([
        "status" => false,
        "message" => "Only CSV files are allowed"
    ]);
    exit;
}

// Open CSV file
$handle = fopen($file['tmp_name'], "r");

if (!$handle) {
    echo json_encode([
        "status" => false,
        "message" => "Unable to read file"
    ]);
    exit;
}

$data = [];
$rowNumber = 0;

// Read CSV
while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

    $rowNumber++;

    // Skip empty rows
    if (count($row) < 3) {
        continue;
    }

    // Optional: Skip header row
    if ($rowNumber == 1 && strtolower($row[0]) == 'name') {
        continue;
    }

    $data[] = [
        "question" => trim($row[0]),
        "option1" => trim($row[1]),
        "option2" => trim($row[2]),  // Plain text (not hashed)
        "option3" => trim($row[3]),
        "option4" => trim($row[4]) 
    ];
}

fclose($handle);

// Output JSON
echo json_encode([
    "status" => true,
    "message" => "CSV processed successfully",
    "total_records" => count($data),
    "data" => $data
]);