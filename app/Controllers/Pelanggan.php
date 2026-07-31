<?php

namespace App\Controllers;

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
        return view('/pelanggan/main', $data);
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

        $builder->where('m_pelanggan.status_aktif', '1');

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
                return '<td class="text-center align-middle"><button class="btn btn-sm btn-outline-dark fw-bolder" onclick="details(' . $data->id_pelanggan . ')">Detail</button></td>
                            ';
            })
            ->toJson(true);  // Mengembalikan data dalam format JSON    
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

    public function details()
    {
        $id_pelanggan = $this->request->getVar('id');

        $pelanggan = $this->db->table('m_pelanggan')
            ->select('m_pelanggan.no_sambungan, m_pelanggan.nama_pelanggan')
            ->select('t_kecamatan.kecamatan')
            ->where('m_pelanggan.id_pelanggan', $id_pelanggan)
            ->join('t_kecamatan', 'm_pelanggan.id_kec = t_kecamatan.id_kec', 'left')
            ->get()->getRowArray();

        $data = [
            'id_pelanggan' => $id_pelanggan,
            'no_sambungan' => $pelanggan['no_sambungan'],
            'nama_pelanggan' => $pelanggan['nama_pelanggan'],
        ];

        $html =  view('/pelanggan/modal/details', $data);
        echo json_encode(['html' => $html]);
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

    public function getJenisPelanggan()
    {
        $id_pelanggan = $this->request->getVar('pelanggan');
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

    public function get_details()
    {
        $data['id_pelanggan'] = $this->request->getVar('id_pelanggan');
        $page =  $this->request->getVar('page');
        $html = view('pelanggan/page/' . $page, $data);
        echo json_encode(['html' => $html]);
    }

    public function get_data_tagihan()
    {
        $id_pelanggan = $this->request->getVar('id_pelanggan');
        $tahun = $this->request->getVar('tahun');
        $bulan = $this->request->getVar('bulan');
        $status = $this->request->getVar('status');

        $t_tagihan = $this->db->table('t_tagihan');
        $t_tagihan->select('t_tagihan.id_pemakaian, t_tagihan.denda, t_tagihan.no_rek, t_tagihan.bulan_tagihan, t_tagihan.jlh_tagihan, t_tagihan.status');
        $t_tagihan->select('m_pelanggan.no_sambungan, m_pelanggan.nama_pelanggan');
        $t_tagihan->select('t_kecamatan.kecamatan, t_kelurahan.kelurahan');
        $t_tagihan->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan');
        $t_tagihan->join('t_kecamatan', 'm_pelanggan.id_kec = t_kecamatan.id_kec');
        $t_tagihan->join('t_kelurahan', 'm_pelanggan.id_kelurahan = t_kelurahan.id_kelurahan');
        $t_tagihan->where('t_tagihan.id_pelanggan', $id_pelanggan);

        if ($tahun != 'ALL') {
            $t_tagihan->where('DATE_FORMAT(t_tagihan.bulan_tagihan, "%Y")', $tahun);
        }

        if ($bulan != 'ALL') {
            $t_tagihan->where('DATE_FORMAT(t_tagihan.bulan_tagihan, "%m")', $bulan);
        }

        if ($status != 'ALL') {
            $t_tagihan->where('t_tagihan.status', $status);
        }

        $t_tagihan->orderBy('t_tagihan.bulan_tagihan', 'DESC');
        $result = $t_tagihan->get()->getResultArray();


        $html = '';
        if ($result) {
            foreach ($result as $key => $value) {
                $btn = ($value['status'] == '1' ? '<button class="btn btn-sm btn-primary fs-1">Kwitansi</button>' : '<button onclick="bayar(' . $value['id_pemakaian'] . ', ' . $value['jlh_tagihan'] . ',' . $value['denda'] . ')"  class="btn btn-sm btn-warning fs-1">Bayar</button>');
                $btn_delete = '<button onclick="delete_tagihan(' . $value['id_pemakaian'] . ')" class="btn btn-sm btn-danger fs-1">Hapus</button>';

                $html .= '<tr>';
                $html .= '<td class="text-black text-center align-middle">' . ($key + 1) . '</td>';
                $html .= '<td class="text-black text-uppercase align-middle"><b>' . $value['no_sambungan'] . ' - ' . $value['nama_pelanggan'] . '</b><br>' . $value['kecamatan'] . ' - ' . $value['kelurahan'] . '</td>';
                $html .= '<td class="text-black text-center align-middle">' . $value['no_rek'] . '</td>';
                $html .= '<td class="text-black text-center align-middle">' . date('F', strtotime($value['bulan_tagihan'])) . '</td>';
                $html .= '<td class="text-black text-center align-middle">' . date('Y', strtotime($value['bulan_tagihan'])) . '</td>';
                $html .= '<td class="text-center align">' . ($value['status'] == '1' ? '<span class="badge fs-1 rounded bg-success">Lunas</span>' : '<span class="badge fs-1 rounded bg-danger">Belum Lunas</span>') . '</td>';
                $html .= '<td class="text-black text-center align-middle">Rp. ' . number_format($value['jlh_tagihan']) . '</td>';
                $html .= '<td class="text-center align-middle ">' . $btn . ' ' .  $btn_delete . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td colspan="100%" class="text-black text-center align-middle">Tidak Ada Data</td>';
            $html .= '</tr>';
        }

        echo json_encode(['html' => $html]);
    }

    public function savePiutang()
    {

        $id_pelanggan = $this->request->getVar('id_pelanggan');
        $januari = $this->request->getVar('januari');
        $februari = $this->request->getVar('februari');
        $maret = $this->request->getVar('maret');
        $april = $this->request->getVar('april');
        $mei = $this->request->getVar('mei');
        $juni = $this->request->getVar('juni');
        $juli = $this->request->getVar('juli');
        $agustus = $this->request->getVar('agustus');
        $september = $this->request->getVar('september');
        $oktober = $this->request->getVar('oktober');
        $november = $this->request->getVar('november');
        $desember = $this->request->getVar('desember');
    }

    public function piutang($bulan, $id_pelanggan)
    {
        $t_tagihan = $this->db->table('t_tagihan');
        $data_tagihan = $t_tagihan->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan')->where('t_tagihan.id_pelanggan', $this->request->getVar('id_pelanggan'))->get()->getRowArray();
        $query = [
            'no_rek' => rand(1111, 9999),
            'id_pelanggan' => $this->request->getVar('id_pelanggan'),
            'id_kab' => $data_tagihan['id_kab'],
            'id_kec' => $data_tagihan['id_kec'],
            'id_kelurahan' => $data_tagihan['id_kelurahan'],
            'id_jasa' => $data_tagihan['id_jasa'],
            'id_jenis' => $data_tagihan['id_jenis'],
            'tarif_dibawah_10' => $data_tagihan['tarif_dibawah_10'],
            'tarif_diatas_10' => $data_tagihan['tarif_diatas_10'],
            'pemakaian_air_lalu' => $data_tagihan['pemakaian_air_lalu'],
            'tag_dibawah_10' => $data_tagihan['tag_dibawah_10'],
            'tag_diatas_10' => $data_tagihan['tag_diatas_10'],
            'by_admin' => $data_tagihan['by_admin'],
            'jlh_tagihan' => $data_tagihan['jlh_tagihan'],
            'denda' => $data_tagihan['denda'],
            'bayar' => $data_tagihan['bayar'],
            'status' => $data_tagihan['status'],
            'aktif' => $data_tagihan['aktif'],
            'id_user' => $_SESSION['id_session'],


        ];

        $insert = $t_tagihan->insert($query);
    }

    public function get_data_pelanggan()
    {
        $id_pelanggan = $this->request->getVar('id_pelanggan');

        $row = $this->db->table('m_pelanggan')
            ->select('t_tagihan.pemakaian_air_lalu')
            ->select('m_pelanggan.nama_pelanggan, m_pelanggan.no_sambungan')
            ->select('m_jenis_pelanggan.jenis_pelanggan')
            ->select('m_jasa.bea_admin, m_jasa.tarif_bawah_10, m_jasa.tarif_atas_10')
            ->select('t_kecamatan.kecamatan, t_kelurahan.kelurahan')
            ->where('m_pelanggan.id_pelanggan', $id_pelanggan)
            ->join('m_jasa', 'm_jasa.id_jasa = m_pelanggan.id_jasa')
            ->join('m_jenis_pelanggan', 'm_jenis_pelanggan.id_jenis = m_jenis_pelanggan.id_jenis')
            ->join('t_kecamatan', 'm_pelanggan.id_kec = t_kecamatan.id_kec')
            ->join('t_kelurahan', 'm_pelanggan.id_kelurahan = t_kelurahan.id_kelurahan')
            ->join('t_tagihan', 'm_pelanggan.id_pelanggan = t_tagihan.id_pelanggan')
            ->orderBy('t_tagihan.tgl_bayar', 'DESC')
            ->get()->getRowArray();

        echo json_encode($row);
    }

    public function get_latters($page, $id_pelanggan)
    {
        // $page = $this->request->getVar('page');
        // $id_pelanggan = $this->request->getVar('id_pelanggan');
        // dd($id_pelanggan);

        $html = '<iframe src="' . base_url('Pelanggan/latters/' . $page . '/' . $id_pelanggan) . '" width="100%" height="100%" frameborder="0"></iframe>';

        echo json_encode(['html' => $html]);
    }

    function getKwitansi($id_pelanggan)
    {
        $pelanggan = $this->db->table('m_pelanggan')
            ->select('m_pelanggan.*, t_kecamatan.kecamatan, t_kelurahan.kelurahan')
            ->select('m_jasa.kelompok_jasa')
            ->select('m_jenis_pelanggan.kd_jenis')
            ->select('users.username')
            ->select('')
            ->join('m_jasa', 'm_jasa.id_jasa = m_pelanggan.id_jasa')
            ->join('users', 'users.id_session = m_pelanggan.petugas_meter')
            ->join('m_jenis_pelanggan', 'm_jenis_pelanggan.id_jenis = m_pelanggan.id_jenis')
            ->join('t_kecamatan', 'm_pelanggan.id_kec = t_kecamatan.id_kec')
            ->join('t_kelurahan', 'm_pelanggan.id_kelurahan = t_kelurahan.id_kelurahan')
            ->where('id_pelanggan', $id_pelanggan)
            ->get()->getRowArray();
        $html = '';
        $html = "<p><b>Detail Pelanggan Baru</b></p>
                <table style='width: 100%;'>
                    <tr>
                        <td>No. Pelanggan Baru</td>
                        <td>: {$pelanggan['no_sambungan']}</td>
                    </tr>
                    <tr>
                        <td>Nama Pelanggan</td>
                        <td>: {$pelanggan['nama_pelanggan']}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: Gorontalo Utara Kecamatan " .  $pelanggan['kecamatan']  . "Desa/Kel. " . $pelanggan['kelurahan'] . "</td>
                    </tr>
                    <tr>
                        <td>Jenis Pelanggan</td>
                        <td>: " . $pelanggan['kelompok_jasa'] . "-"  .  $pelanggan['kd_jenis'] . "</td>
                    </tr>
                    <tr>
                        <td>Tgl. Pendaftaran</td>
                        <td>: " . $pelanggan['tgl_terdaftar']  . "</td>
                    </tr>
                </table>
                <p><b>Detail Pemasangan</b><br /> Data dibawah ini sewaktu-waktu dapat berubah tanpa pemberitahuan.!</p>
                <table style='width: 100%;'>
                    <tr>
                        <td>Petugas Pemasangan</td>
                        <td>: " . $pelanggan['username'] . "</td>
                    </tr>
                    <tr>
                        <td>Merek Meter</td>
                        <td>: <?= r['merek_meter']; ?></td>
                    </tr>
                    <tr>
                        <td>Lama pemasangan</td>
                        <td>: <?= r['lama_pemasangan']; ?></td>
                    </tr>
                    <tr>
                        <td>Ukuran Meter</td>
                        <td>: <?= r['ukuran_meter']; ?> M<sup>3</sup></td>
                    </tr>
                    <tr>
                        <td>Tanggal Rencana Pasang</td>
                        <td>: <?= tgl_indo(r['tgl_pasang']); ?></td>
                    </tr>
                </table>
                <p><b>Detail Pembayaran</b>
                <table style='width: 100%;'>
                    <tr>
                        <td>Total Pembayaran</td>
                        <td>: Rp. <?= r['biaya_pemasangan']; ?></td>
                    </tr>
                    <tr>
                        <td>Terbilang</td>
                        <td>: <?= terbilang(r['biaya_pemasangan']); ?> RUPIAH</td>
                    </tr>
                    <tr>
                        <td>Tanggal Bayar</td>
                        <td>: <?= tgl_indo(r['tanggal_bayar']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>: <?php
                                if (r['lunas_bea_pendaftaran'] == 'Y') {
                                    echo '<span class='label bg-green'>Lunas</span>';
                                } else {
                                    echo '<span class='label bg-red'>Belum Dibayar</span>';
                                } ?>
                        </td>
                    </tr>
                </table>
                <hr />
                <center>
                    Paraf Penerima,<br /><br /><br /><br />
                    <br>
                    <b>" . $_SESSION['nama_lengkap'] . "</b>
                </center>
            </div>
        </div>";

        return $html;
    }

    public function latters($latters_page, $id_pelanggan)
    {
        // dd($latters_page, $id_pelanggan);
        switch ($latters_page):
            case 'kwitansi':
                $title_page = 'KWITANSI';
                break;
            case 'surat_permohonan':
                $title_page = 'SURAT PERMOHONAN';
                break;
            case 'spp':
                $title_page = 'SURAT PERSETUJUAN PENYAMBUNGAN (SPP)';
                break;
            case 'dpb':
                $title_page = 'DAFTAR PERMINTAAN BARANG (DPB)';
                break;
            case 'surat_pernyataan_pelanggan':
                $title_page = 'SURAT PERNYATAAN PELANGGAN';
                break;
        endswitch;

        $html = $this->getKwitansi($id_pelanggan);
        echo $html;
        // die;

        $data = ['html' => $html];
        dd($data);
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
        $mpdf->SetTitle($title_page);
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }

    public function bayar()
    {
        $getdata = $this->request->getPost();
        $db = $this->db->table('t_tagihan')->select('status');
        $bayar = $getdata['denda'] + $getdata['jlh_tagihan'];
        $data = $db->where('id_pemakaian', $getdata['id_pemakaian'])->update(['status' => 1, 'bayar' => $bayar, 'tgl_bayar' => date('Y-m-d h:i:s'), 'id_user' => $_SESSION['id_session']]);
        if ($data) {
            return $this->response->setJSON([
                'success' => 'success'
            ]);
        }
    }

    public function save_rekening_baru()
    {
        $data = $this->request->getVar();

        $db = $this->db->table('t_tagihan');
        $tgl = date('Y-m', strtotime($data['tanggal']));
        $validasi = $db->where('id_pelanggan', $data['id_pelanggan'])->where('DATE_FORMAT(bulan_tagihan, "%Y-%m")', $tgl)->get()->getRowArray();
        if ($validasi) {

            return $this->response->setJSON(['message' => 'Rekening dibulan ini sudah ada'])->setStatusCode(400);
        } else {
            $dta = $db->where('id_pelanggan', $data['id_pelanggan'])->orderBy('bulan_tagihan', 'DESC')->get()->getRowArray();

            $query = [
                'no_rek' => rand(1111, 9999),
                'id_pelanggan' => $data['id_pelanggan'],
                'id_kab' => $dta['id_kab'],
                'id_kec' => $dta['id_kec'],
                'id_kelurahan' => $dta['id_kelurahan'],
                'id_jasa' => $dta['id_jasa'],
                'id_jenis' => $dta['id_jenis'],
                'tarif_dibawah_10' => $dta['tarif_dibawah_10'],
                'tarif_diatas_10' => $dta['tarif_diatas_10'],
                'bulan_tagihan' => $data['tanggal'],
                'pemakaian_air' => $data['pemakaian_air_lalu'],
                'tag_dibawah_10' => $dta['tag_dibawah_10'],
                'tag_diatas_10' => $dta['tag_diatas_10'],
                'by_admin' => $dta['by_admin'],
                'jlh_tagihan' => $dta['jlh_tagihan'],
                'denda' => $dta['denda'],
                'bayar' => 0,
                'tgl_bayar' => null,
                'status' => 0,
                'aktif' => $dta['aktif'],
                'id_user' => session('id_session'),
            ];
            $save = $db->insert($query);
            if ($save === true) {
                return $this->response->setJSON(['status' => 'success']);
            }
        }
    }

    public function deleteTagihan()
    {
        $query = $this->db->table('t_tagihan')->where('id_pemakaian', $this->request->getPost('id_pemakaian'))->delete();
        if ($query) {
            return $this->response->setJSON(['status' => 'success']);
        }
    }
}
