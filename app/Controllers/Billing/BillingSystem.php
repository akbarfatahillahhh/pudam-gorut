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
 *
 * Tab "LPP Unit" dan "Aktivitas PCM" belum diport; keduanya laporan
 * agregat dari t_tagihan + t_catat_meter yang belum ada padanannya.
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
