<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();
require_once 'config.php';

if(!isset($_SESSION['client_id'])){
    header("Location: client_login.php");
    exit();
}

$client_id = $_SESSION['client_id'];

$stmt = $conn->prepare("
SELECT name, email, contact_number, address, created_at
FROM clients
WHERE id=?
");
$stmt->execute([$client_id]);

$client = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Profile</title>

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

.label{
    font-weight:600;
    color:#555;
    font-size:13px;
}

/* 🔥 BOX STYLE */
.info-box{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:10px;
    padding:15px;
    transition:0.2s;
}

.info-box:hover{
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.value{
    font-size:15px;
    font-weight:500;
    margin-top:3px;
}
</style>
</head>

<body>

<?php include 'client_sidebar.php'; ?>

<div class="main-content">

<h4 class="mb-4">
<i class="bi bi-person-circle"></i> My Profile
</h4>

<div class="card">
<div class="card-body">

<div class="row g-3">

<div class="col-md-6">
    <div class="info-box">
        <div class="label"><i class="bi bi-person"></i> Full Name</div>
        <div class="value"><?= htmlspecialchars($client['name']) ?></div>
    </div>
</div>

<div class="col-md-6">
    <div class="info-box">
        <div class="label"><i class="bi bi-envelope"></i> Email</div>
        <div class="value"><?= htmlspecialchars($client['email']) ?></div>
    </div>
</div>

<div class="col-md-6">
    <div class="info-box">
        <div class="label"><i class="bi bi-telephone"></i> Contact Number</div>
        <div class="value"><?= htmlspecialchars($client['contact_number'] ?? '-') ?></div>
    </div>
</div>

<div class="col-md-6">
    <div class="info-box">
        <div class="label"><i class="bi bi-geo-alt"></i> Address</div>
        <div class="value"><?= htmlspecialchars($client['address'] ?? '-') ?></div>
    </div>
</div>

<div class="col-md-6">
    <div class="info-box">
        <div class="label"><i class="bi bi-calendar"></i> Member Since</div>
        <div class="value">
            <?= $client['created_at'] ? date('M d, Y', strtotime($client['created_at'])) : '-' ?>
        </div>
    </div>
</div>

</div>

<hr class="my-4">

<a href="client_edit_profile.php" class="btn btn-primary">
<i class="bi bi-pencil-square"></i> Edit Profile
</a>

</div>
</div>

</div>

</body>
</html>