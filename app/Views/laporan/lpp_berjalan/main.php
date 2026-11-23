<?= $this->extend('layouts/main_billing') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-3"><?= esc($title) ?></h5>
        <p class="text-muted fs-2 mb-3">
            Daftar tagihan lunas per kelurahan dalam rentang bulan terpilih.
        </p>

        <div class="row g-2 mb-3">
            <div class="col-lg-2 col-6">
                <label class="form-label">Tahun</label>
                <select id="lppTahun" class="form-select form-select-sm">
                    <?php for ($i = (int) date('Y'); $i >= 2009; $i--): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-lg-2 col-6">
                <label class="form-label">Bulan Awal</label>
                <select id="lppBulanAwal" class="form-select form-select-sm">
                    <?php
                    $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= (int) date('n') === $i - 1 ? 'selected' : '' ?>><?= $namaBulan[$i] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-lg-2 col-6">
                <label class="form-label">S/D Bulan</label>
                <select id="lppBulanAkhir" class="form-select form-select-sm">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= (int) date('n') === $i ? 'selected' : '' ?>><?= $namaBulan[$i] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-lg-4 col-12">
                <label class="form-label">Kelurahan/Desa</label>
                <select id="lppKelurahan" class="form-select form-select-sm">
                    <option value="">[ Pilih Kelurahan ]</option>
                </select>
            </div>
            <div class="col-lg-2 col-12 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-primary w-100" id="lppTampil">
                    <i class="ti ti-search"></i> Tampilkan
                </button>
            </div>
        </div>

        <div class="mb-2">
            <button class="btn btn-sm btn-danger" id="lppPdf" disabled>
                <i class="fa fa-file-pdf"></i> PDF
            </button>
        </div>

        <div id="lppKosong" class="text-center py-5">
            <i class="ti ti-file-report text-dark" style="font-size:3rem;"></i>
            <h6 class="text-dark mb-1">LPP AIR PERIODIK</h6>
            <p class="text-muted mb-0">Pilih periode dan kelurahan, lalu klik Tampilkan.</p>
        </div>

        <div id="lppHasil" style="display:none;">
            <div class="d-flex justify-content-between align-items-end mb-2">
                <div>
                    <div class="fs-5 fw-semibold text-uppercase" id="lppJudul">-</div>
                    <div class="text-muted fs-2" id="lppPeriode">-</div>
                </div>
                <div class="text-end fs-2 text-muted" id="lppJumlah">-</div>
            </div>

            <div class="table-responsive" style="max-height: 60vh; overflow:auto;">
                <table class="table table-sm table-bordered border-dark align-middle mb-0">
                    <thead class="text-black fs-2">
                        <tr>
                            <th class="text-center" width="4%">#</th>
                            <th class="text-center" width="12%">NO SAMBUNGAN</th>
                            <th>NAMA PELANGGAN</th>
                            <th class="text-center" width="8%">REKENING</th>
                            <th class="text-center" width="12%">BULAN</th>
                            <th class="text-center" width="12%">HARGA AIR</th>
                            <th class="text-center" width="10%">BIAYA ADM</th>
                            <th class="text-center" width="12%">JUMLAH TAGIHAN</th>
                            <th class="text-center" width="8%">PAKAI (M3)</th>
                        </tr>
                    </thead>
                    <tbody class="text-black fs-2" id="lppBody"></tbody>
                    <tfoot class="text-black fs-2 fw-semibold" id="lppFoot"></tfoot>
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

    $.ajax({
        type: 'get',
        url: "<?= base_url('Laporan/LppBerjalan/kelurahan') ?>",
        dataType: 'json',
        success: function(list) {
            if (!Array.isArray(list)) {
                return;
            }

            $.each(list, function(i, item) {
                $('#lppKelurahan').append('<option value="' + item.id + '">' + item.text + '</option>');
            });

            $('#lppKelurahan').select2({
                width: '100%',
                placeholder: '[ Pilih Kelurahan ]',
            });
        },
    });

    function parameter() {
        return {
            tahun: $('#lppTahun').val(),
            bulan_awal: $('#lppBulanAwal').val(),
            bulan_akhir: $('#lppBulanAkhir').val(),
            kelurahan: $('#lppKelurahan').val(),
        };
    }

    $('#lppTampil').click(function() {
        const p = parameter();

        if (!p.kelurahan) {
            Swal.fire('Peringatan', 'Kelurahan wajib dipilih.', 'warning');
            return;
        }

        $.ajax({
            type: 'get',
            url: "<?= base_url('Laporan/LppBerjalan/data') ?>",
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

                $('#lppKosong').hide();

                if (!res.rows || res.rows.length === 0) {
                    $('#lppHasil').show();
                    $('#lppJudul').text('Tidak ada tagihan lunas');
                    $('#lppPeriode').text(res.periode);
                    $('#lppJumlah').text('');
                    $('#lppBody').html('<tr><td colspan="9" class="text-center">Tidak Ada Data</td></tr>');
                    $('#lppFoot').html('');
                    $('#lppPdf').prop('disabled', true);
                    return;
                }

                const wilayah = res.kelurahan
                    ? 'Desa ' + res.kelurahan.kelurahan + (res.kelurahan.kecamatan ? ' — Kec. ' + res.kelurahan.kecamatan : '')
                    : '';

                $('#lppJudul').text('LPP AIR PERIODIK — ' + wilayah);
                $('#lppPeriode').text('Periode: ' + res.periode);
                $('#lppJumlah').text(res.rows.length + ' rekening');

                let html = '';

                $.each(res.rows, function(i, r) {
                    html += '<tr>' +
                        '<td class="text-center">' + r.no + '</td>' +
                        '<td class="text-center">' + (r.no_sambungan ?? '-') + '</td>' +
                        '<td class="text-uppercase">' + (r.nama_pelanggan ?? '-') + '</td>' +
                        '<td class="text-center">' + (r.no_rek ?? '-') + '</td>' +
                        '<td class="text-center">' + r.bulan_text + '</td>' +
                        '<td class="text-end">' + rupiah(r.harga_air) + '</td>' +
                        '<td class="text-end">' + rupiah(r.by_admin) + '</td>' +
                        '<td class="text-end fw-semibold">' + rupiah(r.jlh_tagihan) + '</td>' +
                        '<td class="text-center">' + r.pemakaian + '</td>' +
                        '</tr>';
                });

                $('#lppBody').html(html);

                $('#lppFoot').html(
                    '<tr>' +
                    '<td colspan="5" class="text-end">Jumlah Total</td>' +
                    '<td class="text-end">' + rupiah(res.total.harga_air) + '</td>' +
                    '<td class="text-end">' + rupiah(res.total.by_admin) + '</td>' +
                    '<td class="text-end">' + rupiah(res.total.jlh_tagihan) + '</td>' +
                    '<td class="text-center">' + res.total.pemakaian_air + '</td>' +
                    '</tr>'
                );

                $('#lppHasil').show();
                $('#lppPdf').prop('disabled', false);
            },
            error: function() {
                Swal.fire('Gagal', 'Data LPP tidak bisa diambil.', 'error');
            },
        });
    });

    $('#lppPdf').click(function() {
        const p = parameter();

        if (!p.kelurahan) {
            Swal.fire('Peringatan', 'Kelurahan wajib dipilih.', 'warning');
            return;
        }

        const url = "<?= base_url('Laporan/LppBerjalan/export_pdf') ?>" +
            '?tahun=' + p.tahun +
            '&bulan_awal=' + p.bulan_awal +
            '&bulan_akhir=' + p.bulan_akhir +
            '&kelurahan=' + p.kelurahan;

        window.open(url, '_blank');
    });
})();
</script>
<?= $this->endSection() ?>
