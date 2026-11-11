<table border="1" style="width: 100%; border-color: black; color: black;">
    <tbody>
        <tr>
            <td colspan="5" class="text-right">NOMOR :</td>
            <td class="text-center bg-success fw-bold" style="font-size: 15px; color: black;">
                <?= $result[0]['NOMOR']?>
            </td>
        </tr>
        <tr>
            <td class="text-center" style="width: 20%; background-color: #EEEEEE;">BANK</td>
            <td style="width: 15%;" class="text-center fw-bold ">
                <?php $previous = null; foreach($result as $key => $value): ?>
                <?php if(isset($previous) && $previous == $value['BANK']): ?>
                <?php else: ?>
                <?= $value['BANK'] ?>
                <?php endif; ?>
                <?php $previous = $value['BANK']; ?>
                <?php endforeach; ?>
            </td>
            <td class=" text-center" style="width: 15%; background-color: #EEEEEE;">CEK/GIRO NO
            </td>
            <td style="width: 15%;" class="text-center fw-bold">
                <?= $result[0]['NO_GIRO'] ?>
            </td>
            <td class="text-center" style="width: 15%; background-color: #EEEEEE;">TANGGAL</td>
            <td class="text-center fw-bold "><?= date('d-m-Y', strtotime($result[0]['TANGGAL'])) ?></td>
        </tr>
        <tr>
            <td class="text-right" style="background-color: #EEEEEE;">BEBAN BAGIAN DIREKSI :</td>
            <td colspan="5" class="text-uppercase fw-bold"><?= strtoupper($result[0]['BAGIAN_DIREKSI']) ?></td>
        </tr>
        <tr>
            <td class="text-right" style="background-color: #EEEEEE;">BOLEH DIBAYARKAN KEPADA :</td>
            <td colspan="5" class="text-uppercase fw-bold"><?= strtoupper($result[0]['DIBAYAR_KEPADA']) ?></td>
        </tr>
        <tr style="background-color: #EEEEEE">
            <td rowspan="2" colspan="2" class="text-center">NAMA PERKIRAAN</td>
            <td rowspan="2" colspan="2" class="text-center">KODE PERKIRAAN</td>
            <td colspan="2" class="text-center">JUMLAH</td>
        </tr>
        <tr style="background-color: #EEEEEE;">
            <td class="text-center">DEBET</td>
            <td class="text-center">KREDIT</td>
        </tr>
    </tbody>
    <tbody class="tampildetailvoucher">
        <?php foreach($result as $key => $value): ?>
        <tr style="width: 100%">
            <td colspan="2" class="fw-bold">* <?= $value['NAMA_PERKIRAAN_GROUP'] ?></td>
            <td colspan="2" class="text-center"><?= $value['NOMOR_PERKIRAAN_GROUP'] ?></td>
            <td class="text-right"><?= 'Rp ' . number_format($value['DEBET'], 0, ',', '.') ?></td>
            <td class="text-right"><?= 'Rp ' . number_format($value['KREDIT'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>

        <tr>
            <td colspan="4" class="fw-bold text-center">TOTAL</td>
            <td colspan="2" class="text-right fw-bold">
                <?= 'Rp ' .number_format($result[0]['DEBET'], 0, ',', '.')  ?>
            </td>
        </tr>
    </tbody>
    <tbody>
        <tr style="background-color: #FAEEE7;">
            <td colspan="6">TERBILANG: <font style="font-family: Courier;">
                    <?= terbilang(array_sum(array_column($result, 'DEBET')) ) ?>
                </font>
            </td>
        </tr>
        <tr style="background-color: #FAEEE7;">
            <td colspan="6">PENJELASAN SINGKAT:<br>
                <ul style="list-style: none; text-decoration: none">
                    <?php foreach($result as $key => $value): ?>
                    <li>
                        <font style="font-family: Courier;">
                            <?php
                            
                            if (isset($kets) && $kets == $value['KETERANGAN']) {
                                // tidak tampilkan
                            } else {
                                echo $value['KETERANGAN'];
                     
                            }
                            ?>
                        </font>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </td>
        </tr>
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