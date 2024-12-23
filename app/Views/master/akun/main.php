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
            <table id="table" class="table table-sm table-bordered border-dark text-nowrap mb-0 align-middle">
                <thead class="text-black fs-4">
                    <tr>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </th>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">Nama Lengkap</h6>
                        </th>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">Username</h6>
                        </th>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">Level</h6>
                        </th>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">Status</h6>
                        </th>
                        <th class="text-center align-middle">
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
            pageLength: 50,
            ajax: {
                url: "<?= base_url('Master/Akun/datatable') ?>",
            },
            columns: [{
                    data: 'id',
                    name: 'id',
                    width: '1%',
                    class: 'text-center align-middle'
                },
                {
                    data: 'kdDokter',
                    name: 'kdDokter',
                    width: '10%',
                    class: 'text-center align-middle'
                },
                {
                    data: 'kode',
                    name: 'kode',
                    width: '10%',
                    class: 'text-center align-middle'
                },
                {
                    data: 'nmDokter',
                    name: 'nmDokter',
                    class: 'align-middle'
                },
                {
                    data: 'nmDokterIHS',
                    name: 'nmDokterIHS',
                    class: 'align-middle'
                },
                {
                    data: 'aksiAktif',
                    name: 'aksiAktif',
                    searchable: false,
                    width: '5%',
                    class: 'text-center align-middle'
                },
                {
                    data: 'aksi',
                    name: 'aksi',
                    searchable: false,
                    width: '5%',
                    class: 'text-center align-middle'
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
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });
</script>

<?= $this->endSection() ?>