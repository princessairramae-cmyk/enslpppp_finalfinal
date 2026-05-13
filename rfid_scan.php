<?php

ob_clean();

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

include 'config.php';

date_default_timezone_set('Asia/Manila');

try{

    // RFID VALUE
    $rfid = isset($_POST['rfid'])
        ? trim($_POST['rfid'])
        : '';

    // REMOVE SPACES
    $rfid = str_replace(["\n","\r"," "], "", $rfid);

    // EMPTY RFID
    if(empty($rfid)){

        echo json_encode([
            "status" => "invalid",
            "name" => "No RFID",
            "photo" => "assets/images/default.png"
        ]);

        exit;
    }

    // FIND EMPLOYEE
    $stmt = $conn->prepare("
        SELECT *
        FROM employees
        WHERE rfid_uid = ?
        LIMIT 1
    ");

    $stmt->execute([$rfid]);

    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    // UNKNOWN RFID
    if(!$employee){

        echo json_encode([
            "status" => "invalid",
            "name" => "Unknown Card",
            "photo" => "assets/images/default.png"
        ]);

        exit;
    }

    $emp_id = $employee['id'];

    $name = $employee['full_name'];

    $photo = !empty($employee['photo'])
        ? "assets/images/".$employee['photo']
        : "assets/images/default.png";

    $today = date("Y-m-d");

    $time_now = date("H:i:s");

    // CHECK ATTENDANCE TODAY
    $check = $conn->prepare("
        SELECT *
        FROM attendance
        WHERE employee_id = ?
        AND att_date = ?
        LIMIT 1
    ");

    $check->execute([$emp_id, $today]);

    $attendance = $check->fetch(PDO::FETCH_ASSOC);

    // =========================
    // TIME IN
    // =========================
    if(!$attendance){

        $insert = $conn->prepare("
            INSERT INTO attendance
            (
                employee_id,
                att_date,
                time_in,
                status,
                remarks
            )
            VALUES
            (
                ?,
                ?,
                ?,
                'Present',
                'RFID Time In'
            )
        ");

        $insert->execute([
            $emp_id,
            $today,
            $time_now
        ]);

        echo json_encode([
            "status" => "time_in",
            "name" => $name,
            "photo" => $photo,
            "time" => $time_now
        ]);

        exit;
    }

    // =========================
    // TIME OUT
    // =========================
    if(empty($attendance['time_out'])){

        $update = $conn->prepare("
            UPDATE attendance
            SET
                time_out = ?,
                remarks = 'RFID Time Out'
            WHERE id = ?
        ");

        $update->execute([
            $time_now,
            $attendance['id']
        ]);

        echo json_encode([
            "status" => "time_out",
            "name" => $name,
            "photo" => $photo,
            "time" => $time_now
        ]);

        exit;
    }

    // =========================
    // ALREADY RECORDED
    // =========================
    echo json_encode([
        "status" => "already",
        "name" => $name,
        "photo" => $photo
    ]);

}catch(Throwable $e){

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
        "photo" => "assets/images/default.png"
    ]);

}
?>