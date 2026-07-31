<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Edit <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="EditKelompokTarif">
                    <div class="row">

                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label">Nama Kelompok Tarif Pelanggan</label>
                            <input type="text" name="kelompok_jasa" value="<?= $data['kelompok_jasa'] ?>" class="form-control" placeholder="Kelompok Tarif Pelanggan">
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Pemasangan (Rp)</label>
                            <input type="number" name="pemasangan" class="form-control" value="<?= $data['pemasangan'] ?>" placeholder="Bea. Pemasangan">
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Sambungan Baru (Rp)</label>
                            <input type="number" name="sambung_baru" class="form-control" value="<?= $data['sambung_baru'] ?>" placeholder="Bea. Sambungan Baru">
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Ukur Gambar (Rp)</label>
                            <input type="number" name="ukur_gambar" class="form-control" value="<?= $data['ukur_gambar'] ?>" placeholder="Bea. Ukur Gambar">
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Tarif Bawah (Rp)</label>
                            <input type="number" name="tarif_bawah_10" class="form-control" value="<?= $data['tarif_bawah_10'] ?>" placeholder="Bea. Tarif Bawah">
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Tarif Atas (Rp)</label>
                            <input type="number" name="tarif_atas_10" class="form-control" value="<?= $data['tarif_atas_10'] ?>" placeholder="Bea. Tarif Atas">
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label">Bea. Jaminan Langanan (Rp)</label>
                            <input type="number" name="jaminan" class="form-control" value="<?= $data['jaminan'] ?>" placeholder="Bea. Jaminan Langanan">
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
    $('#EditKelompokTarif').submit(function(e) {

        formEdit = $(this).serialize();
        formEdit += '&id_jasa=<?= $data['id_jasa'] ?>';
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/SetKelompokTarif/update') ?>",
            data: formEdit,
            dataType: "json",
            beforeSend: function() {
                $('#btnSave').html('<i class="fa fa-spinner fa-spin"></i> Loading...');
                Swal.showLoading();
                $('#btnSave').prop('disabled', true);
            },
            success: function(response) {
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
                $('#btnSave').prop('disabled', false);
                if (response.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
                $('#modal').modal('hide');
                getDataKelompokTarif();
            }
        });
    });
</script>