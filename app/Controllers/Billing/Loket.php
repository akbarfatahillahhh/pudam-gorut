<?php

namespace App\Controllers\Billing;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

/**
 * Loket pembayaran rekening air.
 *
 * Alur: cari pelanggan -> lihat riwayat tagihan -> bayar -> cetak kwitansi.
 * Di aplikasi lama (bs) ini modul `loket`, dengan tiga halaman:
 * pembayaran (daftar pelanggan aktif), cektagihan11 (riwayat tagihan),
 * dan kwitansimobile (kwitansi). Di sini ketiganya disatukan jadi satu
 * halaman + modal, memakai pola DataTables/select2/Notyf seperti modul lain.
 *
 * Tabel yang disentuh hanya `t_tagihan` (kolom bayar/tgl_bayar/status/
 * id_user) — sama persis dengan aksi_bayar.php di bs, supaya laporan lama
 * yang membaca tabel itu tetap konsisten.
 */
class Loket extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Loket Pembayaran';
    }

    public function index()
    {
        return view('billing/loket/main', ['title' => $this->title]);
    }

    /**
     * Daftar pelanggan aktif untuk dipilih di loket.
     */
    public function data()
    {
        $jenis = $this->request->getVar('jenis');
        $cari  = trim((string) $this->request->getVar('cari'));

        $builder = $this->db->table('m_pelanggan p')
            ->select('
                p.id_pelanggan,
                p.no_sambungan,
                p.nama_pelanggan,
                p.alamat,
                t_kecamatan.kecamatan,
                t_kelurahan.kelurahan,
                m_jasa.kelompok_jasa,
                m_jenis_pelanggan.jenis_pelanggan,
                m_jenis_pelanggan.kd_jenis,
                COALESCE(tag.tunggakan, 0) AS tunggakan,
                COALESCE(tag.jml_bulan, 0) AS jml_bulan
            ')
            ->join('t_kecamatan', 'p.id_kec = t_kecamatan.id_kec', 'left')
            ->join('t_kelurahan', 'p.id_kelurahan = t_kelurahan.id_kelurahan', 'left')
            ->join('m_jasa', 'p.id_jasa = m_jasa.id_jasa', 'left')
            ->join('m_jenis_pelanggan', 'p.id_jenis = m_jenis_pelanggan.id_jenis', 'left')
            ->join(
                '(SELECT id_pelanggan,
                         SUM(COALESCE(jlh_tagihan, 0) + COALESCE(denda, 0)) AS tunggakan,
                         COUNT(*) AS jml_bulan
                  FROM t_tagihan
                  WHERE status = \'0\'
                  GROUP BY id_pelanggan) tag',
                'tag.id_pelanggan = p.id_pelanggan',
                'left',
                false
            )
            ->where('p.status_aktif', '1');

        if ($jenis === 'tunggakan') {
            $builder->where('COALESCE(tag.jml_bulan, 0) >', 0, false);
        }

        if ($cari !== '') {
            $builder->groupStart()
                ->like('p.no_sambungan', $cari)
                ->orLike('p.nama_pelanggan', $cari)
                ->groupEnd();
        }

        return DataTable::of($builder)
            ->add('tunggakan', function ($row) {
                $n = (int) $row->tunggakan;

                return $n > 0
                    ? '<span class="badge bg-danger">Rp ' . number_format($n, 0, ',', '.') . '</span>'
                    : '<span class="badge bg-success">Lunas</span>';
            })
            ->add('aksi', function ($row) {
                return '<button type="button" class="btn btn-sm btn-outline-primary" '
                    . 'onclick="bukaTagihan(' . (int) $row->id_pelanggan . ')">'
                    . '<i class="fa fa-file-invoice"></i> Cek Tagihan</button>';
            })
            ->toJson(true);
    }

    /**
     * Riwayat tagihan satu pelanggan (modal).
     */
    public function tagihan()
    {
        $idPelanggan = (int) $this->request->getVar('id_pelanggan');

        $pelanggan = $this->db->table('m_pelanggan p')
            ->select('
                p.id_pelanggan,
                p.no_sambungan,
                p.nama_pelanggan,
                p.alamat,
                t_kecamatan.kecamatan,
                t_kelurahan.kelurahan,
                m_jasa.kelompok_jasa,
                m_jenis_pelanggan.jenis_pelanggan,
                m_jenis_pelanggan.kd_jenis
            ')
            ->join('t_kecamatan', 'p.id_kec = t_kecamatan.id_kec', 'left')
            ->join('t_kelurahan', 'p.id_kelurahan = t_kelurahan.id_kelurahan', 'left')
            ->join('m_jasa', 'p.id_jasa = m_jasa.id_jasa', 'left')
            ->join('m_jenis_pelanggan', 'p.id_jenis = m_jenis_pelanggan.id_jenis', 'left')
            ->where('p.id_pelanggan', $idPelanggan)
            ->get()
            ->getRow();

        if ($pelanggan === null) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Pelanggan tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'status'    => 'success',
            'pelanggan' => $pelanggan,
            'html'      => view('billing/loket/modal/tagihan', [
                'id_pelanggan' => $idPelanggan,
                'pelanggan'    => $pelanggan,
            ]),
        ]);
    }

    /**
     * Data tagihan per pelanggan untuk tabel di modal.
     */
    public function data_tagihan()
    {
        $idPelanggan = (int) $this->request->getVar('id_pelanggan');
        $bulan       = (string) $this->request->getVar('bulan');
        $tahun       = (string) $this->request->getVar('tahun');
        $status      = (string) $this->request->getVar('status');

        $builder = $this->db->table('t_tagihan')
            ->select('
                id_pemakaian,
                no_rek,
                bulan_tagihan,
                pemakaian_air,
                pemakaian_air_lalu,
                tag_dibawah_10,
                tag_diatas_10,
                by_admin,
                jlh_tagihan,
                denda,
                bayar,
                tgl_bayar,
                status
            ')
            ->where('id_pelanggan', $idPelanggan);

        if ($bulan !== '' && $bulan !== 'ALL') {
            $builder->where('MONTH(bulan_tagihan)', (int) $bulan, false);
        }

        if ($tahun !== '' && $tahun !== 'ALL') {
            $builder->where('YEAR(bulan_tagihan)', (int) $tahun, false);
        }

        if ($status !== '' && $status !== 'ALL') {
            $builder->where('status', $status);
        }

        $builder->orderBy('bulan_tagihan', 'DESC');

        return DataTable::of($builder)
            ->add('bulan_tagihan', function ($row) {
                return $row->bulan_tagihan
                    ? date('F Y', strtotime($row->bulan_tagihan))
                    : '-';
            })
            ->add('jlh_tagihan', function ($row) {
                return 'Rp ' . number_format((float) $row->jlh_tagihan, 0, ',', '.');
            })
            ->add('denda', function ($row) {
                $n = (float) $row->denda;

                return $n > 0
                    ? 'Rp ' . number_format($n, 0, ',', '.')
                    : '-';
            })
            ->add('status', function ($row) {
                return $row->status === '1'
                    ? '<span class="badge bg-success">Lunas</span>'
                    : '<span class="badge bg-danger">Menunggak</span>';
            })
            ->add('aksi', function ($row) {
                if ($row->status === '1') {
                    return '<button type="button" class="btn btn-sm btn-outline-success" '
                        . 'onclick="lihatKwitansi(' . (int) $row->id_pemakaian . ')">'
                        . '<i class="fa fa-receipt"></i> Kwitansi</button>';
                }

                $total = (float) $row->jlh_tagihan + (float) $row->denda;

                return '<button type="button" class="btn btn-sm btn-warning" '
                    . 'onclick="bayar(' . (int) $row->id_pemakaian . ', ' . (int) $total . ')">'
                    . '<i class="fa fa-money-bill"></i> Bayar</button>';
            })
            ->toJson(true);
    }

    /**
     * Proses pembayaran. Satu tagihan per permintaan, bisa dipilih banyak
     * sekaligus dari UI (dikirim berurutan).
     */
    public function bayar()
    {
        $idTagihan = (int) $this->request->getVar('id_pemakaian');

        if ($idTagihan <= 0) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Tagihan tidak valid.']);
        }

        $tagihan = $this->db->table('t_tagihan')
            ->select('id_pemakaian, jlh_tagihan, denda, status')
            ->where('id_pemakaian', $idTagihan)
            ->get()
            ->getRow();

        if ($tagihan === null) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Tagihan tidak ditemukan.']);
        }

        if ($tagihan->status === '1') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Tagihan ini sudah lunas.']);
        }

        // Sama dengan aksi_bayar.php di aplikasi lama: total = tagihan + denda.
        $total = (float) $tagihan->jlh_tagihan + (float) $tagihan->denda;

        $ok = $this->db->table('t_tagihan')
            ->where('id_pemakaian', $idTagihan)
            ->where('status', '0')
            ->update([
                'bayar'     => $total,
                'tgl_bayar' => date('Y-m-d H:i:s'),
                'status'    => '1',
                'id_user'   => session('id_session'),
            ]);

        if (! $ok) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Pembayaran gagal disimpan.']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'msg'    => 'Pembayaran sebesar Rp ' . number_format($total, 0, ',', '.') . ' berhasil.',
            'total'  => $total,
        ]);
    }

    /**
     * Batalkan pelunasan (hanya admin).
     */
    public function batal_bayar()
    {
        if (session('level') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'msg'    => 'Hanya admin yang boleh membatalkan pelunasan.',
            ]);
        }

        $idTagihan = (int) $this->request->getVar('id_pemakaian');

        if ($idTagihan <= 0) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Tagihan tidak valid.']);
        }

        $ok = $this->db->table('t_tagihan')
            ->where('id_pemakaian', $idTagihan)
            ->update([
                'bayar'     => 0,
                'tgl_bayar' => null,
                'status'    => '0',
            ]);

        if (! $ok) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Gagal membatalkan pelunasan.']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'msg'    => 'Pelunasan dibatalkan.',
        ]);
    }

    /**
     * Kwitansi PDF satu tagihan.
     */
    public function kwitansi_pdf($idTagihan = null)
    {
        helper('terbilang_helper');
        helper('tgl_indo');

        $idTagihan = (int) $idTagihan;

        $row = $this->db->table('t_tagihan t')
            ->select('
                t.*,
                p.no_sambungan,
                p.nama_pelanggan,
                p.alamat,
                t_kecamatan.kecamatan,
                t_kelurahan.kelurahan,
                t_kabupaten.kabupaten,
                m_jenis_pelanggan.jenis_pelanggan,
                m_jenis_pelanggan.kd_jenis,
                m_jasa.kelompok_jasa,
                u.nama_lengkap AS nama_kasir
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan', 'left')
            ->join('t_kecamatan', 't_kecamatan.id_kec = p.id_kec', 'left')
            ->join('t_kelurahan', 't_kelurahan.id_kelurahan = p.id_kelurahan', 'left')
            ->join('t_kabupaten', 't_kabupaten.id_kab = p.id_kab', 'left')
            ->join('m_jenis_pelanggan', 'm_jenis_pelanggan.id_jenis = t.id_jenis', 'left')
            ->join('m_jasa', 'm_jasa.id_jasa = p.id_jasa', 'left')
            ->join('users u', 'u.id_session = t.id_user', 'left')
            ->where('t.id_pemakaian', $idTagihan)
            ->get()
            ->getRowArray();

        if (! $row) {
            return $this->response->setStatusCode(404)->setBody('Tagihan tidak ditemukan.');
        }

        $row['terbilang'] = terbilang($row['jlh_tagihan'] ?? 0) . ' RUPIAH';
        $row['bulan_tagihan_text'] = $row['bulan_tagihan']
            ? date('F Y', strtotime($row['bulan_tagihan']))
            : '-';

        $html = view('billing/loket/pdf/kwitansi', ['row' => $row]);
        $page = view('pdf/potrait', ['html' => $html]);

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'P',
            'default_font_size' => 10,
            'default_font'      => 'arial',
            'tempDir'           => WRITEPATH . 'mpdf_temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle('KWITANSI ' . ($row['no_rek'] ?? ''));

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output('', 'S'));
    }

    /**
     * Kwitansi HTML untuk pratinjau di modal (tanpa PDF).
     */
    public function kwitansi()
    {
        helper('terbilang_helper');

        $idTagihan = (int) $this->request->getVar('id_pemakaian');

        $row = $this->db->table('t_tagihan t')
            ->select('
                t.*,
                p.no_sambungan,
                p.nama_pelanggan,
                t_kecamatan.kecamatan,
                t_kelurahan.kelurahan,
                t_kabupaten.kabupaten,
                m_jenis_pelanggan.kd_jenis
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan', 'left')
            ->join('t_kecamatan', 't_kecamatan.id_kec = p.id_kec', 'left')
            ->join('t_kelurahan', 't_kelurahan.id_kelurahan = p.id_kelurahan', 'left')
            ->join('t_kabupaten', 't_kabupaten.id_kab = p.id_kab', 'left')
            ->join('m_jenis_pelanggan', 'm_jenis_pelanggan.id_jenis = t.id_jenis', 'left')
            ->where('t.id_pemakaian', $idTagihan)
            ->get()
            ->getRowArray();

        if (! $row) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Tagihan tidak ditemukan.']);
        }

        $row['terbilang'] = terbilang($row['jlh_tagihan'] ?? 0) . ' RUPIAH';
        $row['bulan_tagihan_text'] = $row['bulan_tagihan']
            ? date('F Y', strtotime($row['bulan_tagihan']))
            : '-';

        return $this->response->setJSON([
            'status' => 'success',
            'html'   => view('billing/loket/modal/kwitansi', ['row' => $row]),
        ]);
    }
}
