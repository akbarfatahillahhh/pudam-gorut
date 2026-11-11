<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih Modul | PERUMDA GORUT</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="logo/perumda-gorut.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@700;900&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: #f0f9ff;
            color: #0f172a;
            min-height: 100vh;
        }

        .module-page {
            position: relative;
            min-height: 100vh;
            padding: 8px;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== Background dekor ===== */
        .mp-bg {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
            border-radius: 20px;
        }

        .mp-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 12% 18%, rgba(2, 132, 199, .08), transparent 42%),
                radial-gradient(circle at 88% 82%, rgba(14, 165, 233, .10), transparent 45%);
        }

        .mp-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(2, 132, 199, .05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(2, 132, 199, .05) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(circle at 50% 40%, #000, transparent 80%);
            -webkit-mask-image: radial-gradient(circle at 50% 40%, #000, transparent 80%);
        }

        .mp-bubble {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, .9), rgba(14, 165, 233, .2));
            border: 1px solid rgba(255, 255, 255, .6);
            box-shadow: inset 0 0 10px rgba(255, 255, 255, .8), 0 8px 20px rgba(14, 165, 233, .12);
            animation: mpFloat 15s ease-in-out infinite;
        }

        .mp-bubble.b1 { width: 70px; height: 70px; top: 10%; left: 4%; }
        .mp-bubble.b2 { width: 40px; height: 40px; bottom: 12%; left: 8%; animation-delay: -3s; }
        .mp-bubble.b3 { width: 55px; height: 55px; top: 18%; right: 6%; animation-delay: -6s; }
        .mp-bubble.b4 { width: 30px; height: 30px; bottom: 18%; right: 10%; animation-delay: -9s; }

        @keyframes mpFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(15px, -30px) scale(1.05); }
        }

        /* ===== Container ===== */
        .mp-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1000px;
            padding: 24px 16px;
        }

        /* ===== Header ===== */
        .mp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .mp-brand { display: flex; align-items: center; gap: 14px; }

        .mp-brand-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            box-shadow: 0 10px 28px rgba(2, 132, 199, .22);
            position: relative;
        }

        .mp-brand-icon::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 20px;
            border: 2px dashed rgba(2, 132, 199, .25);
            animation: mpRotate 20s linear infinite;
        }

        @keyframes mpRotate { to { transform: rotate(360deg); } }

        .mp-brand-icon img { max-width: 100%; height: auto; }

        .mp-brand-text .mp-brand-title {
            font-size: 17px;
            font-weight: 800;
            color: #0c4a6e;
            letter-spacing: .3px;
        }

        .mp-brand-text .mp-brand-sub {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #0284c7;
        }

        /* ===== Header Right (badge + logout) ===== */
        .mp-header-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .mp-badge-top {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 100px;
            background: #e0f2fe;
            border: 1.5px solid #bae6fd;
            color: #0369a1;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
        }

        .mp-badge-top .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #0ea5e9;
            box-shadow: 0 0 10px #0ea5e9;
            animation: mpPulse 2s infinite;
        }

        @keyframes mpPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%      { opacity: .5; transform: scale(1.3); }
        }

        /* ===== TOMBOL LOGOUT ===== */
        .mp-btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 100px;
            background: #fff;
            border: 1.5px solid #fecaca;
            color: #dc2626;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            transition: all .3s ease;
            box-shadow: 0 4px 12px rgba(220, 38, 38, .08);
        }

        .mp-btn-logout i {
            font-size: 12px;
            transition: transform .3s ease;
        }

        .mp-btn-logout:hover {
            background: #fee2e2;
            border-color: #f87171;
            color: #b91c1c;
            box-shadow: 0 8px 20px rgba(220, 38, 38, .18);
            transform: translateY(-2px);
        }

        .mp-btn-logout:hover i {
            transform: translateX(3px);
        }

        /* ===== Hero ===== */
        .mp-hero {
            text-align: center;
            margin-bottom: 40px;
            animation: mpFadeUp .5s ease both;
        }

        @keyframes mpFadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .mp-hero h1 {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 38px;
            font-weight: 900;
            color: #0c4a6e;
            letter-spacing: -.8px;
            margin-bottom: 12px;
        }

        .mp-hero h1 .grad {
            background: linear-gradient(135deg, #0284c7, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .mp-hero p {
            color: #475569;
            font-size: 15px;
            line-height: 1.7;
            max-width: 560px;
            margin: 0 auto;
        }

        /* ===== Grid Modul ===== */
        .mp-module-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            max-width: 860px;
            margin: 0 auto;
            animation: mpFadeUp .6s ease both;
        }

        @media (max-width: 720px) {
            .mp-module-grid { grid-template-columns: 1fr; }
        }

        .mp-module-card {
            position: relative;
            display: flex;
            flex-direction: column;
            text-align: left;
            gap: 20px;
            padding: 32px 28px;
            background: #fff;
            border: 2px solid #e0f2fe;
            border-radius: 24px;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(2, 132, 199, .06);
            transition: all .35s cubic-bezier(.175, .885, .32, 1.275);
            font-family: inherit;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
        }

        .mp-module-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 165, 233, .10), transparent 70%);
            transform: translate(40%, -40%);
            transition: transform .5s ease;
        }

        .mp-module-card:hover {
            transform: translateY(-8px);
            border-color: #7dd3fc;
            box-shadow: 0 22px 44px rgba(2, 132, 199, .20);
        }

        .mp-module-card:hover::before {
            transform: translate(20%, -20%) scale(1.4);
        }

        .mp-module-card.ekas { border-color: #d1fae5; }
        .mp-module-card.ekas::before { background: radial-gradient(circle, rgba(16, 185, 129, .10), transparent 70%); }
        .mp-module-card.ekas:hover {
            border-color: #6ee7b7;
            box-shadow: 0 22px 44px rgba(5, 150, 105, .18);
        }

        .mp-module-head {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            z-index: 1;
        }

        .mp-module-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #fff;
            flex-shrink: 0;
            background: linear-gradient(135deg, #0284c7, #0ea5e9);
            box-shadow: 0 12px 24px rgba(2, 132, 199, .32);
            transition: transform .35s ease;
        }

        .mp-module-card:hover .mp-module-icon {
            transform: scale(1.08) rotate(-4deg);
        }

        .mp-module-card.ekas .mp-module-icon {
            background: linear-gradient(135deg, #059669, #10b981);
            box-shadow: 0 12px 24px rgba(5, 150, 105, .30);
        }

        .mp-module-title {
            font-size: 21px;
            font-weight: 800;
            color: #0c4a6e;
            margin-bottom: 4px;
        }

        .mp-module-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #0284c7;
            background: #e0f2fe;
            padding: 3px 10px;
            border-radius: 100px;
        }

        .mp-module-card.ekas .mp-module-tag {
            color: #047857;
            background: #d1fae5;
        }

        .mp-module-desc {
            position: relative;
            z-index: 1;
            font-size: 13.5px;
            color: #475569;
            line-height: 1.65;
        }

        .mp-module-foot {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 8px;
            border-top: 1px dashed #e0f2fe;
        }

        .mp-module-card.ekas .mp-module-foot {
            border-top-color: #d1fae5;
        }

        .mp-module-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .3px;
            color: #0284c7;
            text-transform: uppercase;
        }

        .mp-module-card.ekas .mp-module-cta {
            color: #059669;
        }

        .mp-module-arrow {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0f2fe;
            color: #0284c7;
            font-size: 15px;
            transition: all .3s ease;
        }

        .mp-module-card.ekas .mp-module-arrow {
            background: #d1fae5;
            color: #047857;
        }

        .mp-module-card:hover .mp-module-arrow {
            background: #0284c7;
            color: #fff;
            transform: translateX(6px);
        }

        .mp-module-card.ekas:hover .mp-module-arrow {
            background: #059669;
            color: #fff;
        }

        .mp-footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
        }

        @media (max-width: 480px) {
            .mp-header-right { width: 100%; justify-content: space-between; }
        }
    </style>
</head>

<body>

    <div class="module-page">
        <div class="mp-bg">
            <div class="mp-grid"></div>
            <div class="mp-bubble b1"></div>
            <div class="mp-bubble b2"></div>
            <div class="mp-bubble b3"></div>
            <div class="mp-bubble b4"></div>
        </div>

        <div class="mp-container">

            <!-- Header -->
            <div class="mp-header">
                <div class="mp-brand">
                    <div class="mp-brand-icon">
                        <img src="logo/perumda-gorut.png" alt="PERUMDA GORUT">
                    </div>
                    <div class="mp-brand-text">
                        <div class="mp-brand-title">PERUMDA GORUT</div>
                        <div class="mp-brand-sub">Sistem Informasi Terintegrasi</div>
                    </div>
                </div>

                <div class="mp-header-right">
                    <div class="mp-badge-top">
                        <span class="dot"></span> Pilih Modul
                    </div>

                    <!-- TOMBOL LOGOUT -->
                    <a href="logout" class="mp-btn-logout" id="btn-logout">
                        <span>Keluar</span>
                        <i class="fas fa-right-from-bracket"></i>
                    </a>
                </div>
            </div>

            <!-- Hero -->
            <div class="mp-hero">
                <h1>Pilih <span class="grad">Modul</span> Anda</h1>
                <p>Silakan pilih modul yang ingin Anda akses. Anda akan diarahkan ke halaman modul yang sesuai.</p>
            </div>

            <!-- Grid Modul -->
            <div class="mp-module-grid">

                <!-- BILLING SISTEM -->
                <a href="billing" class="mp-module-card">
                    <div class="mp-module-head">
                        <div class="mp-module-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <div class="mp-module-title">Billing Sistem</div>
                            <span class="mp-module-tag">Keuangan &amp; Tagihan</span>
                        </div>
                    </div>
                    <p class="mp-module-desc">
                        Pengelolaan tagihan pelanggan, penagihan, pembayaran, dan laporan keuangan secara terpadu.
                    </p>
                    <div class="mp-module-foot">
                        <span class="mp-module-cta">Masuk Modul</span>
                        <span class="mp-module-arrow"><i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>

                <!-- EKAS-SISTEM -->
                <a href="ekas" class="mp-module-card ekas">
                    <div class="mp-module-head">
                        <div class="mp-module-icon">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <div>
                            <div class="mp-module-title">Ekas-Sistem</div>
                            <span class="mp-module-tag">Operasional &amp; Kasir</span>
                        </div>
                    </div>
                    <p class="mp-module-desc">
                        Pencatatan meter, laporan aktivitas PCM, dan pengelolaan operasional harian di lapangan.
                    </p>
                    <div class="mp-module-foot">
                        <span class="mp-module-cta">Masuk Modul</span>
                        <span class="mp-module-arrow"><i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>

            </div>

            <div class="mp-footer">
                &copy; <span id="year"></span> PERUMDA GORUT &mdash; Hak Cipta Dilindungi
            </div>

        </div>
    </div>

    <script>
        // Set tahun berjalan
        document.getElementById('year').textContent = new Date().getFullYear();

        // Efek loading saat klik kartu modul
        document.querySelectorAll('.mp-module-card').forEach(function (card) {
            card.addEventListener('click', function () {
                var arrow = this.querySelector('.mp-module-arrow');
                arrow.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
            });
        });

        // Konfirmasi logout
        document.getElementById('btn-logout').addEventListener('click', function (e) {
            e.preventDefault();
            if (confirm('Apakah Anda yakin ingin keluar dari aplikasi?')) {
                window.location.href = 'logout';
            }
        });
    </script>

</body>

</html>