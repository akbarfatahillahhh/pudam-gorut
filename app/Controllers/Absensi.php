<?php

namespace App\Controllers;

use Hermawan\DataTables\DataTable;
use App\Models\ProfilOrganisasi;
use App\Models\MappingAsesor;
use App\Models\EP;
use App\Models\File;

class Absensi extends BaseController
{
    var $title;

    public function __construct()
    {

        $this->title = "Absensi";
    }

    public function unit()
    {
        $bulanNama = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $data = [
            'title' => $this->title,
            'bulanNama' => $bulanNama
        ];

        return view('absensi/main', $data);
    }

    public function newUnit()
    {
        if ($this->request->isAJAX()) {
            $html = ['html' => view('/absensi/modal/new')];
            echo json_encode($html);
        }
    }

    public function getDataPegawai()
    {
        if ($this->request->isAJAX()) {
            $unit = $this->request->getVar('unit');

            $users = $this->db->table('users');
            $users->select('users.nama_lengkap, users.id_pegawai');
            $users->select('pegawai.LATITUDE, pegawai.LONGITUDE');
            $users->join('pegawai', 'pegawai.ID = users.id_pegawai');
            $users->where('users.blokir', 'N');

            if ($unit != 'all') {
                list($latitude, $longtitude) = explode("-", $unit);

                $users->where('pegawai.LATITUDE', $latitude);
                $users->where('pegawai.LONGITUDE', $longtitude);
            }

            $result = $users->groupBy('users.id_pegawai')->get()->getResultArray();
            foreach ($result as $key => $value) {
                $result[$key]['aksi'] = '
                <div class="row mx-0 justify-content-center">
                <div class="col-3 form-check">
                <label for="id[' . $value['id_pegawai'] . ']" class="form-check-label text-success">Hadir:</label>
                <input type="radio" class="form-check-input pointer border-dark me-2 absen-datang" name="pegawai[' . $value['id_pegawai'] . ']" data-pegawai="' . $value['id_pegawai'] . '" data-value="TEPAT WAKTU" data-latitude="' .  $value['LATITUDE'] . '" data-longtitude="' . $value['LONGITUDE'] . '" value="TEPAT WAKTU">
                </div>
                <div class="col-3 form-check">
                <label for="id[' . $value['id_pegawai'] . ']" class="form-check-label text-warning">Terlambat:</label>
                <input type="radio" class="form-check-input pointer border-dark me-2 absen-datang" name="pegawai[' . $value['id_pegawai'] . ']" data-pegawai="' . $value['id_pegawai'] . '" data-value="TERLAMBAT" data-latitude="' .  $value['LATITUDE'] . '" data-longtitude="' . $value['LONGITUDE'] . '" value="TERLAMBAT">
                </div>
                <div class="col-3 form-check">
                <label for="id[' . $value['id_pegawai'] . ']" class="form-check-label text-danger">Tidak Hadir:</label>
                <input type="radio" class="form-check-input pointer border-dark me-2 absen-datang" name="pegawai[' . $value['id_pegawai'] . ']" data-pegawai="' . $value['id_pegawai'] . '" data-value="TIDAK HADIR" data-latitude="' .  $value['LATITUDE'] . '" data-longtitude="' . $value['LONGITUDE'] . '" value="TIDAK HADIR">
                </div>
                </div>
                ';
            }

            echo json_encode($result);
        }
    }

    public function simpanAbsensi()
    {
        if ($this->request->isAJAX()) {
            $pegawai = $this->request->getVar('pegawai');
            $unit = $this->request->getVar('unit');
            $tanggal = $this->request->getVar('tanggal');

            if ($pegawai) {
                foreach ($pegawai as $key => $value) {
                    $tablePegawai = $this->db->table('pegawai')->select('LATITUDE,LONGITUDE')->where('ID', $key)->get()->getResultArray();
                    foreach ($tablePegawai as $key2 => $value2) {
                        $data['LATITUDE'] = $value2['LATITUDE'];
                        $data['LONGITUDE'] = $value2['LONGITUDE'];
                        $data['TANGGAL'] = $tanggal;
                        $data['PEGAWAI_ID'] = $key;
                        if ($value == 'TIDAK HADIR') {
                            $data['KETERANGAN'] = 'TIDAK HADIR';
                            $data['JAM'] = "00:00:00";
                        } else {
                            $data['KETERANGAN'] = 'HADIR';
                            $data['JAM'] = $value;
                        }
                        $insert = $this->db->table('absensi_masuk')->insert($data);
                    }
                }

                if ($insert) {
                    echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
                } else {
                    echo json_encode(['status' => 'error', 'msg' => 'Data gagal disimpan']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Pilih Dulu Pegawai']);
            }
        }
    }
    public function getAbsensi()
    {
        if ($this->request->isAJAX()) {
            $bulan = $this->request->getVar('bulan');
            $tahun = $this->request->getVar('tahun');
            $unit = $this->request->getVar('unit');
            $jumlah_hari = date('t', strtotime(sprintf('%s-%02d-01', $tahun, $bulan)));

            $startDate = sprintf('%s-%02d-01', $tahun, $bulan);
            $endDate = date('Y-m-t', strtotime(sprintf('%s-%02d-01', $tahun, $bulan)));

            $thead = '';
            $thead .= '
                <tr>
                    <th class="text-center" rowspan="3" width="3%">No.</th>
                    <th class="text-center" rowspan="3" width="37%">Nama</th>
                    <th class="text-center" colspan="' . $jumlah_hari . '" width="10%">Hari / Tanggal</th>
                    <th class="text-center" colspan="3" rowspan="2" width="10%">Keterangan</th>
                </tr>';
            $thead .= '<tr>';
            if ($tahun) {
                for ($tanggal = 1; $tanggal <= $jumlah_hari; $tanggal++) {
                    $namaHari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][date('w', strtotime(sprintf('%s-%02d-%02d', $tahun, $bulan, $tanggal)))];
                    $tglMerah = ($namaHari == 'Min' || $namaHari == 'Sab') ? 'bg-danger bg-opacity-75' : '';
                    $thead .= "<th class='text-center {$tglMerah}'>" . ucfirst($namaHari) . "</th>";
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Tahun belum dipilih']);
                return false;
            }

            $thead .= '</tr>';
            $thead .= '<tr>';

            for ($i = 1; $i <= $jumlah_hari; $i++) {
                $tanggal = date('Y-m-d', strtotime(sprintf('%s-%02d-%02d', $tahun, $bulan, $i)));
                $tanggalMerah = (date('w', strtotime($tanggal)) == 0 || date('w', strtotime($tanggal)) == 6) ? 'bg-danger bg-opacity-75' : '';
                $thead .= "<th class='text-center {$tanggalMerah}'>{$i}</th>";
            }

            $thead .= '
                    <th class="text-center">Hadir</th> 
                    <th class="text-center">Terlmbat</th>
                    <th class="text-center">Tidak Hadir</th>
                </tr>';

            $dataPegawai = $this->db->table('users')
                ->select('users.nama_lengkap, users.id_pegawai')
                ->join('pegawai', 'pegawai.ID = users.id_pegawai')
                ->where('pegawai.STATUS', 'Aktif');

            if ($unit != 'all') {
                list($latitude, $longtitude) = explode("-", $unit);

                $dataPegawai->where('LATITUDE', $latitude);
                $dataPegawai->where('LONGITUDE', $longtitude);
            }

            $resultPegawai = $dataPegawai->get()->getResultArray();

            $batasMasuk = $this->db->table('m_setting_absen')->select('batas_masuk')->get()->getRow()->batas_masuk;

            $tbody = '';
            $no = 1;

            foreach ($resultPegawai as $key => $value) {
                $dataAbsensi = $this->db->table('absensi_masuk');
                $dataAbsensi->where('TANGGAL >=', $startDate);
                $dataAbsensi->where('TANGGAL <=', $endDate);
                $dataAbsensi->where('PEGAWAI_ID', $value['id_pegawai']);
                $results = $dataAbsensi->get()->getResultArray();

                $tbody .= '<tr>';
                $tbody .= '<td class="text-center fw-bolder" width="3%">' . $no++ . '</td>';
                $tbody .= '<td class="text-uppercase fw-bolder" width="37%">' . $value['nama_lengkap'] . '</td>';

                $totalHadir = 0;
                $totalTidakHadir = 0;
                $totalTerlambat = 0;

                for ($tanggal = 1; $tanggal <= $jumlah_hari; $tanggal++) {
                    $hadir = false;
                    $terlambat = false;

                    $isMinggu = false;
                    if (date('w', strtotime(sprintf('%s-%02d-%02d', $tahun, $bulan, $tanggal))) == 0 || date('w', strtotime(sprintf('%s-%02d-%02d', $tahun, $bulan, $tanggal))) == 6) {
                        $isMinggu = true;
                    }

                    if ($results) {
                        foreach ($results as $key2 => $value2) {
                            if ($tanggal == date('j', strtotime($value2['TANGGAL']))) {
                                if (trim($value2['JAM']) !== '00:00:00') {
                                    $hadir = true;
                                }
                                if (trim($value2['JAM']) > trim($batasMasuk)) {
                                    $terlambat = true;
                                }
                                break;
                            }
                        }

                        if ($terlambat) {
                            $totalTerlambat++;
                        }

                        if ($hadir) {
                            $totalHadir++;
                        } else {
                            $totalTidakHadir++;
                        }

                        $bgHadir = ($hadir ? ($terlambat ? 'bg-warning bg-opacity-75' : 'bg-success bg-opacity-75') : ($isMinggu ? 'bg-danger bg-opacity-75' : ''));
                        $valHadir = ($hadir ? ($isMinggu ? '<span class="text-black">H</span>' : ($terlambat ? '<span class="text-black">T</span>' : '<span class="text-black">H</span>')) : 'X');

                        $tbody .= '<td class="text-center fw-bolder ' . $bgHadir . '">' . $valHadir . '</td>';
                    } else {
                        $bgHadir = ($isMinggu ? 'bg-danger bg-opacity-75' : '');

                        $tbody .= '<td class="text-center fw-bolder ' . $bgHadir . '">--</td>';
                    }
                }

                $tbody .= '<td class="text-center text-success fw-bolder">' . $totalHadir . '</td>';
                $tbody .= '<td class="text-center text-warning fw-bolder">' . $totalTerlambat . '</td>';
                $tbody .= '<td class="text-center text-danger fw-bolder">' . $totalTidakHadir . '</td>';
                $tbody .= '</tr>';
            }

            echo json_encode(['status' => 'sukses', 'thead' => $thead, 'tbody' => $tbody]);
        }
    }


    public function modal_export_pdf()
    {
        $data['bulan'] = $this->request->getVar('bulan');
        $data['tahun'] = $this->request->getVar('tahun');
        $data['unit'] = $this->request->getVar('unit');

        $msg = ['html' => view('absensi/modal/pdf', $data)];
        echo json_encode($msg);
    }

    public function export_pdf($bulan, $tahun, $unit) {}
}
