<?php
require 'db.php';

$sql = "SELECT
            id,
            gate,
            spz,
            carrier,
            info,
            feedback,
            status,
            queue_number,
            created_at,
            updated_at
        FROM traffic
        ORDER BY created_at DESC";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "error" => sqlsrv_errors()
    ]);
    exit;
}

$data = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

    if ($row['created_at'] instanceof DateTime) {
        $row['created_at'] = $row['created_at']->format('c');
    }

    if ($row['updated_at'] instanceof DateTime) {
        $row['updated_at'] = $row['updated_at']->format('c');
    }

    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);