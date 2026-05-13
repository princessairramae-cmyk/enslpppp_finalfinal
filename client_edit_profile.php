<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['client_id'])){
    header("Location: client_login.php");
    exit();
}

$client_id = $_SESSION['client_id'];

// FETCH CURRENT DATA
$stmt = $conn->prepare("SELECT name, email, contact_number, address FROM clients WHERE id=?");
$stmt->execute([$client_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

// UPDATE
if(isset($_POST['update'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact_number'];
    $address = $_POST['address'];

    $update = $conn->prepare("
    UPDATE clients 
    SET name=?, email=?, contact_number=?, address=?
    WHERE id=?
    ");
    $update->execute([$name, $email, $contact, $address, $client_id]);

    header("Location: client_profile.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f4f6f9;
}
.main-content{
    margin-left:260px;
    padding:25px;
}
.card{
    border-radius:10px;
}
</style>
</head>

<body>

<?php include 'client_sidebar.php'; ?>

<div class="main-content">

<h4 class="mb-4">Edit Profile</h4>

<div class="card">
<div class="card-body">

<form method="POST">

<div class="mb-3">
<label>Name</label>
<input type="text" name="name" class="form-control" value="<?= htmlspecialchars($client['name']) ?>" required>
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" value="<?= htmlspecialchars($client['email']) ?>" required>
</div>

<div class="mb-3">
<label>Contact Number</label>
<input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($client['contact_number']) ?>">
</div>

<div class="mb-3">
<label>Address</label>
<textarea name="address" class="form-control"><?= htmlspecialchars($client['address']) ?></textarea>
</div>

<button type="submit" name="update" class="btn btn-success">
Update Profile
</button>

<a href="client_profile.php" class="btn btn-secondary">
Cancel
</a>

</form>

</div>
</div>

</div>

</body>
</html>