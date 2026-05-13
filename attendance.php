<?php
session_start();
include 'config.php';
require_once 'access_control.php';
check_access(['admin']);

/* SAVE */
if(isset($_POST['save_attendance'])){

$emp=$_POST['employee_id'];
$date=$_POST['att_date'];
$in=$_POST['time_in'];
$out=$_POST['time_out'];
$status=$_POST['status'];
$remarks=$_POST['remarks'];

if($out < $in){
    $_SESSION['error']="Time out cannot be earlier than time in";
    header("Location: attendance.php");
    exit();
}

$stmt=$conn->prepare("INSERT INTO attendance
(employee_id,att_date,time_in,time_out,status,remarks)
VALUES (?,?,?,?,?,?)");

$stmt->execute([$emp,$date,$in,$out,$status,$remarks]);

header("Location: attendance.php");
exit();
}

/* UPDATE */
if(isset($_POST['update_attendance'])){

$id=$_POST['id'];
$emp=$_POST['employee_id'];
$date=$_POST['att_date'];
$in=$_POST['time_in'];
$out=$_POST['time_out'];
$status=$_POST['status'];
$remarks=$_POST['remarks'];

if($out < $in){
    $_SESSION['error']="Time out cannot be earlier than time in";
    header("Location: attendance.php");
    exit();
}

$stmt=$conn->prepare("
UPDATE attendance SET
employee_id=?,
att_date=?,
time_in=?,
time_out=?,
status=?,
remarks=?
WHERE id=?
");

$stmt->execute([$emp,$date,$in,$out,$status,$remarks,$id]);

header("Location: attendance.php");
exit();
}

/* DELETE */
if(isset($_GET['delete'])){
$id=$_GET['delete'];

$stmt=$conn->prepare("DELETE FROM attendance WHERE id=?");
$stmt->execute([$id]);

header("Location: attendance.php");
exit();
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Attendance</title>

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
.btn-edit{
background:#0f766e;
color:white;
border:none;
}
.btn-delete{
background:#dc3545;
color:white;
border:none;
}
</style>

</head>

<body>



<?php include 'sidebar.php'; ?>
<?php include 'header.php'; ?>

<div class="main-content">
<div class="container-fluid">

<?php
if(isset($_SESSION['error'])){
echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
unset($_SESSION['error']);
}
?>

<?php
// 🔥 TODAY STATS
$today = date("Y-m-d");

$stmt = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE att_date=?");
$stmt->execute([$today]);
$totalToday = $stmt->fetchColumn();
?>

<!-- 🔥 HEADER DASHBOARD -->
<div class="card shadow-sm mb-4">
<div class="card-body text-center">

<h2 class="fw-bold">Attendance System</h2>
<p class="text-muted">RFID Card Attendance</p>

<div class="row mt-4">

<div class="col-md-6">
<div class="border rounded p-3">
<h6>Date</h6>
<strong><?= date("l, F d, Y") ?></strong>
</div>
</div>

<div class="col-md-6">
<div class="border rounded p-3">
<h6>Total Attendance Today</h6>
<strong><?= $totalToday ?> Person</strong>
</div>
</div>

</div>

</div>
</div>

<!-- 🔥 ACTION BAR -->
<div class="card shadow-sm mb-3">
<div class="card-body d-flex justify-content-between align-items-center">

<h5 class="mb-0">RFID Attendance</h5>

<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
+ Add Attendance
</button>

</div>
</div>

<!-- 🔥 FILTER -->
<div class="card shadow-sm mb-3">
<div class="card-body">

<form method="GET" class="row">

<div class="col-md-3">
<label>From Date</label>
<input type="date" name="from" class="form-control"
value="<?= $_GET['from'] ?? '' ?>">
</div>

<div class="col-md-3">
<label>To Date</label>
<input type="date" name="to" class="form-control"
value="<?= $_GET['to'] ?? '' ?>">
</div>

<div class="col-md-3 d-flex align-items-end">
<button class="btn btn-primary">Filter</button>
<a href="attendance.php" class="btn btn-secondary ms-2">Reset</a>
</div>

</form>

</div>
</div>

<!-- 🔥 TABLE -->
<div class="card shadow-sm">
<div class="card-header bg-primary text-white">
<h5 class="mb-0">Today Attendance</h5>
</div>

<div class="card-body table-responsive">

<table class="table table-hover align-middle">

<thead>
<tr>
<th>Employee</th>
<th>Date</th>
<th>Time In</th>
<th>Time Out</th>
<th>Status</th>
<th>Remarks</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php

$today = date("Y-m-d");

$sql = "
SELECT a.*,e.full_name
FROM attendance a
JOIN employees e ON e.id=a.employee_id
WHERE a.att_date = ?
";

$params = [$today];

if(!empty($_GET['from']) && !empty($_GET['to'])){
    $sql = "
    SELECT a.*,e.full_name
    FROM attendance a
    JOIN employees e ON e.id=a.employee_id
    WHERE a.att_date BETWEEN ? AND ?
    ";
    $params = [$_GET['from'], $_GET['to']];
}

$sql .= " ORDER BY a.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

while($row=$stmt->fetch()){
?>

<tr>
<td class="fw-semibold"><?=$row['full_name']?></td>
<td><?=$row['att_date']?></td>

<td>
<span class="badge bg-success">
<?=$row['time_in']?>
</span>
</td>

<td>
<?php if($row['time_out']){ ?>
<span class="badge bg-dark">
<?=$row['time_out']?>
</span>
<?php } else { ?>
<span class="badge bg-secondary">--</span>
<?php } ?>
</td>

<td>
<?php
$status = $row['status'];

if($status == "Present"){
echo "<span class='badge bg-success'>Present</span>";
}elseif($status == "Absent"){
echo "<span class='badge bg-danger'>Absent</span>";
}else{
echo "<span class='badge bg-warning text-dark'>On Leave</span>";
}
?>
</td>

<td><?=$row['remarks']?></td>

<td>

<button 
class="btn btn-sm btn-warning editBtn"
data-id="<?=$row['id']?>"
data-emp="<?=$row['employee_id']?>"
data-date="<?=$row['att_date']?>"
data-in="<?=$row['time_in']?>"
data-out="<?=$row['time_out']?>"
data-status="<?=$row['status']?>"
data-remarks="<?=$row['remarks']?>"
data-bs-toggle="modal"
data-bs-target="#editModal">
Edit
</button>

<a href="?delete=<?=$row['id']?>" 
onclick="return confirm('Delete attendance?')" 
class="btn btn-sm btn-danger">
Delete
</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>
</div>

</div>
</div>
<!-- ADD MODAL -->
<div class="modal fade" id="addModal">
<div class="modal-dialog">
<form method="POST" class="modal-content">

<div class="modal-header">
<h5>Add Attendance</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="mb-3">
<label>Employee</label>
<select name="employee_id" class="form-control">
<?php
$emp=$conn->query("SELECT * FROM employees");
while($e=$emp->fetch()){
?>
<option value="<?=$e['id']?>"><?=$e['full_name']?></option>
<?php } ?>
</select>
</div>

<div class="mb-3">
<label>Date</label>
<input type="date" name="att_date" class="form-control" required>
</div>

<div class="mb-3">
<label>Time In</label>
<input type="time" name="time_in" class="form-control" required>
</div>

<div class="mb-3">
<label>Time Out</label>
<input type="time" name="time_out" class="form-control" required>
</div>

<div class="mb-3">
<label>Status</label>
<select name="status" class="form-control">
<option>Present</option>
<option>Absent</option>
<option>On Leave</option>
</select>
</div>

<div class="mb-3">
<label>Remarks</label>
<input type="text" name="remarks" class="form-control">
</div>

</div>

<div class="modal-footer">
<button type="submit" name="save_attendance" class="btn btn-primary">
Save
</button>
</div>

</form>
</div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal">
<div class="modal-dialog">
<form method="POST" class="modal-content">

<div class="modal-header">
<h5>Edit Attendance</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" name="id" id="edit_id">

<div class="mb-3">
<label>Employee</label>
<select name="employee_id" id="edit_emp" class="form-control">
<?php
$emp=$conn->query("SELECT * FROM employees");
while($e=$emp->fetch()){
?>
<option value="<?=$e['id']?>"><?=$e['full_name']?></option>
<?php } ?>
</select>
</div>

<div class="mb-3">
<label>Date</label>
<input type="date" name="att_date" id="edit_date" class="form-control">
</div>

<div class="mb-3">
<label>Time In</label>
<input type="time" name="time_in" id="edit_in" class="form-control">
</div>

<div class="mb-3">
<label>Time Out</label>
<input type="time" name="time_out" id="edit_out" class="form-control">
</div>

<div class="mb-3">
<label>Status</label>
<select name="status" id="edit_status" class="form-control">
<option>Present</option>
<option>Absent</option>
<option>On Leave</option>
</select>
</div>

<div class="mb-3">
<label>Remarks</label>
<input type="text" name="remarks" id="edit_remarks" class="form-control">
</div>

</div>

<div class="modal-footer">
<button type="submit" name="update_attendance" class="btn btn-success">
Update
</button>
</div>

</form>
</div>
</div>

<!-- JS -->
<script>
document.querySelectorAll('.editBtn').forEach(button=>{
button.addEventListener('click',function(){

document.getElementById('edit_id').value=this.dataset.id;
document.getElementById('edit_emp').value=this.dataset.emp;
document.getElementById('edit_date').value=this.dataset.date;
document.getElementById('edit_in').value=this.dataset.in.slice(0,5);
document.getElementById('edit_out').value=this.dataset.out.slice(0,5);
document.getElementById('edit_status').value=this.dataset.status;
document.getElementById('edit_remarks').value=this.dataset.remarks;

});
});




</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'footer.php'; ?>

<script>

document.addEventListener("DOMContentLoaded", function(){

    const input = document.getElementById("rfid_input");

    const modalEl = document.getElementById("rfidModal");

    const modal = new bootstrap.Modal(modalEl);

    // AUTO SHOW
    modal.show();

    // AUTO FOCUS
    function focusRFID(){
        input.focus();
    }

    setInterval(focusRFID, 300);

    document.addEventListener("click", focusRFID);

    let scanTimer;

    // AUTO DETECT RFID
    input.addEventListener("input", function(){

        clearTimeout(scanTimer);

        scanTimer = setTimeout(() => {

            let rfid = input.value.trim();

            console.log("RFID:", rfid);

            if(rfid.length < 3){
                input.value = "";
                return;
            }

            fetch("rfid_scan.php", {

                method: "POST",

                headers:{
                    "Content-Type":"application/x-www-form-urlencoded"
                },

                body:"rfid=" + encodeURIComponent(rfid)

            })

            .then(res => res.json())

            .then(data => {

                console.log(data);

                const msg = document.getElementById("rfid_msg");

                const photo = document.getElementById("rfid_photo");

                if(data.status === "time_in"){

                    msg.innerHTML = "✅ TIME IN<br>" + data.name;

                }
                else if(data.status === "time_out"){

                    msg.innerHTML = "⏰ TIME OUT<br>" + data.name;

                }
                else if(data.status === "invalid"){

                    msg.innerHTML = "❌ UNKNOWN CARD";

                }
                else if(data.status === "already"){

                    msg.innerHTML = "✔ ALREADY RECORDED";

                }
                else{

                    msg.innerHTML = "⚠ ERROR";

                }

                photo.src = data.photo;

                modal.show();

                input.value = "";

                setTimeout(() => {

                    location.reload();

                }, 1500);

            })

            .catch(err => {

                console.log(err);

                alert("RFID FETCH ERROR");

            });

        }, 300);

    });

});

</script>

<div class="modal fade" id="rfidModal">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content text-center p-4">




<h4>RFID Scanner</h4>

<h5 id="rfid_msg">Scan RFID...</h5>

<img id="rfid_photo" src="assets/images/default.png" width="120" style="border-radius:50%; margin:15px auto;">



</div>
</div>
</div>


<input 
type="text" 
id="rfid_input"
autocomplete="off"
style="
position:fixed;
top:-100px;
left:-100px;
opacity:0;
z-index:-1;
"
>
</body>
</html>