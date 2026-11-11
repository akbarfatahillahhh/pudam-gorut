<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>
<div class="card card-body border border-dark">
    <h6 class="fw-semibold mb-0 text-dark"><?= $title ?></h6>

    <ul class="nav nav-tabs w-100 border-0 mt-3 mb-4 p-0" id="myTab" role="tablist">
        <li class="nav-item w-50 px-1" role="presentation">
            <button class="nav-link w-100 border border-dark active text-uppercase rounded" id="daftar-pengeluaran-tab"
                data-bs-toggle="tab" data-bs-target="#daftar-pengeluaran" type="button" role="tab"
                aria-controls="daftar-pengeluaran" aria-selected="true">Daftar KAS KELUAR</button>
        </li>
        <li class="nav-item w-50 px-1" role="presentation">
            <button class="nav-link w-100 border border-dark text-uppercase rounded" id="pengeluaran-hari-ini-tab"
                data-bs-toggle="tab" data-bs-target="#pengeluaran-hari-ini" type="button" role="tab"
                aria-controls="pengeluaran-hari-ini" aria-selected="false">Rekap Bulanan
            </button>
        </li>
    </ul>

    <div class="tab-content border border-dark rounded" id="myTabContent">
        <div class="tab-pane fade show active" id="daftar-pengeluaran" role="tabpanel"
            aria-labelledby="daftar-pengeluaran-tab">
            <div class="card-body p-3">

                <div class="">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="date" class="form-control form-control-sm-off text-center" id="tanggalAwal" name="tanggal"
                                value="<?= date('Y-m-01') ?>">
                            <button class="bg-dark text-white small px-2" type="submit" disabled>S/D</button>
                            <input type="date" class="form-control form-control-sm-off text-center" id="tanggalAkhir" name="tanggal"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div class="table-responsive my-3">
                    <table
                        class="table table-sm table-bordered table-hover table-striped border-dark text-nowrap mb-0 align-middle pt-3"
                        id="tablePengeluaran">
                        <thead class=" bg-dark">
                            <tr>
                                <th class="text-center align-middle text-white">#</th>
                                <th class="text-center align-middle text-white">NO. VOUCHER</th>
                                <th class="text-center align-middle text-white">PERKIRAAN</th>
                                <th class="text-center align-middle text-white">URAIAN</th>
                                <th class="text-center align-middle text-white">NO. PERKIRAAN</th>
                                <th class="text-center align-middle text-white">TANGGAL</th>
                                <th class="text-center align-middle text-white">STATUS</th>
                                <th class="text-center align-middle text-white">#</th>
                            </tr>
                        </thead>
                        <tbody class="text-black">
                            <td colspan="100%" class="text-center">Tidak Ada Data</td>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pengeluaran-hari-ini" role="tabpanel" aria-labelledby="pengeluaran-hari-ini-tab">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <input type="month" id="filterDate" class="form-control" value="<?= date('Y-m') ?>">
                    </div>
                    <div class="col-6">
                        <select class="form-select" id="filterLaporan">
                            <option value="" selected>[FILTER LAPORAN]</option>
                            <option value="jbk">JURNAL BAYAR KAS</option>
                            <option value="rjbk">REKAP JURNAL BAYAR KAS</option>
                            <option value="dhhd">DAFTAR HUTANG YANG HARUS DI BAYAR</option>
                            <option value="djhhd">DAFTAR JURNAL VOUCHER YANG HARUS DI BAYAR</option>
                        </select>
                    </div>
                    <div class="card card-body border border-dark my-4" id="panel-pdf-none">
                        <div class="text-center py-5">
                            <i class="fas fa-file-pdf text-dark h1"></i>
                            <h5 class="text-dark">PREVIEW PDF</h5>
                        </div>
                    </div>
                    <div class="card card-body border border-dark my-4" id="panel-pdf-display" style="display: none">
                        <iframe src="" frameborder="0" width="100%" height="600px" id="pdf"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-placement"></div>
<?= $this->endSection() ?>
<?= $this->section('script') ?>
<script>
$('#tablePengeluaran').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        type: "post",
        url: "<?= base_url('E_Kas/Pengeluaran/EntryKas_Keluar/datatable') ?>",
        data: function(data) {
            data.tanggalAwal = $('#tanggalAwal').val()
            data.tanggalAkhir = $('#tanggalAkhir').val()
        },
    },
    columns: [{
            render: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            },
            class: "text-center align-middle py-1 text-dark",
            width: "3%",
            searchable: false
        },
        {
            data: "NOVOUCHER",
            class: "text-center align-middle py-1 text-dark",
        },
        {
            data: "GROUPPERKIRAAN",
            class: "text-start align-middle py-1 text-dark",
        },
        {
            data: "KETERANGAN",
            class: "text-start align-middle py-1 text-dark",
        },
        {
            data: "NOMOR_PERKIRAAN",
            class: "text-center align-middle py-1 text-dark",
        },
        {
            data: "TANGGAL",
            class: "text-center align-middle py-1 text-dark",
        },
        {
            data: "STATUS",
            class: "text-start align-middle py-1 text-dark",
            render: function(data, type, row, meta) {
                return data === 'Y' ? "Pengeluaran Bukan Hutang Lalu" :
                    "Pengeluaran Hutang Lalu";
            }
        },
        {
            data: "aksi",
            class: "text-center align-middle py-1 text-dark",
            width: "3%",
            searchable: false
        },
    ],
});

function voucher(warna, nomor) {
    $.ajax({
        type: "post",
        url: "<?= base_url('E_Kas/Pengeluaran/EntryKas_Keluar/voucher') ?>",
        data: {
            warna: warna,
            nomor: nomor
        },
        dataType: "json",
        success: function(response) {
            $('.modal-placement').html("").html(response.html);
            $('#modal').modal('show');
        }
    });
}

$('#tanggalAwal, #tanggalAkhir').change(function(e) {
    e.preventDefault();
    $('#tablePengeluaran').DataTable().ajax.reload();
});

function hapus(nomor) {
    Swal.fire({
        title: 'Apakah anda yakin?',
        text: "Data akan dihapus!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: "<?= base_url('E_Kas/Pengeluaran/EntryKas_Keluar/hapus') ?>",
                data: {
                    nomor: nomor
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.fire('Berhasil', response.message, 'success');
                        $('#tablePengeluaran').DataTable().ajax.reload();
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                }
            });
        }
    })
}

function kwitansi(nomor) {
    $.ajax({
        type: "post",
        url: "<?= base_url('E_Kas/Pengeluaran/EntryKas_Keluar/kwitansi') ?>",
        data: {
            nomor: nomor
        },
        dataType: "json",
        success: function(response) {
            $('.modal-placement').html("").html(response.html);
            $('#modal').modal('show');
        }
    });
}

$('#filterLaporan').select2({
    width: '100%',
});


$('#filterLaporan,#filterDate').change(function(e) {
    if ($('#filterLaporan').val() == '') {
        Swal.fire('Gagal', 'Pilih Jenis Laporan Terlebih Dahulu', 'warning');
        return false;
    }
    e.preventDefault();
    $('#panel-pdf-none').hide();
    $('#panel-pdf-display').show();

    $.ajax({
        type: "POST",
        url: "<?= base_url('E_Kas/Pengeluaran/EntryKas_Keluar/pdfRekapan') ?>",
        data: {
            filterLaporan: $('#filterLaporan').val(),
        },
        dataType: "json",
        success: function(response) {
            var filterDate = $('#filterDate').val().split(' - ');
            var tanggal1 = filterDate[0];
            $('#pdf').attr('src',
                '<?= base_url('E_Kas/Pengeluaran/EntryKas_Keluar/pdfPreview') ?>' + '/' +
                response.path + '/' + tanggal1);
        }
    });
});
</script>
<?= $this->endSection() ?>