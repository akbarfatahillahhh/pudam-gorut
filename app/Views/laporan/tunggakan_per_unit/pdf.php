<?php
$baris      = $baris ?? [];
$barisAwal  = $baris_awal ?? false;
$total      = $total ?? [];
$ket        = $keterangan ?? [];
?>
<?php if ($barisAwal): ?>
    <div style="margin-top: 10px;">
        <h3 align="center" style="margin:0;">DAFTAR RINCIAN TUNGGAKAN / PIUTANG</h3>
        <p align="center" style="margin: 4px 0 2px;"><b>TAHUN <?= (int) ($ket['tahun'] ?? 0) ?></b></p>

        <table width="100%" class="fs-2 mb-2" style="border-collapse: collapse;">
            <tr>
                <td width="18%">Unit</td>
                <td width="32%">: <?= esc($ket['unit'] ?? 'SEMUA UNIT') ?></td>
                <td width="18%">Status Bayar</td>
                <td>: <?= esc($ket['status'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Kelurahan/Desa</td>
                <td>: <?= esc($ket['kelurahan'] ?? 'SEMUA KELURAHAN') ?></td>
                <td>Status Pelanggan</td>
                <td>: <?= esc($ket['pelanggan'] ?? 'SEMUA') ?></td>
            </tr>
            <tr>
                <td>Jenis Pelanggan</td>
                <td colspan="3">: <?= esc($ket['jenis'] ?? 'SEMUA JENIS') ?></td>
            </tr>
        </table>
    </div>
<?php endif; ?>

<div class="table">
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">Sambungan</th>
                <th>Nama Pelanggan</th>
                <th width="8%">Rekening</th>
                <th width="11%">Harga Air</th>
                <th width="9%">Bea. Adm</th>
                <th width="11%">Jumlah</th>
                <th width="10%">Bulan Tagihan</th>
                <th width="10%">Ket</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($baris) && $barisAwal): ?>
                <tr>
                    <td colspan="9" class="text-center">Tidak Ada Data</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($baris as $r): ?>
                <tr>
                    <td class="text-center"><?= (int) $r['no'] ?></td>
                    <td class="text-center"><?= esc($r['sambungan']) ?></td>
                    <td><?= esc($r['nama']) ?></td>
                    <td class="text-center"><?= esc($r['rek']) ?></td>
                    <td class="text-right"><?= number_format($r['harga'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($r['admin'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($r['jumlah'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= esc($r['bulan']) ?></td>
                    <td class="text-center"><?= esc($r['jenis']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if (! empty($baris_total)): ?>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><b>Jumlah Total</b></td>
                    <td class="text-right"><b><?= number_format((float) ($total['harga_air'] ?? 0), 0, ',', '.') ?></b></td>
                    <td class="text-right"><b><?= number_format((float) ($total['by_admin'] ?? 0), 0, ',', '.') ?></b></td>
                    <td class="text-right"><b><?= number_format((float) ($total['jlh_tagihan'] ?? 0), 0, ',', '.') ?></b></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>
