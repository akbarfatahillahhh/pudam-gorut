<?php
$baris      = $baris ?? [];
$barisAwal  = $baris_awal ?? false;
$keterangan = $keterangan ?? [];
?>
<?php if ($barisAwal): ?>
    <div style="margin-top: 10px;">
        <h3 align="center" style="margin:0;">LAPORAN DAFTAR REKENING DITAGIH (DRD) AIR</h3>
        <p align="center" style="margin: 0 0 2px;"><b>PERIODE : <?= esc($keterangan['periode'] ?? '-') ?></b></p>

        <?php if (! empty($keterangan['judul']) && ! empty($keterangan['nilai'])): ?>
            <p align="center" style="margin: 0 0 2px;">
                <b><?= esc($keterangan['judul']) ?> : <?= esc($keterangan['nilai']) ?></b>
            </p>
        <?php endif; ?>

        <?php if (! empty($keterangan['status'])): ?>
            <p align="center" style="margin: 0 0 10px;"><b><?= esc($keterangan['status']) ?></b></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="table">
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">No. Sambungan</th>
                <th>Pelanggan</th>
                <th width="8%">Rekening</th>
                <th width="11%">Harga Air</th>
                <th width="9%">Bea. Admin</th>
                <th width="11%">Jumlah</th>
                <th width="6%">PMK</th>
                <th width="11%">Bulan Tagihan</th>
                <th width="10%">Ket</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($baris) && $barisAwal): ?>
                <tr>
                    <td colspan="10" class="text-center">Tidak Ada Data</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($baris as $r): ?>
                <?php if (! empty($r['judul'])): ?>
                    <tr>
                        <td colspan="10" class="text-center fw-bold" style="background-color:#ffd99f;">
                            <?= esc($r['judul']) ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <td class="text-center"><?= (int) $r['no'] ?></td>
                    <td class="text-center"><?= esc($r['sambungan']) ?></td>
                    <td><?= esc($r['nama']) ?></td>
                    <td class="text-center"><?= esc($r['rek']) ?></td>
                    <td class="text-right"><?= number_format($r['harga'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($r['admin'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($r['jumlah'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= (int) $r['pmk'] ?></td>
                    <td class="text-center"><?= esc($r['bulan']) ?></td>
                    <td class="text-center"><?= esc($r['ket']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
