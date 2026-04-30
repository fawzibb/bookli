<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookli</title>

    <style>
:root{
    --bg:#f5f7fb;
    --card:#ffffff;
    --text:#111827;
    --muted:#6b7280;
    --border:#e5e7eb;
    --primary:#111827;
    --primary-hover:#1f2937;
    --dark:#0f172a;
    --soft:#f3f4f6;
    --shadow:0 8px 24px rgba(15,23,42,.05);
}

*{box-sizing:border-box}

body{
    margin:0;
    font-family:Arial, sans-serif;
    background:var(--bg);
    color:var(--text);
}

a{text-decoration:none}

.container{
    max-width:1180px;
    margin:auto;
    padding:24px;
}

.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    padding:14px 0 28px;
    flex-wrap:wrap;
}

.brand{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:30px;
    font-weight:900;
    color:var(--text);
}

.brand-icon{
    width:42px;
    height:42px;
    border-radius:14px;
    background:#111827;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow:0 8px 24px rgba(15,23,42,.14);
}

.nav-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}

.language-switch{
    display:flex;
    gap:6px;
    background:#fff;
    border:1px solid var(--border);
    padding:5px;
    border-radius:14px;
}

.language-switch a{
    color:var(--muted);
    font-weight:800;
    font-size:13px;
    padding:7px 10px;
    border-radius:10px;
}

.language-switch a.active{
    background:#111827;
    color:#fff;
}

.btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    padding:13px 19px;
    border-radius:14px;
    font-weight:800;
    transition:.2s;
    border:1px solid transparent;
    cursor:pointer;
}

.btn-primary{
    background:var(--primary);
    color:#fff;
    box-shadow:0 8px 24px rgba(15,23,42,.12);
}

.btn-primary:hover{
    background:var(--primary-hover);
    transform:translateY(-1px);
}

.btn-light{
    background:#fff;
    color:var(--text);
    border-color:var(--border);
}

.btn-light:hover{
    background:#f9fafb;
    transform:translateY(-1px);
}

.hero{
    display:grid;
    grid-template-columns:1.08fr .92fr;
    gap:24px;
    align-items:stretch;
}

.hero-left{
    position:relative;
    overflow:hidden;
    background:linear-gradient(135deg,#111827,#1f2937);
    color:#fff;
    border-radius:28px;
    padding:48px;
    box-shadow:var(--shadow);
}

.hero-left:before{
    content:"";
    position:absolute;
    width:260px;
    height:260px;
    background:rgba(255,255,255,.08);
    border-radius:50%;
    top:-90px;
    inset-inline-end:-80px;
}

.badge{
    position:relative;
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.15);
    color:#e5e7eb;
    padding:9px 14px;
    border-radius:999px;
    font-size:14px;
    font-weight:800;
    margin-bottom:18px;
}

.hero-left h1{
    position:relative;
    margin:0 0 18px;
    font-size:52px;
    line-height:1.08;
    letter-spacing:-1px;
}

.hero-left p{
    position:relative;
    margin:0 0 28px;
    color:#d1d5db;
    font-size:18px;
    line-height:1.8;
    max-width:700px;
}

.hero-actions{
    position:relative;
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.hero-left .btn-light{
    background:rgba(255,255,255,.10);
    color:#fff;
    border-color:rgba(255,255,255,.18);
}

.hero-left .btn-light:hover{
    background:rgba(255,255,255,.16);
}

.hero-right{
    background:#fff;
    border:1px solid var(--border);
    border-radius:28px;
    padding:26px;
    box-shadow:var(--shadow);
}

.preview-card{
    background:#fff;
    border:1px solid var(--border);
    border-radius:22px;
    padding:20px;
    margin-bottom:16px;
}

.preview-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:14px;
    gap:12px;
}

.preview-title{
    font-size:18px;
    font-weight:900;
}

.status{
    background:#f3f4f6;
    color:#111827;
    border:1px solid var(--border);
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:900;
}

.mini-row{
    display:flex;
    gap:10px;
    align-items:center;
    padding:12px;
    border-radius:16px;
    background:#f9fafb;
    margin-top:10px;
    border:1px solid #eeeeee;
}

.mini-icon{
    width:38px;
    height:38px;
    border-radius:12px;
    background:#f3f4f6;
    color:#111827;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    flex:0 0 auto;
}

.mini-row h3{
    margin:0 0 4px;
    font-size:15px;
}

.mini-row p{
    margin:0;
    color:var(--muted);
    font-size:13px;
    line-height:1.5;
}

.stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
    margin-top:16px;
}

.stat{
    background:#fff;
    border:1px solid var(--border);
    border-radius:18px;
    padding:14px;
    text-align:center;
}

.stat strong{
    display:block;
    font-size:22px;
    color:#111827;
}

.stat span{
    color:var(--muted);
    font-size:13px;
}

.section{
    margin-top:56px;
}

.section-head{
    text-align:center;
    max-width:760px;
    margin:0 auto 30px;
}

.section-title{
    margin:0 0 12px;
    font-size:38px;
    line-height:1.2;
    letter-spacing:-.5px;
}

.section-subtitle{
    color:var(--muted);
    margin:0;
    line-height:1.8;
    font-size:17px;
}

.grid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0,1fr));
    gap:18px;
}

.feature{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:22px;
    padding:24px;
    box-shadow:var(--shadow);
    transition:.2s;
}

.feature:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 32px rgba(15,23,42,.08);
}

.feature-icon{
    width:46px;
    height:46px;
    border-radius:16px;
    background:#f3f4f6;
    color:#111827;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    margin-bottom:14px;
}

.feature h3{
    margin:0 0 10px;
    font-size:20px;
}

.feature p{
    margin:0;
    color:var(--muted);
    line-height:1.75;
}

.two-cols{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.box{
    background:#fff;
    border:1px solid var(--border);
    border-radius:22px;
    padding:28px;
    box-shadow:var(--shadow);
}

.box h3{
    margin:0 0 14px;
    font-size:24px;
}

.box ul{
    margin:0;
    padding-inline-start:20px;
    color:var(--muted);
    line-height:2;
}

.cta{
    margin-top:56px;
    position:relative;
    overflow:hidden;
    background:linear-gradient(135deg,#111827,#1f2937);
    color:#fff;
    border-radius:28px;
    padding:44px 28px;
    text-align:center;
    box-shadow:var(--shadow);
}

.cta h2{
    margin:0 0 12px;
    font-size:38px;
}

.cta p{
    margin:0 auto 24px;
    color:#d1d5db;
    line-height:1.8;
    max-width:720px;
}

.cta .btn-light{
    background:#fff;
    color:var(--text);
}

.footer{
    margin-top:28px;
    padding:22px 0;
    text-align:center;
    color:var(--muted);
    font-size:14px;
}

html[dir="rtl"] body{
    direction:rtl;
}

html[dir="rtl"] .section-head{
    text-align:center;
}

html[dir="rtl"] .mini-row{
    text-align:right;
}

@media (max-width: 992px){
    .hero,
    .grid,
    .two-cols{
        grid-template-columns:1fr;
    }

    .hero-left{
        padding:34px 24px;
    }

    .hero-left h1{
        font-size:38px;
    }

    .section-title,
    .cta h2{
        font-size:30px;
    }
}

@media (max-width: 560px){
    .container{
        padding:16px;
    }

    .navbar{
        align-items:flex-start;
    }

    .nav-actions,
    .hero-actions{
        width:100%;
    }

    .btn{
        width:100%;
    }

    .language-switch{
        width:100%;
        justify-content:center;
    }

    .stats{
        grid-template-columns:1fr;
    }

    .hero-left h1{
        font-size:32px;
    }
}
.contact-stats{
    grid-template-columns:repeat(2,1fr);
    max-width:760px;
    margin:auto;
}

.contact-card{
    padding:24px;
    text-align:start;
}

.stat-label{
    font-size:13px;
    font-weight:800;
    color:var(--muted);
    margin-bottom:8px;
    text-transform:uppercase;
    letter-spacing:.5px;
}

.stat-value{
    font-size:20px;
    font-weight:900;
    color:var(--text);
    word-break:break-word;
}

.contact-value{
    line-height:1.6;
}

@media (max-width:768px){
    .contact-stats{
        grid-template-columns:1fr;
    }
}
.contact-link{
    color:var(--text);
    font-weight:900;
    text-decoration:none;
}

.contact-link:hover{
    color:var(--primary);
    text-decoration:underline;
}
</style>
</head>

<body>
    <div class="container">

        <div class="navbar">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-icon">B</span>
                <span>Bookli</span>
            </a>

            <div class="nav-actions">
                <div class="language-switch">
                    <a href="{{ route('language.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                    <a href="{{ route('language.switch', 'ar') }}" class="{{ app()->getLocale() === 'ar' ? 'active' : '' }}">AR</a>
                </div>

                <a href="{{ route('login') }}" class="btn btn-light">
                    {{ __('messages.login') }}
                </a>

                <a href="{{ route('signup') }}" class="btn btn-primary">
                    {{ __('messages.start_free_trial') }}
                </a>
            </div>
        </div>

        <div class="hero">
            <div class="hero-left">
                <div class="badge">
                    {{ __('messages.home_badge') }}
                </div>

                <h1>{{ __('messages.home_title') }}</h1>

                <p>{{ __('messages.home_text') }}</p>

                <div class="hero-actions">
                    <a href="{{ route('signup') }}" class="btn btn-primary">
                        {{ __('messages.create_business_account') }}
                    </a>

                    <a href="{{ route('login') }}" class="btn btn-light">
                        {{ __('messages.login') }}
                    </a>
                </div>
            </div>

            <div class="hero-right">
                <div class="preview-card">
                    <div class="preview-top">
                        <div class="preview-title">
                            {{ __('messages.home_public_page_title') }}
                        </div>

                        <div class="status">
                            {{ __('messages.live_ready') }}
                        </div>
                    </div>

                    <div class="mini-row">
                        <div class="mini-icon">📅</div>
                        <div>
                            <h3>{{ __('messages.booking_mode') }}</h3>
                            <p>{{ __('messages.home_booking_mode_text') }}</p>
                        </div>
                    </div>

                    <div class="mini-row">
                        <div class="mini-icon">🛒</div>
                        <div>
                            <h3>{{ __('messages.menu_mode') }}</h3>
                            <p>{{ __('messages.home_menu_mode_text') }}</p>
                        </div>
                    </div>

                    <div class="mini-row">
                        <div class="mini-icon">⚙️</div>
                        <div>
                            <h3>{{ __('messages.owner_dashboard') }}</h3>
                            <p>{{ __('messages.home_owner_dashboard_text') }}</p>
                        </div>
                    </div>
                </div>

                <div class="stats">
                    <div class="stat">
                        <strong>2</strong>
                        <span>{{ __('messages.modes') }}</span>
                    </div>

                    <div class="stat">
                        <strong>24/7</strong>
                        <span>{{ __('messages.public_page') }}</span>
                    </div>

                    <div class="stat">
                        <strong>QR</strong>
                        <span>{{ __('messages.ready') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-head">
                <h2 class="section-title">{{ __('messages.home_why_title') }}</h2>
                <p class="section-subtitle">{{ __('messages.home_why_text') }}</p>
            </div>

            <div class="grid">
                <div class="feature">
                    <div class="feature-icon">📊</div>
                    <h3>{{ __('messages.simple_dashboard') }}</h3>
                    <p>{{ __('messages.simple_dashboard_text') }}</p>
                </div>

                <div class="feature">
                    <div class="feature-icon">🌐</div>
                    <h3>{{ __('messages.smart_pages') }}</h3>
                    <p>{{ __('messages.smart_pages_text') }}</p>
                </div>

                <div class="feature">
                    <div class="feature-icon">🎟️</div>
                    <h3>{{ __('messages.subscription_ready') }}</h3>
                    <p>{{ __('messages.subscription_ready_text') }}</p>
                </div>

                <div class="feature">
                    <div class="feature-icon">🛡️</div>
                    <h3>{{ __('messages.booking_protection') }}</h3>
                    <p>{{ __('messages.booking_protection_text') }}</p>
                </div>

                <div class="feature">
                    <div class="feature-icon">🍽️</div>
                    <h3>{{ __('messages.menu_orders') }}</h3>
                    <p>{{ __('messages.menu_orders_text') }}</p>
                </div>

                <div class="feature">
                    <div class="feature-icon">🚀</div>
                    <h3>{{ __('messages.built_growth') }}</h3>
                    <p>{{ __('messages.built_growth_text') }}</p>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-head">
                <h2 class="section-title">{{ __('messages.two_modes_title') }}</h2>
                <p class="section-subtitle">{{ __('messages.two_modes_text') }}</p>
            </div>

            <div class="two-cols">
                <div class="box">
                    <h3>{{ __('messages.booking_businesses') }}</h3>
                    <ul>
                        <li>{{ __('messages.booking_item_1') }}</li>
                        <li>{{ __('messages.booking_item_2') }}</li>
                        <li>{{ __('messages.booking_item_3') }}</li>
                        <li>{{ __('messages.booking_item_4') }}</li>
                        <li>{{ __('messages.booking_item_5') }}</li>

                    </ul>
                </div>

                <div class="box">
                    <h3>{{ __('messages.menu_businesses') }}</h3>
                    <ul>
                        <li>{{ __('messages.menu_item_1') }}</li>
                        <li>{{ __('messages.menu_item_2') }}</li>
                        <li>{{ __('messages.menu_item_3') }}</li>
                        <li>{{ __('messages.menu_item_4') }}</li>
                        <li>{{ __('messages.menu_item_5') }}</li>

                    </ul>
                </div>
            </div>
        </div>

        <div class="cta">
            <h2>{{ __('messages.cta_title') }}</h2>
            <p>{{ __('messages.cta_text') }}</p>

            <div class="hero-actions" style="justify-content:center;">
                <a href="{{ route('signup') }}" class="btn btn-primary">
                    {{ __('messages.create_account') }}
                </a>

                <a href="{{ route('login') }}" class="btn btn-light">
                    {{ __('messages.login') }}
                </a>
            </div>
        </div>
        @php
    $contact = [
        'phone' => \App\Models\SiteSetting::where('key', 'contact_phone')->value('value'),
        'email' => \App\Models\SiteSetting::where('key', 'contact_email')->value('value'),
    ];
@endphp

<div class="section">
    <div class="section-head">
        <h2 class="section-title">{{ __('messages.contact_us') }}</h2>
        <p class="section-subtitle">{{ __('messages.contact_us_text') }}</p>
    </div>

    <div class="stats contact-stats">
        <div class="stat contact-card">
    <div class="stat-label">{{ __('messages.phone_whatsapp') }}</div>
    <div class="stat-value contact-value">
        @if(!empty($contact['phone']))
            <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}" class="contact-link">
                {{ $contact['phone'] }}
            </a>
        @else
            {{ __('messages.add_phone_later') }}
        @endif
    </div>
</div>
<div class="stat contact-card">
    <div class="stat-label">{{ __('messages.email') }}</div>
    <div class="stat-value contact-value">
        @if(!empty($contact['email']))
            <a href="mailto:{{ $contact['email'] }}" class="contact-link">
                {{ $contact['email'] }}
            </a>
        @else
            {{ __('messages.add_email_later') }}
        @endif
    </div>
</div>
    </div>
</div>

        <div class="footer">
            © {{ now()->year }} Bookli. {{ __('messages.footer_rights') }}
        </div>

    </div>
</body>
</html>