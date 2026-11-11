<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class RincianPiutangPerunit extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Rincian Piutang Per Unit";
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/laporan/rincian_piutang_perunit/main', $data);
    }

    public function loadPage($page)
    {
        if ($page == 'perUnit') {
            $msg = ['page' =>  view('/laporan/rincian_piutang_perunit/page/per_unit')];
        }

        echo json_encode($msg);
    }

    public function datatable_old()
    {
        $unit = $this->request->getVar('unit');
        $dateStart = $this->request->getVar('dateStart');
        $dateEnd = $this->request->getVar('dateEnd');
        $statusLaporan = $this->request->getVar('statusLaporan');


        $builder = $this->db->table('t_kelurahan');
        $builder->select('t_kelurahan.id_kelurahan, t_kelurahan.kelurahan');

        $additionalCondition = '';
        if ($statusLaporan == 0) {
            $additionalCondition = "AND status = '0'";
        } elseif ($statusLaporan == 1) {
            $additionalCondition = "AND status = '1'";
        } else {
            $additionalCondition = '';
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $subQuery = "
                (SELECT SUM(jlh_tagihan) 
                FROM t_tagihan 
                WHERE t_tagihan.id_kelurahan = t_kelurahan.id_kelurahan 
                AND YEAR(bulan_tagihan) BETWEEN '{$dateStart}' AND '{$dateEnd}' 
                AND MONTH(bulan_tagihan) = {$bulan} 
                {$additionalCondition}
                ) as tagihan_bulan_{$bulan}
            ";

            $builder->select($subQuery, false);
        }

        $builder->where('id_kec', $unit);

        return DataTable::of($builder)
            ->toJson(true);
    }


   public function datatable()
    {
        $unit = $this->request->getVar('unit');
        $dateStart = (int) $this->request->getVar('dateStart');
        $dateEnd = (int) $this->request->getVar('dateEnd');
        $statusLaporan = $this->request->getVar('statusLaporan');

        $builder = $this->db->table('t_kelurahan');
        $builder->select('t_kelurahan.id_kelurahan, t_kelurahan.kelurahan');

        $statusCondition = '';
        if ($statusLaporan === '0' || $statusLaporan === '1') {
            $statusCondition = "AND status = '" . esc($statusLaporan) . "'";
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $subQuery = "
                (SELECT SUM(jlh_tagihan) 
                FROM t_tagihan 
                WHERE t_tagihan.id_kelurahan = t_kelurahan.id_kelurahan 
                AND YEAR(bulan_tagihan) BETWEEN {$dateStart} AND {$dateEnd}
                AND MONTH(bulan_tagihan) = {$bulan}
                {$statusCondition}
                ) AS tagihan_bulan_{$bulan}
            ";
            $builder->select($subQuery, false);
        }

        $builder->where('id_kec', $unit);

        return DataTable::of($builder)->toJson(true);
    }


    public function datatable_old_new()
    {
        $unit = $this->request->getVar('unit');
        $dateStart = $this->request->getVar('dateStart');
        $dateEnd = $this->request->getVar('dateEnd');
        $statusLaporan = $this->request->getVar('statusLaporan');

        $builder = $this->db->table('t_kelurahan');
        $builder->select('t_kelurahan.id_kelurahan, t_kelurahan.kelurahan');

        $additionalCondition = $statusLaporan == 0 ? "AND status = '0'" : ($statusLaporan == 1 ? "AND status = '1'" : '');

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $subQuery = "
            (SELECT SUM(jlh_tagihan) 
            FROM t_tagihan 
            WHERE t_tagihan.id_kelurahan = t_kelurahan.id_kelurahan 
            AND YEAR(bulan_tagihan) BETWEEN ? AND ? 
            AND MONTH(bulan_tagihan) = ? 
            {$additionalCondition}
            ) as tagihan_bulan_{$bulan}
        ";
            $builder->select($subQuery, false);
            $builder->addBinding([$dateStart, $dateEnd, $bulan], 'select');
        }

        $builder->where('id_kec', $unit);

        return DataTable::of($builder)->toJson(true);
    }


    public function modal_export_pdf()
    {
        $data['dateStart'] = $this->request->getVar('dateStart');
        $data['dateEnd'] = $this->request->getVar('dateEnd');
        $data['statusLaporan'] = $this->request->getVar('statusLaporan');
        $data['unit'] = $this->request->getVar('unit');


        $msg = ['html' => view('laporan/rincian_piutang_perunit/modal/pdf', $data)];
        echo json_encode($msg);
    }

    public function export_pdf($dateStart, $dateEnd, $statusLaporan, $unit)
    {
        $html = '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>LAPORAN REKAPAN REKENING DITAGIH (DRD) AIR</h3>";
        $html .= "<p align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>PERIODE : " . date('d/m/Y', strtotime($dateStart)) . " - " . date('d/m/Y', strtotime($dateEnd)) . "</b></p>";

        $builder = $this->db->table('t_kelurahan');
        $builder->select('t_kelurahan.id_kelurahan, t_kelurahan.kelurahan');

        $additionalCondition = $statusLaporan == 0 ? "AND status = '0'" : ($statusLaporan == 1 ? "AND status = '1'" : '');

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $subQuery = "
                (SELECT SUM(jlh_tagihan) 
                FROM t_tagihan 
                WHERE t_tagihan.id_kelurahan = t_kelurahan.id_kelurahan 
                AND YEAR(bulan_tagihan) BETWEEN '{$dateStart}' AND '{$dateEnd}' 
                AND MONTH(bulan_tagihan) = {$bulan} 
                {$additionalCondition}
                ) as tagihan_bulan_{$bulan}
            ";
            $builder->select($subQuery, false);
        }

        $builder->where('id_kec', $unit);
        $result = $builder->get()->getResultArray();

        $html .= '<br><div class="table"><table>';
        $html .= '<tr><th width="5%" rowspan="2">No</th><th rowspan="2">Desa</th><th colspan="12">Bulan Tunggakan Tahun</th><th rowspan="2">Jumlah</th></tr>';
        $html .= '<tr><th>Januari</th><th>Februari</th><th>Maret</th><th>April</th><th>Mei</th><th>Juni</th><th>Juli</th><th>Agustus</th><th>September</th><th>Oktober</th><th>November</th><th>Desember</th></tr>';

        $totals = array_fill(1, 12, 0);
        $totalKeseluruhan = 0;

        foreach ($result as $key => $value) {
            $html .= '<tr><td class="text-center">' . ($key + 1) . '</td><td>' . $value['kelurahan'] . '</td>';
            $totalPerbulan = 0;

            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $tagihan = $value["tagihan_bulan_{$bulan}"] ?? 0;
                $html .= '<td class="text-right">Rp. ' . number_format($tagihan) . '</td>';
                $totals[$bulan] += $tagihan;
                $totalPerbulan += $tagihan;
            }

            $html .= '<td class="text-right">Rp. ' . number_format($totalPerbulan) . '</td></tr>';
            $totalKeseluruhan += $totalPerbulan;
        }

        $html .= '<tr><td class="text-center" colspan="2"><b>Total</b></td>';
        foreach ($totals as $total) {
            $html .= '<td>Rp. ' . number_format($total) . '</td>';
        }
        $html .= '<td>Rp. ' . number_format($totalKeseluruhan) . '</td></tr>';

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
        $mpdf->SetTitle("LAPORAN RINCIAN PIUTANG PER UNIT");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
