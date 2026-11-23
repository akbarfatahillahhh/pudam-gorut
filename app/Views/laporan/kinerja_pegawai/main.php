<?= $this->extend('layouts/main_ekas') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-4"><?= esc($title) ?></h5>

        <div class="row">
            <div class="col-lg-3 col-6 mb-2">
                <label class="form-label fw-semibold">Dari Tanggal</label>
                <input type="date" id="tglAwal" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="col-lg-3 col-6 mb-2">
                <label class="form-label fw-semibold">Sampai Tanggal</label>
                <input type="date" id="tglAkhir" class="form-control form-control-sm" value="<?= date('Y-m-t') ?>">
            </div>
            <div class="col-lg-3 col-12 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-primary" id="btnTampil">
                    <i class="ti ti-search"></i>
                    Tampilkan
                </button>
            </div>
        </div>

        <div class="table-responsive mt-2">
            <table id="tableKinerja" class="table table-sm table-bordered border-dark align-middle mb-0">
                <thead class="text-black fs-2">
                    <tr>
                        <th class="text-center" width="4%">#</th>
                        <th class="text-center" width="14%">NIK</th>
                        <th>NAMA PEGAWAI</th>
                        <th class="text-center" width="10%">STATUS</th>
                        <th class="text-center" width="14%">JML KEGIATAN</th>
                        <th class="text-center" width="16%">KEGIATAN TERAKHIR</th>
                        <th class="text-center" width="8%">#</th>
                    </tr>
                </thead>
                <tbody class="text-black fs-2"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
    let tabelKinerja = null;

    $(document).ready(function() {
        tabelKinerja = $('#tableKinerja').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                type: 'get',
                url: "<?= base_url('ekas/laporan/kinerja-pegawai/data') ?>",
                data: function(data) {
                    data.tglAwal = $('#tglAwal').val();
                    data.tglAkhir = $('#tglAkhir').val();
                },
            },
            columns: [
                { data: 'nourut', class: 'text-center align-middle' },
                { data: 'NIK', class: 'text-center align-middle' },
                { data: 'NAMA', class: 'align-middle' },
                {
                    data: 'STATUS',
                    class: 'text-center align-middle',
                    render: function(data) {
                        return data === 'Aktif'
                            ? '<span class="badge bg-success">Aktif</span>'
                            : '<span class="badge bg-danger">Non Aktif</span>';
                    }
                },
                { data: 'JUMLAH_KEGIATAN', class: 'text-center align-middle fw-semibold' },
                { data: 'KEGIATAN_TERAKHIR', class: 'text-center align-middle' },
                {
                    data: null,
                    class: 'text-center align-middle',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return '<button type="button" class="btn btn-sm btn-outline-primary" ' +
                            'onclick="lihatKegiatan(' + row.ID + ')">Lihat</button>';
                    }
                },
            ],
            order: [[2, 'asc']],
            pageLength: 25,
            language: {
                emptyTable: 'Tidak ada data pegawai.',
                zeroRecords: 'Pegawai tidak ditemukan.',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ baris',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ pegawai',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
            },
        });

        $('#btnTampil').click(function() {
            tabelKinerja.ajax.reload();
        });
    });

    function lihatKegiatan(idPegawai) {
        $.ajax({
            type: 'get',
            url: "<?= base_url('ekas/laporan/kinerja-pegawai/detail') ?>",
            data: {
                id_pegawai: idPegawai,
                tglAwal: $('#tglAwal').val(),
                tglAkhir: $('#tglAkhir').val(),
            },
            dataType: 'json',
            success: function(response) {
                if (response.status !== 'success') {
                    Swal.fire('Gagal', response.msg ?? 'Data tidak ditemukan.', 'error');
                    return;
                }

                const html = `
                    <div class="modal fade" id="modalKinerja" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title fw-semibold">Kegiatan ${response.pegawai}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fs-2 text-muted">
                                            ${$('#tglAwal').val()} s/d ${$('#tglAkhir').val()}
                                            &mdash; ${response.total} kegiatan
                                        </span>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="cetakPdf(${idPegawai})">
                                            <i class="fa fa-file-pdf"></i> PDF
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered border-dark align-middle mb-0">
                                            <thead class="text-black fs-2">
                                                <tr>
                                                    <th class="text-center" width="4%">#</th>
                                                    <th class="text-center" width="14%">TANGGAL</th>
                                                    <th>TUGAS POKOK</th>
                                                    <th>URAIAN KEGIATAN</th>
                                                    <th class="text-center" width="14%">STATUS</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-black fs-2">${response.html}</tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;

                $('.modal-place').html(html);
                $('#modalKinerja').modal('show');
            },
            error: function() {
                Swal.fire('Gagal', 'Tidak bisa mengambil rincian kegiatan.', 'error');
            },
        });
    }

    function cetakPdf(idPegawai) {
        const url = "<?= base_url('ekas/laporan/kinerja-pegawai/pdf') ?>/" + idPegawai +
            '?tglAwal=' + $('#tglAwal').val() + '&tglAkhir=' + $('#tglAkhir').val();
        window.open(url, '_blank');
    }
</script>
<?= $this->endSection() ?>
