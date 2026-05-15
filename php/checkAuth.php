<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["authenticated" => false]);
    exit;
}

echo json_encode([
    "authenticated" => true,
    "role" => $_SESSION['role']
]);