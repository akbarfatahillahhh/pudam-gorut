<?php

namespace Config;

use CodeIgniter\HTTP\ResponseInterface;

$routes = Services::routes();

if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

$routes->get('/', 'Login::index');
$routes->get('/login', 'Login::index');
$routes->get('/logout', 'Login::logout');
$routes->post('/login', 'Login::login');

// =====================================================================
// SISTEM BILLING
// Pelanggan, catat meter, tagihan, piutang, DRD.
// Data master billing hanya untuk admin (dicek FilterSistem + sidebar).
// =====================================================================
$routes->get('/billing', 'Home::billing');
$routes->get('/billing/pelanggan', 'Pelanggan::index');

// loket pembayaran
$routes->get('/billing/loket', 'Billing\Loket::index');
$routes->get('/billing/loket/kwitansi_pdf/(:segment)', 'Billing\Loket::kwitansi_pdf/$1');

// tagihan: entry pemakaian, pembayaran massal, cetak, cek duplikat
$routes->get('/billing/tagihan', 'Billing\Tagihan::index');
$routes->get('/billing/tagihan/duplikat', 'Billing\Tagihan::duplikat');

// catat meter: verifikasi hasil pencatatan petugas
$routes->get('/billing/catat-meter', 'Billing\CatatMeter::index');

// master billing
$routes->get('/billing/master/set-kelompok-tarif', 'Master\SetKelompokTarif::index');
$routes->get('/billing/master/set-jenis-pelanggan', 'Master\SetJenisPelanggan::index');
$routes->get('/billing/master/pelanggan', 'Master\Pelanggan::index');
$routes->get('/billing/master/pasang-meter', 'Master\Pelanggan::pasangMeter');

// master wilayah
$routes->get('/billing/master/kabupaten', 'Master\Wilayah::kab');
$routes->get('/billing/master/kecamatan', 'Master\Wilayah::kec');
$routes->get('/billing/master/kelurahan', 'Master\Wilayah::kel');

// laporan billing
$routes->get('/billing/laporan/drd-air', 'Laporan\DrdAir::index');
$routes->get('/billing/laporan/rekap-drd-air', 'Laporan\RekapDrdAir::index');
$routes->get('/billing/laporan/rincian-piutang-perunit', 'Laporan\RincianPiutangPerunit::index');
$routes->get('/billing/laporan/pelanggan-duplikat', 'Laporan\PelangganDuplikat::index');
$routes->get('/billing/laporan/rekapitulasi-piutang', 'Laporan\RekapitulasiPiutang::index');
$routes->get('/billing/laporan/lpp-berjalan', 'Laporan\LppBerjalan::index');
$routes->get('/billing/laporan/lpp-petugas', 'Laporan\LppPetugas::index');
$routes->get('/billing/laporan/tunggakan-per-unit', 'Laporan\TunggakanPerUnit::index');

// =====================================================================
// URL LAMA MODUL BILLING -> redirect 301 ke prefix /billing
// Endpoint AJAX (autoRoute: /Master/Pelanggan/data, /Pelanggan/data,
// /Laporan/DrdAir/datatable, dst) sengaja TIDAK dipindah, sama seperti
// E-Kas: halaman yang berprefix, AJAX tetap di URL controllernya.
// =====================================================================
foreach ([
    'pelanggan',
    'tagihan',
    'tagihan/duplikat',
    'master/set-kelompok-tarif',
    'master/set-jenis-pelanggan',
    'master/pelanggan',
    'master/pasang-meter',
    'master/kabupaten',
    'master/kecamatan',
    'master/kelurahan',
    'laporan/drd-air',
    'laporan/rekap-drd-air',
    'laporan/rincian-piutang-perunit',
    'laporan/pelanggan-duplikat',
    'laporan/rekapitulasi-piutang',
    'laporan/lpp-berjalan',
    'laporan/lpp-petugas',
    'laporan/tunggakan-per-unit',
] as $lamaBilling) {
    $routes->get('/' . $lamaBilling, static fn () => redirect()->to(base_url('billing/' . $lamaBilling), 301));
}

// =====================================================================
// SISTEM E-KAS
// Kas keluar/masuk, logistik, persediaan, kepegawaian.
// =====================================================================
$routes->get('/ekas', 'Home::ekas');

// master kas
$routes->get('/ekas/master/perkiraan', 'Master\Perkiraan::index');
$routes->get('/ekas/master/perkiraan-group', 'Master\PerkiraanGroup::index');
$routes->get('/ekas/master/jabatan', 'Master\Jabatan::index');
$routes->get('/ekas/master/tugas-pokok', 'Master\Pokok::index');
$routes->get('/ekas/master/pokok', 'Master\Pokok::index');
$routes->get('/ekas/master/pegawai', 'Master\Pegawai::index');
$routes->get('/ekas/master/bank', 'Master\Bank::index');

// kas
$routes->get('/ekas/keluar', 'E_Kas\Pengeluaran\EntryKas_Keluar::index');
$routes->get('/ekas/riwayat', 'E_Kas\Pengeluaran\EntryKas_Keluar::riwayat');

// kas masuk (penerimaan)
$routes->get('/ekas/masuk', 'E_Kas\Pemasukan\PenerimaanKasAir::index');
$routes->post('/ekas/masuk/tampil', 'E_Kas\Pemasukan\PenerimaanKasAir::tampil');
$routes->post('/ekas/masuk/simpan', 'E_Kas\Pemasukan\PenerimaanKasAir::simpan');
$routes->get('/ekas/masuk-non-air', 'E_Kas\Pemasukan\PenerimaanKasNonAir::index');
$routes->post('/ekas/masuk-non-air/simpan', 'E_Kas\Pemasukan\PenerimaanKasNonAir::simpan');

// URL lama /e-kas/* dialihkan permanen ke /ekas/*
$routes->get('/e-kas', static fn () => redirect()->to(base_url('ekas'), 301));
$routes->get('/e-kas/keluar', static fn () => redirect()->to(base_url('ekas/keluar'), 301));
$routes->get('/e-kas/riwayat', static fn () => redirect()->to(base_url('ekas/riwayat'), 301));
$routes->get('/e-kas/masuk', static fn () => redirect()->to(base_url('ekas/masuk'), 301));
$routes->get('/e-kas/masuk-non-air', static fn () => redirect()->to(base_url('ekas/masuk-non-air'), 301));
$routes->post('/e-kas/masuk/tampil', static fn () => redirect()->to(base_url('ekas/masuk/tampil'), 301));
$routes->post('/e-kas/masuk/simpan', static fn () => redirect()->to(base_url('ekas/masuk/simpan'), 301));
$routes->post('/e-kas/masuk-non-air/simpan', static fn () => redirect()->to(base_url('ekas/masuk-non-air/simpan'), 301));

// logistik
$routes->get('/ekas/faktur-pembelian', 'Logistik\BarangMasuk\Penerimaan::index');
$routes->get('/ekas/barang-masuk/penerimaan', 'Logistik\BarangMasuk\Penerimaan::index');
// /barang-masuk/laporan-penerimaan sengaja tidak didaftarkan: controller
// Laporan_penerimaan belum ada (route lama menunjuk ke '??').
$routes->get('/ekas/barang-keluar/permintaan', 'Logistik\BarangKeluar\Permintaan::index');
$routes->get('/ekas/barang-keluar/laporan-permintaan', 'Logistik\BarangKeluar\Laporan_permintaan::index');
$routes->get('/ekas/gudang/barang', 'Logistik\Gudang\Barang::index');
$routes->get('/ekas/gudang/cek-permintaan', 'Logistik\Gudang\CekPermintaan::index');
$routes->get('/ekas/gudang/opname-stok', 'Logistik\Gudang\OpnameStok::index');

// laporan kas
$routes->get('/ekas/laporan/rekap-persediaan', 'Laporan\RekapPersediaan::index');
$routes->get('/ekas/laporan/daftar-hadir', 'Laporan\DaftarHadir::index');
$routes->get('/ekas/laporan/daftar-hadir/pdf/(:segment)/(:segment)/(:segment)', 'Laporan\DaftarHadir::pdf/$1/$2/$3');
$routes->get('/ekas/laporan/kinerja-pegawai', 'Laporan\KinerjaPegawai::index');
$routes->get('/ekas/laporan/kinerja-pegawai/data', 'Laporan\KinerjaPegawai::data');
$routes->get('/ekas/laporan/kinerja-pegawai/detail', 'Laporan\KinerjaPegawai::detail');
$routes->get('/ekas/laporan/kinerja-pegawai/pdf/(:segment)', 'Laporan\KinerjaPegawai::pdf/$1');

// kepegawaian
$routes->get('/ekas/absensi', 'Absensi::unit');
$routes->get('/ekas/slip-gaji', 'SlipGaji::index');
$routes->get('/ekas/persuratan', 'Persuratan::index');

// =====================================================================
// URL LAMA MODUL E-KAS -> redirect 301 ke prefix /ekas
// Supaya bookmark dan link lama tidak mati.
// =====================================================================
foreach ([
    'master/perkiraan',
    'master/perkiraan-group',
    'master/jabatan',
    'master/tugas-pokok',
    'master/pokok',
    'master/pegawai',
    'master/bank',
    'faktur-pembelian',
    'barang-masuk/penerimaan',
    'barang-keluar/permintaan',
    'barang-keluar/laporan-permintaan',
    'gudang/barang',
    'gudang/cek-permintaan',
    'gudang/opname-stok',
    'laporan/rekap-persediaan',
    'laporan/daftar-hadir',
    'laporan/kinerja-pegawai',
    'absensi',
    'slip-gaji',
    'persuratan',
] as $lama) {
    $routes->get('/' . $lama, static fn () => redirect()->to(base_url('ekas/' . $lama), 301));
}

// =====================================================================
// ADMIN (berlaku di kedua sistem)
// =====================================================================
$routes->get('/master/akun', 'Master\Akun::index');

$routes->get('/isSession', function () {
    $response = service('response');
    if (!session()->has('id_session')) {
        return $response->setJSON(['status' => 'session_expired']);
    }
    return $response->setJSON(['status' => 'active']);
});



if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
