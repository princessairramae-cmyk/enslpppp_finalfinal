<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

include 'config.php';

/* ================= FETCH CLIENTS ================= */
$res = $conn->query("
SELECT *
FROM clients
ORDER BY id DESC
");

/* ================= TOTAL ================= */
$total = $conn->query("
SELECT COUNT(*) as total 
FROM clients
")->fetch()['total'];

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Clients</title>

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

<!-- HEADER -->
<div class="card shadow-sm mb-3">
<div class="card-body d-flex justify-content-between align-items-center">

<h3 class="mb-0">
Clients
</h3>

<h6 class="mb-0">
Total Clients: 
<span class="badge bg-primary">
<?= $total ?>
</span>
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
<th>ID</th>
<th>Client Name</th>
<th>Email</th>
<th>Contact Number</th>
<th>Address</th>
<th>Date Registered</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php while($row = $res->fetch()): ?>

<tr>

<td>
<?= $row['id'] ?>
</td>

<td>
<?= htmlspecialchars($row['name']) ?>
</td>

<td>
<?= htmlspecialchars($row['email']) ?>
</td>

<td>
<?= $row['contact_number'] ?: 'N/A' ?>
</td>

<td>
<?= $row['address'] ?: 'N/A' ?>
</td>

<td>
<?= date('M d, Y', strtotime($row['created_at'])) ?>
</td>

<td>
<span class="badge bg-success">
Active
</span>
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