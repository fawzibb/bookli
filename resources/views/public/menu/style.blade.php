<style>
.status{
    padding:14px 16px;
    border-radius:var(--radius);
    margin-bottom:20px;
    font-weight:700;
}

.status.open{
    background:var(--success-bg);
    color:var(--success-text);
}

.status.closed{
    background:var(--danger-bg);
    color:var(--danger-text);
}

.category-nav{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:24px;
    position:sticky;
    top:12px;
    z-index:40;
    padding:12px;
    background:rgba(255,255,255,.75);
    backdrop-filter:blur(10px);
    border:1px solid var(--border);
    border-radius:16px;
}

.category-nav a{
    text-decoration:none;
    background:var(--card);
    color:var(--primary);
    border:1px solid var(--border);
    border-radius:999px;
    padding:10px 15px;
    font-weight:700;
    font-size:14px;
}

.category-nav a:hover{
    background:var(--primary);
    color:#fff;
}

.menu-layout{
    display:grid;
    grid-template-columns:1.35fr .7fr;
    gap:24px;
    align-items:start;
}

.menu-section{
    margin-bottom:32px;
}

.menu-section-title{
    margin:0 0 14px;
    color:var(--primary);
    font-size:28px;
}

.items-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:16px;
}

.item-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:18px;
    box-shadow:0 8px 24px rgba(15,23,42,.05);
}

.item-top{
    display:flex;
    justify-content:space-between;
    gap:12px;
    margin-bottom:8px;
}

.item-name{
    font-size:18px;
    font-weight:800;
    color:var(--text);
}

.item-price{
    font-size:17px;
    font-weight:900;
    color:var(--primary);
    white-space:nowrap;
}

.item-description{
    color:var(--muted);
    font-size:14px;
    line-height:1.6;
    margin-bottom:14px;
}

.item-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.item-tag{
    background:var(--bg);
    border:1px solid var(--border);
    color:var(--primary);
    padding:7px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}

.btn{
    border:none;
    border-radius:12px;
    cursor:pointer;
    font-weight:800;
}

.btn-primary{
    background:var(--button);
    color:#fff;
    padding:11px 15px;
}

.btn-disabled{
    background:#d1d5db;
    color:#6b7280;
    cursor:not-allowed;
    padding:11px 15px;
}

.cart{
    position:sticky;
    top:20px;
}

.cart-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:22px;
    box-shadow:0 8px 24px rgba(15,23,42,.05);
}

.cart-title{
    margin:0;
    color:var(--primary);
    font-size:24px;
}

.cart-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
}

.cart-items-scroll{
    max-height:270px;
    overflow-y:auto;
    padding-right:4px;
}

.cart-empty{
    background:var(--bg);
    border:1px dashed var(--border);
    border-radius:14px;
    padding:16px;
    text-align:center;
    color:var(--muted);
    font-size:14px;
}

.cart-item{
    border-bottom:1px solid var(--border);
    padding:12px 0;
}

.cart-item:last-child{
    border-bottom:none;
}

.cart-item-top{
    display:flex;
    justify-content:space-between;
    gap:10px;
    margin-bottom:8px;
}

.cart-item-name{
    font-weight:700;
}

.cart-item-price{
    font-weight:800;
    color:var(--primary);
    white-space:nowrap;
}

.cart-item-actions{
    display:flex;
    align-items:center;
    gap:6px;
}

.qty{
    color:var(--muted);
    font-size:14px;
    margin-top:3px;
}

.qty-btn{
    width:32px;
    height:32px;
    padding:0;
    border-radius:10px;
    background:var(--button);
    color:#fff;
    border:none;
    font-weight:900;
    cursor:pointer;
}

.remove-btn{
    width:32px;
    height:32px;
    padding:0;
    border-radius:10px;
    background:#991b1b;
    color:#fff;
    border:none;
    font-weight:900;
    cursor:pointer;
}

.cart-close{
    display:none;
    width:36px;
    min-width:36px;
    height:36px;
    flex:0 0 36px;
    padding:0;
    border-radius:12px;
    border:none;
    background:#991b1b;
    color:#fff;
    font-size:22px;
    font-weight:900;
    line-height:1;
    cursor:pointer;
    align-items:center;
    justify-content:center;
}

.total{
    margin-top:14px;
    padding-top:14px;
    border-top:1px solid var(--border);
    display:flex;
    justify-content:space-between;
    font-size:20px;
    font-weight:900;
    color:var(--primary);
}

.submit-btn{
    margin-top:16px;
    width:100%;
    padding:14px;
}

.empty-box{
    background:var(--card);
    border:1px dashed var(--border);
    border-radius:var(--radius);
    padding:24px;
    text-align:center;
    color:var(--muted);
}

.mobile-cart-btn{
    display:none;
}

body.theme-modern .item-card,
body.theme-modern .cart-card{
    border:none;
    box-shadow:0 18px 40px rgba(15,23,42,.08);
}

body.theme-modern .category-nav a{
    border-radius:30px;
}

body.theme-luxury .menu-layout{
    gap:30px;
}

body.theme-luxury .item-card,
body.theme-luxury .cart-card{
    padding:26px;
}

button,
.btn,
.qty-btn,
.remove-btn,
.mobile-cart-btn,
.cart-close{
    transition:transform .08s ease, filter .15s ease, opacity .15s ease;
}

button:hover,
.btn:hover,
.qty-btn:hover,
.remove-btn:hover,
.mobile-cart-btn:hover,
.cart-close:hover{
    filter:brightness(.95);
}

button:active,
.btn:active,
.qty-btn:active,
.remove-btn:active,
.mobile-cart-btn:active,
.cart-close:active{
    transform:scale(.96);
    filter:brightness(.82);
}

@media(max-width:768px){
    .menu-layout{
        grid-template-columns:1fr;
    }

    .items-grid{
        grid-template-columns:1fr;
    }

    .category-nav{
        top:10px;
        overflow-x:auto;
        flex-wrap:nowrap;
        padding-bottom:8px;
    }

    .cart{
        display:none;
        position:fixed;
        left:12px;
        right:12px;
        bottom:82px;
        z-index:998;
        max-height:80vh;
        overflow:auto;
    }

    .cart.show{
        display:block;
    }

    .mobile-cart-btn{
        display:flex;
        position:fixed;
        right:18px;
        bottom:18px;
        z-index:999;
        width:auto;
        padding:14px 18px;
        border-radius:999px;
        background:var(--button);
        color:#fff;
        border:none;
        font-weight:900;
        box-shadow:0 12px 30px rgba(0,0,0,.25);
    }

    .cart-close{
        display:flex;
    }

    .item-footer{
        flex-direction:column;
        align-items:flex-start;
    }

    .item-footer .btn{
        width:100%;
    }
}
</style>