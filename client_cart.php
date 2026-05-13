<?php
session_start();

if(!isset($_SESSION['client_id'])){
    header("Location: client_login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
?>

<!DOCTYPE html>
<html>
<head>
<title>My Cart</title>

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
    border-radius:10px;
}

.qty-input{
    width:70px;
}
</style>
</head>

<body>

<?php include 'client_sidebar.php'; ?>

<div class="main-content">

<h4 class="mb-4">
<i class="bi bi-cart"></i> My Cart
</h4>

<div class="card">
<div class="card-body">

<?php if($cart): ?>

<table class="table table-bordered align-middle">

<thead class="table-light">
<tr>
<th>Product</th>
<th>Price</th>
<th>Qty</th>
<th>Total</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php
$grandTotal = 0;
foreach($cart as $index => $item):

$total = $item['price'] * $item['qty'];
$grandTotal += $total;
?>

<tr>

<td><?= htmlspecialchars($item['product']) ?></td>

<td>₱<?= number_format($item['price'],2) ?></td>

<!-- 🔥 QTY UPDATE -->
<td>
<form method="POST" action="update_cart.php" class="d-flex align-items-center gap-2">

<input type="hidden" name="index" value="<?= $index ?>">

<input type="number"
       name="qty"
       value="<?= $item['qty'] ?>"
       min="0"
       class="form-control form-control-sm qty-input">

<button type="submit" class="btn btn-success btn-sm">
<i class="bi bi-check"></i>
</button>

</form>
</td>

<td>₱<?= number_format($total,2) ?></td>

<td>
<a href="remove_item.php?index=<?= $index ?>" 
class="btn btn-danger btn-sm">
<i class="bi bi-trash"></i>
</a>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<!-- TOTAL -->
<h5 class="text-end">
Total: <strong>₱<?= number_format($grandTotal,2) ?></strong>
</h5>

<!-- CHECKOUT -->
<a href="checkout.php" class="btn btn-primary w-100 mt-3">
<i class="bi bi-credit-card"></i> Checkout
</a>

<?php else: ?>

<div class="text-center py-4">
<i class="bi bi-cart-x" style="font-size:40px;"></i>
<p class="mt-2">Your cart is empty</p>
<a href="client_products.php" class="btn btn-outline-primary">
Browse Products
</a>
</div>

<?php endif; ?>

</div>
</div>

</div>

</body>
</html>