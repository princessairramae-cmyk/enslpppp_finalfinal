<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config.php';
require_once 'access_control.php';
check_access(['admin','engineer','accounting']);

$flash="";

/* SEARCH + CATEGORY */
$q = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';

/* ADD ITEM */
if(isset($_POST['add_item'])){

    $item_name = $_POST['item_name'] ?? '';
    $category_post = $_POST['category'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 0);
    $cost = (float)($_POST['cost'] ?? 0);
    $selling_price = (float)($_POST['selling_price'] ?? 0);
    $reorder_level = (int)($_POST['reorder_level'] ?? 10);

    $imageName = '';

if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){

    $imageName = time() . '_' . basename($_FILES['image']['name']);

    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        'uploads/' . $imageName
    );
}

    try{
        $stmt=$conn->prepare("
       INSERT INTO inventory_items
(item_name,category,unit,quantity,cost,selling_price,reorder_level,image,status,created_at)
VALUES (?,?,?,?,?,?,?,?,'active',NOW())
        ");

        $stmt->execute([
            $item_name,
            $category_post,
            $unit,
            $quantity,
            $cost,
            $selling_price,
            $reorder_level,
            $imageName
        ]);

        header("Location: inventory.php?added=1");
        exit;

    }catch(Exception $e){
        die("ADD ERROR: ".$e->getMessage());
    }
}

/* RESTOCK */
if(isset($_POST['restock_item'])){
    $id = $_POST['id'];
    $add_qty = (int)$_POST['add_quantity'];

    $stmt = $conn->prepare("
        UPDATE inventory_items 
        SET quantity = quantity + ? 
        WHERE id = ?
    ");
    $stmt->execute([$add_qty, $id]);

    header("Location: inventory.php?restocked=1");
    exit;
}

/* DELETE */
if(isset($_POST['delete_item'])){

    $id = $_POST['id'];

    // GET IMAGE FIRST
    $get = $conn->prepare("SELECT image FROM inventory_items WHERE id=?");
    $get->execute([$id]);
    $item = $get->fetch(PDO::FETCH_ASSOC);

    // DELETE IMAGE FILE
    if(!empty($item['image'])){

        $imagePath = 'uploads/' . $item['image'];

        if(file_exists($imagePath)){
            unlink($imagePath);
        }
    }

    // DELETE ITEM FROM DATABASE
    $stmt = $conn->prepare("DELETE FROM inventory_items WHERE id=?");
    $stmt->execute([$id]);

    header("Location: inventory.php?deleted=1");
    exit;
}

/* UPDATE ITEM */
if(isset($_POST['update_item'])){

    $id = $_POST['id'];
    $name = $_POST['item_name'];
    $category = $_POST['category'];
    $unit = $_POST['unit'];
    $qty = $_POST['quantity'];
    $cost = $_POST['cost'];
    $sell = $_POST['selling_price'];
    $reorder = $_POST['reorder_level'];

    // GET OLD IMAGE
    $get = $conn->prepare("SELECT image FROM inventory_items WHERE id=?");
    $get->execute([$id]);
    $old = $get->fetch(PDO::FETCH_ASSOC);

    $imageName = $old['image'];

    // IF NEW IMAGE EXISTS
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){

        $imageName = time().'_'.basename($_FILES['image']['name']);

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            'uploads/'.$imageName
        );
    }

    $stmt = $conn->prepare("
        UPDATE inventory_items
        SET item_name=?,
            category=?,
            unit=?,
            quantity=?,
            cost=?,
            selling_price=?,
            reorder_level=?,
            image=?
        WHERE id=?
    ");

    $stmt->execute([
        $name,
        $category,
        $unit,
        $qty,
        $cost,
        $sell,
        $reorder,
        $imageName,
        $id
    ]);

    header("Location: inventory.php?updated=1");
    exit;
}


/* FLASH */
if(isset($_GET['added'])) $flash="Item added successfully.";
if(isset($_GET['updated'])) $flash="Item updated successfully.";
if(isset($_GET['deleted'])) $flash="Item removed successfully.";
if(isset($_GET['restocked'])) $flash="Item restocked successfully.";

/* FETCH ITEMS */
if($q!=""){
    $stmt=$conn->prepare("SELECT * FROM inventory_items WHERE item_name LIKE ? AND status='active'");
    $stmt->execute(["%$q%"]);
}elseif($category!=""){
    $stmt=$conn->prepare("SELECT * FROM inventory_items WHERE category=? AND status='active'");
    $stmt->execute([$category]);
}else{
    $stmt=$conn->query("SELECT * FROM inventory_items WHERE status='active' ORDER BY item_name ASC");
}

$items=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>

<title>Inventory</title>

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
border-radius:8px;
border:1px solid #e6e6e6;
}

.table thead{
background:#f8f9fa;
}

.btn-edit{
background:#0f766e;
color:white;
border:none;
}

.btn-delete{
background:#dc3545;
color:white;
border:none;
}

</style>

</head>

<body>

<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>

<div class="main-content">
<div class="container-fluid">

<div class="card shadow-sm mb-3">
<div class="card-body">
<h3 class="mb-0">Inventory Management</h3>
</div>
</div>

<?php if($flash){ ?>
<div class="alert alert-success"><?=$flash?></div>
<?php } ?>

<div class="card shadow-sm mb-3">
<div class="card-body">

<form method="GET" class="d-flex align-items-center flex-wrap gap-2">

<div class="d-flex align-items-center gap-2 flex-wrap">

    <!-- ADD BUTTON -->
    <?php if(strtolower($_SESSION['role']) != 'engineer'): ?>
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
    + Add Item
</button>
<?php endif; ?>

    <!-- SEARCH -->
    <input 
    type="text"
    name="q"
    value="<?=htmlspecialchars($q)?>"
    class="form-control"
    placeholder="Search item"
    style="width:220px"
    >

    <button class="btn btn-secondary">Search</button>
    <a href="inventory.php" class="btn btn-light">Clear</a>

</div>

</form>
<div class="mt-3">

<a href="inventory.php" class="btn btn-outline-secondary btn-sm">All</a>
<a href="inventory.php?category=Raw Material" class="btn btn-outline-primary btn-sm">Raw Material</a>
<a href="inventory.php?category=Component" class="btn btn-outline-primary btn-sm">Component</a>
<a href="inventory.php?category=Supply" class="btn btn-outline-primary btn-sm">Supply</a>
<a href="inventory.php?category=Finished Good" class="btn btn-outline-primary btn-sm">Finished Good</a>

</div>

</div>
</div>

<div class="card shadow-sm">
<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>
<tr>
<th>Item</th>
<th>Image</th>
<th>Category</th>
<th>Unit</th>
<th>Quantity</th>
<th>Cost</th>
<th>Selling</th>
<th>Value</th>
<th>Reorder</th>
<th>Status</th>
<th width="220">Action</th>
</tr>
</thead>

<tbody>

<?php foreach($items as $it){

$cost = (float)($it['cost'] ?? 0);

if($cost == 0){
    $cost = 1; // fallback (optional lang)
}
$selling = (float)($it['selling_price'] ?? 0);
$qty = (int)($it['quantity'] ?? 0);

$value = $cost * $qty;
$low = ($qty <= (int)$it['reorder_level']);
?>

<tr>

<td><?=$it['item_name']?></td>
<td>
<?php if(!empty($it['image'])): ?>
    <img src="uploads/<?=$it['image']?>" width="70" height="70" style="object-fit:cover; border-radius:8px;">
<?php else: ?>
    No Image
<?php endif; ?>
</td>
<td><?=$it['category']?></td>
<td><?=$it['unit']?></td>
<td><?=$it['quantity']?></td>

<td>₱<?=number_format((float)($it['cost'] ?? 0),2)?></td>
<td>₱<?=number_format((float)($it['selling_price'] ?? 0),2)?></td>
<td>₱<?=number_format((float)$value,2)?></td>

<td><?=$it['reorder_level']?></td>

<td>
<?php if($low){ ?>
<span class="badge bg-danger">Low</span>
<?php } else { ?>
<span class="badge bg-success">OK</span>
<?php } ?>
</td>

<td>

<?php if(strtolower($_SESSION['role']) != 'engineer'): ?>

<button class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#restockModal"
data-id="<?=$it['id']?>"
data-name="<?=$it['item_name']?>"
>
Restock
</button>

<button
class="btn btn-edit btn-sm"
data-bs-toggle="modal"
data-bs-target="#editModal"
data-id="<?=$it['id']?>"
data-name="<?=$it['item_name']?>"
data-category="<?=$it['category']?>"
data-unit="<?=$it['unit']?>"
data-qty="<?=$it['quantity']?>"
data-cost="<?=$it['cost']?>"
data-sell="<?=$it['selling_price']?>"
data-reorder="<?=$it['reorder_level']?>"
data-image="<?=$it['image']?>"
>
Edit
</button>

<form method="POST" style="display:inline;">
<input type="hidden" name="id" value="<?=$it['id']?>">
<button type="submit" name="delete_item" class="btn btn-delete btn-sm"
onclick="return confirm('Delete this item?')">
Delete
</button>
</form>

<?php else: ?>

<span class="badge bg-secondary">View Only</span>

<?php endif; ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>
</div>
</div>

</div>
</div>

<!-- RESTOCK MODAL -->
<div class="modal fade" id="restockModal">
<div class="modal-dialog">
<form method="POST" class="modal-content">

<div class="modal-header">
<h5>Restock Item</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" name="id" id="restock_id">

<div class="mb-3">
<label>Item</label>
<input type="text" id="restock_name" class="form-control" disabled>
</div>

<div class="mb-3">
<label>Add Quantity</label>
<input type="number" name="add_quantity" class="form-control" required>
</div>

</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button class="btn btn-warning" name="restock_item">Restock</button>
</div>

</form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.addEventListener('DOMContentLoaded', () => {

    const editModal = document.getElementById('editModal');

    if (!editModal) return;

    editModal.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;

        if (!button) return;

        // SAFE FETCH
        const id = button.getAttribute('data-id') || '';
        const name = button.getAttribute('data-name') || '';
        const category = button.getAttribute('data-category') || '';
        const unit = button.getAttribute('data-unit') || '';
        const qty = button.getAttribute('data-qty') || '';
        const cost = button.getAttribute('data-cost') || '';
        const sell = button.getAttribute('data-sell') || '';
const reorder = button.getAttribute('data-reorder') || '';
const image = button.getAttribute('data-image') || '';

        // SAFE ASSIGN
        const edit_id = document.getElementById('edit_id');
        const edit_name = document.getElementById('edit_name');
        const edit_category = document.getElementById('edit_category');
        const edit_unit = document.getElementById('edit_unit');
        const edit_qty = document.getElementById('edit_qty');
        const edit_cost = document.getElementById('edit_cost');
        const edit_sell = document.getElementById('edit_sell');
        const edit_reorder = document.getElementById('edit_reorder');

        if(edit_id) edit_id.value = id;
        if(edit_name) edit_name.value = name;
        if(edit_category) edit_category.value = category;
        if(edit_unit) edit_unit.value = unit;
        if(edit_qty) edit_qty.value = qty;
        if(edit_cost) edit_cost.value = cost;
        if(edit_sell) edit_sell.value = sell;
        if(edit_reorder) edit_reorder.value = reorder;
        const preview = document.getElementById('edit_preview');

if(preview){
    preview.src = 'uploads/' + image;
}

    });

});

</script>


<script>

const restockModal = document.getElementById('restockModal');

restockModal.addEventListener('show.bs.modal', function(event){

let btn = event.relatedTarget;

document.getElementById('restock_id').value = btn.dataset.id;
document.getElementById('restock_name').value = btn.dataset.name;

});

</script>

<!-- ADD ITEM MODAL -->
<div class="modal fade" id="addModal">
<div class="modal-dialog">
<form method="POST" enctype="multipart/form-data" class="modal-content">

<div class="modal-header">
<h5>Add Item</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="mb-2">
<label>Item Name</label>
<input type="text" name="item_name" class="form-control" required>
</div>

<div class="mb-2">
<label>Category</label>
<select name="category" class="form-control" required>
<option value="Raw Material">Raw Material</option>
<option value="Component">Component</option>
<option value="Supply">Supply</option>
<option value="Finished Good">Finished Good</option>
</select>
</div>

<div class="mb-2">
<label>Unit</label>
<input type="text" name="unit" class="form-control" required>
</div>

<div class="mb-2">
<label>Quantity</label>
<input type="number" name="quantity" class="form-control" required>
</div>

<div class="mb-2">
<label>Cost</label>
<input type="number" step="0.01" name="cost" class="form-control" required>
</div>

<div class="mb-2">
<label>Selling Price</label>
<input type="number" step="0.01" name="selling_price" class="form-control" required>
</div>

<div class="mb-2">
<label>Reorder Level</label>
<input type="number" name="reorder_level" class="form-control" value="10">
</div>

<div class="mb-2">
<label>Product Image</label>
<input type="file" name="image" class="form-control">
</div>
</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button class="btn btn-primary" name="add_item">Add Item</button>
</div>

</form>
</div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal">
<div class="modal-dialog">
<form method="POST" enctype="multipart/form-data" class="modal-content">

<div class="modal-header">
<h5>Edit Item</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" name="id" id="edit_id">

<div class="mb-2">
<label>Item Name</label>
<input type="text" name="item_name" id="edit_name" class="form-control" required>
</div>

<div class="mb-2">
<label>Category</label>
<input type="text" name="category" id="edit_category" class="form-control" required>
</div>

<div class="mb-2">
<label>Unit</label>
<input type="text" name="unit" id="edit_unit" class="form-control" required>
</div>

<div class="mb-2">
<label>Quantity</label>
<input type="number" name="quantity" id="edit_qty" class="form-control" required>
</div>

<div class="mb-2">
<label>Cost</label>
<input type="number" step="0.01" name="cost" id="edit_cost" class="form-control" required>
</div>

<div class="mb-2">
<label>Selling Price</label>
<input type="number" step="0.01" name="selling_price" id="edit_sell" class="form-control" required>
</div>

<div class="mb-2">
<label>Reorder Level</label>
<input type="number" name="reorder_level" id="edit_reorder" class="form-control">
</div>


<div class="mb-2">
<label>Current Image</label><br>

<img 
id="edit_preview"
src=""
width="100"
height="100"
style="object-fit:cover;border-radius:8px;border:1px solid #ddd;"
>

</div>

<div class="mb-2">
<label>Change Image</label>
<input type="file" name="image" class="form-control">
</div>
</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button class="btn btn-primary" name="update_item">Update</button>
</div>

</form>
</div>
</div>


</body>
</html>