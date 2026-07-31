<hr class="garis-2" style="border-color: black;">
<center style="color: black;">
    <br>
    <h5 style="text-align: center; font-weight: bold;" id="karajaFont">KWITANSI [NO. <?= $result[0]['NOMOR'] ?>]</h5>
    <br>
</center>
<table border="1"
    style="width: 100%; border-color: black; color: black; font-family: 'Courier New', Courier, monospace;">
    <tbody>
        <tr>
            <td rowspan=" 5" style="width: 10%;">
                <center>
                    SETUJU DIBAYAR
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <font style="font-weight: bold;">MANTO RAHMOLA, SE.,M.Si</font><br>
                    Direktur
                </center>
            </td>
            <td style="background-color: #DADDFC; text-align: right;">Sudah Diterima Dari :</td>
            <td style="padding-left: 3px;">Perumda Gorontalo Utara</td>
        </tr>
        <tr>
            <td style="background-color: #DADDFC; width: 15%; text-align: right;">Jumlah Uang :</td>
            <td style="width: 50%; padding-left: 3px;">
                <?= 'Rp ' . number_format($result[0]['DEBET'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td style="height: 50px; background-color: #DADDFC; text-align: right;">Keterangan :</td>
            <td style="padding-left: 3px;">
                <ul>
                    <?php foreach ($result as $row) :?>
                    <li>
                        <?= $row['KETERANGAN'] . '<br>';?>
                    </li>
                    <?php endforeach; ?>
                </ul>

            </td>
        </tr>
        <tr>
            <td colspan="2">Pembayaran
                dianggap lunas setelah kami
                uangkan</td>
        </tr>
        <tr>
            <td style="height: 50px; background-color: #DADDFC; text-align: right;">Terbilang :</td>
            <td style="font-weight: bold; text-align: left; font-style: italic; padding-left: 3px;" class="noise">
                <?= terbilang(array_sum(array_column($result, 'DEBET')) ) ?>
            </td>
        </tr>
    </tbody>
</table>
<div class="row">
    <div class="col-md-12 mt-3" style="color: black; text-align: right;">
        Kwandang, <?= tanggal_angka_ke_text($result[0]['TANGGAL'])?><br>
        Yang Menerima
        <br>
        <br>
        <br>
        <br>
        <font style="font-weight: bold;">(...........................)</font><br>
        ..........
    </div>
    <div class="col-md-12">
        <hr class="garis-2">
    </div>
</div>
</div>