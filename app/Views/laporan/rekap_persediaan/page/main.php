<div class="text-center">
    <h6 class="text-black fw-semibold text-uppercase">PERIODE : <span id="title-periode" class="text-uppercase"></span></h6>
</div>

<div class="table-responsive">
    <table id="table" class="table table-sm table-bordered border-dark table-striped table-hover mb-0 w-100">
        <thead>
            <tr class="text-center text-black align-middle">
                <th class="border-top border-dark py-1" width="4%">#</th>
                <th class="border-top border-dark py-1" width="12%">KODE BARANG</th>
                <th class="border-top border-dark py-1">NAMA BARANG</th>
                <th class="border-top border-dark py-1" width="8%">SATUAN</th>
                <th class="border-top border-dark py-1" width="10%">STOK AWAL</th>
                <th class="border-top border-dark py-1" width="8%">MASUK</th>
                <th class="border-top border-dark py-1" width="8%">KELUAR</th>
                <th class="border-top border-dark py-1" width="10%">STOK AKHIR</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        let bulan = $('#bulan option:selected').val();
        let tahun = $('#tahun option:selected').val();

        $('#title-periode').text($('#bulan option:selected').text().trim() + ' ' + tahun);

        $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: 'post',
                url: "<?= base_url('Laporan/RekapPersediaan/datatable') ?>",
                data: function(d) {
                    d.bulan = $('#bulan').val();
                    d.tahun = $('#tahun').val();
                },
            },
            columns: [
                {
                    data: null,
                    class: 'text-center align-middle py-1',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                { data: 'barang_kode', class: 'align-middle py-1' },
                { data: 'barang_nama', class: 'align-middle py-1 text-uppercase' },
                { data: 'satuan', class: 'text-center align-middle py-1' },
                { data: 'stok_awal', class: 'text-end align-middle py-1', searchable: false },
                { data: 'masuk', class: 'text-end align-middle py-1 text-success', searchable: false },
                { data: 'keluar', class: 'text-end align-middle py-1 text-danger', searchable: false },
                { data: 'stok_akhir', class: 'text-end align-middle py-1 fw-semibold', searchable: false },
            ],
            order: [[2, 'asc']],
            pageLength: 25,
            language: {
                emptyTable: 'Belum ada data barang.',
                zeroRecords: 'Barang tidak ditemukan.',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ baris',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ barang',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
            },
        });
    });
</script>
