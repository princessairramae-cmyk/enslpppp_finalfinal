<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();
require_once 'config.php';

if(!isset($_SESSION['client_id'])){
    header("Location: client_login.php");
    exit();
}

$client_id = $_SESSION['client_id'];

// GET ORDERS NG CLIENT
$stmt = $conn->prepare("
SELECT 
o.id,
o.order_details,
o.quantity,
o.created_at,
o.payment_method,
o.payment_status,

COALESCE(w.status, o.status) as status,

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

WHERE o.client_id=?

ORDER BY o.id DESC
");
$stmt->execute([$client_id]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Orders</title>

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


.table{
    background:#fff;
    border-radius:10px;
    overflow:hidden;
}

.badge{
    font-size:12px;
    padding:6px 10px;
}
</style>
</head>

<body>

<?php include 'client_sidebar.php'; ?>

<div class="main-content">

<div class="d-flex justify-content-between align-items-center mb-4">

<h4 class="mb-0">
<i class="bi bi-cart"></i> My Orders
</h4>

<a href="client_products.php" class="btn btn-primary">
<i class="bi bi-plus-circle"></i> Place Order
</a>

</div>

<div class="card shadow-sm border-0">
<div class="card-body">

<table class="table table-bordered align-middle">

<thead class="table-light">
<tr>
<th>#</th>
<th>Product</th>
<th>Qty</th>
<th>Status</th>
<th>Payment</th>
<th>Payment Status</th>
<th>Date Ordered</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if($orders): ?>

<?php foreach($orders as $o): ?>

<tr>

<td><?= $o['id'] ?></td>

<td><?= htmlspecialchars($o['order_details']) ?></td>

<td><?= $o['quantity'] ?></td>

<td>
<?php
$status = $o['status'];

/* PRIORITY DELIVERY STATUS */

if(!empty($o['delivery_status'])){

    if(strtolower($o['delivery_status']) == 'pending'){
        $status = 'Pending Delivery';
    }

    elseif(strtolower($o['delivery_status']) == 'out for delivery'){
        $status = 'Out for Delivery';
    }

    elseif(strtolower($o['delivery_status']) == 'delivered'){
        $status = 'Delivered';
    }

}

if($status == 'Pending'){
    echo "<span class='badge bg-warning text-dark'>Pending</span>";
}

elseif($status == 'Processing'){
    echo "<span class='badge bg-info'>Processing</span>";
}

elseif($status == 'In Cutting'){
    echo "<span class='badge bg-primary'>In Cutting</span>";
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
    echo "<span class='badge bg-warning text-dark'>Out for Delivery</span>";
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
</td>

<td>
<?= htmlspecialchars($o['payment_method']) ?>
</td>

<td>

<?php

if($o['payment_status'] == 'Pending Payment'){

    echo "<span class='badge bg-warning text-dark'>
            Pending Payment
          </span>";

}

elseif($o['payment_status'] == 'Paid'){

    echo "<span class='badge bg-success'>
            Paid
          </span>";

}

elseif($o['payment_status'] == 'Rejected Payment'){

    echo "<span class='badge bg-danger'>
            Rejected Payment
          </span>";

}

else{

    echo "<span class='badge bg-secondary'>
            N/A
          </span>";

}

?>

</td>

<td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>

<td>

<a href="view_clientorder.php?id=<?= $o['id'] ?>"
   class="btn btn-primary btn-sm">

   <i class="bi bi-eye"></i> View

</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="8" class="text-center py-4">
    No orders found
</td>
</tr>

<?php endif; ?>
</tbody>

</table>

</div>
</div>

</div>

</body>
</html>