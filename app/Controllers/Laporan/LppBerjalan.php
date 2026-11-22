<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;

/**
 * LPP Air Periodik (Laporan Penerimaan Penagih) per kelurahan.
 *
 * Padanan module/laporan/lppberjalan.php + get_lppberjalan.php di
 * aplikasi lama. Isinya daftar tagihan LUNAS pada rentang bulan terpilih
 * untuk satu kelurahan, lengkap dengan harga air, biaya admin, jumlah
 * tagihan, dan pemakaian air.
 *
 * Kubikasi di sini dihitung langsung `pemakaian_air - pemakaian_air_lalu`.
 * Aplikasi lama menurunkan kubikasi secara terbalik dari jumlah tagihan
 * (`(jlh - (tag_bawah + admin)) / tarif_atas + 10`), lalu memakai selisih
 * meter hanya untuk tagihan di bawah tarif atas. Dua cara itu cocok pada
 * 29.459 dari 29.470 baris tahun 2026; selisihnya hanya baris yang di
 * aplikasi lama dipaksa 0. Selisih meter langsung lebih sederhana dan
 * tidak menghasilkan 0 palsu.
 */
class LppBerjalan extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'LPP Air Periodik';
    }

    public function index()
    {
        return view('laporan/lpp_berjalan/main', ['title' => $this->title]);
    }

    /**
     * Daftar kelurahan untuk dropdown.
     */
    public function kelurahan()
    {
        $list = [];

        $rows = $this->db->table('t_kelurahan k')
            ->select('k.id_kelurahan, k.kelurahan, c.kecamatan')
            ->join('t_kecamatan c', 'c.id_kec = k.id_kec', 'left')
            ->orderBy('c.kecamatan', 'ASC')
            ->orderBy('k.kelurahan', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $list[] = [
                'id'   => $row['id_kelurahan'],
                'text' => ($row['kecamatan'] ? '[' . $row['kecamatan'] . '] ' : '') . $row['kelurahan'],
            ];
        }

        return $this->response->setJSON($list);
    }

    /**
     * Data LPP untuk tabel & PDF.
     */
    public function data()
    {
        $param = $this->parameter();

        if ($param === null) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Tahun, bulan awal, bulan akhir, dan kelurahan wajib dipilih dengan benar.',
            ]);
        }

        [$tahun, $bulanAwal, $bulanAkhir, $idKelurahan] = $param;

        $rows = $this->ambilData($tahun, $bulanAwal, $bulanAkhir, $idKelurahan);

        $total = [
            'harga_air'     => 0,
            'by_admin'      => 0,
            'jlh_tagihan'   => 0,
            'pemakaian_air' => 0,
        ];

        foreach ($rows as $i => $r) {
            $pemakaian = $r['pemakaian_air'] - $r['pemakaian_air_lalu'];

            $rows[$i]['no']            = $i + 1;
            $rows[$i]['harga_air']     = $r['tag_dibawah_10'] + $r['tag_diatas_10'];
            $rows[$i]['pemakaian']     = max(0, $pemakaian);
            $rows[$i]['bulan_text']    = $this->namaBulan((int) date('n', strtotime($r['bulan_tagihan'])))
                . ' ' . date('Y', strtotime($r['bulan_tagihan']));

            $total['harga_air']     += $rows[$i]['harga_air'];
            $total['by_admin']      += $r['by_admin'];
            $total['jlh_tagihan']   += $r['jlh_tagihan'];
            $total['pemakaian_air'] += $rows[$i]['pemakaian'];
        }

        $kelurahan = $this->db->table('t_kelurahan k')
            ->select('k.kelurahan, c.kecamatan')
            ->join('t_kecamatan c', 'c.id_kec = k.id_kec', 'left')
            ->where('k.id_kelurahan', $idKelurahan)
            ->get()
            ->getRowArray();

        return $this->response->setJSON([
            'status'    => 'success',
            'rows'      => $rows,
            'total'     => $total,
            'kelurahan' => $kelurahan,
            'periode'   => $this->namaBulan($bulanAwal) . ' s/d ' . $this->namaBulan($bulanAkhir) . ' ' . $tahun,
        ]);
    }

    public function export_pdf()
    {
        $param = $this->parameter();

        if ($param === null) {
            return $this->response->setStatusCode(400)->setBody('Parameter laporan tidak lengkap.');
        }

        [$tahun, $bulanAwal, $bulanAkhir, $idKelurahan] = $param;

        $rows = $this->ambilData($tahun, $bulanAwal, $bulanAkhir, $idKelurahan);

        $total = ['harga_air' => 0, 'by_admin' => 0, 'jlh_tagihan' => 0, 'pemakaian' => 0];

        foreach ($rows as $i => $r) {
            $rows[$i]['no']         = $i + 1;
            $rows[$i]['harga_air']  = $r['tag_dibawah_10'] + $r['tag_diatas_10'];
            $rows[$i]['pemakaian']  = max(0, $r['pemakaian_air'] - $r['pemakaian_air_lalu']);
            $rows[$i]['bulan_text'] = $this->namaBulan((int) date('n', strtotime($r['bulan_tagihan'])))
                . ' ' . date('Y', strtotime($r['bulan_tagihan']));

            $total['harga_air']   += $rows[$i]['harga_air'];
            $total['by_admin']    += $r['by_admin'];
            $total['jlh_tagihan'] += $r['jlh_tagihan'];
            $total['pemakaian']   += $rows[$i]['pemakaian'];
        }

        $kelurahan = $this->db->table('t_kelurahan k')
            ->select('k.kelurahan, k.kode_kelurahan, c.kecamatan')
            ->join('t_kecamatan c', 'c.id_kec = k.id_kec', 'left')
            ->where('k.id_kelurahan', $idKelurahan)
            ->get()
            ->getRowArray();

        $html = view('laporan/lpp_berjalan/pdf', [
            'rows'      => $rows,
            'total'     => $total,
            'kelurahan' => $kelurahan,
            'tahun'     => $tahun,
            'periode'   => $this->namaBulan($bulanAwal) . ' S/D ' . $this->namaBulan($bulanAkhir) . ' ' . $tahun,
        ]);

        $page = view('pdf/potrait', ['html' => $html]);

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'P',
            'default_font_size' => 9,
            'default_font'      => 'arial',
            'tempDir'           => WRITEPATH . 'mpdf_temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle('LPP AIR PERIODIK');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output('', 'S'));
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    /**
     * @return array{0:int, 1:int, 2:int, 3:int}|null
     */
    private function parameter(): ?array
    {
        $tahun       = $this->request->getVar('tahun');
        $bulanAwal   = $this->request->getVar('bulan_awal');
        $bulanAkhir  = $this->request->getVar('bulan_akhir');
        $idKelurahan = $this->request->getVar('kelurahan');

        if (! is_numeric($tahun) || ! is_numeric($bulanAwal) || ! is_numeric($bulanAkhir) || ! is_numeric($idKelurahan)) {
            return null;
        }

        $tahun       = (int) $tahun;
        $bulanAwal   = (int) $bulanAwal;
        $bulanAkhir  = (int) $bulanAkhir;
        $idKelurahan = (int) $idKelurahan;

        if ($tahun < 2000 || $bulanAwal < 1 || $bulanAwal > 12 || $bulanAkhir < 1 || $bulanAkhir > 12 || $idKelurahan <= 0) {
            return null;
        }

        if ($bulanAwal > $bulanAkhir) {
            [$bulanAwal, $bulanAkhir] = [$bulanAkhir, $bulanAwal];
        }

        return [$tahun, $bulanAwal, $bulanAkhir, $idKelurahan];
    }

    /**
     * Tagihan lunas pada rentang bulan, satu kelurahan.
     */
    private function ambilData(int $tahun, int $bulanAwal, int $bulanAkhir, int $idKelurahan): array
    {
        return $this->db->table('t_tagihan t')
            ->select('
                t.id_pemakaian,
                t.no_rek,
                t.bulan_tagihan,
                t.pemakaian_air,
                t.pemakaian_air_lalu,
                t.tag_dibawah_10,
                t.tag_diatas_10,
                t.tarif_diatas_10,
                t.by_admin,
                t.jlh_tagihan,
                t.bayar,
                t.tgl_bayar,
                p.no_sambungan,
                p.nama_pelanggan,
                j.kd_jenis
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan')
            ->join('m_jenis_pelanggan j', 'j.id_jenis = t.id_jenis', 'left')
            ->where('t.status', '1')
            ->where('YEAR(t.bulan_tagihan)', $tahun, false)
            ->where('MONTH(t.bulan_tagihan) >=', $bulanAwal, false)
            ->where('MONTH(t.bulan_tagihan) <=', $bulanAkhir, false)
            // Wilayah dari m_pelanggan: t_tagihan.id_kelurahan bisa basi,
            // dan 11.474 baris lama punya id_kec NULL.
            ->where('p.id_kelurahan', $idKelurahan)
            ->orderBy('t.bulan_tagihan', 'ASC')
            ->orderBy('p.no_sambungan', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function namaBulan(int $bulan): string
    {
        $nama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return $nama[$bulan] ?? '-';
    }
}
