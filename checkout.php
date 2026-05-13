<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['client_id'])){
    header("Location: client_login.php");
    exit();
}

if(empty($_SESSION['cart'])){
    header("Location: client_cart.php");
    exit();
}

/* PROCESS CHECKOUT */

if(isset($_POST['checkout'])){

    $payment_method = $_POST['payment_method'];

    $proof_name = '';

if(
    isset($_FILES['proof_of_payment']) &&
    $_FILES['proof_of_payment']['error'] == 0
){

    $file_tmp = $_FILES['proof_of_payment']['tmp_name'];

    $file_name = basename(
        $_FILES['proof_of_payment']['name']
    );

    $proof_name = time().'_'.$file_name;

    $upload_path = __DIR__ . '/uploads/' . $proof_name;

    if(move_uploaded_file($file_tmp, $upload_path)){

        // upload success

    }else{

        die("UPLOAD FAILED");

    }

}

    foreach($_SESSION['cart'] as $item){

        $stmt = $conn->prepare("
            INSERT INTO orders
            (
                client_id,
                order_details,
                quantity,
                status,
                payment_method,
                payment_status,
                proof_of_payment
            )
            VALUES
            (
                ?, ?, ?, 'Pending',
                ?, 'Pending Payment', ?
            )
        ");

        $stmt->execute([
            $_SESSION['client_id'],
            $item['product'],
            $item['qty'],
            $payment_method,
            $proof_name
        ]);
    }

    /* CLEAR CART */

    unset($_SESSION['cart']);

    echo "
    <script>
        alert('Order placed successfully!');
        window.location='client_orders.php';
    </script>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Checkout</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header">
            <h3>Checkout</h3>
        </div>

        <div class="card-body">

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">

                    <label class="form-label">
                        Payment Method
                    </label>

                    <select name="payment_method"
                            class="form-control"
                            required>

                        <option value="">
                            Select Payment Method
                        </option>

                        <option value="GCash">
                            GCash
                        </option>

                        <option value="Cash">
                            Cash
                        </option>

                    </select>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Upload Proof of Payment
                    </label>

                    <input type="file"
                           name="proof_of_payment"
                           class="form-control">

                </div>

                <!-- FAKE QR -->

                <div class="text-center mb-4">

                <img src="uploads/gcash_qr.jpg"
                width="250">

                    <p class="mt-2">
                        Scan this QR using GCash
                    </p>

                </div>

                <button type="submit"
                        name="checkout"
                        class="btn btn-primary w-100">

                    Place Order

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>