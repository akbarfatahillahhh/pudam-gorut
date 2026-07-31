<div class="card border border-dark">
    <div class="card-header border-bottom border-dark py-2 rounded-top">
        <h6 class="card-title fs-3 fw-semibold mb-0 text-center">RIWAYAT ABSENSI</h6>
    </div>
    <div class="card-body p-4">
        <div class="row">
            <div class="col-lg-2 mb-2">
                <label class="form-label">Bulan</label>
                <input type="month" class="form-control" id="bulan_absensi" name="bulan_absensi" value="<?= date('Y-m'); ?>">
            </div>
        </div>
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-sm table-bordered border-dark table-hover" id="table-absensi">
                <thead>
                    <tr>
                        <th class="align-middle text-center text-black fs-2" width="3%" rowspan="2">No</th>
                        <th class="align-middle text-center text-black fs-2" width="15%" rowspan="2">Tanggal Absen</th>
                        <th class="align-middle text-center text-black fs-2" colspan="4">Absen</th>
                    </tr>
                    <tr>
                        <th class="align-middle text-center text-black fs-2" width="25%" colspan="2">Masuk</th>
                        <th class="align-middle text-center text-black fs-2" width="25%" colspan="2">Pulang</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="align-middle text-center text-black fs-2" colspan="5">Tidak ada data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- <hr class="border border-dark border-1 opacity-50">
<div class="card card-body shadow-lg overflow-y-auto" id="absensi" style="display: none">
    <iframe src="" frameborder="0" width="100%" height="600px" id="pdf"></iframe>
</div>
<div class="card card-body shadow-lg" id="none">
    <div class="text-center py-5">
        <i class="fas fa-file-pdf text-dark h1"></i>
        <h5 class="text-dark">PREVIEW LAPORAN</h5>
    </div>
</div> -->
<script>
    $(document).ready(function() {
        get_data_absensi();
    });

    $('#bulan_absensi').change(function(e) {
        e.preventDefault();
        get_data_absensi();
    });

    function get_data_absensi() {
        var bulan_absensi = $('#bulan_absensi').val();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pegawai/get_data_absensi') ?>",
            data: {
                bulan_absensi: bulan_absensi,
                id_pegawai: '<?= $pegawai_id ?>'
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
                $('#table-absensi tbody').html(response.html);
            }
        });
    }
    // $('#btnSearch').click(function() {
    //     var tglAwal = $('#tglAwal').val();
    //     var status = $('#status').val();

    //     if (tglAwal === '' || status === '') {
    //         Swal.fire({
    //             icon: 'warning',
    //             title: 'Peringatan',
    //             text: 'Silahkan Pilih Status Absen Terlebih Dahulu',
    //             showConfirmButton: false,
    //             timer: 1500
    //         });
    //         return;
    //     }


    //     $('#absensi').slideDown();
    //     $('#pdf').attr('src', '');
    //     $('#none').hide();

    //     setTimeout(function() {
    //         $('#pdf').attr('src', '<?= base_url('Master/pegawai/absensiPdf') ?>?tglAwal=' + tglAwal + '&idPegawai=' + '<?= $pegawai_id ?? '' ?> ' + '&status=' + status);
    //         $('#absensi').slideDown();
    //     }, 200);
    // });
</script>