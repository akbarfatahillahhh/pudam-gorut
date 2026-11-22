<div class="tab-pane fade show active" id="tab-pemakaian" role="tabpanel">
    <div class="row">
        <div class="col-lg-5">
            <label class="form-label">Cari Pelanggan</label>
            <select id="pelangganPemakaian" class="form-select"></select>
        </div>
        <div class="col-lg-3">
            <label class="form-label">Tanggal Tagihan</label>
            <input type="date" id="tglPemakaian" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>

    <div class="row mt-3" id="panelPemakaian" style="display:none;">
        <div class="col-lg-6">
            <div class="border border-dark rounded p-3 h-100">
                <h6 class="fw-semibold mb-2">Detail Pelanggan</h6>
                <table class="table table-sm table-borderless mb-0 fs-2">
                    <tr><td class="text-muted">Nama</td><td class="text-end fw-semibold" id="pk-nama">-</td></tr>
                    <tr><td class="text-muted">No. Sambungan</td><td class="text-end fw-semibold" id="pk-sambungan">-</td></tr>
                    <tr><td class="text-muted">Golongan</td><td class="text-end fw-semibold" id="pk-golongan">-</td></tr>
                    <tr><td class="text-muted">Kelompok</td><td class="text-end fw-semibold" id="pk-kelompok">-</td></tr>
                    <tr><td class="text-muted">Wilayah</td><td class="text-end fw-semibold" id="pk-wilayah">-</td></tr>
                    <tr><td class="text-muted">Tarif 0-10 m3</td><td class="text-end fw-semibold" id="pk-tarif-bawah">-</td></tr>
                    <tr><td class="text-muted">Tarif &gt; 10 m3</td><td class="text-end fw-semibold" id="pk-tarif-atas">-</td></tr>
                    <tr><td class="text-muted">Bea Admin</td><td class="text-end fw-semibold" id="pk-bea-admin">-</td></tr>
                    <tr><td class="text-muted">Meter Terakhir</td><td class="text-end fw-semibold" id="pk-meter-terakhir">-</td></tr>
                </table>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="border border-dark rounded p-3 h-100">
                <h6 class="fw-semibold mb-2">Entri Pemakaian Air</h6>
                <form id="formPemakaian">
                    <div class="mb-2">
                        <label class="form-label">Angka Meter Lalu (m3)</label>
                        <input type="number" class="form-control" name="pemakaian_air_lalu" id="meterLalu" step="1" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Angka Meter Ini (m3)</label>
                        <input type="number" class="form-control" name="pemakaian_air" id="meterIni" step="1" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Perkiraan Tagihan</label>
                        <div class="border rounded bg-light p-2 fs-2" id="pratinjauTagihan">
                            Isi angka meter untuk melihat perhitungan.
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-sm btn-primary" id="btnSimpanPemakaian">
                            <i class="fa fa-save"></i> Terbitkan Rekening
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let dataPelanggan = null;

    function rupiah(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    $('#pelangganPemakaian').select2({
        width: '100%',
        placeholder: '[ Cari nomor sambungan atau nama pelanggan ]',
        minimumInputLength: 1,
        ajax: {
            url: "<?= base_url('Billing/Tagihan_data/pelanggan') ?>",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { search: params.term };
            },
            processResults: function(data) {
                return { results: data };
            },
        },
    });

    function muatPelanggan() {
        const id = $('#pelangganPemakaian').val();

        if (!id) {
            $('#panelPemakaian').hide();
            return;
        }

        $.ajax({
            type: 'get',
            url: "<?= base_url('Billing/Tagihan_data/pelanggan_detail') ?>",
            data: {
                id_pelanggan: id,
                tanggal: $('#tglPemakaian').val(),
            },
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success') {
                    Swal.fire('Gagal', res.msg, 'error');
                    return;
                }

                dataPelanggan = res.pelanggan;

                $('#pk-nama').text(res.pelanggan.nama_pelanggan ?? '-');
                $('#pk-sambungan').text(res.pelanggan.no_sambungan ?? '-');
                $('#pk-golongan').text((res.pelanggan.kd_jenis ?? '') + ' - ' + (res.pelanggan.jenis_pelanggan ?? ''));
                $('#pk-kelompok').text(res.pelanggan.kelompok_jasa ?? '-');
                $('#pk-wilayah').text([res.pelanggan.kecamatan, res.pelanggan.kelurahan].filter(Boolean).join(' / ') || '-');
                $('#pk-tarif-bawah').text(rupiah(res.pelanggan.tarif_bawah_10));
                $('#pk-tarif-atas').text(rupiah(res.pelanggan.tarif_atas_10));
                $('#pk-bea-admin').text(rupiah(res.bea_admin));

                const meter = res.meter_terakhir ? Number(res.meter_terakhir.pemakaian_air) : 0;
                $('#pk-meter-terakhir').text(res.meter_terakhir
                    ? meter.toLocaleString('id-ID') + ' m3'
                    : 'Belum ada');

                $('#meterLalu').val(meter);
                $('#meterIni').val('');
                dataPelanggan.bea_admin_efektif = res.bea_admin;
                dataPelanggan.sudah_ada = res.sudah_ada;

                pratinjau();
                $('#panelPemakaian').show();

                if (res.sudah_ada) {
                    Notyf ? new Notyf().error('Rekening periode ini sudah terbit untuk pelanggan tersebut.') : null;
                }
            },
            error: function() {
                Swal.fire('Gagal', 'Data pelanggan tidak bisa diambil.', 'error');
            },
        });
    }

    function pratinjau() {
        if (!dataPelanggan) {
            return;
        }

        const lalu = Number($('#meterLalu').val() || 0);
        const ini = Number($('#meterIni').val() || 0);

        if (ini < lalu) {
            $('#pratinjauTagihan').html('<span class="text-danger">Angka meter ini lebih kecil dari meter lalu.</span>');
            return;
        }

        const pakai = ini - lalu;
        const tarifBawah = Number(dataPelanggan.tarif_bawah_10 || 0);
        const tarifAtas = Number(dataPelanggan.tarif_atas_10 || 0);
        const bea = Number(dataPelanggan.bea_admin_efektif || 0);

        const tagBawah = 10 * tarifBawah;
        const tagAtas = pakai > 10 ? (pakai - 10) * tarifAtas : 0;
        const total = tagBawah + tagAtas + bea;

        $('#pratinjauTagihan').html(
            '<div class="d-flex justify-content-between"><span>Pemakaian</span><b>' + pakai.toLocaleString('id-ID') + ' m3</b></div>' +
            '<div class="d-flex justify-content-between"><span>Tarif 0-10 m3</span><b>' + rupiah(tagBawah) + '</b></div>' +
            '<div class="d-flex justify-content-between"><span>Tarif &gt; 10 m3</span><b>' + rupiah(tagAtas) + '</b></div>' +
            '<div class="d-flex justify-content-between"><span>Bea Admin</span><b>' + rupiah(bea) + '</b></div>' +
            '<hr class="my-1">' +
            '<div class="d-flex justify-content-between fs-3"><span>Jumlah Tagihan</span><b>' + rupiah(total) + '</b></div>'
        );
    }

    $('#pelangganPemakaian').on('change', muatPelanggan);
    $('#tglPemakaian').on('change', muatPelanggan);
    $('#meterLalu, #meterIni').on('input', pratinjau);

    $('#formPemakaian').submit(function(e) {
        e.preventDefault();

        const id = $('#pelangganPemakaian').val();
        if (!id) {
            Swal.fire('Peringatan', 'Pilih pelanggan dulu.', 'warning');
            return;
        }

        $.ajax({
            type: 'post',
            url: "<?= base_url('Billing/Tagihan/simpan_pemakaian') ?>",
            data: $(this).serialize() + '&id_pelanggan=' + id + '&tanggal=' + $('#tglPemakaian').val(),
            dataType: 'json',
            beforeSend: function() {
                $('#btnSimpanPemakaian').prop('disabled', true);
            },
            complete: function() {
                $('#btnSimpanPemakaian').prop('disabled', false);
            },
            success: function(res) {
                if (res.status !== 'success') {
                    Swal.fire('Gagal', res.msg, 'error');
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Rekening Terbit',
                    html: 'No. Rekening <b>' + res.no_rek + '</b><br>Jumlah tagihan <b>Rp ' +
                        Number(res.tagihan.jumlah).toLocaleString('id-ID') + '</b>',
                    showCancelButton: true,
                    confirmButtonText: 'Cetak Kwitansi',
                    cancelButtonText: 'Selesai',
                }).then(function(r) {
                    if (r.isConfirmed) {
                        window.open(
                            "<?= base_url('billing/loket/kwitansi_pdf') ?>/" + res.id_pemakaian,
                            '_blank'
                        );
                    }

                    $('#formPemakaian')[0].reset();
                    $('#pelangganPemakaian').val(null).trigger('change');
                    $('#panelPemakaian').hide();
                    dataPelanggan = null;
                });
            },
            error: function() {
                Swal.fire('Gagal', 'Rekening gagal diterbitkan.', 'error');
            },
        });
    });
})();
</script>
