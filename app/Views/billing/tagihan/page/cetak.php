<div class="tab-pane fade" id="tab-cetak" role="tabpanel">
    <div class="row g-2">
        <div class="col-lg-2 col-6">
            <label class="form-label">Bulan</label>
            <select id="ctBulan" class="form-select form-select-sm">
                <?php
                $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= (int) date('n') === $i ? 'selected' : '' ?>><?= $namaBulan[$i] ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-lg-2 col-6">
            <label class="form-label">Tahun</label>
            <select id="ctTahun" class="form-select form-select-sm">
                <?php for ($i = (int) date('Y'); $i >= 2009; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-lg-3 col-8">
            <label class="form-label">Kelurahan/Desa</label>
            <select id="ctKelurahan" class="form-select form-select-sm">
                <option value="0">[ Semua Kelurahan ]</option>
            </select>
        </div>
        <div class="col-lg-2 col-4 d-flex align-items-end">
            <button type="button" class="btn btn-sm btn-danger w-100" id="ctCetak">
                <i class="fa fa-file-pdf"></i> PDF
            </button>
        </div>
    </div>

    <p class="text-muted fs-2 mt-3 mb-0">
        Cetak rekap tagihan per kelurahan. Hasil PDF dibuka di tab baru.
    </p>
</div>

<script>
(function() {
    $.ajax({
        type: 'get',
        url: "<?= base_url('Billing/Tagihan_data/kelurahan') ?>",
        dataType: 'json',
        success: function(list) {
            if (!Array.isArray(list)) {
                return;
            }

            $.each(list, function(i, item) {
                $('#ctKelurahan').append('<option value="' + item.id + '">' + item.text + '</option>');
            });

            $('#ctKelurahan').select2({
                width: '100%',
                dropdownParent: $('#tab-cetak'),
            });
        },
    });

    $('#ctCetak').click(function() {
        const url = "<?= base_url('Billing/Tagihan/cetak') ?>" +
            '?bulan=' + $('#ctBulan').val() +
            '&tahun=' + $('#ctTahun').val() +
            '&kelurahan=' + ($('#ctKelurahan').val() || 0);

        window.open(url, '_blank');
    });
})();
</script>
