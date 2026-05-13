<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

require_once 'config.php';
require_once 'work_order_materials.php';

header('Content-Type: application/json');

/* GET WORK ORDER ID */

$work_order_id = $_GET['work_order_id'] ?? '';

if(!$work_order_id){

    echo json_encode([]);
    exit;

}

/* GET WORK ORDER */

$stmt = $conn->prepare("
SELECT *
FROM work_orders
WHERE id=?
");

$stmt->execute([$work_order_id]);

$wo = $stmt->fetch(PDO::FETCH_ASSOC);

/* NO WORK ORDER */

if(!$wo){

    echo json_encode([]);
    exit;

}

/* PRODUCT NAME */

$product_name = trim($wo['product_name'] ?? '');

/* GET REQUIRED MATERIAL */

$required_material =
$work_order_materials[$product_name]['cutting']
?? '';

/* NO MATERIAL */

if(!$required_material){

    echo json_encode([]);
    exit;

}

/* GET INVENTORY ITEM */

$stmt = $conn->prepare("
SELECT *
FROM inventory_items
WHERE item_name=?
LIMIT 1
");

$stmt->execute([$required_material]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);

/* NO INVENTORY ITEM */

if(!$item){

    echo json_encode([]);
    exit;

}

/* AUTO DETECT ORDER QTY FIELD */

$order_qty = 0;

if(isset($wo['quantity'])){

    $order_qty = $wo['quantity'];

}
elseif(isset($wo['qty'])){

    $order_qty = $wo['qty'];

}
elseif(isset($wo['order_qty'])){

    $order_qty = $wo['order_qty'];

}
elseif(isset($wo['total_qty'])){

    $order_qty = $wo['total_qty'];

}

/* SEND JSON */

$data = [

    [
        'id' => $item['id'],

        'item_name' => $item['item_name'],

        'quantity' => $item['quantity'],

        'order_qty' => $order_qty
    ]

];

echo json_encode($data);