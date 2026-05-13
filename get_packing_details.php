<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

require_once "config.php";

header('Content-Type: application/json');

if(!isset($_GET['work_order_id'])){
    echo json_encode([
        "success" => false
    ]);
    exit;
}

$work_order_id = $_GET['work_order_id'];

/* GET PRODUCT FROM GOOD INSPECTION */
$stmt = $conn->prepare("
SELECT 
    w.id,
    i.id AS item_id,
    i.item_name

FROM work_orders w

INNER JOIN inspection_qc q
    ON w.id = q.work_order_id

INNER JOIN inventory_items i
    ON q.item_id = i.id

WHERE w.id = ?
AND q.status = 'Good'

LIMIT 1
");

$stmt->execute([$work_order_id]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$data){

    echo json_encode([
        "success" => false
    ]);

    exit;
}


/* GET GOOD QTY */
$stmt = $conn->prepare("
SELECT COALESCE(SUM(passed_qty),0) as passed_qty
FROM inspection_qc
WHERE work_order_id=?
AND status='Good'
");

$stmt->execute([$work_order_id]);

$passed = $stmt->fetch(PDO::FETCH_ASSOC);


/* GET PACKED QTY */
$stmt = $conn->prepare("
SELECT COALESCE(SUM(quantity_packed),0) as packed_qty
FROM packing_jobs
WHERE work_order_id=?
");

$stmt->execute([$work_order_id]);

$packed = $stmt->fetch(PDO::FETCH_ASSOC);


/* COMPUTE REMAINING */
$remaining_qty = $passed['passed_qty'] - $packed['packed_qty'];


/* VALIDATION */
if($remaining_qty < 0){
    $remaining_qty = 0;
}


/* RETURN JSON */
echo json_encode([

    "success" => true,

    "item_id" => $data['item_id'],

    "item_name" => $data['item_name'],

    "remaining_qty" => $remaining_qty

]);
?>