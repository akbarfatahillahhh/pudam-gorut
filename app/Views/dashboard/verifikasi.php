<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Verifikasi Angka Meter</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger py-3 text-center">
                    <p class="mb-0 fs-2 text-danger"><b>Perhatian..!</b>, Perhatikan data-data dibawah ini dengan baik dan benar. Data yang bisa diubah hanya tanggal catat dan angka meter.</p>
                </div>
                <p class="text-center fs-2 text-black">
                    Pelanggan Khusus: <?= ($data_pelanggan['pelanggan_khusus'] == 'N') ? '<span class="fw-bolder text-danger">TIDAK</span>' : '<span class="fw-bolder text-success">YA</span>' ?> |
                    Meter Tersedia: <?= ($data_meter['ada_meter'] == 'N') ? '<span class="fw-bolder text-danger">TIDAK</span>' : '<span class="fw-bolder text-primary">YA</span>' ?> |
                    Kondisi Meter: <?= (in_array($data_meter['kondisi_meter'], ['Baik', 'Kabur', 'Rusak'])) ? "<span class=\"fw-bolder " . (['Baik' => 'text-success', 'Kabur' => 'text-warning', 'Rusak' => 'text-danger'][$data_meter['kondisi_meter']] ?? 'text-black') . "\">" . $data_meter['kondisi_meter'] . "</span>" : '<span class="fw-bolder text-black">-</span>' ?>
                </p>
                <div class="row">
                    <div class="col-md-5">
                        <div class="alert alert-warning py-1 px-1">
                            <p class="mb-0 fs-2">
                                <b>Perhatian..!</b>, Jika Kubikasi tidak sesuai!
                                Ubah Angka Meter Baru atau Lama untuk menghasilkan kubikasi yang diinginkan Lalu Tekan [Enter]. 
                                Jika tidak abaikan dan klik Sesuai!.
                            </p>
                        </div>
                        <div>
                            <table class="table table-sm table-bordered table-hover border-dark">
                                <tr>
                                    <th class="text-center fw-normal bg-dark text-white" colspan="2">DETAIL METER</th>
                                </tr>
                                <tr>
                                    <td class="text-black" width="50%">ID PENCATATAN</td>
                                    <td class="text-black fw-bolder" width="50%">
                                        <?= $data_meter['id'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-black" width="50%">NO. SAMBUNGAN</td>
                                    <td class="text-black fw-bolder" width="50%">
                                        <?= $data_pelanggan['no_sambungan'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-black" width="50%">NAMA PELANGGAN</td>
                                    <td class="text-black fw-bolder" width="50%">
                                        <?= $data_pelanggan['nama_pelanggan'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-black" width="50%">TANGGAL CATAT</td>
                                    <td class="text-black" width="50%">
                                        <input type="date" class="w-100 fw-bolder text-primary border border-dark ps-2" id="tgl_catat" name="tgl_catat" value="<?= date('Y-m-d', strtotime($data_meter['tgl_catat']) ?? '') ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-black" width="50%">ANGKA METER BARU</td>
                                    <td class="text-black" width="50%">
                                        <input type="number" class="w-100 fw-bolder text-primary border border-dark ps-2" id="pemakaian_air" name="pemakaian_air" value="<?= $data_tagihan['pemakaian_air'] ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-black" width="50%">ANGKA METER TERAKHIR</td>
                                    <td class="text-black" width="50%">
                                        <input type="number" class="w-100 fw-bolder text-primary border border-dark ps-2" id="pemakaian_air_lalu" name="pemakaian_air_lalu" value="<?= $data_tagihan['pemakaian_air_lalu'] ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-black" width="50%">KUBIKASI</td>
                                    <td class="text-success fw-bolder" width="50%" id="html-kubikasi">
                                        <?= ($data_tagihan['pemakaian_air'] - $data_tagihan['pemakaian_air_lalu']) ?>
                                    </td>
                                </tr>
                            </table>
                            <button type="button" class="btn btn-warning text-black btn-sm small w-100">
                                <b>Atur kubikasi ke nilai 10 ?</b>
                                <br>
                                Click disini! Jika tidak abaikan fungsi ini.
                            </button>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div>
                            <table class="table table-sm table-bordered table-hover border-dark">
                                <tbody>
                                    <tr>
                                        <td class="text-center bg-primary text-white">METER SEKARANG</td>
                                        <td colspan="2" class="text-center bg-warning text-white">METER LALU</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">
                                            <a href="https://mywater.mokaraja.net/foto_meter/<?= $data_meter['foto_meter'] ?>">
                                                <img class="border border-dark" style="height: 60px; width: 60px; object-fit: cover;" src="https://mywater.mokaraja.net/foto_meter/<?= $data_meter['foto_meter'] ?>">
                                            </a>
                                            <a href="https://mywater.mokaraja.net/foto_meter/<?= $data_meter['foto_rumah'] ?>">
                                                <img class="border border-dark" style="height: 60px; width: 60px; object-fit: cover;" src="https://mywater.mokaraja.net/foto_meter/<?= $data_meter['foto_rumah'] ?>">
                                            </a>
                                            <div class="mt-2">
                                                <span class="badge bg-dark fs-1">
                                                    <?= date('F Y', strtotime($data_meter['tgl_catat'])) ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="https://mywater.mokaraja.net/foto_meter/<?= $foto_meter_bulan_lalu['foto_meter'] ?>">
                                                <img class="border border-dark" style="height: 60px; width: 60px; object-fit: cover;" src="https://mywater.mokaraja.net/foto_meter/<?= $foto_meter_bulan_lalu['foto_meter'] ?>" class="img-fluid m-b-10" alt="">
                                            </a>
                                            <a href="https://mywater.mokaraja.net/foto_meter/<?= $foto_meter_bulan_lalu['foto_rumah'] ?>">
                                                <img class="border border-dark" style="height: 60px; width: 60px; object-fit: cover;" src="https://mywater.mokaraja.net/foto_meter/<?= $foto_meter_bulan_lalu['foto_rumah'] ?>" class="img-fluid m-b-10" alt="">
                                            </a>
                                            <div class="mt-2">
                                                <span class="badge bg-dark fs-1">
                                                    <?= date('F Y', strtotime('-1 month', strtotime($data_meter['tgl_catat']))) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="https://mywater.mokaraja.net/foto_meter/<?= $foto_meter_dua_bulan_lalu['foto_meter'] ?>">
                                                <img class="border border-dark" style="height: 60px; width: 60px; object-fit: cover;" src="https://mywater.mokaraja.net/foto_meter/<?= $foto_meter_dua_bulan_lalu['foto_meter'] ?>" class="img-fluid m-b-10" alt="">
                                            </a>
                                            <a href="https://mywater.mokaraja.net/foto_meter/<?= $foto_meter_dua_bulan_lalu['foto_rumah'] ?>">
                                                <img class="border border-dark" style="height: 60px; width: 60px; object-fit: cover;" src="https://mywater.mokaraja.net/foto_meter/<?= $foto_meter_dua_bulan_lalu['foto_rumah'] ?>" class="img-fluid m-b-10" alt="">
                                            </a>
                                            <div class="mt-2">
                                                <span class="badge bg-dark fs-1">
                                                    <?= date('F Y', strtotime('-2 month', strtotime($data_meter['tgl_catat']))) ?>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <table class="table table-sm table-bordered table-hover border-dark">
                                <tbody>
                                    <tr>
                                        <th class="text-center fw-normal bg-dark text-white" colspan="100%">DETAIL REKENING BARU</td>
                                    </tr>
                                    <tr>
                                        <td class="text-black">NO. REKENING</td>
                                        <td class="fw-bolder text-black"><?= $data_tagihan['no_rek'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-black">BULAN</td>
                                        <td class="text-black fw-bolder"><?= date('F Y', strtotime($data_meter['tgl_catat'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-black">0-10 M<sup>3</sup></td>
                                        <td class="d-flex border-start-0 text-black"><b>Rp. </b> <input type="number" class="w-100 fw-bolder text-black border border-dark text-end" value="<?= $data_tagihan['tag_dibawah_10'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td class="text-black">&gt; 10 M<sup>3</sup></td>
                                        <td class="d-flex border-start-0 text-black"><b>Rp. </b><input type="number" class="w-100 fw-bolder text-black border border-dark text-end" value="<?= $data_tagihan['tag_diatas_10'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td class="text-black"> BY. ADMIN</td>
                                        <td class="d-flex border-start-0 text-black"><b>Rp. </b><input type="number" class="w-100 fw-bolder text-black border border-dark text-end" value="<?= $data_tagihan['by_admin'] ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td class="text-black">JUMLAH</td>
                                        <td class="d-flex border-start-0 text-black"><b>Rp. </b><input type="number" class="w-100 fw-bolder text-black border border-dark text-end" value="<?= ($data_tagihan['jlh_tagihan'] + $data_tagihan['by_admin'] ) ?>" readonly></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div>
                                <button type="button" class="btn btn-success btn-sm w-100" id="btn-konfirmasi">
                                    <i class="fas fa-save"></i>
                                    Konfirmasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#btn-konfirmasi').click(function (e) { 
        e.preventDefault();
        $.ajax({
            type: "method",
            url: "url",
            data: {
                id_pemakaian: '',
                tarif_dibawah_10: '',
                tarif_diatas_10: '',
                bulan_tagihan: '',
                pemakaian_air: '',
                pemakaian_air_lalu: '',
                by_admin: '',
                jlh_tagihan: ''
            },
            dataType: "json",
            success: function (response) {
                
            }
        });
    });
</script>