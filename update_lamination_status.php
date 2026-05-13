<?php

require_once 'config.php';

$id = $_GET['id'] ?? 0;

$status = $_GET['status'] ?? '';

$allowed = [
'Pending',
'On Process',
'Completed'
];

if(!in_array($status, $allowed)){

die("Invalid status");

}

$stmt = $conn->prepare("
UPDATE lamination_jobs
SET status=?
WHERE id=?
");

$stmt->execute([
$status,
$id
]);

header("Location: lamination.php");
exit;