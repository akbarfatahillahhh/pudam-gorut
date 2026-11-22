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

        $builder->where('t_kelurahan.id_kec', $unit);

        return DataTable::of($builder)
            ->toJson(true);
    }


   public function datatable()
    {
        $unit = $this->request->getVar('unit');
        $dateStart = $this->request->getVar('dateStart');
        $dateEnd = $this->request->getVar('dateEnd');
        $statusLaporan = $this->request->getVar('statusLaporan');

        [$tglAwal, $tglAkhir] = $this->normalisasiPeriode($dateStart, $dateEnd);

        $tahunAwal  = (int) substr($tglAwal, 0, 4);
        $tahunAkhir = (int) substr($tglAkhir, 0, 4);

        $statusCondition = '';
        if ($statusLaporan === '0' || $statusLaporan === '1') {
            $statusCondition = "AND t_tagihan.status = '" . esc($statusLaporan) . "'";
        }

        $builder = $this->db->table('t_kelurahan');
        $builder->select('t_kelurahan.id_kelurahan, t_kelurahan.kelurahan');

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            // Wilayah lewat m_pelanggan (t_tagihan.id_kelurahan bisa basi),
            // dan YEAR dibandingkan dengan angka tahun, bukan tanggal.
            $subQuery = "
                (SELECT IFNULL(SUM(CAST(t_tagihan.jlh_tagihan AS SIGNED)), 0)
                FROM t_tagihan
                JOIN m_pelanggan ON m_pelanggan.id_pelanggan = t_tagihan.id_pelanggan
                WHERE m_pelanggan.id_kelurahan = t_kelurahan.id_kelurahan
                AND YEAR(t_tagihan.bulan_tagihan) BETWEEN {$tahunAwal} AND {$tahunAkhir}
                AND MONTH(t_tagihan.bulan_tagihan) = {$bulan}
                {$statusCondition}
                ) AS tagihan_bulan_{$bulan}
            ";
            $builder->select($subQuery, false);
        }

        if ($unit !== 'all' && $unit !== '' && $unit !== null) {
            $builder->where('t_kelurahan.id_kec', (int) $unit);
        }

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

        $builder->where('t_kelurahan.id_kec', $unit);

        return DataTable::of($builder)->toJson(true);
    }


    /**
     * Terima tahun ('2026') atau tanggal ('2026-10-01'), kembalikan
     * tanggal penuh + label periode.
     *
     * @return array{0:string, 1:string, 2:string}
     */
    private function normalisasiPeriode($awal, $akhir): array
    {
        $keTanggal = static function ($nilai, bool $akhir): string {
            $nilai = trim((string) $nilai);

            if (preg_match('/^\d{4}$/', $nilai)) {
                return $akhir ? $nilai . '-12-31' : $nilai . '-01-01';
            }

            $ts = strtotime($nilai);

            return $ts === false ? ($akhir ? date('Y-m-d') : date('Y-01-01')) : date('Y-m-d', $ts);
        };

        $tglAwal  = $keTanggal($awal, false);
        $tglAkhir = $keTanggal($akhir, true);

        if ($tglAwal > $tglAkhir) {
            [$tglAwal, $tglAkhir] = [$tglAkhir, $tglAwal];
        }

        $label = substr($tglAwal, 0, 4) === substr($tglAkhir, 0, 4)
            ? substr($tglAwal, 0, 4)
            : substr($tglAwal, 0, 4) . ' - ' . substr($tglAkhir, 0, 4);

        return [$tglAwal, $tglAkhir, $label];
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
        // Halaman mengirim TAHUN saja (2026). strtotime('2026') gagal dan
        // menghasilkan 1970, jadi normalisasi dulu ke tanggal penuh.
        [$tglAwal, $tglAkhir, $labelPeriode] = $this->normalisasiPeriode($dateStart, $dateEnd);

        $html = '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>LAPORAN REKAPAN REKENING DITAGIH (DRD) AIR</h3>";
        $html .= "<p align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>PERIODE : " . $labelPeriode . "</b></p>";

        $builder = $this->db->table('t_kelurahan');
        $builder->select('t_kelurahan.id_kelurahan, t_kelurahan.kelurahan');

        $additionalCondition = $statusLaporan == 0 ? "AND t_tagihan.status = '0'" : ($statusLaporan == 1 ? "AND t_tagihan.status = '1'" : '');

        // $dateStart/$dateEnd bisa berisi tanggal penuh (2020-01-01).
        // Membandingkan YEAR(bulan_tagihan) dengan tanggal penuh selalu
        // false, jadi ambil tahunnya saja.
        $tahunAwal  = (int) substr($tglAwal, 0, 4);
        $tahunAkhir = (int) substr($tglAkhir, 0, 4);

        if ($tahunAwal < 2000) {
            $tahunAwal = 2000;
        }

        if ($tahunAkhir < $tahunAwal) {
            $tahunAkhir = $tahunAwal;
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            // Wilayah lewat m_pelanggan: t_tagihan.id_kelurahan bisa basi
            // dan id_kec NULL pada 11.474 baris lama.
            $subQuery = "
                (SELECT IFNULL(SUM(CAST(t_tagihan.jlh_tagihan AS SIGNED)), 0)
                FROM t_tagihan
                JOIN m_pelanggan ON m_pelanggan.id_pelanggan = t_tagihan.id_pelanggan
                WHERE m_pelanggan.id_kelurahan = t_kelurahan.id_kelurahan
                AND YEAR(t_tagihan.bulan_tagihan) BETWEEN {$tahunAwal} AND {$tahunAkhir}
                AND MONTH(t_tagihan.bulan_tagihan) = {$bulan}
                {$additionalCondition}
                ) as tagihan_bulan_{$bulan}
            ";
            $builder->select($subQuery, false);
        }

        if ($unit !== 'all' && $unit !== '' && $unit !== null) {
            $builder->where('t_kelurahan.id_kec', (int) $unit);
        }
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
