<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$gate = $_POST['gate'] ?? '';
$spz = $_POST['spz'] ?? '';
$carrier = $_POST['carrier'] ?? '';
$info = $_POST['info'] ?? '';
$feedback = $_POST['feedback'] ?? '';
$status = $_POST['status'] ?? 'waiting';

if (!$gate || !$spz || !$carrier) {
    echo json_encode(["success" => false, "error" => "Missing fields"]);
    exit;
}

// 🔥 UTC čas
$sql = "INSERT INTO traffic (gate, spz, carrier, info, feedback, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, GETUTCDATE())";

$params = [$gate, $spz, $carrier, $info, $feedback, $status];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode([
        "success" => false,
        "error" => sqlsrv_errors()
    ]);
    exit;
}

echo json_encode(["success" => true]);