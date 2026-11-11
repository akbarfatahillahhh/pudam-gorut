<div class="text-center">
    <h6 class="text-black fw-semibold text-uppercase">UNIT : <span id="title-unit" class="text-uppercase"></span></h6>
</div>

<div class="table-responsive">
    <table id="table" class="table table-sm table-bordered border-dark table-striped table-hover mb-0 w-100">
        <thead>
            <tr class="text-center text-black align-middle">
                <td class="border-top border-dark py-1" rowspan="2">#</td>
                <td class="border-top border-dark py-1" rowspan="2">DESA</td>
                <td class="border-top border-dark py-1" colspan="12">BULAN TUNGGAKAN TAHUN <span id="title-periode"></span></td>
                <td class="border-top border-dark py-1" rowspan="2">JUMLAH</td>
                <td class="border-top border-dark py-1" rowspan="2">#</td>
            </tr>
            <tr class="text-center text-black align-middle">
                <td class="border-top border-dark py-1">JANUARI</td>
                <td class="border-top border-dark py-1">FEBRUARI</td>
                <td class="border-top border-dark py-1">MARET</td>
                <td class="border-top border-dark py-1">APRIL</td>
                <td class="border-top border-dark py-1">MEI</td>
                <td class="border-top border-dark py-1">JUNI</td>
                <td class="border-top border-dark py-1">JULI</td>
                <td class="border-top border-dark py-1">AGUSTUS</td>
                <td class="border-top border-dark py-1">SEPTEMBER</td>
                <td class="border-top border-dark py-1">OKTOBER</td>
                <td class="border-top border-dark py-1">NOVEMBER</td>
                <td class="border-top border-dark py-1">DESEMBER</td>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('#title-unit').text($('#unit option:selected').text());
        $('#title-periode').text(($('#dateStart').val() == $('#dateEnd').val()) ? $('#dateStart').val() : $('#dateStart').val() + ' - ' + $('#dateEnd').val());
        $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: "get",
                url: "<?= base_url('Laporan/RincianPiutangPerunit/datatable') ?>",
                data: function(data) {
                    data.dateStart = $('#dateStart').val();
                    data.dateEnd = $('#dateEnd').val();
                    data.statusLaporan = $('#statusLaporan').val();
                    data.unit = $('#unit').val();
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    class: 'text-center text-black aling-middle py-1'
                },
                {
                    data: 'kelurahan',
                    name: 'kelurahan',
                    class: 'text-black aling-middle py-1 fw-bolder small'
                },
                {
                    data: 'tagihan_bulan_1',
                    data: 'tagihan_bulan_1',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_1 = data ?? 0;
                        return formatRupiah(tagihan_bulan_1);
                    }
                },
                {
                    data: 'tagihan_bulan_2',
                    data: 'tagihan_bulan_2',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_2 = data ?? 0;
                        return formatRupiah(tagihan_bulan_2);
                    }
                },
                {
                    data: 'tagihan_bulan_3',
                    data: 'tagihan_bulan_3',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_3 = data ?? 0;
                        return formatRupiah(tagihan_bulan_3);
                    }
                },
                {
                    data: 'tagihan_bulan_4',
                    data: 'tagihan_bulan_4',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_4 = data ?? 0;
                        return formatRupiah(tagihan_bulan_4);
                    }
                },
                {
                    data: 'tagihan_bulan_5',
                    data: 'tagihan_bulan_5',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_5 = data ?? 0;
                        return formatRupiah(tagihan_bulan_5);
                    }
                },
                {
                    data: 'tagihan_bulan_6',
                    data: 'tagihan_bulan_6',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_6 = data ?? 0;
                        return formatRupiah(tagihan_bulan_6);
                    }
                },
                {
                    data: 'tagihan_bulan_7',
                    data: 'tagihan_bulan_7',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_7 = data ?? 0;
                        return formatRupiah(tagihan_bulan_7);
                    }
                },
                {
                    data: 'tagihan_bulan_8',
                    data: 'tagihan_bulan_8',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_8 = data ?? 0;
                        return formatRupiah(tagihan_bulan_8);
                    }
                },
                {
                    data: 'tagihan_bulan_9',
                    data: 'tagihan_bulan_9',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_9 = data ?? 0;
                        return formatRupiah(tagihan_bulan_9);
                    }
                },
                {
                    data: 'tagihan_bulan_10',
                    data: 'tagihan_bulan_10',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_10 = data ?? 0;
                        return formatRupiah(tagihan_bulan_10);
                    }
                },
                {
                    data: 'tagihan_bulan_11',
                    data: 'tagihan_bulan_11',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_11 = data ?? 0;
                        return formatRupiah(tagihan_bulan_11);
                    }
                },
                {
                    data: 'tagihan_bulan_12',
                    data: 'tagihan_bulan_12',
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var tagihan_bulan_12 = data ?? 0;
                        return formatRupiah(tagihan_bulan_12);
                    }
                },
                {
                    data: null,
                    class: 'text-center text-black fw-bolder aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        var totalTagihan = parseInt(data.tagihan_bulan_1 ?? 0) + parseInt(data.tagihan_bulan_2 ?? 0) + parseInt(data.tagihan_bulan_3 ?? 0) + parseInt(data.tagihan_bulan_4 ?? 0) + parseInt(data.tagihan_bulan_5 ?? 0) + parseInt(data.tagihan_bulan_6 ?? 0) + parseInt(data.tagihan_bulan_7 ?? 0) + parseInt(data.tagihan_bulan_8 ?? 0) + parseInt(data.tagihan_bulan_9 ?? 0) + parseInt(data.tagihan_bulan_10 ?? 0) + parseInt(data.tagihan_bulan_11 ?? 0) + parseInt(data.tagihan_bulan_12 ?? 0);
                        return formatRupiah(totalTagihan);
                    }
                },
                {
                    data: null,
                    class: 'text-center text-black aling-middle py-1 fw-bolder small',
                    render: function(data) {
                        return '<button class="btn btn-primary btn-sm" onclick="detail(' + data.id_kelurahan + ')">Detail</button>';
                    }
                }
            ]
        });
    });

    function formatRupiah(angka) {
        var rupiah = '';
        var angkarev = angka.toString().split('').reverse().join('');
        for (var i = 0; i < angkarev.length; i++) {
            if (i % 3 == 0) rupiah += angkarev.substr(i, 3) + '.';
        }
        return 'Rp. ' + rupiah.split('', rupiah.length - 1).reverse().join('');
    }

    function detail(id_kec) {
        $.ajax({
            type: "get",
            url: "url",
            data: {
                id_kec: id_kec,
                dateStart: $('#dateStart').val(),
                dateEnd: $('#dateEnd').val(),
                statusLaporan: $('#statusLaporan').val(),
            },
            dataType: "json",
            success: function(response) {

            }
        });
    }
</script>