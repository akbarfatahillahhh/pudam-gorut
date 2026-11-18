<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;



class Pelanggan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Pelanggan";
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/master/bs/pelanggan/main', $data);
    }

    public function pasangMeter()
    {
        $html = view('/master/bs/pelanggan/pasangMeter', ['title' => 'Pasang Meter']);
        echo json_encode(['html' => $html]);
    }

    public function dataPasangMeter()
    {
        $data = $this->db->table('t_pasang_baru')->select('t_pasang_baru.id_registrasi, t_pasang_baru.no_reg, t_pasang_baru.tgl_registrasi, t_pasang_baru.nama_lengkap, t_pasang_baru.alamat, t_pasang_baru.petugas_pemasang, t_pasang_baru.biaya_pemasangan, t_pasang_baru.status_pasang, m_jasa.kelompok_jasa, m_jenis_pelanggan.kd_jenis as golongan')
            ->join('m_jasa', 't_pasang_baru.id_jasa = m_jasa.id_jasa')
            ->join('m_jenis_pelanggan', 't_pasang_baru.id_jenis = m_jenis_pelanggan.id_jenis')
            ->where('status_pasang', 'N');

        return DataTable::of($data)
            ->add('aktif', function ($data) {
                return '<td><button class="btn btn-primary" onclick="setAktifPelanggan(' . $data->id_registrasi . ')">Aktifkan</button></td>';
            })
            ->toJson(true);
    }

    public function setAktifPelanggan()
    {
        $query = $this->db->table('t_pasang_baru')->where('id_registrasi', $this->request->getVar('id'))->update(['status_pasang' =>  'Y']);
        if ($query) {
            return json_encode(['success' => 'Pelanggan berhasil diaktifkan']);
        } else {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Pelanggan gagal diaktifkan']);
        }
    }

    public function data()
    {
        $filterPelanggan = $this->request->getVar('filterPelanggan');
        $filterKecamatan = $this->request->getVar('filterKecamatan');
        $filterKelurahan = $this->request->getVar('filterKelurahan');
        $filterKelompok = $this->request->getVar('filterKelompok');
        $filterGolongan = $this->request->getVar('filterGolongan');

        $builder = $this->db->table('m_pelanggan');
        $builder->select('m_pelanggan.id_pelanggan, m_pelanggan.no_sambungan, m_pelanggan.nama_pelanggan, m_pelanggan.status_aktif, m_pelanggan.pelanggan_khusus, t_kecamatan.kecamatan, t_kelurahan.kelurahan');
        $builder->select('m_jasa.kelompok_jasa, jenis_pelanggan.jenis_pelanggan as golongan');

        $builder->join('t_kecamatan', 'm_pelanggan.id_kec = t_kecamatan.id_kec');
        $builder->join('t_kelurahan', 'm_pelanggan.id_kelurahan = t_kelurahan.id_kelurahan');
        $builder->join('m_jasa', 'm_pelanggan.id_jasa = m_jasa.id_jasa');
        $builder->join('m_jenis_pelanggan as jenis_pelanggan', 'm_pelanggan.id_jenis = jenis_pelanggan.id_jenis');

        if ($filterPelanggan === 'wilayah') {
            if ($filterKelurahan != '' && is_numeric($filterKelurahan)) {
                $builder->where('m_pelanggan.id_kelurahan', $filterKelurahan);
            } else if ($filterKecamatan != '' && is_numeric($filterKecamatan)) {
                $builder->where('m_pelanggan.id_kec', $filterKecamatan);
            }
        } elseif ($filterPelanggan === 'kelompok' && $filterKelompok != '' && is_numeric($filterKelompok)) {
            $builder->where('m_pelanggan.id_jasa', $filterKelompok);
        } elseif ($filterPelanggan === 'golongan' && $filterGolongan != '' && is_numeric($filterGolongan)) {
            $builder->where('m_pelanggan.id_jenis', $filterGolongan);
        }

        return DataTable::of($builder)
            ->add('action', function ($data) {
                return '

                 <td class="text-center align-middle">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v text-black"></i>
                                    </button>
                                    <ul class="dropdown-menu border border-dark py-0">
                                        <li>
                                            <a onclick="pcm(' . $data->id_pelanggan . ')" class="dropdown-item text-black" href="javascript:void(0)">
                                                <i class="fas fa-cogs text-indigo"></i>
                                                Mapping PCM
                                            </a>
                                        </li>
                                        <li class="border-top border-dark">
                                            <a onclick="edit(' . $data->id_pelanggan . ')" class="dropdown-item text-black" href="javascript:void(0)">
                                                <i class="fa-solid fa-pen-to-square text-warning"></i>
                                                Edit
                                            </a>
                                        </li>
                                        <li class="visually-hidden">
                                            <a onclick="hapus(' . $data->id_pelanggan . ')"  class="dropdown-item text-black" href="javascript:void(0)">
                                                <i  class="fa-solid fa-trash text-danger"></i>
                                                Hapus
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                            ';
            })
            ->toJson(true);  // Mengembalikan data dalam format JSON    
    }

    public function save()
    {
        $data = $this->request->getVar();
        $data += ['petugas' => session('nama_lengkap')];
        $kd_kec = $this->db->table('t_kecamatan')->where('id_kec', $data['id_kec'])->get()->getRowArray();
        if ($data['id_jenis'] == 1) {
            $noRegistrasi = $kd_kec['kd_kecamatan'] . '-HU-' . $data['no_reg'];
        } else {

            $noRegistrasi = $kd_kec['kd_kecamatan'] . '-' . $data['no_reg'];
        }
        $data['no_reg'] = $noRegistrasi;
        if ($data['status_pendaftaran'] == 'Pelanggan Baru') {
            $save = $this->db->table('t_pasang_baru')->insert($data);

            $pelanggan = $this->db->table('m_pelanggan')->insert([
                'no_sambungan' => $data['no_reg'],
                'nama_pelanggan' => $data['nama_lengkap'],
                'id_jasa' => $data['id_jasa'],
                'id_jenis' => $data['id_jenis'],
                'id_kab' => $data['id_kab'],
                'id_kec' => $data['id_kec'],
                'id_kelurahan' => $data['id_kel'],
                'alamat' => $data['alamat'],
                'tgl_terdaftar' => $data['tgl_registrasi'],
                'status_pendaftaran' => $data['status_pendaftaran'],
                'nik' => $data['NIK'],
            ]);
        } else {
            $pelanggan = $this->db->table('m_pelanggan')->insert([
                'no_sambungan' => $data['no_reg'],
                'nama_pelanggan' => $data['nama_lengkap'],
                'id_jasa' => $data['id_jasa'],
                'id_jenis' => $data['id_jenis'],
                'id_kab' => $data['id_kab'],
                'id_kec' => $data['id_kec'],
                'id_kelurahan' => $data['id_kel'],
                'alamat' => $data['alamat'],
                'tgl_terdaftar' => $data['tgl_registrasi'],
                'status_pendaftaran' => $data['status_pendaftaran'],
                'nik' => $data['NIK'],
            ]);
        }
        return $this->response->setJSON(['success' => true, 'message' => 'Data saved successfully']);
    }

    public function editStatus()
    {
        $id_pelanggan = $this->request->getVar('id_pelanggan');

        $builder = $this->db->table('m_pelanggan');
        $status = $builder->select('status_aktif')
            ->where('id_pelanggan', $id_pelanggan)
            ->get()
            ->getRowArray();

        if ($status) {
            $builder->where('id_pelanggan', $id_pelanggan)
                ->update(['status_aktif' => $status['status_aktif'] == '1' ? '0' : '1']);

            return $this->response->setJSON(['success' => true, 'message' => 'Data saved successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Data pelanggan tidak ditemukan.']);
        }
    }

    public function editKhusus()
    {
        $id_pelanggan = $this->request->getVar('id_pelanggan');

        $builder = $this->db->table('m_pelanggan');
        $status = $builder->select('pelanggan_khusus')
            ->where('id_pelanggan', $id_pelanggan)
            ->get()
            ->getRowArray();

        if ($status) {
            $builder->where('id_pelanggan', $id_pelanggan)
                ->update(['pelanggan_khusus' => $status['pelanggan_khusus'] == 'Y' ? 'N' : 'Y']);

            return $this->response->setJSON(['success' => true, 'message' => 'Data saved successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Data pelanggan tidak ditemukan.']);
        }
    }

    public function new()
    {
        $NoSambungan = $this->db->table('t_kecamatan')->select('id_kec, kecamatan')->groupBy('id_kec, kecamatan')->get()->getResultArray();
        $kelompok_tarif = $this->db->table('m_jasa')->select('id_jasa, kelompok_jasa')->groupBy('id_jasa, kelompok_jasa')->get()->getResultArray();
        $html =  view('/master/bs/pelanggan/modal/new', ['title' => $this->title, 'sambungan' => $NoSambungan, 'kelompok_tarif' => $kelompok_tarif]);
        echo json_encode(['html' => $html]);
    }

    public function getNoSambungan()
    {
        $id_kec = $this->request->getVar('id_kec');

        $data = $this->db->table('m_pelanggan')
            ->select('MAX(id_pelanggan) AS id_pelanggan, id_kab, nama_pelanggan')
            ->where('id_kec', $id_kec)
            ->get()
            ->getRowArray();
        $kabupaten = $this->db->table('t_kabupaten')->select('kabupaten, id_kab')->where('id_kab', $data['id_kab'])->get()->getRowArray();
        $msg = [
            'data' => $data['id_pelanggan'] + 1,
            'kab' => $kabupaten['kabupaten'],
            'id_kab' => $data['id_kab']

        ];
        // var_dump($msg);
        echo json_encode($msg);
    }


    public function edit()
    {
        $data = $this->db->table('m_pelanggan')
            ->select('m_pelanggan.*, t_kabupaten.id_kab, t_kabupaten.kabupaten, t_kecamatan.id_kec, t_kecamatan.kecamatan, t_kelurahan.id_kelurahan, t_kelurahan.kelurahan')
            ->where('id_pelanggan', $this->request->getVar('id'))
            ->join('t_kabupaten', 't_kabupaten.id_kab = m_pelanggan.id_kab')
            ->join('t_kecamatan', 'm_pelanggan.id_kec = t_kecamatan.id_kec')
            ->join('t_kelurahan', 'm_pelanggan.id_kelurahan = t_kelurahan.id_kelurahan')->get()->getRowArray();
        $kab = $this->db->table('t_kabupaten')->select('*')
            ->get()->getResultArray();
        $kec = $this->db->table('t_kecamatan')->select('*')->where('id_kab', $data['id_kab'])->get()->getResultArray();
        $kel = $this->db->table('t_kelurahan')->select('*')->where('id_kec', $data['id_kec'])->get()->getResultArray();


        $jasa = $this->db->table('m_jasa')
            ->select('m_jasa.id_jasa, m_jasa.kelompok_jasa')
            ->groupBy('m_jasa.id_jasa, m_jasa.kelompok_jasa')
            ->get()
            ->getResultArray();


        $nama_jenis = $this->db->table('m_jenis_pelanggan')->get()->getResultArray();
        $html =  view('/master/bs/pelanggan/modal/edit', ['data' => $data,  'jasa' => $jasa,  'kab' => $kab, 'kec' => $kec, 'kel' => $kel,  'title' => $this->title, 'nama_jenis' => $nama_jenis]);

        echo json_encode(['html' => $html]);
    }

    public function pcm()
    {
        $id_pelanggan = $this->request->getVar('id');

        $pelanggan = $this->db->table('m_pelanggan')
            ->select('m_pelanggan.no_sambungan, m_pelanggan.nama_pelanggan, m_pelanggan.petugas_meter')
            ->select('t_kecamatan.kecamatan')
            ->where('m_pelanggan.id_pelanggan', $id_pelanggan)
            ->join('t_kecamatan', 'm_pelanggan.id_kec = t_kecamatan.id_kec', 'left')
            ->get()->getRowArray();

        $pcm = $this->db->table('users')
            ->select('nama_lengkap, id_session')
            ->where('mobile_akses', '2')
            ->get()->getResultArray();

        $data = [
            'pcm' => $pcm,
            'id_pelanggan' => $id_pelanggan,
            'no_sambungan' => $pelanggan['no_sambungan'],
            'nama_pelanggan' => $pelanggan['nama_pelanggan'],
            'unit' => $pelanggan['kecamatan'],
            'petugas_meter' => $pelanggan['petugas_meter']
        ];

        $html =  view('/master/bs/pelanggan/modal/pcm', $data);
        echo json_encode(['html' => $html]);
    }

    public function updatePcm()
    {
        $id_pelanggan = $this->request->getVar('id_pelanggan');
        $id_pcm = $this->request->getVar('id_pcm');

        $data = [
            'petugas_meter' => $id_pcm
        ];

        $query = $this->db->table('m_pelanggan')
            ->where('id_pelanggan', $id_pelanggan)
            ->update($data);

        if ($query) {
            echo json_encode(['message' => 'Data updated successfully']);
        } else {
            $message = ['message' => 'Data updated failed'];
            $this->response->setStatusCode(400)->setJSON($message);
        }
    }

    public function getJenis()
    {
        $id = $this->request->getVar('id');
        $query = $this->db->table('m_jenis_pelanggan')->where('id_jasa', $id)->get()->getResultArray();
        if ($query != null) {

            $data = '';
            $data .= '<option>[ Pilih Jenis Pelanggan ]</option>';
            foreach ($query as $row) {
                $data .= '<option value="' . $row['id_jenis'] . '">' . $row['jenis_pelanggan'] . '</option>';
            }

            $msg = [
                'data' => $data
            ];

            echo json_encode($msg);
        } else {
            $msg = [
                'data' => "<option>Data tidak ditemukan</option>"
            ];
            echo json_encode($msg);
        }
    }

    public function getkecamatan()
    {
        if ($this->request->isAJAX()) {
            $kabupaten = $this->request->getVar('kabupaten');
            $dtkecamatan = $this->db->table('t_kecamatan')->where('id_kab', $kabupaten)->get();

            if ($dtkecamatan->getNumRows() > 0) {

                $isidata = "";
                $isidata .= "<option>[ Pilih Kecamatan ]</option>";
                foreach ($dtkecamatan->getResultArray() as $row) :

                    $isidata .= '<option value="' . $row['id_kec'] . '">' . $row['kecamatan'] . '</option>';

                endforeach;

                $msg = [
                    'data' => $isidata
                ];

                echo json_encode($msg);
            } else {
                $msg = [
                    'data' => "<option>Data tidak ditemukan</option>"
                ];
                echo json_encode($msg);
            }
        }
    }

    public function getkelurahan()
    {
        if ($this->request->isAJAX()) {
            $kecamatan = $this->request->getVar('kecamatan');

            $dtkelurahan = $this->db->table('t_kelurahan')->where('id_kec', $kecamatan)->get();

            if ($dtkelurahan->getNumRows() > 0) {

                $isidata = "";
                $isidata .= "<option>[ Pilih Kelurahan ]</option>";
                foreach ($dtkelurahan->getResultArray() as $row) :

                    $isidata .= '<option value="' . $row['id_kelurahan'] . '">' . $row['kelurahan'] . '</option>';

                endforeach;

                $msg = [
                    'data' => $isidata
                ];

                echo json_encode($msg);
            } else {
                $msg = [
                    'data' => "<option>Data tidak ditemukan</option>"
                ];
                echo json_encode($msg);
            }
        }
    }

    public function update()
    {
        $data = $this->request->getVar();
        $query = $this->db->table('m_pelanggan')->where('id_pelanggan', $data['id_pelanggan'])->update($data);
        if ($query) {
            echo json_encode(['message' => 'Data updated successfully']);
        } else {
            $message = ['message' => 'Data updated failed'];
            $this->response->setStatusCode(400)->setJSON($message);
        }
    }

    public function getJenisPelanggan()
    {
        $id_pelanggan = $this->request->getVar('pelanggan');
    }

    public function delete()
    {
        $id_pelanggan = $this->request->getVar('id_pelanggan');
        $this->db->table('m_pelanggan')->where('id_pelanggan', $id_pelanggan)->delete();
        echo json_encode(['message' => 'Data deleted successfully']);
    }

    public function modal_export_pdf()
    {
        $msg = ['html' => view('master/bs/pelanggan/modal/pdf')];
        echo json_encode($msg);
    }


    public function export_pdf($pelanggan, $kecamatan, $kelurahan, $kelompok, $golongan)
    {
        ini_set('pcre.backtrack_limit', '5000000');
        ini_set('pcre.recursion_limit', '1000000');

        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>DATA PELANGGAN</h3>";
        $html .= "<p align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>PERIODE : " . date('d/m/Y') . "</b></p>";

        $builder = $this->db->table('m_pelanggan');
        $builder->select('m_pelanggan.id_pelanggan, m_pelanggan.no_sambungan, m_pelanggan.nama_pelanggan, m_pelanggan.status_aktif, m_pelanggan.pelanggan_khusus, t_kecamatan.kecamatan, t_kelurahan.kelurahan');
        $builder->select('m_jasa.kelompok_jasa, jenis_pelanggan.jenis_pelanggan as golongan');

        $builder->join('t_kecamatan', 'm_pelanggan.id_kec = t_kecamatan.id_kec');
        $builder->join('t_kelurahan', 'm_pelanggan.id_kelurahan = t_kelurahan.id_kelurahan');
        $builder->join('m_jasa', 'm_pelanggan.id_jasa = m_jasa.id_jasa');
        $builder->join('m_jenis_pelanggan as jenis_pelanggan', 'm_pelanggan.id_jenis = jenis_pelanggan.id_jenis');

        if ($pelanggan === 'wilayah') {
            if ($kelurahan != '' && $kelurahan != 'ALL'  && is_numeric($kelurahan)) {
                $builder->where('m_pelanggan.id_kelurahan', $kelurahan);
                $builder->orderBy('m_pelanggan.id_kelurahan');
            } else if ($kecamatan != '' && $kecamatan != 'ALL'  && is_numeric($kecamatan)) {
                $builder->where('m_pelanggan.id_kec', $kecamatan);
                $builder->orderBy('m_pelanggan.id_kec');
            }
        } elseif ($pelanggan === 'kelompok' && $kelompok != '' && $kelompok != 'ALL'  && is_numeric($kelompok)) {
            $builder->where('m_pelanggan.id_jasa', $kelompok);
            $builder->orderBy('m_pelanggan.id_jasa');
        } elseif ($pelanggan === 'golongan' && $golongan != '' && $golongan != 'ALL'  && is_numeric($golongan)) {
            $builder->where('m_pelanggan.id_jenis', $golongan);
            $builder->orderBy('m_pelanggan.id_jenis');
        } else {
            $builder->orderBy('m_pelanggan.id_pelanggan');
        }

        $result = $builder->get()->getResultArray();

        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="3%">No</th>';
        $html .= '<th width="10%">No. Sambungan</th>';
        $html .= '<th width="20%">Nama Pelanggan</th>';
        $html .= '<th width="10%">Kecamatan</th>';
        $html .= '<th width="10%">Kelurahan/Desa</th>';
        $html .= '<th width="10%">Status Aktif</th>';
        $html .= '<th width="10%">Status Khusus</th>';
        $html .= '<th  width="10%">Kelompok</th>';
        $html .= '<th  width="10%">Golongan</th>';
        $html .= '</tr>';

        if ($result) {
            foreach ($result as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td class="text-center">' . $value['no_sambungan'] . '</td>';
                $html .= '<td>' . $value['nama_pelanggan'] . '</td>';
                $html .= '<td class="text-center">' . $value['kecamatan'] . '</td>';
                $html .= '<td class="text-center">' . $value['kelurahan'] . '</td>';
                $html .= '<td class="text-center">' . ($value['status_aktif'] == 1 ? '<b style="color: green">Aktif</b>' : '<b style="color: red">Tidak Aktif</b>') . '</td>';
                $html .= '<td class="text-center">' . ($value['pelanggan_khusus'] == 'Y' ? '<b style="color: green">Ya</b>' : '<b style="color: red">Tidak</b>') . '</td>';
                $html .= '<td class="text-center">' . $value['kelompok_jasa'] . '</td>';
                $html .= '<td class="text-center">' . $value['golongan'] . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td colspan="9" class="text-center">Tidak Ada Data</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

        $data = ['html' => $html];
        $page = view('pdf/potrait', $data);

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L',
            'default_font_size' => 10,
            'default_font' => 'arial',
            'tempDir' => __DIR__ . '/temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle("PELANGGAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
