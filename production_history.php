<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once 'config.php';
require_once 'access_control.php';
check_access(['admin','production','accounting', 'engineer']);

$q = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$params = [];

/* =======================
   STATS (FROM work_orders)
======================= */
$total = (int)$conn->query("SELECT COUNT(*) FROM work_orders")->fetchColumn();
$success = (int)$conn->query("SELECT COUNT(*) FROM work_orders WHERE status='Completed'")->fetchColumn();
$failed  = (int)$conn->query("SELECT COUNT(*) FROM work_orders WHERE status='Failed'")->fetchColumn();
$yield = ($total > 0) ? round(($success / $total) * 100) : 0;


/* =======================
   MAIN QUERY (FIXED 🔥)
======================= */
$sql = "
SELECT 
    id,
    wo_no,
    product_name,
    status,
    remarks,
    date_completed AS date_finished
FROM work_orders
WHERE status IN ('Completed','Failed')
";

/* SEARCH */
if ($q !== '') {
    $sql .= " AND (wo_no LIKE ? OR product_name LIKE ? OR remarks LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

/* STATUS FILTER */
if ($statusFilter !== '') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY date_completed DESC";


$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* FUNCTIONS */
function niceDate($dt){
    if(!$dt) return '-';
    return date("M d, Y", strtotime($dt));
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Production History</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{background:#f4f6f9;}
.main-content{margin-left:260px;padding:25px;margin-top:75px;}
.stat-box{background:white;padding:15px;border-radius:8px;}

.stat-card{
    border:none;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    transition:0.2s;
    background:#fff;
}

.stat-card:hover{
    transform:translateY(-2px);
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>

<div class="main-content">

<div class="card mb-3">
    <div class="card-body">
        <h3 class="mb-0">Production History</h3>
    </div>
</div>

<div class="row g-3 mb-3">

    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <small>Total Jobs</small>
                <h4><?= $total ?></h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <small>Success</small>
                <h4 class="text-success"><?= $success ?></h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <small>Failed</small>
                <h4 class="text-danger"><?= $failed ?></h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <small>Yield</small>
                <h4><?= number_format($yield, 0) ?>%</h4>
            </div>
        </div>
    </div>

</div>

<form method="GET" class="row mb-3">
<div class="col-md-6">
<input type="text" name="q" class="form-control"
placeholder="Search WO, product, remarks..."
value="<?= htmlspecialchars($q) ?>">
</div>

<div class="col-md-3">
<select name="status" class="form-control">
<option value="">All Status</option>
<option value="Completed" <?= $statusFilter=='Completed'?'selected':'' ?>>Completed</option>
<option value="Failed" <?= $statusFilter=='Failed'?'selected':'' ?>>Failed</option>
</select>
</div>

<div class="col-md-3">
<button class="btn btn-primary w-100">Filter</button>
</div>
</form>


<div class="card">
<div class="card-body table-responsive">

<table class="table table-bordered">
<thead>
<tr>
<th>ID</th>
<th>Product</th>
<th>Status</th>
<th>Date Finished</th>
<th>Remarks</th>
</tr>
</thead>

<tbody>

<?php if(!$rows): ?>
<tr>
<td colspan="5" class="text-center">No records found</td>
</tr>
<?php else: ?>

<?php foreach($rows as $r): ?>
<tr>
<td>#<?= $r['id'] ?></td>

<td>
<strong><?= htmlspecialchars($r['product_name']) ?></strong><br>
<small><?= htmlspecialchars($r['wo_no']) ?></small>
</td>

<td><?= $r['status'] ?></td>

<td><?= niceDate($r['date_finished']) ?></td>

<td><?= $r['remarks'] ?? 'No remarks' ?></td>
</tr>
<?php endforeach; ?>

<?php endif; ?>

</tbody>
</table>

</div>
</div>

</div>

</body>
</html>