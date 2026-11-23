<?php

/**
 * Helper pemisahan modul aplikasi menjadi dua sistem:
 *   - billing : pelanggan, catat meter, tagihan, piutang, laporan DRD
 *   - kas     : E-Kas (kas keluar/masuk), logistik, persediaan
 *
 * Data Master (master pelanggan/tarif/wilayah untuk billing; perkiraan,
 * pegawai, jabatan, pokok untuk kas) hanya ditampilkan untuk level admin,
 * dan muncul di dalam sistem yang bersangkutan.
 *
 * Hak akses billing/kas disimpan di tabel `t_akses_sistem`
 * (lihat database/t_akses_sistem.sql). Kalau user belum punya baris di
 * tabel itu, dipakai fallback berdasarkan users.level supaya user lama
 * tidak langsung terkunci.
 *
 * File ini adalah satu-satunya sumber kebenaran untuk:
 *   - peta prefix URI -> sistem  (sistem_peta_prefix)
 *   - daftar menu tiap sistem    (sistem_menu)
 *   - hak akses user             (sistem_akses_user / sistem_boleh)
 *
 * Dipakai oleh: App\Filters\FilterSistem, sidebar, navbar,
 * App\Controllers\Home, App\Controllers\Login, App\Controllers\Master\Akun.
 */

if (!defined('SISTEM_BILLING')) {
    define('SISTEM_BILLING', 'billing');
}
if (!defined('SISTEM_KAS')) {
    define('SISTEM_KAS', 'kas');
}
// Penanda khusus: modul yang hanya boleh dibuka level admin.
if (!defined('SISTEM_ADMIN')) {
    define('SISTEM_ADMIN', 'admin');
}

if (!function_exists('sistem_daftar')) {
    /**
     * Daftar sistem yang bisa dipilih user.
     */
    function sistem_daftar(): array
    {
        return [
            SISTEM_BILLING => [
                'label' => 'Billing',
                'icon'  => 'ti ti-file-invoice',
                'home'  => 'billing',
            ],
            SISTEM_KAS => [
                'label' => 'E-Kas',
                'icon'  => 'ti ti-building-bank',
                'home'  => 'ekas',
            ],
        ];
    }
}

if (!function_exists('sistem_peta_prefix')) {
    /**
     * Peta prefix URI -> sistem. Prefix dicocokkan dari yang paling
     * panjang, jadi 'master/pelanggan' menang atas 'pelanggan'.
     *
     * Tiap path WAJIB menunjuk sistem nyata (billing/kas) supaya konteks
     * sistem tidak hilang saat membuka halaman. Pembatasan khusus admin
     * ditangani terpisah lewat sistem_prefix_admin().
     */
    function sistem_peta_prefix(): array
    {
        return [
            // ===== BILLING =====
            // Semua halaman Billing ada di bawah prefix /billing.
            // Prefix lama tetap dipetakan supaya URL lama yang belum
            // ter-redirect dan endpoint AJAX autoRoute tetap terdeteksi
            // sebagai milik sistem billing.
            'billing'                        => SISTEM_BILLING,
            'home/billing'                   => SISTEM_BILLING,
            'pelanggan'                      => SISTEM_BILLING,
            'billing/loket'                  => SISTEM_BILLING,
            'billing/tagihan'                => SISTEM_BILLING,
            'billing/catat-meter'            => SISTEM_BILLING,
            'billing/system'                 => SISTEM_BILLING,
            'tagihan'                        => SISTEM_BILLING, // halaman lama + autoRoute /Billing/Tagihan/...

            // master billing
            'master/pelanggan'               => SISTEM_BILLING,
            'master/pasang-meter'            => SISTEM_BILLING,
            'master/set-kelompok-tarif'      => SISTEM_BILLING,
            'master/set-jenis-pelanggan'     => SISTEM_BILLING,
            'master/kabupaten'               => SISTEM_BILLING,
            'master/kecamatan'               => SISTEM_BILLING,
            'master/kelurahan'               => SISTEM_BILLING,

            // laporan billing
            'laporan/drd-air'                => SISTEM_BILLING,
            'laporan/rekap-drd-air'          => SISTEM_BILLING,
            'laporan/rincian-piutang-perunit' => SISTEM_BILLING,
            'laporan/pelanggan-duplikat'     => SISTEM_BILLING,
            'laporan/rekapitulasi-piutang'   => SISTEM_BILLING,
            'laporan/lpp-berjalan'           => SISTEM_BILLING,
            'laporan/lpp-petugas'            => SISTEM_BILLING,
            'laporan/tunggakan-per-unit'     => SISTEM_BILLING,

            // ===== KAS (E-KAS) =====
            'ekas'                           => SISTEM_KAS,

            // Semua modul E-Kas berada di bawah prefix /ekas.
            // Daftar di bawah tinggal untuk URL lama; route-nya sudah
            // dialihkan 301 ke /ekas/... Supaya konteks sistem tetap benar
            // selama redirect, prefix lama tetap dipetakan ke KAS.
            'home/ekas'                      => SISTEM_KAS,
            'e-kas'                          => SISTEM_KAS, // redirect 301 ke /ekas
            'e_kas'                          => SISTEM_KAS, // autoRoute /E_Kas/...

            'logistik'                       => SISTEM_KAS, // autoRoute /Logistik/...
            'gudang'                         => SISTEM_KAS,
            'barang-keluar'                  => SISTEM_KAS,
            'barang-masuk'                   => SISTEM_KAS,
            'faktur-pembelian'               => SISTEM_KAS,
            'slip-gaji'                      => SISTEM_KAS,
            'persuratan'                     => SISTEM_KAS,

            // master kas
            'master/perkiraan'               => SISTEM_KAS,
            'master/perkiraangroup'          => SISTEM_KAS, // autoRoute /Master/PerkiraanGroup/...
            'master/bank'                    => SISTEM_KAS,
            'master/jabatan'                 => SISTEM_KAS,
            'master/tugas-pokok'             => SISTEM_KAS,
            'master/pokok'                   => SISTEM_KAS,
            'master/pegawai'                 => SISTEM_KAS,

            // laporan kas
            'laporan/rekap-persediaan'       => SISTEM_KAS,
            'laporan/daftar-hadir'           => SISTEM_KAS,
            'laporan/kinerja-pegawai'        => SISTEM_KAS,

            // kepegawaian -> ikut kas
            'absensi'                        => SISTEM_KAS,
        ];
    }
}

if (!function_exists('sistem_prefix_admin')) {
    /**
     * Prefix yang hanya boleh dibuka users.level = 'admin'.
     *
     * Modul di sini tetap milik sistem tertentu (lihat sistem_peta_prefix)
     * supaya sidebar tetap di sistem yang benar; yang dibatasi hanya
     * hak buka untuk non-admin.
     *
     * Daftar ini harus sejalan dengan grup menu yang bertanda 'admin'
     * di sistem_menu(); kalau tidak, menu bisa tersembunyi tapi URL-nya
     * tetap terbuka.
     */
    function sistem_prefix_admin(): array
    {
        return [
            // master billing (halaman berprefix /billing; URL lama tetap
            // didaftarkan supaya bookmark lama yang belum ter-redirect
            // tetap tertutup untuk non-admin).
            'billing/master',
            'billing/tagihan', // terbit rekening & cek no rek: kantor/admin
            'billing/catat-meter', // verifikasi = terbit rekening, ikut admin
            'billing/system', // pemantauan penagihan: ikut admin
            'master/pelanggan',
            'master/pasang-meter',
            'master/set-kelompok-tarif',
            'master/set-jenis-pelanggan',
            'master/kabupaten',
            'master/kecamatan',
            'master/kelurahan',

            // master kas (sekarang di bawah /ekas). Prefix lama tetap
            // didaftarkan supaya URL lama yang belum ter-redirect juga
            // ikut tertutup untuk non-admin.
            'ekas/master',
            'master/perkiraan',
            'master/perkiraangroup', // autoRoute /Master/PerkiraanGroup/...
            'master/bank',
            'master/jabatan',
            'master/tugas-pokok',
            'master/pokok',
            'master/pegawai',

            // berlaku di kedua sistem
            'master/akun',
            'master/akses',
            'master/aksesmodul', // autoRoute /Master/AksesModul/...

            // Administrasi (grup menu 'Administrasi' di kedua sistem)
            'ekas/absensi',
            'ekas/slip-gaji',
            'ekas/persuratan',
            'ekas/laporan/daftar-hadir',
            'ekas/laporan/kinerja-pegawai',
            'absensi',
            'slip-gaji',
            'persuratan',
            'laporan/daftar-hadir',
            'laporan/kinerja-pegawai',
        ];
    }
}

if (!function_exists('sistem_wajib_admin')) {
    /**
     * Apakah path ini hanya untuk admin.
     */
    function sistem_wajib_admin(?string $path): bool
    {
        $path = sistem_normalisasi_path($path);

        if ($path === '') {
            return false;
        }

        foreach (sistem_prefix_admin() as $prefix) {
            $prefix = sistem_normalisasi_path($prefix);

            if ($path === $prefix || strpos($path, $prefix . '/') === 0) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('sistem_normalisasi_path')) {
    function sistem_normalisasi_path(?string $path): string
    {
        return strtolower(trim((string) $path, '/'));
    }
}

if (!function_exists('sistem_dari_path')) {
    /**
     * Tentukan sistem dari path URI.
     *
     * @return string|null kode sistem, SISTEM_ADMIN, atau null kalau
     *                     path tidak terikat sistem (mis. /Home/get_sidebar)
     */
    function sistem_dari_path(?string $path): ?string
    {
        $path = sistem_normalisasi_path($path);

        if ($path === '') {
            return null;
        }

        $cocok   = null;
        $panjang = -1;

        foreach (sistem_peta_prefix() as $prefix => $sistem) {
            $prefix = sistem_normalisasi_path($prefix);

            if ($path === $prefix || strpos($path, $prefix . '/') === 0) {
                if (strlen($prefix) > $panjang) {
                    $panjang = strlen($prefix);
                    $cocok   = $sistem;
                }
            }
        }

        return $cocok;
    }
}

if (!function_exists('sistem_akses_default')) {
    /**
     * Akses bawaan kalau user belum punya baris di t_akses_sistem.
     * Sengaja permisif supaya user lama tidak langsung kehilangan akses.
     */
    function sistem_akses_default(?string $level): array
    {
        if ($level === 'admin') {
            return [SISTEM_BILLING, SISTEM_KAS];
        }

        // Petugas loket / penagih / catat meter / fastpay semuanya kerja
        // di sisi pelanggan & tagihan.
        return [SISTEM_BILLING];
    }
}

if (!function_exists('sistem_akses_user')) {
    /**
     * Daftar sistem yang boleh dibuka user.
     *
     * @param string|null $id_session default: session id_session
     * @param string|null $level      default: session level
     */
    function sistem_akses_user(?string $id_session = null, ?string $level = null): array
    {
        $id_session = $id_session ?: session('id_session');
        $level      = $level ?: session('level');

        if (empty($id_session)) {
            return [];
        }

        if ($level === 'admin') {
            return [SISTEM_BILLING, SISTEM_KAS];
        }

        $akses = [];

        try {
            $rows = \Config\Database::connect()
                ->table('t_akses_sistem')
                ->select('sistem')
                ->where('id_session', $id_session)
                ->get()
                ->getResultArray();

            $akses = array_column($rows, 'sistem');
        } catch (\Throwable $e) {
            // Tabel belum dibuat / DB bermasalah: pakai fallback level.
            log_message('warning', 't_akses_sistem tidak bisa dibaca: ' . $e->getMessage());
        }

        $akses = array_values(array_unique(array_filter($akses, static function ($s) {
            return isset(sistem_daftar()[$s]);
        })));

        if (empty($akses)) {
            $akses = sistem_akses_default($level);
        }

        return $akses;
    }
}

if (!function_exists('sistem_boleh')) {
    /**
     * Cek apakah user sekarang boleh membuka sistem tertentu.
     */
    function sistem_boleh(string $sistem): bool
    {
        if (!isset(sistem_daftar()[$sistem])) {
            return false;
        }

        if (session('level') === 'admin') {
            return true;
        }

        return in_array($sistem, sistem_akses_user(), true);
    }
}

if (!function_exists('sistem_boleh_path')) {
    /**
     * Cek hak akses untuk sebuah path URI: sistemnya harus boleh dibuka
     * user, modulnya (kalau terdaftar) harus boleh dibuka, dan kalau path
     * itu modul admin, levelnya harus admin.
     */
    function sistem_boleh_path(string $path): bool
    {
        if (sistem_wajib_admin($path) && session('level') !== 'admin') {
            return false;
        }

        $sistem = sistem_dari_path($path);

        if ($sistem === null) {
            return true;
        }

        if (!sistem_boleh($sistem)) {
            return false;
        }

        // Modul hanya dibatasi kalau user memang sudah punya daftar modul
        // untuk sistem ini (lihat sistem_akses_modul).
        return sistem_boleh_modul($sistem, sistem_modul_dari_path($path));
    }
}

if (!function_exists('sistem_aktif')) {
    /**
     * Sistem yang sedang dipakai.
     *
     * Urutan penentuan:
     *   1. dari URI (semua path modul sudah dipetakan ke billing/kas)
     *   2. sistem terakhir yang dibuka (disimpan FilterSistem di session),
     *      supaya halaman non-modul seperti /Home/get_sidebar tidak
     *      melempar sidebar kembali ke billing
     *   3. sistem pertama yang boleh dibuka user
     */
    function sistem_aktif(?string $path = null): string
    {
        if ($path === null) {
            try {
                $path = service('request')->getUri()->getPath();
            } catch (\Throwable $e) {
                $path = '';
            }
        }

        $sistem = sistem_dari_path($path);

        if ($sistem !== null) {
            return $sistem;
        }

        $tersimpan = session('sistem_aktif');

        if (!empty($tersimpan) && isset(sistem_daftar()[$tersimpan]) && sistem_boleh($tersimpan)) {
            return $tersimpan;
        }

        $akses = sistem_akses_user();

        return $akses[0] ?? SISTEM_BILLING;
    }
}

if (!function_exists('sistem_home_url')) {
    /**
     * URL dashboard tiap sistem.
     */
    function sistem_home_url(string $sistem): string
    {
        $daftar = sistem_daftar();

        return base_url($daftar[$sistem]['home'] ?? 'billing');
    }
}

if (!function_exists('sistem_lain')) {
    /**
     * Sistem lain yang juga boleh dibuka user (untuk tombol pindah sistem).
     */
    function sistem_lain(): array
    {
        $aktif = sistem_aktif();

        return array_values(array_diff(sistem_akses_user(), [$aktif]));
    }
}

if (!function_exists('sistem_menu')) {
    /**
     * Struktur menu sidebar per sistem.
     *
     * Bentuk tiap item:
     *   label    : teks menu
     *   url      : base_url() relatif (kosong kalau punya children)
     *   icon     : kelas ikon fontawesome
     *   id       : id elemen (opsional)
     *   children : array item anak (menu collapse)
     *   prefix   : prefix path untuk menandai menu aktif
     *
     * Tiap grup:
     *   judul : judul grup di sidebar
     *   admin : true = hanya tampil untuk level admin (Data Master/Administrasi)
     *   items : daftar item
     */
    function sistem_menu(string $sistem): array
    {
        if ($sistem === SISTEM_KAS) {
            return [
                [
                    'judul' => 'Home',
                    'items' => [
                        ['label' => 'Dashboard', 'url' => 'ekas', 'icon' => 'ti ti-layout-dashboard', 'id' => 'dashboard_menu', 'prefix' => 'ekas', 'modul' => 'kas.home', 'selalu' => true],
                    ],
                ],
                [
                    'judul' => 'Kas',
                    'items' => [
                        [
                            'label'    => 'Kas Keluar',
                            'icon'     => 'ti ti-cash-banknote',
                            'prefix'   => 'ekas/keluar|ekas/riwayat',
                            'children' => [
                                ['label' => 'Kas Pengeluaran', 'url' => 'ekas/keluar', 'icon' => 'ti ti-receipt-2', 'prefix' => 'ekas/keluar', 'modul' => 'kas.keluar'],
                                ['label' => 'Riwayat Kas Pengeluaran', 'url' => 'ekas/riwayat', 'icon' => 'ti ti-history', 'prefix' => 'ekas/riwayat', 'modul' => 'kas.riwayat'],
                            ],
                        ],
                        [
                            'label'    => 'Kas Masuk',
                            'icon'     => 'ti ti-cash',
                            'prefix'   => 'ekas/masuk',
                            'children' => [
                                ['label' => 'Penerimaan Kas Air', 'url' => 'ekas/masuk', 'icon' => 'ti ti-droplet', 'prefix' => 'ekas/masuk', 'modul' => 'kas.masuk'],
                                ['label' => 'Penerimaan Kas Non Air', 'url' => 'ekas/masuk-non-air', 'icon' => 'ti ti-receipt', 'prefix' => 'ekas/masuk-non-air', 'modul' => 'kas.masuk_non_air'],
                            ],
                        ],
                    ],
                ],
                [
                    'judul' => 'Logistik',
                    'items' => [
                        ['label' => 'Faktur Pembelian', 'url' => 'ekas/faktur-pembelian', 'icon' => 'ti ti-file-invoice', 'id' => 'faktur_pembelian_menu', 'prefix' => 'ekas/faktur-pembelian|ekas/barang-masuk', 'modul' => 'kas.faktur_pembelian'],
                        ['label' => 'Entry Permintaan', 'url' => 'ekas/barang-keluar/permintaan', 'icon' => 'ti ti-clipboard-plus', 'id' => 'entry_permintaan_menu', 'prefix' => 'ekas/barang-keluar/permintaan', 'modul' => 'kas.permintaan'],
                        [
                            'label'    => 'Gudang',
                            'icon'     => 'ti ti-building-warehouse',
                            'prefix'   => 'ekas/gudang',
                            'children' => [
                                ['label' => 'Data Barang', 'url' => 'ekas/gudang/barang', 'icon' => 'ti ti-packages', 'prefix' => 'ekas/gudang/barang', 'modul' => 'kas.gudang.barang'],
                                ['label' => 'Cek Permintaan', 'url' => 'ekas/gudang/cek-permintaan', 'icon' => 'ti ti-clipboard-check', 'prefix' => 'ekas/gudang/cek-permintaan', 'modul' => 'kas.gudang.cek_permintaan'],
                                ['label' => 'Opname Stok', 'url' => 'ekas/gudang/opname-stok', 'icon' => 'ti ti-clipboard-list', 'prefix' => 'ekas/gudang/opname-stok', 'modul' => 'kas.gudang.opname'],
                            ],
                        ],
                    ],
                ],
                [
                    'judul' => 'Laporan',
                    'items' => [
                        ['label' => 'Rekap Persediaan', 'url' => 'ekas/laporan/rekap-persediaan', 'icon' => 'ti ti-chart-pie', 'prefix' => 'ekas/laporan/rekap-persediaan', 'modul' => 'kas.laporan.rekap_persediaan'],
                    ],
                ],
                [
                    'judul' => 'Data Master',
                    'admin' => true,
                    'items' => [
                        [
                            'label'    => 'Master Keuangan',
                            'icon'     => 'ti ti-wallet',
                            'prefix'   => 'ekas/master/perkiraan|ekas/master/perkiraan-group|ekas/master/bank',
                            'children' => [
                                ['label' => 'Perkiraan', 'url' => 'ekas/master/perkiraan', 'icon' => 'ti ti-report-money', 'prefix' => 'ekas/master/perkiraan'],
                                ['label' => 'Grup Perkiraan', 'url' => 'ekas/master/perkiraan-group', 'icon' => 'ti ti-stack-2', 'prefix' => 'ekas/master/perkiraan-group'],
                                // Master Bank disembunyikan: tabel `master_bank`
                                // belum ada di db_pdam.
                            ],
                        ],
                        [
                            'label'    => 'Master Pegawai',
                            'icon'     => 'ti ti-id-badge',
                            'prefix'   => 'ekas/master/jabatan|ekas/master/pokok|ekas/master/tugas-pokok|ekas/master/pegawai',
                            'children' => [
                                ['label' => 'Jabatan', 'url' => 'ekas/master/jabatan', 'icon' => 'ti ti-hierarchy', 'prefix' => 'ekas/master/jabatan'],
                                ['label' => 'Tugas Pokok', 'url' => 'ekas/master/pokok', 'icon' => 'ti ti-list-details', 'prefix' => 'ekas/master/pokok|ekas/master/tugas-pokok'],
                                ['label' => 'Data Pegawai', 'url' => 'ekas/master/pegawai', 'icon' => 'ti ti-users', 'prefix' => 'ekas/master/pegawai'],
                            ],
                        ],
                    ],
                ],
                [
                    'judul' => 'Administrasi',
                    'admin' => true,
                    'items' => [
                        ['label' => 'User/Akun', 'url' => 'master/akun', 'icon' => 'ti ti-shield-lock', 'id' => 'akun_menu', 'prefix' => 'master/akun'],
                        ['label' => 'Hak Akses Modul', 'url' => 'master/akses', 'icon' => 'ti ti-shield-check', 'id' => 'akses_menu', 'prefix' => 'master/akses'],
                        ['label' => 'Absensi', 'url' => 'ekas/absensi', 'icon' => 'ti ti-calendar-stats', 'id' => 'absensi_menu', 'prefix' => 'ekas/absensi'],
                        ['label' => 'Daftar Hadir Karyawan', 'url' => 'ekas/laporan/daftar-hadir', 'icon' => 'ti ti-calendar-event', 'prefix' => 'ekas/laporan/daftar-hadir'],
                        ['label' => 'Kinerja Pegawai', 'url' => 'ekas/laporan/kinerja-pegawai', 'icon' => 'ti ti-report-analytics', 'prefix' => 'ekas/laporan/kinerja-pegawai'],
                        ['label' => 'Slip Gaji', 'url' => 'ekas/slip-gaji', 'icon' => 'ti ti-file-dollar', 'prefix' => 'ekas/slip-gaji'],
                        ['label' => 'Persuratan', 'url' => 'ekas/persuratan', 'icon' => 'ti ti-mailbox', 'prefix' => 'ekas/persuratan'],
                    ],
                ],
            ];
        }

        // ===== BILLING =====
        return [
            [
                'judul' => 'Home',
                'items' => [
                    ['label' => 'Dashboard', 'url' => 'billing', 'icon' => 'ti ti-layout-dashboard', 'id' => 'dashboard_menu', 'prefix' => 'billing', 'modul' => 'billing.home', 'selalu' => true],
                ],
            ],
            [
                'judul' => 'Billing',
                'items' => [
                    ['label' => 'Pelanggan', 'url' => 'billing/pelanggan', 'icon' => 'ti ti-users', 'id' => 'pelanggan_menu', 'prefix' => 'billing/pelanggan', 'modul' => 'billing.pelanggan'],
                    ['label' => 'Loket Pembayaran', 'url' => 'billing/loket', 'icon' => 'ti ti-cash-banknote', 'id' => 'loket_menu', 'prefix' => 'billing/loket', 'modul' => 'billing.loket'],
                ],
            ],
            [
                // Terbit rekening = tugas kantor, sejalan dengan bs yang
                // hanya menaruhnya di menu super admin / kantor.
                // Grup ini admin-only, jadi modulnya tidak bisa diatur
                // lewat Hak Akses Modul (lihat sistem_modul()).
                'judul' => 'Tagihan (Kantor)',
                'admin' => true,
                'items' => [
                    ['label' => 'Tagihan', 'url' => 'billing/tagihan', 'icon' => 'ti ti-file-invoice', 'id' => 'tagihan_menu', 'prefix' => 'billing/tagihan'],
                    ['label' => 'Catat Meter', 'url' => 'billing/catat-meter', 'icon' => 'ti ti-gauge', 'id' => 'catat_meter_menu', 'prefix' => 'billing/catat-meter'],
                    ['label' => 'Billing System', 'url' => 'billing/system', 'icon' => 'ti ti-activity', 'id' => 'billing_system_menu', 'prefix' => 'billing/system'],
                    ['label' => 'Cek No. Rekening', 'url' => 'billing/tagihan/duplikat', 'icon' => 'ti ti-receipt-2', 'prefix' => 'billing/tagihan/duplikat'],
                ],
            ],
            [
                'judul' => 'Data Master',
                'admin' => true,
                'items' => [
                    [
                        'label'    => 'Master Pelanggan',
                        'icon'     => 'ti ti-user-circle',
                        'prefix'   => 'billing/master/pelanggan|billing/master/pasang-meter|billing/master/set-kelompok-tarif|billing/master/set-jenis-pelanggan',
                        'children' => [
                            ['label' => 'Daftar Pelanggan', 'url' => 'billing/master/pelanggan', 'icon' => 'ti ti-users', 'prefix' => 'billing/master/pelanggan'],
                            ['label' => 'Kelompok Tarif', 'url' => 'billing/master/set-kelompok-tarif', 'icon' => 'ti ti-tags', 'prefix' => 'billing/master/set-kelompok-tarif'],
                            ['label' => 'Jenis Pelanggan', 'url' => 'billing/master/set-jenis-pelanggan', 'icon' => 'ti ti-copy', 'prefix' => 'billing/master/set-jenis-pelanggan'],
                        ],
                    ],
                    [
                        'label'    => 'Master Wilayah',
                        'icon'     => 'ti ti-map-pins',
                        'prefix'   => 'billing/master/kabupaten|billing/master/kecamatan|billing/master/kelurahan',
                        'children' => [
                            ['label' => 'Kabupaten', 'url' => 'billing/master/kabupaten', 'icon' => 'ti ti-building-community', 'prefix' => 'billing/master/kabupaten'],
                            ['label' => 'Kecamatan', 'url' => 'billing/master/kecamatan', 'icon' => 'ti ti-map-2', 'prefix' => 'billing/master/kecamatan'],
                            ['label' => 'Kelurahan', 'url' => 'billing/master/kelurahan', 'icon' => 'ti ti-map-pin', 'prefix' => 'billing/master/kelurahan'],
                        ],
                    ],
                ],
            ],
            [
                'judul' => 'Laporan',
                'items' => [
                    ['label' => 'DRD Air', 'url' => 'billing/laporan/drd-air', 'icon' => 'ti ti-droplet', 'prefix' => 'billing/laporan/drd-air', 'modul' => 'billing.laporan.drd_air'],
                    ['label' => 'DRD Unit/Jenis', 'url' => 'billing/laporan/rekap-drd-air', 'icon' => 'ti ti-chart-pie', 'prefix' => 'billing/laporan/rekap-drd-air', 'modul' => 'billing.laporan.rekap_drd'],
                    ['label' => 'Rincian Piutang Perunit', 'url' => 'billing/laporan/rincian-piutang-perunit', 'icon' => 'ti ti-chart-dots', 'prefix' => 'billing/laporan/rincian-piutang-perunit', 'modul' => 'billing.laporan.piutang_perunit'],
                    ['label' => 'Pelanggan Duplikat', 'url' => 'billing/laporan/pelanggan-duplikat', 'icon' => 'ti ti-file-analytics', 'prefix' => 'billing/laporan/pelanggan-duplikat', 'modul' => 'billing.laporan.pelanggan_duplikat'],
                    ['label' => 'Rekapitulasi Piutang', 'url' => 'billing/laporan/rekapitulasi-piutang', 'icon' => 'ti ti-report-analytics', 'prefix' => 'billing/laporan/rekapitulasi-piutang', 'modul' => 'billing.laporan.rekapitulasi_piutang'],
                    ['label' => 'LPP Air Periodik', 'url' => 'billing/laporan/lpp-berjalan', 'icon' => 'ti ti-file-report', 'prefix' => 'billing/laporan/lpp-berjalan', 'modul' => 'billing.laporan.lpp_berjalan'],
                    ['label' => 'LPP Per Petugas', 'url' => 'billing/laporan/lpp-petugas', 'icon' => 'ti ti-user-check', 'prefix' => 'billing/laporan/lpp-petugas', 'modul' => 'billing.laporan.lpp_petugas'],
                    ['label' => 'Rincian Tunggakan per Unit', 'url' => 'billing/laporan/tunggakan-per-unit', 'icon' => 'ti ti-file-search', 'prefix' => 'billing/laporan/tunggakan-per-unit', 'modul' => 'billing.laporan.tunggakan_unit'],
                ],
            ],
            [
                'judul' => 'Administrasi',
                'admin' => true,
                'items' => [
                    ['label' => 'User/Akun', 'url' => 'master/akun', 'icon' => 'ti ti-shield-lock', 'id' => 'akun_menu', 'prefix' => 'master/akun'],
                    ['label' => 'Hak Akses Modul', 'url' => 'master/akses', 'icon' => 'ti ti-shield-check', 'id' => 'akses_menu', 'prefix' => 'master/akses'],
                ],
            ],
        ];
    }
}

if (!function_exists('sistem_modul')) {
    /**
     * Daftar modul yang bisa diatur per user.
     *
     * Sumbernya menu tiap sistem (sistem_menu()), memakai kunci 'modul'
     * pada tiap item. Grup bertanda admin TIDAK masuk daftar: modul itu
     * tetap hanya untuk level admin dan tidak bisa dibuka lewat
     * t_akses_modul.
     *
     * Modul bertanda 'selalu' (dashboard) tidak bisa dicabut — kalau
     * dicabut, redirect setelah login akan berputar tanpa henti.
     *
     * @return array<string, array{sistem:string,label:string,prefix:string,grup:string,selalu:bool}>
     */
    function sistem_modul(): array
    {
        static $daftar = null;

        if ($daftar !== null) {
            return $daftar;
        }

        $daftar = [];

        foreach (array_keys(sistem_daftar()) as $sistem) {
            foreach (sistem_menu($sistem) as $grup) {
                if (!empty($grup['admin'])) {
                    continue;
                }

                foreach ($grup['items'] as $item) {
                    $semua = array_merge([$item], $item['children'] ?? []);

                    foreach ($semua as $it) {
                        if (empty($it['modul'])) {
                            continue;
                        }

                        $daftar[$it['modul']] = [
                            'sistem' => $sistem,
                            'label'  => $it['label'] ?? $it['modul'],
                            'prefix' => $it['prefix'] ?? $it['url'] ?? '',
                            'grup'   => $grup['judul'] ?? '',
                            'selalu' => !empty($it['selalu']),
                        ];
                    }
                }
            }
        }

        return $daftar;
    }
}

if (!function_exists('sistem_modul_per_sistem')) {
    /**
     * Daftar modul satu sistem, dikelompokkan per grup menu.
     *
     * @return array<string, list<array{modul:string,label:string,selalu:bool}>>
     */
    function sistem_modul_per_sistem(string $sistem): array
    {
        $hasil = [];

        foreach (sistem_modul() as $kode => $info) {
            if ($info['sistem'] !== $sistem) {
                continue;
            }

            // Dashboard ('selalu') tidak bisa dicabut, jadi tidak perlu
            // ditampilkan di form maupun dihitung sebagai modul.
            if ($info['selalu']) {
                continue;
            }

            $hasil[$info['grup']][] = [
                'modul'  => $kode,
                'label'  => $info['label'],
                'selalu' => false,
            ];
        }

        return $hasil;
    }
}

if (!function_exists('sistem_modul_dari_path')) {
    /**
     * Modul yang menaungi sebuah path URI (prefix terpanjang menang).
     *
     * @return string|null kode modul, atau null kalau path tidak terdaftar
     */
    function sistem_modul_dari_path(?string $path): ?string
    {
        $path = sistem_normalisasi_path($path);

        if ($path === '') {
            return null;
        }

        $cocok   = null;
        $panjang = -1;

        foreach (sistem_modul() as $kode => $info) {
            foreach (explode('|', (string) $info['prefix']) as $prefix) {
                $prefix = sistem_normalisasi_path($prefix);

                if ($prefix === '') {
                    continue;
                }

                if ($path === $prefix) {
                    if (strlen($prefix) > $panjang) {
                        $panjang = strlen($prefix);
                        $cocok   = $kode;
                    }

                    continue;
                }

                // Modul 'selalu' (dashboard) hanya mencakup halaman depannya
                // sendiri. Kalau prefix-nya ikut menaungi sub-path, semua
                // halaman yang belum punya modul akan lolos lewat dashboard.
                if ($info['selalu']) {
                    continue;
                }

                if (strpos($path, $prefix . '/') === 0 && strlen($prefix) > $panjang) {
                    $panjang = strlen($prefix);
                    $cocok   = $kode;
                }
            }
        }

        return $cocok;
    }
}

if (!function_exists('sistem_akses_modul')) {
    /**
     * Modul yang boleh dibuka user pada satu sistem.
     *
     * Array kosong berarti BELUM dibatasi: semua modul sistem itu boleh
     * (perilaku lama). Baris di t_akses_modul mempersempit.
     *
     * @return list<string>
     */
    function sistem_akses_modul(?string $id_session, string $sistem): array
    {
        static $cache = [];

        $id_session = $id_session ?: session('id_session');

        if (empty($id_session)) {
            return [];
        }

        $kunci = $id_session . '|' . $sistem;

        if (array_key_exists($kunci, $cache)) {
            return $cache[$kunci];
        }

        try {
            $rows = \Config\Database::connect()
                ->table('t_akses_modul')
                ->select('modul')
                ->where('id_session', $id_session)
                ->where('sistem', $sistem)
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            // Tabel belum dibuat: jangan kunci user keluar.
            log_message('warning', 't_akses_modul tidak bisa dibaca: ' . $e->getMessage());

            return $cache[$kunci] = [];
        }

        return $cache[$kunci] = array_values(array_unique(array_column($rows, 'modul')));
    }
}

if (!function_exists('sistem_boleh_modul')) {
    /**
     * Apakah user sekarang boleh membuka satu modul.
     */
    function sistem_boleh_modul(string $sistem, ?string $modul): bool
    {
        if ($modul === null) {
            return true;
        }

        if (session('level') === 'admin') {
            return true;
        }

        $info = sistem_modul()[$modul] ?? null;

        if ($info === null) {
            // Modul tak dikenal (menu admin / belum terdaftar): jangan
            // pernah diloloskan lewat jalur ini.
            return false;
        }

        if ($info['selalu']) {
            return true;
        }

        $akses = sistem_akses_modul(null, $sistem);

        if (empty($akses)) {
            return true;
        }

        return in_array($modul, $akses, true);
    }
}

if (!function_exists('sistem_menu_terlihat')) {
    /**
     * sistem_menu() yang sudah disaring hak akses user:
     * grup admin disembunyikan untuk non-admin, item yang modulnya tidak
     * boleh dibuka dibuang, grup kosong ikut hilang.
     */
    function sistem_menu_terlihat(string $sistem): array
    {
        $level = session('level');
        $hasil = [];

        foreach (sistem_menu($sistem) as $grup) {
            if (!empty($grup['admin']) && $level !== 'admin') {
                continue;
            }

            $items = [];

            foreach ($grup['items'] as $item) {
                if (!empty($item['modul']) && !sistem_boleh_modul($sistem, $item['modul'])) {
                    continue;
                }

                if (!empty($item['children'])) {
                    $anak = [];

                    foreach ($item['children'] as $c) {
                        if (!empty($c['modul']) && !sistem_boleh_modul($sistem, $c['modul'])) {
                            continue;
                        }

                        $anak[] = $c;
                    }

                    // Item induk tanpa anak tersisa tidak berguna.
                    if (empty($anak)) {
                        continue;
                    }

                    $item['children'] = $anak;
                }

                $items[] = $item;
            }

            if (empty($items)) {
                continue;
            }

            $grup['items'] = $items;
            $hasil[]       = $grup;
        }

        return $hasil;
    }
}

if (!function_exists('sistem_menu_flat')) {
    /**
     * Ratakan menu jadi daftar item (untuk sidebar tanpa grup / keperluan lain).
     */
    function sistem_menu_flat(string $sistem): array
    {
        $hasil = [];

        foreach (sistem_menu($sistem) as $grup) {
            foreach ($grup['items'] as $item) {
                $hasil[] = $item;

                foreach ($item['children'] ?? [] as $anak) {
                    $hasil[] = $anak;
                }
            }
        }

        return $hasil;
    }
}
