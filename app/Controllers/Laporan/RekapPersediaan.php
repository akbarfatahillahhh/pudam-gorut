<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

/**
 * Rekap Persediaan: mutasi stok barang gudang per periode.
 *
 * Sebelumnya controller ini adalah salinan PelangganDuplikat — judul,
 * query, dan PDF-nya semuanya data pelanggan. Sekarang benar-benar
 * membaca tabel logistik:
 *
 *   stok awal  = jumlah_stok_akhir baris mutasi terakhir SEBELUM periode
 *   masuk      = SUM(logistik_penerimaan_tampung.stok_diterima)
 *   keluar     = SUM(logistik_permintaan_tampung.stok_diberikan)
 *   stok akhir = stok awal + masuk - keluar
 *
 * Kolom stok_akhir di logistik_barang sengaja TIDAK dipakai sebagai
 * sumber: nilainya stok saat ini, bukan stok pada periode yang dipilih.
 */
class RekapPersediaan extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Rekap Persediaan';
    }

    public function index()
    {
        return view('laporan/rekap_persediaan/main', [
            'title'       => $this->title,
            'daftarBulan' => [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
            ],
        ]);
    }

    /**
     * Potongan halaman tabel rekap (dipanggil dari main.php).
     */
    public function loadPage()
    {
        $msg = ['page' => view('/laporan/rekap_persediaan/page/main')];
        echo json_encode($msg);
    }

    /**
     * Data tabel rekap untuk bulan/tahun terpilih.
     */
    public function datatable()
    {
        [$bulan, $tahun] = $this->periode();

        if ($bulan === null) {
            return $this->response->setJSON(['error' => 'Bulan dan tahun wajib dipilih.']);
        }

        [$awal, $akhir] = $this->batasPeriode($bulan, $tahun);

        $builder = $this->db->table('logistik_barang b')
            ->select('
                b.id,
                b.barang_kode,
                b.barang_nama,
                b.satuan,
                b.harga
            ')
            ->orderBy('b.barang_nama', 'ASC');

        return DataTable::of($builder)
            ->add('stok_awal', function ($row) use ($awal, $akhir) {
                return $this->hitungBarang((int) $row->id, $awal, $akhir)['stok_awal'];
            })
            ->add('masuk', function ($row) use ($awal, $akhir) {
                return $this->hitungBarang((int) $row->id, $awal, $akhir)['masuk'];
            })
            ->add('keluar', function ($row) use ($awal, $akhir) {
                return $this->hitungBarang((int) $row->id, $awal, $akhir)['keluar'];
            })
            ->add('stok_akhir', function ($row) use ($awal, $akhir) {
                return $this->hitungBarang((int) $row->id, $awal, $akhir)['stok_akhir'];
            })
            ->toJson(true);
    }

    public function modal_export_pdf()
    {
        [$bulan, $tahun] = $this->periode();

        $msg = [
            'html' => view('laporan/rekap_persediaan/modal/pdf', [
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]),
        ];

        echo json_encode($msg);
    }

    public function export_pdf()
    {
        [$bulan, $tahun] = $this->periode();

        if ($bulan === null) {
            return $this->response->setStatusCode(400)->setBody('Bulan dan tahun wajib dipilih.');
        }

        [$awal, $akhir] = $this->batasPeriode($bulan, $tahun);

        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $barang = $this->db->table('logistik_barang b')
            ->select('b.id, b.barang_kode, b.barang_nama, b.satuan')
            ->orderBy('b.barang_nama', 'ASC')
            ->get()
            ->getResultArray();

        $html = '<div style="margin-top:10px;">';
        $html .= "<h3 align='center' style='margin:0;'>REKAP PERSEDIAAN BARANG</h3>";
        $html .= "<p align='center' style='margin:0 0 10px;'><b>PERIODE : "
            . strtoupper($namaBulan[$bulan]) . ' ' . $tahun . "</b></p>";

        $html .= '<div class="table"><table>';
        $html .= '<tr>';
        $html .= '<th width="4%">No</th>';
        $html .= '<th width="12%">Kode Barang</th>';
        $html .= '<th>Nama Barang</th>';
        $html .= '<th width="8%">Satuan</th>';
        $html .= '<th width="8%">Stok Awal</th>';
        $html .= '<th width="8%">Masuk</th>';
        $html .= '<th width="8%">Keluar</th>';
        $html .= '<th width="8%">Stok Akhir</th>';
        $html .= '</tr>';

        $totalAwal = 0;
        $totalMasuk = 0;
        $totalKeluar = 0;
        $totalAkhir = 0;

        foreach ($barang as $i => $b) {
            $hitung = $this->hitungBarang((int) $b['id'], $awal, $akhir);

            $html .= '<tr>';
            $html .= '<td class="text-center">' . ($i + 1) . '</td>';
            $html .= '<td>' . esc($b['barang_kode']) . '</td>';
            $html .= '<td>' . esc($b['barang_nama']) . '</td>';
            $html .= '<td class="text-center">' . esc($b['satuan']) . '</td>';
            $html .= '<td class="text-right">' . number_format($hitung['stok_awal'], 0, ',', '.') . '</td>';
            $html .= '<td class="text-right">' . number_format($hitung['masuk'], 0, ',', '.') . '</td>';
            $html .= '<td class="text-right">' . number_format($hitung['keluar'], 0, ',', '.') . '</td>';
            $html .= '<td class="text-right"><b>' . number_format($hitung['stok_akhir'], 0, ',', '.') . '</b></td>';
            $html .= '</tr>';

            $totalAwal   += $hitung['stok_awal'];
            $totalMasuk  += $hitung['masuk'];
            $totalKeluar += $hitung['keluar'];
            $totalAkhir  += $hitung['stok_akhir'];
        }

        $html .= '<tr>';
        $html .= '<td colspan="4" class="text-center"><b>Total</b></td>';
        $html .= '<td class="text-right"><b>' . number_format($totalAwal, 0, ',', '.') . '</b></td>';
        $html .= '<td class="text-right"><b>' . number_format($totalMasuk, 0, ',', '.') . '</b></td>';
        $html .= '<td class="text-right"><b>' . number_format($totalKeluar, 0, ',', '.') . '</b></td>';
        $html .= '<td class="text-right"><b>' . number_format($totalAkhir, 0, ',', '.') . '</b></td>';
        $html .= '</tr>';

        $html .= '</table></div>';

        $page = view('pdf/potrait', ['html' => $html]);

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'L',
            'default_font_size' => 9,
            'default_font'      => 'arial',
            'tempDir'           => WRITEPATH . 'mpdf_temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle('REKAP PERSEDIAAN');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output('', 'S'));
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    /**
     * @return array{0:int|null, 1:int|null}
     */
    private function periode(): array
    {
        $bulan = $this->request->getVar('bulan');
        $tahun = $this->request->getVar('tahun');

        if (! is_numeric($bulan) || ! is_numeric($tahun)) {
            return [null, null];
        }

        $bulan = (int) $bulan;
        $tahun = (int) $tahun;

        if ($bulan < 1 || $bulan > 12 || $tahun < 2000) {
            return [null, null];
        }

        return [$bulan, $tahun];
    }

    /**
     * Batas periode [awal, akhir) supaya perbandingan tanggal sederhana.
     *
     * @return array{0:string, 1:string}
     */
    private function batasPeriode(int $bulan, int $tahun): array
    {
        $awal  = sprintf('%04d-%02d-01', $tahun, $bulan);
        $akhir = date('Y-m-d', strtotime($awal . ' +1 month'));

        return [$awal, $akhir];
    }

    /**
     * Hitung stok awal / masuk / keluar / stok akhir satu barang
     * dalam satu periode. Dasarnya daftar mutasi barang itu, diurutkan
     * menurut tanggal.
     *
     * @return array{stok_awal:int, masuk:int, keluar:int, stok_akhir:int}
     */
    private function hitungBarang(int $barangId, string $awal, string $akhir): array
    {
        $mutasi = $this->semuaMutasi($barangId);

        $sebelum = null;
        $pertama = null;
        $masuk   = 0;
        $keluar  = 0;
        $adaDalamPeriode = false;

        foreach ($mutasi as $m) {
            $tanggal = $m['tanggal'];

            if (strcmp($tanggal, $awal) < 0) {
                $sebelum = $m;
                continue;
            }

            if ($pertama === null) {
                $pertama = $m;
            }

            if (strcmp($tanggal, $akhir) < 0) {
                $adaDalamPeriode = true;

                if ($m['jenis'] === 'masuk') {
                    $masuk += $m['delta'];
                } else {
                    $keluar += $m['delta'];
                }
            }
        }

        // Stok pembuka: mutasi terakhir sebelum periode, atau stok_awal
        // dari mutasi pertama kalau pencatatan baru dimulai di periode ini.
        $stokAwal = 0;

        if ($sebelum !== null) {
            $stokAwal = $sebelum['stok_akhir'];
        } elseif ($pertama !== null) {
            $stokAwal = $pertama['stok_awal'];
        }

        // Kalau tidak ada mutasi sama sekali dalam periode, stok tetap.
        $stokAkhir = $adaDalamPeriode
            ? $stokAwal + $masuk - $keluar
            : $stokAwal;

        // Barang yang belum pernah bergerak sama sekali: pakai stok
        // terkini sebagai stok akhir (stok_akhir di logistik_barang
        // adalah nilai sekarang, bukan nilai historis).
        if ($sebelum === null && $pertama === null) {
            $sekarang = $this->db->table('logistik_barang')
                ->select('stok_akhir')
                ->where('id', $barangId)
                ->get()
                ->getRowArray();

            $stokAkhir = (int) ($sekarang['stok_akhir'] ?? 0);
            $stokAwal  = $stokAkhir;
        }

        return [
            'stok_awal'  => $stokAwal,
            'masuk'      => $masuk,
            'keluar'     => $keluar,
            'stok_akhir' => $stokAkhir,
        ];
    }

    /**
     * Semua mutasi satu barang, urut dari yang paling lama.
     *
     * @return list<array{tanggal:string, jenis:string, stok_awal:int, stok_akhir:int, delta:int}>
     */
    private function semuaMutasi(int $barangId): array
    {
        $rows = $this->db->query(
            "SELECT p.tanggal AS tanggal, 'masuk' AS jenis,
                    pt.stok_awal, pt.stok_diterima AS delta, pt.jumlah_stok_akhir AS stok_akhir, pt.id AS urut
             FROM logistik_penerimaan_tampung pt
             JOIN logistik_penerimaan p ON p.no_faktur = pt.no_faktur
             WHERE pt.barang_id = ?
             UNION ALL
             SELECT r.tanggal AS tanggal, 'keluar' AS jenis,
                    rt.stok_awal, rt.stok_diberikan AS delta, rt.jumlah_stok_akhir AS stok_akhir, rt.id AS urut
             FROM logistik_permintaan_tampung rt
             JOIN logistik_permintaan r ON r.no_permintaan = rt.no_permintaan
             WHERE rt.barang_id = ?
             ORDER BY tanggal ASC, urut ASC",
            [$barangId, $barangId]
        )->getResultArray();

        return array_map(static function ($r) {
            return [
                'tanggal'    => (string) $r['tanggal'],
                'jenis'      => (string) $r['jenis'],
                'stok_awal'  => (int) $r['stok_awal'],
                'stok_akhir' => (int) $r['stok_akhir'],
                'delta'      => (int) $r['delta'],
            ];
        }, $rows);
    }
}
