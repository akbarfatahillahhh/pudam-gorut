<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl h-90 modal-dialog-centered">
        <div class="modal-content w-100 h-90">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5 fw-bolder" id="modalLabel"><?= $no_sambungan ?> - <?= strtoupper($nama_pelanggan) ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <nav>
                    <div class="nav nav-tabs border-top border-end border-start border-dark" id="nav-tab" role="tablist">
                        <button class="nav-link rounded-0 active" id="nav-one-tab" data-bs-toggle="tab" data-bs-target="#nav-one" type="button" role="tab" aria-controls="nav-one" aria-selected="true" onclick="page('info_surat')">
                            Surat-Surat
                        </button>
                        <button class="nav-link rounded-0" id="nav-two-tab" data-bs-toggle="tab" data-bs-target="#nav-two" type="button" role="tab" aria-controls="nav-two" aria-selected="false" onclick="page('info_tagihan')">
                            Detail Tagihan
                        </button>
                        <button class="nav-link rounded-0" id="nav-three-tab" data-bs-toggle="tab" data-bs-target="#nav-three" type="button" role="tab" aria-controls="nav-three" aria-selected="false" onclick="page('input_rekening_baru')">
                            Input Rekening Baru
                        </button>
                        <button class="nav-link rounded-0" id="nav-four-tab" data-bs-toggle="tab" data-bs-target="#nav-four" type="button" role="tab" aria-controls="nav-four" aria-selected="false" onclick="page('input_piutang')">
                            Input Piutang
                        </button>
                    </div>
                </nav>
                <div class="tab-content border border-dark rounded-bottom p-3 h-90" id="nav-tabContent">
                    <div class="tab-pane fade show active h-100" id="nav-active" role="tabpanel" aria-labelledby="nav-active-tab" tabindex="0"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        page('info_surat')
    });

    function page(page) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Pelanggan/get_details') ?>",
            data: {
                id_pelanggan: '<?= $id_pelanggan ?>',
                page: page
            },
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
                $('#nav-active').html(response.html);
            }
        });
    }
</script>