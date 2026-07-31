<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PERUMDA GORUT</title>
    <link rel="shortcut icon" type="image/png" href="<?= base_url() ?>/logo/perumda-gorut.png" />
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/styles.min.css" />
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <div
            class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-md-6 col-lg-4 col-xxl-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <a href="##" class="text-nowrap logo-img text-center d-block py-3 w-100">
                                    <img src="<?= base_url() ?>/logo/perumda-gorut.png" width="180" alt="">
                                </a>
                                <p class="text-center text-black fw-bolder">PERUMDA GORUT</p>
                                <form id="formLogin">
                                    <div class="mb-3">
                                        <label for="exampleInputEmail1" class="form-label fw-normal">Username</label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="exampleInputPassword1" class="form-label fw-normal">Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">
                                        Masuk
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url() ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $("#formLogin").on("submit", function(e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('login') ?>",
                    data: $(this).serialize(),
                    success: function(data) {
                        if (data.status == "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                window.location.href = "<?= base_url('home') ?>";
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    }
                });
            });
        });
    </script>

</body>

</html>