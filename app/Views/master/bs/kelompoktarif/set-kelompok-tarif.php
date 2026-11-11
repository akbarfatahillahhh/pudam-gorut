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
            <table id="table" class="table table-sm table-bordered table-hover table-striped border-dark text-nowrap-off mb-0 align-middle">
                <thead class="text-black">
                    <tr>
                        <td class="text-center align-middle border-top border-dark" width="1%" rowspan="2">
                            <span class="fw-semibold mb-0">#</span>
                        </td>
                        <td class="text-center align-middle border-top border-dark" rowspan="2">
                            <span class="fw-semibold mb-0">KELOMPOK</span>
                        </td>
                        <td class="text-center align-middle border-top border-dark" colspan="4">
                            <span class="fw-semibold mb-0">BIAYA</span>
                        </td>
                        <td class="text-center align-middle border-top border-dark" colspan="2">
                            <span class="fw-semibold mb-0">BIAYA TARIF</span>
                        </td>
                        <td class="text-center align-middle border-top border-dark" rowspan="2">
                            <span class="fw-semibold mb-0">#</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center align-middle border-top border-dark">
                            <span class="fw-semibold mb-0">PEMASANGAN</span>
                        </td>
                        <td class="text-center align-middle border-top border-dark">
                            <span class="fw-semibold mb-0">SAMBUNGAN BARU</span>
                        </td>
                        <td class="text-center align-middle border-top border-dark">
                            <span class="fw-semibold mb-0">UKUR GAMBAR</span>
                        </td>
                        <td class="text-center align-middle border-top border-dark">
                            <span class="fw-semibold mb-0">JAMINAN LANGGANAN</span>
                        </td>
                        <td class="text-center align-middle border-top border-dark">
                            <span class="fw-semibold mb-0">( 0 - 10M3 )</span>
                        </td>
                        <td class="text-center align-middle border-top border-dark">
                            <span class="fw-semibold mb-0">( > 10 M3 )</span>
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
    $('#exportPdf').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/SetKelompokTarif/modal_export_pdf') ?>",
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
        getDataKelompokTarif();
    });

    function getDataKelompokTarif() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/SetKelompokTarif/data') ?>",
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
                let group = '';
                $.each(response, function(key, value) {
                    if (group != value.id) {
                        group = value.id;
                    }
                    html += `
                        <tr>
                            <td class="text-center text-black align-middle">${no++}</td>
                            <td class="text-center text-black align-middle">${value.kelompok_jasa}</td>
                            <td class="text-center text-black align-middle">Rp. ${parseInt(value.pemasangan).toLocaleString()}</td>
                            <td class="text-center text-black align-middle">Rp. ${parseInt(value.sambung_baru).toLocaleString()}</td>
                            <td class="text-center text-black align-middle">Rp. ${parseInt(value.ukur_gambar).toLocaleString()}</td>
                            <td class="text-center text-black align-middle">Rp. ${parseInt(value.jaminan).toLocaleString()}</td>
                            <td class="text-center text-black align-middle">Rp. ${parseInt(value.tarif_bawah_10).toLocaleString()}</td>
                            <td class="text-center text-black align-middle">Rp. ${parseInt(value.tarif_atas_10).toLocaleString()}</td>
                            <td class="text-center align-middle">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v text-black"></i>
                                    </button>
                                    <ul class="dropdown-menu border border-dark py-0">
                                        <li>
                                            <span class="dropdown-item pointer text-black" onclick="editKelompokTarif(${value.id_jasa})">
                                                <i class="fa-solid fa-pen-to-square text-warning"></i>
                                                Edit
                                            </span>
                                        </li>
                                        <li class="visually-hidden">
                                            <span class="dropdown-item text-black pointer" onclick="deleteKelompokTarif(${value.id_jasa})">
                                                <i class="fa-solid fa-trash text-danger"></i>
                                                Hapus
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `
                })
                $('#data-perkiraan').html(html);
                $('#table').DataTable();
            }
        });
    }

    function deleteKelompokTarif(id) {
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
                    url: `<?= base_url('Master/SetKelompokTarif/delete') ?>`,
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
                        getDataKelompokTarif();
                    }
                });
            }
        });
    }

    function editKelompokTarif(id) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/SetKelompokTarif/edit') ?>",
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
            dataType: "json",
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    }
</script>

<script>
    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/SetKelompokTarif/new') ?>",
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