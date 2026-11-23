<div class="p-3 bg-white text-black">
    <table class="w-100 mb-2" style="border-collapse: collapse;">
        <tr>
            <td rowspan="3" width="14%" class="text-center" style="vertical-align: middle;">
                <img src="<?= base_url('logo/perumda-gorut.png') ?>" width="80" alt="PERUMDA">
            </td>
            <td colspan="3" class="fw-bold fs-3">
                PERUSAHAAN UMUM DAERAH (PERUMDA)<br>
                TIRTA GERBANG EMAS KAB. GORONTALO UTARA
            </td>
            <td class="text-center fs-2" style="border: 1px solid #000; padding: 4px;">
                <b>REKENING PEMBAYARAN AIR MINUM</b><br>
                Nomor : <?= esc($row['no_rek'] ?? '-') ?>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="fs-2">Jl. Kasmat Lahay Titidu-Kwandang, Telp. (0442) 310 007</td>
        </tr>
        <tr>
            <td colspan="3" class="text-end fs-2">
                <?php if (($row['status'] ?? '0') === '1'): ?>
                    <span class="badge bg-success">LUNAS</span>
                <?php else: ?>
                    <span class="badge bg-danger">TAGIHAN</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <table class="w-100 mb-2 fs-2" style="border-collapse: collapse;">
        <tr>
            <td width="12%">NAMA</td>
            <td width="28%">: <?= esc($row['nama_pelanggan'] ?? '-') ?></td>
            <td width="10%">PERIODE</td>
            <td width="22%">: <?= esc($row['bulan_tagihan_text'] ?? '-') ?></td>
            <td width="12%">GOLONGAN</td>
            <td>: <?= esc($row['kd_jenis'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>NO. SAMB</td>
            <td>: <?= esc($row['no_sambungan'] ?? '-') ?></td>
            <td>WILAYAH</td>
            <td>: <?= esc($row['kecamatan'] ?? '-') ?></td>
            <td>KELOMPOK</td>
            <td>: <?= esc($row['kelompok_jasa'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>ALAMAT</td>
            <td colspan="5">
                : Kab. <?= esc($row['kabupaten'] ?? '-') ?>,
                Kec. <?= esc($row['kecamatan'] ?? '-') ?>,
                Desa/Kel. <?= esc($row['kelurahan'] ?? '-') ?>
            </td>
        </tr>
    </table>

    <table class="w-100 fs-2 mb-2" style="border-collapse: collapse; border: 1px solid #000;">
        <thead>
            <tr class="bg-light text-center">
                <th colspan="2" style="border: 1px solid #000; padding: 4px;">Angka Meter</th>
                <th rowspan="2" style="border: 1px solid #000; padding: 4px;">Pemakaian</th>
                <th colspan="2" style="border: 1px solid #000; padding: 4px;">Harga Air</th>
                <th rowspan="2" style="border: 1px solid #000; padding: 4px;">Biaya Adm.</th>
                <th rowspan="2" style="border: 1px solid #000; padding: 4px;">Jumlah Tagihan</th>
            </tr>
            <tr class="bg-light text-center">
                <th style="border: 1px solid #000; padding: 4px;">Bulan Ini</th>
                <th style="border: 1px solid #000; padding: 4px;">Bulan Lalu</th>
                <th style="border: 1px solid #000; padding: 4px;">0-10 M<sup>3</sup></th>
                <th style="border: 1px solid #000; padding: 4px;">&gt;10 M<sup>3</sup></th>
            </tr>
            <tr class="bg-light text-center">
                <th style="border: 1px solid #000; padding: 4px;">M<sup>3</sup></th>
                <th style="border: 1px solid #000; padding: 4px;">M<sup>3</sup></th>
                <th style="border: 1px solid #000; padding: 4px;">M<sup>3</sup></th>
                <th style="border: 1px solid #000; padding: 4px;">(Rp)</th>
                <th style="border: 1px solid #000; padding: 4px;">(Rp)</th>
                <th style="border: 1px solid #000; padding: 4px;">(Rp)</th>
                <th style="border: 1px solid #000; padding: 4px;">(Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="text-center">
                <td style="border: 1px solid #000; padding: 4px;"><?= esc($row['pemakaian_air'] ?? '-') ?></td>
                <td style="border: 1px solid #000; padding: 4px;"><?= esc($row['pemakaian_air_lalu'] ?? '-') ?></td>
                <td style="border: 1px solid #000; padding: 4px;">
                    <?php
                    $ini  = (float) ($row['pemakaian_air'] ?? 0);
                    $lalu = (float) ($row['pemakaian_air_lalu'] ?? 0);
                    echo max(0, $ini - $lalu);
                    ?>
                </td>
                <td style="border: 1px solid #000; padding: 4px;"><?= number_format((float) ($row['tag_dibawah_10'] ?? 0), 0, ',', '.') ?></td>
                <td style="border: 1px solid #000; padding: 4px;"><?= number_format((float) ($row['tag_diatas_10'] ?? 0), 0, ',', '.') ?></td>
                <td style="border: 1px solid #000; padding: 4px;"><?= number_format((float) ($row['by_admin'] ?? 0), 0, ',', '.') ?></td>
                <td style="border: 1px solid #000; padding: 4px; font-weight: bold;"><?= number_format((float) ($row['jlh_tagihan'] ?? 0), 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td colspan="7" style="border: 1px solid #000; padding: 4px;">
                    TERBILANG : <?= esc($row['terbilang'] ?? '-') ?>
                </td>
            </tr>
            <?php if ((float) ($row['denda'] ?? 0) > 0): ?>
                <tr>
                    <td colspan="6" class="text-end" style="border: 1px solid #000; padding: 4px;">Denda</td>
                    <td class="text-center" style="border: 1px solid #000; padding: 4px;">
                        <?= number_format((float) $row['denda'], 0, ',', '.') ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if ((float) ($row['bayar'] ?? 0) > 0): ?>
                <tr>
                    <td colspan="6" class="text-end fw-bold" style="border: 1px solid #000; padding: 4px;">Dibayar</td>
                    <td class="text-center fw-bold" style="border: 1px solid #000; padding: 4px;">
                        <?= number_format((float) $row['bayar'], 0, ',', '.') ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="w-100 fs-2">
        <tr>
            <td>
                Tanggal Cetak:
                <?= $row['tgl_bayar'] ? date('d/m/Y H:i', strtotime($row['tgl_bayar'])) : date('d/m/Y') ?>
                <?php if (! empty($row['nama_kasir'])): ?>
                    <br>Kasir: <?= esc($row['nama_kasir']) ?>
                <?php endif; ?>
            </td>
            <td class="text-center" width="30%">
                Direktur,
                <br><br><br><br>
                <b>(......................................)</b>
            </td>
        </tr>
    </table>
</div>
