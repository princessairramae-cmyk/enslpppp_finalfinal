<?php
session_start();

if(isset($_GET['index'])){
    unset($_SESSION['cart'][$_GET['index']]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

header("Location: client_cart.php");
exit();