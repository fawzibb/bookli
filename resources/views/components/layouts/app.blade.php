<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $title ?? 'Bookli' }}</title>

<style>
.lang-switch{
    display:flex;
    gap:8px;
    align-items:center;
}

.lang-switch a{
    text-decoration:none;
    padding:8px 10px;
    border-radius:10px;
    background:#f3f4f6;
    color:#111827;
    font-size:13px;
    font-weight:700;
}

html[dir="rtl"] body{
    direction:rtl;
}

html[dir="rtl"] th,
html[dir="rtl"] td{
    text-align:right;
}

html[dir="rtl"] .close-btn{
    float:left;
}

:root{
    --bg:#f5f7fb;
    --card:#ffffff;
    --text:#111827;
    --muted:#6b7280;
    --border:#e5e7eb;
    --primary:#111827;
    --sidebar-bg:#0f172a;
    --sidebar-text:#e5e7eb;
    --sidebar-muted:#94a3b8;
    --sidebar-hover:rgba(255,255,255,.08);
    --sidebar-active:#ffffff;
    --sidebar-active-text:#111827;
}

*{box-sizing:border-box}

body{
    margin:0;
    font-family:Arial,sans-serif;
    background:var(--bg);
    color:var(--text);
}

.layout{
    display:flex;
    min-height:100vh;
}

/* desktop top bar */
.desktop-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:14px 18px;
    background:#fff;
    border-bottom:1px solid var(--border);
    position:sticky;
    top:0;
    z-index:1100;
}

.top-left{
    display:flex;
    align-items:center;
    gap:12px;
}

.menu-btn{
    border:none;
    background:#111827;
    color:#fff;
    font-size:22px;
    width:42px;
    height:42px;
    border-radius:10px;
    cursor:pointer;
}

.top-brand{
    font-size:22px;
    font-weight:bold;
}

/* sidebar */
.sidebar{
    width:270px;
    background:var(--sidebar-bg);
    color:var(--sidebar-text);
    padding:22px;
    flex-shrink:0;
    min-height:calc(100vh - 71px);
    transition:.25s ease;
}

.sidebar.hidden{
    display:none;
}

.brand{
    font-size:28px;
    font-weight:bold;
    margin-bottom:6px;
    color:#fff;
}

.sidebar p{
    color:var(--sidebar-muted);
    margin:0 0 20px;
}

.sidebar .nav-link{
    display:block;
    padding:12px 14px;
    margin-bottom:8px;
    text-decoration:none;
    color:var(--sidebar-text);
    border-radius:12px;
    transition:.2s;
}

.sidebar .nav-link:hover{
    background:var(--sidebar-hover);
}

.sidebar .nav-link.active{
    background:var(--sidebar-active);
    color:var(--sidebar-active-text);
}

.content{
    flex:1;
    padding:24px;
    min-width:0;
}

/* mobile top bar */
.mobile-top{
    display:none;
    align-items:center;
    justify-content:space-between;
    padding:14px 18px;
    background:var(--sidebar-bg);
    color:#fff;
    border-bottom:1px solid rgba(255,255,255,.08);
    position:sticky;
    top:0;
    z-index:1200;
}

/* mobile overlay */
.mobile-overlay{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.35);
    z-index:1999;
}

.mobile-overlay.open{
    display:block;
}

/* mobile drawer */
.mobile-drawer{
    display:none;
    position:fixed;
    top:0;
    left:0;
    right:0;
    background:var(--sidebar-bg);
    color:var(--sidebar-text);
    z-index:2000;
    padding:20px;
    border-bottom-left-radius:18px;
    border-bottom-right-radius:18px;
    box-shadow:0 12px 30px rgba(0,0,0,.18);
}

.mobile-drawer.open{
    display:block;
}

.mobile-drawer .brand{
    color:#fff;
}

.mobile-drawer p{
    color:var(--sidebar-muted);
    margin:0 0 18px;
}

.mobile-drawer .nav-link{
    display:block;
    padding:12px 14px;
    margin-bottom:8px;
    text-decoration:none;
    color:var(--sidebar-text);
    border-radius:12px;
}

.mobile-drawer .nav-link:hover{
    background:var(--sidebar-hover);
}

.mobile-drawer .nav-link.active{
    background:#fff;
    color:#111827;
}

.close-btn{
    float:right;
    border:none;
    background:#ef4444;
    color:#fff;
    width:38px;
    height:38px;
    border-radius:10px;
    cursor:pointer;
    font-size:18px;
}

/* components */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:14px;
    margin-bottom:20px;
    flex-wrap:wrap;
}

.page-title{
    margin:0;
    font-size:30px;
}

.page-subtitle{
    margin:6px 0 0;
    color:var(--muted);
}

.card{
    background:#fff;
    border:1px solid var(--border);
    border-radius:18px;
    padding:22px;
    box-shadow:0 8px 24px rgba(15,23,42,.04);
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
}

.grid-3{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:18px;
}

.stat{
    background:#fff;
    border:1px solid var(--border);
    border-radius:18px;
    padding:18px;
}

.stat-label{
    color:var(--muted);
    font-size:14px;
}

.stat-value{
    font-size:28px;
    font-weight:bold;
    margin-top:8px;
}

.btn,.btn-light,.btn-danger{
    padding:10px 14px;
    border:none;
    border-radius:12px;
    text-decoration:none;
    cursor:pointer;
    font-weight:600;
}

.btn{background:#111827;color:#fff;}
.btn-light{background:#f3f4f6;color:#111827;}
.btn-danger{background:#dc2626;color:#fff;}

.input,.select,.textarea{
    width:100%;
    padding:12px 14px;
    border:1px solid var(--border);
    border-radius:12px;
    background:#fff;
}

.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.table-wrap{
    overflow:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:12px;
    border-bottom:1px solid #eee;
    text-align:left;
}

.badge{
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}

.badge-completed{background:#dcfce7;color:#166534;}
.badge-cancelled{background:#fee2e2;color:#991b1b;}
.badge-pending{background:#fef3c7;color:#92400e;}
.badge-confirmed{background:#dbeafe;color:#1d4ed8;}

/* mobile */
@media (max-width: 900px){
    .desktop-top{
        display:none;
    }

    .sidebar{
        display:none !important;
    }

    .mobile-top{
        display:flex;
    }

    .content{
        padding:18px;
    }

    .page-title{
        font-size:24px;
    }
}
</style>
</head>
<body>

<div class="desktop-top">
    <div class="top-left">
        <button class="menu-btn" onclick="toggleDesktopSidebar()">☰</button>
        <div class="top-brand">Bookli</div>
    </div>

    <div class="lang-switch">
        <a href="{{ route('language.switch', 'en') }}">English</a>
        <a href="{{ route('language.switch', 'ar') }}">العربية</a>
    </div>
</div>

<div class="mobile-top">
    <strong>Bookli</strong>

    <div style="display:flex;align-items:center;gap:8px;">
        <div class="lang-switch">
            <a href="{{ route('language.switch', 'en') }}">EN</a>
            <a href="{{ route('language.switch', 'ar') }}">AR</a>
        </div>

        <button class="menu-btn" onclick="openMenu()">☰</button>
    </div>
</div>

<div id="mobileOverlay" class="mobile-overlay" onclick="closeMenu()"></div>

<div id="mobileDrawer" class="mobile-drawer">
    <button class="close-btn" onclick="closeMenu()">✕</button>
    <div style="clear:both;margin-top:14px;">
        {!! $sidebar ?? '' !!}
    </div>
</div>

<div class="layout">
    <aside id="desktopSidebar" class="sidebar">
        {!! $sidebar ?? '' !!}
    </aside>

    <main class="content">
        {{ $slot }}
    </main>
</div>

<script>
function toggleDesktopSidebar(){
    const sidebar = document.getElementById('desktopSidebar');
    sidebar.classList.toggle('hidden');
}

function openMenu(){
    document.getElementById('mobileDrawer').classList.add('open');
    document.getElementById('mobileOverlay').classList.add('open');
}

function closeMenu(){
    document.getElementById('mobileDrawer').classList.remove('open');
    document.getElementById('mobileOverlay').classList.remove('open');
}
</script>

</body>
</html>