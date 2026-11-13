<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PERUMDA GORUT</title>
    <link rel="shortcut icon" type="image/png" href="<?= base_url() ?>/logo/perumda-gorut.png" />
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/styles.min.css?v=<?= filemtime(FCPATH . 'assets/css/styles.min.css') ?>" />

    <!-- @import di dalam styles.min.css tidak berlaku (bukan di awal file),
         jadi tabler-icons dimuat eksplisit di sini. Versi ikut filemtime
         supaya cache Cloudflare/browser tidak menyajikan file lama. -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/icons/tabler-icons/tabler-icons.css?v=<?= filemtime(FCPATH . 'assets/css/icons/tabler-icons/tabler-icons.css') ?>" />

    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/js/all.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Memuat DataTables CSS dari CDN -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

    <!-- Memuat DataTables JavaScript dari CDN -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <!-- Memuat DataTables Responsif (jika diperlukan) -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/responsive/2.2.5/js/dataTables.responsive.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.5/css/responsive.dataTables.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Pastikan font ini ada di <head> -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@700;900&display=swap" rel="stylesheet">

    <style>
    /* =====================================================
       PERUMDA GORUT - PREMIUM SAAS DASHBOARD UI
       ===================================================== */

    /* 1. VARIABEL & GLOBAL RESET */
    :root {
        --primary: #0284c7;
        --primary-hover: #0369a1;
        --bg-body: #f8fafc; /* Abu-abu kebiruan sangat lembut */
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: var(--bg-body) !important;
        font-size: 13.5px;
        color: var(--text-dark);
        -webkit-font-smoothing: antialiased;
    }

    h1, h2, h3, h4, h5, h6 {
        font-family: 'Fraunces', serif !important;
        color: #0c4a6e !important;
    }

    /* 2. SIDEBAR MODERN */
    .left-sidebar {
        background: #ffffff !important;
        border-right: 1px solid var(--border-color) !important;
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.02) !important;
    }
    
    /* Sembunyikan Scrollbar Jadul, ganti dengan yang tipis */
    .sidebar-nav::-webkit-scrollbar { width: 4px; }
    .sidebar-nav::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    .brand-logo {
        padding: 24px;
        border-bottom: 1px solid var(--border-color) !important;
    }

    /* Menu Item */
    .sidebar-nav .nav-link {
        color: var(--text-muted) !important;
        font-weight: 600 !important;
        border-radius: 8px;
        margin: 2px 12px;
        padding: 10px 16px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .sidebar-nav .nav-link:hover {
        background-color: #f1f5f9 !important;
        color: var(--primary) !important;
        transform: translateX(4px); /* Efek geser halus */
    }
    
    /* Menu Aktif */
    .sidebar-nav .nav-link.active {
        background: #eff6ff !important; /* Biru sangat muda */
        color: var(--primary) !important;
        font-weight: 700 !important;
        position: relative;
    }
    /* Garis biru di kiri menu aktif */
    .sidebar-nav .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0; top: 20%; bottom: 20%;
        width: 4px;
        background: var(--primary);
        border-radius: 0 4px 4px 0;
    }

    /* HAPUS PAKSA IKON X DAN CHECK */
    .sidebar-nav .nav-link span.text-danger,
    .sidebar-nav .nav-link span.text-success,
    .sidebar-nav .nav-link i.fa-times,
    .sidebar-nav .nav-link i.fa-check,
    .sidebar-nav .nav-link i.ti-x,
    .sidebar-nav .nav-link i.ti-check {
        display: none !important;
    }

    /* 3. HEADER TOP BAR */
    .app-header {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(12px); /* Efek kaca */
        border-bottom: 1px solid var(--border-color) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02) !important;
    }
    .app-header .text-black {
        color: var(--text-dark) !important;
        font-weight: 700;
        font-size: 12.5px;
        letter-spacing: 0.5px;
    }
    .app-header img.rounded-circle {
        border: 2px solid #e2e8f0;
        transition: border-color 0.3s;
    }
    .app-header img.rounded-circle:hover {
        border-color: var(--primary);
    }

    /* 4. KARTU STATISTIK (CARD) */
    .card {
        border: 1px solid var(--border-color) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06) !important;
        transform: translateY(-2px); /* Efek melayang */
    }
    .card-title {
        font-family: 'Plus Jakarta Sans', sans-serif !important; 
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: var(--text-muted) !important;
        letter-spacing: 1px;
    }
    
    /* Angka Besar */
    .totalRekening, .totalMeterDiCatat, .totalDiTagih {
        font-family: 'Fraunces', serif !important;
        color: #0f172a !important;
        font-size: 26px !important;
        font-weight: 700 !important;
    }

    /* 5. TAB MENU */
    .nav-pills {
        gap: 8px;
        border-bottom: 1px solid var(--border-color) !important;
        padding-bottom: 16px;
    }
    .nav-pills .nav-link {
        border-radius: 8px;
        font-weight: 600 !important;
        font-size: 12.5px;
        color: var(--text-muted) !important;
        padding: 10px 18px;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link:hover {
        background: #f1f5f9 !important;
        color: var(--primary) !important;
    }
    .nav-pills .nav-link.active {
        background: var(--primary) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3) !important;
    }

    /* 6. TABEL (Paling Krusial) */
    .table-responsive {
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: #fff;
    }
    .table thead th {
        background-color: #f8fafc !important;
        color: var(--text-muted) !important;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 16px !important;
    }
    .table tbody td {
        padding: 16px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 13px;
        color: var(--text-dark);
        font-weight: 500;
        vertical-align: middle;
    }
    /* Zebra Striping (Belang-belang halus) */
    .table tbody tr:nth-child(even) {
        background-color: #fafbfc;
    }
    .table tbody tr:hover {
        background-color: #f0f9ff !important;
    }
    
    /* Badge Periode (01 Nov 2026 - 10 Nov 2026) */
    .table .badge {
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .table .badge.bg-primary { background: #38bdf8 !important; }
    .table .badge.bg-dark { background: #1e293b !important; }
    .table .badge.bg-success { background: #34d399 !important; }

    /* Tombol Aksi (Titik Tiga) */
    .table .btn-action {
        background: transparent;
        border: none;
        color: #94a3b8;
        padding: 6px;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .table .btn-action:hover {
        background: #e2e8f0;
        color: var(--text-dark);
    }

    /* 7. FORM & BUTTON */
    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        padding: 10px 14px;
    }
    .form-control:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15) !important;
    }
    
    /* Tombol PDF */
    .btn-danger {
        background: #fff1f2 !important;
        color: #e11d48 !important;
        border: 1px solid #fecdd3 !important;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        box-shadow: 0 2px 4px rgba(225, 29, 72, 0.05);
    }
    .btn-danger:hover {
        background: #ffe4e6 !important;
        transform: translateY(-1px);
    }
</style>

</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">