<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();
require_once "config.php";
require_once 'access_control.php';

check_access(['admin','production','engineer']);

$flash="";

/* SAVE CUTTING JOB */

if(isset($_POST['save_cutting'])){

$work_order_id = $_POST['work_order_id'];
$item_id       = $_POST['item_id'];
$quantity_cut  = $_POST['quantity_cut'];
$operator      = $_POST['operator'];
$date_cut      = $_POST['date_cut'];

/* GET ITEM DETAILS */

$stmt=$conn->prepare("
SELECT *
FROM inventory_items
WHERE id=?
");

$stmt->execute([$item_id]);

$item=$stmt->fetch(PDO::FETCH_ASSOC);

/* CHECK STOCK */

if(!$item){

$_SESSION['error']="Material not found.";
header("Location: cutting.php");
exit;

}

if($item['quantity'] < $quantity_cut){

$_SESSION['error']="Not enough stock!";
header("Location: cutting.php");
exit;

}

/* INSERT CUTTING JOB */

$stmt=$conn->prepare("
INSERT INTO cutting_jobs
(work_order_id,item_id,quantity_cut,operator,date_cut,status)
VALUES (?,?,?,?,?,'Pending')
");

$stmt->execute([
$work_order_id,
$item_id,
$quantity_cut,
$operator,
$date_cut
]);

/* UPDATE WORK ORDER STATUS */

$update = $conn->prepare("
UPDATE work_orders
SET status='In Cutting'
WHERE id=?
");

$update->execute([$work_order_id]);

/* UPDATE CLIENT ORDER STATUS */

$updateOrder = $conn->prepare("
UPDATE orders
SET status='In Cutting'
WHERE id = (
    SELECT order_id
    FROM work_orders
    WHERE id=?
)
");

$updateOrder->execute([$work_order_id]);

/* DEDUCT INVENTORY */

$stmt=$conn->prepare("
UPDATE inventory_items
SET quantity = quantity - ?
WHERE id=?
");

$stmt->execute([
$quantity_cut,
$item_id
]);

/* STOCK MOVEMENT */

$stmt=$conn->prepare("
INSERT INTO stock_movements
(item_id,movement_type,quantity,reference,movement_date)
VALUES (?,?,?,?,NOW())
");

$stmt->execute([
$item_id,
'cutting',
$quantity_cut,
$work_order_id
]);

/* COMPUTE COST */

$total_cost = $quantity_cut * $item['cost'];

/* GET WORK ORDER INFO */

$stmt=$conn->prepare("
SELECT wo_no,product_name
FROM work_orders
WHERE id=?
");

$stmt->execute([$work_order_id]);

$wo=$stmt->fetch(PDO::FETCH_ASSOC);

/* DESCRIPTION */

$desc = "Cutting - ".$wo['wo_no']." - ".$item['item_name'];

/* INSERT ACCOUNTING */

$stmt=$conn->prepare("
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

header("Location: cutting.php?success=1");
exit();

}

/* FLASH */

if(isset($_GET['success'])){
$flash="Cutting job saved and expense recorded.";
}

/* FETCH CUTTING JOBS */

$jobs=$conn->query("
SELECT c.*,i.item_name,w.wo_no,w.product_name
FROM cutting_jobs c
LEFT JOIN inventory_items i ON i.id=c.item_id
LEFT JOIN work_orders w ON w.id=c.work_order_id
ORDER BY c.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* WORK ORDERS */

$workorders=$conn->query("
SELECT id,wo_no,product_name
FROM work_orders
ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>

<title>Cutting Jobs</title>

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
</style>

</head>

<body>

<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>

<div class="main-content">

<?php
if(isset($_SESSION['error'])){
echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
unset($_SESSION['error']);
}
?>

<?php if($flash){ ?>
<div class="alert alert-success"><?=$flash?></div>
<?php } ?>

<div class="card shadow-sm mb-3">
<div class="card-body">
<h3>Cutting Jobs</h3>
</div>
</div>

<div class="card shadow-sm">

<div class="card-header">
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCuttingModal">
+ Add Cutting Job
</button>
</div>

<div class="card-body">

<table class="table table-bordered">

<thead>
<tr>
<th>Work Order</th>
<th>Material</th>
<th>Quantity</th>
<th>Operator</th>
<th>Date</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php foreach($jobs as $row): ?>

<tr>

<td>
<?=$row['wo_no']?> - <?=$row['product_name']?>
</td>

<td>
<?=$row['item_name']?>
</td>

<td>
<?=$row['quantity_cut']?>
</td>

<td>
<?=$row['operator']?>
</td>

<td>
<?=$row['date_cut']?>
</td>

<td>

<?php if($row['status'] == 'Pending'): ?>
<span class="badge bg-secondary">Pending</span>

<?php elseif($row['status'] == 'On Process'): ?>
<span class="badge bg-warning text-dark">On Process</span>

<?php elseif($row['status'] == 'Completed'): ?>
<span class="badge bg-success">Completed</span>

<?php endif; ?>

</td>

<td>

<?php if($row['status'] == 'Pending'): ?>

<a href="update_cutting_status.php?id=<?=$row['id']?>&status=On Process"
class="btn btn-warning btn-sm">
Start
</a>

<?php elseif($row['status'] == 'On Process'): ?>

<a href="update_cutting_status.php?id=<?=$row['id']?>&status=Completed"
class="btn btn-success btn-sm">
Complete
</a>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

<!-- MODAL -->

<div class="modal fade" id="addCuttingModal">

<div class="modal-dialog">

<form method="POST" class="modal-content">

<div class="modal-header">
<h5>Add Cutting Job</h5>

<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="mb-3">

<label>Work Order</label>

<select name="work_order_id"
id="work_order"
class="form-control"
required>

<option value="">Select</option>

<?php foreach($workorders as $wo): ?>

<option value="<?=$wo['id']?>">

<?=$wo['wo_no']?> - <?=$wo['product_name']?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="mb-3">

<label>Material</label>

<select name="item_id"
id="material"
class="form-control"
required>

<option value="">Select Work Order First</option>

</select>

</div>

<div class="mb-3">

<label>Quantity Cut</label>

<input type="number"
id="qty_cut"
name="quantity_cut"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Operator</label>

<input type="text"
name="operator"
class="form-control">

</div>

<div class="mb-3">

<label>Date</label>

<input type="date"
name="date_cut"
class="form-control"
required>

</div>

</div>

<div class="modal-footer">

<button class="btn btn-primary"
name="save_cutting">

Save

</button>

</div>

</form>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.addEventListener("DOMContentLoaded", function(){

const workOrder = document.getElementById("work_order");
const material  = document.getElementById("material");
const qty       = document.getElementById("qty_cut");

/* LOAD MATERIALS */

function loadMaterials(){

let wo_id = workOrder.value;

if(!wo_id){

material.innerHTML =
'<option value="">Select</option>';

return;

}

fetch('get_materials.php?work_order_id=' + wo_id)

.then(response => response.json())

.then(data => {

console.log(data);

material.innerHTML='';

if(data.length === 0){

material.innerHTML =
'<option value="">No Material Found</option>';

return;

}

data.forEach((row,index)=>{

let option = document.createElement('option');

option.value = row.id;
option.textContent = row.item_name;

option.setAttribute(
'data-stock',
row.quantity
);

option.setAttribute(
'data-order',
row.order_qty
);

material.appendChild(option);

/* AUTO SELECT FIRST */

if(index === 0){

option.selected = true;

let stock = parseInt(row.quantity) || 0;

let orderQty = parseInt(row.order_qty) || 0;

let suggested =
orderQty > stock
? stock
: orderQty;

qty.value = suggested;
qty.max   = stock;

}

});

})

.catch(error => {

console.log(error);

});

}

/* CHANGE WORK ORDER */

workOrder.addEventListener(
'change',
loadMaterials
);

/* CHANGE MATERIAL */

material.addEventListener(
'change',
function(){

let selected =
material.options[
material.selectedIndex
];

if(!selected) return;

let stock =
parseInt(
selected.getAttribute('data-stock')
) || 0;

let orderQty =
parseInt(
selected.getAttribute('data-order')
) || 0;

let suggested =
orderQty > stock
? stock
: orderQty;

qty.value = suggested;
qty.max   = stock;

}
);

});

</script>

</body>
</html>