<h4 style="text-align: center; font-weight: bold; text-transform: uppercase;">DAFTAR HUTANG YANG HARUS DI BAYAR
    <br>BULAN
    <?= $tanggal . ' '. $tahun ;?>
</h4>

<table border="1" style="width:100%;border-color: black; color: black; border-collapse: collapse">
    <thead>
        <thead>
            <tr>
                <th colspan="2">Voucher</th>
                <th colspan="2">Bayar</th>
                <th rowspan="2">Perkiraan</th>
                <th rowspan="2">Nomor</th>
                <th rowspan="2">Uraian</th>
                <th colspan="2">Kredit</th>
                <th rowspan="2">Debet</th>
            </tr>
            <tr>
                <th>Tanggal</th>
                <th>Nomor</th>
                <th>Tanggal</th>
                <th>Cek</th>
                <th>Hut Usaha</th>
                <th>Hut Lain Lain</th>
            </tr>
            <tr>
                <?php for($i = 1; $i < 11 ; $i++): ?>
                <td style="text-align: center;background-color:rgb(169, 169, 169);"><?= $i; ?></td>
                <?php endfor; ?>
            </tr>
        </thead>
    </thead>
    <tbody>
        <?php
        $group = "";
        foreach ($result as $key => $value) {
            if ($group != $value['GROUP_PERKIRAAN']) {
                $group = $value['GROUP_PERKIRAAN'];
                echo '<tr>
                <td colspan="10" style="text-align: center;background-color:rgb(169, 169, 169);">' . $value['GROUP_PERKIRAAN'] . '</td>
                </tr>';
            }
         echo '<tr>
            <td>'.$value['TGL'].'</td>
            <td>'.$value['NMR'].'</td>
            <td>'.$value['TGL'].'</td>
            <td>'.$value['BANK'].'</td>
            <td>'.$value['NAMA_PERKIRAAN'].'</td>
            <td>'.$value['NOMOR_PERKIRAAN'].'</td>
            <td>'.$value['KETERANGAN'].'</td>
            <td> Rp.'.number_format($value['KREDIT'], 0, ',', '.').'</td>
            <td> Rp.'.number_format($value['DEBET'], 0, ',', '.').'</td>
            <td> Rp.'.number_format($value['DEBET'], 0, ',', '.').'</td>
         </tr>';
        }
        ?>
        <tr>
            <td colspan="8" style="text-align: center;background-color:rgb(169, 169, 169);">JUMLAH</td>
            <td style="text-align: center;background-color:rgb(169, 169, 169);">
                <?= 'Rp.'.number_format(array_sum(array_column($result, 'DEBET')), 0, ',', '.'); ?></td>
            <td style="text-align: center;background-color:rgb(169, 169, 169);">
                <?= 'Rp.'.number_format(array_sum(array_column($result, 'DEBET')), 0, ',', '.'); ?></td>
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