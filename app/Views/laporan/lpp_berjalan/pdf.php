<?php
$kel        = $kelurahan ?? [];
$baris      = $baris ?? [];
$barisAwal  = $baris_awal ?? false;
$total      = $total ?? [];
$namaKel    = $kel['kelurahan'] ?? '-';
$namaKec    = $kel['kecamatan'] ?? '-';
$kodeKel    = $kel['kode_kelurahan'] ?? '......';
?>
<?php if ($barisAwal): ?>
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
    </div>
<?php endif; ?>

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
                    <td class="text-center"><?= esc($r['bulan']) ?></td>
                    <td class="text-right"><?= number_format($r['harga_air'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($r['by_admin'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($r['jlh_tagihan'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= (int) $r['pemakaian'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if (! empty($baris_total)): ?>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right"><b>Jumlah Total</b></td>
                    <td class="text-right"><b><?= number_format((float) ($total['harga_air'] ?? 0), 0, ',', '.') ?></b></td>
                    <td class="text-right"><b><?= number_format((float) ($total['by_admin'] ?? 0), 0, ',', '.') ?></b></td>
                    <td class="text-right"><b><?= number_format((float) ($total['jlh_tagihan'] ?? 0), 0, ',', '.') ?></b></td>
                    <td class="text-center"><b><?= (int) ($total['pemakaian'] ?? 0) ?></b></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>
