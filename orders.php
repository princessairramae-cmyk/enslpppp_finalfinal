<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'config.php';

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ================= APPROVE ================= */
if(isset($_GET['approve'])){
    $id = (int)$_GET['approve'];

    $stmt = $conn->prepare("
    SELECT o.*, c.email, c.name AS client_name
    FROM orders o
    LEFT JOIN clients c ON o.client_id = c.id
    WHERE o.id=?
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    
    $product = $order['order_details'];
    $client_name = $order['client_name'];
$qty = $order['quantity']; // ✅ AUTO QTY
$stmt = $conn->prepare("
SELECT selling_price 
FROM inventory_items 
WHERE item_name = ?
");
$stmt->execute([$product]);

$item = $stmt->fetch();
$selling_price = $item['selling_price'] ?? 0;

    $client_name = $order['client_name'];
    $year = date("Y");

$last = $conn->query("SELECT id FROM work_orders ORDER BY id DESC LIMIT 1")->fetch();
$next = (int)($last['id'] ?? 0) + 1;

$wo_no = "WO-$year-".str_pad($next,4,"0",STR_PAD_LEFT);
    $email = $order['email']; // make sure meron nito sa DB

    $date_started = date("Y-m-d H:i:s");

    // insert to work_orders
    $stmt = $conn->prepare("
   INSERT INTO work_orders 
(wo_no, order_id, product_name, client_name, qty, selling_price, current_stage, status, date_started)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $wo_no,
        $id,
        $product,
        $client_name,
        $qty,             // ✅ AUTO FROM ORDER
        $selling_price,   // ✅ AUTO FROM INVENTORY
        'Cutting',
        'Confirmed',
        $date_started
    ]);
    // update orders status
  /* PAYMENT METHOD */

$stmtPay = $conn->prepare("
SELECT payment_method
FROM orders
WHERE id=?
");

$stmtPay->execute([$id]);

$payment_method = strtolower(
    trim($stmtPay->fetchColumn())
);

/* GCASH = AUTO PAID */
/* CASH = PENDING */

if($payment_method == 'gcash'){

    $conn->prepare("
    UPDATE orders 
    SET 
        status='Confirmed',
        payment_status='Paid'
    WHERE id=?
    ")->execute([$id]);

    /* AUTO ACCOUNTING FOR GCASH */

    $total_amount = $qty * $selling_price;

    $desc = "GCash Payment - ".$product.
            " (".$qty." pcs)";

    $stmtAcc = $conn->prepare("
    INSERT INTO accounting_transactions
    (
        txn_date,
        type,
        category,
        description,
        amount
    )
    VALUES
    (
        NOW(),
        'Income',
        'GCash Payment',
        ?,
        ?
    )
    ");

    $stmtAcc->execute([
        $desc,
        $total_amount
    ]);

}else{

    /* CASH */

    $conn->prepare("
    UPDATE orders 
    SET 
        status='Confirmed'
    WHERE id=?
    ")->execute([$id]);

}

    // ================= EMAIL =================
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'enslpinc.111@gmail.com'; // gmail 
        $mail->Password = 'spjk wgsu cjgu xjxx';    // APP PASSWORD
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('enslpinc.111@gmail.com', 'EnSLP Inc,');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Order Approved';


        $mail->Body = '
        <div style="font-family:Arial; max-width:600px; margin:auto; border:1px solid #ddd; border-radius:10px; overflow:hidden;">
            
            <div style="background:#28a745; color:#fff; padding:15px; text-align:center;">
                <h2 style="margin:0;">EnSLP Inc.</h2>
            </div>
        
            <div style="padding:20px;">
                <h3 style="color:#28a745;">Order Approved ✅</h3>
        
                <p>Your order has been successfully confirmed.</p>
        
                <p><b>Product:</b> '.$product.'</p>
                <p><b>Status:</b> Confirmed</p>
        
                <div style="text-align:center; margin-top:20px;">
                    <a href="http://localhost/enslp-main/view_order.php?id='.$id.'" style="background:#28a745; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px;">
                        View Order
                    </a>
                </div>
            </div>
        
            <div style="background:#f1f1f1; padding:10px; text-align:center; font-size:12px;">
                © EnSLP Inc. 
            </div>
        
        </div>
        ';

        $mail->send();

    } catch (Exception $e) {
        echo $mail->ErrorInfo;
        exit();
    }
    // ================= END EMAIL =================

    header("Location: orders.php?success=1");
    exit();
}

if(isset($_GET['reject'])){
    $id = (int)$_GET['reject'];

    // kunin order details
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    $product = $order['order_details'];
    $email = $order['email'];

    // update status
    $conn->prepare("UPDATE orders SET status='Rejected' WHERE id=? AND status='Pending'")
         ->execute([$id]);

    // ================= EMAIL =================
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'enslpinc.111@gmail.com';
        $mail->Password = 'spjk wgsu cjgu xjxx';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('enslpinc.111@gmail.com', 'EnSLP Inc.');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Order Rejected';
        $mail->Body = '
        <div style="font-family:Arial; max-width:600px; margin:auto; border:1px solid #ddd; border-radius:10px; overflow:hidden;">
            
            <div style="background:#dc3545; color:#fff; padding:15px; text-align:center;">
                <h2 style="margin:0;">EnSLP Inc.</h2>
            </div>
        
            <div style="padding:20px;">
                <h3 style="color:#dc3545;">Order Rejected ❌</h3>
        
                <p>We’re sorry, your order has been rejected.</p>
        
                <p><b>Product:</b> '.$product.'</p>
                <p><b>Status:</b> Rejected</p>
        
                <div style="text-align:center; margin-top:20px;">
                    <a href="#" style="background:#dc3545; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px;">
                        Contact Support
                    </a>
                </div>
            </div>
        
            <div style="background:#f1f1f1; padding:10px; text-align:center; font-size:12px;">
                © EnSLP Incorporation
            </div>
        
        </div>
        ';


        $mail->send();

    } catch (Exception $e) {
        // optional debug
        // echo $mail->ErrorInfo;
    }
    // ================= END EMAIL =================

    header("Location: orders.php?rejected=1");
    exit();
}

/* ================= FETCH ================= */
$res = $conn->query("
SELECT o.*, c.name AS client_name, c.email
FROM orders o
LEFT JOIN clients c ON o.client_id = c.id
ORDER BY o.id DESC
");

/* ================= TOTAL ================= */
$total = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch()['total'];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Orders</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body{
background:#f4f6f9;
font-family:Arial;
margin:0;
}
.main-content{
margin-left:260px;
padding:25px;
margin-top:75px;
}
.card{
border-radius:10px;
}
.table thead{
background:#f0f3f7;
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>

<div class="main-content">
<div class="container-fluid">

<!-- SUCCESS ALERT -->
<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    ✅ Order approved & email sent!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- REJECT ALERT -->
<?php if(isset($_GET['rejected'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    ❌ Order rejected!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- HEADER -->
<div class="card shadow-sm mb-3">
<div class="card-body d-flex justify-content-between align-items-center">
<h3 class="mb-0">Orders</h3>
<h6 class="mb-0">
Total Orders: <span class="badge bg-primary"><?= $total ?></span>
</h6>
</div>
</div>

<!-- TABLE -->
<div class="card shadow-sm">
<div class="card-body">
<div class="table-responsive">

<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
<th>Client</th>
<th>Product</th>
<th>Qty</th>
<th>Status</th>
<th>Payment Method</th>
<th>Payment Status</th>
<th>Proof</th>
<th>Date Ordered</th>
<th>Action</th>
</tr>
</thead>

<tbody>
<?php while($row = $res->fetch()): ?>
<tr>
<td><?= htmlspecialchars($row['client_name']) ?></td>
<td><?= htmlspecialchars($row['order_details']) ?></td>

<td><?= $row['quantity'] ?></td>

<td>
<?php
$status = $row['status'];
if($status == 'Pending'){
    echo '<span class="badge bg-warning text-dark">Pending</span>';
} elseif($status == 'Confirmed'){
    echo '<span class="badge bg-success">Confirmed</span>';
} elseif($status == 'Rejected'){
    echo '<span class="badge bg-danger">Rejected</span>';
} elseif($status == 'Completed'){
    echo '<span class="badge bg-primary">Completed</span>';
} else {
    echo '<span class="badge bg-secondary">Confirmed</span>';
}
?>
</td>

<td>
<?= htmlspecialchars($row['payment_method']) ?>
</td>

<td>

<?php
if($row['payment_status'] == 'Pending Payment'){

    echo '<span class="badge bg-warning text-dark">
            Pending Payment
          </span>';

}

elseif($row['payment_status'] == 'Paid'){

    echo '<span class="badge bg-success">
            Paid
          </span>';

}

elseif($row['payment_status'] == 'Rejected Payment'){

    echo '<span class="badge bg-danger">
            Rejected Payment
          </span>';

}

else{

    echo '<span class="badge bg-secondary">
            N/A
          </span>';

}
?>

</td>

<td>

<?php if(!empty($row['proof_of_payment'])): ?>

<a href="uploads/<?= $row['proof_of_payment'] ?>"
   target="_blank"
   class="btn btn-primary btn-sm">

   View Proof

</a>

<?php else: ?>

<span class="text-muted">No Proof</span>

<?php endif; ?>

</td>


<td>
<?= date('M d, Y', strtotime($row['created_at'])) ?>
</td>

<td>
<?php if($row['status'] == 'Pending'): ?>
<a href="?approve=<?= $row['id'] ?>" class="btn btn-success btn-sm">
<i class="bi bi-check"></i>
</a>
<a href="?reject=<?= $row['id'] ?>" class="btn btn-danger btn-sm">
<i class="bi bi-x"></i>
</a>
<?php else: ?>
<span class="text-muted">No Action</span>
<?php endif; ?>
</td>

</tr>
<?php endwhile; ?>
</tbody>

</table>

</div>
</div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'footer.php'; ?>

</body>
</html>