<?php
require 'db.php';
session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$sql = "SELECT * FROM users WHERE username = ?";
$params = [$username];

$stmt = sqlsrv_query($conn, $sql, $params);
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if ($user && $user['password'] === $password) {

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}