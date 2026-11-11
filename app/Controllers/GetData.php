<?php

namespace App\Controllers;

class Getdata extends BaseController
{
    public function getPegawai()
    {
        if ($this->request->isAJAX()) {
            $table = $this->db->table('pegawai')
                ->select('ID, NAMA')
                ->get();

            echo json_encode(array_map(fn($row) => [
                'id' => $row['ID'],
                'text' => $row['NAMA']
            ], $table->getResultArray()));
        }
    }

    public function getDataPegawai($id_pegawai) {}

    public function getJabatan()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search') ?? '';
            if ($caridata != '') {
                $table = $this->db->table('jabatan')->LIKE('JABATAN', $caridata)->get();
            } else {
                $table = $this->db->table('jabatan')->get();
            }
            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($table->getResultArray() as $row):
                    $list[$key]['id'] = $row['ID'];
                    $list[$key]['text'] = $row['JABATAN'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function getPenagihByTagihan()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');

            $builder = $this->db->table('users');
            $builder->select('users.nama_lengkap, users.id_session');
            $builder->join('t_tagihan', 'users.id_session = t_tagihan.id_user');
            $builder->where('users.blokir', 'N');

            if ($caridata != "") {
                $builder->like('nama_lengkap', $caridata);
            }
            $builder->groupBy('users.id_session');
            $result = $builder->get()->getResultArray();

            if (!empty($result)) {
                $list = array_map(function ($row) {
                    return [
                        'id' => $row['id_session'],
                        'text' => $row['nama_lengkap']
                    ];
                }, $result);

                echo json_encode($list);
            }
        }
    }

    public function getPCM()
    {
        if ($this->request->isAJAX()) {

            $builder = $this->db->table('users');
            $builder->select('nama_lengkap, id_session');
            $builder->where('level', 'mobileloket');
            $builder->where('mobile_akses', 2);
            $builder->where('blokir', 'N');

            $builder->groupBy('id_session');
            $result = $builder->get()->getResultArray();

            if (!empty($result)) {
                $list = array_map(function ($row) {
                    return [
                        'id' => $row['id_session'],
                        'text' => strtoupper($row['nama_lengkap'])
                    ];
                }, $result);

                echo json_encode($list);
            }
        }
    }

    public function getTugasPokok()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');

            $builder = $this->db->table('tugas_pokok');
            $builder->select('ID, TUGAS_POKOK');

            if ($caridata != "") {
                $builder->like('TUGAS_POKOK', $caridata);
            }

            $result = $builder->get()->getResultArray();

            if (!empty($result)) {
                $list = array_map(function ($row) {
                    return [
                        'id' => $row['ID'],
                        'text' => $row['TUGAS_POKOK'],
                        'text_after' => preg_replace('/^\d+\./', '', $row['TUGAS_POKOK'])
                    ];
                }, $result);

                echo json_encode($list);
            }
        }
    }

    public function getUnit()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');

            $builder = $this->db->table('t_kecamatan');
            $builder->select('id_kec, kecamatan');
            $builder->where('t_kecamatan.kd_kecamatan <>', '');

            if ($caridata != "") {
                $builder->like('kecamatan', $caridata);
            }

            $result = $builder->get()->getResultArray();

            if (!empty($result)) {
                $list = array_map(function ($row) {
                    return [
                        'id' => $row['id_kec'],
                        'text' => $row['kecamatan']
                    ];
                }, $result);

                echo json_encode($list);
            }
        }
    }

    public function getUnitByTitikKoordinat()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');

            $builder = $this->db->table('m_titik_absen');

            if ($caridata != "") {
                $builder->like('unit', $caridata);
            }

            $result = $builder->get()->getResultArray();

            if (!empty($result)) {
                $list = array_map(function ($row) {
                    return [
                        'id' => $row['LATITUDE'] . '-' . $row['LONGITUDE'],
                        'text' => strtoupper($row['UNIT'])
                    ];
                }, $result);

                echo json_encode($list);
            }
        }
    }

    public function getKel()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');

            $builder = $this->db->table('t_kelurahan');
            $builder->select('t_kelurahan.id_kelurahan, t_kelurahan.kelurahan, t_kelurahan.id_kec');
            $builder->select('t_kecamatan.kecamatan');
            $builder->select('t_kelurahan.id_kelurahan, t_kelurahan.kelurahan');
            $builder->join('t_kecamatan', 't_kecamatan.id_kec = t_kelurahan.id_kec');
            $builder->where('t_kecamatan.kd_kecamatan <>', '');

            if ($caridata != "") {
                $builder->like('t_kecamatan.kecamatan', $caridata);
                $builder->orLike('t_kelurahan.kelurahan', $caridata);
            }

            $builder->orderBy('t_kelurahan.id_kec', 'ASC');
            $result = $builder->get()->getResultArray();

            if (!empty($result)) {
                $list = array_map(function ($row) {
                    return [
                        'id' => $row['id_kelurahan'],
                        'text' => "[" . $row['kecamatan'] . "] " . $row['kelurahan']
                    ];
                }, $result);

                echo json_encode($list);
            }
        }
    }

    public function GetJenisPengeluaran()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != '') {
                $table = $this->db->table('m_jenis_pengeluaran')->LIKE('jenis_pengeluaran', $caridata)->get();
            } else {
                $table = $this->db->table('m_jenis_pengeluaran')->get();
            }

            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                $list[$key]['id'] = 'all';
                $list[$key]['text'] = 'SEMUA JENIS PENGELUARAN';
                foreach ($table->getResultArray() as $row):
                    $key++;
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['jenis_pengeluaran'];
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function getprovinsi()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != '') {
                $instalasi = $this->db
                    ->table('master_provinsi')

                    ->LIKE('ID', $caridata)
                    ->orLIKE('PROVINSI', $caridata)
                    // ->WHERE('NORMAL', 'DEBET')
                    ->get();
            } else {
                $instalasi = $this->db
                    ->table('master_provinsi')
                    // ->WHERE('NORMAL', 'DEBET')
                    ->get();
            }

            if ($instalasi->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($instalasi->getResultArray() as $row):
                    $list[$key]['id'] = $row['ID'];
                    $list[$key]['text'] = $row['PROVINSI'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function getkabupaten()
    {
        if ($this->request->isAJAX()) {
            $provinsi = $this->request->getVar('provinsi');
            $search = $this->request->getVar('search');
            if ($search != '') {
                $dtkabupaten = $this->db->table('master_kabupaten')->where('IDPROVINSI', $provinsi)->LIKE('KABUPATEN', $search)->get();
            } else {
                $dtkabupaten = $this->db->table('master_kabupaten')->where('IDPROVINSI', $provinsi)->get();
            }
            // var_dump($dtkabupaten->getResultArray());
            if ($dtkabupaten->getNumRows() > 0) {
                // $isidata = '';
                // $isidata .= '<option>[ Pilih Kabupaten ]</option>';
                $list = [];
                $key = 0;
                foreach ($dtkabupaten->getResultArray() as $row):
                    // $isidata .= '<option value="' . $row['ID'] . '">' . $row['KABUPATEN'] . '</option>';
                    $list[$key]['id'] = $row['ID'];
                    $list[$key]['text'] = $row['KABUPATEN'];
                    $key++;
                endforeach;

                // $msg = [
                //     'data' => $isidata,
                // ];
                echo json_encode($list);
            } else {
                $msg = [
                    'kosong' => '<option>Data tidak ditemukan</option>',
                ];
            }
        }
    }

    public function getLogistikBarang()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != '') {
                $data = $this->db->table('logistik_barang')->LIKE('barang_nama', $caridata)->get();
            } else {
                $data = $this->db->table('logistik_barang')->get();
            }

            if ($data->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($data->getResultArray() as $row):
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['barang_nama'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }
    public function getkecamatan()
    {
        if ($this->request->isAJAX()) {
            $kabupaten = $this->request->getVar('kabupaten');
            $search = $this->request->getVar('search');
            // $dtkecamatan = $this->db->table('master_kecamatan')->where('IDKAB', $kabupaten)->get();
            if ($search != '') {
                $dtkecamatan = $this->db->table('master_kecamatan')->where('IDKAB', $kabupaten)->LIKE('KECAMATAN', $search)->get();
            } else {
                $dtkecamatan = $this->db->table('master_kecamatan')->select('ID, KECAMATAN')->where('IDKAB', $kabupaten)->get();
            }
            // var_dump($dtkecamatan->getResultArray());
            if ($dtkecamatan->getNumRows() > 0) {
                // $isidata = '';
                // $isidata .= '<option>[ Pilih Kecamatan ]</option>';
                $list = [];
                $key = 0;
                foreach ($dtkecamatan->getResultArray() as $row):
                    $list[$key]['id'] = $row['ID'];
                    $list[$key]['text'] = $row['KECAMATAN'];
                    $key++;
                // $isidata .= '<option value="' . $row['ID'] . '">' . $row['KECAMATAN'] . '</option>';
                endforeach;

                // $msg = [
                //     'data' => $isidata,
                // ];
                echo json_encode($list);
            } else {
                $msg = [
                    'kosong' => '<option>Data tidak ditemukan</option>',
                ];
            }
        }
    }

    public function getkelurahan()
    {
        if ($this->request->isAJAX()) {
            $kecamatan = $this->request->getVar('kecamatan');
            $search = $this->request->getVar('search');

            if ($search != '') {
                $dtkelurahan = $this->db->table('master_kelurahan')->where('IDKEC', $kecamatan)->LIKE('KELURAHAN', $search)->get();
            } else {
                $dtkelurahan = $this->db->table('master_kelurahan')->where('IDKEC', $kecamatan)->get();
            }

            if ($dtkelurahan->getNumRows() > 0) {
                // $isidata = '';
                // $isidata .= '<option>[ Pilih Kelurahan ]</option>';
                $list = [];
                $key = 0;
                foreach ($dtkelurahan->getResultArray() as $row):
                    // $isidata .= '<option value="' . $row['ID'] . '">' . $row['KELURAHAN'] . '</option>';
                    $list[$key]['id'] = $row['ID'];
                    $list[$key]['text'] = $row['KELURAHAN'];
                    $key++;
                endforeach;

                // $msg = [
                //     'data' => $isidata,
                // ];

                echo json_encode($list);
            } else {
                $msg = [
                    'kosong' => '<option>Data tidak ditemukan</option>',
                ];
            }
        }
    }
    public function getPerkiraanGroup()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != '') {
                $table = $this->db->table('akun_perkiraan_group')->LIKE('GROUP_PERKIRAAN', $caridata)->get();
            } else {
                $table = $this->db->table('akun_perkiraan_group')->get();
            }
            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($table->getResultArray() as $row):
                    $list[$key]['id'] = $row['ID'];
                    $list[$key]['text'] = $row['NO_PERKIRAAN'] . ' | ' . $row['GROUP_PERKIRAAN'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function getPerkiraan()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if($caridata != ""){
                $instalasi = $this->db->table('akun_perkiraan')
                ->LIKE('NOMOR', $caridata)
                ->orLIKE('NAMA_PERKIRAAN', $caridata)
                ->WHERE('STATUS', 'Biaya')
                ->get();
            }else{
                $instalasi = $this->db->table('akun_perkiraan')
                ->WHERE('STATUS', 'Biaya')
                ->get();
            }

            if ($instalasi->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($instalasi->getResultArray() as $row) :
                    $list[$key]['id'] = $row['ID'];
                    $list[$key]['text'] = $row['NORMAL'] . " - " . $row['NOMOR'] . " | " . $row['NAMA_PERKIRAAN'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function getKabupatenMaster()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != '') {
                $table = $this->db->table('t_kabupaten')->LIKE('kabupaten', $caridata)->get();
            } else {
                $table = $this->db->table('t_kabupaten')->get();
            }
            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($table->getResultArray() as $row):
                    $list[$key]['id'] = $row['id_kab'];
                    $list[$key]['text'] = $row['kabupaten'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function getKecamatanMaster()
    {

        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            $kabupaten = $this->request->getGet('kabupaten');
            if ($caridata != '') {
                $table = $this->db->table('t_kecamatan')->LIKE('kecamatan', $caridata)->where('id_kab', $kabupaten)->get();
            } else {
                $table = $this->db->table('t_kecamatan')->where('id_kab', $kabupaten)->get();
            }
            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($table->getResultArray() as $row):
                    $list[$key]['id'] = $row['id_kec'];
                    $list[$key]['text'] = $row['kecamatan'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }


    public function filtering()
    {
        if ($this->request->isAJAX()) {
            $data = $this->request->getVar('data');
            if ($data == 'wilayah') {
                $kecamatan = $this->db->table('t_kecamatan')
                    ->select('id_kec as id, kecamatan as text')
                    ->get()->getResultArray();
                echo json_encode(['kecamatan' => $kecamatan]);
            } else if ($data == 'kelompok') {
                $kelompok = $this->db->table('m_jasa')
                    ->select('id_jasa as id, kelompok_jasa as text')
                    ->get()->getResultArray();
                echo json_encode(['kelompok' => $kelompok]);
            } else if ($data == 'golongan') {
                $golongan = $this->db->table('m_jenis_pelanggan')
                    ->select('id_jenis as id, jenis_pelanggan as text')
                    ->get()->getResultArray();
                echo json_encode(['golongan' => $golongan]);
            }
        }
    }

    public function FilterKelurahan()
    {
        if ($this->request->isAJAX()) {
            $id_kecamatan = $this->request->getVar('id');
            $kelurahan = $this->db->table('t_kelurahan')
                ->select('id_kelurahan as id, kelurahan as text')
                ->where('id_kec', $id_kecamatan)
                ->get()->getResultArray();
            echo json_encode(['kelurahan' => $kelurahan]);
        }
    }

    public function getBank()
    {
        if ($this->request->isAJAX()) {
            $table = $this->db->table('master_bank')->get();
            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($table->getResultArray() as $row):
                    $list[$key]['id'] = $row['NAMA_BANK'];
                    $list[$key]['text'] = $row['NAMA_BANK'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }
}
