<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Edit <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <input type="hidden" class="form-control" name="id" placeholder="KODE" value="<?= $id ?>" readonly required>
                    <div class="row">
                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label mb-1">Kode</label>
                            <input type="text" class="form-control" name="barang_kode" placeholder="KODE" value="<?= $barang_kode ?>" readonly required>
                        </div>
                        <div class="col-lg-8 mb-2">
                            <label for="" class="form-label mb-1">Nama Barang</label>
                            <input type="text" class="form-control" name="barang_nama" placeholder="NAMA BARANG" value="<?= $barang_nama ?>" required>
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label mb-1">Stok</label>
                            <input type="text" class="form-control" name="stok_akhir" placeholder="STOK" value="<?= $stok_akhir ?>" required>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label mb-1">Satuan</label>
                            <select type="text" class="form-control" name="satuan" id="satuan" required>
                                <option value="" selected>[ Pilih Satuan ]</option>
                                <?php foreach (['Botol', 'Buah', 'Cm', 'Dos', 'Item', 'Kaleng', 'Kg', 'Lembar', 'Liter', 'Meter', 'Pak', 'Paket', 'Pcs', 'Ujung', 'Unit'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $satuan == $s ? 'selected' : ''; ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label text-black mb-1">Harga</label>
                            <input type="text" class="form-control" name="harga" placeholder="Harga..." value="<?= $harga ?>" required>
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
    $('#form-insert').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "<?= base_url('Logistik/Gudang/Barang/master') ?>",
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
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        showConfirmButton: true,
                        timer: 1500
                    }).then(() => {
                        $('#modal').modal('hide');
                        location.reload();
                    })
                }
            }
        });
    });

    $('#satuan').select2({
        width: '100%',
        dropdownParent: $("#modal"),
    });
</script>