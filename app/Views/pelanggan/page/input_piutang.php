<form id="formPiutang" class="px-3">
    <div class="row">
        <div class="col-12">
            <h5 class="text-center text-uppercase border-bottom border-dark pb-3 mb-3">Form Input Piutang</h5>
        </div>

        <div class="col-12">
            <div class="alert alert-warning small text-center">
                <p class="text-black mb-0"><strong>Perhatian..!</strong> Angka yang dimasukan sudah termasuk Biaya Admin!.</p>
                <p class="text-black mb-0">Jika pada bulan tertentu bernilai 0 (NOL), maka data pada bulan tersebut tidak akan disimpan secara otomatis. Sebaliknya jika angka lebih dari 0 (NOL) pada bulan tertentu akan disimpan secara otomatis.</p>
            </div>
        </div>

        <div class="col-12">
            <h5 class="text-center text-uppercase border-bottom border-dark"></h5>
        </div>

        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">Januari</label>
            <input type="number" class="form-control text-center" name="januari" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">Februari</label>
            <input type="number" class="form-control text-center" name="februari" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">Maret</label>
            <input type="number" class="form-control text-center" name="maret" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">April</label>
            <input type="number" class="form-control text-center" name="april" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">Mei</label>
            <input type="number" class="form-control text-center" name="mei" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">Juni</label>
            <input type="number" class="form-control text-center" name="juni" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">Juli</label>
            <input type="number" class="form-control text-center" name="juli" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">Agustus</label>
            <input type="number" class="form-control text-center" name="agustus" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">September</label>
            <input type="number" class="form-control text-center" name="september" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">Oktober</label>
            <input type="number" class="form-control text-center" name="oktober" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">November</label>
            <input type="number" class="form-control text-center" name="november" value="0">
        </div>
        <div class="col-lg-2 mb-2 text-center">
            <label for="" class="form-label">Desember</label>
            <input type="number" class="form-control text-center" name="desember" value="0">
        </div>

        <div class="col-12 text-center mt-3">
            <button type="submit" class="btn btn-primary w-75">
                <i class="fa fa-save"></i>
                Simpan
            </button>
        </div>

    </div>
</form>

<script>
    $('#formPiutang').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('id_pelanggan', '<?= $id_pelanggan ?>');
        $.ajax({
            type: "post",
            url: "<?= base_url('Pelanggan/savePiutang') ?>",
            data: formData,
            contentType: false,
            processData: false,
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
            success: function(response) {}
        });
    });
</script>