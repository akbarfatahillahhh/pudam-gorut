<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-9">
                <h5 class="card-title fw-semibold mb-4"><?= $title ?></h5>
            </div>
            <div class="col-3" id="lay-filter">
                <select name="" id="jenisLaporan" class="form-select">
                    <option value="all" selected>Semua</option>
                    <option value="perUnit">Per Unit</option>
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label for="" class="form-label">Periode</label>
                <select name="" id="tahunAwal" class="form-select">
                    <option value="">[ Pilih Tahun ]</option>
                    <?php for ($i = date('Y'); $i >= 2010; $i--): ?>
                        <option value="<?= $i ?>" <?= ($i == date('Y', strtotime('-1 year'))) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label for="" class="form-label">S/D</label>
                <select name="" id="tahunAkhir" class="form-select">
                    <option value="">[ Pilih Tahun ]</option>
                    <?php for ($i = date('Y'); $i >= 2010; $i--): ?>
                        <option value="<?= $i ?>" <?= ($i == date('Y')) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-lg-3" id="lay-unit" style="display:none;">
                <label for="" class="form-label">Unit</label>
                <select name="" id="unit" class="form-select">
                    <option value="all" selected>Semua Unit</option>
                </select>
            </div>
        </div>

        <div class="mt-3">
            <div class="mb-2">
                <button class="btn btn-sm btn-danger" id="exportPdf">
                    <i class="fa fa-file-pdf"></i>
                    PDF
                </button>
                <button class="btn btn-sm btn-success" id="exportExcel">
                    <i class="fa fa-file-excel"></i>
                    Excel
                </button>
            </div>
            <div class="py-2 border border-dark">
                <div class="border-bottom border-dark">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-end align-middle" width="15%">
                                <img src="<?= base_url() ?>/logo/perumda-gorut.png" class="me-2" width="100">
                            </td>
                            <td class="align-middle">
                                <div class="fs-2 text-black fw-semibold">PERUSAHAAN UMUM DAERAH AIR MINUM</div>
                                <div class="fs-2 text-black fw-semibold">TIRTA GERBANG EMAS</div>
                                <div class="fs-2 text-black fw-semibold">KABUPATEN GORONTALO UTARA</div>
                                <div class="fs-2 text-black fw-semibold">Jl. Kasmat Lahay Titidu - Kwandang No. 17, Telp. (0442) 310077, Kode Pos 96252</div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="px-3 py-3" id="pagePlace">
                    <div class="text-center py-5">
                        <i class="fas fa-file-pdf text-dark h1"></i>
                        <h5 class="text-dark">PREVIEW LAPORAN</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-place"></div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>


<script>
    $(document).ready(function() {
        getPiutang($('#tahunAwal').val(), $('#tahunAkhir').val());
    });

    async function getPiutang(tahunAwal, tahunAkhir) {
        $.ajax({
            method: "get",
            url: "<?= base_url('Laporan/RekapitulasiPiutang/getPiutang') ?>",
            data: {
                jenisLaporan: $('#jenisLaporan').val(),
                tahunAwal: $('#tahunAwal').val(),
                tahunAkhir: $('#tahunAkhir').val()
            },
            dataType: "json",
            beforeSend: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Loading...',
                    html: 'Sedang Diproses',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                $('#pagePlace').html('');

                var html = '';
                html += '<table class="table table-sm table-bordered border border-dark table-hover mb-0">'
                html += '<thead>'
                html += '<tr class="text-center">'
                html += '<th class="text-black">Kecamatan</th>'
                html += '<th class="text-black">Tahun</th>'
                html += '<th class="text-black">Piutang</th>'
                html += '<th class="text-black">Telah Dibayar</th>'
                html += '<th class="text-black">Sisa Piutang</th>'
                html += '</tr>'
                html += '</thead>'
                html += '<tbody>'
                if (response) {

                    var totalPiutangKeseluruhan = 0;
                    var totalTelahDibayarKeseluruhan = 0;
                    var totalSisaDibayarKeseluruhan = 0;
                    $.each(response, function(kecamatan, dataTahun) {
                        let rowspan = dataTahun.length + 1;
                        var totalPiutang = 0;
                        var totalTelahDibayar = 0;
                        var totalSisaDibayar = 0;
                        $.each(dataTahun, function(index, item) {
                            let jumlah_bayar = parseInt(item.jumlah_bayar) || 0;
                            let jumlah_sisa_piutang = parseInt(item.jumlah_piutang) || 0;
                            let jumlah_piutang = jumlah_sisa_piutang + jumlah_bayar;

                            html += '<tr class="text-center text-black">'
                            if (index === 0) {
                                html += '<td rowspan="' + rowspan + '">' + kecamatan + '</td>';
                            }
                            html += '<td>' + item.tahun + '</td>'
                            html += '<td class="fw-bolder text-warning fst-italic">Rp. ' + jumlah_piutang.toLocaleString('id-ID') + '</td>'
                            html += '<td class="fw-bolder text-success fst-italic">Rp. ' + jumlah_bayar.toLocaleString('id-ID') + '</td>'
                            html += '<td class="fw-bolder text-danger fst-italic">Rp. ' + jumlah_sisa_piutang.toLocaleString('id-ID') + '</td>'
                            html += '</tr>'

                            totalPiutang += jumlah_piutang;
                            totalTelahDibayar += jumlah_bayar;
                            totalSisaDibayar += jumlah_sisa_piutang;
                        });

                        html += '<tr class="text-center bg-dark text-white">'
                        html += '<td>Total</td>'
                        html += '<td class="fw-bolder text-warning fst-italic">Rp. ' + totalPiutang.toLocaleString('id-ID') + '</td>'
                        html += '<td class="fw-bolder text-success fst-italic">Rp. ' + totalTelahDibayar.toLocaleString('id-ID') + '</td>'
                        html += '<td class="fw-bolder text-danger fst-italic">Rp. ' + totalSisaDibayar.toLocaleString('id-ID') + '</td>'
                        html += '</tr>'

                        totalPiutangKeseluruhan += totalPiutang;
                        totalTelahDibayarKeseluruhan += totalTelahDibayar;
                        totalSisaDibayarKeseluruhan += totalSisaDibayar;
                    });

                    html += '<tr class="text-center text-black">'
                    html += '<td colspan="2" class="fw-bolder">Total Keseluruhan</td>'
                    html += '<td class="fw-bolder text-warning fst-italic">Rp. ' + totalPiutangKeseluruhan.toLocaleString('id-ID') + '</td>'
                    html += '<td class="fw-bolder text-success fst-italic">Rp. ' + totalTelahDibayarKeseluruhan.toLocaleString('id-ID') + '</td>'
                    html += '<td class="fw-bolder text-danger fst-italic">Rp. ' + totalSisaDibayarKeseluruhan.toLocaleString('id-ID') + '</td>'
                    html += '</tr>'
                } else {
                    html += '<tr><td colspan="100%" class="text-center text-danger">DATA KOSONG</td></tr>';
                }

                html += '</tbody>'
                html += '</table>'

                $('#pagePlace').html(html);
            }
        });
    }

    async function getPiutangByUnit(unit, tahunAwal, tahunAkhir) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Laporan/RekapitulasiPiutang/getPiutang') ?>",
            data: {
                unit,
                tahunAwal,
                tahunAkhir
            },
            dataType: "json",
            beforeSend: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Loading...',
                    html: 'Sedang Diproses',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                $('#pagePlace').html('');

                if (response && response.length > 0) {
                    let kategori = response[0].kategori;
                    let kategoriKeys = Object.keys(kategori);
                    let colspanKategori = kategoriKeys.length;

                    let html = '';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-sm table-bordered border border-dark table-hover mb-0">';
                    html += '<thead>';
                    html += '<tr class="text-center">';
                    html += '<th class="text-black" rowspan="2">Kecamatan</th>';
                    html += '<th class="text-black" rowspan="2">Tahun</th>';
                    html += '<th class="text-black" colspan="' + colspanKategori + '">Jenis Pelanggan</th>';
                    html += '<th class="text-black" rowspan="2">Jumlah</th>';
                    html += '</tr>';
                    html += '<tr class="text-center">';

                    kategoriKeys.forEach((kategori) => {
                        html += '<th class="text-black" width="20%">' + kategori + '</th>';
                    });

                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';

                    let tempNamaKecamatan = '';
                    let totalPiutangKeseluruhan = 0;
                    let totalKeseluruhanPerkategori = {};

                    var totalPiutangPerKecamatan = 0;
                    var totalKeseluruhanPerKecamatanPerkategori = {};
                    var totalKeseluruhanPerKecamatanPerkategoriPerJumlah = {};
                    response.forEach((value, index) => {
                        let namaKecamatan = value.nama_kecamatan;
                        let tahun = value.tahun;
                        let totalPiutang = 0;

                        const rowspanTahun = response.filter(item => item.nama_kecamatan === namaKecamatan).length;

                        if (tempNamaKecamatan !== namaKecamatan) {
                            html += `<tr><td class="text-black" rowspan="${(rowspanTahun + 1)}">${namaKecamatan}</td>`;
                            tempNamaKecamatan = namaKecamatan;
                        } else {
                            html += '<tr>';
                        }

                        html += `<td class="text-black text-center">${tahun}</td>`;

                        kategoriKeys.forEach((kategori) => {
                            let jumlahPiutang = value.kategori[kategori] ? value.kategori[kategori].jumlah_piutang : 0;
                            html += `<td class="text-center fst-italic text-black">Rp. ${jumlahPiutang.toLocaleString('id-ID')}</td>`;

                            totalKeseluruhanPerKecamatanPerkategori[namaKecamatan] = totalKeseluruhanPerKecamatanPerkategori[namaKecamatan] || {};
                            totalKeseluruhanPerKecamatanPerkategori[namaKecamatan][kategori] = (totalKeseluruhanPerKecamatanPerkategori[namaKecamatan][kategori] || 0) + jumlahPiutang;

                            totalKeseluruhanPerkategori[kategori] = (totalKeseluruhanPerkategori[kategori] || 0) + jumlahPiutang;
                            totalPiutang += jumlahPiutang;
                        });

                        html += `<td class="text-center fst-italic fw-bolder text-black">Rp. ${totalPiutang.toLocaleString('id-ID')}</td></tr>`;
                        totalPiutangKeseluruhan += totalPiutang;

                        if (namaKecamatan !== response[index + 1]?.nama_kecamatan) {
                            html += '<tr class="text-white bg-dark">'
                            html += '<td class="text-center fw-bolder">Total</td>';

                            var totalPiutangComboTahun = 0;
                            kategoriKeys.forEach((kategori) => {
                                let totalPerKategori = totalKeseluruhanPerKecamatanPerkategori[namaKecamatan][kategori] || 0;
                                html += `<td class="text-center fw-bolder">Rp. ${totalPerKategori.toLocaleString('id-ID')}</td>`;

                                totalPiutangComboTahun += totalPerKategori;
                            })
                            html += `<td class="text-center fw-bolder">Rp. ${totalPiutangComboTahun.toLocaleString('id-ID')}</td>`;
                            html += '</tr>';
                        }
                    });

                    html += '<tr>';
                    html += '<td class="text-center text-black fw-bolder" colspan="2">Total Keseluruhan</td>';

                    kategoriKeys.forEach((kategori) => {
                        let totalKategori = totalKeseluruhanPerkategori[kategori] || 0;
                        html += `<td class="text-center text-black fw-bolder">Rp. ${totalKategori.toLocaleString('id-ID')}</td>`;
                    });

                    html += `<td class="text-center text-black fw-bolder">Rp. ${totalPiutangKeseluruhan.toLocaleString('id-ID')}</td>`;
                    html += '</tr>';

                    html += '</tbody></table></div>';

                    $('#pagePlace').html(html);
                } else {
                    $('#pagePlace').html('<tr><td colspan="6" class="text-center text-danger">DATA KOSONG</td></tr>');
                }

            }

        });
    }

    $('#jenisLaporan, #tahunAwal, #tahunAkhir').change(function(e) {
        e.preventDefault();
        var tahunAwal = $('#tahunAwal').val();
        var tahunAkhir = $('#tahunAkhir').val();
        var jenisLaporan = $('#jenisLaporan').val();
        var unit = $('#unit').val();

        if (jenisLaporan === 'perUnit') {
            $('#lay-filter').removeClass('col-lg-3').addClass('col-lg-3');
            $('#lay-unit').show();
            getPiutangByUnit(unit, tahunAwal, tahunAkhir)
        } else {
            $('#lay-filter').removeClass('col-lg-3').addClass('col-lg-3');
            $('#lay-unit').hide();
            if (jenisLaporan == 'all') {
                if (tahunAwal > tahunAkhir) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Tahun Akhir tidak boleh melebihi Tahun Awal',
                        timer: 5000,
                        showConfirmButton: false
                    });
                    return false;
                }
                if (!tahunAwal && !tahunAkhir) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Tahun Awal dan Tahun Akhir harus diisi',
                        timer: 5000,
                        showConfirmButton: false
                    });
                    return false;
                }
                getPiutang(jenisLaporan, tahunAwal, tahunAkhir);
            }
        }
    });

    $('#unit').select2({
        width: '100%',
        placeholder: 'Pilih Unit',
        allowClear: true,
        ajax: {
            url: '<?= base_url('GetData/getUnit') ?>',
            dataType: 'json',
            processResults: function(data) {
                let units = $.map(data, function(item) {
                    return {
                        text: item.text,
                        id: item.id
                    }
                });
                units.unshift({
                    id: 'all',
                    text: 'Semua Unit'
                });
                return {
                    results: units,
                }
            }
        }
    });

    $('#unit').change(function(e) {
        e.preventDefault()
        var tahunAwal = $('#tahunAwal').val();
        var tahunAkhir = $('#tahunAkhir').val();
        var unit = $('#unit').val();

        if (tahunAwal !== '' && tahunAkhir !== '') {
            getPiutangByUnit(unit, tahunAwal, tahunAkhir)
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Tahun Awal dan Tahun Akhir harus diisi',
                timer: 5000,
                showConfirmButton: false
            });
        }
    })

    $('#exportPdf').click(function(e) {
        e.preventDefault();
        var unit = $('#unit').val();
        var jenisLaporan = $('#jenisLaporan').val();
        var tahunAwal = $('#tahunAwal').val();
        var tahunAkhir = $('#tahunAkhir').val();

        $.ajax({
            type: "post",
            url: "<?= base_url('Laporan/RekapitulasiPiutang/modal_export_pdf') ?>",
            data: {
                jenisLaporan: jenisLaporan,
                tahunAwal: tahunAwal,
                tahunAkhir: tahunAkhir,
                unit: unit
            },
            dataType: "json",
            beforeSend: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Loading...',
                    html: 'Sedang Diproses',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });
</script>

<?= $this->endSection() ?>