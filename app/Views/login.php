<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | PERUMDA GORUT</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="shortcut icon" href="<?= base_url('logo/perumda-gorut.png') ?>" type="image/x-icon">

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@700;900&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            background: #f0f9ff; /* Latar belakang biru sangat muda */
            color: #0f172a;
        }

        /* ============ BACKGROUND DECOR ============ */
        .bg-decor {
            position: fixed;
            inset: 0;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .bg-decor::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 12% 18%, rgba(2, 132, 199, 0.1), transparent 42%),
                radial-gradient(circle at 88% 82%, rgba(14, 165, 233, 0.12), transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(3, 105, 161, 0.05), transparent 60%);
        }

        .water-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(2, 132, 199, 0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(2, 132, 199, 0.07) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(circle at 50% 50%, black, transparent 85%);
            -webkit-mask-image: radial-gradient(circle at 50% 50%, black, transparent 85%);
        }

        /* Floating Bubbles (Gelembung Air) */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.9), rgba(14, 165, 233, 0.2));
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.8), 0 8px 20px rgba(14, 165, 233, 0.15);
            animation: floatBubble 15s ease-in-out infinite;
        }

        .bubble-1 { width: 80px; height: 80px; top: 12%; left: 6%; animation-delay: 0s; }
        .bubble-2 { width: 45px; height: 45px; top: 74%; left: 8%; animation-delay: -3s; }
        .bubble-3 { width: 65px; height: 65px; top: 22%; right: 5%; animation-delay: -6s; }
        .bubble-4 { width: 35px; height: 35px; bottom: 14%; right: 10%; animation-delay: -9s; }

        @keyframes floatBubble {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(20px, -40px) scale(1.05); }
        }

        .cross-icon {
            position: absolute;
            color: #0ea5e9;
            opacity: 0.15;
            font-size: 40px;
            animation: floatBubble 15s ease-in-out infinite;
        }

        .cross-1 { top: 45%; left: 3%; animation-delay: -2s; }
        .cross-2 { top: 8%; right: 25%; animation-delay: -7s; font-size: 60px; color: #0284c7; }
        .cross-3 { bottom: 8%; left: 30%; animation-delay: -4s; font-size: 30px; color: #38bdf8; }

        .chart-icon {
            position: absolute;
            color: #0284c7;
            opacity: 0.1;
            font-size: 70px;
            animation: floatBubble 18s ease-in-out infinite;
        }
        .chart-1 { top: 55%; right: 30%; animation-delay: -5s; }
        .chart-2 { bottom: 20%; left: 15%; animation-delay: -10s; font-size: 50px; }

        /* ============ CONTAINER ============ */
        .login-container {
            position: relative;
            z-index: 1;
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* ============ LEFT PANEL ============ */
        .left-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 60px;
            position: relative;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #fff;
            box-shadow: 0 10px 28px rgba(2, 132, 199, 0.35);
            position: relative;
            padding: 8px;
        }

        .logo-icon::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 20px;
            border: 2px dashed rgba(2, 132, 199, 0.3);
            animation: rotateBorder 20s linear infinite;
        }

        @keyframes rotateBorder {
            to { transform: rotate(360deg); }
        }

        .logo-text .brand {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #0c4a6e;
        }

        .logo-text .sub {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #0284c7;
        }

        /* HERO */
        .hero-content {
            max-width: 560px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 100px;
            background: #e0f2fe;
            border: 1.5px solid #bae6fd;
            color: #0369a1;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 22px;
        }

        .hero-badge .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #0ea5e9;
            box-shadow: 0 0 10px #0ea5e9;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.5; transform: scale(1.3); }
        }

        .hero-content h1 {
            font-family: 'Fraunces', serif;
            font-size: 48px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 18px;
            letter-spacing: -1.5px;
            color: #0c4a6e;
        }

        .hero-content h1 .gradient {
            background: linear-gradient(135deg, #0284c7, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-content p {
            font-size: 15px;
            line-height: 1.7;
            color: #475569;
            margin-bottom: 30px;
            max-width: 490px;
        }

        /* Alur kerja */
        .flow-title {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #0284c7;
            margin-bottom: 12px;
        }

        .flow-steps {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .flow-step {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(10px);
            border: 1px solid #bae6fd;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            color: #0c4a6e;
            transition: all 0.3s ease;
        }

        .flow-step:hover {
            transform: translateY(-2px);
            border-color: #7dd3fc;
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.12);
        }

        .flow-step i {
            color: #0284c7;
            font-size: 12px;
        }

        .flow-arrow {
            color: #7dd3fc;
            font-size: 12px;
        }

        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            max-width: 500px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid #bae6fd;
            border-radius: 14px;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(14, 165, 233, 0.12);
            border-color: #7dd3fc;
        }

        .feature-item i {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            color: #0369a1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .feature-item span {
            font-size: 12.5px;
            font-weight: 600;
            color: #0c4a6e;
            line-height: 1.3;
        }

        .footer-left {
            font-size: 12px;
            color: #64748b;
        }

        /* ========================================= */
        /* ============ RIGHT PANEL (UPGRADED) ===== */
        /* ========================================= */
        .right-panel {
            flex: 0 0 540px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
        }

        /* Efek Kaca Belakang (Glow & Blur) */
        .right-panel::before {
            content: '';
            position: absolute;
            inset: 20px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 1), rgba(255, 255, 255, 1));
            backdrop-filter: blur(50px);
            -webkit-backdrop-filter: blur(50px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 36px;
            box-shadow:
                0 30px 60px rgba(2, 132, 199, 0.12), /* Bayangan Biru */
                0 0 0 1px rgba(255, 255, 255, 0.5) inset, /* Garis dalam */
                inset 0 1px 0 rgba(255, 255, 255, 1);
            z-index: 0;
            overflow: hidden;
        }

        /* Pendar cahaya di dalam card */
        .right-panel::after {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle at 50% 50%, rgba(56, 189, 248, 0.15) 0%, transparent 60%);
            animation: rotateGlow 15s linear infinite;
            z-index: 0;
            pointer-events: none;
        }

        @keyframes rotateGlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-card {
            position: relative;
            width: 100%;
            max-width: 400px;
            z-index: 2;
        }

        /* Icon di atas form */
        .form-icon {
            width: 70px;
            height: 70px;
            border-radius: 22px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: 0 15px 35px rgba(2, 132, 199, 0.2), 0 0 0 1px rgba(2, 132, 199, 0.05);
            padding: 12px;
            transform: rotate(-3deg);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .form-icon:hover {
            transform: rotate(0deg) scale(1.05);
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            color: #0369a1;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .login-card h2 {
            font-family: 'Fraunces', serif;
            font-size: 32px;
            font-weight: 900;
            color: #0c4a6e;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-card .desc {
            font-size: 14px;
            color: #475569;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #0c4a6e;
            margin-bottom: 8px;
            letter-spacing: 0.2px;
            transition: color 0.3s;
        }

        /* Input Form Premium */
        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 2px solid transparent;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.05);
        }

        .input-wrap:focus-within {
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15), 0 8px 20px rgba(2, 132, 199, 0.1);
            transform: translateY(-2px);
        }

        .form-group:focus-within label {
            color: #0284c7;
        }

        .input-wrap i.field-icon {
            padding: 0 16px;
            color: #94a3b8;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .input-wrap:focus-within i.field-icon {
            color: #0284c7;
            transform: scale(1.1);
        }

        .input-wrap input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            padding: 16px 0;
            color: #0f172a;
            font-size: 14.5px;
            font-weight: 600;
            font-family: inherit;
        }

        .input-wrap input::placeholder {
            color: #cbd5e1;
            font-weight: 500;
        }

        .input-wrap .toggle-btn {
            padding: 0 18px;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.2s;
        }

        .input-wrap .toggle-btn:hover {
            color: #0284c7;
            transform: scale(1.1);
        }

        .input-wrap.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
            animation: shake 0.4s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25%      { transform: translateX(-6px); }
            75%      { transform: translateX(6px); }
        }

        .row-remember {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            font-size: 13.5px;
        }

        .row-remember label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            cursor: pointer;
            font-weight: 600;
        }

        .row-remember input[type="checkbox"] {
            accent-color: #0ea5e9;
            cursor: pointer;
            width: 18px;
            height: 18px;
            border-radius: 4px;
        }

        .row-remember a {
            color: #0284c7;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s;
        }

        .row-remember a:hover {
            color: #0369a1;
            text-decoration: underline;
        }

        /* Tombol Login Super Premium */
        .btn-login {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #0284c7, #0ea5e9);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 12px 30px rgba(2, 132, 199, 0.35);
            position: relative;
            overflow: hidden;
        }

        /* Efek Kilau Menyapu */
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 18px 40px rgba(2, 132, 199, 0.5);
        }

        .btn-login:active {
            transform: translateY(1px) scale(0.98);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 28px 0 20px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #cbd5e1, transparent);
        }

        .security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 12.5px;
            color: #64748b;
            text-align: center;
            font-weight: 600;
        }

        .security-note i {
            color: #0ea5e9;
            font-size: 14px;
        }

        .copyright {
            margin-top: 24px;
            text-align: center;
            font-size: 12.5px;
            color: #94a3b8;
            font-weight: 500;
        }

        .copyright a {
            color: #0284c7;
            text-decoration: none;
            font-weight: 700;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1024px) {
            .left-panel { padding: 40px; }
            .hero-content h1 { font-size: 40px; }
            .right-panel { flex: 0 0 460px; }
        }

        @media (max-width: 900px) {
            html, body { overflow: auto; }
            .login-container { flex-direction: column; height: auto; min-height: 100vh; }
            .left-panel { padding: 32px 24px; flex: none; }
            .hero-content h1 { font-size: 32px; }
            .features { grid-template-columns: 1fr; max-width: 100%; }
            .footer-left { margin-top: 24px; }
            .right-panel { flex: none; padding: 24px; }
            .right-panel::before { inset: 12px; border-radius: 24px; }
            .bubble, .chart-icon { display: none; }
        }

        @media (max-width: 480px) {
            .hero-content h1 { font-size: 26px; }
            .login-card h2 { font-size: 26px; }
            .form-icon { width: 60px; height: 60px; font-size: 20px; }
            .flow-steps { gap: 4px; }
            .flow-step { padding: 7px 10px; font-size: 11px; }
        }

        .bg-white { background-color: #ffffff !important; }
        .img-fluid { max-width: 100%; height: auto; }
    </style>
</head>

<body>

    <!-- Background Decor (Tema Air & Gelembung) -->
    <div class="bg-decor">
        <div class="water-grid"></div>
        
        <!-- Gelembung Air -->
        <div class="bubble bubble-1"></div>
        <div class="bubble bubble-2"></div>
        <div class="bubble bubble-3"></div>
        <div class="bubble bubble-4"></div>

        <!-- Ikon Air Melayang -->
        <i class="fas fa-droplet cross-icon cross-1"></i>
        <i class="fas fa-water cross-icon cross-2"></i>
        <i class="fas fa-faucet-drip cross-icon cross-3"></i>
        <i class="fas fa-chart-pie chart-icon chart-1"></i>
        <i class="fas fa-file-invoice-dollar chart-icon chart-2"></i>
    </div>

    <div class="login-container">

        <!-- LEFT: BRANDING -->
        <div class="left-panel">
            <div class="logo-area">
                <div class="logo-icon bg-white">
                    <img 
                        class="img-fluid" 
                        src="<?= base_url('logo/perumda-gorut.png') ?>" 
                        alt="PERUMDA GORUT">
                </div>
                <div class="logo-text">
                    <div class="brand">PERUMDA GORUT</div>
                    <div class="sub">Kabupaten Gorontalo Utara</div>
                </div>
            </div>

            <div class="hero-content">
                <div class="hero-badge">
                    <span class="dot"></span>
                    Sistem Informasi Terintegrasi
                </div>
                <h1>
                    Pelayanan Prima,<br>
                    <span class="gradient">Tata Kelola</span><br>
                    Air Bersih.
                </h1>
                <p>
                    Platform digital terpadu untuk pengelolaan layanan pelanggan, pencatatan meter, 
                    keuangan, hingga pelaporan operasional Perusahaan Umum Daerah Gorontalo Utara.
                </p>

                <div class="flow-title">Alur Layanan</div>
                <div class="flow-steps">
                    <div class="flow-step">
                        <i class="fas fa-users"></i> Pelanggan
                    </div>
                    <i class="fas fa-chevron-right flow-arrow"></i>
                    <div class="flow-step">
                        <i class="fas fa-gauge-high"></i> Catat Meter
                    </div>
                    <i class="fas fa-chevron-right flow-arrow"></i>
                    <div class="flow-step">
                        <i class="fas fa-file-invoice"></i> Tagihan
                    </div>
                    <i class="fas fa-chevron-right flow-arrow"></i>
                    <div class="flow-step">
                        <i class="fas fa-money-bill-wave"></i> Pembayaran
                    </div>
                </div>

                <div class="features">
                    <div class="feature-item">
                        <i class="fas fa-hand-holding-water"></i>
                        <span>Layanan<br>Pelanggan</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-coins"></i>
                        <span>Keuangan &<br>Kasir</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Laporan<br>Real-time</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-halved"></i>
                        <span>Keamanan &<br>Audit Trail</span>
                    </div>
                </div>
            </div>

            <div class="footer-left">
                &copy; <?= date('Y') ?> PERUMDA GORUT &mdash; Hak Cipta Dilindungi
            </div>
        </div>

        <!-- RIGHT: LOGIN FORM (UPGRADED) -->
        <div class="right-panel">
            <div class="login-card">
                
                <!-- Icon dengan efek 3D -->
                <div class="form-icon">
                    <img 
                        class="img-fluid" 
                        src="<?= base_url('logo/perumda-gorut.png') ?>" 
                        alt="PERUMDA GORUT">
                </div>

                <!-- Badge kecil di atas Judul -->
                <div class="login-badge">
                    <i class="fas fa-shield-check"></i> Portal Admin
                </div>

                <h2>Selamat Datang</h2>
                <p class="desc">Masuk untuk mengakses sistem informasi manajemen PERUMDA GORUT.</p>

                <form id="form-login" method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <i class="fas fa-user field-icon"></i>
                            <input type="text" id="username" name="username" placeholder="Masukkan username">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Masukkan password">
                            <span class="toggle-btn" id="toggle-password">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        <span>Masuk ke Aplikasi</span>
                    </button>

                    <div class="divider">
                        <i class="fas fa-shield-halved"></i> SECURE ACCESS
                    </div>

                    <div class="security-note">
                        <i class="fas fa-lock"></i>
                        Data terenkripsi &amp; hanya diakses user berwenang
                    </div>
                </form>

                <div class="copyright">
                    Copyright &copy; <?= date('Y') ?> |
                    <a href="javascript:void(0)">Tim IT PERUMDA GORUT</a>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {

            // Toggle password
            $('#toggle-password').on('click', function () {
                var $input = $('#password');
                var $icon = $(this).find('i');
                if ($input.attr('type') === 'password') {
                    $input.attr('type', 'text');
                    $icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    $input.attr('type', 'password');
                    $icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Reset invalid state
            $('#username, #password').on('input', function () {
                $(this).closest('.input-wrap').removeClass('is-invalid');
            });

            // Submit login
            $('#form-login').on('submit', function (e) {
                e.preventDefault();

                var username = $('#username').val().trim();
                var password = $('#password').val();

                $('.input-wrap').removeClass('is-invalid');

                if (username === '' || password === '') {
                    if (username === '') $('#username').closest('.input-wrap').addClass('is-invalid');
                    if (password === '') $('#password').closest('.input-wrap').addClass('is-invalid');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'Username dan password wajib diisi.',
                        confirmButtonColor: '#0ea5e9'
                    });
                    return;
                }

                var $btn = $('.btn-login');
                var originalHtml = $btn.html();
                $btn.prop('disabled', true)
                    .html('<i class="fas fa-circle-notch fa-spin"></i> <span>Memverifikasi...</span>');

                $.ajax({
                    type: "post",
                    url: "<?= base_url('login') ?>",
                    data: { username: username, password: password },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Login Berhasil',
                                text: 'Mengalihkan ke dashboard...',
                                timer: 1200,
                                showConfirmButton: false
                            }).then(function () {
                                // Home::index mengarahkan ke dashboard sistem
                                // yang boleh dibuka user (billing / kas).
                                window.location.href = "<?= base_url('home') ?>";
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Login Gagal',
                                text: response.message || 'Username atau password salah.',
                                confirmButtonColor: '#0ea5e9'
                            });
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function (xhr) {
                        console.log('Error:', xhr.status, xhr.statusText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan Sistem',
                            text: 'Tidak dapat menghubungi server. Coba lagi nanti.',
                            confirmButtonColor: '#0ea5e9'
                        });
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

        });
    </script>

</body>

</html>