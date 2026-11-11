<div class="text-center">
    <h6 class="text-black fw-semibold">PETUGAS : <span id="title-petugas"></span></h6>
</div>

<div class="table-responsive">
    <table id="table" class="table table-sm table-bordered border-dark table-striped table-hover mb-0 w-100">
        <thead>
            <tr class="text-center text-black align-middle">
                <td class="border-top border-dark py-1">#</td>
                <td class="border-top border-dark py-1">NO. SAMBUNG</td>
                <td class="border-top border-dark py-1">PELANGGAN</td>
                <td class="border-top border-dark py-1">REKENING</td>
                <td class="border-top border-dark py-1">HARGA AIR</td>
                <td class="border-top border-dark py-1">BEA. ADMIN</td>
                <td class="border-top border-dark py-1">JUMLAH</td>
                <td class="border-top border-dark py-1">PMK</td>
                <td class="border-top border-dark py-1">BULAN TAGIHAN</td>
                <td class="border-top border-dark py-1">KET</td>
                <td class="border-top border-dark py-1">USER</td>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        get_select_petugas();

        $('#dateStart, #dateEnd, #statusLaporan, .filter-per-petugas #petugas').change(function(e) {
            e.preventDefault();
            $('#table').DataTable().ajax.reload();
        });

        $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: "get",
                url: "<?= base_url('Laporan/DrdAir/datatable') ?>",
                data: function(data) {
                    data.jenisLaporan = $('#jenisLaporan').val();
                    data.dateStart = $('#dateStart').val();
                    data.dateEnd = $('#dateEnd').val();
                    data.statusLaporan = $('#statusLaporan').val();
                    data.petugas = $('.filter-per-petugas #petugas').val();
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
                    data: 'no_sambungan',
                    data: 'no_sambungan',
                    class: 'text-center text-black aling-middle py-1',
                },
                {
                    data: 'nama_pelanggan',
                    name: 'nama_pelanggan',
                    class: 'text-black aling-middle py-1'
                },
                {
                    data: 'no_rek',
                    data: 'no_rek',
                    class: 'text-center text-black aling-middle py-1',
                },
                {
                    data: 'hargaAir',
                    data: 'hargaAir',
                    class: 'text-center text-black aling-middle py-1',
                },
                {
                    data: 'by_admin',
                    data: 'by_admin',
                    class: 'text-center text-black aling-middle py-1',
                },
                {
                    data: 'jlh_tagihan',
                    data: 'jlh_tagihan',
                    class: 'text-center text-black aling-middle py-1',
                },
                {
                    data: 'pemakaian',
                    data: 'pemakaian',
                    class: 'text-center text-black aling-middle py-1',
                },
                {
                    data: 'bulan_tagihan',
                    data: 'bulan_tagihan',
                    class: 'text-center text-black aling-middle py-1',
                },
                {
                    data: 'jenis_pelanggan',
                    data: 'jenis_pelanggan',
                    class: 'text-center text-black aling-middle py-1',
                },
                {
                    data: 'user',
                    data: 'user',
                    class: 'text-center text-black aling-middle py-1',
                    render: function(data) {
                        return `<span class="badge bg-dark rounded-pill fs-1 aling-middle"><i class="fas fa-user me-1"></i> ${data}</span>`;
                    }
                },
            ]
        });
    });

    function get_select_petugas() {
        $.ajax({
            type: "get",
            url: "<?= base_url('GetData/getPenagihByTagihan') ?>",
            dataType: "json",
            success: function(response) {
                var html = '';
                html += '<option value="all" selected>[ Semua Petugas ]</option>';
                $.each(response, function(key, value) {
                    html += '<option value="' + value.id + '">' + value.text + '</option>';
                });
                $('.filter-per-petugas #petugas').html(html);
                $('.filter-per-petugas #petugas').select2({
                    width: '100%'
                });
            }
        });
    }
</script>