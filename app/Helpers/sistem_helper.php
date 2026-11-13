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
                'home'  => 'kas',
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
            'billing'                        => SISTEM_BILLING,
            'home/billing'                   => SISTEM_BILLING,
            'pelanggan'                      => SISTEM_BILLING,

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

            // ===== KAS (E-KAS) =====
            'kas'                            => SISTEM_KAS,
            'home/ekas'                      => SISTEM_KAS,
            'e-kas'                          => SISTEM_KAS,
            'e_kas'                          => SISTEM_KAS, // autoRoute /E_Kas/...
            'logistik'                       => SISTEM_KAS, // autoRoute /Logistik/...
            'gudang'                         => SISTEM_KAS,
            'barang-keluar'                  => SISTEM_KAS,
            'barang-masuk'                   => SISTEM_KAS,
            'faktur-pembelian'               => SISTEM_KAS,

            // master kas
            'master/perkiraan'               => SISTEM_KAS,
            'master/bank'                    => SISTEM_KAS,
            'master/jabatan'                 => SISTEM_KAS,
            'master/tugas-pokok'             => SISTEM_KAS,
            'master/pokok'                   => SISTEM_KAS,
            'master/pegawai'                 => SISTEM_KAS,

            // laporan kas
            'laporan/rekap-persediaan'       => SISTEM_KAS,

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
     */
    function sistem_prefix_admin(): array
    {
        return [
            'master/pelanggan',
            'master/pasang-meter',
            'master/set-kelompok-tarif',
            'master/set-jenis-pelanggan',
            'master/kabupaten',
            'master/kecamatan',
            'master/kelurahan',
            'master/perkiraan',
            'master/bank',
            'master/jabatan',
            'master/tugas-pokok',
            'master/pokok',
            'master/pegawai',
            'master/akun',
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
     * user, dan kalau path itu modul admin, levelnya harus admin.
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

        return sistem_boleh($sistem);
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
                        ['label' => 'Dashboard', 'url' => 'kas', 'icon' => 'ti ti-layout-dashboard', 'id' => 'dashboard_menu', 'prefix' => 'kas'],
                    ],
                ],
                [
                    'judul' => 'Kas',
                    'items' => [
                        [
                            'label'    => 'Kas Keluar',
                            'icon'     => 'ti ti-cash-banknote',
                            'prefix'   => 'e-kas/keluar|e-kas/riwayat',
                            'children' => [
                                ['label' => 'Kas Pengeluaran', 'url' => 'e-kas/keluar', 'icon' => 'ti ti-receipt-2', 'prefix' => 'e-kas/keluar'],
                                ['label' => 'Riwayat Kas Pengeluaran', 'url' => 'e-kas/riwayat', 'icon' => 'ti ti-history', 'prefix' => 'e-kas/riwayat'],
                            ],
                        ],
                        // Kas Masuk (penerimaan kas air / non air) belum ada
                        // controller-nya, jadi belum ditampilkan.
                    ],
                ],
                [
                    'judul' => 'Logistik',
                    'items' => [
                        ['label' => 'Faktur Pembelian', 'url' => 'faktur-pembelian', 'icon' => 'ti ti-file-invoice', 'id' => 'faktur_pembelian_menu', 'prefix' => 'faktur-pembelian|barang-masuk'],
                        ['label' => 'Entry Permintaan', 'url' => 'barang-keluar/permintaan', 'icon' => 'ti ti-clipboard-plus', 'id' => 'entry_permintaan_menu', 'prefix' => 'barang-keluar/permintaan'],
                        [
                            'label'    => 'Gudang',
                            'icon'     => 'ti ti-building-warehouse',
                            'prefix'   => 'gudang',
                            'children' => [
                                ['label' => 'Data Barang', 'url' => 'gudang/barang', 'icon' => 'ti ti-packages', 'prefix' => 'gudang/barang'],
                                ['label' => 'Cek Permintaan', 'url' => 'gudang/cek-permintaan', 'icon' => 'ti ti-clipboard-check', 'prefix' => 'gudang/cek-permintaan'],
                                ['label' => 'Opname Stok', 'url' => 'gudang/opname-stok', 'icon' => 'ti ti-clipboard-list', 'prefix' => 'gudang/opname-stok'],
                            ],
                        ],
                    ],
                ],
                [
                    'judul' => 'Laporan',
                    'items' => [
                        ['label' => 'Rekap Persediaan', 'url' => 'laporan/rekap-persediaan', 'icon' => 'ti ti-chart-pie', 'prefix' => 'laporan/rekap-persediaan'],
                    ],
                ],
                [
                    'judul' => 'Data Master',
                    'admin' => true,
                    'items' => [
                        [
                            'label'    => 'Master Keuangan',
                            'icon'     => 'ti ti-wallet',
                            'prefix'   => 'master/perkiraan|master/bank',
                            'children' => [
                                ['label' => 'Perkiraan', 'url' => 'master/perkiraan', 'icon' => 'ti ti-report-money', 'prefix' => 'master/perkiraan'],
                                // Master Bank disembunyikan: tabel `master_bank`
                                // belum ada di db_pdam.
                            ],
                        ],
                        [
                            'label'    => 'Master Pegawai',
                            'icon'     => 'ti ti-id-badge',
                            'prefix'   => 'master/jabatan|master/pokok|master/tugas-pokok|master/pegawai',
                            'children' => [
                                ['label' => 'Jabatan', 'url' => 'master/jabatan', 'icon' => 'ti ti-hierarchy', 'prefix' => 'master/jabatan'],
                                ['label' => 'Tugas Pokok', 'url' => 'master/pokok', 'icon' => 'ti ti-list-details', 'prefix' => 'master/pokok|master/tugas-pokok'],
                                ['label' => 'Data Pegawai', 'url' => 'master/pegawai', 'icon' => 'ti ti-users', 'prefix' => 'master/pegawai'],
                            ],
                        ],
                    ],
                ],
                [
                    'judul' => 'Administrasi',
                    'admin' => true,
                    'items' => [
                        ['label' => 'User/Akun', 'url' => 'master/akun', 'icon' => 'ti ti-shield-lock', 'id' => 'akun_menu', 'prefix' => 'master/akun'],
                        ['label' => 'Absensi', 'url' => 'absensi', 'icon' => 'ti ti-calendar-stats', 'id' => 'absensi_menu', 'prefix' => 'absensi'],
                    ],
                ],
            ];
        }

        // ===== BILLING =====
        return [
            [
                'judul' => 'Home',
                'items' => [
                    ['label' => 'Dashboard', 'url' => 'billing', 'icon' => 'ti ti-layout-dashboard', 'id' => 'dashboard_menu', 'prefix' => 'billing'],
                ],
            ],
            [
                'judul' => 'Billing',
                'items' => [
                    ['label' => 'Pelanggan', 'url' => 'pelanggan', 'icon' => 'ti ti-users', 'id' => 'pelanggan_menu', 'prefix' => 'pelanggan'],
                ],
            ],
            [
                'judul' => 'Data Master',
                'admin' => true,
                'items' => [
                    [
                        'label'    => 'Master Pelanggan',
                        'icon'     => 'ti ti-user-circle',
                        'prefix'   => 'master/pelanggan|master/pasang-meter|master/set-kelompok-tarif|master/set-jenis-pelanggan',
                        'children' => [
                            ['label' => 'Daftar Pelanggan', 'url' => 'master/pelanggan', 'icon' => 'ti ti-users', 'prefix' => 'master/pelanggan'],
                            ['label' => 'Kelompok Tarif', 'url' => 'master/set-kelompok-tarif', 'icon' => 'ti ti-tags', 'prefix' => 'master/set-kelompok-tarif'],
                            ['label' => 'Jenis Pelanggan', 'url' => 'master/set-jenis-pelanggan', 'icon' => 'ti ti-copy', 'prefix' => 'master/set-jenis-pelanggan'],
                        ],
                    ],
                    [
                        'label'    => 'Master Wilayah',
                        'icon'     => 'ti ti-map-pins',
                        'prefix'   => 'master/kabupaten|master/kecamatan|master/kelurahan',
                        'children' => [
                            ['label' => 'Kabupaten', 'url' => 'master/kabupaten', 'icon' => 'ti ti-building-community', 'prefix' => 'master/kabupaten'],
                            ['label' => 'Kecamatan', 'url' => 'master/kecamatan', 'icon' => 'ti ti-map-2', 'prefix' => 'master/kecamatan'],
                            ['label' => 'Kelurahan', 'url' => 'master/kelurahan', 'icon' => 'ti ti-map-pin', 'prefix' => 'master/kelurahan'],
                        ],
                    ],
                ],
            ],
            [
                'judul' => 'Laporan',
                'items' => [
                    ['label' => 'DRD Air', 'url' => 'laporan/drd-air', 'icon' => 'ti ti-droplet', 'prefix' => 'laporan/drd-air'],
                    ['label' => 'DRD Unit/Jenis', 'url' => 'laporan/rekap-drd-air', 'icon' => 'ti ti-chart-pie', 'prefix' => 'laporan/rekap-drd-air'],
                    ['label' => 'Rincian Piutang Perunit', 'url' => 'laporan/rincian-piutang-perunit', 'icon' => 'ti ti-chart-dots', 'prefix' => 'laporan/rincian-piutang-perunit'],
                    ['label' => 'Pelanggan Duplikat', 'url' => 'laporan/pelanggan-duplikat', 'icon' => 'ti ti-file-analytics', 'prefix' => 'laporan/pelanggan-duplikat'],
                    ['label' => 'Rekapitulasi Piutang', 'url' => 'laporan/rekapitulasi-piutang', 'icon' => 'ti ti-report-analytics', 'prefix' => 'laporan/rekapitulasi-piutang'],
                ],
            ],
            [
                'judul' => 'Administrasi',
                'admin' => true,
                'items' => [
                    ['label' => 'User/Akun', 'url' => 'master/akun', 'icon' => 'ti ti-shield-lock', 'id' => 'akun_menu', 'prefix' => 'master/akun'],
                ],
            ],
        ];
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
