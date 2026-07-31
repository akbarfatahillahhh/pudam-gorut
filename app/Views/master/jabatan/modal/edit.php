<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalLabel">Edit <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-update">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <input type="hidden" name="ID" value="<?= $ID ?>">
                            <label for="" class="form-label">JABATAN</label>
                            <input type="text" class="form-control" name="JABATAN" placeholder="Jabatan..." value="<?= $JABATAN ?>">
                        </div>
                        <div class="border-top text-end pt-3 mt-4">
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
    $('#form-update').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "<?= base_url('Master/Jabatan/master') ?>",
            data: $(this).serialize(),
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
                if (response.status == "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.msg,
                        showConfirmButton: true,
                        timer: 1500
                    }).then(() => {
                        $('#modal').modal('hide');
                        location.reload();
                    });
                }
            }
        });
    });
</script>