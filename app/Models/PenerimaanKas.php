<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Penerimaan kas (air & non air).
 *
 * Tabel `penerimaan_kas` berasal dari database warisan dan kolom `id`-nya
 * tidak memakai AUTO_INCREMENT (tanpa primary key juga). Karena itu id harus
 * diisi sendiri sebelum insert, kalau tidak MySQL menolak dengan
 * "Field 'id' doesn't have a default value".
 */
class PenerimaanKas extends Model
{
    protected $table      = 'penerimaan_kas';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'id',
        'nomor_bukti',
        'tgl_penerimaan',
        'id_kec',
        'id_kelompok',
        'id_jenis',
        'jumlah',
        'keterangan',
    ];

    /**
     * Simpan beberapa baris penerimaan sekaligus dengan id berurutan.
     *
     * Kalau $prefix diisi, nomor bukti dibuat otomatis di dalam transaksi yang
     * sama (jadi nomor urut tidak bentrok dengan proses simpan lain).
     *
     * @return array{tersimpan: int, nomor_bukti: string}
     */
    public function simpanBatch(array $rows, ?string $prefix = null): array
    {
        if ($rows === []) {
            return ['tersimpan' => 0, 'nomor_bukti' => ''];
        }

        $this->db->transBegin();

        try {
            // FOR UPDATE mengunci baris sampai transaksi selesai, supaya dua
            // proses simpan bersamaan tidak memakai id yang sama.
            $maks = $this->db->query('SELECT MAX(id) AS maks FROM ' . $this->db->protectIdentifiers($this->table) . ' FOR UPDATE')
                ->getRow();
            $next = (int) ($maks->maks ?? 0);

            foreach ($rows as &$row) {
                $row['id'] = ++$next;
            }
            unset($row);

            $nomorBukti = (string) ($rows[0]['nomor_bukti'] ?? '');

            if ($prefix !== null) {
                $nomorBukti = $this->nomorBuktiBerikutnya($prefix, (string) $rows[0]['tgl_penerimaan']);

                foreach ($rows as &$row) {
                    $row['nomor_bukti'] = $nomorBukti;
                }
                unset($row);
            }

            $hasil = $this->insertBatch($rows, null, 100);

            if ($hasil === false || $this->db->transStatus() === false) {
                $this->db->transRollback();

                return ['tersimpan' => 0, 'nomor_bukti' => ''];
            }

            $this->db->transCommit();

            return ['tersimpan' => count($rows), 'nomor_bukti' => $nomorBukti];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal simpan penerimaan kas: ' . $e->getMessage());

            return ['tersimpan' => 0, 'nomor_bukti' => ''];
        }
    }

    /**
     * Nomor bukti otomatis, mis. PA-20261118-001 / PN-20261118-001.
     * Nomor dihitung per prefix + tanggal penerimaan, urut numerik.
     */
    public function nomorBuktiBerikutnya(string $prefix, string $tanggal): string
    {
        $kode = $prefix . '-' . date('Ymd', strtotime($tanggal)) . '-';

        $row = $this->db->query(
            'SELECT MAX(CAST(SUBSTRING(nomor_bukti, ?) AS UNSIGNED)) AS urut FROM ' . $this->db->protectIdentifiers($this->table) . ' WHERE nomor_bukti LIKE ?',
            [strlen($kode) + 1, $kode . '%']
        )->getRow();

        $urut = (int) ($row->urut ?? 0) + 1;

        return $kode . str_pad((string) $urut, 3, '0', STR_PAD_LEFT);
    }
}
