<h4 style="text-align: center; font-weight: bold; text-transform: uppercase;">REKAPAN JURNAL BAYAR KAS<br>BULAN
    <?= $tanggal . ' '. $tahun ;?></h4>

<table border="1" style="width:100%;border-color: black; color: black; border-collapse: collapse">
    <thead>
        <tr>
            <th colspan="2">VOUCHER</th>
            <th rowspan="2">URAIAN</th>
            <th colspan="2">CEK</th>
            <th>KREDIT</th>
            <th colspan="2">DEBET</th>
        </tr>
        <tr>
            <th>NOMOR</th>
            <th>TANGGAL</th>
            <th>NOMOR</th>
            <th>TANGGAL</th>
            <th>KAS BANK</th>
            <th>HUTANG USAHA</th>
            <th>HUTANG LAINNYA</th>
        </tr>
        <tr>
            <?php for($i = 1; $i < 9 ; $i++): ?>
            <td style="text-align: center;background-color:rgb(169, 169, 169);"><?= $i; ?></td>
            <?php endfor; ?>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($result)): ?>
        <?php foreach($result as $row): ?>
        <tr>
            <td><?= $row['NOMOR']; ?></td>
            <td><?= date('d-m-Y', strtotime($row['TANGGAL']));  ?></td>
            <td><?= $row['KETERANGAN']  ?></td>
            <td><?= $row['BANK']  ?></td>
            <td><?= date('d-m-Y', strtotime($row['TANGGAL']));  ?></td>
            <td><?= 'Rp.'.number_format($row['DEBET'], 0, ',', '.');  ?></td>
            <td><?= 'Rp.'.number_format($row['KREDIT'], 0, ',', '.');  ?></td>
            <td><?= 'Rp.'.number_format($row['DEBET'], 0, ',', '.');  ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="8" style="text-align: center;">TIDAK ADA DATA</td>
        </tr>
        <?php endif; ?>
        <tr>
            <td colspan="5" style="text-align: center;background-color:rgb(169, 169, 169);">JUMLAH</td>
            <td style="text-align: center;background-color:rgb(169, 169, 169);">
                <?= 'Rp.'.number_format(array_sum(array_column($result, 'DEBET')), 0, ',', '.'); ?></td>
            <td style="text-align: center;background-color:rgb(169, 169, 169);">0</td>
            <td style="text-align: center;background-color:rgb(169, 169, 169);">
                <?= 'Rp.'.number_format(array_sum(array_column($result, 'DEBET')), 0, ',', '.'); ?></td>
        </tr>
    </tbody>
</table>
<table style="width:100%;border-color: black; color: black;">
    <tbody>
        <tr>
            <td colspan="2" style="text-align: center;">
                Disetujui Oleh
                <br>
                <br>
                <br>
                <br>
                MANTO RAHMOLA, SE.,M.Si<br>
                (Direksi)
            </td>
            <td colspan="2" style="text-align: center;">
                Diketahui Oleh
                <br>
                <br>
                <br>
                <br>
                ...........................<br>
                (________________)
            </td>
            <td colspan="2" style="text-align: center;">
                <br>
                <br>
                <br>
                <br>
                WIWIN AKUBA, SH<br>
                (Kabag Keuangan)
            </td>
        </tr>
    </tbody>
</table>