<h4 style="text-align: center; font-weight: bold; text-transform: uppercase;">REKAPAN JURNAL BAYAR KAS<br>BULAN
    <?= $tanggal . ' '. $tahun ;?></h4>

<table border="1" style="width:100%;border-color: black; color: black; border-collapse: collapse">
    <thead>
        <tr>
            <th rowspan="2">NOMOR</th>
            <th rowspan="2">URAIAN</th>
            <th rowspan="2">NO PERKIRAAN</th>
            <th colspan="2">SUB BUKU BESAR</th>
            <th colspan="2">BUKU BESAR</th>
        </tr>
        <tr>
            <th>DEBET</th>
            <th>KREDIT</th>
            <th>DEBET</th>
            <th>KREDIT</th>
        </tr>
        <tr>
            <?php for($i = 1; $i < 8 ; $i++): ?>
            <td style="text-align: center;background-color:rgb(169, 169, 169);"><?= $i; ?></td>
            <?php endfor; ?>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($result)): ?>
        <?php $i = 1;
        
            foreach($result as $key => $row):?>
        <tr>
            <td><?= $i++?></td>
            <td><?= $row['GROUP_PERKIRAAN']; ?></td>
            <td><?= $row['NO_PERKIRAAN']; ?></td>
            <td><?= 0 ?></td>
            <td><?= 0 ?></td>
            <?php if($row['NORMAL'] == 'Debet'): ?>
            <td><?= 'Rp.'.number_format($row['DEBET'], 0, ',', '.');  ?></td>
            <td><?= 'Rp.'.number_format($row['KREDIT'], 0, ',', '.');  ?></td>
            <?php else: ?>
            <td><?= 'Rp.'.number_format($row['KREDIT'], 0, ',', '.');  ?></td>
            <td><?= 'Rp.'.number_format($row['DEBET'], 0, ',', '.');  ?></td>
            <?php endif; ?>
            <?php endforeach; ?> <?php endif; ?>
        </tr>
        <tr>
            <td colspan="3">JUMLAH</td>
            <td>0</td>
            <td>0</td>
            <?php if(!empty($result)): ?>
            <?php if($result[0]['NORMAL'] == 'Debet'): ?>
            <td><?= 'Rp.'.number_format(array_sum(array_column($result, 'DEBET')), 0, ',', '.'); ?></td>
            <td><?= 'Rp.'.number_format(array_sum(array_column($result, 'KREDIT')), 0, ',', '.'); ?></td>
            <?php else: ?>
            <td><?= 'Rp.'.number_format(array_sum(array_column($result, 'KREDIT')), 0, ',', '.'); ?></td>
            <td><?= 'Rp.'.number_format(array_sum(array_column($result, 'DEBET')), 0, ',', '.'); ?></td>
            <?php endif; ?>
            <?php else : ?>
            <td>0</td>
            <td>0</td>
            <?php endif; ?>

        </tr>
    </tbody>
</table>
<table style=" width:100%;border-color: black; color: black;">
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