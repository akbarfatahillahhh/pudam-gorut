<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4">Master Data <?= $title ?> </h5>
            </div>
            <div class="col-6 text-end">
                <button type="button" class="btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-plus-circle"></i>
                    Tambah
                </button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-4 col-md-4 col-lg-3 mb-2">
                <select name="" id="select-group" class="form-select">
                    <option value="">[ Pilih Grup Perkiraan ]</option>
                </select>
            </div>
            <div class="col-sm-4 col-md-4 col-lg-4 mb-2">
                <input type="search" name="search" id="search" class="form-control" placeholder="Cari Perkiraan...">
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
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto">
            <table id="table" class="table table-sm table-bordered border-dark text-nowrap mb-0 align-middle">
                <thead class="text-black fs-4">
                    <tr>
                        <td class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </td>
                        <td class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">Perkiraan</h6>
                        </td>
                        <td class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">Nomor</h6>
                        </td>
                        <td class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">Normal</h6>
                        </td>
                        <td class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">Status</h6>
                        </td>
                        <td class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </td>
                    </tr>
                </thead>
                <tbody class="text-black" id="data-perkiraan"></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-place"></div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>

<script>
    $(function() {
        getDataPerkiraan();
    });

    $('#search').on('keyup', function() {
        getDataPerkiraan($('#select-group').val(), $(this).val());
    });

    $('#select-group').select2({
        serverSide: true,
        placeholder: '[ Pilih Grup Perkiraan ]',
        ajax: {
            url: "<?= base_url('GetData/getPerkiraanGroup') ?>",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
        }
    });

    $('#select-group').on('change', function() {
        getDataPerkiraan($(this).val());
    });

    function getDataPerkiraan(data, search) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Perkiraan/data') ?>",
            data: {
                id: data,
                search: search
            },
            dataType: "json",
            beforeSend: function() {
                if (search == '' || search == null || search == undefined) {
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
                }
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                let html = '';
                let no = 1;
                let group = '';
                if (Array.isArray(response) && response.length > 0) {
                    $.each(response, function(key, value) {

                        if (group != value.GROUP_PERKIRAAN) {
                            group = value.GROUP_PERKIRAAN;
                            html += `
                            <tr class="fw-bold">
                                <td class="text-center align-middle bg-dark text-white" colspan="6">${value.GROUP_PERKIRAAN}</td>
                            </tr>
                        `;
                        }
                        html += `
                        <tr data-id="${value.ID}" data-nomor="${value.NOMOR}" data-normal="${value.NORMAL}" data-status="${value.STATUS}">
                            <td class="text-center align-middle">${no++}</td>
                            <td class="text-center align-middle">${value.NAMA_PERKIRAAN}</td>
                            <td class="text-center align-middle">${value.NOMOR}</td>
                            <td class="text-center align-middle">${value.NORMAL}</td>
                            <td class="text-center align-middle">${value.STATUS}</td>
                            <td class="text-center align-middle">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v text-black"></i>
                                    </button>
                                    <ul class="dropdown-menu py-0 border border-dark">
                                        <li>
                                            <a class="dropdown-item text-black" href="#" onclick="edit(${value.ID})">
                                                <i class="fa-solid fa-pen-to-square text-warning"></i> Edit
                                            </a>
                                        </li>
                                        <li class="visually-hidden">
                                            <a class="dropdown-item text-black" href="#" onclick="hapus(${value.ID})">
                                                <i class="fa-solid fa-trash text-danger"></i> Hapus
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `;
                    });
                    $('#data-perkiraan').html(html);
                } else if (response.length == 0) {
                    $('#data-perkiraan').html("<tr><td colspan='100%' class='text-center bg-danger text-white'>Data Tidak Ada</td></tr>");
                }
            }
        });
    }

    async function edit(id) {
        $.ajax({
            type: "GET",
            url: "<?= base_url('Master/Perkiraan/edit') ?>",
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
                    url: "<?= base_url('Master/Perkiraan/master') ?>",
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
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                getDataPerkiraan();
                            });
                        }
                    }
                });

            }
        });
    }
</script>

<script>
    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Perkiraan/new') ?>",
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

    $('#exportPdf').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Perkiraan/export_pdf') ?>",
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