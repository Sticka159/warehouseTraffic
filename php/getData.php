<?php
require 'db.php';

$sql = "SELECT * FROM traffic ORDER BY created_at DESC";
$stmt = sqlsrv_query($conn, $sql);

$data = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

    // ✅ created_at (už správně)
    if ($row['created_at'] instanceof DateTime) {
        $row['created_at'] = $row['created_at']->format('c');
    }

    // 🔥 FIX: updated_at stejný formát jako created_at
    if ($row['updated_at'] instanceof DateTime) {
        $row['updated_at'] = $row['updated_at']->format('c');
    }

    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);