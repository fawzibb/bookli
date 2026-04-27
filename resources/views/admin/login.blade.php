<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<style>
body{font-family:Arial;background:#f3f4f6;display:flex;justify-content:center;align-items:center;height:100vh}
.box{background:#fff;padding:30px;border-radius:14px;width:400px}
input,button{width:100%;padding:10px;margin-top:10px}
button{background:#111827;color:#fff;border:none}
.error{background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px}
</style>
</head>
<body>

<div class="box">
<h1>Admin Login</h1>

@if($errors->any())
<div class="error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="/admin/login">
@csrf
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
</div>

</body>
</html>