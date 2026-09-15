<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized"
    ]);
    exit;
}

$gate = $_POST['gate'] ?? '';
$spz = $_POST['spz'] ?? '';
$carrier = $_POST['carrier'] ?? '';
$info = $_POST['info'] ?? '';
$feedback = $_POST['feedback'] ?? '';
$status = $_POST['status'] ?? 'waiting';

if (!$gate || !$spz || !$carrier) {
    echo json_encode([
        "success" => false,
        "error" => "Missing fields"
    ]);
    exit;
}


// =====================================================
// 1. VYTVOŘENÍ NOVÉHO ZÁZNAMU
// =====================================================

$sql = "INSERT INTO traffic (
            gate,
            spz,
            carrier,
            info,
            feedback,
            status,
            created_at
        )
        OUTPUT
            INSERTED.id,
            INSERTED.created_at
        VALUES (?, ?, ?, ?, ?, ?, GETUTCDATE())";

$params = [
    $gate,
    $spz,
    $carrier,
    $info,
    $feedback,
    $status
];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode([
        "success" => false,
        "error" => sqlsrv_errors()
    ]);
    exit;
}


// =====================================================
// 2. ZÍSKAT ID A CREATED_AT NOVÉHO ZÁZNAMU
// =====================================================

$newRow = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$newRow) {
    echo json_encode([
        "success" => false,
        "error" => "Could not retrieve created traffic record."
    ]);
    exit;
}

$trafficId = $newRow['id'];
$createdAt = $newRow['created_at'];


// =====================================================
// 3. ZAPSAT NOVÝ ZÁZNAM DO STATUS CHANGES
// =====================================================

$sqlLog = "INSERT INTO TrafficStatusChanges (
                TrafficId,
                Gate,
                SPZ,
                Carrier,
                Info,
                Feedback,
                OldStatus,
                NewStatus,
                QueueNumber,
                CreatedAt
           )
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$paramsLog = [
    $trafficId,
    $gate,
    $spz,
    $carrier,
    $info,
    $feedback,
    null,
    $status,
    null,
    $createdAt
];

$stmtLog = sqlsrv_query($conn, $sqlLog, $paramsLog);

if ($stmtLog === false) {
    echo json_encode([
        "success" => false,
        "error" => "Traffic created, but status change could not be logged.",
        "sql_error" => sqlsrv_errors()
    ]);
    exit;
}


// =====================================================
// 4. ÚSPĚCH
// =====================================================

echo json_encode([
    "success" => true,
    "id" => $trafficId
]);