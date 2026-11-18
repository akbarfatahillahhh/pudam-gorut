<?= $this->extend('layouts/main_ekas') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-4"><?= esc($title) ?></h5>

        <form id="formNonAir">
            <div class="row">
                <div class="col-lg-3 col-6 mb-2">
                    <label class="form-label fw-semibold">Tanggal Penerimaan</label>
                    <input type="date" name="tgl_penerimaan" id="tglPenerimaan" class="form-control form-control-sm"
                        value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-lg-3 col-6 mb-2">
                    <label class="form-label fw-semibold">Nomor Bukti</label>
                    <input type="text" name="nomor_bukti" class="form-control form-control-sm" maxlength="50"
                        placeholder="Kosongkan untuk otomatis">
                </div>
                <div class="col-lg-4 col-12 mb-2">
                    <label class="form-label fw-semibold">Unit Penagihan</label>
                    <select class="form-select" name="id_kec" id="unit" required>
                        <option value="">[ Pilih Unit Penagihan ]</option>
                        <?php foreach ($unit as $row) : ?>
                            <option value="<?= esc($row['id_kec'], 'attr') ?>"><?= esc($row['kecamatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-12 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-primary w-100" id="btnTambah">
                        <i class="ti ti-plus"></i>
                        Tambah Baris
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered border-dark align-middle mb-0" id="tabelBaris">
                    <thead class="text-black">
                        <tr>
                            <th width="4%" class="text-center">#</th>
                            <th>JENIS PENERIMAAN</th>
                            <th width="18%" class="text-end">JUMLAH</th>
                            <th width="28%">KETERANGAN</th>
                            <th width="6%" class="text-center">#</th>
                        </tr>
                    </thead>
                    <tbody class="text-black"></tbody>
                    <tfoot>
                        <tr class="fw-semibold">
                            <td colspan="2" class="text-end">TOTAL</td>
                            <td class="text-end" id="totalBaris">0</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-sm btn-success" id="btnSimpan">
                    <i class="ti ti-device-floppy"></i>
                    Simpan Penerimaan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border border-dark mt-3">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-4">Riwayat Penerimaan Non Air Terakhir</h5>

        <div class="table-responsive">
            <table class="table table-sm table-bordered border-dark align-middle mb-0">
                <thead class="text-black">
                    <tr>
                        <th class="text-center" width="4%">#</th>
                        <th width="16%">NOMOR BUKTI</th>
                        <th class="text-center" width="12%">TANGGAL</th>
                        <th width="18%">UNIT</th>
                        <th width="24%">JENIS</th>
                        <th class="text-end" width="14%">JUMLAH</th>
                        <th>KETERANGAN</th>
                    </tr>
                </thead>
                <tbody class="text-black">
                    <?php if ($riwayat === []) : ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada penerimaan non air.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($riwayat as $i => $row) : ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><?= esc($row['nomor_bukti'] ?? '-') ?></td>
                                <td class="text-center"><?= esc($row['tgl_penerimaan'] ?? '-') ?></td>
                                <td><?= esc($row['kecamatan'] ?? '-') ?></td>
                                <td>
                                    <?= esc(trim(($row['nomor_perkiraan'] ?? '') . ' | ' . ($row['NAMA_PERKIRAAN'] ?? ''), ' |')) ?>
                                </td>
                                <td class="text-end"><?= number_format((int) $row['jumlah'], 0, ',', '.') ?></td>
                                <td><?= esc($row['keterangan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
    const formatRupiah = new Intl.NumberFormat('id-ID');
    const opsiJenis = <?= json_encode(array_map(static function ($row) {
        return [
            'id'   => (int) $row['ID'],
            'text' => trim($row['NOMOR'] . ' | ' . $row['NAMA_PERKIRAAN']),
        ];
    }, $jenis), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    let nomorBaris = 0;

    $(document).ready(function() {
        $('#unit').select2({
            width: '100%',
            placeholder: '[ Pilih Unit Penagihan ]'
        });

        tambahBaris();
    });

    $('#btnTambah').click(function(e) {
        e.preventDefault();
        tambahBaris();
    });

    function tambahBaris() {
        nomorBaris++;

        let opsi = '<option value="">[ Pilih Jenis Penerimaan ]</option>';
        opsiJenis.forEach(function(item) {
            opsi += '<option value="' + item.id + '">' +
                $('<div>').text(item.text).html() + '</option>';
        });

        const baris = '<tr>' +
            '<td class="text-center align-middle nomor-baris">' + nomorBaris + '</td>' +
            '<td><select class="form-select form-select-sm pilih-jenis" name="id_jenis[]" required>' + opsi + '</select></td>' +
            '<td><input type="text" class="form-control form-control-sm text-end input-jumlah" name="jumlah[]" ' +
            'value="0" data-nilai="0" required></td>' +
            '<td><input type="text" class="form-control form-control-sm" name="keterangan[]" maxlength="150" ' +
            'placeholder="Keterangan penerimaan"></td>' +
            '<td class="text-center align-middle">' +
            '<button type="button" class="btn btn-sm btn-danger btn-hapus"><i class="ti ti-trash"></i></button>' +
            '</td>' +
            '</tr>';

        $('#tabelBaris tbody').append(baris);
        $('#tabelBaris tbody tr').last().find('.pilih-jenis').select2({
            width: '100%'
        });
    }

    $(document).on('click', '.btn-hapus', function() {
        if ($('#tabelBaris tbody tr').length <= 1) {
            Swal.fire('Peringatan', 'Minimal harus ada satu baris penerimaan.', 'warning');
            return;
        }

        $(this).closest('tr').remove();
        urutkanBaris();
        hitungTotal();
    });

    function urutkanBaris() {
        $('#tabelBaris tbody tr').each(function(index) {
            $(this).find('.nomor-baris').text(index + 1);
        });
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
        $('#totalBaris').text(formatRupiah.format(total));
    }

    $('#formNonAir').submit(function(e) {
        e.preventDefault();

        if ($('#unit').val() === '') {
            Swal.fire('Peringatan', 'Pilih unit penagihan terlebih dahulu.', 'warning');
            return;
        }

        const tgl = $('#tglPenerimaan').val();

        if (tgl === '') {
            $('#tglPenerimaan').addClass('is-invalid').trigger('focus');
            return;
        }
        $('#tglPenerimaan').removeClass('is-invalid');

        let adaNominal = false;

        $('#tabelBaris tbody tr').each(function() {
            // Baca atribut langsung: cache $.data() tidak ikut berubah saat
            // pengguna mengetik nominal baru.
            const nilai = parseInt($(this).find('.input-jumlah').attr('data-nilai') || '0', 10);
            if (nilai > 0) {
                adaNominal = true;
            }
        });

        if (!adaNominal) {
            Swal.fire('Peringatan', 'Isi minimal satu baris dengan nominal lebih dari nol.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Konfirmasi',
            html: 'Simpan <b>Penerimaan Kas Non Air</b> ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                type: 'post',
                url: "<?= base_url('ekas/masuk-non-air/simpan') ?>",
                data: $('#formNonAir').serialize(),
                dataType: 'json',
                beforeSend: function() {
                    $('#btnSimpan').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...'
                    );
                },
                complete: function() {
                    $('#btnSimpan').prop('disabled', false).html(
                        '<i class="ti ti-device-floppy"></i> Simpan Penerimaan'
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
                        window.location.reload();
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>
