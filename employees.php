<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'config.php'; 
require_once 'access_control.php';
check_access(['admin']);

// TOTAL EMPLOYEES (with filter)
$q = trim($_GET['q'] ?? '');
$dept = $_GET['dept'] ?? '';
$count_sql = "SELECT COUNT(*) as total FROM employees WHERE 1";

if(!empty($q)){
    $count_sql .= " AND full_name LIKE :q ";
}

if(!empty($dept)){
    $count_sql .= " AND department = :dept ";
}

$count_stmt = $conn->prepare($count_sql);

if(!empty($q)){
    $count_stmt->bindValue(':q', "%$q%");
}

if(!empty($dept)){
    $count_stmt->bindParam(':dept', $dept);
}

$count_stmt->execute();
$totalEmployees = $count_stmt->fetch()['total'];

$q = trim($_GET['q'] ?? '');
$dept = $_GET['dept'] ?? '';
$search = "%".$q."%";

function money2($n){ return number_format((float)$n, 2); }

/* ADD EMPLOYEE */
if (isset($_POST['add_employee'])) {

    $name=$_POST['full_name'];
    $rfid = $_POST['rfid_uid'];
    $pos=$_POST['position'];
    $dept=$_POST['department'];
    $status=$_POST['employment_status'];
    $date=$_POST['date_hired'];
    $type=$_POST['salary_type'];
    $salary=$_POST['salary_amount'];
    $con = $_POST['contact_no'];
    
    // 🔥 IMAGE UPLOAD
    $photo = "default.png";
    
    if(!empty($_FILES['photo']['name'])){
        $filename = $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "assets/images/" . $filename);
        $photo = $filename;
    }
    
    if (!preg_match('/^[0-9]{11}$/', $con)) {
        die("Contact number must be exactly 11 digits.");
    }
    
    $stmt=$conn->prepare("INSERT INTO employees
    (full_name,rfid_uid, position,department,employment_status,date_hired,salary_type,salary_amount,contact_no,photo)
    VALUES (?,?,?,?,?,?,?,?,?,?)");
    
    $stmt->execute([$name,$rfid,$pos,$dept,$status,$date,$type,$salary,$con,$photo]);
    
    header("Location: employees.php");
    exit();
    }

/* UPDATE */
if (isset($_POST['update_employee'])) {

    $id=$_POST['employee_id'];
    $name=$_POST['full_name'];
    $rfid = $_POST['rfid_uid'];
    $pos=$_POST['position'];
    $dept=$_POST['department'];
    $status=$_POST['employment_status'];
    $date=$_POST['date_hired'];
    $type=$_POST['salary_type'];
    $salary=$_POST['salary_amount'];
    $con=$_POST['contact_no'];
    
    /* GET CURRENT PHOTO */
    $getPhoto = $conn->prepare("SELECT photo FROM employees WHERE id=?");
    $getPhoto->execute([$id]);
    $current = $getPhoto->fetch();
    
    $photo = $current['photo'];
    
    /* NEW PHOTO */
    if(!empty($_FILES['photo']['name'])){
    
        $filename = $_FILES['photo']['name'];
    
        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            "assets/images/" . $filename
        );
    
        $photo = $filename;
    }
    
    $stmt=$conn->prepare("
    UPDATE employees
    SET full_name=?,
    rfid_uid=?,
    position=?,
    department=?,
    employment_status=?,
    date_hired=?,
    salary_type=?,
    salary_amount=?,
    contact_no=?,
    photo=?
    WHERE id=?");
    
    $stmt->execute([
    $name,
    $rfid,
    $pos,
    $dept,
    $status,
    $date,
    $type,
    $salary,
    $con,
    $photo,
    $id
    ]);
    
    header("Location: employees.php");
    exit();
    }

/* DELETE */
if(isset($_GET['delete'])){
$id=$_GET['delete'];

$stmt=$conn->prepare("DELETE FROM employees WHERE id=?");
$stmt->execute([$id]);

header("Location: employees.php");
exit();
}

?>
<!DOCTYPE html>
<html>
<head>

<title>Employees</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>

img{
border-radius:50%;
object-fit:cover;
}

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

.table td, .table th{
    vertical-align: middle;
    font-size: 14px;
}

.table th{
    white-space: nowrap;
}

.btn-edit, .btn-delete{
    width:32px;
    height:32px;
    padding:0;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

/* CARD */
.card{
border-radius:8px;
}

/* TABLE */
.table thead{
background:#f0f3f7;
}

/* BUTTON */
.btn-blue{
background:#0d6efd;
color:white;
border:none;
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

<div class="card shadow-sm mb-3">
<div class="card-body d-flex justify-content-between align-items-center">

<h3 class="mb-0">Employees</h3>

</div>
</div>

<div class="card shadow-sm">

<div class="card-header d-flex justify-content-between align-items-center">

<button class="btn btn-blue" data-bs-toggle="modal" data-bs-target="#addModal">
+ Add Employee
</button>

</div>

<div class="card-body">

<div class="row mb-3 align-items-center">

<div class="col-md-8">
<form method="GET" class="d-flex gap-2">

<input type="text" name="q" class="form-control"
placeholder="Search name..."
value="<?= htmlspecialchars($q) ?>">

<select name="dept" class="form-control">
    <option value="">All Departments</option>
    <option value="Accounting" <?= ($dept=='Accounting')?'selected':'' ?>>Accounting</option>
    <option value="Engineering" <?= ($dept=='Engineering')?'selected':'' ?>>Engineering</option>
    <option value="Production" <?= ($dept=='Production')?'selected':'' ?>>Production</option>
    <option value="HR" <?= ($dept=='HR')?'selected':'' ?>>HR</option>
    <option value="Admin" <?= ($dept=='Admin')?'selected':'' ?>>Admin</option>
    <option value="Medical" <?= ($dept=='Medical')?'selected':'' ?>>Medical</option>
</select>

<button class="btn btn-primary">Filter</button>

</form>
</div>

<div class="col-md-4 text-end">

<div class="bg-light p-2 rounded shadow-sm d-inline-block">
<small class="text-muted">Total Employees</small><br>
<strong style="font-size:18px;">
<?=$totalEmployees?>
</strong>
</div>

</div>

</div>

<div class="table-responsive">

<table class="table table-bordered">

<thead>
<tr>
<th>Photo</th>
<th>RFID</th>
<th>Name</th>
<th>Position</th>
<th>Department</th>
<th>Status</th>
<th>Date Hired</th>
<th>Salary Type</th>
<th>Salary</th>
<th>Contact</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php
$sql = "SELECT * FROM employees WHERE 1";

if(!empty($q)){
    $sql .= " AND full_name LIKE :q ";
}

if(!empty($dept)){
    $sql .= " AND department = :dept ";
}



$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if(!empty($q)){
    $search = "%".$q."%";
    $stmt->bindParam(':q', $search);
}

if(!empty($dept)){
    $stmt->bindParam(':dept', $dept);
}

$stmt->execute();

while($row=$stmt->fetch()){
?>

<tr>

<td>
<img src="assets/images/<?=$row['photo'] ?: 'default.png'?>" 
width="45" height="45" 
style="border-radius:50%; object-fit:cover;">
</td>
<td>
<span class="badge bg-dark">
<?=$row['rfid_uid'] ?: 'N/A'?>
</span>
</td>

<td><?=$row['full_name']?></td>
<td><?=$row['position']?></td>
<td><?=$row['department']?></td>
<td><?=$row['employment_status']?></td>
<td><?=$row['date_hired']?></td>
<td><?=$row['salary_type']?></td>
<td>₱ <?=money2($row['salary_amount'])?></td>
<td><?=$row['contact_no']?></td>

<td class="text-center">

<button
type="button"
class="btn btn-edit btn-sm me-1"
data-bs-toggle="modal"
data-bs-target="#editModal"
data-id="<?=$row['id']?>"
data-name="<?=$row['full_name']?>"
data-rfid="<?=$row['rfid_uid']?>"
data-position="<?=$row['position']?>"
data-dept="<?=$row['department']?>"
data-status="<?=$row['employment_status']?>"
data-date="<?=$row['date_hired']?>"
data-type="<?=$row['salary_type']?>"
data-salary="<?=$row['salary_amount']?>"
data-contact="<?=$row['contact_no']?>"
data-photo="<?=$row['photo']?>"
>

<i class="bi bi-pencil"></i>
</button>

<a href="?delete=<?=$row['id']?>"
onclick="return confirm('Delete employee?')"
class="btn btn-delete btn-sm"
title="Delete"
>
<i class="bi bi-trash"></i>
</a>

</td>

</tr>


<!-- EDIT MODAL -->



</div>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>


<!-- ADD MODAL -->

<div class="modal fade" id="addModal">

<div class="modal-dialog">

<form method="POST" enctype="multipart/form-data" class="modal-content">

<div class="modal-header">

<h5>Add Employee</h5>

<button type="button" class="btn-close" data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="mb-3">
<label>Name</label>
<input type="text" name="full_name" class="form-control" required>
</div>

<div class="mb-3">
<label>Photo</label>
<input type="file" name="photo" class="form-control">
</div>

<div class="mb-3">
<label>RFID Card UID</label>
<input type="text" name="rfid_uid" class="form-control" placeholder="Scan RFID here">
</div>

<div class="mb-3">
<label>Position</label>
<select name="position" id="position" class="form-control" required onchange="updateDepartment()">
    <option value="">Select Position</option>
    <option value="Production Operator">Production Operator</option>
    <option value="Accountant">Accountant</option>
    <option value="Staff">Staff</option>
    <option value="Engineer">Engineer</option>
    <option value="Nurse">Nurse</option>
</select>
</div>

<div class="mb-3">
<label>Department</label>
<select name="department" id="department" class="form-control" required>
    <option value="">Select Department</option>
</select>
</div>

<div class="mb-3">
<label>Status</label>
<select name="employment_status" class="form-control">
<option>Regular</option>
<option>Probationary</option>
<option>Contractual</option>
<option>Part-time</option>
</select>
</div>

<div class="mb-3">
<label>Date Hired</label>
<input type="date" name="date_hired" class="form-control">
</div>

<div class="mb-3">
<label>Salary Type</label>
<select name="salary_type" id="salary_type" class="form-control">
<option>Daily</option>
<option>Monthly</option>
</select>
</div>

<div class="mb-3">
<label>Salary Amount</label>
<input type="number" name="salary_amount" id="salary_amount" class="form-control">
</div>

<div class="mb-3">
<label>Contact</label>
<input type="text" name="contact_no" class="form-control" maxlength="11" pattern="[0-9]{11}" required>
</div>

</div>

<div class="modal-footer">

<button type="submit" name="add_employee" class="btn btn-primary">
Save Employee
</button>

</div>

</form>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="modal fade" id="editModal">
<div class="modal-dialog">

<form method="POST" action="employees.php" enctype="multipart/form-data" class="modal-content">

<input type="hidden" name="employee_id" id="edit_id">

<div class="modal-header">
<h5>Edit Employee</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="text-center mb-3">
    <img id="edit_preview"
    src="assets/images/default.png"
    width="90"
    height="90"
    style="border-radius:50%; object-fit:cover;">
</div>

<input type="text" name="full_name" id="edit_name" class="form-control mb-2">

<label class="mb-1">Change Photo</label>
<input type="file" name="photo" class="form-control mb-2">
<input type="text" name="rfid_uid" id="edit_rfid" class="form-control mb-2" placeholder="RFID">
<input type="text" name="position" id="edit_position" class="form-control mb-2">
<input type="text" name="department" id="edit_dept" class="form-control mb-2">

<select name="employment_status" id="edit_status" class="form-control mb-2">
<option>Regular</option>
<option>Probationary</option>
<option>Contractual</option>
<option>Part-time</option>
</select>

<input type="date" name="date_hired" id="edit_date" class="form-control mb-2">

<select name="salary_type" id="edit_type" class="form-control mb-2">
<option>Daily</option>
<option>Monthly</option>
</select>

<input type="number" name="salary_amount" id="edit_salary" class="form-control mb-2">
<input type="text" name="contact_no" id="edit_contact" class="form-control mb-2" maxlength="11" pattern="[0-9]{11}" required>

</div>

<div class="modal-footer">
<button type="submit" name="update_employee" class="btn btn-primary">Update</button>
</div>

</form>

</div>
</div>

<script>
function updateDepartment() {
    const position = document.getElementById("position").value;
    const department = document.getElementById("department");

    let options = [];

    if (position === "Production Operator") {
        options = ["Production"];
    } 
    else if (position === "Accountant") {
        options = ["Accounting"];
    } 
    else if (position === "Staff") {
        options = ["HR", "Admin"];
    } 
    else if (position === "Engineer") {
        options = ["Engineering", "Production"];
    } 
    else if (position === "Nurse") {
        options = ["Medical", "HR"];
    }

    department.innerHTML = '<option value="">Select Department</option>';

    options.forEach(function(dep) {
        department.innerHTML += `<option value="${dep}">${dep}</option>`;
    });
}
</script>

<script>
const editModal = document.getElementById('editModal');

editModal.addEventListener('show.bs.modal', function(event){

let btn = event.relatedTarget;

document.getElementById('edit_id').value = btn.dataset.id;
document.getElementById('edit_name').value = btn.dataset.name;
document.getElementById('edit_rfid').value = btn.dataset.rfid;
document.getElementById('edit_position').value = btn.dataset.position;
document.getElementById('edit_dept').value = btn.dataset.dept;
document.getElementById('edit_status').value = btn.dataset.status;
document.getElementById('edit_date').value = btn.dataset.date;
document.getElementById('edit_type').value = btn.dataset.type;
document.getElementById('edit_salary').value = btn.dataset.salary;
document.getElementById('edit_contact').value = btn.dataset.contact;

document.getElementById('edit_preview').src =
"assets/images/" + btn.dataset.photo;

});
</script>

<?php include 'footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function(){

    const salaryType = document.getElementById("salary_type");
    const salaryAmount = document.getElementById("salary_amount");

    salaryType.addEventListener("change", function(){

        if(salaryType.value === "Monthly"){
            salaryAmount.value = 30000;
        }
        else if(salaryType.value === "Daily"){
            salaryAmount.value = 20000;
        }

    });

});
</script>
</body>
</html>
```