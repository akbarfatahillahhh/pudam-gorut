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
        <div class="table-responsive">
            <table id="table" class="table table-sm table-bordered table-striped border-dark text-nowrap mb-0 align-middle w-100">
                <thead class="text-black fs-4">
                    <tr>
                        <th class="text-center align-middle border border-dark">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </th>
                        <th class="text-center align-middle border border-dark">
                            <h6 class="fw-semibold mb-0">Nama Lengkap</h6>
                        </th>
                        <th class="text-center align-middle border border-dark">
                            <h6 class="fw-semibold mb-0">Username</h6>
                        </th>
                        <th class="text-center align-middle border border-dark">
                            <h6 class="fw-semibold mb-0">Level</h6>
                        </th>
                        <th class="text-center align-middle border border-dark">
                            <h6 class="fw-semibold mb-0">Status</h6>
                        </th>
                        <th class="text-center align-middle border border-dark">
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
    $(document).ready(function() {
        $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: "get",
                url: "<?= base_url('Master/Akun/datatable') ?>",
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    class: 'text-center text-black aling-middle py-1 border-bottom border-dark',
                    searchable: false,
                    width: '3%'
                },
                {
                    data: null,
                    class: 'text-black aling-middle py-1 border-bottom border-dark',
                    width: '25%',
                    render: function(data) {
                        return `<button class="btn text-uppercase btn-xs py-0 text-primary" onclick="detailPegawai(${data.id_pegawai})">${data.nama_lengkap}</button>`;
                    }
                },
                {
                    data: 'username',
                    class: 'text-center text-black aling-middle py-1 border-bottom border-dark',
                    width: '20%',
                },
                {
                    data: 'level',
                    class: 'text-center text-uppercase text-black aling-middle py-1 border-bottom border-dark',
                    searchable: false,
                    width: '15%'
                },
                {
                    data: 'blokir',
                    class: 'text-center text-uppercase text-black aling-middle py-1 border-bottom border-dark',
                    searchable: false,
                    width: '10%'
                },
                {
                    data: 'aksi',
                    class: 'text-center text-black aling-middle py-1 border-bottom border-dark',
                    searchable: false,
                    width: '3%'
                },
            ]
        });
    });

    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Akun/new') ?>",
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

    function blokir(username, blokir) {
        var title = (blokir == 'Y') ? 'Aktifkan' : 'Blokir';
        Swal.fire({
            title: `${title} Pelanggan ini ?`,
            html: `Apakah anda yakin untuk <strong>${title}</strong> Pelanggan ini !`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Ya, ${title}!`,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('Master/Akun/blokir/') ?>/" + username,
                    data: {
                        username: username,
                        blokir: blokir
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#table').DataTable().ajax.reload();
                    }
                });
            }
        })
    }

    function detailPegawai(id_pegawai) {
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Akun/detail_pegawai') ?>",
            data: {
                id_pegawai: id_pegawai
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

    function gantiAkses(id_session) {
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Akun/setting_akses') ?>",
            data: {
                id_session: id_session
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

    function gantiPassword(id_session) {
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Akun/ganti_password') ?>",
            data: {
                id_session: id_session
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

    function edit(id_session) {
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Akun/edit') ?>",
            data: {
                id_session: id_session
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
</script>

<?= $this->endSection() ?>