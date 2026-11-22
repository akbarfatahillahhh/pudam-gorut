<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= esc($title) ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <div class="row">
                        <div class="col-lg-4 mb-2">
                            <label class="form-label">No Perkiraan</label>
                            <input type="text" class="form-control" name="NO_PERKIRAAN" placeholder="Contoh: 16.02.00" maxlength="50" required>
                        </div>
                        <div class="col-lg-8 mb-2">
                            <label class="form-label">Grup Perkiraan</label>
                            <input type="text" class="form-control" name="GROUP_PERKIRAAN" placeholder="Contoh: Uang Muka Kerja" maxlength="150" required>
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Normal</label>
                            <select name="NORMAL" class="form-select" required>
                                <option value="">[ Pilih Normal ]</option>
                                <option value="Debet">DEBET</option>
                                <option value="Kredit">KREDIT</option>
                            </select>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Status</label>
                            <select name="STATUS" class="form-select" required>
                                <option value="">[ Pilih Status ]</option>
                                <option value="Biaya">BIAYA</option>
                                <option value="Pendapatan">PENDAPATAN</option>
                            </select>
                        </div>
                    </div>
                    <div class="border-top text-end pt-3 mt-4">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-success">
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
    $('#form-insert').submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: "<?= base_url('Master/PerkiraanGroup/master') ?>",
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Loading...',
                    html: 'Sedang Diproses',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                });
                Swal.showLoading();
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                if (response.status === 'success') {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modal')).hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.msg,
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    reloadTable();
                } else {
                    Swal.fire('Gagal', response.msg, 'error');
                }
            },
            error: function() {
                Swal.fire('Gagal', 'Data gagal disimpan.', 'error');
            },
        });
    });
</script>
