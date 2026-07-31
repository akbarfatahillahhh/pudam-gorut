<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="EditjenisPelanggan">
                    <div class="row">
                        <div class="col-sm-12 mb-2">
                            <label for="" class="form-label">Jenis Pelanggan</label>
                            <input type="text" name="jenis_pelanggan" value="<?= $data['jenis_pelanggan'] ?>" class="form-control" placeholder="Maasukan nama jenis pelanggan, contoh: rumah tangga, yayasan sosial dan sterusnya">
                        </div>

                        <div class="col-sm-6 mb-2">
                            <label for="" class="form-label">Kode Jenis Pelangan</label>
                            <input type="text" name="kd_jenis" value="<?= $data['kd_jenis'] ?>" class="form-control text-uppercase" placeholder="Kode Jenis Pelangan">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="kelompok_tarif" class="form-label">Kelompok Tarif</label>
                            <select name="id_jasa" class="form-control" id="kelompok_tarif">
                                <option value="">[ Pilih Kelompok Tarif ]</option>
                                <?php foreach ($data['kelompok_jasa'] as $row) : ?>
                                    <option value="<?= $row['id_jasa'] ?>" <?= ($row['id_jasa'] == $data['id_jasa']) ? 'selected' : '' ?>><?= $row['kelompok_jasa'] ?></option>
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
        });
    });
    $('#EditjenisPelanggan').submit(function(e) {
        e.preventDefault();

        formEdit = $(this).serialize();
        formEdit += '&id_jenis=<?= $data['id_jenis'] ?>';
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/SetJenisPelanggan/update') ?>",
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
                getDataKelompok();

            },
            error: function(xhr, status, error) {
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
                $('#btnSave').prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan!',
                    showConfirmButton: false,
                    timer: 1500
                })
            }
        });
    });
</script>