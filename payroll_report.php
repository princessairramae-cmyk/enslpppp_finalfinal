<?php
include 'config.php';

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$params = [];

$sql = "
SELECT pr.*, e.full_name, e.department
FROM payroll pr
JOIN employees e ON e.id = pr.employee_id
WHERE 1
";

if($from && $to){
    $sql .= " AND pr.period_start >= ? AND pr.period_end <= ?";
    $params[] = $from;
    $params[] = $to;
}

$sql .= " ORDER BY pr.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

// 🔥 TOTALS
$totalGross = 0;
$totalNet   = 0;
$totalDed   = 0;

foreach($data as $row){
    $totalGross += $row['gross_pay'];
    $totalNet   += $row['net_pay'];

    $ded = ($row['sss'] + $row['philhealth'] + $row['pagibig'] + $row['other_deductions']);
    $totalDed += $ded;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Payroll Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
background:#f4f6f9;
font-family:Arial;
}
.main{
padding:30px;
}
.card{
border-radius:8px;
}
</style>

</head>

<body>

<div class="main container-fluid">

<h2 class="mb-3">📊 General Payroll Report</h2>

<!-- 🔥 FILTER -->
<div class="card mb-3 shadow-sm">
<div class="card-body">

<form method="GET" class="row">

<div class="col-md-3">
<label>From</label>
<input type="date" name="from" class="form-control" value="<?= $from ?>">
</div>

<div class="col-md-3">
<label>To</label>
<input type="date" name="to" class="form-control" value="<?= $to ?>">
</div>

<div class="col-md-3 d-flex align-items-end">
<button class="btn btn-primary">Generate</button>
</div>

</form>

</div>
</div>

<!-- 🔥 SUMMARY -->
<div class="row mb-3">

<div class="col-md-4">
<div class="card shadow-sm text-center p-3">
<h6>Total Gross</h6>
<h4>₱ <?= number_format($totalGross,2) ?></h4>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm text-center p-3">
<h6>Total Deductions</h6>
<h4>₱ <?= number_format($totalDed,2) ?></h4>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm text-center p-3">
<h6>Total Net Pay</h6>
<h4>₱ <?= number_format($totalNet,2) ?></h4>
</div>
</div>

</div>

<!-- 🔥 TABLE -->
<div class="card shadow-sm">
<div class="card-body table-responsive">

<table class="table table-bordered">

<thead>
<tr>
<th>Employee</th>
<th>Department</th>
<th>Cutoff</th>
<th>Gross</th>
<th>Deductions</th>
<th>Net</th>
</tr>
</thead>

<tbody>

<?php foreach($data as $row): 
$ded = ($row['sss'] + $row['philhealth'] + $row['pagibig'] + $row['other_deductions']);
?>

<tr>

<td><?= $row['full_name'] ?></td>
<td><?= $row['department'] ?></td>
<td><?= $row['period_start']?> - <?= $row['period_end']?></td>

<td>₱ <?= number_format($row['gross_pay'],2) ?></td>
<td>₱ <?= number_format($ded,2) ?></td>
<td><b>₱ <?= number_format($row['net_pay'],2) ?></b></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>
</div>

</div>

</body>
</html>