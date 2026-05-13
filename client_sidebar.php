<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$current_page = basename($_SERVER['PHP_SELF']);

// CART COUNT
$cartCount = 0;
if(isset($_SESSION['cart'])){
    foreach($_SESSION['cart'] as $item){
        $cartCount += $item['qty'];
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root{
--sb-width:260px;
--primary-blue:#0d6efd;
--sidebar-bg:#243b55;
}

.sidebar{
background: linear-gradient(180deg, #2c4a6b 0%, #1f2f45 100%);
position:fixed;
top:0;
left:0;
bottom:0;
width:var(--sb-width);
z-index:9999;
display:flex;
flex-direction:column;
padding:15px 0;
}

/* LOGO */
.sidebar-logo{
width:100px;
height:100px;
object-fit:contain;
background:white;
padding:12px;
border-radius:18px;
display:block;
margin:10px auto;
box-shadow:0 3px 8px rgba(0,0,0,0.25);
}

.brand-text{
color:white;
font-weight:700;
font-size:16px;
text-align:center;
}

/* MENU */
.sidebar-menu{
flex:1;
padding:15px;
}

.nav-link{
display:flex;
align-items:center;
justify-content:space-between;
color:#adb5bd;
padding:10px 15px;
text-decoration:none;
border-radius:8px;
margin-bottom:5px;
font-size:.9rem;
transition:0.2s;
}

.nav-left{
display:flex;
align-items:center;
}

.nav-left i{
width:22px;
margin-right:10px;
}

.nav-link:hover{
background:rgba(255,255,255,0.08);
color:white;
}

.nav-link.active{
background:linear-gradient(90deg,#0d6efd,#3d8bfd);
color:white;
box-shadow:0 4px 10px rgba(13,110,253,0.3);
}

/* BADGE */
.cart-badge{
background:red;
color:#fff;
font-size:11px;
padding:3px 7px;
border-radius:50%;
}

/* FOOTER */
.user-footer{
padding:15px;
border-top:1px solid #2d3238;
}

.user-name{
color:white;
font-size:.85rem;
font-weight:600;
}

.logout-btn{
color:#ff6b6b;
font-size:.85rem;
}
</style>

<div class="sidebar">

    <!-- LOGO -->
    <div class="text-center">
        <img src="logo.png" class="sidebar-logo">
        <div class="brand-text">Client Portal</div>
    </div>

    <!-- MENU -->
    <div class="sidebar-menu">

        <!-- DASHBOARD -->
        <a href="client_dashboard.php" class="nav-link <?= ($current_page=='client_dashboard.php')?'active':'' ?>">
            <div class="nav-left">
                <i class="bi bi-speedometer2"></i> Dashboard
            </div>
        </a>

        <!-- PRODUCTS -->
        <a href="client_products.php" class="nav-link <?= ($current_page=='client_products.php')?'active':'' ?>">
            <div class="nav-left">
                <i class="bi bi-box"></i> Products
            </div>
        </a>

        <!-- CART -->
        <a href="client_cart.php" class="nav-link <?= ($current_page=='client_cart.php')?'active':'' ?>">
            <div class="nav-left">
                <i class="bi bi-cart"></i> Cart
            </div>

            <?php if($cartCount > 0): ?>
                <span class="cart-badge"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>

        <!-- MY ORDERS -->
        <a href="client_orders.php" class="nav-link <?= ($current_page=='client_orders.php')?'active':'' ?>">
            <div class="nav-left">
                <i class="bi bi-bag-check"></i> My Orders
            </div>
        </a>

        <!-- PROFILE -->
        <a href="client_profile.php" class="nav-link <?= ($current_page=='client_profile.php')?'active':'' ?>">
            <div class="nav-left">
                <i class="bi bi-person"></i> Profile
            </div>
        </a>

    </div>

    <!-- FOOTER -->
    <div class="user-footer">
        <small class="text-muted d-block">Client:</small>

        <span class="user-name">
            <?= htmlspecialchars($_SESSION['client_name'] ?? 'Client') ?>
        </span>

        <a href="logout.php" class="nav-link logout-btn mt-2">
            <div class="nav-left">
                <i class="bi bi-box-arrow-right"></i> Logout
            </div>
        </a>
    </div>

</div>