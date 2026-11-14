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
                    <i class="fa fa-plus-circle"></i> Tambah
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
            <table id="table" class="table table-sm table-bordered table-hover border-dark mb-0 align-middle w-100">
                <thead class="fs-4">
                    <tr>
                        <th class="text-center align-middle text-dark fs-2" width="3%" rowspan="2">#</th>
                        <th class="text-center align-middle text-dark fs-2" rowspan="2">KELOMPOK DAN JENIS PELANGGAN</th>
                        <th class="text-center align-middle text-dark fs-2" colspan="4">BIAYA</th>
                        <th class="text-center align-middle text-dark fs-2" colspan="2">BIAYA TARIF</th>
                        <th class="text-center align-middle text-dark fs-2" rowspan="2">#</th>
                    </tr>
                    <tr>
                        <th class="text-center align-middle text-dark fs-2">PEMASANGAN</th>
                        <th class="text-center align-middle text-dark fs-2">SAMBUNGAN BARU</th>
                        <th class="text-center align-middle text-dark fs-2">UKUR GAMBAR</th>
                        <th class="text-center align-middle text-dark fs-2">JAMINAN LANGGANAN</th>
                        <th class="text-center align-middle text-dark fs-2">( 0 - 10M3 )</th>
                        <th class="text-center align-middle text-dark fs-2">( > 10 M3 )</th>
                    </tr>
                </thead>
                <tbody id="data-kelompok"></tbody>
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
            url: "<?= base_url('Master/SetJenisPelanggan/modal_export_pdf') ?>",
            dataType: "json",
            success: function(response) {
                $('.modal-place').html(``).html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });
</script>

<script>
    $(function() {
        getDataKelompok();
    });

    function getDataKelompok() {
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/SetJenisPelanggan/data') ?>",
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
                let html = '';
                let no = 1;
                let currentKelompok = '';
                $.each(response, function(key, value) {
                    if (currentKelompok !== value.kelompok_jasa) {
                        html += `
                            <tr class="bg-dark text-white border-bottom-0">
                                <td class="text-center fw-bold">${no++}</td>
                                <td class="text-center fw-bold">${value.kelompok_jasa}</td>
                                <td class="text-center border-bottom-0">Rp. ${parseInt(value.pemasangan).toLocaleString()}</td>
                                <td class="text-center border-bottom-0">Rp. ${parseInt(value.sambung_baru).toLocaleString()}</td>
                                <td class="text-center border-bottom-0">Rp. ${parseInt(value.ukur_gambar).toLocaleString()}</td>
                                <td class="text-center border-bottom-0">Rp. ${parseInt(value.jaminan).toLocaleString()}</td>
                                <td class="text-center border-bottom-0">Rp. ${parseInt(value.tarif_bawah_10).toLocaleString()}</td>
                                <td class="text-center border-bottom-0">Rp. ${parseInt(value.tarif_atas_10).toLocaleString()}</td>
                                <td></td>
                            </tr>
                        `;
                        currentKelompok = value.kelompok_jasa;
                    }

                    html += `
                    <tr class="">
                        <td class="text-center fw-semibold">+</td>
                        <td class="text-dark ps-4 fw-semibold" colspan="7">${value.jenis_pelanggan}</td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm" type="button" data-bs-toggle="dropdown">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ol class="dropdown-menu border border-dark py-0">
                                    <li>
                                        <span class="dropdown-item pointer text-black" onclick="editjenispelanggan(${value.id_jenis})">
                                            <i class="fa-solid fa-pen-to-square text-warning"></i> Edit
                                        </span>
                                    </li>
                                    <li class="visually-hidden">
                                        <span class="dropdown-item pointer text-black" onclick="deletejenispelanggan(${value.id_jenis})">
                                            <i class="fa-solid fa-trash text-danger"></i> Hapus
                                        </span>
                                    </li>
                                </ol>
                            </div>
                        </td>
                    </tr>
                `;
                });

                $('#data-kelompok').html(html);
            }
        });
    }

    function deletejenispelanggan(id) {
        Swal.fire({
            title: 'Hapus Data?',
            text: "Anda yakin ingin menghapus data ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: `<?= base_url('Master/SetJenisPelanggan/delete') ?>`,
                    data: {
                        id: id
                    },
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
                        Swal.fire(
                            'Berhasil!',
                            'Data berhasil dihapus.',
                            'success'
                        );
                        getDataKelompok();
                    }
                });
            }
        });
    }

    function editjenispelanggan(id) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/SetJenisPelanggan/edit') ?>",
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

    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/SetJenisPelanggan/new') ?>",
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