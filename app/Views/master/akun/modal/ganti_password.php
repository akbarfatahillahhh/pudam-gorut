<div class="modal fade" id="modal" tabindex="-1"  data-bs-backdrop="static"  aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalLabel">Ganti Password</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-ganti-password">
                    <div class="row">

                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label">Pegawai</label>
                            <input type="text" class="form-control fw-bolder text-center" placeholder="Pegawai..." value="<?= $nama_lengkap ?>" readonly>
                        </div>
                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label">Password Baru</label>
                            <input type="text" class="form-control" id="password_baru" placeholder="Password Baru..." autocomplete="off">
                        </div>
                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label">Konfirmasi Password</label>
                            <input type="text" class="form-control" id="password_konfirmasi" placeholder="Konfirmasi Password..." autocomplete="off">
                        </div>

                    </div>
                    <div class="border-top text-end pt-3 mt-4">
                        <input type="hidden" name="id_session" value="<?= $id_session ?>">
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
    $(document).ready(function() {
        $("#form-ganti-password").submit(function(e) {
            e.preventDefault();
            if ($("#password_baru").val() != $("#password_konfirmasi").val()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Maaf!',
                    text: 'Password Baru dna Konfirmasi Password tidak sama.',
                    showConfirmButton: true
                });
                return false;
            }

            $.ajax({
                type: "POST",
                url: "<?= base_url('Master/Akun/save_ganti_password') ?>",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    Swal.fire('Berhasil', 'Password berhasil diubah', 'success');
                    $('#modal').modal('hide');
                }
            });
        });
    });
</script>