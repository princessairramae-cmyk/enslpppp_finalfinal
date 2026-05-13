<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
<title>RFID Attendance</title>

<style>
body{
    text-align:center;
    font-family:Arial;
    background:#f4f6f9;
    margin-top:100px;
    transition:0.3s;
}
h1{
    font-size:40px;
}
#msg{
    font-size:30px;
    margin-top:20px;
}
#rfid {
    opacity: 0;
    position: absolute;
}
</style>
</head>

<body>

<h1>RFID Attendance</h1>
<h2 id="msg">Scan RFID...</h2>
<img id="photo" src="assets/images/default.png" width="150" style="margin-top:20px;">

<input type="text" id="rfid" autofocus>

<script>


</script>

</body>
</html>