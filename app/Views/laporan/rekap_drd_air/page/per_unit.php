<div class="text-center">
    <h6 class="text-black fw-semibold">UNIT : <span id="title-unit" class="text-uppercase"></span></h6>
    <h6 class="text-black fw-semibold">PERIODE : <span id="periode-awal"></span> - <span id="periode-akhir"></span></h6>
</div>

<div class="table-responsive">
    <table id="table" class="table table-sm table-bordered border-dark table-striped table-hover mb-0 w-100">
        <thead>
            <tr class="text-center text-black align-middle">
                <td class="border-top border-dark py-1" rowspan="2">#</td>
                <td class="border-top border-dark py-1" rowspan="2">JENIS PELANGGAN</td>
                <td class="border-top border-dark py-1" rowspan="2">TERPASANG</td>
                <td class="border-top border-dark py-1" rowspan="2">LBR</td>
                <td class="border-top border-dark py-1" colspan="3">M<sup>M3</sup></td>
                <td class="border-top border-dark py-1" rowspan="2">HARGA AIR</td>
                <td class="border-top border-dark py-1" rowspan="2">BA</td>
                <td class="border-top border-dark py-1" rowspan="2">JUMLAH</td>
            </tr>
            <tr class="text-center text-black align-middle">
                <td class="border-top border-dark py-1">&lt; 10<sup>3</sup></td>
                <td class="border-top border-dark py-1">&gt; 10<sup>3</sup></td>
                <td class="border-top border-dark py-1">KUBIKASI</td>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        get_select_unit();
        getDataTableUnit()
        $('#dateStart, #dateEnd, #statusLaporan, .filter-per-unit #unit').change(function(e) {
            e.preventDefault();
            getDataTableUnit()
        });
    });

    function formatTanggal(tanggal) {
        let parts = tanggal.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return tanggal;
    }

    function getDataTableUnit() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Laporan/RekapDrdAir/datatable') ?>",
            data: {
                jenisLaporan: $('#jenisLaporan').val(),
                dateStart: $('#dateStart').val(),
                dateEnd: $('#dateEnd').val(),
                statusLaporan: $('#statusLaporan').val(),
                unit: $('.filter-per-unit #unit').val(),
            },
            dataType: "json",
            beforeSend: function() {
                $('#title-unit').text($('select#unit :selected').text());
                $('#periode-awal').text(formatTanggal($('#dateStart').val()));
                $('#periode-akhir').text(formatTanggal($('#dateEnd').val()));

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
                $('#table tbody').html(response.html);
            }
        });
    }

    function get_select_unit() {
        $.ajax({
            type: "get",
            url: "<?= base_url('GetData/getUnit') ?>",
            dataType: "json",
            success: function(response) {
                var html = '';
                html += '<option value="all" selected>[ Semua Unit ]</option>';
                $.each(response, function(key, value) {
                    html += '<option value="' + value.id + '">' + value.text + '</option>';
                });
                $('.filter-per-unit #unit').html(html);
                $('.filter-per-unit #unit').select2({
                    width: '100%'
                });
            }
        });
    }
</script>