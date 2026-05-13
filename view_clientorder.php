<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();
require_once 'config.php';

if(!isset($_SESSION['client_id'])){
    header("Location: client_login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: client_orders.php");
    exit();
}

$order_id = $_GET['id'];
$client_id = $_SESSION['client_id'];

/* GET ORDER */

$stmt = $conn->prepare("
SELECT 
    o.*,
    w.status as work_status,
    
    (
        SELECT d.status
        FROM deliveries d
        WHERE d.wo_id = w.id
        ORDER BY d.id DESC
        LIMIT 1
    ) as delivery_status

FROM orders o

LEFT JOIN work_orders w
ON w.product_name = o.order_details

WHERE o.id=? 
AND o.client_id=?
");

$stmt->execute([$order_id, $client_id]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order){
    header("Location: client_orders.php");
    exit();
}

/* FINAL STATUS */

$status = $order['work_status'] ?? $order['status'];

if(!empty($order['delivery_status'])){

    if(strtolower($order['delivery_status']) == 'pending'){
        $status = 'Pending Delivery';
    }

    elseif(strtolower($order['delivery_status']) == 'out for delivery'){
        $status = 'Out for Delivery';
    }

    elseif(strtolower($order['delivery_status']) == 'delivered'){
        $status = 'Delivered';
    }

}
?>

<!DOCTYPE html>
<html>
<head>

<title>View Order</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>

body{
    background:#f4f6f9;
    font-family:Arial;
}

.main-content{
    margin-left:260px;
    padding:25px;
}

.card{
    border:none;
    border-radius:12px;
}

.badge{
    font-size:13px;
    padding:8px 12px;
}

.info-label{
    font-weight:bold;
    color:#555;
}

.timeline{
    border-left:3px solid #0d6efd;
    margin-left:10px;
    padding-left:20px;
}

.timeline-item{
    margin-bottom:20px;
}

.timeline-dot{
    width:14px;
    height:14px;
    background:#0d6efd;
    border-radius:50%;
    position:absolute;
    left:-8px;
    margin-top:5px;
}

.timeline-content{
    position:relative;
}

</style>

</head>

<body>

<?php include 'client_sidebar.php'; ?>

<div class="main-content">

<div class="d-flex justify-content-between align-items-center mb-4">

<h3>
<i class="bi bi-receipt"></i> Order Details
</h3>

<a href="client_orders.php" class="btn btn-secondary">
<i class="bi bi-arrow-left"></i> Back
</a>

</div>

<!-- ORDER DETAILS -->

<div class="card shadow-sm mb-4">

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<div class="info-label">Product</div>

<div>
<?= htmlspecialchars($order['order_details']) ?>
</div>

</div>

<div class="col-md-6 mb-3">

<div class="info-label">Quantity</div>

<div>
<?= $order['quantity'] ?>
</div>

</div>

<div class="col-md-6 mb-3">

<div class="info-label">Payment Method</div>

<div>
<?= htmlspecialchars($order['payment_method']) ?>
</div>

</div>

<div class="col-md-6 mb-3">

<div class="info-label">Payment Status</div>

<div>

<?php

if($order['payment_status'] == 'Pending Payment'){

    echo "<span class='badge bg-warning text-dark'>
            Pending Payment
          </span>";

}

elseif($order['payment_status'] == 'Paid'){

    echo "<span class='badge bg-success'>
            Paid
          </span>";

}

elseif($order['payment_status'] == 'Rejected Payment'){

    echo "<span class='badge bg-danger'>
            Rejected Payment
          </span>";

}

?>

</div>

</div>

<div class="col-md-6 mb-3">

<div class="info-label">Order Status</div>

<div>

<?php

if($status == 'Pending'){
    echo "<span class='badge bg-warning text-dark'>Pending</span>";
}

elseif($status == 'Confirmed'){
    echo "<span class='badge bg-primary'>Confirmed</span>";
}

elseif($status == 'In Cutting'){
    echo "<span class='badge bg-info'>In Cutting</span>";
}

elseif($status == 'In Etching'){
    echo "<span class='badge bg-info'>In Etching</span>";
}

elseif($status == 'In Lamination'){
    echo "<span class='badge bg-dark'>In Lamination</span>";
}

elseif($status == 'In QC'){
    echo "<span class='badge bg-warning text-dark'>In QC</span>";
}

elseif($status == 'Packed'){
    echo "<span class='badge bg-secondary'>Packed</span>";
}

elseif($status == 'Pending Delivery'){
    echo "<span class='badge bg-secondary'>Pending Delivery</span>";
}

elseif($status == 'Out for Delivery'){
    echo "<span class='badge bg-warning text-dark'>
            Out for Delivery
          </span>";
}

elseif($status == 'Delivered'){
    echo "<span class='badge bg-success'>Delivered</span>";
}

elseif($status == 'Completed'){
    echo "<span class='badge bg-success'>Completed</span>";
}

else{
    echo "<span class='badge bg-secondary'>$status</span>";
}

?>

</div>

</div>

<div class="col-md-6 mb-3">

<div class="info-label">Date Ordered</div>

<div>
<?= date('F d, Y h:i A', strtotime($order['created_at'])) ?>
</div>

</div>

</div>

</div>

</div>

<!-- PROOF OF PAYMENT -->

<div class="card shadow-sm mb-4">

<div class="card-header bg-white">
<h5 class="mb-0">
<i class="bi bi-image"></i> Proof of Payment
</h5>
</div>

<div class="card-body text-center">

<?php if(!empty($order['proof_of_payment'])): ?>

<img src="uploads/<?= $order['proof_of_payment'] ?>"
     class="img-fluid rounded shadow"
     style="max-width:350px;">

<?php else: ?>

<p class="text-muted">
No proof uploaded.
</p>

<?php endif; ?>

</div>

</div>

<!-- ORDER TIMELINE -->

<div class="card shadow-sm">

<div class="card-header bg-white">
<h5 class="mb-0">
<i class="bi bi-clock-history"></i> Order Timeline
</h5>
</div>

<div class="card-body">

<div class="timeline">

<div class="timeline-item">
<div class="timeline-content">
<div class="timeline-dot"></div>

<h6>Order Placed</h6>

<p class="text-muted mb-0">
<?= date('F d, Y h:i A', strtotime($order['created_at'])) ?>
</p>

</div>
</div>

<?php if($order['payment_status'] == 'Paid'): ?>

<div class="timeline-item">
<div class="timeline-content">
<div class="timeline-dot"></div>

<h6>Payment Approved</h6>

<p class="text-muted mb-0">
Payment has been verified successfully.
</p>

</div>
</div>

<?php endif; ?>

<?php if($status != 'Pending'): ?>

<div class="timeline-item">
<div class="timeline-content">
<div class="timeline-dot"></div>

<h6><?= $status ?></h6>

<p class="text-muted mb-0">
Your order is currently in this stage.
</p>

</div>
</div>

<?php endif; ?>

</div>

</div>

</div>

</div>

</body>
</html>