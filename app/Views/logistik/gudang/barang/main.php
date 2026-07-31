<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4">Data <?= $title ?></h5>
            </div>
            <div class="col-6 text-end">
                <button type="button" class=" text-uppercase btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-plus-circle"></i>
                    tambah <?= $title ?>
                </button>
            </div>

            <div class="mb-2">
                <button class="btn btn-sm btn-danger" id="exportPdf">
                    <i class="fa fa-file-pdf"></i>
                    PDF
                </button>
                <button class="btn btn-sm btn-success" id="exportExcel">
                    <i class="fa fa-file-excel"></i>
                    Excel
                </button>
            </div>

            <div class="table-responsive">
                <table id="data-barang" class="table table-sm table-bordered table-hover table-striped border-dark text-nowrap mb-0 align-middle pt-3">
                    <thead>
                        <tr>
                            <th class="text-center border-top border-dark text-black">Kode Barang</th>
                            <th class="text-center border-top border-dark text-black">Barang</th>
                            <th class="text-center border-top border-dark text-black">Satuan</th>
                            <th class="text-center border-top border-dark text-black">Stok Saat Ini </th>
                            <th class="text-center border-top border-dark text-black">Harga</th>
                            <th class="text-center border-top border-dark text-black">#</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal-place"></div>

<?= $this->endSection() ?>
<?= $this->section('script') ?>


<script>
    $('#exportPdf').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Logistik/Gudang/Barang/modal_export_pdf') ?>",
            dataType: "json",
            success: function(response) {
                $('.modal-place').html(``).html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });
</script>

<script>
    $('#data-barang').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('logistik/gudang/barang/data') ?>',
            type: 'POST'
        },
        columns: [{
                data: 'barang_kode',
                class: 'text-center align-middle py-1 text-dark fw-semibold',
                width: '10%',
            },
            {
                data: "barang_nama",
                class: 'align-middle py-1 text-dark fw-semibold',
            },
            {
                data: "satuan",
                class: 'text-center align-middle text-center py-1 text-dark fw-semibold',
                searchable: false
            },
            {
                data: "stok_akhir",
                class: 'text-center align-middle text-center py-1 text-dark fw-semibold',
                searchable: false
            },
            {
                data: "harga",
                class: 'text-center align-middle text-center py-1 text-dark fw-semibold',
                searchable: false,
                render: function(data, type, row) {
                    return formatRupiah(data);
                }
            },
            {
                data: "aksi",
                class: 'text-center align-middle text-center py-1 text-dark fw-semibold',
                width: '5%',
                searchable: false
            },
        ]
    })

    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('logistik/gudang/barang/new') ?>",
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
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    })

    function formatRupiah(angka) {
        var rupiah = '';
        var angkarev = angka.toString().split('').reverse().join('');
        for (var i = 0; i < angkarev.length; i++)
            if (i % 3 == 0) rupiah += angkarev.substr(i, 3) + '.';
        return 'Rp. ' + rupiah.split('', rupiah.length - 1).reverse().join('');
    }

    function edit(id) {
        $.ajax({
            type: "get",
            url: "<?= base_url('logistik/gudang/barang/edit') ?>",
            data: {
                id: id
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
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    }

    function del(id) {
        Swal.fire({
            icon: 'question',
            title: 'Yakin ingin menghapus data ini?',
            showDenyButton: true,
            confirmButtonText: 'Ya',
            denyButtonText: 'Tidak',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('logistik/gudang/barang/master') ?>",
                    data: {
                        id: id
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
                        if (response.status == 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            })
                        }
                    }
                })
            }
        })
    }
</script>
<?= $this->endSection() ?>