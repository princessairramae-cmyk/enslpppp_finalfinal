<?php

require_once 'config.php';

if(isset($_GET['work_order_id'])){

    $work_order_id = $_GET['work_order_id'];

    /* GET FINISHED PRODUCT */

    $stmt = $conn->prepare("
    SELECT inventory_items.id, inventory_items.item_name
    FROM work_orders
    LEFT JOIN inventory_items
        ON work_orders.product_name = inventory_items.item_name
    WHERE work_orders.id=?
    LIMIT 1
    ");

    $stmt->execute([$work_order_id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    /* GET LAMINATION QUANTITY */

    $laminationStmt = $conn->prepare("
    SELECT quantity
    FROM lamination_jobs
    WHERE work_order_id=?
    AND status='Completed'
    ORDER BY id DESC
    LIMIT 1
    ");

    $laminationStmt->execute([$work_order_id]);

    $lamination = $laminationStmt->fetch(PDO::FETCH_ASSOC);

    if($row){

        echo json_encode([
            'success'   => true,
            'id'        => $row['id'],
            'item_name' => $row['item_name'],
            'quantity'  => $lamination['quantity'] ?? 0
        ]);

    }else{

        echo json_encode([
            'success' => false
        ]);

    }

}
?>