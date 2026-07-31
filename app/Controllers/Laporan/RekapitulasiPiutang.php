<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;


class RekapitulasiPiutang extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Rekapitulasi Piutang';
    }
    public function index()
    {
        $data['title'] = $this->title;
        return view('/laporan/rekapitulasi_piutang/main', $data);
    }

    public function getPiutang()
    {
        $jenisLaporan = $this->request->getVar('jenisLaporan');
        $unit = $this->request->getVar('unit');
        $tahunAwal = $this->request->getVar('tahunAwal');
        $tahunAkhir = $this->request->getVar('tahunAkhir');

        if ($jenisLaporan == 'all') {
            $t_kecamatan = $this->db->table('t_kecamatan')->get()->getResultArray();

            $allYears = range($tahunAwal, $tahunAkhir);
            $dataArray = [];

            foreach ($t_kecamatan as $value) {
                $kecamatan = $value['kecamatan'];
                $dataArray[$kecamatan] = [];
                foreach ($allYears as $year) {
                    $dataArray[$kecamatan][$year] = [
                        'tahun' => $year,
                        'jumlah_bayar' => 0,
                        'jumlah_piutang' => 0
                    ];
                }
            }

            function getTagihanData($db, $status, $tahunAwal, $tahunAkhir)
            {
                $selectField = ($status == 0)
                    ? 'IFNULL(SUM(t_tagihan.jlh_tagihan + t_tagihan.denda), 0) as total'
                    : 'IFNULL(SUM(t_tagihan.bayar), 0) as total';

                $query = $db->table('t_tagihan')
                    ->select("
                        t_kecamatan.kecamatan,
                        YEAR(t_tagihan.bulan_tagihan) as tahun,
                        $selectField
                    ")
                    ->join('t_kecamatan', 't_kecamatan.id_kec = t_tagihan.id_kec');

                if ($status == 0) {
                    $query->where('t_tagihan.bayar', 0);
                } else {
                    $query->where('t_tagihan.status', $status);
                }

                if ($tahunAwal == $tahunAkhir) {
                    $query->where('YEAR(t_tagihan.bulan_tagihan)', $tahunAwal);
                } else {
                    $query->where('YEAR(t_tagihan.bulan_tagihan) >=', $tahunAwal)
                        ->where('YEAR(t_tagihan.bulan_tagihan) <=', $tahunAkhir);
                }

                return $query->groupBy('t_kecamatan.kecamatan, YEAR(t_tagihan.bulan_tagihan)')
                    ->orderBy('t_kecamatan.kecamatan, YEAR(t_tagihan.bulan_tagihan)', 'ASC')
                    ->get()->getResultArray();
            }

            $bayarData = getTagihanData($this->db, 1, $tahunAwal, $tahunAkhir);
            $piutangData = getTagihanData($this->db, 0, $tahunAwal, $tahunAkhir);

            foreach ($bayarData as $row) {
                $dataArray[$row['kecamatan']][$row['tahun']]['jumlah_bayar'] = $row['total'];
            }

            foreach ($piutangData as $row) {
                $dataArray[$row['kecamatan']][$row['tahun']]['jumlah_piutang'] = $row['total'];
            }

            foreach ($dataArray as $kecamatan => &$data) {
                $data = array_values($data);
            }
            unset($data);

            $result = $dataArray;
        } else {
            $m_jenis_pelanggan = $this->db->table('m_jenis_pelanggan')->get()->getResultArray();
            $t_kecamatan = $this->db->table('t_kecamatan');
            if ($unit != 'all') {
                $t_kecamatan->where('t_kecamatan.id_kec', $unit);
            }
            $t_kecamatan = $t_kecamatan->get()->getResultArray();

            $allYears = range($tahunAwal, $tahunAkhir);
            $dataArray = [];

            foreach ($t_kecamatan as $kec) {
                $id_kecamatan = $kec['id_kec'];
                $nama_kecamatan = $kec['kecamatan'];

                $dataArray[$id_kecamatan] = [
                    'nama_kecamatan' => $nama_kecamatan,
                    'data' => []
                ];

                foreach ($m_jenis_pelanggan as $pelanggan) {
                    $jenis_pelanggan = $pelanggan['jenis_pelanggan'];
                    $dataArray[$id_kecamatan]['data'][$jenis_pelanggan] = [];

                    foreach ($allYears as $year) {
                        $dataArray[$id_kecamatan]['data'][$jenis_pelanggan][$year] = [
                            'tahun' => $year,
                            'jumlah_bayar' => 0,
                            'jumlah_piutang' => 0
                        ];
                    }
                }
            }

            function getTagihanData($db, $status, $tahunAwal, $tahunAkhir)
            {
                $selectField = ($status == 0)
                    ? 'IFNULL(SUM(t_tagihan.jlh_tagihan + t_tagihan.denda), 0) as total'
                    : 'IFNULL(SUM(t_tagihan.bayar), 0) as total';

                $query = $db->table('t_tagihan')
                    ->select("
                    t_kecamatan.id_kec,
                    m_jenis_pelanggan.jenis_pelanggan,
                    YEAR(t_tagihan.bulan_tagihan) as tahun,
                    $selectField
                ")
                    ->join('m_jenis_pelanggan', 'm_jenis_pelanggan.id_jenis = t_tagihan.id_jenis')
                    ->join('m_pelanggan', 'm_pelanggan.id_pelanggan = t_tagihan.id_pelanggan')
                    ->join('t_kecamatan', 't_kecamatan.id_kec = m_pelanggan.id_kec');

                if ($status == 0) {
                    $query->where('t_tagihan.bayar', 0);
                } else {
                    $query->where('t_tagihan.status', $status);
                }

                $query->where('YEAR(t_tagihan.bulan_tagihan) >=', $tahunAwal)
                    ->where('YEAR(t_tagihan.bulan_tagihan) <=', $tahunAkhir);

                return $query->groupBy('t_kecamatan.id_kec, m_jenis_pelanggan.jenis_pelanggan, YEAR(t_tagihan.bulan_tagihan)')
                    ->orderBy('t_kecamatan.id_kec, m_jenis_pelanggan.jenis_pelanggan, YEAR(t_tagihan.bulan_tagihan)', 'ASC')
                    ->get()->getResultArray();
            }

            $bayarData = getTagihanData($this->db, 1, $tahunAwal, $tahunAkhir);
            $piutangData = getTagihanData($this->db, 0, $tahunAwal, $tahunAkhir);

            foreach ($bayarData as $row) {
                $id_kecamatan = $row['id_kec'];
                $jenis_pelanggan = $row['jenis_pelanggan'];
                $tahun = $row['tahun'];

                if (isset($dataArray[$id_kecamatan]['data'][$jenis_pelanggan][$tahun])) {
                    $dataArray[$id_kecamatan]['data'][$jenis_pelanggan][$tahun]['jumlah_bayar'] = (int) $row['total'];
                }
            }

            foreach ($piutangData as $row) {
                $id_kecamatan = $row['id_kec'];
                $jenis_pelanggan = $row['jenis_pelanggan'];
                $tahun = $row['tahun'];

                if (isset($dataArray[$id_kecamatan]['data'][$jenis_pelanggan][$tahun])) {
                    $dataArray[$id_kecamatan]['data'][$jenis_pelanggan][$tahun]['jumlah_piutang'] = (int) $row['total'];
                }
            }

            $resultArray = [];
            foreach ($dataArray as $id_kecamatan => $kecamatan) {
                $nama_kecamatan = $kecamatan['nama_kecamatan'];

                $tahunSet = [];
                foreach ($kecamatan['data'] as $jenisPelanggan => $tahunData) {
                    foreach ($tahunData as $tahun => $data) {
                        if (!isset($tahunSet[$tahun])) {
                            $tahunSet[$tahun] = [
                                'nama_kecamatan' => $nama_kecamatan,
                                'tahun' => $tahun,
                                'kategori' => []
                            ];
                        }
                        $tahunSet[$tahun]['kategori'][$jenisPelanggan] = [
                            'jumlah_bayar' => $data['jumlah_bayar'],
                            'jumlah_piutang' => $data['jumlah_piutang']
                        ];
                    }
                }
                $resultArray = array_merge($resultArray, array_values($tahunSet));
            }

            $result = $resultArray;
        }

        return $this->response->setJSON($result);
    }

    public function modal_export_pdf()
    {
        $data['jenisLaporan'] = $this->request->getVar('jenisLaporan');
        $data['tahunAwal'] = $this->request->getVar('tahunAwal');
        $data['tahunAkhir'] = $this->request->getVar('tahunAkhir');
        $data['Unit'] = $this->request->getVar('Unit');
        $data['unit'] = $this->request->getVar('unit');


        $msg = ['html' => view('laporan/rekapitulasi_piutang/modal/pdf', $data)];
        echo json_encode($msg);
    }

    public function export_pdf($jenisLaporan, $tahunAwal, $tahunAkhir, $unit)
    {
        $html = '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>LAPORAN DATA PELANGGAN DUPLIKAT</h3>";

        $periodeTahun = ($tahunAwal == $tahunAkhir) ? $tahunAwal : $tahunAwal . ' - ' . $tahunAkhir;
        if ($jenisLaporan === 'all') {
            $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>SEMUA UNIT</h3>";
            $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>PERIODE : " . $periodeTahun . "</h3>";

            $html .= '<br>';
            $html .= '<div class="table">';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<th width="20%">Kecamatan</th>';
            $html .= '<th>Tahun</th>';
            $html .= '<th>Piutang</th>';
            $html .= '<th>Telah Dibayar</th>';
            $html .= '<th>Sisa Piutang</th>';
            $html .= '</tr>';

            $t_kecamatan = $this->db->table('t_kecamatan')->get()->getResultArray();

            $allYears = range($tahunAwal, $tahunAkhir);
            $dataArray = [];

            foreach ($t_kecamatan as $value) {
                $kecamatan = $value['kecamatan'];
                $dataArray[$kecamatan] = [];
                foreach ($allYears as $year) {
                    $dataArray[$kecamatan][$year] = [
                        'tahun' => $year,
                        'jumlah_bayar' => 0,
                        'jumlah_piutang' => 0
                    ];
                }
            }

            function getTagihanData($db, $status, $tahunAwal, $tahunAkhir)
            {
                $selectField = ($status == 0)
                    ? 'IFNULL(SUM(t_tagihan.jlh_tagihan + t_tagihan.denda), 0) as total'
                    : 'IFNULL(SUM(t_tagihan.bayar), 0) as total';

                $query = $this->db->table('t_tagihan')
                    ->select("
                            t_kecamatan.kecamatan,
                            YEAR(t_tagihan.bulan_tagihan) as tahun,
                            $selectField
                        ")
                    ->join('t_kecamatan', 't_kecamatan.id_kec = t_tagihan.id_kec');

                if ($status == 0) {
                    $query->where('t_tagihan.bayar', 0);
                } else {
                    $query->where('t_tagihan.status', $status);
                }

                if ($tahunAwal == $tahunAkhir) {
                    $query->where('YEAR(t_tagihan.bulan_tagihan)', $tahunAwal);
                } else {
                    $query->where('YEAR(t_tagihan.bulan_tagihan) >=', $tahunAwal)
                        ->where('YEAR(t_tagihan.bulan_tagihan) <=', $tahunAkhir);
                }

                return $query->groupBy('t_kecamatan.kecamatan, YEAR(t_tagihan.bulan_tagihan)')
                    ->orderBy('t_kecamatan.kecamatan, YEAR(t_tagihan.bulan_tagihan)', 'ASC')
                    ->get()->getResultArray();
            }

            $bayarData = getTagihanData($this->db, 1, $tahunAwal, $tahunAkhir);
            $piutangData = getTagihanData($this->db, 0, $tahunAwal, $tahunAkhir);

            foreach ($bayarData as $row) {
                $dataArray[$row['kecamatan']][$row['tahun']]['jumlah_bayar'] = $row['total'];
            }

            foreach ($piutangData as $row) {
                $dataArray[$row['kecamatan']][$row['tahun']]['jumlah_piutang'] = $row['total'];
            }

            foreach ($dataArray as $kecamatan => &$data) {
                $data = array_values($data);
            }
            unset($data);

            $result = $dataArray;

            $totalSemuaPiutang = 0;
            $totalSemuaDiBayar = 0;
            $totalSemuaSisaPiutang = 0;
            foreach ($result as $kecamatan => $value) {
                $firstRow = true;

                $totalPiutang = 0;
                $totalDiBayar = 0;
                $totalSisaPiutang = 0;
                foreach ($value as $year => $data) {
                    $html .= '<tr>';

                    if ($firstRow) {
                        $html .= '<td rowspan="' . (count($value) + 1) . '">' . $kecamatan . '</td>';
                        $firstRow = false;
                    }

                    $html .= '<td class="text-center">' . $data['tahun'] . '</td>';
                    $html .= '<td class="text-right">Rp. ' . number_format($data['jumlah_piutang'] + $data['jumlah_bayar']) . '</td>';
                    $html .= '<td class="text-right">Rp. ' . number_format($data['jumlah_bayar']) . '</td>';
                    $html .= '<td class="text-right">Rp. ' . number_format($data['jumlah_piutang']) . '</td>';
                    $html .= '</tr>';

                    $totalPiutang += $data['jumlah_piutang'] + $data['jumlah_bayar'];
                    $totalDiBayar += $data['jumlah_bayar'];
                    $totalSisaPiutang += $data['jumlah_piutang'];
                }

                $html .= '<tr class="text-center" style="background-color:rgb(255, 181, 181)">';
                $html .= '<td class="text-center"><b>Total</b></td>';
                $html .= '<td class="text-right">Rp. ' . number_format($totalPiutang) . '</td>';
                $html .= '<td class="text-right">Rp. ' . number_format($totalDiBayar) . '</td>';
                $html .= '<td class="text-right">Rp. ' . number_format($totalSisaPiutang) . '</td>';
                $html .= '</tr>';

                $totalSemuaPiutang += $totalPiutang;
                $totalSemuaDiBayar += $totalDiBayar;
                $totalSemuaSisaPiutang += $totalSisaPiutang;
            }

            $html .= '<tr class="text-center">';
            $html .= '<td class="text-center" colspan="2"><b>Total Keseluruhan</b></td>';
            $html .= '<td class="text-right">Rp. ' . number_format($totalSemuaPiutang) . '</td>';
            $html .= '<td class="text-right">Rp. ' . number_format($totalSemuaDiBayar) . '</td>';
            $html .= '<td class="text-right">Rp. ' . number_format($totalSemuaSisaPiutang) . '</td>';
            $html .= '</tr>';
        } else {
            $kecamatan = ($unit != 'all') ? $this->db->table('t_kecamatan')->select('kecamatan')->where('id_kec', $unit)->get()->getRow()->kecamatan : 'SEMUA UNIT';
            $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>UNIT : " . strtoupper($kecamatan) . "</h3>";
            $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>PERIODE : " . $periodeTahun . "</h3>";

            $html .= '<style>';
            $html .= '.table, .table th, .table td { font-size: 9px !important; }';
            $html .= '</style>';


            $m_jenis_pelanggan = $this->db->table('m_jenis_pelanggan')->get()->getResultArray();

            $t_kecamatan = $this->db->table('t_kecamatan');
            if ($unit != 'all') {
                $t_kecamatan->where('t_kecamatan.id_kec', $unit);
            }
            $t_kecamatan = $t_kecamatan->get()->getResultArray();

            $allYears = range($tahunAwal, $tahunAkhir);
            $dataArray = [];

            foreach ($t_kecamatan as $kec) {
                $id_kecamatan = $kec['id_kec'];
                $nama_kecamatan = $kec['kecamatan'];

                $dataArray[$id_kecamatan] = [
                    'nama_kecamatan' => $nama_kecamatan,
                    'data' => []
                ];

                foreach ($m_jenis_pelanggan as $pelanggan) {
                    $jenis_pelanggan = $pelanggan['jenis_pelanggan'];
                    $dataArray[$id_kecamatan]['data'][$jenis_pelanggan] = [];

                    foreach ($allYears as $year) {
                        $dataArray[$id_kecamatan]['data'][$jenis_pelanggan][$year] = [
                            'tahun' => $year,
                            'jumlah_bayar' => 0,
                            'jumlah_piutang' => 0
                        ];
                    }
                }
            }

            function getTagihanData($db, $status, $tahunAwal, $tahunAkhir)
            {
                $selectField = ($status == 0)
                    ? 'IFNULL(SUM(t_tagihan.jlh_tagihan + t_tagihan.denda), 0) as total'
                    : 'IFNULL(SUM(t_tagihan.bayar), 0) as total';

                $query = $db->table('t_tagihan')
                    ->select("
                    t_kecamatan.id_kec,
                    m_jenis_pelanggan.jenis_pelanggan,
                    YEAR(t_tagihan.bulan_tagihan) as tahun,
                    $selectField
                ")
                    ->join('m_jenis_pelanggan', 'm_jenis_pelanggan.id_jenis = t_tagihan.id_jenis')
                    ->join('m_pelanggan', 'm_pelanggan.id_pelanggan = t_tagihan.id_pelanggan')
                    ->join('t_kecamatan', 't_kecamatan.id_kec = m_pelanggan.id_kec');

                if ($status == 0) {
                    $query->where('t_tagihan.bayar', 0);
                } else {
                    $query->where('t_tagihan.status', $status);
                }

                $query->where('YEAR(t_tagihan.bulan_tagihan) >=', $tahunAwal)
                    ->where('YEAR(t_tagihan.bulan_tagihan) <=', $tahunAkhir);

                return $query->groupBy('t_kecamatan.id_kec, m_jenis_pelanggan.jenis_pelanggan, YEAR(t_tagihan.bulan_tagihan)')
                    ->orderBy('t_kecamatan.id_kec, m_jenis_pelanggan.jenis_pelanggan, YEAR(t_tagihan.bulan_tagihan)', 'ASC')
                    ->get()->getResultArray();
            }

            $bayarData = getTagihanData($this->db, 1, $tahunAwal, $tahunAkhir);
            $piutangData = getTagihanData($this->db, 0, $tahunAwal, $tahunAkhir);

            foreach ($bayarData as $row) {
                $id_kecamatan = $row['id_kec'];
                $jenis_pelanggan = $row['jenis_pelanggan'];
                $tahun = $row['tahun'];

                if (isset($dataArray[$id_kecamatan]['data'][$jenis_pelanggan][$tahun])) {
                    $dataArray[$id_kecamatan]['data'][$jenis_pelanggan][$tahun]['jumlah_bayar'] = (int) $row['total'];
                }
            }

            foreach ($piutangData as $row) {
                $id_kecamatan = $row['id_kec'];
                $jenis_pelanggan = $row['jenis_pelanggan'];
                $tahun = $row['tahun'];

                if (isset($dataArray[$id_kecamatan]['data'][$jenis_pelanggan][$tahun])) {
                    $dataArray[$id_kecamatan]['data'][$jenis_pelanggan][$tahun]['jumlah_piutang'] = (int) $row['total'];
                }
            }

            $resultArray = [];
            foreach ($dataArray as $id_kecamatan => $kecamatan) {
                $nama_kecamatan = $kecamatan['nama_kecamatan'];

                $tahunSet = [];
                foreach ($kecamatan['data'] as $jenisPelanggan => $tahunData) {
                    foreach ($tahunData as $tahun => $data) {
                        if (!isset($tahunSet[$tahun])) {
                            $tahunSet[$tahun] = [
                                'nama_kecamatan' => $nama_kecamatan,
                                'tahun' => $tahun,
                                'kategori' => []
                            ];
                        }
                        $tahunSet[$tahun]['kategori'][$jenisPelanggan] = [
                            'jumlah_bayar' => $data['jumlah_bayar'],
                            'jumlah_piutang' => $data['jumlah_piutang']
                        ];
                    }
                }
                $resultArray = array_merge($resultArray, array_values($tahunSet));
            }

            $result = $resultArray;

            $html .= '<br>';
            $html .= '<div class="table">';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<th rowspan="2">Kecamatan</th>';
            $html .= '<th rowspan="2">Tahun</th>';

            if (!empty($result)) {
                $kategoriKeys = array_keys($result[0]['kategori']);
                $colspanKategori = count($kategoriKeys);

                $html .= '<th colspan="' . $colspanKategori . '">Jenis Pelanggan</th>';
                $html .= '<th rowspan="2">Jumlah</th>';
                $html .= '</tr><tr class="text-center">';

                foreach ($kategoriKeys as $kategori) {
                    $html .= '<th>' . $kategori . '</th>';
                }
            }
            $html .= '</tr></thead><tbody>';


            $tempNamaKecamatan = '';
            $totalPiutangKeseluruhan = 0;
            $totalKeseluruhanPerkategori = [];
            $totalKeseluruhanPerKecamatanPerkategori = [];

            foreach ($result as $index => $value) {
                $namaKecamatan = $value['nama_kecamatan'];
                $tahun = $value['tahun'];
                $totalPiutang = 0;

                $rowspanTahun = count(array_filter($result, fn($item) => $item['nama_kecamatan'] === $namaKecamatan));

                if ($tempNamaKecamatan !== $namaKecamatan) {
                    $html .= '<tr><td class="text-black" rowspan="' . ($rowspanTahun + 1) . '">' . $namaKecamatan . '</td>';
                    $tempNamaKecamatan = $namaKecamatan;
                } else {
                    $html .= '<tr>';
                }

                $html .= '<td class="text-black text-center">' . $tahun . '</td>';

                foreach ($kategoriKeys as $kategori) {
                    $jumlahPiutang = $value['kategori'][$kategori]['jumlah_piutang'] ?? 0;
                    $html .= '<td class="text-center fst-italic text-black">Rp. ' . number_format($jumlahPiutang, 0, ',', '.') . '</td>';

                    $totalKeseluruhanPerKecamatanPerkategori[$namaKecamatan][$kategori] = ($totalKeseluruhanPerKecamatanPerkategori[$namaKecamatan][$kategori] ?? 0) + $jumlahPiutang;
                    $totalKeseluruhanPerkategori[$kategori] = ($totalKeseluruhanPerkategori[$kategori] ?? 0) + $jumlahPiutang;
                    $totalPiutang += $jumlahPiutang;
                }

                $html .= '<td class="text-center fst-italic fw-bolder text-black">Rp. ' . number_format($totalPiutang, 0, ',', '.') . '</td></tr>';
                $totalPiutangKeseluruhan += $totalPiutang;

                if (!isset($result[$index + 1]) || $result[$index + 1]['nama_kecamatan'] !== $namaKecamatan) {
                    $html .= '<tr style="background: rgb(255, 181, 181)"><td class="text-center text-black fw-bolder">Total</td>';
                    $totalPiutangComboTahun = 0;
                    foreach ($kategoriKeys as $kategori) {
                        $totalPerKategori = $totalKeseluruhanPerKecamatanPerkategori[$namaKecamatan][$kategori] ?? 0;
                        $html .= '<td class="text-center text-black fw-bolder">Rp. ' . number_format($totalPerKategori, 0, ',', '.') . '</td>';
                        $totalPiutangComboTahun += $totalPerKategori;
                    }
                    $html .= '<td class="text-center text-black fw-bolder">Rp. ' . number_format($totalPiutangComboTahun, 0, ',', '.') . '</td>';
                    $html .= '</tr>';
                }
            }

            $html .= '<tr>';
            $html .= '<td class="text-center text-black fw-bolder" colspan="2">Total Keseluruhan</td>';
            foreach ($kategoriKeys as $kategori) {
                $totalKategori = $totalKeseluruhanPerkategori[$kategori] ?? 0;
                $html .= '<td class="text-center text-black fw-bolder">Rp. ' . number_format($totalKategori, 0, ',', '.') . '</td>';
            }
            $html .= '<td class="text-center text-black fw-bolder">Rp. ' . number_format($totalPiutangKeseluruhan, 0, ',', '.') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table></div></br>';

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
        $mpdf->SetTitle("LAPORAN PELANGGAN DUPLIKAT");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
