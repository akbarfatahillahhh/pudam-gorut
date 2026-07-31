<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Akun extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Akun";
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/master/akun/main', $data);
    }

    public function datatable()
    {
        $builder = $this->db->table('users');
        $builder->select('
            users.username, 
            users.nama_lengkap,
            users.level,
            users.blokir,
            users.id_session,
            users.id_pegawai,
            users.mobile_akses,
        ');

        $builder->orderBy('users.level', 'ASC');

        // <option value="admin">ADMINISTRATOR</option>
        // <option value="direktur">DIREKTUR</option>
        // <option value="wakil1">WAKIL DIREKTUR 1</option>
        // <option value="wakil2">WAKIL DIREKTUR 2</option>
        // <option value="kabag_akun">KABAG AKUNTANSI</option>
        // <option value="bendahara">BENDAHARA</option>
        // <option value="loket">LOKET</option>
        // <option value="mobileloket">MOBILE LOKET</option>
        // <option value="user">ADMINISTRASI</option>

        return DataTable::of($builder)
            ->add('level', function ($row) {
                if ($row->level === 'mobileloket') {
                    $petugas = ($row->mobile_akses == '1') ? '<span class="badge fs-1 rounded fw-semibold bg-success">Petugas Penagih</span>' : '<span class="badge fs-1 rounded fw-semibold bg-warning">Petugas Catat Meter</span>';
                    return '<span class="badge fs-1 rounded fw-semibold bg-dark">Mobile Loket</span>' . '<div class="pt-1"></div>' . $petugas;
                } else if ($row->level) {
                    return $row->level;
                }
            })
            ->add('blokir', function ($row) {
                $blokir = $row->blokir == 'Y' ? '<i class="fas fa-ban"></i> Blokir' : '<i class="fas fa-unlock"></i> Aktif';
                return '<span class="badge fs-1 rounded fw-semibold pointer btn-blokir-' . $row->username . ' ' . ($row->blokir == 'Y' ? 'bg-danger' : 'bg-success') . '" onclick="blokir(\'' . $row->username . '\', \'' . $row->blokir . '\')">' . $blokir . '</span>';
            })
            ->add('aksi', function ($row) {
                return '<div class="dropdown">
                            <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v text-black"></i>
                            </button>
                            <ul class="dropdown-menu py-0">
                                <li>
                                    <a class="dropdown-item text-black" href="#" onclick="gantiAkses(\'' . $row->id_session . '\')">
                                        <i class="fa-solid fa-user-cog text-danger"></i>
                                        Setting Akses
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-black" href="#" onclick="gantiPassword(\'' . $row->id_session . '\')">
                                        <i class="fa-solid fa-cogs text-indigo"></i>
                                        Ganti Password
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-black" href="#" onclick="edit(\'' . $row->id_session . '\')">
                                        <i class="fa-solid fa-pen-to-square text-warning"></i>
                                        Edit
                                    </a>
                                </li>
                            </ul>
                        </div>';
            })
            ->toJson(true);
    }

    public function detail_pegawai()
    {
        $pegawai = $this->db->table('pegawai')->where('id', $this->request->getPost('id_pegawai'))->get()->getRowArray();

        $NIK = ($pegawai && $pegawai['NIK'] != null) ? $pegawai['NIK'] : '-';
        $NAMA = ($pegawai && $pegawai['NAMA'] != null) ? $pegawai['NAMA'] : '-';
        $TGL_LAHIR = ($pegawai && $pegawai['TGL_LAHIR'] != null) ? date('d M Y', strtotime($pegawai['TGL_LAHIR'])) : '-';
        $TEMPAT_LAHIR = ($pegawai && $pegawai['TEMPAT_LAHIR'] != null) ? $pegawai['TEMPAT_LAHIR'] : '-';
        $KELAMIN = ($pegawai && $pegawai['KELAMIN'] != null) ? $pegawai['KELAMIN'] : '-';
        $ALAMAT = ($pegawai && $pegawai['ALAMAT'] != null) ? $pegawai['ALAMAT'] : '-';
        $STATUS = ($pegawai && $pegawai['STATUS'] != null) ? $pegawai['STATUS'] : '-';

        $data = [
            'NIK' => $NIK,
            'NAMA' => $NAMA,
            'TEMPAT_LAHIR' => $TEMPAT_LAHIR,
            'TGL_LAHIR' => $TGL_LAHIR,
            'KELAMIN' => $KELAMIN,
            'ALAMAT' => $ALAMAT,
            'STATUS' => $STATUS
        ];

        $html = ['html' => view('/master/akun/modal/pegawai', $data)];
        echo json_encode($html);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/master/akun/modal/new', $data)];
        echo json_encode($html);
    }

    public function ganti_password()
    {
        $data['title'] = $this->title;
        $data['id_session'] = $this->request->getPost('id_session');
        $data['nama_lengkap'] = $this->db->table('users')->select('nama_lengkap')->where('id_session', $data['id_session'])->get()->getRow()->nama_lengkap;

        $html = ['html' => view('/master/akun/modal/ganti_password', $data)];
        echo json_encode($html);
    }

    public function save_ganti_password()
    {
        $id_session = $this->request->getPost('id_session');
        $password_baru = $this->request->getPost('password_baru');
        $password_konfirmasi = $this->request->getPost('password_konfirmasi');

        $data['password'] = md5($password_baru);

        $users = $this->db->table('users');
        $users->where('id_session', $id_session);
        $users->update($data);

        echo json_encode(['status' => 'success', 'msg' => 'Password berhasil diubah']);
    }

    public function setting_akses()
    {
        $data['title'] = $this->title;
        $id_session = $this->request->getPost('id_session');
        $data['id_session'] = $id_session;

        // Ambil data menu dari database
        $delete = $this->db->table('t_user_akses')
            ->where('id_session', $id_session)
            ->delete();

        $row = $this->db->table('t_user_akses')
            ->where('id_session', $id_session)
            ->get()
            ->getRowArray();

        // Daftar semua nama menu
        $menu_list = [
            'perkiraan', 'jabatan', 'tugas_pokok', 'data_pegawai',
            'daftar_pelanggan', 'kelompok_tarif', 'jenis_pelanggan',
            'kabupaten', 'kecamatan', 'kelurahan', 'user_akun', 'absensi',
            'faktur_pembelian', 'entry_permintaan', 'data_barang', 'cek_permintaan',
            'opname_stok', 'pelanggan', 'kas_pengeluaran', 'riwayat_kas_pengeluaran',
            'penerimaan_kas_air', 'penerimaan_kas_non_air', 'drd_air', 'drd_unit_jenis',
            'rincian_piutang_perunit', 'pelanggan_duplikat', 'rekapitulasi_piutang',
            'rekap_persediaan'
        ];

        $checked_menu = [];

        if (!empty($row['menu'])) {
            $checked_menu = json_decode($row['menu'], true); // array hasil decode JSON
        }

        // Loop menu dan isi data checked atau tidak
        foreach ($menu_list as $menu_name) {
            $data[$menu_name] = in_array($menu_name, $checked_menu) ? 'checked' : '';
        }

        $html = ['html' => view('/master/akun/modal/setting_akses', $data)];
        echo json_encode($html);
    }


    public function save_setting_akses()
    {
        $id_session = $this->request->getPost('id_session');
        $menu = $this->request->getPost('menu');
        
        if (!$menu || !$id_session) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap.'
            ]);
        }

        $data_menu = json_encode($menu);

        $this->db->table('t_user_akses')->insert([
            'id_session' => $id_session,
            'menu' => $data_menu
        ]);
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Akses berhasil disimpan.'
        ]);
    }


    public function edit()
    {
        $data['title'] = $this->title;
        $id_session = $this->request->getPost('id_session');
        $data['data'] = $this->db->table('users')->where('id_session', $id_session)->get()->getRowArray();

        $html = ['html' => view('/master/akun/modal/edit', $data)];
        echo json_encode($html);
    }

    public function blokir()
    {
        $username = $this->request->getPost('username');
        $blokir = ($this->request->getPost('blokir') == 'Y') ? 'N' : 'Y';

        $users = $this->db->table('users');
        $users->where('username', $username);
        $users->set(['blokir' => $blokir]);
        $users->update();

        $response = [
            'status' => 'success',
            'msg' => 'Data berhasil diubah'
        ];

        return $this->response->setJSON($response);
    }

    public function save()
    {
        $data = $this->request->getPost();

        $cek_username = $this->db->table('users')->where('username', $data['username'])->countAllResults();
        if ($cek_username > 0) {
            $msg = [
                'status' => 'error',
                'msg' => 'Username sudah digunakan'
            ];
            echo json_encode($msg);
        } else {
            $data['password'] = md5($data['password']);
            $data['id_session'] = bin2hex(random_bytes(25));

            $this->db->table('users')->insert($data);

            $msg = [
                'status' => 'success',
                'msg' => 'Data berhasil disimpan'
            ];
        }
        echo json_encode($msg);
    }

    public function update()
    {
        $data = $this->request->getPost();

        $cek_username = $this->db->table('users')->where('username', $data['username'])->countAllResults();

        if ($cek_username > 0) {
            $msg = [
                'status' => 'error',
                'msg' => 'Username sudah digunakan'
            ];
        } else {
            $this->db->table('users')
                ->where('id_session', $data['id_session'])
                ->update($data);

            $msg = [
                'status' => 'success',
                'msg' => 'Data berhasil disimpan'
            ];
        }

        echo json_encode($msg);
    }

    public function getDataPegawai()
    {
        $id_pegawai = $this->request->getPost('id_pegawai');
        $pegawai = $this->db->table('pegawai')
            ->select('ID as id_pegawai, NIK as nik, NAMA as nama, TEMPAT_LAHIR tempat_lahir, TGL_LAHIR as tgl_lahir, KELAMIN as kelamin, ALAMAT as alamat')
            ->where('ID', $id_pegawai)->get()
            ->getRowArray();

        echo json_encode($pegawai);
    }
}
