<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;

/**
 * Laporan aktivitas kinerja harian pegawai.
 *
 * Dulu ada di aplikasi lama sebagai Laporanekinerja (hanya halaman kosong
 * yang menampilkan daftar pegawai). Di sini laporannya benar-benar
 * menampilkan isi tabel `ekinerja` per pegawai dan per periode.
 */
class KinerjaPegawai extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Laporan Kinerja Pegawai';
    }

    public function index()
    {
        $unit = $this->db->table('t_kecamatan')
            ->select('id_kec, kecamatan')
            ->orderBy('kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        return view('laporan/kinerja_pegawai/main', [
            'title' => $this->title,
            'unit'  => $unit,
        ]);
    }

    /**
     * Daftar pegawai + jumlah kegiatan, untuk tabel utama.
     */
    public function data()
    {
        $tglAwal  = $this->request->getVar('tglAwal') ?: date('Y-m-01');
        $tglAkhir = $this->request->getVar('tglAkhir') ?: date('Y-m-t');
        $cari     = $this->request->getVar('search');

        $builder = $this->db->table('pegawai')
            ->select('pegawai.ID, pegawai.NIK, pegawai.NAMA, pegawai.STATUS')
            ->select('COUNT(ekinerja.ID) AS JUMLAH_KEGIATAN', false)
            ->select('MAX(ekinerja.TANGGAL_KINERJA) AS KEGIATAN_TERAKHIR', false)
            ->join(
                'ekinerja',
                'ekinerja.PEGAWAI_ID = pegawai.ID AND ekinerja.TANGGAL_KINERJA >= ' . $this->db->escape($tglAwal)
                    . ' AND ekinerja.TANGGAL_KINERJA <= ' . $this->db->escape($tglAkhir),
                'left'
            );

        if (!empty($cari['value'])) {
            $builder->groupStart()
                ->like('pegawai.NAMA', $cari['value'])
                ->orLike('pegawai.NIK', $cari['value'])
                ->groupEnd();
        }

        $builder->groupBy('pegawai.ID, pegawai.NIK, pegawai.NAMA, pegawai.STATUS');
        $builder->orderBy('pegawai.NAMA', 'ASC');

        $rows = $builder->get()->getResultArray();

        $data = [];
        $no   = 0;

        foreach ($rows as $row) {
            $data[] = [
                'nourut'           => ++$no,
                'ID'               => $row['ID'],
                'NIK'              => esc($row['NIK']),
                'NAMA'             => esc($row['NAMA']),
                'STATUS'           => $row['STATUS'],
                'JUMLAH_KEGIATAN'  => (int) $row['JUMLAH_KEGIATAN'],
                'KEGIATAN_TERAKHIR' => $row['KEGIATAN_TERAKHIR']
                    ? date('d/m/Y', strtotime($row['KEGIATAN_TERAKHIR']))
                    : '-',
            ];
        }

        return $this->response->setJSON([
            'data'            => $data,
            'recordsTotal'    => count($data),
            'recordsFiltered' => count($data),
        ]);
    }

    /**
     * Rincian kegiatan satu pegawai pada rentang tanggal tertentu.
     */
    public function detail()
    {
        $idPegawai = $this->request->getVar('id_pegawai');
        $tglAwal   = $this->request->getVar('tglAwal') ?: date('Y-m-01');
        $tglAkhir  = $this->request->getVar('tglAkhir') ?: date('Y-m-t');

        $pegawai = $this->db->table('pegawai')
            ->select('ID, NIK, NAMA')
            ->where('ID', $idPegawai)
            ->get()
            ->getRowArray();

        if (!$pegawai) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Pegawai tidak ditemukan.',
            ]);
        }

        $result = $this->db->table('ekinerja')
            ->select('ekinerja.*, tugas_pokok.TUGAS_POKOK')
            ->join('tugas_pokok', 'tugas_pokok.ID = ekinerja.TUGAS_POKOK_ID', 'left')
            ->where('ekinerja.PEGAWAI_ID', $idPegawai)
            ->where('ekinerja.TANGGAL_KINERJA >=', $tglAwal)
            ->where('ekinerja.TANGGAL_KINERJA <=', $tglAkhir)
            ->orderBy('ekinerja.TANGGAL_KINERJA', 'ASC')
            ->get()
            ->getResultArray();

        $html  = '';
        $total = count($result);

        if ($total > 0) {
            foreach ($result as $i => $row) {
                $statusKinerja = $row['STATUS_KINERJA'] ?? '-';
                $badge = 'bg-secondary';
                if ($statusKinerja === 'Selesai') {
                    $badge = 'bg-success';
                } elseif ($statusKinerja === 'Delegasi') {
                    $badge = 'bg-warning';
                } elseif ($statusKinerja === 'Proses') {
                    $badge = 'bg-info';
                }

                $html .= '<tr>';
                $html .= '<td class="align-middle text-center fs-2">' . ($i + 1) . '</td>';
                $html .= '<td class="align-middle text-center fs-2">'
                    . date('d/m/Y', strtotime($row['TANGGAL_KINERJA'])) . '</td>';
                $html .= '<td class="align-middle fs-2">' . esc($row['TUGAS_POKOK'] ?? '-') . '</td>';
                $html .= '<td class="align-middle fs-2">' . esc($row['KETERANGAN_LAINNYA'] ?? '-') . '</td>';
                $html .= '<td class="align-middle text-center fs-2">'
                    . '<span class="badge ' . $badge . '">' . esc($statusKinerja) . '</span></td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td class="align-middle text-center fs-2" colspan="5">'
                . 'Tidak ada kegiatan pada periode ini.</td></tr>';
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'pegawai' => esc($pegawai['NAMA']),
            'total'   => $total,
            'html'    => $html,
        ]);
    }

    /**
     * Cetak laporan kinerja satu pegawai (PDF).
     */
    public function pdf($id_pegawai)
    {
        helper('tgl_indo');

        $tglAwal  = $this->request->getVar('tglAwal') ?: date('Y-m-01');
        $tglAkhir = $this->request->getVar('tglAkhir') ?: date('Y-m-t');

        $pegawai = $this->db->table('pegawai')
            ->select('ID, NIK, NAMA')
            ->where('ID', $id_pegawai)
            ->get()
            ->getRowArray();

        if (!$pegawai) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        $result = $this->db->table('ekinerja')
            ->select('ekinerja.*, tugas_pokok.TUGAS_POKOK')
            ->join('tugas_pokok', 'tugas_pokok.ID = ekinerja.TUGAS_POKOK_ID', 'left')
            ->where('ekinerja.PEGAWAI_ID', $id_pegawai)
            ->where('ekinerja.TANGGAL_KINERJA >=', $tglAwal)
            ->where('ekinerja.TANGGAL_KINERJA <=', $tglAkhir)
            ->orderBy('ekinerja.TANGGAL_KINERJA', 'ASC')
            ->get()
            ->getResultArray();

        $html = view('laporan/kinerja_pegawai/pdf', [
            'pegawai'  => $pegawai,
            'result'   => $result,
            'tglAwal'  => $tglAwal,
            'tglAkhir' => $tglAkhir,
        ]);

        $convert = view('pdf/landscape', ['html' => $html]);

        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'Legal',
            'orientation' => 'P',
            'tempDir'     => WRITEPATH . 'mpdf_temp',
        ]);

        $mpdf->setFooter('LAPORAN KINERJA PEGAWAI');
        $mpdf->WriteHTML($convert);
        $mpdf->SetTitle('Laporan Kinerja Pegawai');

        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output('Laporan Kinerja - ' . $pegawai['NAMA'] . '.pdf', 'I');
    }
}
