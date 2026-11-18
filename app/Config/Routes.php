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
$routes->get('/pelanggan', 'Pelanggan::index');

// master billing
$routes->get('/master/set-kelompok-tarif', 'Master\SetKelompokTarif::index');
$routes->get('/master/set-jenis-pelanggan', 'Master\SetJenisPelanggan::index');
$routes->get('/master/pelanggan', 'Master\Pelanggan::index');
$routes->get('/master/pasang-meter', 'Master\Pelanggan::pasangMeter');

// master wilayah
$routes->get('/master/kabupaten', 'Master\Wilayah::kab');
$routes->get('/master/kecamatan', 'Master\Wilayah::kec');
$routes->get('/master/kelurahan', 'Master\Wilayah::kel');

// laporan billing
$routes->get('/laporan/drd-air', 'Laporan\DrdAir::index');
$routes->get('/laporan/rekap-drd-air', 'Laporan\RekapDrdAir::index');
$routes->get('/laporan/rincian-piutang-perunit', 'Laporan\RincianPiutangPerunit::index');
$routes->get('/laporan/pelanggan-duplikat', 'Laporan\PelangganDuplikat::index');
$routes->get('/laporan/rekapitulasi-piutang', 'Laporan\RekapitulasiPiutang::index');

// =====================================================================
// SISTEM E-KAS
// Kas keluar/masuk, logistik, persediaan, kepegawaian.
// =====================================================================
$routes->get('/ekas', 'Home::ekas');

// master kas
$routes->get('/master/perkiraan', 'Master\Perkiraan::index');
$routes->get('/master/jabatan', 'Master\Jabatan::index');
$routes->get('/master/tugas-pokok', 'Master\Pokok::index');
$routes->get('/master/pokok', 'Master\Pokok::index');
$routes->get('/master/pegawai', 'Master\Pegawai::index');
$routes->get('/master/bank', 'Master\Bank::index');

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
$routes->get('/faktur-pembelian', 'Logistik\BarangMasuk\Penerimaan::index');
$routes->get('/barang-masuk/penerimaan', 'Logistik\BarangMasuk\Penerimaan::index');
// /barang-masuk/laporan-penerimaan sengaja tidak didaftarkan: controller
// Laporan_penerimaan belum ada (route lama menunjuk ke '??').
$routes->get('/barang-keluar/permintaan', 'Logistik\BarangKeluar\Permintaan::index');
$routes->get('/barang-Keluar/laporan-permintaan', 'Logistik\BarangKeluar\Laporan_permintaan::index');
$routes->get('/gudang/barang', 'Logistik\Gudang\Barang::index');
$routes->get('/gudang/cek-permintaan', 'Logistik\Gudang\CekPermintaan::index');
$routes->get('/gudang/opname-stok', 'Logistik\Gudang\OpnameStok::index');

// laporan kas
$routes->get('/laporan/rekap-persediaan', 'Laporan\RekapPersediaan::index');
$routes->get('/laporan/daftar-hadir', 'Laporan\DaftarHadir::index');
$routes->get('/laporan/daftar-hadir/pdf/(:segment)/(:segment)/(:segment)', 'Laporan\DaftarHadir::pdf/$1/$2/$3');
$routes->get('/laporan/kinerja-pegawai', 'Laporan\KinerjaPegawai::index');
$routes->get('/laporan/kinerja-pegawai/data', 'Laporan\KinerjaPegawai::data');
$routes->get('/laporan/kinerja-pegawai/detail', 'Laporan\KinerjaPegawai::detail');
$routes->get('/laporan/kinerja-pegawai/pdf/(:segment)', 'Laporan\KinerjaPegawai::pdf/$1');

// kepegawaian
$routes->get('/absensi', 'Absensi::unit');
$routes->get('/slip-gaji', 'SlipGaji::index');
$routes->get('/persuratan', 'Persuratan::index');

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
