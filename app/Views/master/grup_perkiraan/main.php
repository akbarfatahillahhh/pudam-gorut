<?= $this->extend('layouts/main_ekas') ?>
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
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table id="table" class="table table-sm table-bordered table-striped border-dark text-nowrap mb-0 align-middle w-100">
                <thead class="text-black fs-4">
                    <tr>
                        <th class="text-center" width="3%">No.</th>
                        <th class="text-center">Grup Perkiraan</th>
                        <th class="text-center">No. Perkiraan</th>
                        <th class="text-center">Normal</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">#</th>
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
                url: "<?= base_url('Master/GrupPerkiraan/datatable') ?>",
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    class: 'text-center',
                    searchable: false,
                    width: '3%'
                },
                {
                    data: 'GROUP_PERKIRAAN',
                },
                { data: 'NO_PERKIRAAN', class: 'text-center', },
                { data: 'NORMAL', class: 'text-center', },
                { data: 'STATUS', class: 'text-center', },
                {
                    data: 'aksi',
                    class: 'text-center',
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
            url: "<?= base_url('Master/Bank/new') ?>",
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

    function edit(id) {
        $.ajax({
            type: "POST",
            url: "<?= base_url('Master/Bank/edit') ?>",
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

    function hapus(id) {
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
                    url: "<?= base_url('Master/Bank/delete') ?>",
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
                    success: function(response) {
                        if (response.status == true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            })
                            $('#table').DataTable().ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            })
                        }
                    }
                });
            }
        });
    }
</script>

<?= $this->endSection() ?>