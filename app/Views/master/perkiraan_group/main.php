<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4">Master <?= esc($title) ?></h5>
            </div>
            <div class="col-6 text-end">
                <button type="button" class="btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-plus-circle"></i>
                    Tambah
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="table-group" class="table table-sm table-hover table-striped table-bordered border-dark text-nowrap mb-0 align-middle w-100">
                <thead class="text-black fs-4">
                    <tr>
                        <th class="text-center align-middle border-top border-dark" width="4%">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">NO PERKIRAAN</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">GRUP PERKIRAAN</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark" width="10%">
                            <h6 class="fw-semibold mb-0">NORMAL</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark" width="12%">
                            <h6 class="fw-semibold mb-0">STATUS</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark" width="4%">
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
    $(function() {
        $('#table-group').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('Master/PerkiraanGroup/data') ?>",
                type: 'POST',
            },
            columns: [{
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    class: 'text-center align-middle',
                    searchable: false,
                },
                { data: 'NO_PERKIRAAN', class: 'text-center align-middle' },
                { data: 'GROUP_PERKIRAAN', class: 'align-middle text-uppercase' },
                { data: 'NORMAL', class: 'text-center align-middle', searchable: false },
                { data: 'STATUS', class: 'text-center align-middle' },
                { data: 'aksi', class: 'text-center align-middle', searchable: false, orderable: false },
            ],
            order: [[1, 'asc']],
            pageLength: 25,
            language: {
                emptyTable: 'Belum ada grup perkiraan.',
                zeroRecords: 'Grup perkiraan tidak ditemukan.',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ baris',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ grup',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
            },
        });
    });

    function reloadTable() {
        $('#table-group').DataTable().ajax.reload(null, false);
    }

    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'get',
            url: "<?= base_url('Master/PerkiraanGroup/new') ?>",
            dataType: 'json',
            success: function(response) {
                $('.modal-place').html(response.html);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modal')).show();
            },
            error: function() {
                Swal.fire('Gagal', 'Tidak bisa membuka form tambah.', 'error');
            },
        });
    });

    function edit(id) {
        $.ajax({
            type: 'get',
            url: "<?= base_url('Master/PerkiraanGroup/edit') ?>",
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'error') {
                    Swal.fire('Gagal', response.msg, 'error');
                    return;
                }

                $('.modal-place').html(response.html);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modal')).show();
            },
            error: function() {
                Swal.fire('Gagal', 'Tidak bisa membuka form edit.', 'error');
            },
        });
    }

    function hapus(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: 'Grup perkiraan yang masih dipakai tidak bisa dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: "<?= base_url('Master/PerkiraanGroup/hapus') ?>",
                data: { ID: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.msg,
                            showConfirmButton: false,
                            timer: 1500,
                        });
                        reloadTable();
                    } else {
                        Swal.fire('Gagal', response.msg, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Gagal', 'Data gagal dihapus.', 'error');
                },
            });
        });
    }
</script>
<?= $this->endSection() ?>
