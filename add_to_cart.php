<?php
session_start();

if(!isset($_SESSION['client_id'])){
    header("Location: client_login.php");
    exit();
}

$product = $_POST['product'];
$price = $_POST['price'];

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

// check if existing
$found = false;

foreach($_SESSION['cart'] as &$item){
    if($item['product'] == $product){
        $item['qty'] += 1;
        $found = true;
        break;
    }
}

if(!$found){
    $_SESSION['cart'][] = [
        'product' => $product,
        'price' => $price,
        'qty' => 1
    ];
}

// balik sa products
header("Location: client_products.php");
exit();