<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "csci6040_study";

$con = mysqli_connect($host, $username, $password, $database);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>


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
        "name" => trim($row[0]),
        "email" => trim($row[1]),
        "password" => trim($row[2])  // Plain text (not hashed)
    ];
}

fclose($handle);

// Output JSON
echo json_encode([
    "status" => true,
    "message" => "CSV processed successfully",
    "total_records" => count($data),

]);

$stmt=$stmt = $con->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
foreach ($data as $entry) {
    $hashed_password = md5($entry['password']);
    $stmt->bind_param("sss", $entry['name'], $entry['email'], $hashed_password);
    $stmt->execute();
}
$stmt->close();
echo json_encode([
    "status" => true,
    "message" => "File processed successfully",
    // "data" => $data[:3];
]);
