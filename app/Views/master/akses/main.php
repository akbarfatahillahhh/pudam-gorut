<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row mb-3">
            <div class="col-12">
                <h5 class="card-title fw-semibold mb-1"><?= esc($title) ?></h5>
                <p class="text-muted fs-2 mb-0">
                    Atur sistem dan modul yang boleh dibuka tiap user.
                    Data Master &amp; Administrasi tetap hanya untuk level admin.
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table id="table" class="table table-sm table-hover table-striped table-bordered border-dark text-nowrap mb-0 align-middle w-100">
                <thead class="text-black fs-4">
                    <tr>
                        <th class="text-center align-middle border-top border-dark" width="4%">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">NAMA LENGKAP</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark" width="12%">
                            <h6 class="fw-semibold mb-0">USERNAME</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark" width="12%">
                            <h6 class="fw-semibold mb-0">LEVEL</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark" width="18%">
                            <h6 class="fw-semibold mb-0">SISTEM</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark" width="18%">
                            <h6 class="fw-semibold mb-0">MODUL</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark" width="8%">
                            <h6 class="fw-semibold mb-0">AKSI</h6>
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
        $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('Master/AksesModul/data') ?>",
                type: 'GET',
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    class: 'text-center align-middle',
                    searchable: false,
                    orderable: false,
                },
                {
                    data: 'nama_lengkap',
                    class: 'align-middle text-uppercase',
                },
                {
                    data: 'username',
                    class: 'text-center align-middle',
                },
                {
                    data: 'level',
                    class: 'text-center align-middle text-uppercase',
                },
                {
                    data: 'sistem',
                    class: 'align-middle',
                    searchable: false,
                    orderable: false,
                },
                {
                    data: 'modul',
                    class: 'align-middle',
                    searchable: false,
                    orderable: false,
                },
                {
                    data: 'akses',
                    class: 'text-center align-middle',
                    searchable: false,
                    orderable: false,
                },
            ],
            order: [[3, 'asc'], [1, 'asc']],
            pageLength: 25,
            language: {
                emptyTable: 'Belum ada user.',
                zeroRecords: 'User tidak ditemukan.',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                infoFiltered: '(disaring dari _MAX_ total data)',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya',
                },
            },
        });
    });

    function aturAkses(id_session) {
        $.ajax({
            type: 'POST',
            url: "<?= base_url('Master/AksesModul/edit') ?>",
            data: {
                id_session: id_session
            },
            dataType: 'json',
            success: function(response) {
                if (!response.html) {
                    Swal.fire('Gagal', response.msg || 'Form tidak bisa dibuka.', 'error');
                    return;
                }

                $('.modal-place').html('').html(response.html);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modal')).show();
            },
            error: function() {
                Swal.fire('Gagal', 'Form tidak bisa dibuka.', 'error');
            },
        });
    }

    window.reloadAkses = function() {
        $('#table').DataTable().ajax.reload(null, false);
    };
</script>
<?= $this->endSection() ?>
