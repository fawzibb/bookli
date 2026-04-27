<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Business Account</title>
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

        .box{
            width:100%;
            max-width:520px;
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

        input, select, button{
            width:100%;
            padding:12px 14px;
            border:1px solid #e5e7eb;
            border-radius:12px;
            margin-bottom:12px;
            box-sizing:border-box;
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
    </style>
</head>
<body>
<div class="box">
    <h1>{{ __('messages.create_business_account') }}</h1>
    <p>{{ __('messages.signup_subtitle') }}</p>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/signup">
        @csrf

        <input name="business_name" placeholder="{{ __('messages.business_name') }}" required>
        <input name="first_name" placeholder="{{ __('messages.first_name') }}" required>
        <input name="last_name" placeholder="{{ __('messages.last_name') }}" required>
        <input name="phone" placeholder="{{ __('messages.phone_number') }}" required>

        <select name="business_type_id" required>
            <option value="">{{ __('messages.select_business_type') }}</option>

            @foreach($businessTypes as $type)
                <option value="{{ $type->id }}" {{ old('business_type_id') == $type->id ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>

        <div class="password-box">
            <input type="password" name="password" id="password" placeholder="{{ __('messages.password') }}" required>
            <span class="toggle-password" onclick="togglePassword()">👁</span>
        </div>

        <button type="submit">{{ __('messages.create_account') }}</button>
    </form>

    <div class="footer">
        {{ __('messages.already_have_account') }}
        <a href="{{ route('login') }}">{{ __('messages.login') }}</a>
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