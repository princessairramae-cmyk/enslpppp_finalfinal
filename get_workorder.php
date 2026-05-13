<?php

require_once 'config.php';

if(isset($_GET['work_order_id'])){

    $wo_id = $_GET['work_order_id'];

    $stmt = $conn->prepare("
        SELECT product_name, qty
        FROM work_orders
        WHERE id=?
    ");

    $stmt->execute([$wo_id]);

    $wo = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($wo);
}
?>