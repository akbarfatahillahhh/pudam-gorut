<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5 text-uppercase" id="modalLabel">Password <?= $title ?> A.N <?= $NAMA ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-update">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-danger">
                                <i class="fa fa-exclamation-circle"></i>
                                <span>Perhatian Password default/awal karyawan adalah NIK, silahkan ubah password sesuai
                                    dengan keinginan Anda. Pastikan password Anda digunakan dengan penuh tanggung
                                    jawab!</span>
                            </div>
                        </div>
                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="PASSWORD" id="exampleInputPassword1"
                                    placeholder="Password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
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
    // Event listener for the toggle button
    $("#togglePassword").on("click", function() {
        const passwordInput = $("#exampleInputPassword1");
        const icon = $(this).find("i");


        const isPassword = passwordInput.attr("type") === "password";
        passwordInput.attr("type", isPassword ? "text" : "password");

        icon.toggleClass("fa-eye fa-eye-slash");

        $(this).attr("aria-label", isPassword ? "Hide password" : "Show password");
    });
</script>
<script>
    $('#form-update').submit(function(e) {
        e.preventDefault();
        var id = "<?= $ID ?>";
        $.ajax({
            type: "POST",
            url: "<?= base_url('Master/Pegawai/master') ?>",
            data: $(this).serialize() + "&ID=" + id,
            dataType: "json",
            success: function(response) {
                // alert(response.msg);
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
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.msg,
                        showConfirmButton: true,
                        timer: 1500
                    });
                }
            }
        });
    });
</script>
