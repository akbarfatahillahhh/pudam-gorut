<?php
/**
 * Partial rekap tagihan untuk mPDF.
 *
 * Dirender per potongan (bukan sekali untuk semua baris) karena mPDF
 * menolak HTML > pcre.backtrack_limit. Controller memanggil view ini
 * berulang kali dengan $showHeader untuk potongan pertama.
 *
 * Variabel: $rows, $bulan, $tahun, $showHeader, $grandTotal, $showTotal
 */
$namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
?>
<?php if ($showHeader): ?>
    <div style="margin-top: 10px;">
        <h3 align="center" style="margin: 0;">REKAP TAGIHAN REKENING AIR</h3>
        <p align="center" style="margin: 0 0 10px 0;">
            <b>PERIODE : <?= esc(($bulan > 0 ? $namaBulan[$bulan] : 'SEMUA BULAN') . ' ' . ($tahun > 0 ? $tahun : '')) ?></b>
        </p>
    </div>
<?php endif; ?>

<div class="table">
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">No. Sambungan</th>
                <th>Pelanggan</th>
                <th width="16%">Kecamatan / Kelurahan</th>
                <th width="6%">Rek</th>
                <th width="6%">Gol</th>
                <th width="8%">Meter Lalu</th>
                <th width="8%">Meter Ini</th>
                <th width="10%">Jumlah</th>
                <th width="8%">Denda</th>
                <th width="10%">Total</th>
                <th width="6%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows) && $showHeader): ?>
                <tr>
                    <td colspan="12" class="text-center">Tidak Ada Data</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="text-center"><?= (int) $r['_no'] ?></td>
                    <td class="text-center"><?= esc($r['no_sambungan'] ?? '-') ?></td>
                    <td><?= esc($r['nama_pelanggan'] ?? '-') ?></td>
                    <td>
                        <?= esc($r['kecamatan'] ?? '-') ?>
                        <?= $r['kelurahan'] ? ' / ' . esc($r['kelurahan']) : '' ?>
                    </td>
                    <td class="text-center"><?= esc($r['no_rek'] ?? '-') ?></td>
                    <td class="text-center"><?= esc($r['kd_jenis'] ?? '-') ?></td>
                    <td class="text-center"><?= esc($r['pemakaian_air_lalu'] ?? '-') ?></td>
                    <td class="text-center"><?= esc($r['pemakaian_air'] ?? '-') ?></td>
                    <td class="text-right"><?= number_format((float) $r['jlh_tagihan'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format((float) $r['denda'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format((float) $r['jlh_tagihan'] + (float) $r['denda'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= ($r['status'] ?? '0') === '1' ? 'Lunas' : 'Tagihan' ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (! empty($showTotal)): ?>
                <tr>
                    <td colspan="10" class="text-right"><b>TOTAL (<?= (int) $jumlahBaris ?> rekening)</b></td>
                    <td class="text-right"><b><?= number_format((float) $grandTotal, 0, ',', '.') ?></b></td>
                    <td></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
