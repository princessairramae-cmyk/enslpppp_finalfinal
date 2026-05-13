<?php
include 'config.php';

$id = $_GET['id'] ?? 0;

// ORDER
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->execute([$id]);
$order = $stmt->fetch();

// PRICE
$stmt = $conn->prepare("
SELECT selling_price 
FROM inventory_items 
WHERE item_name=?
");
$stmt->execute([$order['order_details']]);
$item = $stmt->fetch();

$price = $item['selling_price'] ?? 0;
$total = $price * $order['quantity'];
?>

<!DOCTYPE html>
<html>
<head>
<title>View Order</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f1f5f9;
    font-family:'Segoe UI';
}

.container-box{
    max-width:700px;
    margin:50px auto;
}

.card{
    border-radius:15px;
    border:none;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.header{
    background:#16a34a;
    color:white;
    padding:15px;
    border-radius:15px 15px 0 0;
    text-align:center;
}

.section{
    padding:20px;
}

.badge{
    font-size:14px;
}
</style>

</head>

<body>

<div class="container-box">

<div class="card">

<div class="header">
<h4>EnSLP Inc.</h4>
</div>

<div class="section">

<h5 class="text-success">Order Details ✅</h5>

<p><b>Order ID:</b> <?= $order['id'] ?></p>
<p><b>Product:</b> <?= $order['order_details'] ?></p>
<p><b>Quantity:</b> <?= $order['quantity'] ?></p>
<p><b>Price:</b> ₱<?= number_format($price,2) ?></p>
<p><b>Total:</b> ₱<?= number_format($total,2) ?></p>

<p><b>Status:</b> 
<span class="badge bg-success"><?= $order['status'] ?></span>
</p>

</div>

</div>

</div>

</body>
</html>