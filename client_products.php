<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['client_id'])){
    header("Location: client_login.php");
    exit();
}

// GET PRODUCTS
$stmt = $conn->query("
SELECT item_name, selling_price, image 
FROM inventory_items 
WHERE category='Finished Good' AND status='active'
");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// CART COUNT
$cartCount = 0;
if(isset($_SESSION['cart'])){
    foreach($_SESSION['cart'] as $item){
        $cartCount += $item['qty'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Products</title>

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


/* PRODUCT CARD */
.product-card{
    background:#fff;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
    transition:0.3s;
    height:100%;
    display:flex;
    flex-direction:column;
}

.product-card:hover{
    transform:translateY(-5px);
}

/* IMAGE */
.product-img{
    width:100%;
    height:180px;
    object-fit:cover;
}

/* BODY */
.product-body{
    padding:15px;
    text-align:center;
    flex:1;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}

.price{
    font-size:18px;
    font-weight:bold;
    color:#0d6efd;
}

/* CART BADGE */
.cart-btn{
    position:relative;
}

.cart-badge{
    position:absolute;
    top:-5px;
    right:-10px;
    background:red;
    color:#fff;
    font-size:11px;
    padding:3px 6px;
    border-radius:50%;
}
</style>
</head>

<body>

<?php include 'client_sidebar.php'; ?>

<div class="main-content">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">

<h4 class="mb-0">
<i class="bi bi-box"></i> Products
</h4>

<a href="client_cart.php" class="btn btn-dark cart-btn">
<i class="bi bi-cart"></i> Cart

<?php if($cartCount > 0): ?>
<span class="cart-badge"><?= $cartCount ?></span>
<?php endif; ?>

</a>

</div>

<!-- PRODUCTS -->
<div class="row g-4">

<?php if($products): ?>

<?php foreach($products as $p): ?>

<div class="col-md-3">
<div class="product-card">

<!-- IMAGE -->
<?php if(!empty($p['image'])): ?>
<img src="uploads/<?= $p['image'] ?>" class="product-img">
<?php else: ?>
<img src="https://via.placeholder.com/300x180?text=No+Image" class="product-img">
<?php endif; ?>

<div class="product-body">

<div>
<h6><?= htmlspecialchars($p['item_name']) ?></h6>

<div class="price">
₱<?= number_format($p['selling_price'],2) ?>
</div>
</div>

<!-- ADD TO CART -->
<form method="POST" action="add_to_cart.php" class="mt-2">
    <input type="hidden" name="product" value="<?= $p['item_name'] ?>">
    <input type="hidden" name="price" value="<?= $p['selling_price'] ?>">

    <button type="submit" class="btn btn-success w-100">
        <i class="bi bi-cart-plus"></i> Add to Cart
    </button>
</form>

</div>
</div>
</div>

<?php endforeach; ?>

<?php else: ?>

<p class="text-center">No products available</p>

<?php endif; ?>

</div>

</div>

</body>
</html>