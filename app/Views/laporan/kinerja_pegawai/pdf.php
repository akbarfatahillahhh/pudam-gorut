<table>
    <tr>
        <td class="text-center" style="border: none;">
            <h3 style="margin: 0;">PERUMDA AIR MINUM TIRTA GERBANG EMAS<br>GORONTALO UTARA</h3>
            <hr>
            <h4 style="margin: 0;">LAPORAN KINERJA PEGAWAI</h4>
        </td>
    </tr>
</table>

<br>

<table class="table-no-border" style="border: none;">
    <tr>
        <td style="width: 20%; border: none;">NAMA PEGAWAI</td>
        <td style="width: 2%; border: none;">:</td>
        <td style="border: none;"><b><?= esc(strtoupper($pegawai['NAMA'])) ?></b></td>
    </tr>
    <tr>
        <td style="border: none;">NIK</td>
        <td style="border: none;">:</td>
        <td style="border: none;"><?= esc($pegawai['NIK']) ?></td>
    </tr>
    <tr>
        <td style="border: none;">PERIODE</td>
        <td style="border: none;">:</td>
        <td style="border: none;">
            <?= date('d/m/Y', strtotime($tglAwal)) ?> S/D <?= date('d/m/Y', strtotime($tglAkhir)) ?>
        </td>
    </tr>
</table>

<br>

<div class="table">
    <table>
        <thead>
            <tr>
                <th class="text-center" width="4%">NO</th>
                <th class="text-center" width="14%">TANGGAL</th>
                <th>TUGAS POKOK</th>
                <th>URAIAN KEGIATAN</th>
                <th class="text-center" width="12%">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($result)) : ?>
                <?php foreach ($result as $i => $row) : ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row['TANGGAL_KINERJA'])) ?></td>
                        <td><?= esc($row['TUGAS_POKOK'] ?? '-') ?></td>
                        <td><?= esc($row['KETERANGAN_LAINNYA'] ?? '-') ?></td>
                        <td class="text-center"><?= esc($row['STATUS_KINERJA'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td class="text-center" colspan="5">Tidak ada kegiatan pada periode ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<br><br>

<table class="table-no-border" style="border: none;">
    <tr>
        <td style="width: 60%; border: none;"></td>
        <td class="text-center" style="border: none;">
            Gorontalo Utara, <?= date('d/m/Y') ?><br><br><br><br><br>
            <u><b>PIMPINAN</b></u>
        </td>
    </tr>
</table>
