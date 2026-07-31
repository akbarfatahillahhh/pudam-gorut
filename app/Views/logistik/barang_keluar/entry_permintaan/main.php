<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>
<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4">Entry <?= $title ?></h5>
            </div>
            <div class="col-6 text-end">
                <button type="button" class="btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-plus-circle"></i>
                    PERMINTAAN BARU
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="table-permintaan" class="table table-sm table-bordered table-hover table-striped border-dark mb-0 w-100 align-middle pt-2">
                <thead class="text-black">
                    <tr>
                        <th class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">TGL. PERMINTAAN</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">No. PERMINTAAN</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark">
                            <h6 class="fw-semibold mb-0">Nama PENERIMA</h6>
                        </th>
                        <th class="text-center align-middle border-top border-dark">
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
    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Logistik/BarangKeluar/Permintaan/new') ?>",
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

    $('#table-permintaan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('Logistik/BarangKeluar/Permintaan/data') ?>",
            type: "post",
        },
        columns: [{
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                class: "text-center align-middle",
                width: "1%",
                searchable: false
            },
            {
                data: "tanggal",
                class: "text-center align-middle text-black py-1",
                width: "15%",
                searchable: false
            },
            {
                data: "no_permintaan",
                class: "text-center align-middle text-black py-1",
                width: "25%",
            },
            {
                data: "penerima",
                class: "text-uppercase text-center align-middle text-black py-1"
            },
            {
                data: "aksi",
                class: "text-center align-middle text-black py-1",
                width: "1%",
                searchable: false
            },
        ]
    });

    function edit(id) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Logistik/BarangKeluar/Permintaan/edit') ?>",
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
</script>

<?= $this->endSection() ?>