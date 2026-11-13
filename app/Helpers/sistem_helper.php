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
                'icon'  => 'fa-solid fa-file-invoice-dollar',
                'home'  => 'billing',
            ],
            SISTEM_KAS => [
                'label' => 'E-Kas',
                'icon'  => 'fa-solid fa-cash-register',
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
     * Nilai SISTEM_ADMIN berarti modul hanya untuk level admin
     * (dipakai di kedua sistem).
     */
    function sistem_peta_prefix(): array
    {
        return [
            // ===== BILLING =====
            'billing'                        => SISTEM_BILLING,
            'home/billing'                   => SISTEM_BILLING,
            'pelanggan'                      => SISTEM_BILLING,

            // master billing
            'master/pelanggan'               => SISTEM_ADMIN,
            'master/pasang-meter'            => SISTEM_ADMIN,
            'master/set-kelompok-tarif'      => SISTEM_ADMIN,
            'master/set-jenis-pelanggan'     => SISTEM_ADMIN,
            'master/kabupaten'               => SISTEM_ADMIN,
            'master/kecamatan'               => SISTEM_ADMIN,
            'master/kelurahan'               => SISTEM_ADMIN,

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
            'master/perkiraan'               => SISTEM_ADMIN,
            'master/bank'                    => SISTEM_ADMIN,
            'master/jabatan'                 => SISTEM_ADMIN,
            'master/tugas-pokok'             => SISTEM_ADMIN,
            'master/pokok'                   => SISTEM_ADMIN,
            'master/pegawai'                 => SISTEM_ADMIN,

            // laporan kas
            'laporan/rekap-persediaan'       => SISTEM_KAS,

            // kepegawaian -> ikut kas
            'absensi'                        => SISTEM_KAS,

            // ===== ADMIN (berlaku di kedua sistem) =====
            'master/akun'                    => SISTEM_ADMIN,
        ];
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
     * Cek apakah user sekarang boleh membuka sistem/modul tertentu.
     */
    function sistem_boleh(string $sistem): bool
    {
        if ($sistem === SISTEM_ADMIN) {
            return session('level') === 'admin';
        }

        if (!isset(sistem_daftar()[$sistem])) {
            return false;
        }

        if (session('level') === 'admin') {
            return true;
        }

        return in_array($sistem, sistem_akses_user(), true);
    }
}

if (!function_exists('sistem_aktif')) {
    /**
     * Sistem yang sedang dipakai, ditentukan dari URI. Kalau URI tidak
     * terikat sistem, pakai sistem pertama yang boleh diakses user.
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

        if ($sistem !== null && $sistem !== SISTEM_ADMIN) {
            return $sistem;
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
                            'icon'     => 'fa fa-arrow-left',
                            'prefix'   => 'e-kas/keluar|e-kas/riwayat',
                            'children' => [
                                ['label' => 'Kas Pengeluaran', 'url' => 'e-kas/keluar', 'icon' => 'fa-solid fa-money-bill-transfer', 'prefix' => 'e-kas/keluar'],
                                ['label' => 'Riwayat Kas Pengeluaran', 'url' => 'e-kas/riwayat', 'icon' => 'fas fa-history', 'prefix' => 'e-kas/riwayat'],
                            ],
                        ],
                        // Kas Masuk (penerimaan kas air / non air) belum ada
                        // controller-nya, jadi belum ditampilkan.
                    ],
                ],
                [
                    'judul' => 'Logistik',
                    'items' => [
                        ['label' => 'Faktur Pembelian', 'url' => 'faktur-pembelian', 'icon' => 'fa-solid fa-right-to-bracket', 'id' => 'faktur_pembelian_menu', 'prefix' => 'faktur-pembelian|barang-masuk'],
                        ['label' => 'Entry Permintaan', 'url' => 'barang-keluar/permintaan', 'icon' => 'fa-solid fa-right-from-bracket', 'id' => 'entry_permintaan_menu', 'prefix' => 'barang-keluar/permintaan'],
                        [
                            'label'    => 'Gudang',
                            'icon'     => 'fas fa-boxes',
                            'prefix'   => 'gudang',
                            'children' => [
                                ['label' => 'Data Barang', 'url' => 'gudang/barang', 'icon' => 'fas fa-boxes-packing', 'prefix' => 'gudang/barang'],
                                ['label' => 'Cek Permintaan', 'url' => 'gudang/cek-permintaan', 'icon' => 'fas fa-clipboard-check', 'prefix' => 'gudang/cek-permintaan'],
                                ['label' => 'Opname Stok', 'url' => 'gudang/opname-stok', 'icon' => 'fas fa-clipboard-list', 'prefix' => 'gudang/opname-stok'],
                            ],
                        ],
                    ],
                ],
                [
                    'judul' => 'Laporan',
                    'items' => [
                        ['label' => 'Rekap Persediaan', 'url' => 'laporan/rekap-persediaan', 'icon' => 'fa-solid fa-chart-column', 'prefix' => 'laporan/rekap-persediaan'],
                    ],
                ],
                [
                    'judul' => 'Data Master',
                    'admin' => true,
                    'items' => [
                        [
                            'label'    => 'Master Keuangan',
                            'icon'     => 'fas fa-box',
                            'prefix'   => 'master/perkiraan|master/bank',
                            'children' => [
                                ['label' => 'Perkiraan', 'url' => 'master/perkiraan', 'icon' => 'fas fa-box', 'prefix' => 'master/perkiraan'],
                                // Master Bank disembunyikan: tabel `master_bank`
                                // belum ada di db_pdam.
                            ],
                        ],
                        [
                            'label'    => 'Master Pegawai',
                            'icon'     => 'fas fa-users',
                            'prefix'   => 'master/jabatan|master/pokok|master/tugas-pokok|master/pegawai',
                            'children' => [
                                ['label' => 'Jabatan', 'url' => 'master/jabatan', 'icon' => 'fas fa-box', 'prefix' => 'master/jabatan'],
                                ['label' => 'Tugas Pokok', 'url' => 'master/pokok', 'icon' => 'fas fa-box', 'prefix' => 'master/pokok|master/tugas-pokok'],
                                ['label' => 'Data Pegawai', 'url' => 'master/pegawai', 'icon' => 'fas fa-box', 'prefix' => 'master/pegawai'],
                            ],
                        ],
                    ],
                ],
                [
                    'judul' => 'Administrasi',
                    'admin' => true,
                    'items' => [
                        ['label' => 'User/Akun', 'url' => 'master/akun', 'icon' => 'fas fa-user-cog', 'id' => 'akun_menu', 'prefix' => 'master/akun'],
                        ['label' => 'Absensi', 'url' => 'absensi', 'icon' => 'fa-solid fa-file-contract', 'id' => 'absensi_menu', 'prefix' => 'absensi'],
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
                    ['label' => 'Pelanggan', 'url' => 'pelanggan', 'icon' => 'fas fa-users', 'id' => 'pelanggan_menu', 'prefix' => 'pelanggan'],
                ],
            ],
            [
                'judul' => 'Data Master',
                'admin' => true,
                'items' => [
                    [
                        'label'    => 'Master Pelanggan',
                        'icon'     => 'fas fa-users',
                        'prefix'   => 'master/pelanggan|master/pasang-meter|master/set-kelompok-tarif|master/set-jenis-pelanggan',
                        'children' => [
                            ['label' => 'Daftar Pelanggan', 'url' => 'master/pelanggan', 'icon' => 'fas fa-cogs', 'prefix' => 'master/pelanggan'],
                            ['label' => 'Kelompok Tarif', 'url' => 'master/set-kelompok-tarif', 'icon' => 'fas fa-cogs', 'prefix' => 'master/set-kelompok-tarif'],
                            ['label' => 'Jenis Pelanggan', 'url' => 'master/set-jenis-pelanggan', 'icon' => 'fas fa-users', 'prefix' => 'master/set-jenis-pelanggan'],
                        ],
                    ],
                    [
                        'label'    => 'Master Wilayah',
                        'icon'     => 'fas fa-location-dot',
                        'prefix'   => 'master/kabupaten|master/kecamatan|master/kelurahan',
                        'children' => [
                            ['label' => 'Kabupaten', 'url' => 'master/kabupaten', 'icon' => 'fas fa-location-dot', 'prefix' => 'master/kabupaten'],
                            ['label' => 'Kecamatan', 'url' => 'master/kecamatan', 'icon' => 'fas fa-location-dot', 'prefix' => 'master/kecamatan'],
                            ['label' => 'Kelurahan', 'url' => 'master/kelurahan', 'icon' => 'fas fa-location-dot', 'prefix' => 'master/kelurahan'],
                        ],
                    ],
                ],
            ],
            [
                'judul' => 'Laporan',
                'items' => [
                    ['label' => 'DRD Air', 'url' => 'laporan/drd-air', 'icon' => 'fa-solid fa-chart-column', 'prefix' => 'laporan/drd-air'],
                    ['label' => 'DRD Unit/Jenis', 'url' => 'laporan/rekap-drd-air', 'icon' => 'fa-solid fa-chart-column', 'prefix' => 'laporan/rekap-drd-air'],
                    ['label' => 'Rincian Piutang Perunit', 'url' => 'laporan/rincian-piutang-perunit', 'icon' => 'fa-solid fa-chart-column', 'prefix' => 'laporan/rincian-piutang-perunit'],
                    ['label' => 'Pelanggan Duplikat', 'url' => 'laporan/pelanggan-duplikat', 'icon' => 'fa-solid fa-chart-column', 'prefix' => 'laporan/pelanggan-duplikat'],
                    ['label' => 'Rekapitulasi Piutang', 'url' => 'laporan/rekapitulasi-piutang', 'icon' => 'fa-solid fa-chart-column', 'prefix' => 'laporan/rekapitulasi-piutang'],
                ],
            ],
            [
                'judul' => 'Administrasi',
                'admin' => true,
                'items' => [
                    ['label' => 'User/Akun', 'url' => 'master/akun', 'icon' => 'fas fa-user-cog', 'id' => 'akun_menu', 'prefix' => 'master/akun'],
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
