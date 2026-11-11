<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Mapping PCM</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formPcmPelanggan">
                    <div>
                        <table class="table table-sm table-bordered border-dark mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-uppercase text-black fw-bold text-white bg-dark text-center" colspan="100%">DETAIL PELANGGAN</td>
                                </tr>
                                <tr>
                                    <td class="text-uppercase text-black fw-semibold" width="30%">No. Sambungan</td>
                                    <td class="text-uppercase text-black fw-semibold ps-2"><?= $no_sambungan ?></td>
                                </tr>
                                <tr>
                                    <td class="text-uppercase text-black fw-semibold" width="30%">Nama Pelanggan</td>
                                    <td class="text-uppercase text-black fw-semibold ps-2"><?= $nama_pelanggan ?></td>
                                </tr>
                                <tr>
                                    <td class="text-uppercase text-black fw-semibold" width="30%">Unit</td>
                                    <td class="text-uppercase text-black fw-semibold ps-2"><?= $unit ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td class="text-uppercase text-black fw-bold text-white bg-warning text-center" colspan="100%">PCM</td>
                                </tr>
                                <tr>
                                    <td class="px-2 py-2" colspan="100%">
                                        <select class="form-select" id="select-pcm" name="pcm" required>
                                            <?php if ($petugas_meter) : ?>
                                                <?php foreach ($pcm as $value) : ?>
                                                    <option value="<?= $value['id_session'] ?>" <?= ($petugas_meter == $value['id_session']) ? 'selected' : '' ?>><?= $value['nama_lengkap'] ?></option>
                                                <?php endforeach ?>
                                            <?php else : ?>
                                                <option value="">[ Belum Ada PCM ]</option>
                                            <?php endif ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-top text-center pt-3 mt-4">
                        <button type="submit" id="btnSave" class="btn w-75 btn-success">
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
    $('#formPcmPelanggan').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pelanggan/updatePcm') ?>",
            data: {
                id_pelanggan: '<?= $id_pelanggan ?>',
                id_pcm: $('#select-pcm').val(),
            },
            dataType: "json",
            beforeSend: function() {
                $('#btnSave').attr('disabled', 'disabled');
                $('#btnSave').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
            },
            success: function(response) {
                $('#btnSave').attr('disabled', false);
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
                if (response.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                    $('#modal').modal('hide');
                    $('#table').DataTable().ajax.reload(null, false);
                }
            },
            error: function(xhr, status, error) {
                $('#btnSave').attr('disabled', false);
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });
    $(document).ready(function() {
        $('#select-pcm').select2({
            width: '100%',
            dropdownParent: $("#modal")
        });
    });
</script>