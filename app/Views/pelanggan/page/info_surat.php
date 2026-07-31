<div class="row h-100">
    <div class="col-lg-3 h-100">
        <div class="mb-2">
            <button class="btn btn-sm btn-outline-dark text-start w-100" id="kwitansi" onclick="get_latters('kwitansi')">
                KWITANSI
            </button>
        </div>
        <div class="mb-2">
            <button class="btn btn-sm btn-outline-dark text-start w-100" id="surat_permohonan" onclick="get_latters('surat_permohonan')">
                SURAT PERMOHONAN
            </button>
        </div>
        <div class="mb-2">
            <button class="btn btn-sm btn-outline-dark text-start w-100" id="spp" onclick="get_latters('spp')">
                SURAT PERSETUJUAN PENYAMBUNGAN (SPP)
            </button>
        </div>
        <div class="mb-2">
            <button class="btn btn-sm btn-outline-dark text-start w-100" id="dpb" onclick="get_latters('dpb')">
                DAFTAR PERMINTAAN BARANG (DPB)
            </button>
        </div>
        <div class="mb-2">
            <button class="btn btn-sm btn-outline-dark text-start w-100" id="surat_pernyataan_pelanggan" onclick="get_latters('surat_pernyataan_pelanggan')">
                SURAT PERNYATAAN PELANGGAN
            </button>
        </div>
    </div>

    <div class="col-9 h-100" id="content-place">
        <div class="border border-dark py-5">
            <h5 class="text-center text-black text-uppercase py-5">
                <i class="fa fa-file-pdf text-black h2"></i>
                <br>
                Preview PDF
            </h5>
        </div>
    </div>
</div>

<script>
    function get_latters(page) {
        console.log(page)
        $.ajax({
            type: "get",
            url: "<?= base_url('Pelanggan/get_latters') ?>/" + page + "/" + '<?= $id_pelanggan ?>',
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
                $('.btn').removeClass('btn-dark').addClass('btn-outline-dark');
                $('#' + page).removeClass('btn-outline-dark').addClass('btn-dark');
            },
            success: function(response) {
                $('#content-place').html(response.html);
            }
        });
    }
</script>