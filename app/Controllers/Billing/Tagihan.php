<?php

namespace App\Controllers\Billing;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

/**
 * Tagihan: penerbitan rekening (entry pemakaian) dan pelunasan massal.
 *
 * Port dari modul `tagihan` aplikasi lama (bs):
 *   - entry pemakaian / rekening baru  -> form + pemakaian()
 *   - form entry pembayaran massal     -> pembayaran() + simpan_pembayaran()
 *   - cetak tagihan per kelurahan      -> cetak()
 *   - cek update no rek (duplikat)     -> duplikat()
 *
 * Rumus tagihan mengikuti bs apa adanya (lihat aksi_simpan_pemakaian.php):
 *   pemakaian  = meter_ini - meter_lalu
 *   tag bawah  = 10 x tarif_bawah_10              (selalu 10 m3, bukan min(pemakaian,10))
 *   tag atas   = max(pemakaian - 10, 0) x tarif_atas_10
 *   jumlah     = tag_bawah + tag_atas + bea_admin
 * Rumus ini sudah dicocokkan dengan 3.581 baris t_tagihan periode 2026-10.
 */
class Tagihan extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Tagihan';
    }

    public function index()
    {
        return view('billing/tagihan/main', ['title' => $this->title]);
    }

    // =================================================================
    // ENTRY PEMAKAIAN (PENERBITAN REKENING)
    // =================================================================

    /**
     * Simpan rekening baru hasil entry pemakaian.
     */
    public function simpan_pemakaian()
    {
        $idPelanggan = (int) $this->request->getVar('id_pelanggan');
        $tanggal     = (string) $this->request->getVar('tanggal');
        $meterIni    = (float) $this->request->getVar('pemakaian_air');
        $meterLalu   = (float) $this->request->getVar('pemakaian_air_lalu');

        if ($idPelanggan <= 0) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Pelanggan wajib dipilih.']);
        }

        $ts = strtotime($tanggal);

        if ($ts === false) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Tanggal tagihan tidak valid.']);
        }

        if ($meterIni < $meterLalu) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Angka meter ini tidak boleh lebih kecil dari meter lalu (' . $meterLalu . ').',
            ]);
        }

        $pelanggan = $this->db->table('m_pelanggan p')
            ->select('
                p.id_pelanggan, p.no_sambungan, p.nama_pelanggan,
                p.id_kab, p.id_kec, p.id_kelurahan, p.id_jasa, p.id_jenis,
                m_jasa.tarif_bawah_10, m_jasa.tarif_atas_10, m_jasa.bea_admin
            ')
            ->join('m_jasa', 'p.id_jasa = m_jasa.id_jasa', 'left')
            ->where('p.id_pelanggan', $idPelanggan)
            ->get()
            ->getRowArray();

        if (! $pelanggan) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Pelanggan tidak ditemukan.']);
        }

        $bulan = (int) date('n', $ts);
        $tahun = (int) date('Y', $ts);

        // Satu pelanggan hanya boleh punya satu rekening per bulan.
        $sudahAda = $this->db->table('t_tagihan')
            ->where('id_pelanggan', $idPelanggan)
            ->where('MONTH(bulan_tagihan)', $bulan, false)
            ->where('YEAR(bulan_tagihan)', $tahun, false)
            ->countAllResults() > 0;

        if ($sudahAda) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Tagihan ' . $pelanggan['nama_pelanggan'] . ' untuk '
                    . $this->namaBulan($bulan) . ' ' . $tahun . ' sudah dientri.',
            ]);
        }

        $tarifBawah = (float) $pelanggan['tarif_bawah_10'];
        $tarifAtas  = (float) $pelanggan['tarif_atas_10'];

        // Pengecualian Hankam (id_jenis 13) dari aplikasi lama: bea admin 35.000.
        $beaAdmin = (int) $pelanggan['id_jenis'] === 13
            ? 35000
            : (float) $pelanggan['bea_admin'];

        $pemakaian = $meterIni - $meterLalu;
        $tagBawah  = 10 * $tarifBawah;
        $tagAtas   = $pemakaian > 10 ? ($pemakaian - 10) * $tarifAtas : 0;
        $jumlah    = $tagBawah + $tagAtas + $beaAdmin;

        $noRek = $this->nomorRekening($bulan, $tahun);

        $ok = $this->db->table('t_tagihan')->insert([
            'no_rek'             => $noRek,
            'id_pelanggan'       => $idPelanggan,
            'id_kab'             => $pelanggan['id_kab'],
            'id_kec'             => $pelanggan['id_kec'],
            'id_kelurahan'       => $pelanggan['id_kelurahan'],
            'id_jasa'            => $pelanggan['id_jasa'],
            'id_jenis'           => $pelanggan['id_jenis'],
            'tarif_dibawah_10'   => $tarifBawah,
            'tarif_diatas_10'    => $tarifAtas,
            'bulan_tagihan'      => date('Y-m-d', $ts),
            'pemakaian_air'      => $meterIni,
            'pemakaian_air_lalu' => $meterLalu,
            'tag_dibawah_10'     => $tagBawah,
            'tag_diatas_10'      => $tagAtas,
            'by_admin'           => $beaAdmin,
            'jlh_tagihan'        => $jumlah,
            'denda'              => 0,
            'bayar'              => 0,
            'status'             => '0',
            'aktif'              => '1',
            'id_user'            => session('nama_lengkap'),
        ]);

        if (! $ok) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Rekening gagal disimpan.']);
        }

        $id = $this->db->insertID();

        return $this->response->setJSON([
            'status'    => 'success',
            'msg'       => 'Rekening ' . $noRek . ' berhasil diterbitkan.',
            'id_pemakaian' => $id,
            'no_rek'    => $noRek,
            'tagihan'   => [
                'pemakaian'  => $pemakaian,
                'tag_bawah'  => $tagBawah,
                'tag_atas'   => $tagAtas,
                'bea_admin'  => $beaAdmin,
                'jumlah'     => $jumlah,
            ],
        ]);
    }

    // =================================================================
    // ENTRY PEMBAYARAN MASSAL
    // =================================================================

    /**
     * Daftar tagihan yang belum lunas untuk satu periode + kelurahan.
     */
    public function tagihan_belum_lunas()
    {
        $bulan     = $this->request->getVar('bulan');
        $tahun     = $this->request->getVar('tahun');
        $kelurahan = (int) $this->request->getVar('kelurahan');

        $builder = $this->db->table('t_tagihan t')
            ->select('
                t.id_pemakaian,
                t.no_rek,
                t.bulan_tagihan,
                t.jlh_tagihan,
                t.denda,
                t.bayar,
                t.status,
                t.tgl_bayar,
                p.no_sambungan,
                p.nama_pelanggan,
                k.kelurahan,
                c.kecamatan,
                j.kd_jenis
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan', 'left')
            // t_tagihan lama punya id_kec NULL (11.474 baris), jadi wilayah
            // diambil dari m_pelanggan yang selalu terisi.
            ->join('t_kelurahan k', 'k.id_kelurahan = p.id_kelurahan', 'left')
            ->join('t_kecamatan c', 'c.id_kec = p.id_kec', 'left')
            ->join('m_jenis_pelanggan j', 'j.id_jenis = t.id_jenis', 'left')
            ->where('t.status', '0');

        if ($kelurahan > 0) {
            $builder->where('t.id_kelurahan', $kelurahan);
        }

        if ($bulan !== '' && $bulan !== 'ALL') {
            $builder->where('MONTH(t.bulan_tagihan)', (int) $bulan, false);
        }

        if ($tahun !== '' && $tahun !== 'ALL') {
            $builder->where('YEAR(t.bulan_tagihan)', (int) $tahun, false);
        }

        return DataTable::of($builder)
            ->add('jlh_tagihan', function ($row) {
                return (float) $row->jlh_tagihan;
            })
            ->add('denda', function ($row) {
                return (float) $row->denda;
            })
            ->add('total', function ($row) {
                return (float) $row->jlh_tagihan + (float) $row->denda;
            })
            ->add('aksi', function ($row) {
                return '<input type="checkbox" class="form-check-input pilih-tagihan" '
                    . 'value="' . (int) $row->id_pemakaian . '" '
                    . 'data-total="' . ((float) $row->jlh_tagihan + (float) $row->denda) . '">';
            })
            ->toJson(true);
    }

    /**
     * Pelunasan massal: [{id_pemakaian, bayar, tgl_bayar}].
     */
    public function simpan_pembayaran()
    {
        $items = $this->request->getVar('items');

        if (! is_array($items) || empty($items)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Tidak ada tagihan yang dipilih.']);
        }

        $berhasil = 0;
        $gagal    = [];

        $this->db->transStart();

        foreach ($items as $item) {
            $id   = (int) ($item['id_pemakaian'] ?? 0);
            $date = (string) ($item['tgl_bayar'] ?? '');

            if ($id <= 0) {
                continue;
            }

            $ts = strtotime($date);
            if ($ts === false) {
                $ts = time();
            }

            $tagihan = $this->db->table('t_tagihan')
                ->select('jlh_tagihan, denda, status')
                ->where('id_pemakaian', $id)
                ->get()
                ->getRowArray();

            if (! $tagihan) {
                $gagal[] = $id;
                continue;
            }

            if ($tagihan['status'] === '1') {
                $gagal[] = $id;
                continue;
            }

            $total = (float) $tagihan['jlh_tagihan'] + (float) $tagihan['denda'];

            $this->db->table('t_tagihan')
                ->where('id_pemakaian', $id)
                ->where('status', '0')
                ->update([
                    'bayar'     => $total,
                    'tgl_bayar' => date('Y-m-d H:i:s', $ts),
                    'status'    => '1',
                    'id_user'   => session('id_session'),
                ]);

            $berhasil++;
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Pembayaran gagal disimpan, tidak ada yang berubah.',
            ]);
        }

        $msg = $berhasil . ' tagihan berhasil dilunasi.';

        if (! empty($gagal)) {
            $msg .= ' ' . count($gagal) . ' dilewati (sudah lunas / tidak ditemukan).';
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'msg'     => $msg,
            'berhasil' => $berhasil,
            'gagal'   => $gagal,
        ]);
    }

    // =================================================================
    // CETAK TAGIHAN PER KELURAHAN
    // =================================================================

    public function cetak()
    {
        helper('terbilang_helper');

        $bulan     = (int) $this->request->getVar('bulan');
        $tahun     = (int) $this->request->getVar('tahun');
        $kelurahan = (int) $this->request->getVar('kelurahan');

        // Periode wajib. Satu tahun penuh = 43.000+ baris: PDF-nya tidak
        // berguna dan prosesnya kehabisan memori.
        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) date('n');
        }

        if ($tahun < 2000) {
            $tahun = (int) date('Y');
        }

        $filter = function ($builder) use ($bulan, $tahun, $kelurahan) {
            $builder->where('MONTH(t.bulan_tagihan)', $bulan, false);
            $builder->where('YEAR(t.bulan_tagihan)', $tahun, false);

            if ($kelurahan > 0) {
                $builder->where('p.id_kelurahan', $kelurahan);
            }

            return $builder;
        };

        $select = '
            t.id_pemakaian, t.no_rek, t.bulan_tagihan, t.pemakaian_air,
            t.pemakaian_air_lalu, t.tag_dibawah_10, t.tag_diatas_10,
            t.by_admin, t.jlh_tagihan, t.denda, t.status,
            p.no_sambungan, p.nama_pelanggan, p.alamat,
            k.kelurahan, c.kecamatan, j.kd_jenis
        ';

        $base = function () use ($select) {
            return $this->db->table('t_tagihan t')
                ->select($select)
                // t_tagihan lama punya id_kec NULL (11.474 baris), jadi
                // wilayah diambil dari m_pelanggan yang selalu terisi.
                ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan', 'left')
                ->join('t_kelurahan k', 'k.id_kelurahan = p.id_kelurahan', 'left')
                ->join('t_kecamatan c', 'c.id_kec = p.id_kec', 'left')
                ->join('m_jenis_pelanggan j', 'j.id_jenis = t.id_jenis', 'left');
        };

        $jumlah = $filter($this->db->table('t_tagihan t')
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan', 'left'))
            ->countAllResults();

        $grandTotal = (float) ($filter($this->db->table('t_tagihan t')
            ->select('SUM(COALESCE(t.jlh_tagihan, 0) + COALESCE(t.denda, 0)) AS total', false)
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan', 'left'))
            ->get()
            ->getRowArray()['total'] ?? 0);

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'L',
            'default_font_size' => 9,
            'default_font'      => 'arial',
            'tempDir'           => WRITEPATH . 'mpdf_temp',
        ]);

        // Kop + CSS ditulis sekali.
        $mpdf->WriteHTML(view('pdf/landscape', ['html' => '']));

        // Baris diambil & ditulis per potongan. Satu WriteHTML dengan ribuan
        // baris melewati pcre.backtrack_limit, dan menampung semuanya di
        // memori menghabiskan memory_limit 128M.
        $perPotong = 200;
        $offset    = 0;
        $no        = 0;
        $halaman   = 0;

        do {
            $rows = $filter($base())
                ->orderBy('c.kecamatan', 'ASC')
                ->orderBy('k.kelurahan', 'ASC')
                ->orderBy('p.no_sambungan', 'ASC')
                ->limit($perPotong, $offset)
                ->get()
                ->getResultArray();

            $jumlahPotong = count($rows);

            foreach ($rows as $i => $r) {
                $rows[$i]['_no'] = ++$no;
            }

            $terakhir = $offset + $jumlahPotong >= $jumlah;

            $mpdf->WriteHTML(view('billing/tagihan/pdf/rekap', [
                'rows'        => $rows,
                'bulan'       => $bulan,
                'tahun'       => $tahun,
                'showHeader'  => $halaman === 0,
                'showTotal'   => $terakhir,
                'grandTotal'  => $grandTotal,
                'jumlahBaris' => $jumlah,
            ]));

            $offset += $perPotong;
            $halaman++;
        } while ($offset < $jumlah);

        $mpdf->SetTitle('REKAP TAGIHAN');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output('', 'S'));
    }

    // =================================================================
    // CEK NOMOR REKENING DUPLIKAT
    // =================================================================

    public function duplikat()
    {
        return view('billing/tagihan/duplikat', ['title' => 'Cek Nomor Rekening']);
    }

    public function data_duplikat()
    {
        $bulan = $this->request->getVar('bulan');
        $tahun = $this->request->getVar('tahun');

        $builder = $this->db->table('t_tagihan')
            ->select('
                no_rek,
                YEAR(bulan_tagihan) AS tahun,
                MONTH(bulan_tagihan) AS bulan,
                COUNT(*) AS jumlah
            ')
            ->where('no_rek IS NOT NULL', null, false)
            ->where('no_rek !=', '')
            ->groupBy('no_rek')
            ->groupBy('tahun')
            ->groupBy('bulan')
            ->having('COUNT(*) >', 1);

        if ($bulan !== '' && $bulan !== 'ALL') {
            $builder->where('MONTH(bulan_tagihan)', (int) $bulan, false);
        }

        if ($tahun !== '' && $tahun !== 'ALL') {
            $builder->where('YEAR(bulan_tagihan)', (int) $tahun, false);
        }

        return DataTable::of($builder)
            ->add('periode', function ($row) {
                return $this->namaBulan((int) $row->bulan) . ' ' . $row->tahun;
            })
            ->add('aksi', function ($row) {
                return '<button type="button" class="btn btn-sm btn-outline-primary" '
                    . 'onclick="lihatDuplikat(\'' . esc($row->no_rek, 'js') . '\',' . (int) $row->bulan . ','
                    . (int) $row->tahun . ')"><i class="fa fa-eye"></i> Lihat</button>';
            })
            ->toJson(true);
    }

    /**
     * Rincian pelanggan yang memakai satu nomor rekening.
     */
    public function rincian_duplikat()
    {
        $noRek = (string) $this->request->getVar('no_rek');
        $bulan = (int) $this->request->getVar('bulan');
        $tahun = (int) $this->request->getVar('tahun');

        $rows = $this->db->table('t_tagihan t')
            ->select('
                t.id_pemakaian, t.no_rek, t.bulan_tagihan, t.jlh_tagihan,
                t.status, p.no_sambungan, p.nama_pelanggan, p.alamat
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan', 'left')
            ->where('t.no_rek', $noRek)
            ->where('MONTH(t.bulan_tagihan)', $bulan, false)
            ->where('YEAR(t.bulan_tagihan)', $tahun, false)
            ->orderBy('t.id_pemakaian', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'html'   => view('billing/tagihan/modal/duplikat', ['rows' => $rows, 'no_rek' => $noRek]),
        ]);
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    /**
     * Nomor rekening urut per periode: 0001, 0002, dst.
     *
     * Aplikasi lama memakai COUNT(*) seluruh baris periode itu, yang bisa
     * menghasilkan nomor kembar kalau ada baris terhapus. Di sini diambil
     * MAX dari nomor numerik supaya selalu naik.
     */
    private function nomorRekening(int $bulan, int $tahun): string
    {
        $row = $this->db->table('t_tagihan')
            ->select('MAX(CAST(no_rek AS UNSIGNED)) AS terakhir', false)
            ->where('MONTH(bulan_tagihan)', $bulan, false)
            ->where('YEAR(bulan_tagihan)', $tahun, false)
            ->get()
            ->getRowArray();

        $terakhir = (int) ($row['terakhir'] ?? 0);

        return str_pad((string) ($terakhir + 1), 4, '0', STR_PAD_LEFT);
    }

    private function namaBulan(int $bulan): string
    {
        $nama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return $nama[$bulan] ?? '-';
    }
}
