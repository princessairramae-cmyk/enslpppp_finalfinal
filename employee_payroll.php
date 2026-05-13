<?php
session_start();
include 'config.php';
require_once 'access_control.php';
check_access(['admin']);

$emp_id = $_GET['id'];

// 🔥 EMPLOYEE
$stmt = $conn->prepare("SELECT * FROM employees WHERE id=?");
$stmt->execute([$emp_id]);
$emp = $stmt->fetch(PDO::FETCH_ASSOC);

// 🔥 ATTENDANCE
$stmt2 = $conn->prepare("
SELECT 
    att_date,
    time_in,
    time_out,
    TIMESTAMPDIFF(MINUTE, time_in, time_out) / 60 as hours
FROM attendance
WHERE employee_id = ?
ORDER BY att_date DESC
");

$stmt2->execute([$emp_id]);
$records = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$total_hours = 0;
?>

<!DOCTYPE html>
<html>
<head>

<title>Employee Payroll</title>

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

<div class="d-flex justify-content-between align-items-center mb-3">

    <!-- LEFT -->
    <a href="payroll.php" class="btn btn-secondary">
        ← Back to Payroll
    </a>

    <!-- RIGHT -->
    <a href="print_payslip.php?id=<?php echo $emp_id; ?>" 
       class="btn btn-success">
       🖨 Print Payslip
    </a>

</div>

<!-- 🔥 EMPLOYEE HEADER --
<div class="card shadow-sm mb-4">
<div class="card-body text-center">

<div class="d-flex align-items-center justify-content-center gap-3">

<img src="assets/images/<?php echo $emp['photo'] ?: 'default.png'; ?>"
width="70" height="70"
style="border-radius:50%; object-fit:cover;">

<div>
<h3 class="fw-bold mb-0"><?php echo $emp['full_name']; ?></h3>
<p class="text-muted mb-0">Individual Payroll Overview</p>
</div>

</div>

</div>
</div>

<!-- 🔥 ATTENDANCE TABLE -->
<div class="card shadow-sm">
<div class="card-header bg-primary text-white">
<h5 class="mb-0">Attendance Records</h5>
</div>

<div class="card-body table-responsive">

<table class="table table-hover align-middle text-center">

<thead>
<tr>
<th>Date</th>
<th>Time In</th>
<th>Time Out</th>
<th>Hours</th>
</tr>
</thead>

<tbody>

<?php foreach($records as $r): 
    $hours = $r['hours'] ? $r['hours'] : 0;
    $total_hours += $hours;
?>

<tr>

<td><?php echo $r['att_date']; ?></td>

<td>
<span class="badge bg-success">
<?php echo $r['time_in']; ?>
</span>
</td>

<td>
<span class="badge bg-dark">
<?php echo $r['time_out'] ?: '--'; ?>
</span>
</td>

<td>
<span class="badge bg-<?php echo $hours > 0 ? 'primary' : 'secondary'; ?>">
<?php echo $hours; ?> hrs
</span>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>
</div>

<!-- 🔥 SUMMARY CARDS -->
<?php 
$rate = $emp['rate_per_hour'] ? $emp['rate_per_hour'] : 0;
$salary = $total_hours * $rate;
?>

<div class="row mt-3 text-center">

<div class="col-md-4">
<div class="card shadow-sm p-3">
<h6>Total Hours</h6>
<h4 class="text-primary"><?php echo $total_hours; ?> hrs</h4>
</div>
</div>



<div class="col-md-4">
<div class="card shadow-sm p-3">
<h6>Rate</h6>
<h4>₱<?php echo number_format($rate,2); ?></h4>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm p-3">
<h6>Total Salary</h6>
<h4 class="text-success">₱<?php echo number_format($salary,2); ?></h4>
</div>
</div>

</div>

</div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>