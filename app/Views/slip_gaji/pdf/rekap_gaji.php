<?php
/**
 * Badan HTML untuk mPDF: rekap gaji karyawan satu bulan.
 *
 * Satu kolom per jenis tunjangan/potongan, mengikuti bentuk cetakan aplikasi
 * lama. Nominal potongan ditampilkan apa adanya dari gaji_t_pegawai
 * (versi lama mengalikannya lagi dengan nominal master sehingga total potongan
 * membengkak).
 *
 * @var string      $judul
 * @var array       $result    baris gaji_t_pegawai + pegawai/jabatan/jenis
 * @var array       $tunjangan master gaji_t_tunjangan
 * @var array       $potongan  master gaji_t_potongan
 * @var string|null $status    status bulan (Y = final, P = draft)
 */
$totalPerTunjangan = array_fill(0, count($tunjangan), 0);
$totalPerPotongan  = array_fill(0, count($potongan), 0);
$totalSemua        = 0;
?>
<div style="margin-top: 5px;">
    <h4 style="text-align: center; margin: 0 0 4px 0;"><?= $judul ?></h4>
    <p style="text-align: center; margin: 0 0 6px 0;">
        STATUS : <?= $status === 'Y' ? 'FINAL' : ($status === 'P' ? 'DRAFT' : 'BELUM DISIMPAN') ?>
    </p>

    <table border="1" style="width: 100%; border-collapse: collapse; border-color: #000;">
        <thead>
            <tr>
                <th rowspan="2" width="2%">NO</th>
                <th rowspan="2" width="14%">NAMA</th>
                <th rowspan="2" width="3%">L/P</th>
                <th rowspan="2" width="4%">GOL</th>
                <th rowspan="2" width="11%">JABATAN</th>
                <th rowspan="2" width="8%">TUNJ. JABATAN</th>
                <th colspan="<?= max(1, count($tunjangan)) ?>">TUNJANGAN</th>
                <th colspan="<?= max(1, count($potongan)) ?>">POTONGAN</th>
                <th rowspan="2" width="8%">JUMLAH</th>
            </tr>
            <tr>
                <?php if (empty($tunjangan)) : ?>
                    <th>-</th>
                <?php else : ?>
                    <?php foreach ($tunjangan as $t) : ?>
                        <th><?= esc($t['jenis_tunjangan']) ?></th>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (empty($potongan)) : ?>
                    <th>-</th>
                <?php else : ?>
                    <?php foreach ($potongan as $p) : ?>
                        <th><?= esc($p['jenis_potongan']) ?></th>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($result)) : ?>
                <tr>
                    <td colspan="<?= 7 + max(1, count($tunjangan)) + max(1, count($potongan)) ?>"
                        style="text-align: center;">Tidak ada data gaji pada bulan ini</td>
                </tr>
            <?php else : ?>
                <?php $no = 1; ?>
                <?php foreach ($result as $row) : ?>
                    <?php
                    $totalTunjanganPegawai = 0;
                    $totalPotonganPegawai  = 0;
                    ?>
                    <tr>
                        <td style="text-align: center; font-size: 8px;"><?= $no++ ?></td>
                        <td style="font-size: 8px;"><?= esc($row['nama_pegawai'] ?? '-') ?></td>
                        <td style="text-align: center; font-size: 8px;"><?= esc($row['jk'] ?? '-') ?></td>
                        <td style="text-align: center; font-size: 8px;"><?= esc($row['gol'] ?? '-') ?></td>
                        <td style="font-size: 8px;"><?= esc($row['nama_jabatan'] ?? '-') ?></td>
                        <td style="text-align: right; font-size: 8px;">
                            <?= number_format((int) ($row['tunjangan_jabatan'] ?? 0), 0, ',', '.') ?>
                        </td>

                        <?php if (empty($tunjangan)) : ?>
                            <td style="text-align: right; font-size: 8px;">0</td>
                        <?php else : ?>
                            <?php foreach ($tunjangan as $index => $t) : ?>
                                <?php
                                $cocok = !empty($row['id_tunjangan']) && (int) $row['id_tunjangan'] === (int) $t['id'];
                                $qty   = $cocok ? (int) ($row['qty_tunjangan'] ?? 0) : 0;
                                $total = $qty * (int) $t['jumlah'];

                                $totalTunjanganPegawai      += $total;
                                $totalPerTunjangan[$index]  += $total;
                                ?>
                                <td style="text-align: right; font-size: 8px;">
                                    <?= $qty > 0
                                        ? number_format($qty, 0, ',', '.') . ' x ' . number_format((int) $t['jumlah'], 0, ',', '.') . ' = ' . number_format($total, 0, ',', '.')
                                        : '-' ?>
                                </td>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (empty($potongan)) : ?>
                            <td style="text-align: right; font-size: 8px;">0</td>
                        <?php else : ?>
                            <?php foreach ($potongan as $index => $p) : ?>
                                <?php
                                $cocok = !empty($row['id_potongan']) && (int) $row['id_potongan'] === (int) $p['id'];
                                $total = $cocok ? (int) ($row['total_potongan'] ?? 0) : 0;

                                $totalPotonganPegawai      += $total;
                                $totalPerPotongan[$index]  += $total;
                                ?>
                                <td style="text-align: right; font-size: 8px;">
                                    <?= $total > 0 ? number_format($total, 0, ',', '.') : '-' ?>
                                </td>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php $jumlahBaris = $totalTunjanganPegawai - $totalPotonganPegawai; ?>
                        <?php $totalSemua += $jumlahBaris; ?>
                        <td style="text-align: right; font-size: 8px;"><b><?= number_format($jumlahBaris, 0, ',', '.') ?></b></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: center; background-color: #a9a9a9;"><b>JUMLAH</b></td>
                <?php foreach ($totalPerTunjangan as $totalKolom) : ?>
                    <td style="text-align: right; background-color: #a9a9a9;"><b><?= number_format($totalKolom, 0, ',', '.') ?></b></td>
                <?php endforeach; ?>
                <?php if (empty($tunjangan)) : ?>
                    <td style="background-color: #a9a9a9;">0</td>
                <?php endif; ?>

                <?php foreach ($totalPerPotongan as $totalKolom) : ?>
                    <td style="text-align: right; background-color: #a9a9a9;"><b><?= number_format($totalKolom, 0, ',', '.') ?></b></td>
                <?php endforeach; ?>
                <?php if (empty($potongan)) : ?>
                    <td style="background-color: #a9a9a9;">0</td>
                <?php endif; ?>

                <td style="text-align: right; background-color: #a9a9a9;"><b><?= number_format($totalSemua, 0, ',', '.') ?></b></td>
            </tr>
        </tfoot>
    </table>

    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td colspan="2" style="text-align: center;">
                Disetujui Oleh
                <br><br><br><br>
                MANTO RAHMOLA, SE.,M.Si<br>
                (Direksi)
            </td>
            <td colspan="2" style="text-align: center;">
                Diketahui Oleh
                <br><br><br><br>
                ...........................<br>
                (________________)
            </td>
            <td colspan="2" style="text-align: center;">
                <br><br><br><br>
                WIWIN AKUBA, SH<br>
                (Kabag Keuangan)
            </td>
        </tr>
    </table>
</div>
