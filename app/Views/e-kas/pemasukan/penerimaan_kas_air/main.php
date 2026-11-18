<?= $this->extend('layouts/main_ekas') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-4"><?= esc($title) ?></h5>

        <div class="row">
            <div class="col-lg-3 col-6 mb-2">
                <label class="form-label fw-semibold">Tanggal Penagihan Awal</label>
                <input type="date" id="tglAwal" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="col-lg-3 col-6 mb-2">
                <label class="form-label fw-semibold">Sampai Dengan</label>
                <input type="date" id="tglAkhir" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-lg-4 col-12 mb-2">
                <label class="form-label fw-semibold">Unit Penagihan</label>
                <select class="form-select" id="unit"></select>
            </div>
            <div class="col-lg-2 col-12 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-success w-100" id="btnTarik">
                    <i class="ti ti-cloud-download"></i>
                    Tarik Data Billing
                </button>
            </div>
        </div>

        <div class="alert alert-warning text-black small py-2 mb-3">
            <i class="ti ti-alert-triangle"></i>
            Tarik data billing terlebih dahulu, periksa nominalnya, lalu proses simpan. Penerimaan yang sudah
            disimpan tidak otomatis dihapus walaupun data billing berubah.
        </div>

        <form id="formPenerimaan">
            <div class="row">
                <div class="col-lg-3 col-6 mb-2">
                    <label class="form-label fw-semibold">Tanggal Penerimaan</label>
                    <input type="date" name="tgl_penerimaan" id="tglPenerimaan" class="form-control form-control-sm"
                        value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-lg-3 col-6 mb-2">
                    <label class="form-label fw-semibold">Nomor Bukti</label>
                    <input type="text" name="nomor_bukti" class="form-control form-control-sm"
                        placeholder="Kosongkan untuk otomatis">
                </div>
                <div class="col-lg-4 col-12 mb-2">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control form-control-sm"
                        value="Penerimaan Air" maxlength="150">
                </div>
                <div class="col-lg-2 col-12 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100" id="btnSimpan" disabled>
                        <i class="ti ti-device-floppy"></i>
                        Proses
                    </button>
                </div>
            </div>

            <div class="border border-dark rounded p-3" id="areaHasil">
                <div class="text-center py-5">
                    <i class="ti ti-database-import text-dark" style="font-size: 3rem;"></i>
                    <h5 class="text-dark mb-1">REKAP PENERIMAAN KAS AIR</h5>
                    <p class="text-muted mb-0">Pilih periode dan unit penagihan, lalu klik Tarik Data Billing.</p>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal-place"></div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
    const formatRupiah = new Intl.NumberFormat('id-ID');

    $(document).ready(function() {
        $('#unit').select2({
            width: '100%',
            allowClear: true,
            placeholder: '[ Cari dan Pilih Unit Penagihan ]',
            ajax: {
                url: "<?= base_url('GetData/getUnitPenagihan') ?>",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.text
                            };
                        })
                    };
                }
            }
        });
    });

    $('#btnTarik').click(function(e) {
        e.preventDefault();

        const tglAwal = $('#tglAwal').val();
        const tglAkhir = $('#tglAkhir').val();
        const unit = $('#unit').val();

        if (tglAwal === '') {
            $('#tglAwal').addClass('is-invalid').trigger('focus');
            return;
        }
        $('#tglAwal').removeClass('is-invalid');

        if (tglAkhir === '') {
            $('#tglAkhir').addClass('is-invalid').trigger('focus');
            return;
        }
        $('#tglAkhir').removeClass('is-invalid');

        if (unit === null || unit === '') {
            Swal.fire('Peringatan', 'Pilih unit penagihan terlebih dahulu.', 'warning');
            return;
        }

        tarikData(tglAwal, tglAkhir, unit);
    });

    function tarikData(tglAwal, tglAkhir, unit) {
        $.ajax({
            type: 'post',
            url: "<?= base_url('ekas/masuk/tampil') ?>",
            data: {
                tglawal: tglAwal,
                tglakhir: tglAkhir,
                unit: unit
            },
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Memuat...',
                    html: 'Menarik data billing',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            complete: function() {
                Swal.close();
            },
            error: function(xhr) {
                let pesan = 'Gagal menarik data billing.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    pesan = xhr.responseJSON.message;
                }
                Swal.fire('Gagal', pesan, 'error');
            },
            success: function(response) {
                if (!response.status) {
                    $('#btnSimpan').prop('disabled', true);
                    $('#areaHasil').html(kosongState(response.message));
                    return;
                }

                $('#areaHasil').html(tabelRekap(response));
                $('#btnSimpan').prop('disabled', false);
            }
        });
    }

    function kosongState(pesan) {
        return '<div class="text-center py-5">' +
            '<i class="ti ti-inbox text-dark" style="font-size: 3rem;"></i>' +
            '<h5 class="text-dark mb-1">TIDAK ADA DATA</h5>' +
            '<p class="text-muted mb-0">' + $('<div>').text(pesan || 'Tidak ada data penerimaan.').html() + '</p>' +
            '</div>';
    }

    function tabelRekap(response) {
        let baris = '';

        response.rows.forEach(function(row) {
            baris += '<tr>' +
                '<td class="text-center align-middle">' + row.id_jenis + '</td>' +
                '<td class="text-start align-middle">' + $('<div>').text(row.jenis_pelanggan).html() + '</td>' +
                '<td class="text-center align-middle">' + (row.tgl_terakhir ? row.tgl_terakhir.substring(0, 10) : '-') + '</td>' +
                '<td class="text-center align-middle">' + formatRupiah.format(row.jml_pelanggan) + '</td>' +
                '<td class="text-end align-middle">' + formatRupiah.format(row.jumlah) + '</td>' +
                '<td class="text-end align-middle" style="width: 200px;">' +
                '<input type="text" class="form-control form-control-sm text-end input-jumlah" name="jumlah[]" ' +
                'value="' + formatRupiah.format(row.jumlah) + '" data-nilai="' + row.jumlah + '">' +
                '<input type="hidden" name="id_jenis[]" value="' + row.id_jenis + '">' +
                '<input type="hidden" name="id_kelompok[]" value="' + row.id_kelompok + '">' +
                '<input type="hidden" name="id_kec[]" value="' + row.id_kec + '">' +
                '</td>' +
                '</tr>';
        });

        return '<div class="mb-3">' +
            '<div class="fs-5 fw-semibold text-uppercase">Daftar Penerimaan Kas Air Unit ' +
            $('<div>').text(response.info.unit).html() + '</div>' +
            '<div class="text-muted">Periode tagihan: ' + response.info.tglawal + ' s/d ' + response.info.tglakhir + '</div>' +
            '</div>' +
            '<div class="table-responsive">' +
            '<table class="table table-sm table-bordered border-dark mb-0 align-middle">' +
            '<thead class="text-black">' +
            '<tr>' +
            '<th class="text-center" width="6%">ID</th>' +
            '<th>JENIS PELANGGAN</th>' +
            '<th class="text-center" width="14%">TGL BAYAR TERAKHIR</th>' +
            '<th class="text-center" width="10%">JML PELANGGAN</th>' +
            '<th class="text-end" width="16%">JUMLAH PENERIMAAN</th>' +
            '<th class="text-end" width="20%">NILAI DISIMPAN</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody class="text-black">' + baris + '</tbody>' +
            '<tfoot>' +
            '<tr class="fw-semibold">' +
            '<td colspan="4" class="text-end">TOTAL</td>' +
            '<td class="text-end" id="totalPenerimaan">' + formatRupiah.format(response.total) + '</td>' +
            '<td></td>' +
            '</tr>' +
            '</tfoot>' +
            '</table>' +
            '</div>';
    }

    $(document).on('input', '.input-jumlah', function() {
        const angka = this.value.replace(/[^0-9]/g, '');
        this.dataset.nilai = angka === '' ? '0' : angka;
        this.value = angka === '' ? '' : formatRupiah.format(parseInt(angka, 10));
        hitungTotal();
    });

    function hitungTotal() {
        let total = 0;
        $('.input-jumlah').each(function() {
            total += parseInt(this.dataset.nilai || '0', 10);
        });
        $('#totalPenerimaan').text(formatRupiah.format(total));
    }

    $('#formPenerimaan').submit(function(e) {
        e.preventDefault();

        if ($('#areaHasil .input-jumlah').length === 0) {
            Swal.fire('Peringatan', 'Tarik data billing terlebih dahulu.', 'warning');
            return;
        }

        if ($('#tglPenerimaan').val() === '') {
            $('#tglPenerimaan').addClass('is-invalid').trigger('focus');
            return;
        }
        $('#tglPenerimaan').removeClass('is-invalid');

        Swal.fire({
            title: 'Konfirmasi',
            html: 'Anda akan menyimpan <b>Penerimaan Kas Air</b>. Pastikan nominal sudah sesuai.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Proses',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                type: 'post',
                url: "<?= base_url('ekas/masuk/simpan') ?>",
                data: $('#formPenerimaan').serialize(),
                dataType: 'json',
                beforeSend: function() {
                    $('#btnSimpan').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...'
                    );
                },
                complete: function() {
                    $('#btnSimpan').prop('disabled', false).html(
                        '<i class="ti ti-device-floppy"></i> Proses'
                    );
                },
                error: function(xhr) {
                    let pesan = 'Gagal menyimpan penerimaan.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        pesan = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal', pesan, 'error');
                },
                success: function(response) {
                    if (!response.status) {
                        Swal.fire('Gagal', response.message, 'error');
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message
                    }).then(function() {
                        $('#btnSimpan').prop('disabled', true);
                        $('#areaHasil').html(kosongState('Penerimaan sudah disimpan. Tarik ulang data bila perlu.'));
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>
