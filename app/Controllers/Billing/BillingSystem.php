<?php

namespace App\Controllers\Billing;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

/**
 * Dashboard Billing System: pemantauan penagihan, BMS, dan LPP unit.
 *
 * Padanan module/home/billing_sistem.php aplikasi lama yang berisi lima
 * tab. Tab "Catat Meter" punya halaman sendiri
 * (Billing\CatatMeter) karena mengubah data. Sisa tab di sini hanya
 * membaca.
 *
 * Tab yang aktif:
 *   - Penagih  : progres penagihan per petugas mobileloket
 *   - BMS      : penagihan lewat Bima Sakti FastPay (users.level='fastpay')
 *   - PCM      : aktivitas pencatatan meter per petugas (tercatat/terlewati)
 *
 * Tab "LPP Unit" di aplikasi lama (ss_rekap_lpp_air.php, 830 baris)
 * belum diport: laporannya matriks lima lapis (unit x jenis pelanggan x
 * loket x bulan berjalan/tunggakan) yang perlu dipetakan ulang dulu.
 */
class BillingSystem extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Billing System';
    }

    public function index()
    {
        return view('billing/system/main', ['title' => $this->title]);
    }

    /**
     * Progres penagihan per petugas mobileloket.
     */
    public function penagih()
    {
        $awal  = $this->tanggal('awal', date('Y-m-01'));
        $akhir = $this->tanggal('akhir', date('Y-m-d'));

        $builder = $this->db->table('users u')
            ->select('
                u.id_session,
                u.username,
                u.nama_lengkap,
                u.id_kec,
                k.kecamatan
            ')
            ->join('t_kecamatan k', 'k.id_kec = u.id_kec', 'left')
            ->where('u.level', 'mobileloket')
            ->where('u.blokir', 'N')
            ->where('u.mobile_akses', '1');

        return DataTable::of($builder)
            ->add('pelanggan_unit', function ($row) {
                return $this->db->table('m_pelanggan')
                    ->where('id_kec', (int) $row->id_kec)
                    ->where('status_aktif', '1')
                    ->countAllResults();
            })
            ->add('ditagih', function ($row) use ($awal, $akhir) {
                return $this->db->table('t_tagihan t')
                    ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan')
                    ->where('t.status', '1')
                    ->where('t.id_user', $row->id_session)
                    ->where('DATE(t.tgl_bayar) >=', $awal)
                    ->where('DATE(t.tgl_bayar) <=', $akhir)
                    ->where('p.id_kec', (int) $row->id_kec)
                    ->countAllResults();
            })
            ->add('nilai', function ($row) use ($awal, $akhir) {
                $r = $this->db->table('t_tagihan t')
                    ->select('IFNULL(SUM(CAST(t.jlh_tagihan AS SIGNED)), 0) AS total', false)
                    ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan')
                    ->where('t.status', '1')
                    ->where('t.id_user', $row->id_session)
                    ->where('DATE(t.tgl_bayar) >=', $awal)
                    ->where('DATE(t.tgl_bayar) <=', $akhir)
                    ->where('p.id_kec', (int) $row->id_kec)
                    ->get()
                    ->getRowArray();

                return (float) ($r['total'] ?? 0);
            })
            ->add('aksi', function ($row) use ($awal, $akhir) {
                return '<button type="button" class="btn btn-sm btn-outline-primary" '
                    . 'onclick="lihatRincian(' . "'" . esc($row->id_session, 'js') . "'" . ', '
                    . "'" . esc($row->nama_lengkap, 'js') . "'" . ')">'
                    . '<i class="fa fa-list"></i> Rincian</button>';
            })
            ->toJson(true);
    }

    /**
     * Rincian tagihan satu petugas (modal).
     */
    public function rincian_penagih()
    {
        $idSession = (string) $this->request->getVar('id_session');
        $awal      = $this->tanggal('awal', date('Y-m-01'));
        $akhir     = $this->tanggal('akhir', date('Y-m-d'));

        $rows = $this->db->table('t_tagihan t')
            ->select('
                t.no_rek, t.bulan_tagihan, t.jlh_tagihan, t.tgl_bayar,
                p.no_sambungan, p.nama_pelanggan,
                k.kecamatan, kl.kelurahan
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan')
            ->join('t_kecamatan k', 'k.id_kec = p.id_kec', 'left')
            ->join('t_kelurahan kl', 'kl.id_kelurahan = p.id_kelurahan', 'left')
            ->where('t.status', '1')
            ->where('t.id_user', $idSession)
            ->where('DATE(t.tgl_bayar) >=', $awal)
            ->where('DATE(t.tgl_bayar) <=', $akhir)
            ->orderBy('t.tgl_bayar', 'DESC')
            ->orderBy('t.id_pemakaian', 'ASC')
            ->get()
            ->getResultArray();

        $total = 0;
        foreach ($rows as $r) {
            $total += (float) $r['jlh_tagihan'];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'html'   => view('billing/system/modal/rincian_penagih', [
                'rows'   => $rows,
                'total'  => $total,
                'awal'   => $awal,
                'akhir'  => $akhir,
            ]),
        ]);
    }

    /**
     * Penagihan lewat BMS FastPay.
     */
    public function bms()
    {
        $awal  = $this->tanggal('awal', date('Y-m-01'));
        $akhir = $this->tanggal('akhir', date('Y-m-d'));

        $builder = $this->db->table('t_tagihan t')
            ->select('
                t.id_pemakaian,
                t.no_rek,
                t.bulan_tagihan,
                t.jlh_tagihan,
                t.tgl_bayar,
                p.no_sambungan,
                p.nama_pelanggan,
                k.kecamatan,
                kl.kelurahan,
                u.nama_lengkap AS nama_petugas
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan')
            ->join('users u', 'u.id_session = t.id_user', 'left')
            ->join('t_kecamatan k', 'k.id_kec = p.id_kec', 'left')
            ->join('t_kelurahan kl', 'kl.id_kelurahan = p.id_kelurahan', 'left')
            ->where('t.status', '1')
            ->where('u.level', 'fastpay')
            ->where('DATE(t.tgl_bayar) >=', $awal)
            ->where('DATE(t.tgl_bayar) <=', $akhir);

        $builder->orderBy('t.tgl_bayar', 'DESC');
        $builder->orderBy('t.id_pemakaian', 'ASC');

        return DataTable::of($builder)
            ->add('jlh_tagihan', function ($row) {
                return (float) $row->jlh_tagihan;
            })
            ->add('tgl_bayar', function ($row) {
                return $row->tgl_bayar ? date('d/m/Y H:i', strtotime($row->tgl_bayar)) : '-';
            })
            ->add('bulan_tagihan', function ($row) {
                return $row->bulan_tagihan ? date('M Y', strtotime($row->bulan_tagihan)) : '-';
            })
            ->toJson(true);
    }

    /**
     * Ringkasan BMS: jumlah transaksi + total nilai untuk periode aktif.
     * Dipisah supaya tabel tidak perlu menghitung di sisi klien.
     */
    public function bms_ringkasan()
    {
        $awal  = $this->tanggal('awal', date('Y-m-01'));
        $akhir = $this->tanggal('akhir', date('Y-m-d'));

        $row = $this->db->table('t_tagihan t')
            ->select('COUNT(*) AS jumlah, IFNULL(SUM(CAST(t.jlh_tagihan AS SIGNED)), 0) AS total', false)
            ->join('users u', 'u.id_session = t.id_user', 'left')
            ->where('t.status', '1')
            ->where('u.level', 'fastpay')
            ->where('DATE(t.tgl_bayar) >=', $awal)
            ->where('DATE(t.tgl_bayar) <=', $akhir)
            ->get()
            ->getRowArray();

        return $this->response->setJSON([
            'status' => 'success',
            'jumlah' => (int) ($row['jumlah'] ?? 0),
            'total'  => (float) ($row['total'] ?? 0),
        ]);
    }

    /**
     * Aktivitas PCM: pelanggan per petugas meter, ditandai sudah atau
     * belum tercatat pada periode terpilih.
     *
     * Padanan control_meter_evaluasi.php + data/ss_evaluasi_pcm.php.
     * "Tercatat" = ada baris di t_catat_meter ATAU t_tagihan untuk
     * pelanggan itu pada periode tersebut.
     */
    public function pcm()
    {
        $bulan    = (int) ($this->request->getVar('bulan') ?? date('n'));
        $tahun    = (int) ($this->request->getVar('tahun') ?? date('Y'));
        $idPetugas = (string) ($this->request->getVar('petugas') ?? '');
        $status   = (string) ($this->request->getVar('status') ?? 'all');

        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) date('n');
        }

        if ($tahun < 2000) {
            $tahun = (int) date('Y');
        }

        $builder = $this->db->table('m_pelanggan p')
            ->select('
                p.id_pelanggan,
                p.no_sambungan,
                p.nama_pelanggan,
                p.status_aktif,
                k.kecamatan,
                kl.kelurahan,
                j.kd_jenis,
                c.angka_meter,
                c.tgl_catat,
                t.pemakaian_air AS meter_tagihan,
                t.bulan_tagihan,
                u.nama_lengkap AS nama_petugas
            ')
            ->join('t_kecamatan k', 'k.id_kec = p.id_kec', 'left')
            ->join('t_kelurahan kl', 'kl.id_kelurahan = p.id_kelurahan', 'left')
            ->join('m_jenis_pelanggan j', 'j.id_jenis = p.id_jenis', 'left')
            ->join('users u', "CONVERT(u.id_session USING latin1) COLLATE latin1_swedish_ci = p.petugas_meter", 'left', false)
            // Satu catatan meter per pelanggan pada periode ini.
            ->join(
                '(SELECT id_pelanggan, MAX(angka_meter) AS angka_meter, MAX(tgl_catat) AS tgl_catat
                  FROM t_catat_meter
                  WHERE MONTH(tgl_catat) = ' . $bulan . ' AND YEAR(tgl_catat) = ' . $tahun . '
                  GROUP BY id_pelanggan) c',
                'c.id_pelanggan = p.id_pelanggan',
                'left',
                false
            )
            ->join(
                '(SELECT id_pelanggan, MAX(pemakaian_air) AS pemakaian_air, MAX(bulan_tagihan) AS bulan_tagihan
                  FROM t_tagihan
                  WHERE MONTH(bulan_tagihan) = ' . $bulan . ' AND YEAR(bulan_tagihan) = ' . $tahun . '
                  GROUP BY id_pelanggan) t',
                't.id_pelanggan = p.id_pelanggan',
                'left',
                false
            )
            ->where('p.status_aktif', '1');

        if ($idPetugas !== '') {
            $builder->where('p.petugas_meter', $idPetugas);
        }

        // Filter status PCM: tercatat = punya catat meter atau rekening;
        // terlewati = tidak punya keduanya.
        if ($status === 'N') {
            $builder->where('c.angka_meter IS NULL', null, false)
                ->where('t.pemakaian_air IS NULL', null, false);
        } else {
            $builder->groupStart()
                ->where('c.angka_meter IS NOT NULL', null, false)
                ->orWhere('t.pemakaian_air IS NOT NULL', null, false)
                ->groupEnd();
        }

        $builder->orderBy('kl.kelurahan', 'ASC');
        $builder->orderBy('p.no_sambungan', 'ASC');
        $builder->orderBy('p.id_pelanggan', 'ASC');

        return DataTable::of($builder)
            ->add('status_catat', function ($row) {
                return ($row->angka_meter !== null || $row->meter_tagihan !== null)
                    ? '<span class="badge bg-success">Tercatat</span>'
                    : '<span class="badge bg-danger">Terlewati</span>';
            })
            ->add('angka_meter', function ($row) {
                return $row->angka_meter !== null ? (int) $row->angka_meter : (int) $row->meter_tagihan;
            })
            ->add('tgl_catat', function ($row) {
                $tgl = $row->tgl_catat ?? $row->bulan_tagihan;

                return $tgl ? date('d/m/Y', strtotime($tgl)) : '-';
            })
            ->toJson(true);
    }

    /**
     * Daftar petugas pencatat meter untuk dropdown PCM.
     */
    public function petugas_meter()
    {
        // m_pelanggan.petugas_meter (latin1_swedish_ci) dan
        // users.id_session (latin1_general_ci) beda collation, jadi join
        // langsung ditolak MySQL. Samakan collation saat join.
        $rows = $this->db->table('m_pelanggan p')
            ->select('p.petugas_meter, u.nama_lengkap, COUNT(*) AS jumlah')
            ->join('users u', "CONVERT(u.id_session USING latin1) COLLATE latin1_swedish_ci = p.petugas_meter", 'left', false)
            ->where('p.status_aktif', '1')
            ->where('p.petugas_meter IS NOT NULL', null, false)
            ->where('p.petugas_meter !=', '')
            ->groupBy('p.petugas_meter, u.nama_lengkap')
            ->orderBy('jumlah', 'DESC')
            ->get()
            ->getResultArray();

        $list = [['id' => '', 'text' => 'SEMUA PETUGAS']];

        foreach ($rows as $row) {
            $list[] = [
                'id'   => $row['petugas_meter'],
                'text' => strtoupper($row['nama_lengkap'] ?? 'TANPA NAMA') . ' (' . $row['jumlah'] . ')',
            ];
        }

        return $this->response->setJSON($list);
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    private function tanggal(string $kunci, string $default): string
    {
        $nilai = (string) ($this->request->getVar($kunci) ?? '');
        $ts    = strtotime($nilai);

        return $ts === false ? $default : date('Y-m-d', $ts);
    }
}
