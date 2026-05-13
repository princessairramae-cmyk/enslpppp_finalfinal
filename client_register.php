<?php
session_start();
include 'config.php';

$message = "";
$success = "";

if(isset($_POST['register'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_raw = $_POST['password'];
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    // validation
    if(empty($name) || empty($email) || empty($password_raw)){

        $message = "Please fill all required fields!";

    } else {

        // check email
        $check = $conn->prepare("SELECT id FROM clients WHERE email=?");
        $check->execute([$email]);

        if($check->rowCount() > 0){

            $message = "Email already exists!";

        } else {

            // hash password
            $password = password_hash($password_raw, PASSWORD_DEFAULT);

            // insert
            $stmt = $conn->prepare("
                INSERT INTO clients(name,email,password,contact_number,address)
                VALUES(?,?,?,?,?)
            ");

            $stmt->execute([
                $name,
                $email,
                $password,
                $contact,
                $address
            ]);

            $success = "Registration successful! You can now login.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Client Sign Up</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    margin:0;
    height:100vh;
    font-family:'Segoe UI', sans-serif;
    background:linear-gradient(135deg, #0a1f44, #0d3b8c);
    display:flex;
    justify-content:center;
    align-items:center;
    overflow:hidden;
}

/* GLOW BG */
body::before{
    content:"";
    position:absolute;
    width:400px;
    height:400px;
    background:rgba(255,255,255,0.03);
    border-radius:50%;
    top:-120px;
    left:-120px;
}

body::after{
    content:"";
    position:absolute;
    width:300px;
    height:300px;
    background:rgba(255,255,255,0.03);
    border-radius:50%;
    bottom:-100px;
    right:-100px;
}

/* CARD */
.form-box{
    width:100%;
    max-width:420px;
    background:white;
    padding:40px;
    border-radius:18px;
    box-shadow:0 15px 40px rgba(0,0,0,0.15);
    position:relative;
    z-index:2;
}

/* LOGO */
.logo{
    text-align:center;
    margin-bottom:15px;
}

.logo img{
    width:85px;
    background:#fff;
    padding:12px;
    border-radius:20px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

/* TITLE */
h2{
    text-align:center;
    color:#0d3b8c;
    font-weight:700;
    margin-bottom:5px;
}

.subtitle{
    text-align:center;
    color:#6b7280;
    font-size:14px;
    margin-bottom:25px;
}

/* INPUT */
.form-control{
    height:48px;
    border-radius:10px;
    margin-bottom:15px;
    border:1px solid #d1d5db;
    font-size:14px;
}

.form-control:focus{
    border-color:#0d3b8c;
    box-shadow:none;
}

/* BUTTON */
.btn-primary{
    background:#0d3b8c;
    border:none;
    height:48px;
    border-radius:10px;
    font-weight:bold;
    margin-top:5px;
}

.btn-primary:hover{
    background:#0a2e6e;
}

/* ALERT */
.alert{
    font-size:14px;
    border-radius:10px;
}

/* LINK */
.link{
    text-align:center;
    margin-top:18px;
    font-size:14px;
}

.link a{
    text-decoration:none;
    color:#0d3b8c;
    font-weight:600;
}

.link a:hover{
    text-decoration:underline;
}

.version{
    text-align:center;
    margin-top:20px;
    font-size:12px;
    color:#9ca3af;
}

</style>
</head>

<body>

<div class="form-box">

    <!-- LOGO -->
    <div class="logo">
        <img src="logo.png">
    </div>

    <!-- TITLE -->
    <h2>Create Account</h2>

    <div class="subtitle">
        Register your client account
    </div>

    <!-- ERROR -->
    <?php if($message): ?>
        <div class="alert alert-danger">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- SUCCESS -->
    <?php if($success): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST">

        <input type="text"
               name="name"
               class="form-control"
               placeholder="Full Name"
               required>

        <input type="email"
               name="email"
               class="form-control"
               placeholder="Email Address"
               required>

        <input type="password"
               name="password"
               class="form-control"
               placeholder="Password"
               required>

        <input type="text"
               name="contact"
               class="form-control"
               placeholder="Contact Number">

        <input type="text"
               name="address"
               class="form-control"
               placeholder="Address">

        <button type="submit"
                name="register"
                class="btn btn-primary w-100">
            CREATE ACCOUNT
        </button>

    </form>

    <!-- LINK -->
    <div class="link">
        Already have an account?
        <a href="client_login.php">Sign In</a>
    </div>

    <div class="version">
        ENSLP CLIENT PORTAL v1.0
    </div>

</div>

</body>
</html>