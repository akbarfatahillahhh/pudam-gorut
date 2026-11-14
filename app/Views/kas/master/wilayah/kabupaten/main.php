<?php $uri = current_url(true); ?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>
<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4 text-uppercase">Master <?= $title . " " . $uri->getSegment(2) ?> </h5>
            </div>
            <div class="col-6 text-end">
                <button type="button" class="btn btn-sm btn-primary d-none" id="btnAdd">
                    <i class="fa fa-plus-circle"></i>
                    Tambah
                </button>
            </div>
            <div class="col-12">
                <div class="table-responsive-off">
                    <table class="table table-md table-hover table-striped table-bordered table-hover border-dark text-nowrap mb-0 align-middle w-100" id="table">
                        <thead class="text-black fs-4">
                            <tr>
                                <th class="text-center align-middle border-top border-dark">
                                    <h6 class="fw-semibold mb-0">#</h6>
                                </th>
                                <th class="text-center align-middle border-top border-dark">
                                    <h6 class="fw-semibold mb-0">KABUPATEN</h6>
                                </th>
                                <th class="text-center align-middle border-top border-dark">
                                    <h6 class="fw-semibold mb-0">#</h6>
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-place"></div>
<?= $this->endSection() ?>
<?= $this->section('script') ?>
<script>
    $('#table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "<?= base_url('Master/Wilayah/' . $uri->getSegment(2)) ?>",
            type: "post",
        },
        columns: [{
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                class: 'text-center align-middle py-1 text-dark',
                searchable: false,
                width: '5%'
            },
            {
                data: 'kabupaten',
                class: 'text-center align-middle py-1 text-dark',
            },
            {
                data: 'aksi',
                class: 'text-center align-middle py-1 text-dark',
                searchable: false,
                width: '5%'
            },
        ],
    });
    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            url: "<?= base_url('Master/Wilayah/' . $uri->getSegment(2) . 'new') ?>",
            type: "get",
            dataType: "json",
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('#modal').modal('show');
            }
        })
    });

    function edit(id) {
        $.ajax({
            url: "<?= base_url('Master/Wilayah/' . $uri->getSegment(2) . 'edit') ?>",
            type: "get",
            data: {
                id: id
            },
            dataType: "json",
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('#modal').modal('show');
            }
        })
    }
</script>
<?= $this->endSection() ?>