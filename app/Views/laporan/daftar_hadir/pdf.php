<?php
/**
 * Badan HTML untuk mPDF: Daftar Hadir Karyawan.
 *
 * @var array $report hasil Laporan\DaftarHadir::susunLaporan()
 */
$hari = $report['hari'];
?>
<div style="margin-top: 5px;">
    <h4 style="text-align: center; margin: 0;">DAFTAR HADIR KARYAWAN</h4>
    <table style="width: 100%; font-size: 11px; margin-bottom: 6px;">
        <tr>
            <td width="20%">PERIODE</td>
            <td>: <b><?= strtoupper($report['bulanNama']) . ' ' . $report['tahun'] ?></b></td>
        </tr>
        <tr>
            <td>UNIT KERJA</td>
            <td>: <b><?= esc(strtoupper($report['namaUnit'])) ?></b></td>
        </tr>
    </table>

    <table border="1" style="width: 100%; border-collapse: collapse; border-color: #000; font-size: 7px;">
        <thead>
            <tr>
                <th rowspan="2" width="2%" style="font-size: 7px;">NO</th>
                <th rowspan="2" width="12%" style="font-size: 7px;">NAMA</th>
                <?php foreach ($hari as $h) : ?>
                    <?php $bgKepala = $h['weekend'] ? 'background-color:#f5b7b1;' : ''; ?>
                    <th colspan="2" style="font-size: 6px; <?= $bgKepala ?>"><?= (int) date('j', strtotime($h['tanggal'])) ?></th>
                <?php endforeach; ?>
                <th rowspan="2" width="5%" style="font-size: 6px;">HADIR<br>DATANG</th>
                <th rowspan="2" width="5%" style="font-size: 6px;">HADIR<br>PULANG</th>
                <th rowspan="2" width="5%" style="font-size: 6px;">TERLAMBAT</th>
                <th rowspan="2" width="4%" style="font-size: 6px;">ABSEN</th>
            </tr>
            <tr>
                <?php foreach ($hari as $h) : ?>
                    <?php $bgKepala = $h['weekend'] ? 'background-color:#f5b7b1;' : ''; ?>
                    <th style="font-size: 6px; <?= $bgKepala ?>">P</th>
                    <th style="font-size: 6px; <?= $bgKepala ?>">S</th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($report['pegawai'])) : ?>
                <tr>
                    <td colspan="<?= 2 + (count($hari) * 2) + 4 ?>" style="text-align: center;">Tidak ada pegawai aktif pada unit ini</td>
                </tr>
            <?php else : ?>
                <?php foreach ($report['pegawai'] as $p) : ?>
                    <tr>
                        <td style="text-align: center; font-size: 7px;"><?= $p['no'] ?></td>
                        <td style="text-align: left; font-size: 7px;"><?= esc(strtoupper($p['nama'])) ?></td>
                        <?php foreach ($hari as $i => $h) : ?>
                            <?php
                            $pasangan = [
                                'datang' => $p['datang'][$i],
                                'pulang' => $p['pulang'][$i],
                            ];
                            foreach ($pasangan as $nilai) :
                                $bg = '#ffffff';
                                if ($nilai === 'H') {
                                    $bg = '#3ec70b';
                                } elseif ($nilai === 'T') {
                                    $bg = '#ffde02';
                                } elseif ($nilai === '--' && $h['weekend']) {
                                    $bg = '#f5b7b1';
                                } elseif ($nilai === '--') {
                                    $bg = '#fdecea';
                                }
                                ?>
                                <td style="text-align: center; font-size: 6px; background-color: <?= $bg ?>;"><?= $nilai ?></td>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <td style="text-align: center; font-size: 7px;"><?= $p['totalDatang'] ?></td>
                        <td style="text-align: center; font-size: 7px;"><?= $p['totalPulang'] ?></td>
                        <td style="text-align: center; font-size: 7px;"><?= $p['totalTelat'] ?></td>
                        <td style="text-align: center; font-size: 7px;"><?= $p['totalAbsen'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="font-size: 9px; margin-top: 6px;">
        <b>Catatan:</b>
        <b style="color: green;">P</b> = Datang,
        <b style="color: red;">S</b> = Pulang,
        H = Hadir, T = Terlambat, -- = Tidak Hadir.
    </p>
</div>
