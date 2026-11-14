<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="formKelompokTarif">
                    <div class="row">

                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label">Nama Kelompok Tarif Pelanggan</label>
                            <input type="text" name="kelompok_jasa" class="form-control" placeholder="Kelompok Tarif Pelanggan">
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Pemasangan (Rp)</label>
                            <input type="number" name="pemasangan" class="form-control" placeholder="Bea. Pemasangan">
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Sambungan Baru (Rp)</label>
                            <input type="number" name="sambung_baru" class="form-control" placeholder="Bea. Sambungan Baru">
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Ukur Gambar (Rp)</label>
                            <input type="number" name="ukur_gambar" class="form-control" placeholder="Bea. Ukur Gambar">
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Tarif Bawah (Rp)</label>
                            <input type="number" name="tarif_bawah_10" class="form-control" placeholder="Bea. Tarif Bawah">
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Tarif Atas (Rp)</label>
                            <input type="number" name="tarif_atas_10" class="form-control" placeholder="Bea. Tarif Atas">
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Jaminan Langanan (Rp)</label>
                            <input type="number" name="jaminan" class="form-control" placeholder="Bea. Jaminan Langanan">
                        </div>

                    </div>
                    <div class="border-top text-end pt-3 mt-4">
                        <button type="submit" id="btnSave" class="btn btn-sm btn-success">
                            <i class="fa fa-save"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $('#formKelompokTarif').submit(function(e) {
        e.preventDefault();
        kelompokTarif = $(this).serialize();
        kelompokTarif += "&id_user=<?= session('id_pegawai') ?>";
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/SetKelompokTarif/save') ?>",
            data: kelompokTarif,
            dataType: "json",
            beforeSend: function() {
                $('#btnSave').html('<i class="fa fa-spinner fa-spin"></i> Loading...');
                Swal.showLoading();
                $('#btnSave').prop('disabled', true);
            },
            success: function(response) {
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
                $('#btnSave').prop('disabled', false);
                getDataKelompokTarif();
                if (response.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
            }
        });
    });
</script>