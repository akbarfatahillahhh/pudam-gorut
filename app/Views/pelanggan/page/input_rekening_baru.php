<div class="row">
    <div class="col-lg-6">
        <div class="card border border-dark rounded-0 border-bottom-0 shadow-none">
            <div class="card-body px-0 pt-2 pb-0 px-0">
                <h6 class="card-title fw-semibold text-center fs-3">Detail Pelanggan</h6>
                <table class="table table-borderless table-striped table-hover w-100 mb-0">
                    <tr class="border-bottom border-top border-dark">
                        <td class="py-2 text-black">Nama Pelanggan</td>
                        <td class="py-2 text-black text-end fw-semibold" width="30%" id="rekening-nama-pelanggan">-</td>
                    </tr>
                    <tr class="border-bottom border-top border-dark">
                        <td class="py-2 text-black">No. Sambungan</td>
                        <td class="py-2 text-black text-end fw-semibold" width="30%" id="rekening-no-sambungan">-</td>
                    </tr>
                    <tr class="border-bottom border-top border-dark">
                        <td class="py-2 text-black">Jenis Pelanggan</td>
                        <td class="py-2 text-black text-end fw-semibold" width="30%" id="rekening-jenis-pelanggan">-</td>
                    </tr>
                    <tr class="border-bottom border-top border-dark">
                        <td class="py-2 text-black">Unit</td>
                        <td class="py-2 text-black text-end fw-semibold" width="30%" id="rekening-unit">-</td>
                    </tr>
                    <tr class="border-bottom border-top border-dark">
                        <td class="py-2 text-black">Kelurahan/Desa</td>
                        <td class="py-2 text-black text-end fw-semibold" width="30%" id="rekening-kelurahan">-</td>
                    </tr>
                    <tr class="border-bottom border-top border-dark">
                        <td class="py-2 text-black">Tarif 0-10<sup>M3</sup></td>
                        <td class="py-2 text-black text-end fw-semibold" width="30%" id="rekening-tarif-dibawah">-</td>
                    </tr>
                    <tr class="border-bottom border-top border-dark">
                        <td class="py-2 text-black">Tarif &gt; 10 <sup>M3</sup></td>
                        <td class="py-2 text-black text-end fw-semibold" width="30%" id="rekening-tarif-atas">-</td>
                    </tr>
                    <tr class="border-bottom border-top border-dark">
                        <td class="py-2 text-black">Biaya Admin</td>
                        <td class="py-2 text-black text-end fw-semibold" width="30%" id="rekening-biaya-admin">-</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border border-dark rounded-0 shadow-none">
            <div class="card-body py-2 px-0">
                <h6 class="card-title fw-semibold text-center fs-3">Entri Pemakaian Air (Rekening Baru)</h6>
                <div class="border-top border-dark px-3 py-2">
                    <form id="formRekeningBaru">
                        <div classs="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="">
                        </div>
                        <div classs="mb-3">
                            <label class="form-label">Jumlah Meter Air (M3)</label>
                            <input type="number" class="form-control" name="pemakaian" value="">
                        </div>
                        <div classs="mb-3">
                            <label class="form-label">Angka Meter Lalu (M3)</label>
                            <input type="number" class="form-control" name="pemakaian_air_lalu" id="pemakaian_air_lalu" required value="">
                        </div>
                        <div class="mt-3 text-end">
                            <button type="submit" id="btn-submit" class="btn btn-primary px-4">
                                <i class="fa fa-save"></i>
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        get_data_pelanggan('<?= $id_pelanggan ?>');
        $('#formRekeningBaru').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize();
            data += '&id_pelanggan=' + '<?= $id_pelanggan ?>';
            $.ajax({
                type: "get",
                url: "<?= base_url('Pelanggan/save_rekening_baru') ?>",
                data: data,
                dataType: "json",
                beforeSend: () => {
                    $('#btn-submit').prop('disabled', true);
                    // Swal.showLoading({
                    //     title: 'Loading...',
                    //     html: 'Sedang Diproses',
                    //     allowOutsideClick: false
                    // });
                    // Swal.fire({
                    //     icon: 'warning',
                    //     title: 'Loading...',
                    //     html: 'Sedang Diproses',
                    //     showConfirmButton: false,
                    //     allowOutsideClick: false,
                    //     onBeforeOpen: () => {
                    //         Swal.showLoading()
                    //     }
                    // });
                },
                complete: () => {
                    $('#btn-submit').prop('disabled', false);
                },
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.status,

                        });
                    }
                },
                error: (xhr, status, error) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: xhr.responseJSON.message,
                    })
                }
            });

        });
    });




    function get_data_pelanggan(id_pelanggan) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Pelanggan/get_data_pelanggan') ?>",
            data: {
                id_pelanggan: id_pelanggan
            },
            dataType: "json",
            beforeSend: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Loading...',
                    html: 'Sedang Diproses',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                $('#rekening-nama-pelanggan').text(response.nama_pelanggan);
                $('#rekening-no-sambungan').text(response.no_sambungan);
                $('#rekening-jenis-pelanggan').text(response.jenis_pelanggan);
                $('#rekening-unit').text(response.kecamatan);
                $('#rekening-kelurahan').text(response.kelurahan);

                $('#rekening-tarif-dibawah').text(number_format(response.tarif_bawah_10));
                $('#rekening-tarif-atas').text(number_format(response.tarif_atas_10));
                $('#rekening-biaya-admin').text(number_format(response.bea_admin));
                $('#pemakaian_air_lalu').val(response.pemakaian_air_lalu);
            },

        });
    }

    function number_format(nominal) {
        return "Rp. " + nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
</script>