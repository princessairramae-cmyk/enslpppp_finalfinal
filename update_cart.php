<?php
session_start();

if(isset($_POST['index']) && isset($_POST['qty'])){

    $index = $_POST['index'];
    $qty = (int) $_POST['qty'];

    if($qty <= 0){
        // remove if zero
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }else{
        $_SESSION['cart'][$index]['qty'] = $qty;
    }
}

header("Location: client_cart.php");
exit();