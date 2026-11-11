<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Pegawai extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Pegawai';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/master/pegawai/main', $data);
    }

    public function data()
    {
        $query = $this->db
            ->table('pegawai')
            ->select('
                pegawai.KODE,
                pegawai.ID,
                pegawai.NAMA,
                pegawai.NIK,
                pegawai.TEMPAT_LAHIR,
                pegawai.TGL_LAHIR,
                pegawai.KELAMIN,
                pegawai.ALAMAT,
                pegawai.STATUS,
                pegawai.FOTO,
                pegawai.ALAMAT,

                master_kelurahan.KELURAHAN,
                master_kecamatan.KECAMATAN,
                master_kabupaten.KABUPATEN,
                master_provinsi.PROVINSI
            ')
            ->join('master_kelurahan', 'pegawai.KELURAHAN_ID = master_kelurahan.ID', 'left')
            ->join('master_kecamatan', 'pegawai.KECAMATAN_ID = master_kecamatan.ID', 'left')
            ->join('master_kabupaten', 'pegawai.KABUPATEN_ID = master_kabupaten.ID', 'left')
            ->join('master_provinsi', 'pegawai.PROVINSI_ID = master_provinsi.ID', 'left');

        return DataTable::of($query)
            ->add('KODE', function ($data) {
                return $data->KODE . '-PUDAM';
            })
            ->add('TGL_LAHIR', function ($data) {
                return date('d M Y', strtotime($data->TGL_LAHIR));
            })
            ->add('aktif', function ($data) {
                $status = $data->STATUS == 'Aktif' ? 'bg-danger' : 'bg-success';
                $icon = $data->STATUS == 'Aktif' ? 'fa-ban' : 'fa-unlock';
                $text = $data->STATUS == 'Aktif' ? 'Blokir' : 'Aktif';

                return "<a href='javascript:void(0)' class='badge fs-1 fw-semibold text-uppercase rounded $status edit' onclick='setStatus($data->ID, \"$data->STATUS\", \"$data->NIK\")'>
                    <i class='fa $icon'></i> $text
                </a>";
            })
            ->add('img', function ($data) {
                return "<img src='" . base_url('assets/images/profile/' . $data->FOTO) . "' class='img-thumbnail' width='100px' height='100px' alt=''>";
            })
            ->add('aksi', function ($data) {
                return "<div class='dropdown'>
                                <button class='btn btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                    <i class='fa fa-ellipsis-v text-black'></i>
                                </button>
                                <ul class='dropdown-menu py-0'>
                                    <li>
                                       <a href='javascript:void(0)' class='dropdown-item text-black ' onclick='setPassword(" .
                    $data->ID .
                    ")'><i class='fa fa-key text-warning'></i>
                                            Ubah Password
                                        </a>
                                    </li>
                                    <li>
                                       <a href='javascript:void(0)' class='dropdown-item text-black ' onclick='details(" .
                    $data->ID .
                    ")'><i class='fa fa-info-circle text-info'></i>
                                           Details
                                        </a>
                                    </li>
                                </ul>
                            </div>

                ";
            })
            ->toJson(true);
    }

    public function setStatus()
    {
        $id = $this->request->getVar('ID');
        $status = $this->request->getVar('status');
        $nik_blok = $this->request->getVar('nikblok');

        $UpdateNik = $nik_blok . '_blokir_' . date('Y-m-d');

        $row = $this->db->table('pegawai')->where('ID', $id)->get()->getRow();

        if ($status == 'Aktif') {
            if ($row) {
                $ubahStatus = $this->db
                    ->table('pegawai')
                    ->where('ID', $id)
                    ->update(['STATUS' => $status, 'TAMPUNG_NIK_NLOKIR' => $nik_blok, 'NIK' => $row->TAMPUNG_NIK_NLOKIR]);
            }
        } else {
            $ubahStatus = $this->db
                ->table('pegawai')
                ->where('ID', $id)
                ->update(['STATUS' => $status, 'TAMPUNG_NIK_NLOKIR' => $nik_blok, 'NIK' => $UpdateNik]);
        }
        if ($ubahStatus) {
            echo json_encode(['status' => 'success', 'msg' => 'Status berhasil diubah']);
        } else {
            echo json_encode(['status' => 'success', 'msg' => 'Status gagal diubah']);
        }
    }

    public function setPassword()
    {
        $id = $this->request->getVar('ID');
        $user = $this->db->table('pegawai')->where('ID', $id)->get()->getRow();
        $html = ['html' => view('/master/pegawai/modal/edit', ['ID' => $id, 'title' => $this->title, 'NAMA' => $user->NAMA])];
        echo json_encode($html);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $data['KODE'] = $this->db->table('pegawai')->selectMax('KODE')->get()->getRow()->KODE + 1;
        $html = ['html' => view('/master/pegawai/modal/new', $data)];
        echo json_encode($html);
    }

    public function master()
    {
        $data = $this->request->getPost();
        // var_dump($data);
        $query = $this->db->table('pegawai');
        if (isset($data['ID']) && isset($data['PASSWORD'])) {
            $pass = password_hash($data['PASSWORD'], PASSWORD_BCRYPT);
            $query->where('ID', $data['ID'])->update([
                'PASSWORD' => $pass,
            ]);
        } else {
            $pass = password_hash($data['NIK'], PASSWORD_BCRYPT);
            $query->insert([
                'KODE' => $data['KODE'],
                'NAMA' => $data['NAMA'],
                'NIK' => $data['NIK'],
                'TEMPAT_LAHIR' => $data['TEMPAT_LAHIR'],
                'TGL_LAHIR' => $data['TGL_LAHIR'],
                'KELAMIN' => $data['KELAMIN'],
                'KELURAHAN_ID' => $data['KELURAHAN'],
                'KECAMATAN_ID' => $data['KECAMATAN'],
                'KABUPATEN_ID' => $data['KABUPATEN'],
                'PROVINSI_ID' => $data['PROVINSI'],
                'ALAMAT' => $data['ALAMAT'],
                'STATUS' => 'Aktif',
                'PASSWORD' => $pass,
                'LATITUDE' => $data['LATITUDE'],
                'LONGITUDE' => $data['LONGTITUDE'],
            ]);
        }
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
    }

    public function details()
    {
        $id = $this->request->getVar('ID');
        $data = $this->db->table('pegawai')->where('ID', $id)->get()->getRow();

        $detail = [
            'id_pegawai' => $data->ID,
            'nama' => $data->NAMA,
        ];

        $html = ['html' => view('/master/pegawai/modal/details', ['detail' => $detail, 'title' => $this->title])];
        echo json_encode($html);
    }

    public function details_modal()
    {
        $modal = $this->request->getVar('modal');
        $id_pegawai = $this->request->getVar('id_pegawai');

        if ($modal == 'info_personal') {
            $pegawai = $this->db->table('pegawai')
                ->select('pegawai.ID, pegawai.NIK, pegawai.NAMA, pegawai.TGL_LAHIR, pegawai.TEMPAT_LAHIR, pegawai.KELAMIN')
                ->select('pegawai.KELURAHAN_ID, pegawai.KECAMATAN_ID, pegawai.KABUPATEN_ID, pegawai.PROVINSI_ID')
                ->select('pegawai.LATITUDE, pegawai.LONGITUDE')
                ->select('master_provinsi.PROVINSI')
                ->select('master_kabupaten.KABUPATEN')
                ->select('master_kecamatan.KECAMATAN')
                ->select('master_kelurahan.KELURAHAN')
                // ->select('master_provinsi.PROVINSI, master_kabupaten.KABUPATEN, master_kecamatan.KECAMATAN, master_kelurahan.KELURAHAN')
                ->join('master_kelurahan', 'pegawai.KELURAHAN_ID = master_kelurahan.ID', 'left')
                ->join('master_provinsi', 'pegawai.PROVINSI_ID = master_provinsi.ID', 'left')
                ->join('master_kecamatan', 'pegawai.KECAMATAN_ID = master_kecamatan.ID')
                ->join('master_kabupaten', 'pegawai.KABUPATEN_ID = master_kabupaten.ID')
                ->where('pegawai.ID', $id_pegawai)
                ->get()->getRow();


            $titik_absen = $this->db->table('m_titik_absen')
                ->get()->getResultArray();

            $data = [
                'pegawai' => $pegawai,
                'titik_absen' => $titik_absen,
            ];
        } else if ($modal == 'e_kinerja') {
            $data = ['pegawai_id' => $id_pegawai];
        } else if ($modal == 'info_absensi') {
            $data = ['pegawai_id' => $id_pegawai];
        } else if ($modal == 'info_pendidikan') {
            $data = ['pegawai_id' => $id_pegawai];
        } else if ($modal == 'info_pelatihan') {
            $data = ['pegawai_id' => $id_pegawai];
        } else if ($modal == 'info_jabatan') {
            $jabatan = $this->db->table('jabatan')
                ->get()->getResultArray();

            $data = [
                'jabatan' => $jabatan,
                'pegawai_id' => $id_pegawai
            ];
        } else if ($modal == 'laporan_absensi') {
            $data = [
                'pegawai_id' => $id_pegawai
            ];
        }

        $html = ['html' => view('master/pegawai/modal/component/' . $modal, ($data ?? []))];

        echo json_encode($html);
    }

    function get_data_kinerja()
    {
        $id_pegawai = $this->request->getVar('id_pegawai');
        $tglAwal = $this->request->getVar('tglAwal');
        $tglAkhir = $this->request->getVar('tglAkhir');

        $eKinerja = $this->db->table('ekinerja');
        $eKinerja->select('pegawai.NAMA');
        $eKinerja->select('ekinerja.*');
        $eKinerja->select('tugas_pokok.TUGAS_POKOK');
        $eKinerja->where('PEGAWAI_ID', $id_pegawai);
        $eKinerja->where('TANGGAL_KINERJA >=', $tglAwal);
        $eKinerja->where('TANGGAL_KINERJA <=', $tglAkhir);
        $eKinerja->join('pegawai', 'pegawai.ID = ekinerja.PEGAWAI_ID', 'left');
        $eKinerja->join('tugas_pokok', 'tugas_pokok.ID = ekinerja.TUGAS_POKOK_ID');
        $eKinerja->orderBy('ekinerja.TANGGAL_KINERJA', 'ASC');
        $result = $eKinerja->get()->getResultArray();

        $html = '';
        if ($result) {
            foreach ($result as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . ($key + 1) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . date('d/m/Y', strtotime($value['TANGGAL_KINERJA'])) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . $value['TUGAS_POKOK'] . '</td>';
                $html .= '<td class="align-middle text-black fs-2">' . $value['KETERANGAN_LAINNYA'] . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2"><span class="fw-bolder text-primary pointer" onclick="lihatKinerja(' . $value['UPLOAD_BUKTI'] . ')">Lihat</span></td>';
                $html .= '<td class="align-middle text-black text-center fs-2">';
                $html .= '<div class="dropdown">';
                $html .= '<button class="btn btn-sm " type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="true">';
                $html .= '<i class="fa fa-ellipsis-v text-black"></i>';
                $html .= '</button>';
                $html .= '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                $html .= '<li class="dropdown-item">';
                $html .= '<a class="text-black" href="javascript:void(0)" onclick="hapusKinerja(' . $value['ID'] . ')"><i class="fa fa-trash pe-1 text-danger"></i> HAPUS</a>';
                $html .= '</li>';
                $html .= '</ul>';
                $html .= '</div>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td class="align-middle text-black text-center fs-2" colspan="8">Tidak ada data</td>';
            $html .= '</tr>';
        }

        echo json_encode(['html' => $html]);
    }

    public function kinerjaPdf()
    {
        helper('tanggal');
        $tglAwal = $this->request->getGet('tglAwal');
        $tglAkhir = $this->request->getGet('tglAkhir');
        $pegawai = $this->request->getGet('idPegawai');

        $eKinerja = $this->db->table('ekinerja');
        $eKinerja->select('pegawai.NAMA');
        $eKinerja->select('ekinerja.*');
        $eKinerja->select('tugas_pokok.TUGAS_POKOK');
        $eKinerja->where('PEGAWAI_ID', $pegawai);
        $eKinerja->where('TANGGAL_KINERJA >=', $tglAwal);
        $eKinerja->where('TANGGAL_KINERJA <=', $tglAkhir);
        $eKinerja->join('pegawai', 'pegawai.ID = ekinerja.PEGAWAI_ID', 'left');
        $eKinerja->join('tugas_pokok', 'tugas_pokok.ID = ekinerja.TUGAS_POKOK_ID');
        $eKinerja->orderBy('ekinerja.TANGGAL_KINERJA', 'ASC');
        $result = $eKinerja->get()->getResultArray();


        $table = '';

        $table .= '
        <div style="text-align: center;">
            <h4 style="font-weight: bold;">LAPORAN AKTIVITAS HARIAN PERIODE<br>
            ' . (empty($result) ? 'Tidak ada data' : tanggal_angka_ke_text($tglAwal) . ' - ' .  tanggal_angka_ke_text($tglAkhir) . '<br><span style="color: red;">' . ($result[0]['NAMA'] ?? '') . '</span>') . '</h4>    
        </div>
        <table style="width: 100%; border-collapse: collapse; ">
            <thead>
                <tr>
                    <th style="border: 1px solid black; padding: 5px; text-align: center;">NO</th>
                    <th style="border: 1px solid black; padding: 5px; text-align: center;">TANGGAL</th>
                    <th style="border: 1px solid black; padding: 5px; text-align: center;">TUGAS POKOK</th>
                    <th style="border: 1px solid black; padding: 5px; text-align: center;">URAIAN KEGIATAN</th>
                    <th style="border: 1px solid black; padding: 5px; text-align: center;">BUKTI</th>
                </tr>
            </thead>
            <tbody>';
        if ($result) {
            $no = 1;
            foreach ($result as $key => $value) {
                $table .= '
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">' . $no++ . '</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">' . date('d-m-Y', strtotime($value['TANGGAL_KINERJA'])) . '</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: start;"> ' . preg_replace('/[\d\.]/', '', $value['TUGAS_POKOK']) . '</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: start;">' . preg_replace('/[\d\.]/', '', $value['KETERANGAN_LAINNYA']) . '</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">Bukti</td>
                    </tr>';
            }
        } else {
            $table .= '<tr><td colspan="5" style=" border: 1px solid black; text-align: center;">Tidak ada data</td></tr>';
        }
        $table .= ' </tbody>
         </table>';

        $view =  view('/pdf/potrait', ['html' => $table]);

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'Legal',
            'orientation' => 'P',
        ]);
        $mpdf->setFooter('LAPORAN AKTIVITAS HARIAN');
        $mpdf->WriteHTML($view);
        $mpdf->SetTitle('LAPORAN AKTIVITAS HARIAN');
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output('LAPORAN AKTIVITAS HARIAN.pdf', 'I');
    }

    public function saveBiodata()
    {
        $id_pegawai = $this->request->getPost('id_pegawai');
        $nik = $this->request->getPost('nik');
        $nama = $this->request->getPost('nama');
        $tanggal_lahir = $this->request->getPost('tanggal_lahir');
        $tempat_lahir = $this->request->getPost('tempat_lahir');
        $jenis_kelamin = $this->request->getPost('jenis_kelamin');

        $data = [
            'NIK' => $nik,
            'NAMA' => $nama,
            'TGL_LAHIR' => $tanggal_lahir,
            'TEMPAT_LAHIR' => $tempat_lahir,
            'KELAMIN' => $jenis_kelamin
        ];

        $pegawai = $this->db->table('pegawai');
        $pegawai->where('ID', $id_pegawai);
        $pegawai->update($data);

        echo json_encode(['status' => 'success', 'msg' => 'Biodata berhasil disimpan']);
    }
    public function saveAlamat()
    {
        $id_pegawai = $this->request->getPost('id_pegawai');
        $provinsi = $this->request->getPost('provinsi');
        $kabupaten = $this->request->getPost('kabupaten');
        $kecamatan = $this->request->getPost('kecamatan');
        $kelurahan = $this->request->getPost('kelurahan');

        $data = [
            'PROVINSI_ID' => $provinsi,
            'KABUPATEN_ID' => $kabupaten,
            'KECAMATAN_ID' => $kecamatan,
            'KELURAHAN_ID' => $kelurahan
        ];

        $pegawai = $this->db->table('pegawai');
        $pegawai->where('ID', $id_pegawai);
        $pegawai->update($data);

        echo json_encode(['status' => 'success', 'msg' => 'Alamat berhasil disimpan']);
    }
    public function saveTitikAbsen()
    {
        $id_pegawai = $this->request->getPost('id_pegawai');
        $lat = $this->request->getPost('lat');
        $long = $this->request->getPost('long');

        $data = [
            'LATITUDE' => $lat,
            'LONGITUDE' => $long
        ];

        $pegawai = $this->db->table('pegawai');
        $pegawai->where('ID', $id_pegawai);
        $pegawai->update($data);

        echo json_encode(['status' => 'success', 'msg' => 'Titik Absen berhasil disimpan']);
    }

    public function saveJabatan()
    {
        $id_pegawai = $this->request->getPost('id_pegawai');
        $no_sk = $this->request->getPost('val-no_sk');
        $jabatan = $this->request->getPost('val-jabatan');
        $tgl_mulai = $this->request->getPost('val-tgl_mulai');
        $tgl_selesai = $this->request->getPost('val-tgl_selesai');
        $status_pegawai = $this->request->getPost('val-status_pegawai');

        $this->db->table('riwayat_jabatan')
            ->where('ID_PEGAWAI', $id_pegawai)
            ->update(['STATUS' => 'Selesai']);

        $data = [
            'ID_PEGAWAI' => $id_pegawai,
            'NO_SK' => $no_sk,
            'ID_JABATAN' => $jabatan,
            'TGL_SK' => $tgl_mulai,
            'TGL_SELESAI' => $tgl_selesai,
            'STATUS_PEGAWAI' => $status_pegawai,
            'BERKAS' => '-',
            'STATUS' => 'Aktif'
        ];

        $this->db->table('riwayat_jabatan')->insert($data);

        echo json_encode(['status' => 'success', 'msg' => 'Jabatan berhasil disimpan']);
    }

    public function saveKinerja()
    {
        $id_pegawai = $this->request->getPost('id_pegawai');

        $tugas_pokok = $this->request->getPost('val-tugas-pokok');
        $uraian_kegiatan = $this->request->getPost('val-uraian-kegiatan');
        $tgl_kegiatan = $this->request->getPost('val-tgl-kegiatan');

        $bukti_kegiatan = $this->request->getFile('val-bukti-kegiatan');

        $newFileName = "kinerja-" . $id_pegawai . "-" . date("d-m-y") . "-" . $bukti_kegiatan->getRandomName();
        $uploadDir = ROOTPATH . 'perumda/kinerja';
        $bukti_kegiatan->move($uploadDir, $newFileName);

        $bukti = NULL;
        if ($bukti_kegiatan->isValid()) {
            $bukti = $newFileName;
        }

        $data = [
            'PEGAWAI_ID' => $id_pegawai,
            'TANGGAL_KINERJA' => $tgl_kegiatan,
            'TUGAS_POKOK_ID' => $tugas_pokok,
            'KETERANGAN_LAINNYA' => $uraian_kegiatan,
            'UPLOAD_BUKTI' => $bukti
        ];

        $this->db->table('ekinerja')->insert($data);

        echo json_encode(['status' => 'success', 'msg' => 'Kinerja berhasil disimpan']);
    }

    function get_data_jabatan()
    {
        $id_pegawai = $this->request->getVar('id_pegawai');

        $riwayat_jabatan = $this->db->table('riwayat_jabatan')
            ->select('riwayat_jabatan.*')
            ->select('jabatan.JABATAN')
            ->where('ID_PEGAWAI', $id_pegawai)
            ->join('jabatan', 'riwayat_jabatan.ID_JABATAN =  jabatan.ID')
            ->orderBy('riwayat_jabatan.ID', 'DESC')
            ->get()->getResultArray();

        $html = '';
        if ($riwayat_jabatan) {
            foreach ($riwayat_jabatan as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . ($key + 1) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . $value['NO_SK'] . '</td>';
                $html .= '<td class="align-middle text-black fs-2">' . $value['JABATAN'] . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . date('d/m/Y', strtotime($value['TGL_SK'])) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . date('d/m/Y', strtotime($value['TGL_SELESAI'])) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . ($value['STATUS_PEGAWAI'] == 'Tetap' ? '<span class="badge fs-2 rounded bg-primary">' . $value['STATUS_PEGAWAI'] . '</span>' : '<span class="badge fs-2 rounded bg-dark">' . $value['STATUS_PEGAWAI'] . '</span>') . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . ($value['STATUS'] == 'Aktif' ? '<span class="badge fs-2 rounded bg-success">' . $value['STATUS'] . '</span>' : '<span class="badge fs-2 rounded bg-danger">' . $value['STATUS'] . '</span>') . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">';
                $html .= '<div class="dropdown">';
                $html .= '<button class="btn btn-sm " type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="true">';
                $html .= '<i class="fa fa-ellipsis-v text-black"></i>';
                $html .= '</button>';
                $html .= '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                $html .= '<li class="dropdown-item">';
                $html .= '<a class="text-black" href="javascript:void(0)" onclick="hapusJabatan(' . $value['ID'] . ')"><i class="fa fa-trash pe-1 text-danger"></i> HAPUS</a>';
                $html .= '</li>';
                $html .= '</ul>';
                $html .= '</div>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td class="align-middle text-black text-center fs-2" colspan="8">Tidak ada data</td>';
            $html .= '</tr>';
        }

        echo json_encode(['html' => $html]);
    }

    public function savePelatihan()
    {
        $id_pegawai = $this->request->getPost('id_pegawai');
        $tema_pelatihan = $this->request->getPost('val-tema_pelatihan');
        $tgl_pelatihan = $this->request->getPost('val-tgl_pelatihan');
        $tempat_pelatihan = $this->request->getPost('val-tempat_pelatihan');

        $data = [
            'PEGAWAI_ID' => $id_pegawai,
            'TEMA_PELATIHAN' => $tema_pelatihan,
            'TANGGAL_PELATIHAN' => $tgl_pelatihan,
            'TEMPAT_PELATIHAN' => $tempat_pelatihan,
            'BUKTI' => '-'
        ];

        $this->db->table('riwayat_pelatihan')->insert($data);

        echo json_encode(['status' => 'success', 'msg' => 'Pelatihan berhasil disimpan']);
    }

    function get_data_pelatihan()
    {
        $id_pegawai = $this->request->getVar('id_pegawai');

        $riwayat_pelatihan = $this->db->table('riwayat_pelatihan')
            ->select('riwayat_pelatihan.*')
            ->where('PEGAWAI_ID', $id_pegawai)
            ->orderBy('riwayat_pelatihan.TANGGAL_PELATIHAN', 'DESC')
            ->get()->getResultArray();

        $html = '';
        if ($riwayat_pelatihan) {
            foreach ($riwayat_pelatihan as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . ($key + 1) . '</td>';
                $html .= '<td class="align-middle text-black fs-2">' . $value['TEMA_PELATIHAN'] . '</td>';
                $html .= '<td class="align-middle text-black fs-2">' . $value['TEMPAT_PELATIHAN'] . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . date('d/m/Y', strtotime($value['TANGGAL_PELATIHAN'])) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">';
                $html .= '<div class="dropdown">';
                $html .= '<button class="btn btn-sm " type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="true">';
                $html .= '<i class="fa fa-ellipsis-v text-black"></i>';
                $html .= '</button>';
                $html .= '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                $html .= '<li class="dropdown-item">';
                $html .= '<a class="text-black" href="javascript:void(0)" onclick="hapusPelatihan(' . $value['ID'] . ')"><i class="fa fa-trash pe-1 text-danger"></i> HAPUS</a>';
                $html .= '</li>';
                $html .= '</ul>';
                $html .= '</div>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td class="align-middle text-black text-center fs-2" colspan="5">Tidak ada data</td>';
            $html .= '</tr>';
        }

        echo json_encode(['html' => $html]);
    }

    public function savePendidikan()
    {
        $id_pegawai = $this->request->getPost('id_pegawai');
        $tempat_pendidikan = $this->request->getPost('val-tempat_pendidikan');
        $pendidikan = $this->request->getPost('val-pendidikan');
        $tahun_lulus = $this->request->getPost('val-tahun_lulus');

        $data = [
            'ID_PEGAWAI' => $id_pegawai,
            'TEMPAT_PENDIDIKAN' => $tempat_pendidikan,
            'TAHUN_LULUS' => $tahun_lulus,
            'PENDIDIKAN' => $pendidikan,
            'UPLOAD_BERKAS' => NULL
        ];

        $this->db->table('riwayat_pendidikan')->insert($data);

        echo json_encode(['status' => 'success', 'msg' => 'Pendidikan berhasil disimpan']);
    }

    function delete_pendidikan()
    {
        $id_pendidikan = $this->request->getVar('id_pendidikan');

        $this->db->table('riwayat_pendidikan')
            ->where('ID', $id_pendidikan)
            ->delete();

        echo json_encode(['status' => 'success', 'msg' => 'Pendidikan berhasil dihapus']);
    }

    function delete_pelatihan()
    {
        $id_pelatihan = $this->request->getVar('id_pelatihan');

        $this->db->table('riwayat_pelatihan')
            ->where('ID', $id_pelatihan)
            ->delete();

        echo json_encode(['status' => 'success', 'msg' => 'Pelatihan berhasil dihapus']);
    }

    function delete_kinerja()
    {
        $id_kinerja = $this->request->getVar('id_kinerja');

        $this->db->table('ekinerja')
            ->where('ID', $id_kinerja)
            ->delete();

        echo json_encode(['status' => 'success', 'msg' => 'Kinerja berhasil dihapus']);
    }

    function delete_jabatan()
    {
        $id_jabatan = $this->request->getVar('id_jabatan');

        $this->db->table('riwayat_jabatan')
            ->where('ID', $id_jabatan)
            ->delete();

        echo json_encode(['status' => 'success', 'msg' => 'Jabatan berhasil dihapus']);
    }

    function get_data_pendidikan()
    {
        $id_pegawai = $this->request->getVar('id_pegawai');

        $riwayat_pendidikan = $this->db->table('riwayat_pendidikan')
            ->select('riwayat_pendidikan.*')
            ->where('ID_PEGAWAI', $id_pegawai)
            ->orderBy('riwayat_pendidikan.TAHUN_LULUS', 'DESC')
            ->get()->getResultArray();

        $html = '';
        if ($riwayat_pendidikan) {
            foreach ($riwayat_pendidikan as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . ($key + 1) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . $value['PENDIDIKAN'] . '</td>';
                $html .= '<td class="align-middle text-black fs-2">' . $value['TEMPAT_PENDIDIKAN'] . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . $value['TAHUN_LULUS'] . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">';
                $html .= '<div class="dropdown">';
                $html .= '<button class="btn btn-sm " type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="true">';
                $html .= '<i class="fa fa-ellipsis-v text-black"></i>';
                $html .= '</button>';
                $html .= '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                $html .= '<li class="dropdown-item">';
                $html .= '<a class="text-black" href="javascript:void(0)" onclick="hapusPendidikan(' . $value['ID'] . ')"><i class="fa fa-trash pe-1 text-danger"></i> HAPUS</a>';
                $html .= '</li>';
                $html .= '</ul>';
                $html .= '</div>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td class="align-middle text-black text-center fs-2" colspan="5">Tidak ada data</td>';
            $html .= '</tr>';
        }

        echo json_encode(['html' => $html]);
    }

    function get_data_absensi()
    {
        $id_pegawai = $this->request->getVar('id_pegawai');
        $bulan_absensi = $this->request->getVar('bulan_absensi');

        $m_setting_absen = $this->db->table('m_setting_absen')
            ->select('batas_masuk, batas_pulang')
            ->get()->getRowArray();

        $absensi = $this->db->table('absensi_masuk')
            ->select('absensi_masuk.TANGGAL, absensi_masuk.JAM as JAM_MASUK, absensi_pulang.JAM as JAM_PULANG')
            ->select('absensi_masuk.KETERANGAN as KETERANGAN_MASUK, absensi_pulang.KETERANGAN as KETERANGAN_PULANG')
            ->join('absensi_pulang', 'absensi_pulang.PEGAWAI_ID=absensi_masuk.PEGAWAI_ID', 'left')
            ->where('DATE_FORMAT(absensi_masuk.TANGGAL, "%Y-%m")', $bulan_absensi)
            ->where('absensi_masuk.PEGAWAI_ID', $id_pegawai)
            ->orderBy('absensi_masuk.TANGGAL', 'DESC')
            ->groupBy('absensi_masuk.TANGGAL')
            ->limit(32)
            ->get()
            ->getResultArray();

        $html = '';
        $no = 1;
        if ($absensi) {
            foreach ($absensi as $key => $value) {
                $bgMasuk = ($value['JAM_MASUK'] <= $m_setting_absen['batas_masuk']) ? 'bg-success' : 'bg-danger';
                $bgPulang = ($value['JAM_PULANG'] >= $m_setting_absen['batas_pulang']) ? 'bg-success' : 'bg-warning';

                $html .= '<tr>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . $no++ . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . date('d/m/Y', strtotime($value['TANGGAL'])) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2" width="20%">' . date('H:i', strtotime($value['JAM_MASUK'])) . ' WITA</td>';
                $html .= '<td class="align-middle text-black text-center fs-2 ' . $bgMasuk . '" width="20%">' . ($value['KETERANGAN_MASUK'] != NULL ? $value['KETERANGAN_MASUK'] : '-') . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2" width="20%">' . date('H:i', strtotime($value['JAM_PULANG'])) . ' WITA</td>';
                $html .= '<td class="align-middle text-black text-center fs-2" width="20%">' . ($value['KETERANGAN_PULANG'] != NULL ? $value['KETERANGAN_PULANG'] : '-') . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td class="align-middle text-black text-center fs-2" colspan="5">Tidak ada data</td>';
            $html .= '</tr>';
        }

        echo json_encode(['html' => $html]);
    }

    function pdf_pendidikan($id_pegawai)
    {
        $pegawai = $this->db->table('pegawai')
            ->where('ID', $id_pegawai)
            ->get()->getRowArray()['NAMA'];

        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>RIWAYAT PENDIDIKAN</h3>";
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>" . $pegawai . "</b></h3>";

        $riwayat_pendidikan = $this->db->table('riwayat_pendidikan')
            ->select('riwayat_pendidikan.*')
            ->where('ID_PEGAWAI', $id_pegawai)
            ->orderBy('riwayat_pendidikan.TAHUN_LULUS', 'DESC')
            ->get()->getResultArray();

        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="8%">No</th>';
        $html .= '<th>Jenjang Pendidikan</th>';
        $html .= '<th>Tempat Pendidikan</th>';
        $html .= '<th>Tahun Kelulusan</th>';
        $html .= '</tr>';

        if ($riwayat_pendidikan) {
            foreach ($riwayat_pendidikan as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td class="text-center">' . $value['PENDIDIKAN'] . '</td>';
                $html .= '<td class="">' . $value['TEMPAT_PENDIDIKAN'] . '</td>';
                $html .= '<td class="text-center">' . $value['TAHUN_LULUS'] . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td class="text-center" colspan="4">Tidak ada data</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

        $html .= '</div>';

        $data = ['html' => $html];
        $page = view('pdf/potrait', $data);

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'default_font_size' => 10,
            'default_font' => 'arial',
            'tempDir' => __DIR__ . '/temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle("JABATAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }

    function pdf_pelatihan($id_pegawai)
    {
        $pegawai = $this->db->table('pegawai')
            ->where('ID', $id_pegawai)
            ->get()->getRowArray()['NAMA'];

        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>RIWAYAT PELATIHAN</h3>";
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>" . $pegawai . "</b></h3>";

        $riwayat_pelatihan = $this->db->table('riwayat_pelatihan')
            ->select('riwayat_pelatihan.*')
            ->where('PEGAWAI_ID', $id_pegawai)
            ->orderBy('riwayat_pelatihan.TANGGAL_PELATIHAN', 'DESC')
            ->get()->getResultArray();

        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="3%">No</th>';
        $html .= '<th>Tema Pelatihan</th>';
        $html .= '<th>Tempat Pelatihan</th>';
        $html .= '<th>Tanggal Pelatihan</th>';
        $html .= '</tr>';

        if ($riwayat_pelatihan) {
            foreach ($riwayat_pelatihan as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td class="">' . $value['TEMA_PELATIHAN'] . '</td>';
                $html .= '<td class="">' . $value['TEMPAT_PELATIHAN'] . '</td>';
                $html .= '<td class="text-center">' . date('d/m/Y', strtotime($value['TANGGAL_PELATIHAN'])) . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td class="text-center" colspan="4">Tidak ada data</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

        $html .= '</div>';

        $data = ['html' => $html];
        $page = view('pdf/potrait', $data);

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'default_font_size' => 10,
            'default_font' => 'arial',
            'tempDir' => __DIR__ . '/temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle("JABATAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
    function pdf_jabatan($id_pegawai)
    {
        $pegawai = $this->db->table('pegawai')
            ->where('ID', $id_pegawai)
            ->get()->getRowArray()['NAMA'];

        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>RIWAYAT JABATAN</h3>";
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>" . $pegawai . "</b></h3>";

        $riwayat_jabatan = $this->db->table('riwayat_jabatan')
            ->select('riwayat_jabatan.*')
            ->select('jabatan.JABATAN')
            ->where('ID_PEGAWAI', $id_pegawai)
            ->join('jabatan', 'riwayat_jabatan.ID_JABATAN =  jabatan.ID')
            ->orderBy('riwayat_jabatan.ID', 'DESC')
            ->get()->getResultArray();

        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="3%">No</th>';
        $html .= '<th>No. SK</th>';
        $html .= '<th>Nama Jabatan</th>';
        $html .= '<th>Periode</th>';
        $html .= '<th>S/D</th>';
        $html .= '<th>Status Pegawai</th>';
        $html .= '<th>Status Jabatan</th>';
        $html .= '</tr>';

        if ($riwayat_jabatan) {
            foreach ($riwayat_jabatan as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td class="text-center">' . $value['NO_SK'] . '</td>';
                $html .= '<td class="">' . $value['JABATAN'] . '</td>';
                $html .= '<td class="text-center">' . date('d/m/Y', strtotime($value['TGL_SK'])) . '</td>';
                $html .= '<td class="text-center">' . date('d/m/Y', strtotime($value['TGL_SELESAI'])) . '</td>';
                $html .= '<td class="text-center">' . ($value['STATUS_PEGAWAI'] == 'Tetap' ? '<span class="badge rounded bg-primary">' . $value['STATUS_PEGAWAI'] . '</span>' : '<span class="badge rounded bg-dark">' . $value['STATUS_PEGAWAI'] . '</span>') . '</td>';
                $html .= '<td class="text-center">' . ($value['STATUS'] == 'Aktif' ? '<span class="badge rounded bg-success">' . $value['STATUS'] . '</span>' : '<span class="badge rounded bg-danger">' . $value['STATUS'] . '</span>') . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td class="text-center" colspan="7">Tidak ada data</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

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
        $mpdf->SetTitle("JABATAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }

    function pdf_kinerja($id_pegawai, $tglAwal, $tglAkhir)
    {
        $pegawai = $this->db->table('pegawai')
            ->where('ID', $id_pegawai)
            ->get()->getRowArray()['NAMA'];

        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>RIWAYAT KINERJA</h3>";
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>" . $pegawai . "</b></h3>";
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>PERIODE : " . date('d/m/Y', strtotime($tglAwal)) . " - " . date('d/m/Y', strtotime($tglAkhir)) . "</h3>";

        $eKinerja = $this->db->table('ekinerja');
        $eKinerja->select('pegawai.NAMA');
        $eKinerja->select('ekinerja.*');
        $eKinerja->select('tugas_pokok.TUGAS_POKOK');
        $eKinerja->where('PEGAWAI_ID', $id_pegawai);
        $eKinerja->where('TANGGAL_KINERJA >=', $tglAwal);
        $eKinerja->where('TANGGAL_KINERJA <=', $tglAkhir);
        $eKinerja->join('pegawai', 'pegawai.ID = ekinerja.PEGAWAI_ID', 'left');
        $eKinerja->join('tugas_pokok', 'tugas_pokok.ID = ekinerja.TUGAS_POKOK_ID');
        $eKinerja->orderBy('ekinerja.TANGGAL_KINERJA', 'ASC');
        $eKinerja = $eKinerja->get()->getResultArray();

        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="3%">No</th>';
        $html .= '<th>Tugas Pokok</th>';
        $html .= '<th>Tanggal Kinerja</th>';
        $html .= '<th>Uraian Kegiatan</th>';
        $html .= '</tr>';

        if ($eKinerja) {
            foreach ($eKinerja as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . ($key + 1) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . date('d/m/Y', strtotime($value['TANGGAL_KINERJA'])) . '</td>';
                $html .= '<td class="align-middle text-black text-center fs-2">' . $value['TUGAS_POKOK'] . '</td>';
                $html .= '<td class="align-middle text-black fs-2">' . $value['KETERANGAN_LAINNYA'] . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td class="text-center" colspan="4">Tidak ada data</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

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
        $mpdf->SetTitle("KINERJA");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
