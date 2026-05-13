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

/* TOTAL ORDERS */
$stmt = $conn->prepare("
SELECT COUNT(*) 
FROM orders 
WHERE client_id=?
");
$stmt->execute([$client_id]);
$totalOrders = $stmt->fetchColumn();


/* PENDING */
$stmt = $conn->prepare("
SELECT COUNT(*)
FROM orders o
LEFT JOIN work_orders w 
ON w.product_name = o.order_details
WHERE o.client_id=?
AND COALESCE(w.status,o.status)='Pending'
");
$stmt->execute([$client_id]);
$pending = $stmt->fetchColumn();


/* PROCESSING */
$stmt = $conn->prepare("
SELECT COUNT(*)
FROM orders o
LEFT JOIN work_orders w 
ON w.product_name = o.order_details
WHERE o.client_id=?
AND COALESCE(w.status,o.status) IN
('Processing','In Cutting','In Etching','In Lamination','In QC','Packed')
");
$stmt->execute([$client_id]);
$processing = $stmt->fetchColumn();


/* COMPLETED */
$stmt = $conn->prepare("
SELECT COUNT(*)
FROM orders o
LEFT JOIN work_orders w 
ON w.product_name = o.order_details
WHERE o.client_id=?
AND COALESCE(w.status,o.status) IN
('Completed','Delivered')
");
$stmt->execute([$client_id]);
$completed = $stmt->fetchColumn();


/* LATEST ORDER */
$stmt = $conn->prepare("
SELECT 
o.order_details,
o.quantity,

COALESCE(w.status,o.status) as status

FROM orders o

LEFT JOIN work_orders w
ON w.product_name = o.order_details

WHERE o.client_id=?

ORDER BY o.id DESC
LIMIT 1
");

$stmt->execute([$client_id]);
$latest = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Client Dashboard</title>

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

.dashboard-title{
    background:#fff;
    padding:10px 15px;
    border-radius:8px 8px 0 0;
    margin-bottom:15px;
    font-weight:600;
}

.dashboard-container{
    background:#fff;
    border-radius:10px;
    padding:20px;
    box-shadow:0 3px 10px rgba(0,0,0,0.05);
}

.summary-card{
    background:#fff;
    border:1px solid #ddd;
    border-radius:8px;
    padding:15px;
    text-align:center;
}

.summary-number{
    font-size:22px;
    font-weight:bold;
}

.summary-label{
    font-size:13px;
    color:#666;
}
</style>
</head>

<body>

<?php include 'client_sidebar.php'; ?>

<div class="main-content">

<div class="dashboard-title">
<h4><i class="bi bi-speedometer2"></i> Dashboard</h4>
</div>

<div class="dashboard-container">

<!-- SUMMARY -->
<div class="row g-3 mb-4">

<div class="col-md-3">
<div class="summary-card">
<div class="summary-number text-primary"><?= $totalOrders ?></div>
<div class="summary-label">Total Orders</div>
</div>
</div>

<div class="col-md-3">
<div class="summary-card">
<div class="summary-number text-warning"><?= $pending ?></div>
<div class="summary-label">Pending</div>
</div>
</div>

<div class="col-md-3">
<div class="summary-card">
<div class="summary-number text-info"><?= $processing ?></div>
<div class="summary-label">Processing</div>
</div>
</div>

<div class="col-md-3">
<div class="summary-card">
<div class="summary-number text-success"><?= $completed ?></div>
<div class="summary-label">Completed</div>
</div>
</div>

</div>



<!-- LATEST ORDER -->
<div class="card">
<div class="card-header">Latest Order</div>

<div class="card-body">

<?php if($latest): ?>

<b><?= htmlspecialchars($latest['order_details']) ?></b><br>
Qty: <?= $latest['quantity'] ?><br>
<?php
$status = $latest['status'];

$badge = "secondary";

if($status == 'Pending'){
    $badge = "warning";
}
elseif($status == 'Processing'){
    $badge = "info";
}
elseif($status == 'In Cutting'){
    $badge = "primary";
}
elseif($status == 'In Etching'){
    $badge = "info";
}
elseif($status == 'In Lamination'){
    $badge = "dark";
}
elseif($status == 'In QC'){
    $badge = "warning";
}
elseif($status == 'Packed'){
    $badge = "secondary";
}
elseif($status == 'Delivered'){
    $badge = "success";
}
elseif($status == 'Completed'){
    $badge = "success";
}
?>

Status:
<span class="badge bg-<?= $badge ?>">
<?= $status ?>
</span>

<?php else: ?>

No orders yet

<?php endif; ?>

</div>
</div>

</div>

</div>

</body>
</html>