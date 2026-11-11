<div class="text-center">
    <h6 class="text-black fw-semibold text-uppercase">PERIODE : <span id="title-periode" class="text-uppercase"></span></h6>
</div>

<div class="table-responsive">
    <table id="table" class="table table-sm table-bordered border-dark table-striped table-hover mb-0 w-100">
        <thead>
            <tr class="text-center text-black align-middle">
                <td class="border-top border-dark py-1" rowspan="2">#</td>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        var bulan = $('#bulan option:selected').text();
        var tahun = $('#tahun option:selected').text();
        $('#title-periode').text(bulan + ' ' + tahun);
        // $('#table').DataTable({
        //     processing: true,
        //     serverSide: true,
        //     ajax: {
        //         type: "get",
        //         url: "<?= base_url('Laporan/PelangganDuplikat/datatable') ?>",
        //         data: function(data) {
        //             data.parameter = $('#parameter').val();
        //             data.unit = $('#unit').val();
        //         }
        //     },
        //     columns: [{
        //             data: null,
        //             width: '5%',
        //             searchable: false,
        //             render: function(data, type, row, meta) {
        //                 return meta.row + meta.settings._iDisplayStart + 1;
        //             },
        //             class: 'text-center text-black aling-middle py-1'
        //         },
        //         {
        //             data: 'no_sambungan',
        //             width: '12%',
        //             searchable: false,
        //             class: 'text-center text-black aling-middle fw-bolder py-1'
        //         },
        //         {
        //             data: 'nama_pelanggan',
        //             class: 'text-black aling-middle py-1',
        //         },
        //         {
        //             data: 'kecamatan',
        //             searchable: false,
        //             class: 'text-black aling-middle py-1'
        //         },
        //         {
        //             data: 'kelurahan',
        //             searchable: false,
        //             class: 'text-black aling-middle py-1'
        //         },
        //         {
        //             data: 'jenis_pelanggan',
        //             searchable: false,
        //             class: 'text-center text-black aling-middle py-1'
        //         }
        //     ]
        // });
    });
</script>