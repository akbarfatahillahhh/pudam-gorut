<div>
    <div class="row">
        <div class="col-lg-2">
            <label for="" class="form-label">Periode</label>
            <input type="date" id="bms-dateStart" class="form-control" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="col-lg-2">
            <label for="" class="form-label">S/D</label>
            <input type="date" id="bms-dateEnd" class="form-control" value="<?= date('Y-m-d') ?>">
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
            <table class="table table-sm table-striped table-bordered table-hover border-dark mb-0" id="bms-table">
                <thead>
                    <tr>
                        <th class="text-center align-middle text-black">#</th>
                        <th class="text-center align-middle text-black">Unit</th>
                        <th class="text-center align-middle text-black">Jumlah Rekening</th>
                        <th class="text-center align-middle text-black">Total Penerimaan</th>
                        <th class="text-center align-middle text-black">#</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center align-middle py-4" colspan="100%">DATA KOSONG</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<script>
    $(document).ready(function() {
        $('#bms-dateStart, #bms-dateEnd').change(function(e) {
            e.preventDefault();
            var dateStart = $('#bms-dateStart').val();
            var dateEnd = $('#bms-dateEnd').val();

            getDataLPP_bms_fastpay()
        });
        getDataLPP_bms_fastpay()

        $('#exportPdf').click(function (e) { 
            e.preventDefault();
            $.ajax({
                type: "post",
                url: "<?= base_url('Home/modal_export_pdf') ?>",
                data: {
                    awal: $('#bms-dateStart').val(),
                    akhir: $('#bms-dateEnd').val(),
                    route: 'lpp_bms_fastpay'
                },
                dataType: "json",
                success: function (response) {
                    $('.modal-place').html(response.page);
                    $('#modal').modal('show');
                }
            });
        });
    });

    function getDataLPP_bms_fastpay() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Home/data_lpp_bms_fastpay') ?>",
            data: {
                awal: $('#bms-dateStart').val(),
                akhir: $('#bms-dateEnd').val(),
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
                $('#bms-table tbody').html(response.html);
            }
        });
    }
</script>