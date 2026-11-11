<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class RekapDrdAir extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Rekap DRD Air";
    }

    public function laporan()
    {
        return view('/pdf/potrait');
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/laporan/rekap_drd_air/main', $data);
    }

    public function loadPage($page)
    {
        if ($page == 'perUnit') {
            $msg = ['page' =>  view('/laporan/rekap_drd_air/page/per_unit')];
        } else if ($page == 'perKel') {
            $msg = ['page' =>  view('/laporan/rekap_drd_air/page/per_kel')];
        }

        echo json_encode($msg);
    }

    public function datatable()
    {
        $jenisLaporan = $this->request->getVar('jenisLaporan');
        $dateStart = $this->request->getVar('dateStart');
        $dateEnd = $this->request->getVar('dateEnd');
        $statusLaporan = $this->request->getVar('statusLaporan');

        if ($jenisLaporan == 'perUnit') {
            $unit = $this->request->getVar('unit');
            return $this->getDataPerUnit($dateStart, $dateEnd, $statusLaporan, $unit);
        } else if ($jenisLaporan == 'perKel') {
            $kel = $this->request->getVar('kel');
            return $this->getDataPerKel($dateStart, $dateEnd, $statusLaporan, $kel);
        }
    }

    function getDataPerUnit($dateStart, $dateEnd, $statusLaporan, $unit)
    {
        $html = '';
        $m_jenis_pelanggan = $this->db->table('m_jenis_pelanggan')->get()->getResult();
        foreach ($m_jenis_pelanggan as $key => $jp) {
            $idJenis = $jp->id_jenis;

            $builder = $this->db->table('t_tagihan')
                ->select('
                    SUM(pemakaian_air) as pemakaian_air, 
                    COUNT(id_pemakaian) as rekening, 
                    SUM(tag_dibawah_10) as dibawah10, 
                    tarif_dibawah_10 as tagdibawah10, 
                    tarif_diatas_10 as tagdiatas10, 
                    SUM(tag_diatas_10) as diatas10, 
                    SUM(by_admin) as beadmin
                ')
                ->where('bulan_tagihan >=', $dateStart)
                ->where('bulan_tagihan <=', $dateEnd)
                ->where('id_jenis', $idJenis);

            if ($statusLaporan != 'all') {
                $builder->where('status', $statusLaporan);
            }

            if ($unit !== 'all') {
                $builder->join('t_kelurahan', 't_kelurahan.id_kelurahan = t_tagihan.id_kelurahan');
                $builder->where('t_kelurahan.id_kec', $unit);
            }

            $query = $builder->get()->getRowArray();

            $beadmin = $query['beadmin'] ?? 0;
            
            $biayaair = $query['dibawah10'] + $query['diatas10'];
            // $jumlah = $biayaair + $query['beadmin'];
            $jumlah = $biayaair + $beadmin;

            $tarifB = $query['tagdibawah10'];
            $kBawa = ($tarifB > 0) ? $query['dibawah10'] / $tarifB : $kBawa = 0;

            $tarifA = $query['tagdiatas10'];
            $kAtas = ($tarifA > 0) ? ($query['diatas10'] / $tarifA) : $kAtas = 0;

            $kubukasi = $kAtas + $kBawa;

            $totalPelanggan = $this->db->table('m_pelanggan')
                ->select('COUNT(DISTINCT id_pelanggan) as totalPelanggan')
                ->where('id_jenis', $jp->id_jenis);
            if ($unit !== 'all') {
                $totalPelanggan->where('id_kec', $unit);
            }
            $totalPelanggan = $totalPelanggan->get()->getRow();

            $html .= "<tr>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . ($key + 1) . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder py-1'>" . $jp->jenis_pelanggan . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . $totalPelanggan->totalPelanggan . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . $query['rekening'] . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . $kBawa . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . $kAtas . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . $kubukasi . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-end fst-italic py-1'>Rp. " . number_format($biayaair) . "</td>";
            // $html .= "<td class='text-black aling-middle fw-bolder text-end fst-italic py-1'>Rp. " . number_format($query['beadmin']) . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-end fst-italic py-1'>Rp. " . number_format($beadmin) . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-end fst-italic py-1'>Rp. " . number_format($jumlah) . "</td>";
            $html .= "</tr>";
        }

        echo json_encode(['html' => $html]);
    }

    function getDataPerKel($dateStart, $dateEnd, $statusLaporan, $kel)
    {
        $html = '';
        $m_jenis_pelanggan = $this->db->table('m_jenis_pelanggan')->get()->getResult();
        foreach ($m_jenis_pelanggan as $key => $jp) {
            $idJenis = $jp->id_jenis;

            $builder = $this->db->table('t_tagihan')
                ->select('
                    SUM(pemakaian_air) as pemakaian_air, 
                    COUNT(id_pemakaian) as rekening, 
                    SUM(tag_dibawah_10) as dibawah10, 
                    tarif_dibawah_10 as tagdibawah10, 
                    tarif_diatas_10 as tagdiatas10, 
                    SUM(tag_diatas_10) as diatas10, 
                    SUM(by_admin) as beadmin
                ')
                ->where('bulan_tagihan >=', $dateStart)
                ->where('bulan_tagihan <=', $dateEnd)
                ->where('id_jenis', $idJenis);

            if ($statusLaporan != 'all') {
                $builder->where('status', $statusLaporan);
            }

            if ($kel !== 'all') {
                $builder->join('t_kelurahan', 't_kelurahan.id_kelurahan = t_tagihan.id_kelurahan');
                $builder->where('t_kelurahan.id_kelurahan', $kel);
            }

            $query = $builder->get()->getRowArray();

            $beadmin = $query['beadmin'] ?? 0;

            $biayaair = $query['dibawah10'] + $query['diatas10'];
            // $jumlah = $biayaair + $query['beadmin'];
            $jumlah = $biayaair + $beadmin;

            $tarifB = $query['tagdibawah10'];
            $kBawa = ($tarifB > 0) ? $query['dibawah10'] / $tarifB : $kBawa = 0;

            $tarifA = $query['tagdiatas10'];
            $kAtas = ($tarifA > 0) ? ($query['diatas10'] / $tarifA) : $kAtas = 0;

            $kubukasi = $kAtas + $kBawa;

            $totalPelanggan = $this->db->table('m_pelanggan')
                ->select('COUNT(DISTINCT id_pelanggan) as totalPelanggan')
                ->where('id_jenis', $jp->id_jenis);
            if ($kel !== 'all') {
                $totalPelanggan->where('id_kelurahan', $kel);
            }
            $totalPelanggan = $totalPelanggan->get()->getRow();

            $html .= "<tr>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . ($key + 1) . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder py-1'>" . $jp->jenis_pelanggan . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . $totalPelanggan->totalPelanggan . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . $query['rekening'] . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . $kBawa . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . $kAtas . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-center py-1'>" . $kubukasi . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-end fst-italic py-1'>Rp. " . number_format($biayaair) . "</td>";
            // $html .= "<td class='text-black aling-middle fw-bolder text-end fst-italic py-1'>Rp. " . number_format($query['beadmin']) . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-end fst-italic py-1'>Rp. " . number_format($beadmin) . "</td>";
            $html .= "<td class='text-black aling-middle fw-bolder text-end fst-italic py-1'>Rp. " . number_format($jumlah) . "</td>";
            $html .= "</tr>";
        }

        echo json_encode(['html' => $html]);
    }

    public function modal_export_pdf()
    {
        $data['jenisLaporan'] = $this->request->getVar('jenisLaporan');
        $data['dateStart'] = $this->request->getVar('dateStart');
        $data['dateEnd'] = $this->request->getVar('dateEnd');
        $data['statusLaporan'] = $this->request->getVar('statusLaporan');
        $data['unit'] = $this->request->getVar('unit');
        $data['kel'] = $this->request->getVar('kel');

        $msg = ['html' => view('laporan/rekap_drd_air/modal/pdf', $data)];
        echo json_encode($msg);
    }

    public function export_pdf($jenisLaporan, $dateStart, $dateEnd, $statusLaporan, $unit, $kel)
    {
        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>LAPORAN REKAPAN REKENING DITAGIH (DRD) AIR</h3>";
        $html .= "<p align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>PERIODE : " . date('d/m/Y', strtotime($dateStart)) . " - " . date('d/m/Y', strtotime($dateEnd)) . "</b></p>";

        if ($jenisLaporan == 'perUnit') {
            $nama = ($unit == 'all') ? 'SEMUA UNIT' : $this->db->table('m_kecamatan')->where('id_kec', $unit)->get()->getRowArray()['kecamatan'];

            $html .= '<br>';
            $html .= '<div class="table">';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<th width="5%" rowspan="2">No</th>';
            $html .= '<th rowspan="2">Jenis Pelanggan</th>';
            $html .= '<th rowspan="2">Terpasang</th>';
            $html .= '<th rowspan="2">LBR</th>';
            $html .= '<th colspan="3">M<sup>M3</sup></th>';
            $html .= '<th rowspan="2">HARGA AIR</th>';
            $html .= '<th rowspan="2">BA</th>';
            $html .= '<th rowspan="2">JUMLAH</th>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>&lt; 10<sup>3</sup></th>';
            $html .= '<th>&gt; 10<sup>3</sup></th>';
            $html .= '<th>KUBIKASI</th>';
            $html .= '</tr>';

            $m_jenis_pelanggan = $this->db->table('m_jenis_pelanggan')->get()->getResult();
            foreach ($m_jenis_pelanggan as $key => $jp) {
                $idJenis = $jp->id_jenis;

                $builder = $this->db->table('t_tagihan')
                    ->select('
                    SUM(pemakaian_air) as pemakaian_air, 
                    COUNT(id_pemakaian) as rekening, 
                    SUM(tag_dibawah_10) as dibawah10, 
                    tarif_dibawah_10 as tagdibawah10, 
                    tarif_diatas_10 as tagdiatas10, 
                    SUM(tag_diatas_10) as diatas10, 
                    SUM(by_admin) as beadmin
                ')
                    ->where('bulan_tagihan >=', $dateStart)
                    ->where('bulan_tagihan <=', $dateEnd)
                    ->where('id_jenis', $idJenis);

                if ($statusLaporan != 'all') {
                    $builder->where('status', $statusLaporan);
                }

                if ($unit !== 'all') {
                    $builder->join('t_kelurahan', 't_kelurahan.id_kelurahan = t_tagihan.id_kelurahan');
                    $builder->where('t_kelurahan.id_kec', $unit);
                }

                $query = $builder->get()->getRowArray();

                $beadmin = $query['beadmin'] ?? 0;
                $biayaair = $query['dibawah10'] + $query['diatas10'];
                // $jumlah = $biayaair + $query['beadmin'];
                $jumlah = $biayaair + $beadmin;

                $tarifB = $query['tagdibawah10'];
                $kBawa = ($tarifB > 0) ? $query['dibawah10'] / $tarifB : $kBawa = 0;

                $tarifA = $query['tagdiatas10'];
                $kAtas = ($tarifA > 0) ? ($query['diatas10'] / $tarifA) : $kAtas = 0;

                $kubukasi = $kAtas + $kBawa;

                $totalPelanggan = $this->db->table('m_pelanggan')
                    ->select('COUNT(DISTINCT id_pelanggan) as totalPelanggan')
                    ->where('id_jenis', $jp->id_jenis);
                if ($kel !== 'all') {
                    $totalPelanggan->where('id_kec', $unit);
                }
                $totalPelanggan = $totalPelanggan->get()->getRow();

                $html .= "<tr>";
                $html .= "<td class='text-center'>" . ($key + 1) . "</td>";
                $html .= "<td class='py-1'>" . $jp->jenis_pelanggan . "</td>";
                $html .= "<td class='text-center'>" . $totalPelanggan->totalPelanggan . "</td>";
                $html .= "<td class='text-center'>" . $query['rekening'] . "</td>";
                $html .= "<td class='text-center'>" . $kBawa . "</td>";
                $html .= "<td class='text-center'>" . $kAtas . "</td>";
                $html .= "<td class='text-center'>" . $kubukasi . "</td>";
                $html .= "<td class='text-right'>Rp. " . number_format($biayaair) . "</td>";
                $html .= "<td class='text-right'>Rp. " . number_format($beadmin) . "</td>";
                $html .= "<td class='text-right'>Rp. " . number_format($jumlah) . "</td>";
                $html .= "</tr>";
            }


            $html .= '</table>';
            $html .= '</div>';
        } else if ($jenisLaporan == 'perKel') {
            $nama_kelurahan = ($kel == 'all') ? 'SEMUA KELURAHAN' : $this->db->table('t_kelurahan')->where('id_kelurahan', $kel)->get()->getRowArray()['kelurahan'];
            $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>PERKELURAHAN : " . $nama_kelurahan . "</h3>";

            $html .= '<br>';
            $html .= '<div class="table">';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<th width="5%" rowspan="2">No</th>';
            $html .= '<th rowspan="2">Jenis Pelanggan</th>';
            $html .= '<th rowspan="2">Terpasang</th>';
            $html .= '<th rowspan="2">LBR</th>';
            $html .= '<th colspan="3">M<sup>M3</sup></th>';
            $html .= '<th rowspan="2">HARGA AIR</th>';
            $html .= '<th rowspan="2">BA</th>';
            $html .= '<th rowspan="2">JUMLAH</th>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>&lt; 10<sup>3</sup></th>';
            $html .= '<th>&gt; 10<sup>3</sup></th>';
            $html .= '<th>KUBIKASI</th>';
            $html .= '</tr>';

            $m_jenis_pelanggan = $this->db->table('m_jenis_pelanggan')->get()->getResult();
            foreach ($m_jenis_pelanggan as $key => $jp) {
                $idJenis = $jp->id_jenis;

                $builder = $this->db->table('t_tagihan')
                    ->select('
                    SUM(pemakaian_air) as pemakaian_air, 
                    COUNT(id_pemakaian) as rekening, 
                    SUM(tag_dibawah_10) as dibawah10, 
                    tarif_dibawah_10 as tagdibawah10, 
                    tarif_diatas_10 as tagdiatas10, 
                    SUM(tag_diatas_10) as diatas10, 
                    SUM(by_admin) as beadmin
                ')
                    ->where('bulan_tagihan >=', $dateStart)
                    ->where('bulan_tagihan <=', $dateEnd)
                    ->where('id_jenis', $idJenis);

                if ($statusLaporan != 'all') {
                    $builder->where('status', $statusLaporan);
                }

                if ($kel !== 'all') {
                    $builder->join('t_kelurahan', 't_kelurahan.id_kelurahan = t_tagihan.id_kelurahan');
                    $builder->where('t_kelurahan.id_kelurahan', $kel);
                }

                $query = $builder->get()->getRowArray();

                $beadmin = $query['beadmin'] ?? 0;
                $biayaair = $query['dibawah10'] + $query['diatas10'];
                // $jumlah = $biayaair + $query['beadmin'];
                $jumlah = $biayaair + $beadmin;


                $tarifB = $query['tagdibawah10'];
                $kBawa = ($tarifB > 0) ? $query['dibawah10'] / $tarifB : $kBawa = 0;

                $tarifA = $query['tagdiatas10'];
                $kAtas = ($tarifA > 0) ? ($query['diatas10'] / $tarifA) : $kAtas = 0;

                $kubukasi = $kAtas + $kBawa;

                $totalPelanggan = $this->db->table('m_pelanggan')
                    ->select('COUNT(DISTINCT id_pelanggan) as totalPelanggan')
                    ->where('id_jenis', $jp->id_jenis);
                if ($kel !== 'all') {
                    $totalPelanggan->where('id_kelurahan', $kel);
                }
                $totalPelanggan = $totalPelanggan->get()->getRow();

                $html .= "<tr>";
                $html .= "<td class='text-black aling-middle text-center py-1'>" . ($key + 1) . "</td>";
                $html .= "<td class='text-black aling-middle py-1'>" . $jp->jenis_pelanggan . "</td>";
                $html .= "<td class='text-black aling-middle text-center py-1'>" . $totalPelanggan->totalPelanggan . "</td>";
                $html .= "<td class='text-black aling-middle text-center py-1'>" . $query['rekening'] . "</td>";
                $html .= "<td class='text-black aling-middle text-center py-1'>" . $kBawa . "</td>";
                $html .= "<td class='text-black aling-middle text-center py-1'>" . $kAtas . "</td>";
                $html .= "<td class='text-black aling-middle text-center py-1'>" . $kubukasi . "</td>";
                $html .= "<td class='text-black aling-middle text-end fst-italic py-1'>Rp. " . number_format($biayaair) . "</td>";
                // $html .= "<td class='text-black aling-middle text-end fst-italic py-1'>Rp. " . number_format($query['beadmin']) . "</td>";
                $html .= "<td class='text-black aling-middle text-end fst-italic py-1'>Rp. " . number_format($beadmin) . "</td>";
                $html .= "<td class='text-black aling-middle text-end fst-italic py-1'>Rp. " . number_format($jumlah) . "</td>";
                $html .= "</tr>";
            }

            $html .= '</table>';
            $html .= '</div>';
        }

        $html .= '</br>';

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
        $mpdf->SetTitle("LAPORAN REKAPAN REKENING DITAGIH (DRD) AIR");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
