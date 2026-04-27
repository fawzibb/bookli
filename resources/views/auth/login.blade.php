<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Login</title>
    <style>
        body{
            margin:0;
            font-family:Arial,sans-serif;
            background:#f5f7fb;
            display:flex;
            align-items:center;
            justify-content:center;
            min-height:100vh;
            padding:20px;
        }

        .box{
            width:100%;
            max-width:420px;
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:20px;
            padding:28px;
            box-shadow:0 10px 30px rgba(15,23,42,.06);
        }

        h1{
            margin:0 0 8px;
        }

        p{
            color:#6b7280;
            margin:0 0 20px;
        }

        input, button{
            width:100%;
            padding:12px 14px;
            border:1px solid #e5e7eb;
            border-radius:12px;
            margin-bottom:12px;
            box-sizing:border-box;
            font-size:15px;
        }

        button{
            background:#111827;
            color:#fff;
            border:none;
            cursor:pointer;
            font-weight:600;
        }

        .error{
            background:#fee2e2;
            color:#991b1b;
            padding:12px;
            border-radius:12px;
            margin-bottom:14px;
        }

        .footer{
            margin-top:14px;
            color:#6b7280;
            font-size:14px;
        }

        a{
            color:#111827;
            font-weight:600;
            text-decoration:none;
        }

        .password-box{
            position:relative;
            margin-bottom:12px;
        }

        .password-box input{
            margin-bottom:0;
            padding-right:45px;
        }

        .toggle-password{
            position:absolute;
            right:14px;
            top:50%;
            transform:translateY(-50%);
            cursor:pointer;
            color:#6b7280;
            font-size:18px;
            user-select:none;
        }
    </style>
</head>
<body>
<div class="box">
    <h1>{{ __('messages.welcome_back') }}</h1>
    <p>{{ __('messages.login_manage_business') }}</p>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/login">
        @csrf

        <input name="phone" placeholder="{{ __('messages.phone_number') }}" required>

        <div class="password-box">
            <input type="password" name="password" id="password" placeholder="{{ __('messages.password') }}" required>
            <span class="toggle-password" onclick="togglePassword()">👁</span>
        </div>

        <button type="submit">{{ __('messages.login') }}</button>
    </form>

    <div class="footer">
        {{ __('messages.new_here') }}
        <a href="{{ route('signup') }}">{{ __('messages.create_account') }}</a>
    </div>
</div>

<script>
function togglePassword() {
    const password = document.getElementById('password');

    if (password.type === 'password') {
        password.type = 'text';
    } else {
        password.type = 'password';
    }
}
</script>
</body>
</html>