<?php

$serverName = "blatna-apps-db.database.windows.net";

$connectionOptions = [
    "Database" => "Warehouse_traffic",
    "Uid" => "trafficAppUser",
    "PWD" => "StrongPassword123!",
    "CharacterSet" => "UTF-8",
    "Encrypt" => true,
    "TrustServerCertificate" => false
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}