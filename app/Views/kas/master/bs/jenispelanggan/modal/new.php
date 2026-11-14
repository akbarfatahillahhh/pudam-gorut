<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="jenisPelanggan">
                    <div class="row">
                        <div class="col-sm-12 mb-2">
                            <label for="" class="form-label">Jenis Pelanggan</label>
                            <input type="text" name="jenis_pelanggan" class="form-control" placeholder="Maasukan nama jenis pelanggan, contoh: rumah tangga, yayasan sosial dan sterusnya">
                        </div>

                        <div class="col-sm-6 mb-2">
                            <label for="" class="form-label">Kode Jenis Pelangan</label>
                            <input type="text" name="kd_jenis" class="form-control text-uppercase" placeholder="Kode Jenis Pelangan">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="kelompok_tarif" class="form-label">Kelompok Tarif</label>
                            <select name="id_jasa" class="form-control w-100" id="kelompok_tarif">
                                <option selected>[PILIH KELOMPOK TARIF]</option>
                                <?php foreach ($kelompok_jasa as $row) : ?>
                                    <option value="<?= $row['id_jasa'] ?>"><?= $row['kelompok_jasa'] ?></option>
                                <?php endforeach ?>
                            </select>
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
    $(document).ready(function() {
        $('#kelompok_tarif').select2({
                width: '100%',
                dropdownParent: $("#modal")
            }

        );

    });
    $('#jenisPelanggan').submit(function(e) {
        e.preventDefault();
        kelompokTarif = $(this).serialize();
        kelompokTarif += '&id_user=1&urutan=3';
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/SetJenisPelanggan/save') ?>",
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
                getDataKelompok();
                if (response.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
            },
            error: function(xhr, status, error) {
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
                $('#btnSave').prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: xhr.responseText,
                })
            }
        });
    });
</script>