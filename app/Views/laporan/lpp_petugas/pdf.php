<?php
$petugas = $petugas ?? [];
$namaPetugas = strtoupper($petugas['nama_lengkap'] ?? '-');
$namaUnit    = $petugas['kecamatan'] ?? 'SEMUA UNIT';
?>
<div style="margin-top: 10px;">
    <h3 align="center" style="margin:0;">LAPORAN REKAP DRD / PETUGAS</h3>
    <p align="center" style="margin: 6px 0 10px;">
        <b><?= esc($namaPetugas) ?></b> &mdash; UNIT <?= esc(strtoupper($namaUnit)) ?>
    </p>

    <table width="100%" class="fs-2 mb-2" style="border-collapse: collapse;">
        <tr>
            <td width="20%">Tanggal Penagihan</td>
            <td>: <?= esc($tgl_awal) ?> s/d <?= esc($tgl_akhir) ?></td>
        </tr>
    </table>

    <div class="table">
        <table>
            <thead>
                <tr>
                    <th width="4%" rowspan="2">No</th>
                    <th rowspan="2">Jenis Pelanggan</th>
                    <th width="8%" rowspan="2">Terpasang</th>
                    <th width="8%" rowspan="2">Ditagih</th>
                    <th colspan="3">Kubikasi (M3)</th>
                    <th width="12%" rowspan="2">Harga Air</th>
                    <th width="10%" rowspan="2">Biaya Adm</th>
                    <th width="12%" rowspan="2">Jumlah</th>
                </tr>
                <tr>
                    <th width="7%">&le; 10</th>
                    <th width="7%">&gt; 10</th>
                    <th width="7%">Total</th>
                </tr>
                <tr style="font-style:italic;">
                    <td class="text-center">1</td>
                    <td class="text-center">2</td>
                    <td class="text-center">3</td>
                    <td class="text-center">4</td>
                    <td class="text-center">5</td>
                    <td class="text-center">6</td>
                    <td class="text-center">7</td>
                    <td class="text-center">8</td>
                    <td class="text-center">9</td>
                    <td class="text-center">10</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($baris)): ?>
                    <tr>
                        <td colspan="10" class="text-center">Tidak Ada Data</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($baris as $i => $b): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td><?= esc($b['jenis']) ?></td>
                            <td class="text-center"><?= number_format($b['terpasang'], 0, ',', '.') ?></td>
                            <td class="text-center"><?= number_format($b['rekening'], 0, ',', '.') ?></td>
                            <td class="text-center"><?= number_format($b['kub_bawah'], 0, ',', '.') ?></td>
                            <td class="text-center"><?= number_format($b['kub_atas'], 0, ',', '.') ?></td>
                            <td class="text-center"><?= number_format($b['kubikasi'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($b['harga_air'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($b['by_admin'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($b['jlh_tagihan'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2" class="text-center"><b>Jumlah Total</b></td>
                        <td class="text-center"><b><?= number_format($total['terpasang'], 0, ',', '.') ?></b></td>
                        <td class="text-center"><b><?= number_format($total['rekening'], 0, ',', '.') ?></b></td>
                        <td class="text-center"><b><?= number_format($total['kub_bawah'], 0, ',', '.') ?></b></td>
                        <td class="text-center"><b><?= number_format($total['kub_atas'], 0, ',', '.') ?></b></td>
                        <td class="text-center"><b><?= number_format($total['kubikasi'], 0, ',', '.') ?></b></td>
                        <td class="text-right"><b><?= number_format($total['harga_air'], 0, ',', '.') ?></b></td>
                        <td class="text-right"><b><?= number_format($total['by_admin'], 0, ',', '.') ?></b></td>
                        <td class="text-right"><b><?= number_format($total['jlh_tagihan'], 0, ',', '.') ?></b></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
