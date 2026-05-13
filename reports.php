<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once "config.php";
require_once 'access_control.php';
check_access(['admin','accounting','staff']);

/* FILTER */
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

/* DEFAULT (pag walang filter = today) */
if(empty($from) || empty($to)){
    $from = date('Y-m-d');
    $to   = date('Y-m-d');
}

/* ================= SUMMARY ================= */

// WORK ORDERS
$stmt=$conn->prepare("
SELECT COUNT(*) 
FROM work_orders
WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$from,$to]);
$total_orders=$stmt->fetchColumn();

// REVENUE
$stmt=$conn->prepare("
SELECT COALESCE(SUM(amount),0)
FROM accounting_transactions
WHERE type='Income'
AND DATE(txn_date) BETWEEN ? AND ?
");
$stmt->execute([$from,$to]);
$total_revenue=$stmt->fetchColumn();

// EXPENSE
$stmt=$conn->prepare("
SELECT COALESCE(SUM(amount),0)
FROM accounting_transactions
WHERE type='Expense'
AND DATE(txn_date) BETWEEN ? AND ?
");
$stmt->execute([$from,$to]);
$total_expense=$stmt->fetchColumn();

// DELIVERED
$stmt=$conn->prepare("
SELECT COUNT(*)
FROM deliveries
WHERE status='delivered'
AND DATE(delivered_date) BETWEEN ? AND ?
");
$stmt->execute([$from,$to]);
$total_delivered=$stmt->fetchColumn();

/* PRODUCTION */
$production=$conn->prepare("
SELECT status, COUNT(*) total
FROM work_orders
WHERE DATE(created_at) BETWEEN ? AND ?
GROUP BY status
");
$production->execute([$from,$to]);
$prod_data=$production->fetchAll(PDO::FETCH_ASSOC);

/* INVENTORY */
$inventory=$conn->prepare("
SELECT ii.item_name, SUM(sm.quantity) total_used
FROM stock_movements sm
JOIN inventory_items ii ON ii.id=sm.item_id
WHERE sm.movement_type != 'Stock In'
AND DATE(sm.created_at) BETWEEN ? AND ?
GROUP BY ii.item_name
ORDER BY total_used DESC
LIMIT 5
");
$inventory->execute([$from,$to]);
$inv_data=$inventory->fetchAll(PDO::FETCH_ASSOC);

/* LOGISTICS */
$stmt=$conn->prepare("
SELECT COUNT(*) FROM packing_jobs
WHERE DATE(date_packed) BETWEEN ? AND ?
");
$stmt->execute([$from,$to]);
$total_packed=$stmt->fetchColumn();

/* HR - ATTENDANCE */
$stmt=$conn->prepare("
SELECT status, COUNT(*) total
FROM attendance
WHERE DATE(att_date) BETWEEN ? AND ?
GROUP BY status
");
$stmt->execute([$from,$to]);
$att_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* HR - PAYROLL */
$stmt=$conn->prepare("
SELECT COALESCE(SUM(net_pay),0)
FROM payroll
WHERE DATE(period_end) BETWEEN ? AND ?
");
$stmt->execute([$from,$to]);
$total_payroll = $stmt->fetchColumn() ?? 0;

?>

<!DOCTYPE html>
<html>
<head>
<title>Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{background:#f4f6f9;font-family:Arial;}
.main-content{margin-left:260px;padding:20px;margin-top:70px;}
.card{border-radius:10px;height:100%;}
.card-body{padding:15px;}
.summary-card{text-align:center;padding:10px;}
.text-yellow{ color:#f1c40f; }
.text-green{ color:#27ae60; }
.text-red{ color:#e74c3c; }
.text-blue{ color:#3498db; }
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>

<div class="main-content">

<!-- TITLE -->
<div class="card mb-2">
<div class="card-body">
<h4 class="fw-bold mb-0" style="letter-spacing:0.5px;">
Analytics Report
</h4>

<div class="text-muted" style="font-size:12px;">
Range: <?= $from ?> to <?= $to ?>
</div>
</div>
</div>

<!-- FILTER -->
<form method="GET" class="mb-3">
<div class="d-flex gap-2 flex-wrap align-items-end">

<div>
<label>From</label>
<input type="date" name="from" class="form-control" value="<?= $from ?>">
</div>

<div>
<label>To</label>
<input type="date" name="to" class="form-control" value="<?= $to ?>">
</div>

<div>
<button class="btn btn-primary px-4">Filter</button>
</div>

</div>
</form>

<!-- SUMMARY -->
<div class="row g-2 mb-2">

<div class="col-md-3">
<div class="card summary-card">
<h6>Work Orders</h6>
<h3 class="text-yellow"><?=$total_orders?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card summary-card">
<h6>Revenue</h6>
<h3 class="text-green">₱<?=number_format($total_revenue,2)?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card summary-card">
<h6>Expenses</h6>
<h3 class="text-red">₱<?=number_format($total_expense,2)?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card summary-card">
<h6>Delivered</h6>
<h3 class="text-blue"><?=$total_delivered?></h3>
</div>
</div>

</div>

<!-- GRID -->
<div class="row g-2">

<div class="col-md-6">
<div class="card"><div class="card-body">
<h6>Production</h6><hr>
<?php foreach($prod_data as $p){ ?>
<p><?=$p['status']?>: <b><?=$p['total']?></b></p>
<?php } ?>
</div></div>
</div>

<div class="col-md-6">
<div class="card"><div class="card-body">
<h6>Inventory</h6><hr>
<?php foreach($inv_data as $i){ ?>
<p><?=$i['item_name']?> - <b><?=$i['total_used']?></b></p>
<?php } ?>
</div></div>
</div>

<div class="col-md-6">
<div class="card"><div class="card-body">
<h6>Logistics</h6><hr>
<p>Packed: <b><?=$total_packed?></b></p>
<p>Delivered: <b><?=$total_delivered?></b></p>
</div></div>
</div>

<div class="col-md-6">
<div class="card"><div class="card-body">
<h6>HR</h6>
<?php foreach($att_data as $a){ ?>
<p><?=$a['status']?>: <b><?=$a['total']?></b></p>
<?php } ?>
<hr>
<p>Total Payroll: <b>₱<?=number_format($total_payroll,2)?></b></p>
</div></div>
</div>

</div>

</div>

</body>
</html>