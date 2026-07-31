<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Buat <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form class="forms">
                        <div class="row">
                            <div class="col-lg-4">
                                <label for="" class="form-label">Kode Opname Stok</label>
                                <input type="text" class="form-control" name="KODE_OPNAME_STOK" id="KODE_OPNAME_STOK" value="<?= "OPN-" . date('Ymd') . rand(100000, 9999) ?>" readonly>
                            </div>
                            <div class="col-lg-4">
                                <label for="" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="TANGGAL_OPNAME_STOK" id="TANGGAL_OPNAME_STOK" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-lg-4">
                                <label for="" class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan" id="KETERANGAN" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="container-fluid mt-2" style="max-height: 490px; overflow-y: scroll;">
                            <div class="table-responsive">
                                <table class="table table-lg table-bordered table-hover table-striped border-light text-nowrap mb-0 align-middle p-2" id="tableOpname" style="width:100%">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th class="text-center align-middle border-top border-dark">
                                                <h6 class="fw-semibold mb-0 text-white">#</h6>
                                            </th>
                                            <th class="text-center align-middle border-top border-dark">
                                                <h6 class="fw-semibold mb-0 text-white">KODE BARANG</h6>
                                            </th>
                                            <th class="text-center align-middle border-top border-dark">
                                                <h6 class="fw-semibold mb-0 text-white">NAMA BARANG</h6>
                                            </th>
                                            <th class="text-center align-middle border-top border-dark">
                                                <h6 class="fw-semibold mb-0 text-white">STOK SAAT INI</h6>
                                            </th>
                                            <th class="text-center align-middle border-top border-dark">
                                                <h6 class="fw-semibold mb-0 text-white">QTY</h6>
                                            </th>
                                            <th class="text-center align-middle border-top border-dark">
                                                <h6 class="fw-semibold mb-0 text-white">AKSI</h6>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4 mx-3">
                            <button type="submit" class="btn btn-success text-uppercase" id="btnSimpan"><i class="fa fa-save"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    // Objek untuk menyimpan nilai input berdasarkan barang_kode
    var inputValues = {};
    var lockedItems = {};

    // Inisialisasi DataTables
    $('#tableOpname').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        ajax: {
            url: '<?= base_url('Logistik/Gudang/OpnameStok/data') ?>',
            type: 'POST'
        },
        columns: [{
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                class: 'text-center align-middle py-1 text-dark',
                searchable: false,
                width: '5%'
            },
            {
                data: 'barang_kode',
                class: 'text-center align-middle py-1 text-dark',
            },
            {
                data: 'barang_nama',
                class: 'align-middle py-1 text-dark',
            },
            {
                data: 'stok_akhir',
                class: 'text-center align-middle py-1 text-dark',
            },
            {
                data: 'qty',
                class: 'text-center align-middle py-1 text-dark',
            },
            {
                data: 'aksi',
                class: 'text-center align-middle py-1 text-dark',
                searchable: false,
            }
        ]
    });


    $('#tableOpname').on('input', '.qty-input', function() {
        const barangKode = $(this).data('barang-kode');
        const value = $(this).val();
        inputValues[barangKode] = value;
    });

    $('#tableOpname').on('draw.dt', function() {
        $('.qty-input').each(function() {
            const barangKode = $(this).data('barang-kode');
            if (barangKode && inputValues[barangKode]) {
                $(this).val(inputValues[barangKode]);
            }
        });

        if (lockedItems && Object.keys(lockedItems).length > 0) {
            Object.keys(lockedItems).forEach((kode_barang) => {
                if (lockedItems[kode_barang]) {
                    // Kunci input yang sesuai
                    $(`input[name="qty[${kode_barang}]"]`).prop('disabled', true);
                    $(`.kunci-${kode_barang}`).hide();
                    $(`.unkunci-${kode_barang}`).removeClass('sr-only');
                }
            });
        }
    });

    $('.forms').submit(function(e) {
        e.preventDefault(); // Prevent form submission directly
        var status = $('#status').val();

        console.log(status);
        if (Object.keys(lockedItems).length !== $('#tableOpname tbody tr').length && status != 'Y') {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Kunci semua data terlebih dahulu',
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, lengkapi data'
            });
            // return false;
        } else {
            $.ajax({
                url: '<?= base_url('Logistik/Gudang/OpnameStok/simpanOpname') ?>',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                beforeSend: function() {
                    $('#btnSimpan').attr('disabled', 'disabled').html('<i class="fa fa-spin fa-spinner"></i>');
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#modal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Terjadi kesalahan saat menyimpan data.',
                        });
                    }
                },
                complete: function() {
                    $('#btnSimpan').removeAttr('disabled').html('Simpan');
                }
            });
        }


    });

    function kunci(kode_barang) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Apakah anda yakin ingin mengunci data ini?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, kunci data ini'
        }).then((result) => {
            if (result.isConfirmed) {
                $(`input[name="qty[${kode_barang}]"]`).prop('disabled', true);
                $(`.kunci-${kode_barang}`).hide();
                $(`.unkunci-${kode_barang}`).removeClass('sr-only');

                lockedItems[kode_barang] = true;
            }
        });
    }

    function unkunci(kode_barang) {
        Swal.fire({
            icon: 'info',
            title: 'Konfirmasi',
            text: 'Apakah anda ingin membuka kunci data ini?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, buka kunci'
        }).then((result) => {
            if (result.isConfirmed) {
                $(`input[name="qty[${kode_barang}]"]`).prop('disabled', false);
                $(`.kunci-${kode_barang}`).show();
                $(`.unkunci-${kode_barang}`).addClass('sr-only');

                lockedItems[kode_barang] = false;
            }
        });
    }
</script>