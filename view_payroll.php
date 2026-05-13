<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { die("Invalid payroll."); }

function peso($n){ return number_format((float)$n,2); }
function num($v){ return is_numeric($v)?(float)$v:0; }

$stmt = $conn->prepare("
SELECT pr.*, e.full_name, e.department, e.position,
       e.salary_type, e.salary_amount, e.daily_rate, e.monthly_salary
FROM payroll pr
JOIN employees e ON e.id = pr.employee_id
WHERE pr.id = ?
LIMIT 1
");
$stmt->execute([$id]);
$row = $stmt->fetch();

if(!$row){ die("Payroll not found."); }

$company = "EnSLP Inc.";

/* salary fix */
$salary_type = $row['salary_type'] ?? '';
$salary_amount = num($row['salary_amount']);

if ($salary_type === '' || $salary_amount <= 0) {
    $salary_amount = $row['monthly_salary'] ?: $row['daily_rate'];
    $salary_type = $row['monthly_salary'] ? 'Monthly' : 'Daily';
}

$rateLabel = ($salary_type == 'Monthly') ? 'MONTHLY SALARY' : 'DAILY RATE';
$rateSuffix = ($salary_type == 'Monthly') ? '/month' : '/day';

/* values */
$gross = num($row['gross_pay']);
$net   = num($row['net_pay']);

$sss = num($row['sss']);
$philhealth = num($row['philhealth']);
$pagibig = num($row['pagibig']);
$other = num($row['other_deductions']);

$total_ded = $sss + $philhealth + $pagibig + $other;
?>

<!DOCTYPE html>
<html>
<head>
<title>View Payroll</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{background:#f5f8ff;font-family:'Segoe UI';}
.wrap{max-width:860px;margin:30px auto;}
.cardx{
background:#fff;
border-radius:16px;
padding:22px;
border:1px solid #e7eefc;
}
.line{height:1px;background:#e7eefc;margin:15px 0;}
.muted{color:#64748b;font-size:13px;}
.net-row td{
background:#f3f7ff;
font-weight:bold;
}
</style>

</head>

<body>

<div class="wrap">

<div class="mb-3">
<a href="payroll.php" class="btn btn-secondary btn-sm">← Back</a>
</div>

<div class="cardx shadow-sm">

<div class="d-flex justify-content-between">
<div>
<h4 class="fw-bold"><?= $company ?></h4>
<div class="muted">Payroll Details</div>
</div>

<div class="text-end">
<div class="fw-semibold">Payroll #<?= $row['id'] ?></div>
<div class="muted">
<?= $row['period_start'] ?> to <?= $row['period_end'] ?>
</div>
</div>
</div>

<div class="line"></div>

<div class="row">
<div class="col-md-6">
<div class="muted">EMPLOYEE</div>
<div class="fw-semibold"><?= $row['full_name'] ?></div>
<div class="muted"><?= $row['position'] ?> • <?= $row['department'] ?></div>
</div>

<div class="col-md-6 text-end">
<div class="muted"><?= $rateLabel ?></div>
<div class="fw-semibold">
₱<?= peso($salary_amount) ?> <?= $rateSuffix ?>
</div>
</div>
</div>

<div class="line"></div>

<div class="row">

<div class="col-md-6">
<table class="table table-sm">
<tr><td>Days Worked</td><td class="text-end"><?= $row['days_worked'] ?></td></tr>
<tr><td>OT Hours</td><td class="text-end"><?= $row['overtime_hours'] ?></td></tr>
<tr><td>OT Rate</td><td class="text-end">₱<?= peso($row['overtime_rate']) ?></td></tr>
<tr><td>Allowances</td><td class="text-end">₱<?= peso($row['allowances']) ?></td></tr>
</table>
</div>

<div class="col-md-6">
<table class="table table-sm">

<tr>
<td>Gross Pay</td>
<td class="text-end">₱<?= peso($gross) ?></td>
</tr>

<?php if($sss>0): ?>
<tr><td>SSS</td><td class="text-end">₱<?= peso($sss) ?></td></tr>
<?php endif; ?>

<?php if($philhealth>0): ?>
<tr><td>PhilHealth</td><td class="text-end">₱<?= peso($philhealth) ?></td></tr>
<?php endif; ?>

<?php if($pagibig>0): ?>
<tr><td>Pag-IBIG</td><td class="text-end">₱<?= peso($pagibig) ?></td></tr>
<?php endif; ?>

<?php if($other>0): ?>
<tr><td>Other</td><td class="text-end">₱<?= peso($other) ?></td></tr>
<?php endif; ?>

<tr>
<td>Total Deductions</td>
<td class="text-end">₱<?= peso($total_ded) ?></td>
</tr>

<tr class="net-row">
<td>Net Pay</td>
<td class="text-end">₱<?= peso($net) ?></td>
</tr>

</table>
</div>

</div>

</div>

</div>

</body>
</html>