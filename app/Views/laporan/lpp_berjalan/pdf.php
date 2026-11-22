<?php
$kel = $kelurahan ?? [];
$namaKel = $kel['kelurahan'] ?? '-';
$namaKec = $kel['kecamatan'] ?? '-';
$kodeKel = $kel['kode_kelurahan'] ?? '......';
?>
<div style="margin-top: 10px;">
    <table width="100%" style="border-collapse: collapse;">
        <tr>
            <td colspan="2" rowspan="3" width="18%" class="text-left">
                <img src="<?= base_url('logo/perumda-gorut.png') ?>" width="90" alt="PERUMDA">
            </td>
            <td class="fs-2 fw-bold">PERUSAHAAN UMUM DAERAH AIR MINUM</td>
        </tr>
        <tr>
            <td class="fs-2 fw-bold">TIRTA GERBANG EMAS</td>
        </tr>
        <tr>
            <td class="fs-3">KABUPATEN GORONTALO UTARA</td>
        </tr>
        <tr>
            <td colspan="3" class="fs-3">
                Jl. Kasmat Lahay Titidu - Kwandang No. 17, Telp. (0442) 310077, Kode Pos 96252
            </td>
        </tr>
    </table>

    <hr>

    <table width="100%" class="fs-2" style="border-collapse: collapse;">
        <tr>
            <td width="18%">Nomor Laporan</td>
            <td width="32%">: ....../LPP.A/<?= esc($kodeKel) ?>/<?= (int) $tahun ?></td>
            <td width="18%">Desa/Kelurahan</td>
            <td>: <?= esc($namaKel) ?></td>
        </tr>
        <tr>
            <td>Periode Laporan</td>
            <td>: <?= esc($periode) ?></td>
            <td>Kecamatan</td>
            <td>: <?= esc($namaKec) ?></td>
        </tr>
    </table>

    <h3 align="center" style="margin: 12px 0 2px;">LAPORAN PENERIMAAN PENAGIH (LPP-AIR)</h3>
    <p align="center" style="margin: 0 0 10px;"><b>LPP AIR PERIODIK</b></p>

    <div class="table">
        <table>
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="12%">No. Sambungan</th>
                    <th>Nama Pelanggan</th>
                    <th width="8%">Rekening</th>
                    <th width="12%">Bulan Tagihan</th>
                    <th width="12%">Harga Air (Rp)</th>
                    <th width="10%">Biaya Adm (Rp)</th>
                    <th width="12%">Jumlah Tagihan (Rp)</th>
                    <th width="8%">Pakai (M3)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Tidak Ada Data</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="text-center"><?= (int) $r['no'] ?></td>
                            <td class="text-center"><?= esc($r['no_sambungan'] ?? '-') ?></td>
                            <td><?= esc($r['nama_pelanggan'] ?? '-') ?></td>
                            <td class="text-center"><?= esc($r['no_rek'] ?? '-') ?></td>
                            <td class="text-center"><?= esc($r['bulan_text']) ?></td>
                            <td class="text-right"><?= number_format((float) $r['harga_air'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format((float) $r['by_admin'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format((float) $r['jlh_tagihan'], 0, ',', '.') ?></td>
                            <td class="text-center"><?= (int) $r['pemakaian'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="5" class="text-right"><b>Jumlah Total</b></td>
                        <td class="text-right"><b><?= number_format((float) $total['harga_air'], 0, ',', '.') ?></b></td>
                        <td class="text-right"><b><?= number_format((float) $total['by_admin'], 0, ',', '.') ?></b></td>
                        <td class="text-right"><b><?= number_format((float) $total['jlh_tagihan'], 0, ',', '.') ?></b></td>
                        <td class="text-center"><b><?= (int) $total['pemakaian'] ?></b></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
