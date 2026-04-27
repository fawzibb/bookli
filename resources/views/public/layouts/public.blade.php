<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $business->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('public.partials.design-vars')
    @include('public.assets.style')
</head>
<body class="theme-{{ $settings->public_theme ?? 'default' }}">

<div class="wrapper">

    @include('public.partials.owner-cta')
    <div class="language-switcher">
    <a href="{{ route('language.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">
        English
    </a>

    <a href="{{ route('language.switch', 'ar') }}" class="{{ app()->getLocale() === 'ar' ? 'active' : '' }}">
        عربي
    </a>
</div>
    @include('public.partials.business-hero')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    @yield('content')

</div>

@include('public.assets.script')

@stack('scripts')
</body>
</html>