<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <div class="mb-3 text-center">
                        <label for="" class="form-label">Nama Bank</label>
                        <input type="text" class="form-control" name="NAMA_BANK" placeholder="Nama Bank..." required>
                    </div>
                    <div class="border-top text-center pt-3 mt-4">
                        <button type="submit" class="btn btn-sm w-100 btn-success">
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
            url: "<?= base_url('Master/Bank/save') ?>",
            type: "POST",
            data: $(this).serialize(),
            dataType: "JSON",
            success: function(response) {
                if (response.status == true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                    $('#modal').modal('hide');
                    $('#table').DataTable().ajax.reload();
                } else {
                    alert(response.message);
                }
            }
        })
    });
</script>