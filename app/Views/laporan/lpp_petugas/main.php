<?= $this->extend('layouts/main_billing') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-3"><?= esc($title) ?></h5>
        <p class="text-muted fs-2 mb-3">
            Rekap penagihan per jenis pelanggan untuk satu petugas penagih, berdasarkan tanggal bayar.
        </p>

        <div class="row g-2 mb-3">
            <div class="col-lg-2 col-6">
                <label class="form-label">Tanggal Awal</label>
                <input type="date" id="lptAwal" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="col-lg-2 col-6">
                <label class="form-label">S/D Tanggal</label>
                <input type="date" id="lptAkhir" class="form-control form-control-sm" value="<?= date('Y-m-t') ?>">
            </div>
            <div class="col-lg-4 col-12">
                <label class="form-label">Petugas Penagih</label>
                <select id="lptPetugas" class="form-select form-select-sm">
                    <option value="">[ Pilih Petugas ]</option>
                </select>
            </div>
            <div class="col-lg-2 col-6 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-primary w-100" id="lptTampil">
                    <i class="ti ti-search"></i> Tampilkan
                </button>
            </div>
        </div>

        <div class="mb-2">
            <button class="btn btn-sm btn-danger" id="lptPdf" disabled>
                <i class="fa fa-file-pdf"></i> PDF
            </button>
        </div>

        <div id="lptKosong" class="text-center py-5">
            <i class="ti ti-user-check text-dark" style="font-size:3rem;"></i>
            <h6 class="text-dark mb-1">REKAP DRD PER PETUGAS</h6>
            <p class="text-muted mb-0">Pilih tanggal dan petugas, lalu klik Tampilkan.</p>
        </div>

        <div id="lptHasil" style="display:none;">
            <div class="mb-2">
                <div class="fs-5 fw-semibold text-uppercase" id="lptJudul">-</div>
                <div class="text-muted fs-2" id="lptInfo">-</div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered border-dark align-middle mb-0">
                    <thead class="text-black fs-2 text-center">
                        <tr>
                            <th rowspan="2" width="4%">NO</th>
                            <th rowspan="2">JENIS PELANGGAN</th>
                            <th rowspan="2" width="8%">TERPASANG</th>
                            <th rowspan="2" width="8%">DITAGIH</th>
                            <th colspan="3">KUBIKASI (M3)</th>
                            <th rowspan="2" width="12%">HARGA AIR</th>
                            <th rowspan="2" width="10%">BIAYA ADM</th>
                            <th rowspan="2" width="12%">JUMLAH</th>
                        </tr>
                        <tr>
                            <th width="7%">&le; 10</th>
                            <th width="7%">&gt; 10</th>
                            <th width="7%">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="text-black fs-2" id="lptBody"></tbody>
                    <tfoot class="text-black fs-2 fw-semibold bg-light" id="lptFoot"></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
(function() {
    function rupiah(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    function angka(n) {
        return Number(n || 0).toLocaleString('id-ID');
    }

    $.ajax({
        type: 'get',
        url: "<?= base_url('Laporan/LppPetugas/petugas') ?>",
        dataType: 'json',
        success: function(list) {
            if (!Array.isArray(list)) {
                return;
            }

            $.each(list, function(i, item) {
                $('#lptPetugas').append('<option value="' + item.id + '">' + item.text + '</option>');
            });

            $('#lptPetugas').select2({
                width: '100%',
                placeholder: '[ Pilih Petugas ]',
            });
        },
    });

    function parameter() {
        return {
            tgl_awal: $('#lptAwal').val(),
            tgl_akhir: $('#lptAkhir').val(),
            petugas: $('#lptPetugas').val(),
        };
    }

    $('#lptTampil').click(function() {
        const p = parameter();

        if (!p.petugas) {
            Swal.fire('Peringatan', 'Petugas wajib dipilih.', 'warning');
            return;
        }

        $.ajax({
            type: 'get',
            url: "<?= base_url('Laporan/LppPetugas/data') ?>",
            data: p,
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Loading...',
                    html: 'Sedang Diproses',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                });
                Swal.showLoading();
            },
            complete: function() {
                Swal.close();
            },
            success: function(res) {
                if (res.status !== 'success') {
                    Swal.fire('Gagal', res.msg, 'error');
                    return;
                }

                $('#lptKosong').hide();
                $('#lptHasil').show();

                const namaPetugas = (res.petugas && res.petugas.nama_lengkap)
                    ? res.petugas.nama_lengkap.toUpperCase()
                    : '-';
                const unit = (res.petugas && res.petugas.kecamatan)
                    ? res.petugas.kecamatan
                    : 'SEMUA UNIT';

                $('#lptJudul').text('REKAP DRD — ' + namaPetugas);
                $('#lptInfo').text(
                    'Unit: ' + unit + ' · Periode: ' + res.tgl_awal + ' s/d ' + res.tgl_akhir +
                    ' · ' + angka(res.total.rekening) + ' rekening ditagih'
                );

                let html = '';
                let adaData = false;

                $.each(res.baris, function(i, b) {
                    if (b.rekening > 0) {
                        adaData = true;
                    }

                    html += '<tr>' +
                        '<td class="text-center">' + (i + 1) + '</td>' +
                        '<td class="text-uppercase">' + (b.jenis ?? '-') + '</td>' +
                        '<td class="text-center">' + angka(b.terpasang) + '</td>' +
                        '<td class="text-center">' + angka(b.rekening) + '</td>' +
                        '<td class="text-center">' + angka(b.kub_bawah) + '</td>' +
                        '<td class="text-center">' + angka(b.kub_atas) + '</td>' +
                        '<td class="text-center fw-semibold">' + angka(b.kubikasi) + '</td>' +
                        '<td class="text-end">' + rupiah(b.harga_air) + '</td>' +
                        '<td class="text-end">' + rupiah(b.by_admin) + '</td>' +
                        '<td class="text-end fw-semibold">' + rupiah(b.jlh_tagihan) + '</td>' +
                        '</tr>';
                });

                $('#lptBody').html(html);

                $('#lptFoot').html(
                    '<tr>' +
                    '<td colspan="2" class="text-center">JUMLAH TOTAL</td>' +
                    '<td class="text-center">' + angka(res.total.terpasang) + '</td>' +
                    '<td class="text-center">' + angka(res.total.rekening) + '</td>' +
                    '<td class="text-center">' + angka(res.total.kub_bawah) + '</td>' +
                    '<td class="text-center">' + angka(res.total.kub_atas) + '</td>' +
                    '<td class="text-center">' + angka(res.total.kubikasi) + '</td>' +
                    '<td class="text-end">' + rupiah(res.total.harga_air) + '</td>' +
                    '<td class="text-end">' + rupiah(res.total.by_admin) + '</td>' +
                    '<td class="text-end">' + rupiah(res.total.jlh_tagihan) + '</td>' +
                    '</tr>'
                );

                $('#lptPdf').prop('disabled', !adaData);
            },
            error: function() {
                Swal.fire('Gagal', 'Data rekap tidak bisa diambil.', 'error');
            },
        });
    });

    $('#lptPdf').click(function() {
        const p = parameter();

        if (!p.petugas) {
            Swal.fire('Peringatan', 'Petugas wajib dipilih.', 'warning');
            return;
        }

        const url = "<?= base_url('Laporan/LppPetugas/export_pdf') ?>" +
            '?tgl_awal=' + p.tgl_awal +
            '&tgl_akhir=' + p.tgl_akhir +
            '&petugas=' + encodeURIComponent(p.petugas);

        window.open(url, '_blank');
    });
})();
</script>
<?= $this->endSection() ?>
