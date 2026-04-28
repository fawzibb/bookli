<style>
*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:var(--font);
    background:var(--bg);
    color:var(--text);
}

.wrapper{
    max-width:1100px;
    margin:auto;
    padding:24px;
}

.top-link{
    display:flex;
    justify-content:flex-end;
    margin-bottom:12px;
}

.top-link a{
    text-decoration:none;
    background:var(--card);
    border:1px solid var(--border);
    color:var(--text);
    padding:10px 14px;
    border-radius:12px;
    font-size:14px;
    font-weight:600;
    box-shadow:0 4px 12px rgba(15,23,42,.04);
}

.hero{
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    color:#fff;
    padding:32px;
    border-radius:var(--radius);
    margin-bottom:24px;
    overflow:hidden;

    height:180px;
}

.hero-content{
    display:grid;
    grid-template-columns:1fr auto;
    align-items:center;
    gap:24px;
    height:100%;
}

.hero-text{
    min-width:0;
    max-width:680px;
}

.hero-text h1{
    margin:0 0 10px;
    font-size:34px;
    line-height:1.1;
}

.hero-text p{
    margin:0;
    color:rgba(255,255,255,.85);
}

.hero-logo-wrap{
    justify-self:end;
    height:100%;
    width:320px;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}

.hero-logo{
    width:100%;
    height:100%;
    max-width:300px;
    max-height:160px;
    object-fit:contain;
    background:transparent;
    border:none;
    padding:0;
    box-shadow:none;
    filter:drop-shadow(0 12px 24px rgba(0,0,0,.20));
}

.grid{
    display:grid;
    grid-template-columns:1.2fr .8fr;
    gap:24px;
}

.card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:22px;
    box-shadow:0 8px 24px rgba(15,23,42,.05);
}

.section-title{
    margin:0 0 16px;
    font-size:22px;
}

.form-group{
    margin-bottom:14px;
}

label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    font-size:14px;
}

input,
select,
button,
textarea{
    width:100%;
    padding:12px 14px;
    border:1px solid var(--border);
    border-radius:12px;
    font-size:14px;
    outline:none;
    background:#fff;
}

input:focus,
select:focus,
textarea:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(0,0,0,.06);
}

button{
    background:var(--button);
    color:#fff;
    border:none;
    cursor:pointer;
    font-weight:700;
    transition:.2s;
}

button:hover{
    opacity:.92;
}

.alert{
    padding:14px 16px;
    border-radius:12px;
    margin-bottom:16px;
}

.alert-success{
    background:var(--success-bg);
    color:var(--success-text);
}

.alert-error{
    background:var(--danger-bg);
    color:var(--danger-text);
}

.muted{
    color:var(--muted);
    font-size:14px;
}

.slots{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(90px,1fr));
    gap:10px;
    margin-top:14px;
}

.slot{
    padding:10px 12px;
    text-align:center;
    border:1px solid var(--border);
    border-radius:12px;
    cursor:pointer;
    background:#fff;
    transition:.2s;
    font-weight:600;
}

.slot:hover{
    border-color:var(--primary);
}

.slot.active{
    background:var(--primary);
    color:#fff;
    border-color:var(--primary);
}

.service-box{
    border:1px solid var(--border);
    border-radius:14px;
    padding:14px;
    margin-bottom:10px;
    background:var(--card);
}

.service-box strong{
    display:block;
    margin-bottom:4px;
}

/* DEFAULT */
body.theme-default .hero{
    background:linear-gradient(135deg,var(--primary),var(--secondary));
}

/* MODERN */
body.theme-modern .wrapper{
    max-width:1200px;
}

body.theme-modern .hero{
    box-shadow:0 20px 40px rgba(0,0,0,.12);
}

body.theme-modern .card{
    border:none;
    box-shadow:0 18px 40px rgba(15,23,42,.08);
}

body.theme-modern .grid{
    gap:30px;
}

body.theme-modern .slots{
    gap:14px;
}

body.theme-modern .slot{
    border-radius:30px;
}

/* ELEGANT */
body.theme-elegant{
    background:var(--bg);
}

body.theme-elegant .hero{
    padding:40px;
    letter-spacing:.3px;
}

body.theme-elegant .card{
    box-shadow:none;
    border:1px solid var(--border);
}

body.theme-elegant .section-title{
    font-weight:500;
}

body.theme-elegant button{
    text-transform:uppercase;
    letter-spacing:.5px;
}

.elegant-layout{
    grid-template-columns:1fr !important;
}

.elegant-layout .card:first-child{
    order:2;
}

.elegant-layout .card:last-child{
    order:1;
}

/* LUXURY */
body.theme-luxury .wrapper{
    max-width:1280px;
}

body.theme-luxury .hero{
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    padding:55px;
}

body.theme-luxury .hero-text h1{
    font-size:42px;
}

body.theme-luxury .card{
    padding:28px;
}

@media (max-width:900px){
    .grid{
        grid-template-columns:1fr;
    }
    .hero{
    height:auto;
    min-height:220px;
    padding:26px;
}

    .hero-content{
    grid-template-columns:1fr;
    text-align:center;
}

.hero-logo-wrap{
    justify-self:center;
    order:-1;
    width:100%;
    height:90px;
}

.hero-logo{
    max-height:80px;
    max-width:220px;
    width:auto;
    height:auto;
}

.hero-text h1{
    font-size:28px;
}

body.theme-luxury .hero-text h1{
    font-size:32px;
}

    .wrapper{
        padding:16px;
    }
}
.language-switcher{
    display:flex;
    justify-content:flex-end;
    gap:8px;
    margin-bottom:14px;
}

.language-switcher a{
    text-decoration:none;
    padding:8px 12px;
    border-radius:999px;
    border:1px solid var(--border);
    background:var(--card);
    color:var(--text);
    font-size:13px;
    font-weight:700;
}

.language-switcher a.active{
    background:var(--button);
    color:#fff;
    border-color:var(--button);
}
/* Bigger logo for Elegant + Luxury */
body.theme-elegant .hero-logo-wrap,
body.theme-luxury .hero-logo-wrap{
    width:360px;
}

body.theme-elegant .hero-logo,
body.theme-luxury .hero-logo{
    max-width:340px;
    max-height:170px;
}

/* Mobile */
@media (max-width:900px){
    body.theme-elegant .hero-logo,
    body.theme-luxury .hero-logo{
        max-width:260px;
        max-height:100px;
    }
}
</style>