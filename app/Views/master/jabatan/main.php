<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4">Master <?= $title ?></h5>
            </div>
            <div class="col-6 text-end">
                <button type="button" class="btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-plus-circle"></i>
                    Tambah
                </button>
            </div>
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
            <table id="table-jabatan" class="table table-sm table-hover table-striped table-bordered border-dark text-nowrap mb-0 align-middle w-100 pt-3">
                <thead class="text-black fs-4">
                    <tr>
                        <th class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">JABATAN</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </th>
                    </tr>
                </thead>
                <tbody class="text-black"></tbody>
            </table>
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
            url: "<?= base_url('Master/Jabatan/modal_export_pdf') ?>",
            dataType: "json",
            success: function(response) {
                $('.modal-place').html(``).html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });
</script>

<script>
    $('#table-jabatan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('Master/Jabatan/data') ?>",
            type: "post",
        },
        columns: [{
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                class: "text-center align-middle py-1 text-dark",
                width: "3%",
                searchable: false
            },
            {
                data: "JABATAN",
                class: "align-middle py-1 text-dark",
            },
            {
                data: "action",
                class: "text-center align-middle py-1 text-dark",
                width: "3%",
                searchable: false
            },
        ]
    });

    async function edit(id) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Jabatan/edit') ?>",
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

    async function hapus(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Anda tidak akan bisa mengembalikan data ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('Master/Jabatan/master') ?>",
                    data: {
                        ID: id,
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
                        if (response.status == "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                showConfirmButton: true,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                });
            }
        })
    }
</script>

<script>
    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Jabatan/new') ?>",
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
    });
</script>

<?= $this->endSection() ?>