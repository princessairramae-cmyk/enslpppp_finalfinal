<?php
session_start();

if(!isset($_SESSION['client_id'])){
    header("Location: client_login.php");
    exit();
}

$method = $_POST['method'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Processing Payment</title>

    <meta http-equiv="refresh" content="3;url=payment_success.php?method=<?= $method ?>">
</head>
<body>

<h2>Processing Payment...</h2>

<p>Please wait...</p>

</body>
</html>