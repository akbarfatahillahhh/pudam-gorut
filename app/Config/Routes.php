<?php

namespace Config;

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
$routes->get('/master/tugas-pokok', 'Master\TugasPokok::index');
$routes->get('/master/pegawai', 'Master\Pegawai::index');

// routes master wilayah
$routes->get('/master/kabupaten', 'Master\Kabupaten::index');
$routes->get('/master/kecamatan', 'Master\Kecamatan::index');
$routes->get('/master/kelurahan', 'Master\Kelurahan::index');


if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
