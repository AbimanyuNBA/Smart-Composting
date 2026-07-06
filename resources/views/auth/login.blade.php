<!DOCTYPE html>

<html>

<head>

<title>Login Smart Composting</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<style>

body{

background:#eef5f3;

height:100vh;

display:flex;

align-items:center;

justify-content:center;

font-family:Arial;

}


.login-card{

width:400px;

background:white;

padding:35px;

border-radius:25px;

box-shadow:0 15px 40px #0002;

}


.btn-login{

background:#10b981;

color:white;

font-weight:bold;

border-radius:12px;

}

</style>


</head>


<body>



<div class="login-card">


<h3 class="fw-bold mb-1">
🌱 Smart Composting
</h3>

<p class="text-muted mb-4">
IoT & AI System Login
</p>




@if(session('error'))

<div class="alert alert-danger">

{{ session('error') }}

</div>

@endif




<form method="POST" action="/login">


@csrf



<label>Username</label>

<input 
type="text"
name="username"
class="form-control mb-3"
placeholder="admin">





<label>Password</label>

<input
type="password"
name="password"
class="form-control mb-4"
placeholder="password">




<button class="btn btn-login w-100">

Login

</button>



</form>


</div>


</body>

</html>