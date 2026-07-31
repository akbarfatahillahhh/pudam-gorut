<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4"><?= $title ?></h5>
            </div>
            <div class="col-6 text-end">
                <button class="btn btn-sm btn-primary" onclick="showModal('stok-opname-barang')">
                    <i class="fa fa-plus-circle"></i>
                    Buat Stok Opname
                </button>
            </div>
        </div>
        <div id="content-stok-barang">
            <div class="table-responsive-off">
                <table class="table table-sm table-bordered table-md p-1 w-100 small pt-3" id="tableStokOpnamBarang">
                    <thead>
                        <tr>
                            <th class="border-top border-dark" width="5%">NO.</th>
                            <th class="border-top border-dark">KODE STOK OPNAME</th>
                            <th class="border-top border-dark">TGL. STOK OPNAME</th>
                            <th class="border-top border-dark" width="5%">#</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan=" 100%" class="text-center align-middle py-2 text-black">Tidak Ada Data</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<?= $this->endSection() ?>
<?= $this->section('script') ?>

<script>
    $(document).ready(function() {
        $('.tabs').removeClass('btn-primary').addClass('btn-outline-primary');
        $('.tabs:first').removeClass('btn-outline-primary').addClass('btn-primary');
    });
    tabs = (e, id) => {
        $('.tabs').removeClass('btn-primary').addClass('btn-outline-primary');
        $(e).addClass('btn-primary').removeClass('btn-outline-primary');

        if (id == 'stok-opname') {
            $('#content-stok-opname').show();
            $('#content-stok-barang').hide();

        } else if (id == 'stok-barang') {
            $('#content-stok-barang').show();
            $('#content-stok-opname').hide();
        }
    }

    showModal = (id) => {
        $.ajax({
            type: "get",
            url: "<?= base_url('logistik/gudang/OpnameStok/show_modal') ?>",
            data: {
                modal: id
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
                })
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                $('.modal-place').html(response.html);
                $('#modal').modal('show');
            }
        });
    }

    $('#tableStokOpnamBarang').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "<?= base_url('logistik/gudang/OpnameStok/dataOpnameBarang') ?>",
            type: "post",
        },
        columns: [{
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                class: 'text-center align-middle py-0 text-black border-bottom border-dark',
                searchable: false,
                orderable: false,
                width: '3%'
            },
            {
                data: 'referensi',
                class: 'text-center align-middle py-0 text-black border-bottom border-dark',
                orderable: false,
            },
            {
                data: 'tanggal',
                class: 'text-center align-middle py-0 text-black border-bottom border-dark',
                orderable: false,
                render: function(data, type, row, meta) {
                    var date = new Date(data);
                    return ('0' + date.getDate()).slice(-2) + "/" + ('0' + (date.getMonth() + 1)).slice(-2) + "/" + date.getFullYear();
                }
            },
            {
                data: 'aksi',
                class: 'text-center align-middle py-1 text-black border-bottom border-dark',
                width: '10%',
                searchable: false,
                orderable: false,
            },
        ]
    });

    function detailsOpnameStok(referensi) {
        $.ajax({
            type: "get",
            url: "<?= base_url('logistik/gudang/OpnameStok/detailsOpnameStok') ?>",
            data: {
                referensi: referensi
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
                })
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                $('.modal-place').html(response.html);
                $('#modal').modal('show');
            }
        });
    }
</script>
<?= $this->endSection() ?>