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
            <table id="table-pegawai" class="table table-sm table-striped table-hover table-bordered border-dark text-nowrap py-3 align-middle">
                <thead class="text-black">
                    <tr>
                        <td class="text-center align-middle border-top border-dark">#</td>
                        <td class="text-center align-middle border-top border-dark">KODE</td>
                        <td class="text-center align-middle border-top border-dark">NAMA</td>
                        <td class="text-center align-middle border-top border-dark">TANGGAL LAHIR</td>
                        <td class="text-center align-middle border-top border-dark">JENIS KELAMIN</td>
                        <td class="text-center align-middle border-top border-dark">ALAMAT</td>
                        <td class="text-center align-middle border-top border-dark">STATUS</td>
                        <td class="text-center align-middle border-top border-dark">#</td>
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
    $('#table-pegawai').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('master/pegawai/data') ?>',
            type: 'GET',
        },
        columns: [{
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                class: "text-center text-black py-1",
                searchable: false
            },
            {
                data: "KODE",
                class: "text-center text-black py-1",
            },
            {
                data: "NAMA",
                class: "text-black text-uppercase py-1",
            },
            {
                data: "TGL_LAHIR",
                class: "text-center text-black py-1",
                searchable: false
            },
            {
                data: "KELAMIN",
                class: "text-center text-black py-1",
                render: function(data, type, row, meta) {
                    return (data == 'L') ? 'Laki-laki' : 'Perempuan';
                },
                searchable: false

            },
            {
                data: "ALAMAT",
                class: "text-center text-black py-1"
            },
            {
                data: "aktif",
                class: "text-center text-black py-1",
                searchable: false

            },
            {
                data: "aksi",
                class: "text-center text-black py-1",
                searchable: false
            },
        ]
    });

    $('#btnAdd').click(function() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pegawai/new') ?>",
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
                $('.modal-place').html(response.html);
                $('#modal').modal('show');
            }
        });
    });

    function setPassword(id) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pegawai/setPassword') ?>/",
            data: {
                ID: id
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
                $('.modal-place').html(response.html);
                $('#modal').modal('show');
            }
        })
    }

    function setStatus(id, status, nikblok) {
        if (status == 'Aktif') {
            var status = 'Blokir';
        } else {
            var status = 'Aktif';
        }
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data akan diubah menjadi " + status,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, ubah!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('Master/Pegawai/setStatus') ?>",
                    data: {
                        ID: id,
                        status: status,
                        nikblok: nikblok
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
                                text: response.msg,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // location.reload();
                                $('#table-pegawai').DataTable().ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    }
                });
            }
        });
    }

    details = (id) => {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pegawai/details') ?>",
            data: {
                ID: id
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
                $('.modal-place').html(response.html);
                $('#modal').modal('show');
            }
        });
    }
</script>
<?= $this->endSection() ?>