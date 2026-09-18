<?php
require 'db.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$id = $_POST['id'] ?? null;
$info = $_POST['info'] ?? null;
$feedback = $_POST['feedback'] ?? null;
$gate = $_POST['gate'] ?? null;
$spz = $_POST['spz'] ?? null;
$carrier = $_POST['carrier'] ?? null;
$status = $_POST['status'] ?? null;

$role = $_SESSION['role'] ?? 'worker';

if (!$id) {
    echo json_encode([
        "success" => false,
        "error" => "Missing ID"
    ]);
    exit;
}


// =====================================================
// 1. ZJISTIT STARÝ STAV + FRONTU
// =====================================================

$sqlOld = "SELECT status, queue_number
           FROM traffic
           WHERE id = ?";

$stmtOld = sqlsrv_query($conn, $sqlOld, [$id]);

$oldStatus = null;
$oldQueue = null;

if ($stmtOld && $row = sqlsrv_fetch_array($stmtOld, SQLSRV_FETCH_ASSOC)) {
    $oldStatus = $row['status'];
    $oldQueue = $row['queue_number'];
}


// =====================================================
// 2. ODCHOD Z FRONTY
// =====================================================

if (
    $oldStatus === 'waiting_load' &&
    $status !== 'waiting_load' &&
    $oldQueue !== null
) {

    $sqlShift = "UPDATE traffic
                 SET queue_number = queue_number - 1
                 WHERE queue_number > ?";

    sqlsrv_query($conn, $sqlShift, [$oldQueue]);
}


// =====================================================
// 3. NOVÉ ZAŘAZENÍ DO FRONTY
// =====================================================

$queueNumber = $oldQueue;

if (
    $status === 'waiting_load' &&
    $oldStatus !== 'waiting_load'
) {

    $sqlQueue = "SELECT ISNULL(MAX(queue_number), 0) + 1 AS nextQueue
                 FROM traffic";

    $stmtQueue = sqlsrv_query($conn, $sqlQueue);

    if (
        $stmtQueue &&
        $row = sqlsrv_fetch_array($stmtQueue, SQLSRV_FETCH_ASSOC)
    ) {
        $queueNumber = $row['nextQueue'];
    }
}


// =====================================================
// 4. POKUD NENÍ VE FRONTĚ → NULL
// =====================================================

if ($status !== 'waiting_load') {
    $queueNumber = null;
}


// =====================================================
// 5. UPDATE TRAFFIC
// =====================================================

if ($role === 'worker') {

    $sql = "UPDATE traffic
            SET info = ?,
                feedback = ?,
                status = ?,
                queue_number = ?,
                updated_at = GETUTCDATE()
            WHERE id = ?";

    $params = [
        $info,
        $feedback,
        $status,
        $queueNumber,
        $id
    ];

} else {

    $sql = "UPDATE traffic
            SET gate = ?,
                spz = ?,
                carrier = ?,
                info = ?,
                feedback = ?,
                status = ?,
                queue_number = ?,
                updated_at = GETUTCDATE()
            WHERE id = ?";

    $params = [
        $gate,
        $spz,
        $carrier,
        $info,
        $feedback,
        $status,
        $queueNumber,
        $id
    ];
}

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode([
        "success" => false,
        "error" => sqlsrv_errors()
    ]);
    exit;
}


// =====================================================
// 6. ZMĚNA STATUSU NA LOADED → ULOŽIT KOMPLETNÍ SNAPSHOT
// =====================================================

if ($oldStatus !== $status && $status === 'loaded') {

    // -------------------------------------------------
    // Načíst aktuální hodnoty přímo z traffic
    // -------------------------------------------------

    $sqlSnapshot = "SELECT
                        gate,
                        spz,
                        carrier,
                        info,
                        feedback,
                        queue_number,
                        created_at
                    FROM traffic
                    WHERE id = ?";

    $stmtSnapshot = sqlsrv_query(
        $conn,
        $sqlSnapshot,
        [$id]
    );

    if ($stmtSnapshot === false) {
        echo json_encode([
            "success" => false,
            "error" => "Traffic updated, but snapshot could not be loaded.",
            "sql_error" => sqlsrv_errors()
        ]);
        exit;
    }

    $snapshot = sqlsrv_fetch_array(
        $stmtSnapshot,
        SQLSRV_FETCH_ASSOC
    );

    if (!$snapshot) {
        echo json_encode([
            "success" => false,
            "error" => "Traffic updated, but snapshot record could not be found."
        ]);
        exit;
    }


    // -------------------------------------------------
    // Uložit kompletní snapshot
    // -------------------------------------------------

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
        $id,
        $snapshot['gate'],
        $snapshot['spz'],
        $snapshot['carrier'],
        $snapshot['info'],
        $snapshot['feedback'],
        $oldStatus,
        $status,
        $snapshot['queue_number'],
        $snapshot['created_at']
    ];

    $stmtLog = sqlsrv_query(
        $conn,
        $sqlLog,
        $paramsLog
    );

    if ($stmtLog === false) {
        echo json_encode([
            "success" => false,
            "error" => "Traffic updated, but status change could not be logged.",
            "sql_error" => sqlsrv_errors()
        ]);
        exit;
    }
}


// =====================================================
// 7. ÚSPĚCH
// =====================================================

echo json_encode([
    "success" => true
]);