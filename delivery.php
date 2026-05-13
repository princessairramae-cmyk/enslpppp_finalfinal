<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();
require_once "config.php";

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'access_control.php';
check_access(['admin','accounting','staff']);

/* GENERATE DR NUMBER */
function generateDR($conn){
    $stmt=$conn->query("SELECT COUNT(*) FROM deliveries");
    $n=$stmt->fetchColumn()+1;
    return "DR-".date("Y")."-".str_pad($n,4,"0",STR_PAD_LEFT);
}

/* CREATE DELIVERY */
if(isset($_POST['save_delivery'])){

    $dr = generateDR($conn);

    $wo_id = $_POST['wo_id'];

    /* CHECK PACKING */
    $check = $conn->prepare("
    SELECT COUNT(*) FROM packing_jobs WHERE work_order_id=?
    ");
    $check->execute([$wo_id]);
    $packing = $check->fetchColumn();

    if($packing == 0){
        die("❌ Cannot create delivery. Items not packed yet.");
    }

    $delivered_to = $_POST['delivered_to'];
    $address = $_POST['address'];
    $delivered_date = $_POST['delivered_date'];
    $status = "pending";
    $remarks = $_POST['remarks'];
    $delivery_qty = (int)$_POST['delivery_qty'];

    $stmt=$conn->prepare("
    INSERT INTO deliveries
    (
        dr_no,
        wo_id,
        delivered_to,
        address,
        delivered_date,
        status,
        remarks,
        delivery_qty,
        created_at
    )
    VALUES (?,?,?,?,?,?,?,?,NOW())
    ");

    $stmt->execute([
        $dr,
        $wo_id,
        $delivered_to,
        $address,
        $delivered_date,
        $status,
        $remarks,
        $delivery_qty
    ]);

    header("Location: delivery.php?created=1");
    exit;
}

/* REDELIVER */
if(isset($_POST['redeliver_id'])){

    $id = $_POST['redeliver_id'];

    $stmt = $conn->prepare("
    UPDATE deliveries
    SET status='pending',
        remarks='Good'
    WHERE id=?
    ");

    $stmt->execute([$id]);

    header("Location: delivery.php?redeliver=1");
    exit;
}

/* UPDATE STATUS */
if(isset($_POST['update_status'])){

    $id=$_POST['delivery_id'];
    $new_status=strtolower(trim($_POST['new_status']));

    /* GET CURRENT STATUS */
    $stmt=$conn->prepare("SELECT status FROM deliveries WHERE id=?");
    $stmt->execute([$id]);
    $current=strtolower($stmt->fetchColumn());

    /* STRICT FLOW */
    if($current == "pending" && $new_status != "out for delivery"){
        header("Location: delivery.php?error=invalid_flow");
        exit;
    }

    if($current == "out for delivery" && $new_status != "delivered"){
        header("Location: delivery.php?error=invalid_flow");
        exit;
    }

    /* UPDATE STATUS */
    $stmt=$conn->prepare("UPDATE deliveries SET status=? WHERE id=?");
    $stmt->execute([$new_status,$id]);

    if($new_status=="delivered"){

        /* GET REMARKS */
$stmtRemarks = $conn->prepare("
SELECT remarks
FROM deliveries
WHERE id=?
");

$stmtRemarks->execute([$id]);

$remarks = strtolower(trim($stmtRemarks->fetchColumn()));


        /* GET WO ID */
        $stmt=$conn->prepare("SELECT wo_id FROM deliveries WHERE id=?");
        $stmt->execute([$id]);
        $row=$stmt->fetch();
        $wo_id=$row['wo_id'];

        /* TOTAL DELIVERED */
        $stmt=$conn->prepare("
        SELECT COALESCE(SUM(delivery_qty),0)
        FROM deliveries
        WHERE wo_id=? AND status='delivered'
        ");
        $stmt->execute([$wo_id]);
        $total_delivered = $stmt->fetchColumn();

        /* ORDER QTY */
        $stmt=$conn->prepare("
        SELECT qty
        FROM work_orders
        WHERE id=?
        ");
        $stmt->execute([$wo_id]);
        $order_qty = $stmt->fetchColumn();

      /* COMPLETE */
if($total_delivered >= $order_qty){

    /* UPDATE WORK ORDER */
    $stmt=$conn->prepare("
    UPDATE work_orders
    SET status='Completed',
        date_completed=NOW()
    WHERE id=?
    ");

    $stmt->execute([$wo_id]);

    /* GET ORDER ID */
    $stmtOrder = $conn->prepare("
    SELECT order_id
    FROM work_orders
    WHERE id=?
    ");

    $stmtOrder->execute([$wo_id]);

    $order_id = $stmtOrder->fetchColumn();

    /* UPDATE ORDER STATUS */
    if($order_id){

        $stmtUpdateOrder = $conn->prepare("
        UPDATE orders
        SET status='Delivered'
        WHERE id=?
        ");

        $stmtUpdateOrder->execute([$order_id]);
    }
}

     /* GET WORK ORDER + CLIENT EMAIL */
$stmt=$conn->prepare("
SELECT 
    w.wo_no,
    w.product_name,
    w.qty,
    w.selling_price,
    w.client_name,
    c.email AS client_email
FROM work_orders w
LEFT JOIN orders o ON w.order_id = o.id
LEFT JOIN clients c ON o.client_id = c.id
WHERE w.id=?
");

$stmt->execute([$wo_id]);
$wo=$stmt->fetch();

        if(!$wo){
            die("Work order not found");
        }

        /* EMAIL */
        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->SMTPDebug = 2;
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'enslpinc.111@gmail.com';
            $mail->Password   = 'spjk wgsu cjgu xjxx';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('enslpinc.111@gmail.com', 'ENSLP System');
$mail->addReplyTo('enslpinc.111@gmail.com', 'ENSLP System');

            if(!empty($wo['client_email'])){
                $mail->addAddress($wo['client_email'], $wo['client_name']);
            }else{
                $mail->addAddress('enslpinc.111@gmail.com', $wo['client_name']);
            }

            $mail->isHTML(true);
            $mail->Subject = 'Delivery Completed - '.$wo['wo_no'];

            $mail->Body = "

            <div style='
            max-width:650px;
            margin:auto;
            font-family:Arial,sans-serif;
            border:1px solid #ddd;
            border-radius:10px;
            overflow:hidden;
            '>
            
                <!-- HEADER -->
            
                <div style='
                background:#28a745;
                color:#fff;
                padding:20px;
                text-align:center;
                font-size:30px;
                font-weight:bold;
                '>
            
                    EnSLP Inc.
            
                </div>
            
                <!-- BODY -->
            
                <div style='padding:30px;background:#fff;'>
            
                    <h2 style='color:#28a745;margin-top:0;'>
                    Order Delivered ✅
                    </h2>
            
                    <p style='font-size:15px;color:#333;'>
                    Hello <strong>".$wo['client_name']."</strong>,
                    </p>
            
                    <p style='font-size:15px;color:#333;line-height:1.6;'>
            
                    Your order has been successfully delivered.
            
                    </p>
            
                    <table style='
                    width:100%;
                    border-collapse:collapse;
                    margin-top:20px;
                    '>
            
                        <tr>
                            <td style='padding:10px;font-weight:bold;width:180px;'>
                            Work Order:
                            </td>
            
                            <td style='padding:10px;'>
                            ".$wo['wo_no']."
                            </td>
                        </tr>
            
                        <tr>
                            <td style='padding:10px;font-weight:bold;'>
                            Product:
                            </td>
            
                            <td style='padding:10px;'>
                            ".$wo['product_name']."
                            </td>
                        </tr>
            
                        <tr>
                            <td style='padding:10px;font-weight:bold;'>
                            Quantity:
                            </td>
            
                            <td style='padding:10px;'>
                            ".$wo['qty']." pcs
                            </td>
                        </tr>
            
                        <tr>
                            <td style='padding:10px;font-weight:bold;'>
                            Status:
                            </td>
            
                            <td style='padding:10px;'>
            
                                <span style='
                                background:#28a745;
                                color:#fff;
                                padding:6px 12px;
                                border-radius:5px;
                                font-size:13px;
                                '>
            
                                Delivered
            
                                </span>
            
                            </td>
                        </tr>
            
                    </table>
            
                    <!-- BUTTON -->
            
                    <div style='text-align:center;margin-top:35px;'>
            
                        <a href='http://localhost/enslp-main/client_orders.php'
                        style='
                        background:#28a745;
                        color:#fff;
                        text-decoration:none;
                        padding:14px 28px;
                        border-radius:6px;
                        display:inline-block;
                        font-size:15px;
                        font-weight:bold;
                        '>
            
                        View Order
            
                        </a>
            
                    </div>
            
                </div>
            
                <!-- FOOTER -->
            
                <div style='
                background:#f1f1f1;
                padding:15px;
                text-align:center;
                color:#777;
                font-size:13px;
                '>
            
                    © EnSLP Inc.
            
                </div>
            
            </div>
            
            ";

            $mail->send();
            echo "Email sent successfully";

        } catch (Exception $e) {

            echo $mail->ErrorInfo;
        
        }

        /* GET DELIVERY QTY */
        $stmt=$conn->prepare("
        SELECT delivery_qty
        FROM deliveries
        WHERE id=?
        ");

        $stmt->execute([$id]);
        $delivery_qty=$stmt->fetchColumn();

        if(trim(strtolower($remarks)) == "good"){

            /* DEDUCT INVENTORY */
            $stmt_out = $conn->prepare("
            UPDATE inventory_items
            SET quantity = quantity - ?
            WHERE item_name = ?
            ");
        
            $stmt_out->execute([
                $delivery_qty,
                $wo['product_name']
            ]);
        
            /* GET ITEM ID */
            $stmt_item = $conn->prepare("
            SELECT id
            FROM inventory_items
            WHERE item_name=?
            ");
        
            $stmt_item->execute([$wo['product_name']]);
            $item_id = $stmt_item->fetchColumn();
        
            /* STOCK MOVEMENT */
            $stmt_sm = $conn->prepare("
            INSERT INTO stock_movements
            (
                item_id,
                movement_type,
                quantity,
                reference,
                notes,
                created_at
            )
            VALUES (?,?,?,?,?,NOW())
            ");
        
            $stmt_sm->execute([
                $item_id,
                'OUT',
                $delivery_qty,
                'DR-'.$id,
                'Delivery '.$wo['wo_no']
            ]);
        
            /* ACCOUNTING */
            $total = $delivery_qty * $wo['selling_price'];
        
            $desc = "Delivery ".$wo['wo_no'].
            " - ".$wo['product_name'].
            " (".$delivery_qty." pcs)";
        
            $stmt2=$conn->prepare("
            INSERT INTO accounting_transactions
            (
                txn_date,
                type,
                category,
                reference_no,
                wo_id,
                description,
                payment_method,
                amount
            )
            VALUES
            (
                NOW(),
                'Income',
                'Delivery',
                ?,
                ?,
                ?,
                ?,
                ?
            )
            ");
        
            $stmt2->execute([
                'DR-'.$id,
                $wo_id,
                $desc,
                'Delivery',
                $total
            ]);
        
        }
    }

    header("Location: delivery.php?updated=1");
    exit;
}

/* FETCH WORK ORDERS */
$workOrders=$conn->query("
SELECT *
FROM work_orders
WHERE status!='Completed'
ORDER BY id DESC
")->fetchAll();

/* FETCH DELIVERIES */
$deliveries=$conn->query("
SELECT d.*,w.wo_no,w.product_name,w.client_name
FROM deliveries d
LEFT JOIN work_orders w
ON d.wo_id=w.id
ORDER BY d.id DESC
")->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>

<title>Delivery Management</title>

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

<?php include "sidebar.php"; ?>
<?php include "header.php"; ?>

<div class="main-content">

<div class="card shadow-sm mb-3">
<div class="card-body">
<h3>Delivery Management</h3>
</div>
</div>

<?php if(isset($_GET['created'])){ ?>
<div class="alert alert-success">Delivery created.</div>
<?php } ?>

<?php if(isset($_GET['updated'])){ ?>
<div class="alert alert-success">Status updated.</div>
<?php } ?>

<?php if(isset($_GET['redeliver'])){ ?>
<div class="alert alert-danger">
Delivery marked for redelivery.
</div>
<?php } ?>

<div class="card shadow-sm">

<div class="card-header">
<button class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#deliveryModal">

+ Create Delivery

</button>
</div>

<div class="card-body table-responsive">

<table class="table table-bordered">

<thead>
<tr>
<th>DR No</th>
<th>Work Order</th>
<th>Customer</th>
<th>Date</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if(!$deliveries){ ?>

<tr>
<td colspan="6" class="text-center">
No deliveries yet
</td>
</tr>

<?php } ?>

<?php foreach($deliveries as $d){ ?>

<tr>

<td><?=$d['dr_no']?></td>

<td>
<?=$d['wo_no']?> - <?=$d['product_name']?>
</td>

<td><?=$d['client_name']?></td>

<td><?=$d['delivered_date']?></td>

<td>

<?php

$status = strtolower(trim($d['status']));

if($status=="delivered"){
    echo '<span class="badge bg-success">Delivered</span>';
}
elseif($status=="out for delivery"){
    echo '<span class="badge bg-warning text-dark">Out for Delivery</span>';
}
else{
    echo '<span class="badge bg-secondary">Pending</span>';
}

?>

</td>

<td>

<?php
$remarks = strtolower(trim($d['remarks']));
?>

<?php if($remarks=="no good"){ ?>

<form method="POST">

<input type="hidden"
name="redeliver_id"
value="<?=$d['id']?>">

<button class="btn btn-sm btn-danger">
Redeliver
</button>

</form>

<?php } elseif($status!="delivered"){ ?>

<form method="POST" style="display:flex;gap:5px">

<input type="hidden"
name="delivery_id"
value="<?=$d['id']?>">

<select name="new_status"
class="form-control form-control-sm">

<option value="pending"
<?=($status=="pending")?'selected':''?>>
Pending
</option>

<option value="out for delivery"
<?=($status=="out for delivery")?'selected':''?>>

Out for Delivery

</option>

<option value="delivered"
<?=($status=="pending")?'disabled':''?>>

Delivered

</option>

</select>

<button class="btn btn-sm btn-primary"
name="update_status">

Update

</button>

</form>

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<!-- MODAL -->

<div class="modal fade" id="deliveryModal">

<div class="modal-dialog">

<form method="POST" class="modal-content">

<div class="modal-header">

<h5>Create Delivery</h5>

<button type="button"
class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="mb-3">

<label>Work Order</label>

<select name="wo_id"
id="wo_select"
class="form-control"
required>

<option value="">Select Work Order</option>

<?php foreach($workOrders as $wo){ ?>

<?php

/* CLIENT ADDRESS */
$stmtClient = $conn->prepare("
SELECT address
FROM clients
WHERE TRIM(REPLACE(LOWER(name),' ',''))
=
TRIM(REPLACE(LOWER(?),' ',''))
LIMIT 1
");

$stmtClient->execute([$wo['client_name']]);

$clientAddress = $stmtClient->fetchColumn();

if(empty($clientAddress)){
    $clientAddress = '';
}

/* PACKED */
$stmtPacked = $conn->prepare("
SELECT COALESCE(SUM(quantity_packed),0)
FROM packing_jobs
WHERE work_order_id=?
");

$stmtPacked->execute([$wo['id']]);

$packed_qty = $stmtPacked->fetchColumn();

/* DELIVERED */
$stmtDelivered = $conn->prepare("
SELECT COALESCE(SUM(delivery_qty),0)
FROM deliveries
WHERE wo_id=?
");

$stmtDelivered->execute([$wo['id']]);

$delivered_qty = $stmtDelivered->fetchColumn();

/* REMAINING */
$remaining_delivery = $packed_qty - $delivered_qty;

if($remaining_delivery < 0){
    $remaining_delivery = 0;
}

?>

<option
value="<?=$wo['id']?>"
data-client="<?=htmlspecialchars($wo['client_name'])?>"
data-address="<?=htmlspecialchars($clientAddress)?>"
data-qty="<?=$remaining_delivery?>"
>

<?=$wo['wo_no']?> - <?=$wo['product_name']?> - <?=$wo['client_name']?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label>Delivered To</label>

<input type="text"
id="delivered_to"
name="delivered_to"
class="form-control">

</div>

<div class="mb-3">

<label>Address</label>

<input type="text"
id="address"
name="address"
class="form-control">

</div>

<div class="mb-3">

<label>Quantity to Deliver</label>

<input type="number"
name="delivery_qty"
id="delivery_qty"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Date</label>

<input type="date"
name="delivered_date"
value="<?=date('Y-m-d')?>"
class="form-control">

</div>

<div class="mb-3">

<label>Status</label>

<input type="text"
class="form-control"
value="Pending"
readonly>

</div>

<div class="mb-3">

<label>Remarks</label>

<select name="remarks" class="form-control">

<option value="Good">Good</option>

<option value="No Good">No Good</option>

</select>

</div>

</div>

<div class="modal-footer">

<button class="btn btn-primary"
name="save_delivery">

Save Delivery

</button>

</div>

</form>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.getElementById("wo_select").addEventListener("change", function(){

    let selected = this.options[this.selectedIndex];

    let client  = selected.dataset.client || '';
    let address = selected.dataset.address || '';
    let qty     = selected.dataset.qty || '';

    document.getElementById("delivered_to").value = client;

    document.getElementById("address").value = address;

    document.getElementById("delivery_qty").value = qty;

});

</script>

<?php include "footer.php"; ?>

</body>
</html>