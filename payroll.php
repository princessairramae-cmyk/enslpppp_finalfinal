<?php
session_start();
include 'config.php';
require_once 'access_control.php';
check_access(['admin']);

// 🔥 GET DATA
$stmt = $conn->prepare("
SELECT 
    e.id,
    e.full_name,
    e.photo,
    e.rate_per_hour,
    SUM(TIMESTAMPDIFF(HOUR, a.time_in, a.time_out)) as total_hours
FROM employees e
LEFT JOIN attendance a 
    ON e.id = a.employee_id 
    AND a.time_out IS NOT NULL
GROUP BY e.id
");

$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>

<title>Payroll</title>

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
.card{
border-radius:8px;
}
.table thead{
background:#f8f9fa;
}
</style>

</head>

<body>

<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>

<div class="main-content">
<div class="container-fluid">

<!-- 🔥 HEADER -->
<div class="card shadow-sm mb-4">
<div class="card-body text-center">

<h2 class="fw-bold">Payroll System</h2>
<p class="text-muted">Employee Salary Overview</p>

</div>
</div>

<!-- 🔥 PAYROLL TABLE -->
<div class="card shadow-sm">
<div class="card-header bg-primary text-white">
<h5 class="mb-0">General Payroll Report</h5>
</div>

<div class="card-body table-responsive">

<table class="table table-hover align-middle text-center">

<thead>
<tr>
<th>Photo</th>
<th>Name</th>
<th>Total Hours</th>
<th>Rate</th>
<th>Salary</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php foreach($employees as $emp): 
    $hours = $emp['total_hours'] ? $emp['total_hours'] : 0;
    $rate  = $emp['rate_per_hour'] ? $emp['rate_per_hour'] : 0;
    $salary = $hours * $rate;
?>

<tr>

<td>
<img src="assets/images/<?php echo $emp['photo'] ?: 'default.png'; ?>" width="50" style="border-radius:50%;">
</td>

<td class="fw-semibold"><?php echo $emp['full_name']; ?></td>

<td>
<span class="badge bg-primary">
<?php echo $hours; ?> hrs
</span>
</td>

<td>₱<?php echo number_format($rate,2); ?></td>

<td>
<strong class="text-success">
₱<?php echo number_format($salary,2); ?>
</strong>
</td>

<td>
<a href="employee_payroll.php?id=<?php echo $emp['id']; ?>" 
class="btn btn-sm btn-primary">
View
</a>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>
</div>

</div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>