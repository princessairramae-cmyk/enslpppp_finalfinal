<?php
require_once "config.php";

$id = $_GET['id'];
$status = $_GET['status'];

$stmt = $conn->prepare("
UPDATE cutting_jobs 
SET status=? 
WHERE id=?
");

$stmt->execute([$status, $id]);

header("Location: cutting.php");
exit;