<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <div class="row">
                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label text-black mb-1">Kode Barang</label>
                            <input type="text" class="form-control" name="barang_kode" placeholder="Kode Barang..." value="<?= "BRG-" .  rand(10000000, 9999) ?>" readonly required>
                        </div>
                        <div class="col-lg-8 mb-2">
                            <label for="" class="form-label text-black mb-1">Nama Barang</label>
                            <input type="text" class="form-control" name="barang_nama" placeholder="Nama Barang..." required>
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label text-black mb-1">Stok Barang</label>
                            <input type="text" class="form-control" name="stok_akhir" placeholder="Stok Sekarang..." required>
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label text-black mb-1">Satuan</label>
                            <select type="text" class="form-control" name="satuan" id="satuan" required>
                                <option value="" selected>[ Pilih Satuan ]</option>
                                <option value="Botol">Botol</option>
                                <option value="Buah">Buah</option>
                                <option value="Cm">Centi Meter</option>
                                <option value="Dos">Dos</option>
                                <option value="Item">Item</option>
                                <option value="Kaleng">Kaleng</option>
                                <option value="Kg">Kg</option>
                                <option value="Lembar">Lembar</option>
                                <option value="Liter">Liter</option>
                                <option value="Meter">Meter</option>
                                <option value="Pak">Pak</option>
                                <option value="Paket">Paket</option>
                                <option value="Pcs">Pcs</option>
                                <option value="Ujung">Ujung</option>
                                <option value="Unit">Unit</option>
                            </select>
                        </div>

                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label text-black mb-1">Harga</label>
                            <input type="text" class="form-control" name="harga" placeholder="Harga..." required>
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