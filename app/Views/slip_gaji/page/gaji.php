<?php
/**
 * Sub-halaman Gaji Karyawan (tab pertama Slip Gaji).
 *
 * @var array $bulanNama
 */
?>
<?= $this->include('slip_gaji/_helper') ?>
<div class="card-body p-3">
    <div class="row align-items-end mb-3">
        <div class="col-lg-2 col-md-4 mb-2">
            <label class="form-label" for="bulan_gaji">Bulan</label>
            <select class="form-select" id="bulan_gaji">
                <?php foreach ($bulanNama as $nomor => $nama) : ?>
                    <option value="<?= $nomor ?>" <?= $nomor == date('n') ? 'selected' : '' ?>>
                        <?= esc($nama) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
            <label class="form-label" for="tahun_gaji">Tahun</label>
            <select class="form-select" id="tahun_gaji">
                <?php for ($i = (int) date('Y'); $i >= 2009; $i--) : ?>
                    <option value="<?= $i ?>" <?= $i == date('Y') ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-lg-8 col-md-12 mb-2 text-lg-end">
            <span class="badge bg-secondary me-2" id="badgeStatusGaji">DRAFT</span>
            <button type="button" class="btn btn-sm btn-danger" id="btnPrintGaji">
                <i class="ti ti-file-download"></i>
                Cetak PDF
            </button>
            <button type="button" class="btn btn-sm btn-warning" id="btnDraftGaji">
                <i class="ti ti-device-floppy"></i>
                Simpan Draft
            </button>
            <button type="button" class="btn btn-sm btn-primary" id="btnSimpanGaji">
                <i class="ti ti-checks"></i>
                Simpan Final
            </button>
        </div>
    </div>

    <div class="table-responsive" style="max-height: 560px; overflow-y: auto;">
        <table class="table table-sm table-bordered border-dark text-nowrap align-middle mb-0" id="tableGaji">
            <thead class="text-black">
                <tr>
                    <th class="text-center align-middle border border-dark" width="3%">No</th>
                    <th class="text-center align-middle border border-dark">Nama Pegawai</th>
                    <th class="text-center align-middle border border-dark">L/P</th>
                    <th class="text-center align-middle border border-dark">Gol</th>
                    <th class="text-center align-middle border border-dark">Jabatan</th>
                    <th class="text-center align-middle border border-dark">Tunj. Jabatan</th>
                    <th class="text-center align-middle border border-dark">Jenis Tunjangan</th>
                    <th class="text-center align-middle border border-dark">Qty</th>
                    <th class="text-center align-middle border border-dark">Total Tunjangan</th>
                    <th class="text-center align-middle border border-dark">Jenis Potongan</th>
                    <th class="text-center align-middle border border-dark">Total Potongan</th>
                    <th class="text-center align-middle border border-dark">Jumlah</th>
                </tr>
            </thead>
            <tbody class="text-black">
                <tr>
                    <td colspan="12" class="text-center align-middle py-3">Memuat data...</td>
                </tr>
            </tbody>
            <tfoot class="text-black fw-semibold">
                <tr>
                    <td colspan="8" class="text-center align-middle border border-dark">JUMLAH</td>
                    <td class="text-end align-middle border border-dark" id="totalTunjanganSemua">0</td>
                    <td class="border border-dark"></td>
                    <td class="text-end align-middle border border-dark" id="totalPotonganSemua">0</td>
                    <td class="text-end align-middle border border-dark" id="totalSemua">0</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
    $(function() {
        var daftarTunjangan = [];
        var daftarPotongan = [];
        var gajiFinal = false;

        function rupiah(nilai) {
            return slipGajiRupiah(nilai);
        }

        function opsiMaster(daftar, terpilih, kosong) {
            var html = '<option value="0">' + kosong + '</option>';
            daftar.forEach(function(item) {
                var label = item.jenis_tunjangan || item.jenis_potongan;
                html += '<option value="' + item.id + '"' + (Number(terpilih) === Number(item.id) ? ' selected' : '') +
                    '>' + slipGajiEscape(label) + ' (' + rupiah(item.jumlah) + ')</option>';
            });
            return html;
        }

        function hitungBaris($row) {
            var idTunjangan = Number($row.find('.tunjangan-jenis').val()) || 0;
            var qty = Number($row.find('.tunjangan-qty').val()) || 0;
            var nominalTunjangan = 0;

            daftarTunjangan.forEach(function(item) {
                if (Number(item.id) === idTunjangan) {
                    nominalTunjangan = Number(item.jumlah) || 0;
                }
            });

            var totalTunjangan = idTunjangan > 0 ? qty * nominalTunjangan : 0;
            $row.find('.tunjangan-total').text(rupiah(totalTunjangan));

            var idPotongan = Number($row.find('.potongan-jenis').val()) || 0;
            var totalPotongan = Number($row.find('.potongan-total').val()) || 0;
            if (idPotongan <= 0) {
                totalPotongan = 0;
                // Hanya tulis ulang saat potongan dikosongkan, supaya kursor
                // tidak melompat ketika pengguna sedang mengetik nominal.
                $row.find('.potongan-total').val(0);
            }

            $row.find('.jumlah-baris').text(rupiah(totalTunjangan - totalPotongan));
            $row.data('total-tunjangan', totalTunjangan);
            $row.data('total-potongan', totalPotongan);

            hitungSemua();
        }

        function hitungSemua() {
            var totalTunjangan = 0;
            var totalPotongan = 0;

            $('#tableGaji tbody tr').each(function() {
                totalTunjangan += Number($(this).data('total-tunjangan')) || 0;
                totalPotongan += Number($(this).data('total-potongan')) || 0;
            });

            $('#totalTunjanganSemua').text(rupiah(totalTunjangan));
            $('#totalPotonganSemua').text(rupiah(totalPotongan));
            $('#totalSemua').text(rupiah(totalTunjangan - totalPotongan));
        }

        function kunciForm(final) {
            gajiFinal = final;
            $('#tableGaji').find('select, input').prop('disabled', final);
            $('#btnSimpanGaji, #btnDraftGaji').prop('disabled', final);
            $('#badgeStatusGaji')
                .removeClass('bg-secondary bg-success')
                .addClass(final ? 'bg-success' : 'bg-secondary')
                .text(final ? 'FINAL' : 'DRAFT');
        }

        function getMasterGaji() {
            $.ajax({
                type: 'get',
                url: "<?= base_url('SlipGaji/getMasterGaji') ?>",
                data: {
                    bulan: $('#bulan_gaji').val(),
                    tahun: $('#tahun_gaji').val()
                },
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Memuat Data ...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                },
                complete: function() {
                    Swal.close();
                },
                success: function(response) {
                    daftarTunjangan = response.tunjangan || [];
                    daftarPotongan = response.potongan || [];

                    var rows = response.data || [];
                    var html = '';

                    if (!rows.length) {
                        html = '<tr><td colspan="12" class="text-center align-middle py-3">Tidak ada data pegawai</td></tr>';
                    }

                    rows.forEach(function(row, index) {
                        html += '<tr data-id_pegawai="' + row.id + '">' +
                            '<td class="text-center align-middle border border-dark">' + (index + 1) + '</td>' +
                            '<td class="align-middle border border-dark">' + slipGajiEscape(row.nama_pegawai) + '</td>' +
                            '<td class="text-center align-middle border border-dark">' + slipGajiEscape(row.jk) + '</td>' +
                            '<td class="text-center align-middle border border-dark">' + slipGajiEscape(row.gol) + '</td>' +
                            '<td class="align-middle border border-dark">' + slipGajiEscape(row.jabatan) + '</td>' +
                            '<td class="text-end align-middle border border-dark">' + rupiah(row.tunjangan_jabatan) + '</td>' +
                            '<td class="align-middle border border-dark">' +
                            '<select class="form-select form-select-sm tunjangan-jenis">' +
                            opsiMaster(daftarTunjangan, row.id_tunjangan, '- Pilih -') +
                            '</select></td>' +
                            '<td class="align-middle border border-dark" width="80">' +
                            '<input type="number" min="0" step="1" class="form-control form-control-sm text-end tunjangan-qty" value="' +
                            (Number(row.qty_tunjangan) || 0) + '"></td>' +
                            '<td class="text-end align-middle border border-dark tunjangan-total">0</td>' +
                            '<td class="align-middle border border-dark">' +
                            '<select class="form-select form-select-sm potongan-jenis">' +
                            opsiMaster(daftarPotongan, row.id_potongan, '- Pilih -') +
                            '</select></td>' +
                            '<td class="align-middle border border-dark" width="110">' +
                            '<input type="number" min="0" step="1" class="form-control form-control-sm text-end potongan-total" value="' +
                            (Number(row.total_potongan) || 0) + '"></td>' +
                            '<td class="text-end align-middle border border-dark fw-semibold jumlah-baris">0</td>' +
                            '</tr>';
                    });

                    $('#tableGaji tbody').html(html);
                    $('#tableGaji tbody tr').each(function() {
                        hitungBaris($(this));
                    });

                    kunciForm(response.final === true);
                }
            });
        }

        function simpanData(status) {
            if (gajiFinal) {
                Swal.fire('Info', 'Gaji bulan ini sudah final dan tidak dapat diubah', 'info');
                return;
            }

            var data = [];
            $('#tableGaji tbody tr').each(function() {
                var $row = $(this);
                var idPegawai = Number($row.data('id_pegawai'));
                if (!idPegawai) {
                    return;
                }

                data.push({
                    id_pegawai: idPegawai,
                    id_tunjangan: Number($row.find('.tunjangan-jenis').val()) || 0,
                    qty_tunjangan: Number($row.find('.tunjangan-qty').val()) || 0,
                    id_potongan: Number($row.find('.potongan-jenis').val()) || 0,
                    total_potongan: Number($row.find('.potongan-total').val()) || 0
                });
            });

            if (!data.length) {
                Swal.fire('Info', 'Tidak ada data pegawai untuk disimpan', 'info');
                return;
            }

            Swal.fire({
                title: status === 'Y' ? 'Simpan sebagai final?' : 'Simpan sebagai draft?',
                text: status === 'Y' ?
                    'Gaji yang sudah final tidak dapat diubah lagi.' : 'Data masih bisa diubah kembali.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: "<?= base_url('SlipGaji/saveData') ?>",
                    data: {
                        data: data,
                        bulan: $('#bulan_gaji').val(),
                        tahun: $('#tahun_gaji').val(),
                        status: status
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Menyimpan ...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: response.status ? 'success' : 'error',
                            title: response.status ? 'Berhasil' : 'Gagal',
                            text: response.msg || 'Data tidak dapat disimpan'
                        });

                        if (response.status) {
                            getMasterGaji();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menyimpan data (' + xhr.status + ')'
                        });
                    }
                });
            });
        }

        $('#bulan_gaji, #tahun_gaji').on('change', function() {
            getMasterGaji();
        });

        $('#tableGaji').on('change', '.tunjangan-jenis, .potongan-jenis', function() {
            hitungBaris($(this).closest('tr'));
        });

        $('#tableGaji').on('input', '.tunjangan-qty, .potongan-total', function() {
            hitungBaris($(this).closest('tr'));
        });

        $('#btnSimpanGaji').on('click', function() {
            simpanData('Y');
        });

        $('#btnDraftGaji').on('click', function() {
            simpanData('P');
        });

        $('#btnPrintGaji').on('click', function() {
            var url = "<?= base_url('SlipGaji/print') ?>?bulan=" + $('#bulan_gaji').val() +
                '&tahun=' + $('#tahun_gaji').val();
            window.open(url, '_blank');
        });

        getMasterGaji();
    });
</script>
