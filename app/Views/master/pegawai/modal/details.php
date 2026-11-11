<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5 fw-bolder" id="modalLabel"><?= strtoupper($detail['nama']) ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card card-body shadow-lg border border-dark">
                    <div class="d-flex">
                        <div class="col-md-2">
                            <div class="mb-1 mx-1">
                                <a href="javascript:void(0)" class="btn tabs btn-md w-100  btn-outline-dark" onclick="tabs(this,'info_personal')">PERSONAL INFO</a>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-1 mx-1">
                                <a href="javascript:void(0)" class="btn tabs btn-md w-100  btn-outline-dark" onclick="tabs(this,'e_kinerja')">E-KINERJA</a>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-1 mx-1">
                                <a href="javascript:void(0)" class="btn tabs btn-md w-100  btn-outline-dark" onclick="tabs(this,'info_absensi')">RIWAYAT KEHADIRAN</a>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-1 mx-1">
                                <a href="javascript:void(0)" class="btn tabs btn-md w-100  btn-outline-dark" onclick="tabs(this,'info_pendidikan')">PENDIDIKAN</a>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-1 mx-1">
                                <a href="javascript:void(0)" class="btn tabs btn-md w-100  btn-outline-dark" onclick="tabs(this,'info_pelatihan')">PELATIHAN</a>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-1 mx-1">
                                <a href="javascript:void(0)" class="btn tabs btn-md w-100  btn-outline-dark" onclick="tabs(this,'info_jabatan')">JABATAN</a>
                            </div>
                        </div>
                    </div>
                    <div id="content" class="mt-2">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.tabs').removeClass('btn-dark').addClass('btn-outline-dark');
        $('.tabs:first').removeClass('btn-outline-dark').addClass('btn-dark');

        tabs($('.tabs:first'), 'info_personal');
    });

    tabs = (e, id) => {
        $('.tabs').removeClass('btn-dark').addClass('btn-outline-dark');

        $(e).removeClass('btn-outline-dark').addClass('btn-dark');

        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pegawai/details_modal') ?>",
            data: {
                modal: id,
                id_pegawai: '<?= $detail['id_pegawai'] ?>'
            },
            dataType: "json",
            beforeSend: function() {
                $('#content').html('');
                $('#content').html('<div class="d-flex justify-content-center my-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
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
                $('#content').html(''); // Kosongkan konten sebelumnya
                $('#content').html(response.html);
                $('#label').html(id); // Tambahkan konten baru
            },
            error: function(xhr, status, error) {
                console.log("Error: " + error);
            }
        });
    };
</script>