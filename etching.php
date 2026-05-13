<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config.php";
require_once 'access_control.php';

check_access(['admin','production','engineer']);

$flash = "";

/* SAVE ETCHING JOB */

if(isset($_POST['add_etching'])){

    $work_order_id = $_POST['work_order_id'];
    $item_id       = $_POST['item_id'];
    $design        = $_POST['design'];
    $operator      = $_POST['operator'];
    $date_etched   = $_POST['date_etched'];
    $quantity      = $_POST['quantity'];

    /* GET ITEM DETAILS */

    $stmt = $conn->prepare("
    SELECT *
    FROM inventory_items
    WHERE id=?
    ");

    $stmt->execute([$item_id]);

    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    /* CHECK ITEM */

    if(!$item){

        $_SESSION['error'] = "Material not found!";
        header("Location: etching.php");
        exit;

    }

    /* CHECK STOCK */

    if($item['quantity'] < $quantity){

        $_SESSION['error'] = "Not enough stock!";
        header("Location: etching.php");
        exit;

    }

    /* CHECK IF CUTTING IS COMPLETED */

$checkCutting = $conn->prepare("
SELECT *
FROM cutting_jobs
WHERE work_order_id=?
AND status='Completed'
");

$checkCutting->execute([$work_order_id]);

$cuttingDone = $checkCutting->fetch(PDO::FETCH_ASSOC);

if(!$cuttingDone){

    $_SESSION['error'] = "Cannot proceed to etching. Cutting must be completed first.";

    header("Location: etching.php");
    exit;

}

    /* INSERT ETCHING JOB */

    $stmt = $conn->prepare("
   INSERT INTO etching_jobs
(work_order_id,item_id,design,operator,date_etched,quantity,status)
VALUES (?,?,?,?,?,?,'Pending')
    ");

    $stmt->execute([
        $work_order_id,
        $item_id,
        $design,
        $operator,
        $date_etched,
        $quantity
    ]);

    /* UPDATE STATUS */

    $update = $conn->prepare("
    UPDATE work_orders
    SET status='In Etching'
    WHERE id=?
    ");

    $update->execute([$work_order_id]);

    /* DEDUCT INVENTORY */

    $stmt = $conn->prepare("
    UPDATE inventory_items
    SET quantity = quantity - ?
    WHERE id=?
    ");

    $stmt->execute([
        $quantity,
        $item_id
    ]);

    /* STOCK MOVEMENT */

    $stmt = $conn->prepare("
    INSERT INTO stock_movements
    (item_id,movement_type,quantity,reference,movement_date)
    VALUES (?,?,?,?,NOW())
    ");

    $stmt->execute([
        $item_id,
        'etching',
        $quantity,
        $work_order_id
    ]);

    /* COMPUTE COST */

    $total_cost = $quantity * ($item['cost'] ?? 0);

    /* GET WORK ORDER */

    $stmt = $conn->prepare("
    SELECT wo_no
    FROM work_orders
    WHERE id=?
    ");

    $stmt->execute([$work_order_id]);

    $wo = $stmt->fetch(PDO::FETCH_ASSOC);

    /* DESCRIPTION */

    $desc =
    "Etching - ".
    $wo['wo_no'].
    " - ".
    $item['item_name'];

    /* INSERT ACCOUNTING */

    $stmt = $conn->prepare("
    INSERT INTO accounting_transactions
    (txn_date,type,category,reference_no,wo_id,description,payment_method,amount)
    VALUES (NOW(),'Expense','Production',?,?,?,?,?)
    ");

    $stmt->execute([
        $wo['wo_no'],
        $work_order_id,
        $desc,
        'Manufacturing',
        $total_cost
    ]);

    header("Location: etching.php?success=1");
    exit;
}

/* FLASH */

if(isset($_GET['success'])){
    $flash="Etching job saved successfully.";
}

/* FETCH ETCHING JOBS */

$stmt = $conn->query("
SELECT e.*,i.item_name,w.wo_no,w.product_name
FROM etching_jobs e
LEFT JOIN inventory_items i ON e.item_id=i.id
LEFT JOIN work_orders w ON e.work_order_id=w.id
ORDER BY e.id DESC
");

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* WORK ORDERS */

$stmt = $conn->query("
SELECT id,wo_no,product_name
FROM work_orders
ORDER BY id DESC
");

$workorders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>

<head>

<title>Etching Jobs</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:Arial;
}

.main-content{
margin-left:260px;
padding:25px;
margin-top:70px;
}

.card{
border-radius:10px;
}

</style>

</head>

<body>

<div class="d-flex">

<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>

<div class="main-content flex-grow-1">

<div class="card shadow-sm mb-3">
<div class="card-body">
<h3 class="mb-0">Etching Jobs</h3>
</div>
</div>

<?php if(isset($_SESSION['error'])){ ?>

<div class="alert alert-danger">
<?= $_SESSION['error']; ?>
</div>

<?php unset($_SESSION['error']); } ?>

<?php if($flash){ ?>

<div class="alert alert-success">
<?=$flash?>
</div>

<?php } ?>

<div class="card shadow-sm">

<div class="card-header">

<button class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#addEtchingModal">

+ Add Etching Job

</button>

</div>

<div class="card-body p-0">

<table class="table table-bordered mb-0">

<thead class="table-light">

<tr>
<th>Work Order</th>
<th>Material</th>
<th>Quantity</th>
<th>Operator</th>
<th>Date Etched</th>
<th>Status</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<?php if($jobs){ ?>

<?php foreach($jobs as $row){ ?>

<tr>

<td><?=$row['wo_no']?> - <?=$row['product_name']?></td>

<td><?=$row['design']?></td>

<td><?=$row['quantity']?></td>

<td><?=$row['operator']?></td>

<td><?=date('Y-m-d', strtotime($row['date_etched']))?></td>

<td>

<?php if($row['status'] == 'Pending'): ?>

<span class="badge bg-secondary">
Pending
</span>

<?php elseif($row['status'] == 'On Process'): ?>

<span class="badge bg-warning text-dark">
On Process
</span>

<?php elseif($row['status'] == 'Completed'): ?>

<span class="badge bg-success">
Completed
</span>

<?php endif; ?>

</td>

<td>

<?php if($row['status'] == 'Pending'): ?>

<a href="update_etching_status.php?id=<?=$row['id']?>&status=On Process"
class="btn btn-warning btn-sm">

Start

</a>

<?php elseif($row['status'] == 'On Process'): ?>

<a href="update_etching_status.php?id=<?=$row['id']?>&status=Completed"
class="btn btn-success btn-sm">

Complete

</a>

<?php endif; ?>

</td>

</tr>

<?php } ?>

<?php }else{ ?>

<tr>
<td colspan="5" class="text-center p-3">
No etching jobs found
</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<!-- MODAL -->

<div class="modal fade" id="addEtchingModal">

<div class="modal-dialog">

<form method="POST" class="modal-content">

<div class="modal-header">

<h5>Add Etching Job</h5>

<button type="button"
class="btn-close"
data-bs-dismiss="modal">
</button>

</div>

<div class="modal-body">

<div class="mb-3">

<label>Work Order</label>

<select
name="work_order_id"
id="work_order"
class="form-control"
required>

<option value="">Select Work Order</option>

<?php foreach($workorders as $wo){ ?>

<option value="<?=$wo['id']?>">

<?=$wo['wo_no']?> - <?=$wo['product_name']?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label>Material</label>

<input type="text"
id="design"
name="design"
class="form-control"
readonly>

</div>

<!-- HIDDEN ITEM ID -->

<input type="hidden"
name="item_id"
id="item_id">

<div class="mb-3">

<label>Operator</label>

<input type="text"
name="operator"
class="form-control">

</div>

<div class="mb-3">

<label>Date Etched</label>

<input type="date"
name="date_etched"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Quantity</label>

<input type="number"
id="qty"
name="quantity"
class="form-control"
required
min="1">

</div>

</div>

<div class="modal-footer">

<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancel

</button>

<button type="submit"
name="add_etching"
class="btn btn-primary">

Save

</button>

</div>

</form>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'footer.php'; ?>

<script>

document.addEventListener("DOMContentLoaded", function(){

const workOrder = document.getElementById("work_order");
const design    = document.getElementById("design");
const itemId    = document.getElementById("item_id");
const qty       = document.getElementById("qty");

/* LOAD MATERIAL */

function loadMaterial(){

let wo_id = workOrder.value;

if(!wo_id){

design.value = '';
itemId.value = '';
qty.value = '';

return;

}

fetch('get_etching_material.php?work_order_id=' + wo_id)

.then(res => res.json())

.then(data => {

console.log(data);

if(data.error){

design.value = 'No Material Found';
itemId.value = '';
qty.value = '';

return;

}

/* MATERIAL */

design.value = data.item_name;

/* ITEM ID */

itemId.value = data.id;

/* AUTO QTY */

qty.value = data.order_qty;

qty.max = data.quantity;

});

}

workOrder.addEventListener(
'change',
loadMaterial
);

});

</script>

</body>
</html>