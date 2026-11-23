<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

/**
 * Daftar Rincian Tunggakan/Piutang per Unit (dan per Kelurahan).
 *
 * Menggabungkan empat halaman terpisah di aplikasi lama:
 *   act=dpkecamatan          -> dp_kecamatan.php
 *   act=dpkecamatan_aktif    -> dp_kecamatan_aktif.php
 *   act=dp_piutang_new       -> dp_kec_desa_new.php
 *   act=dp_piutang_new_aktif -> dp_kec_desa_new_aktif.php
 *
 * Keempatnya query yang sama: tagihan per pelanggan untuk satu tahun,
 * disaring status bayar. Versi "_aktif" hanya menambahkan filter
 * m_pelanggan.status_aktif, dan versi "_desa" hanya menambahkan filter
 * kelurahan + jenis pelanggan. Di sini semuanya jadi satu laporan
 * dengan filter lengkap.
 *
 * Isi baris: No, Sambungan, Nama Pelanggan, Rekening, Harga Air,
 * Bea Admin, Jumlah, Bulan Tagihan. Baris dikelompokkan per pelanggan
 * (satu header per sambungan, seperti laporan lama).
 */
class TunggakanPerUnit extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Rincian Tunggakan per Unit';
    }

    public function index()
    {
        return view('laporan/tunggakan_per_unit/main', [
            'title' => $this->title,
            'tahun' => range((int) date('Y'), 2009),
        ]);
    }

    /**
     * Unit (kecamatan) untuk dropdown.
     */
    public function unit()
    {
        $rows = $this->db->table('t_kecamatan')
            ->select('id_kec, kecamatan')
            ->orderBy('kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        $list = [];

        foreach ($rows as $row) {
            $list[] = ['id' => $row['id_kec'], 'text' => $row['kecamatan']];
        }

        return $this->response->setJSON($list);
    }

    /**
     * Kelurahan untuk dropdown, mengikuti unit terpilih.
     */
    public function kelurahan()
    {
        $idKec = (int) $this->request->getVar('id_kec');

        $builder = $this->db->table('t_kelurahan')
            ->select('id_kelurahan, kelurahan')
            ->orderBy('kelurahan', 'ASC');

        if ($idKec > 0) {
            $builder->where('id_kec', $idKec);
        }

        $list = [];

        foreach ($builder->get()->getResultArray() as $row) {
            $list[] = ['id' => $row['id_kelurahan'], 'text' => $row['kelurahan']];
        }

        return $this->response->setJSON($list);
    }

    /**
     * Jenis pelanggan untuk dropdown.
     */
    public function jenis_pelanggan()
    {
        $rows = $this->db->table('m_jenis_pelanggan')
            ->select('id_jenis, jenis_pelanggan, kd_jenis')
            ->orderBy('id_jenis', 'ASC')
            ->get()
            ->getResultArray();

        $list = [];

        foreach ($rows as $row) {
            $list[] = [
                'id'   => $row['id_jenis'],
                'text' => $row['jenis_pelanggan'] . ' (' . $row['kd_jenis'] . ')',
            ];
        }

        return $this->response->setJSON($list);
    }

    public function datatable()
    {
        $filter = $this->filter();

        return DataTable::of($this->query($filter))
            ->add('hargaAir', function ($row) {
                return '<i>Rp. ' . number_format((float) $row->tag_dibawah_10 + (float) $row->tag_diatas_10, 0, ',', '.') . '</i>';
            })
            ->add('by_admin', function ($row) {
                return '<i>Rp. ' . number_format((float) $row->by_admin, 0, ',', '.') . '</i>';
            })
            ->add('jlh_tagihan', function ($row) {
                return '<i>Rp. ' . number_format((float) $row->jlh_tagihan, 0, ',', '.') . '</i>';
            })
            ->add('status', function ($row) {
                return $row->status === '1'
                    ? '<span class="badge bg-success">Lunas</span>'
                    : '<span class="badge bg-danger">Tunggakan</span>';
            })
            ->toJson(true);
    }

    public function export_pdf()
    {
        $filter = $this->filter();

        if ($filter['tahun'] < 2000) {
            return $this->response->setStatusCode(400)->setBody('Tahun wajib dipilih.');
        }

        // Batas aman PDF: mPDF butuh ~25 detik untuk 3.500 baris dan
        // max_execution_time server 30 detik. Tolak lebih awal dengan
        // pesan yang jelas, jangan dibiarkan timeout tanpa keterangan.
        $batasBaris = 4500;
        $jumlahBaris = $this->query($filter)->countAllResults(false);

        if ($jumlahBaris > $batasBaris) {
            $pesan = 'Data terlalu banyak untuk diekspor sekali jalan: '
                . number_format($jumlahBaris, 0, ',', '.') . ' baris (batas '
                . number_format($batasBaris, 0, ',', '.') . ').'
                . ' Persempit filter (unit/kelurahan/jenis/status) atau ekspor per unit.';

            return $this->response->setStatusCode(413)->setBody($pesan);
        }

        $rows = $this->query($filter)->get()->getResultArray();

        $total = ['harga_air' => 0, 'by_admin' => 0, 'jlh_tagihan' => 0];
        $baris = [];

        foreach ($rows as $i => $r) {
            $harga = (float) $r['tag_dibawah_10'] + (float) $r['tag_diatas_10'];

            $baris[] = [
                'no'        => $i + 1,
                'sambungan' => $r['no_sambungan'] ?? '-',
                'nama'      => $r['nama_pelanggan'] ?? '-',
                'rek'       => $r['no_rek'] ?? '-',
                'harga'     => $harga,
                'admin'     => (float) $r['by_admin'],
                'jumlah'    => (float) $r['jlh_tagihan'],
                'bulan'     => $r['bulan_tagihan'] ? date('M Y', strtotime($r['bulan_tagihan'])) : '-',
                'jenis'     => $r['jenis_pelanggan'] ?? '-',
            ];

            $total['harga_air']   += $harga;
            $total['by_admin']    += (float) $r['by_admin'];
            $total['jlh_tagihan'] += (float) $r['jlh_tagihan'];
        }

        $keterangan = $this->keterangan($filter);

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'L',
            'default_font_size' => 9,
            'default_font'      => 'arial',
            'tempDir'           => WRITEPATH . 'mpdf_temp',
        ]);

        $mpdf->WriteHTML(view('laporan/tunggakan_per_unit/pdf', [
            'baris_awal' => true,
            'baris'      => [],
            'total'      => $total,
            'keterangan' => $keterangan,
        ]));

        $potongan = array_chunk($baris, 200);
        $akhir    = count($potongan) - 1;

        foreach ($potongan as $i => $chunk) {
            $mpdf->WriteHTML(view('laporan/tunggakan_per_unit/pdf', [
                'baris_awal'  => false,
                'baris'       => $chunk,
                'total'       => $total,
                'keterangan'  => $keterangan,
                'baris_total' => $i === $akhir,
            ]));
        }

        $mpdf->SetTitle('DAFTAR RINCIAN TUNGGAKAN');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output('', 'S'));
    }

    /**
     * Cek jumlah baris yang akan diekspor, tanpa membuat PDF.
     * Dipakai halaman untuk memberi tahu sebelum membuka tab baru.
     */
    public function cek_jumlah()
    {
        $filter = $this->filter();

        if ($filter['tahun'] < 2000) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Tahun wajib dipilih.']);
        }

        $jumlah = $this->query($filter)->countAllResults(false);
        $batas  = 4500;

        return $this->response->setJSON([
            'status'  => 'success',
            'jumlah'  => $jumlah,
            'batas'   => $batas,
            'boleh'   => $jumlah > 0 && $jumlah <= $batas,
        ]);
    }

    // =================================================================
    // QUERY
    // =================================================================

    private function query(array $f)
    {
        $builder = $this->db->table('t_tagihan t')
            ->select('
                t.id_pemakaian,
                t.no_rek,
                t.bulan_tagihan,
                t.tag_dibawah_10,
                t.tag_diatas_10,
                t.tarif_diatas_10,
                t.by_admin,
                t.jlh_tagihan,
                t.status,
                p.id_pelanggan,
                p.no_sambungan,
                p.nama_pelanggan,
                p.status_aktif,
                j.jenis_pelanggan,
                j.kd_jenis,
                k.kelurahan
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan')
            ->join('m_jenis_pelanggan j', 'j.id_jenis = t.id_jenis', 'left')
            ->join('t_kelurahan k', 'k.id_kelurahan = p.id_kelurahan', 'left')
            ->where('YEAR(t.bulan_tagihan)', $f['tahun'], false);

        // Wilayah dari m_pelanggan: t_tagihan.id_kec NULL pada 11.474
        // baris lama, dan laporan lama memakai kolom yang bisa basi itu.
        if ($f['unit'] > 0) {
            $builder->where('p.id_kec', $f['unit']);
        }

        if ($f['kelurahan'] > 0) {
            $builder->where('p.id_kelurahan', $f['kelurahan']);
        }

        if ($f['jenis'] > 0) {
            $builder->where('t.id_jenis', $f['jenis']);
        }

        if ($f['status'] === '0' || $f['status'] === '1') {
            $builder->where('t.status', $f['status']);
        }

        if ($f['pelanggan'] === 'aktif') {
            $builder->where('p.status_aktif', '1');
        } elseif ($f['pelanggan'] === 'nonaktif') {
            $builder->where('p.status_aktif', '0');
        }

        // Kunci urutan unik supaya paginasi DataTables dan potongan PDF
        // tidak menghasilkan baris ganda / hilang.
        $builder->orderBy('p.no_sambungan', 'ASC');
        $builder->orderBy('t.bulan_tagihan', 'ASC');
        $builder->orderBy('t.id_pemakaian', 'ASC');

        return $builder;
    }

    /**
     * @return array{tahun:int, unit:int, kelurahan:int, jenis:int, status:string, pelanggan:string}
     */
    private function filter(): array
    {
        $angka = fn (string $kunci) => (int) $this->request->getVar($kunci);

        return [
            'tahun'     => $angka('tahun'),
            'unit'      => $angka('unit'),
            'kelurahan' => $angka('kelurahan'),
            'jenis'     => $angka('jenis'),
            'status'    => (string) ($this->request->getVar('status') ?? 'all'),
            'pelanggan' => (string) ($this->request->getVar('pelanggan') ?? 'all'),
        ];
    }

    private function keterangan(array $f): array
    {
        $cari = function (string $tabel, string $kolomId, string $kolomNama, int $id) {
            if ($id <= 0) {
                return null;
            }

            $row = $this->db->table($tabel)->select($kolomNama)->where($kolomId, $id)->get()->getRowArray();

            return $row ? strtoupper($row[$kolomNama]) : null;
        };

        $statusBayar = [
            'all' => 'KESELURUHAN',
            '0'   => 'BELUM DIBAYAR',
            '1'   => 'SUDAH DIBAYAR',
        ][$f['status']] ?? 'KESELURUHAN';

        $statusPelanggan = [
            'all'      => null,
            'aktif'    => 'PELANGGAN AKTIF',
            'nonaktif' => 'PELANGGAN TIDAK AKTIF',
        ][$f['pelanggan']] ?? null;

        return [
            'tahun'      => $f['tahun'],
            'unit'       => $cari('t_kecamatan', 'id_kec', 'kecamatan', $f['unit']),
            'kelurahan'  => $cari('t_kelurahan', 'id_kelurahan', 'kelurahan', $f['kelurahan']),
            'jenis'      => $cari('m_jenis_pelanggan', 'id_jenis', 'jenis_pelanggan', $f['jenis']),
            'status'     => $statusBayar,
            'pelanggan'  => $statusPelanggan,
        ];
    }
}
