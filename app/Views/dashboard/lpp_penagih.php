<div>
    <div class="row">
        <div class="col-lg-2">
            <label for="" class="form-label">Periode</label>
            <input type="date" id="lpp_penagih-dateStart" class="form-control" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="col-lg-2">
            <label for="" class="form-label">S/D</label>
            <input type="date" id="lpp_penagih-dateEnd" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="my-2">
        <button class="btn btn-sm btn-danger" id="exportPdf">
            <i class="fa fa-file-pdf"></i>
            PDF
        </button>
        <button class="btn btn-sm btn-success" id="exportExcel">
            <i class="fa fa-file-excel"></i>
            Excel
        </button>
    </div>
    <div class="">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered table-hover border-dark mb-0" id="lpp_penagih-table">
                <thead>
                    <tr>
                        <th class="text-center align-middle text-black">#</th>
                        <th class="text-center align-middle text-black">Petugas</th>
                        <th class="text-center align-middle text-black">Unit</th>
                        <th class="text-center align-middle text-black">Total Rek</th>
                        <th class="text-center align-middle text-black">Jml. Rek Ditagih</th>
                        <th class="text-center align-middle text-black">Total Tagihan</th>
                        <th class="text-center align-middle text-black">Periode</th>
                        <th class="text-center align-middle text-black">#</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center align-middle text-black py-4" colspan="100%">DATA KOSONG</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<script>
    $(document).ready(function() {
        $('#lpp_penagih-dateStart, #lpp_penagih-dateEnd').change(function(e) {
            e.preventDefault();
            var dateStart = $('#lpp_penagih-dateStart').val();
            var dateEnd = $('#lpp_penagih-dateEnd').val();

            getDataLPP_penagih()
        });
        getDataLPP_penagih();

        $('#exportPdf').click(function (e) { 
            e.preventDefault();
            $.ajax({
                type: "post",
                url: "<?= base_url('Home/modal_export_pdf') ?>",
                data: {
                    awal: $('#lpp_penagih-dateStart').val(),
                    akhir: $('#lpp_penagih-dateEnd').val(),
                    route: 'lpp_penagih'
                },
                dataType: "json",
                success: function (response) {
                    $('.modal-place').html(response.page);
                    $('#modal').modal('show');
                }
            });
        });
    });

    function getDataLPP_penagih() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Home/data_lpp_penagih') ?>",
            data: {
                awal: $('#lpp_penagih-dateStart').val(),
                akhir: $('#lpp_penagih-dateEnd').val(),
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
                $('#lpp_penagih-table tbody').html(response.html);
            }
        });
    }
</script>