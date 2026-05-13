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

h1{ font-size:40px; }

#msg{
    font-size:30px;
    margin-top:20px;
}

/* hidden input but working */
#rfid{
    position:absolute;
    opacity:0;
}
</style>

</head>
<body>

<h1>RFID Attendance</h1>
<h2 id="msg">Scan RFID...</h2>

<input type="text" id="rfid">

<script>

let input = document.getElementById("rfid");
let msg = document.getElementById("msg");

// 🔥 ALWAYS FOCUS (MAC FIX)
setInterval(() => {
    input.focus();
}, 500);

// MAIN SCAN EVENT
input.addEventListener("input", function(){

    let rfid = this.value.trim();

    if(rfid.length >= 5){

        fetch("rfid_scan.php", {
            method: "POST",
            body: new URLSearchParams({rfid: rfid})
        })
        .then(res => res.json())
        .then(data => {

if(data.photo){
    document.getElementById("photo").src = data.photo;
}

if(data.status === "time_in"){
    document.getElementById("msg").innerText = "✅ TIME IN: " + data.name;
    document.body.style.background = "#d4edda";
}
else if(data.status === "time_out"){
    document.getElementById("msg").innerText = "⏰ TIME OUT: " + data.name;
    document.body.style.background = "#fff3cd";
}
else if(data.status === "invalid"){
    document.getElementById("msg").innerText = "❌ Unknown Card";
    document.body.style.background = "#f8d7da";
}
else{
    document.getElementById("msg").innerText = "✔ Already Recorded";
    document.body.style.background = "#d1ecf1";
}

})
        .catch(err => {
            msg.innerText = "⚠ Error";
            console.log(err);
        });

        this.value = "";
    }

});

</script>

</body>
</html>