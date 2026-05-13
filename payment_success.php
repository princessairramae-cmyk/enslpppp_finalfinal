<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['client_id'])){
    header("Location: client_login.php");
    exit();
}

if(empty($_SESSION['cart'])){
    header("Location: client_cart.php");
    exit();
}

$method = $_POST['method'];

/* UPLOAD IMAGE */
$filename = time() . "_" . $_FILES['proof']['name'];
$tempname = $_FILES['proof']['tmp_name'];

move_uploaded_file($tempname, "uploads/" . $filename);

/* SAVE ORDERS */
foreach($_SESSION['cart'] as $item){

    $stmt = $conn->prepare("
        INSERT INTO orders (
            client_id,
            order_details,
            quantity,
            status,
            payment_method,
            payment_status,
            proof_of_payment
        )
        VALUES (?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $_SESSION['client_id'],
        $item['product'],
        $item['qty'],
        'Pending',
        $method,
        'Pending Verification',
        $filename
    ]);
}

/* CLEAR CART */
unset($_SESSION['cart']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Submitted</title>
</head>
<body>

<h1>Payment Submitted!</h1>

<p>Your proof of payment has been uploaded.</p>

<p>Please wait for admin verification.</p>

<a href="client_orders.php">
    View Orders
</a>

</body>
</html>