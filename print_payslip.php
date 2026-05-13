<?php
session_start();
include 'config.php';
require_once 'access_control.php';
check_access(['admin']);

$emp_id = $_GET['id'];

// EMPLOYEE
$stmt = $conn->prepare("SELECT * FROM employees WHERE id=?");
$stmt->execute([$emp_id]);
$emp = $stmt->fetch(PDO::FETCH_ASSOC);

// ATTENDANCE
$stmt2 = $conn->prepare("
SELECT 
    TIMESTAMPDIFF(MINUTE, time_in, time_out) / 60 as hours
FROM attendance
WHERE employee_id = ? AND time_out IS NOT NULL
");
$stmt2->execute([$emp_id]);

$total_hours = 0;
while($r = $stmt2->fetch()){
    $total_hours += $r['hours'];
}

// COMPUTE
$rate = $emp['rate_per_hour'] ?? 0;
$gross = $total_hours * $rate;

// DEDUCTIONS (pwede mo pa palitan later)
$sss = $gross * 0.05;
$philhealth = $gross * 0.03;
$late = 50;

$total_deduction = $sss + $philhealth + $late;
$net = $gross - $total_deduction;

// SAMPLE DATE RANGE
$date_from = date("M 1, Y");
$date_to   = date("M d, Y");
?>

<!DOCTYPE html>
<html>
<head>
<title>Payslip</title>

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

/* PAYSLIP DESIGN */
.payslip-box{
    max-width:750px;
    margin:auto;
    border:1px solid #ddd;
    padding:25px;
    background:#fff;
}

.company-header{
    text-align:center;
}

.company-header img{
    width:60px;
    margin-bottom:5px;
}

.section-title{
    font-weight:bold;
    margin-top:15px;
    border-bottom:1px solid #ccc;
    padding-bottom:5px;
}

.flex-between{
    display:flex;
    justify-content:space-between;
}

.signature{
    margin-top:50px;
    display:flex;
    justify-content:space-between;
    text-align:center;
}

/* PRINT MODE */
@media print {
    .no-print{
        display:none;
    }
    .main-content{
        margin:0;
        padding:0;
    }
}
</style>

</head>

<body>

<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>

<div class="main-content">
<div class="container-fluid">

<!-- TOP BAR -->
<div class="d-flex justify-content-between mb-3 no-print">
    <h4 class="fw-bold">Payslip Preview</h4>
    <button onclick="window.print()" class="btn btn-success">
        🖨 Print Payslip
    </button>
</div>

<!-- PAYSLIP -->
<div class="payslip-box shadow-sm">

<div class="company-header">
    <img src="assets/logo.png" onerror="this.style.display='none'">
    <h5 class="fw-bold mb-0">EnSLP Inc.</h5>
    <small>Integrated Management System</small>
</div>

<hr>

<div class="flex-between">
    <div>
        <strong>Employee:</strong><br>
        <?php echo $emp['full_name']; ?>
    </div>

    <div style="text-align:right;">
        <strong>Payroll Period:</strong><br>
        <?php echo $date_from . " - " . $date_to; ?>
    </div>
</div>

<div class="section-title">Earnings</div>

<div class="flex-between">
    <span>Total Hours</span>
    <span><?php echo number_format($total_hours,2); ?></span>
</div>

<div class="flex-between">
    <span>Rate per Hour</span>
    <span>₱<?php echo number_format($rate,2); ?></span>
</div>

<div class="flex-between">
    <strong>Gross Pay</strong>
    <strong>₱<?php echo number_format($gross,2); ?></strong>
</div>

<div class="section-title">Deductions</div>

<div class="flex-between">
    <span>SSS</span>
    <span>₱<?php echo number_format($sss,2); ?></span>
</div>

<div class="flex-between">
    <span>PhilHealth</span>
    <span>₱<?php echo number_format($philhealth,2); ?></span>
</div>

<div class="flex-between">
    <span>Late Penalty</span>
    <span>₱<?php echo number_format($late,2); ?></span>
</div>

<div class="flex-between">
    <strong>Total Deductions</strong>
    <strong>₱<?php echo number_format($total_deduction,2); ?></strong>
</div>

<hr>

<div class="flex-between">
    <h5>Net Pay</h5>
    <h5 class="text-success">₱<?php echo number_format($net,2); ?></h5>
</div>

<!-- SIGNATURE -->
<div class="signature">
    <div>
        ________________________<br>
        Employee Signature
    </div>

    <div>
        ________________________<br>
        Authorized Signature
    </div>
</div>

</div>

</div>
</div>

</body>
</html>