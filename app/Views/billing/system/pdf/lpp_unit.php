<div style="margin-top:10px;">
    <h3 align="center" style="margin:0;">REKAPITULASI LPP AIR PER UNIT</h3>
    <p align="center" style="margin:4px 0 2px;"><b>PERIODE : <?= esc($periode) ?></b></p>
    <p align="center" style="margin:0 0 10px;">
        <b>UNIT : <?= esc($unit) ?> &mdash; <?= esc($loket) ?></b>
    </p>

    <div class="table">
        <table>
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="22%">Unit</th>
                    <th>Kelompok Jasa</th>
                    <th width="10%">Rekening</th>
                    <th width="16%">Harga Air</th>
                    <th width="14%">Biaya Admin</th>
                    <th width="16%">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($baris)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak Ada Data</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($baris as $i => $r): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td><?= esc($r['unit']) ?></td>
                            <td><?= esc($r['kelompok']) ?></td>
                            <td class="text-center"><?= number_format($r['rekening'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($r['harga_air'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($r['by_admin'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($r['jumlah'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-center"><b>Jumlah Total</b></td>
                        <td class="text-center"><b><?= number_format($total['rekening'], 0, ',', '.') ?></b></td>
                        <td class="text-right"><b><?= number_format($total['harga_air'], 0, ',', '.') ?></b></td>
                        <td class="text-right"><b><?= number_format($total['by_admin'], 0, ',', '.') ?></b></td>
                        <td class="text-right"><b><?= number_format($total['jumlah'], 0, ',', '.') ?></b></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
