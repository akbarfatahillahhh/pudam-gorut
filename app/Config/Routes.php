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

// routes master e-kas
$routes->get('/master/perkiraan', 'Master\Perkiraan::index');
$routes->get('/master/jabatan', 'Master\Jabatan::index');
$routes->get('/master/tugas-pokok', 'Master\TugasPokok::index');
$routes->get('/master/pegawai', 'Master\Pegawai::index');

// routes master billing system
$routes->get('/master/set-kelompok-tarif', 'Master\SetKelompokTarif::index');
$routes->get('/master/set-jenis-pelanggan', 'Master\SetJenisPelanggan::index');
$routes->get('/master/pelanggan', 'Master\Pelanggan::index');
$routes->get('/master/pasang-meter', 'Master\Pelanggan::pasangMeter');
$routes->get('/master/tugas-pokok', 'Master\TugasPokok::index');
$routes->get('/master/pegawai', 'Master\Pegawai::index');

// routes master wilayah
$routes->get('/master/kabupaten', 'Master\Wilayah::kab');
$routes->get('/master/kecamatan', 'Master\Wilayah::kec');
$routes->get('/master/kelurahan', 'Master\Wilayah::kel');

$routes->get('/master/akun', 'Master\Akun::index');

$routes->get('/master/bank', 'Master\Bank::index');

$routes->get('/absensi', 'Absensi::unit');

// routes laporan billing system
$routes->get('/laporan/drd-air', 'Laporan\DrdAir::index');
$routes->get('/laporan/rekap-drd-air', 'Laporan\RekapDrdAir::index');
$routes->get('/laporan/rincian-piutang-perunit', 'Laporan\RincianPiutangPerunit::index');
$routes->get('/laporan/pelanggan-duplikat', 'Laporan\PelangganDuplikat::index');
$routes->get('/laporan/rekapitulasi-piutang', 'Laporan\RekapitulasiPiutang::index');
$routes->get('/laporan/rekap-persediaan', 'Laporan\RekapPersediaan::index');

$routes->get('/barang-keluar/permintaan', 'Logistik\BarangKeluar\Permintaan::index');
$routes->get('/barang-Keluar/laporan-permintaan', 'Logistik\BarangKeluar\Laporan_permintaan::index');

$routes->get('/faktur-pembelian', 'Logistik\BarangMasuk\Penerimaan::index');
$routes->get('/barang-masuk/penerimaan', 'Logistik\BarangMasuk\Penerimaan::index');
$routes->get('/barang-masuk/laporan-penerimaan', 'Logistik\BarangMasuk\??::index');

$routes->get('/gudang/barang', 'Logistik\Gudang\Barang::index');
$routes->get('/gudang/cek-permintaan', 'Logistik\Gudang\CekPermintaan::index');
$routes->get('/gudang/opname-stok', 'Logistik\Gudang\OpnameStok::index');

$routes->get('/pelanggan', 'Pelanggan::index');

$routes->get('/e-kas/keluar', 'E_Kas\Pengeluaran\EntryKas_Keluar::index');
$routes->get('/e-kas/riwayat', 'E_Kas\Pengeluaran\EntryKas_Keluar::riwayat');


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
