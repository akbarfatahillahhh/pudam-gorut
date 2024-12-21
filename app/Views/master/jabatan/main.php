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
                            <h6 class="fw-semibold mb-0">Perkiraan</h6>
                        </th>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">Nomor</h6>
                        </th>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">Normal</h6>
                        </th>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">Status</h6>
                        </th>
                        <th class="text-center align-middle">
                            <h6 class="fw-semibold mb-0">#</h6>
                        </th>
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

    function getDataPerkiraan() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Perkiraan/data') ?>",
            dataType: "json",
            success: function(response) {
                let html = '';
                let no = 1;
                let group = '';
                $.each(response, function(key, value) {
                    if (group != value.GROUP_PERKIRAAN) {
                        group = value.GROUP_PERKIRAAN;
                        html += `
                            <tr class="fw-bold">
                                <td class="text-center align-middle bg-dark text-white" colspan="6">${value.GROUP_PERKIRAAN}</td>
                            </tr>
                        `
                    }
                    html += `
                        <tr>
                            <td class="text-center align-middle">${no++}</td>
                            <td class="text-center align-middle">${value.GROUP_PERKIRAAN}</td>
                            <td class="text-center align-middle">${value.NOMOR}</td>
                            <td class="text-center align-middle">${value.NORMAL}</td>
                            <td class="text-center align-middle">${value.STATUS}</td>
                            <td class="text-center align-middle">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v text-black"></i>
                                    </button>
                                    <ul class="dropdown-menu py-0">
                                        <li>
                                            <a class="dropdown-item text-black" href="#">
                                                <i class="fa-solid fa-pen-to-square text-warning"></i>
                                                Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-black" href="#">
                                                <i class="fa-solid fa-trash text-danger"></i>
                                                Hapus
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `
                })
                $('#data-perkiraan').html(html);
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
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });
</script>

<?= $this->endSection() ?>