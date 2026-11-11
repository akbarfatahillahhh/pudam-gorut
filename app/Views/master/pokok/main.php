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
        <div class="row mb-3">
            <div class="col-sm-4 col-md-4 col-lg-3 mb-2">
                <select name="" id="select-pokok" class="form-select">
                    <option value="">[ Pilih Tugas Pokok ]</option>
                </select>
            </div>
            <div class="col-sm-4 col-md-4 col-lg-4 mb-2">
                <input type="search" name="search" id="search" class="form-control" placeholder="Cari Tugas Pokok...">
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
            <table id="" class="table table-sm table-bordered border-dark text-nowrap mb-0 align-middle">
                <thead class="text-black fs-4">
                    <tr>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </th>
                        <th class="align-middle">
                            <h6 class="fw-semibold mb-0">Tugas Pokok</h6>
                        </th>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </th>
                    </tr>
                </thead>
                <tbody class="text-black" id="data-pokok"></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-place"></div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>

<script>
    $(document).ready(function() {
        getDataPokok();
    })

    $('#select-pokok').select2({
        placeholder: '[ Pilih Tugas Pokok ]',
        width: '100%',
        ajax: {
            url: '/GetData/getJabatan',
            type: 'GET',
            dataType: 'json',
            data: function(params) {
                return {
                    search: params.term,
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            }
        }
    });

    $('#select-pokok').on('change', function() {
        getDataPokok($(this).val(), $('#search').val());
    })
    $('#search').on('keyup', function() {
        getDataPokok($('#select-pokok').val(), $(this).val());
    })

    function getDataPokok(jabatan, search) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pokok/data') ?>",
            data: {
                search: search,
                jabatan: jabatan
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
                if (Array.isArray(response) && response.length > 0) {
                    let html = '';
                    let group = '';
                    let no = 1;
                    $.each(response, function(indexInArray, valueOfElement) {
                        if (group != valueOfElement.JABATAN) {
                            group = valueOfElement.JABATAN;
                            html += `
                            <tr class="fw-bold">
                                <td class="bg-dark text-white text-center">${no++}</td>
                                <td class="bg-dark text-white text-center" colspan="2">${valueOfElement.JABATAN}</td>
                            </tr>
                        `;
                        }
                        html += `
                        <tr>
                            <td></td>
                            <td>${valueOfElement.TUGAS_POKOK}</td>
                            <td class="text-center align-middle">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v text-black"></i>
                                    </button>
                                    <ul class="dropdown-menu py-0">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item text-black" onclick="edit(${valueOfElement.ID})"><i class="text-warning fa fa-gear"></i> Edit</a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        `;
                    });
                    $('#data-pokok').html(html);
                } else if (Array.isArray(response) && response.length == 0) {
                    $('#data-pokok').html("<tr><td colspan='100%' class='text-center bg-danger text-white'>Data Tidak Ada</td></tr>");
                }
            }
        });
    }

    async function edit(id) {
        $.ajax({
            type: "GET",
            url: "<?= base_url('Master/Pokok/edit') ?>",
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
            url: "<?= base_url('Master/Pokok/new') ?>",
            dataType: "json",
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });
</script>

<?= $this->endSection() ?>