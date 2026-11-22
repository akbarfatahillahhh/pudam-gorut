<?= $this->extend('layouts/main_billing') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-3"><?= esc($title) ?></h5>
        <p class="text-muted fs-2 mb-3">
            Hasil pencatatan meter petugas. Verifikasi akan menerbitkan rekening pelanggan.
        </p>

        <div class="row g-2 mb-3">
            <div class="col-lg-2 col-6">
                <label class="form-label">Bulan</label>
                <select id="cmBulan" class="form-select form-select-sm">
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
                <select id="cmTahun" class="form-select form-select-sm">
                    <?php for ($i = (int) date('Y'); $i >= 2021; $i--): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-lg-3 col-6">
                <label class="form-label">Petugas Catat</label>
                <select id="cmPetugas" class="form-select form-select-sm">
                    <option value="all">SEMUA PETUGAS</option>
                </select>
            </div>
            <div class="col-lg-3 col-6">
                <label class="form-label">Status Verifikasi</label>
                <select id="cmVerifikasi" class="form-select form-select-sm">
                    <option value="all">Semua</option>
                    <option value="N">Belum Diverifikasi</option>
                    <option value="Y">Terverifikasi</option>
                </select>
            </div>
            <div class="col-lg-2 col-12 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-primary w-100" id="cmTampil">
                    <i class="ti ti-refresh"></i> Muat
                </button>
            </div>
        </div>

        <div class="table-responsive" style="max-height: 60vh; overflow:auto;">
            <table id="tableCatatMeter" class="table table-sm table-bordered border-dark align-middle mb-0">
                <thead class="text-black fs-2">
                    <tr>
                        <th class="text-center" width="4%">#</th>
                        <th class="text-center" width="11%">PERIODE</th>
                        <th width="16%">PETUGAS CATAT</th>
                        <th>PELANGGAN</th>
                        <th class="text-center" width="8%">GOL</th>
                        <th class="text-center" width="9%">ANGKA METER</th>
                        <th class="text-center" width="11%">TGL CATAT</th>
                        <th class="text-center" width="9%">FOTO</th>
                        <th class="text-center" width="12%">STATUS</th>
                        <th class="text-center" width="14%">#</th>
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
(function() {
    let tabel = null;
    let dataVerifikasi = null;

    function nomorRekening(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    $.ajax({
        type: 'get',
        url: "<?= base_url('Billing/CatatMeter/petugas') ?>",
        dataType: 'json',
        success: function(list) {
            if (!Array.isArray(list)) {
                return;
            }

            $.each(list, function(i, item) {
                $('#cmPetugas').append('<option value="' + item.id + '">' + item.text + '</option>');
            });

            $('#cmPetugas').select2({ width: '100%' });
        },
    });

    function filter() {
        return {
            bulan: $('#cmBulan').val(),
            tahun: $('#cmTahun').val(),
            petugas: $('#cmPetugas').val(),
            verifikasi: $('#cmVerifikasi').val(),
        };
    }

    function muat() {
        if (tabel) {
            tabel.destroy();
        }

        tabel = $('#tableCatatMeter').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: 'post',
                url: "<?= base_url('Billing/CatatMeter/datatable') ?>",
                data: function(d) {
                    Object.assign(d, filter());
                },
            },
            columns: [
                { data: null, class: 'text-center align-middle', searchable: false, orderable: false },
                { data: 'periode', class: 'text-center align-middle', searchable: false },
                { data: 'nama_petugas', class: 'align-middle', searchable: false },
                {
                    data: null,
                    class: 'align-middle',
                    render: function(data, type, row) {
                        return '<b>' + (row.no_sambungan ?? '-') + '</b><br>' +
                            '<span class="text-uppercase">' + (row.nama_pelanggan ?? '-') + '</span><br>' +
                            '<span class="text-muted fs-1">' +
                            [row.kecamatan, row.kelurahan].filter(Boolean).join(' / ') + '</span>';
                    },
                },
                { data: 'kd_jenis', class: 'text-center align-middle', searchable: false },
                { data: 'angka_meter', class: 'text-center align-middle fw-semibold', searchable: false },
                {
                    data: 'tgl_catat',
                    class: 'text-center align-middle',
                    searchable: false,
                    render: function(d) {
                        return d ? new Date(d).toLocaleDateString('id-ID') : '-';
                    },
                },
                {
                    data: null,
                    class: 'text-center align-middle',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row) {
                        const f = [];

                        if (row.foto_meter) {
                            f.push('<a href="' + row.foto_meter + '" target="_blank" title="Foto meter">M</a>');
                        }
                        if (row.foto_segel) {
                            f.push('<a href="' + row.foto_segel + '" target="_blank" title="Foto segel">S</a>');
                        }
                        if (row.foto_rumah) {
                            f.push('<a href="' + row.foto_rumah + '" target="_blank" title="Foto rumah">R</a>');
                        }

                        return f.length ? f.join(' · ') : '<span class="text-muted">-</span>';
                    },
                },
                { data: 'status', class: 'text-center align-middle', searchable: false },
                { data: 'aksi', class: 'text-center align-middle', searchable: false, orderable: false },
            ],
            order: [[6, 'desc']],
            pageLength: 25,
            language: {
                emptyTable: 'Tidak ada hasil pencatatan meter pada filter ini.',
                zeroRecords: 'Tidak ada data.',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ baris',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ catatan',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
            },
            drawCallback: function() {
                const info = this.api().page.info();

                this.api().column(0).nodes().each(function(cell, i) {
                    cell.innerHTML = info.start + i + 1;
                });
            },
        });
    }

    muat();

    $('#cmTampil').click(muat);
    $('#cmBulan, #cmTahun, #cmPetugas, #cmVerifikasi').on('change', muat);

    window.bukaVerifikasi = function(id) {
        $.ajax({
            type: 'get',
            url: "<?= base_url('Billing/CatatMeter/detail') ?>",
            data: { id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success') {
                    Swal.fire('Gagal', res.msg, 'error');
                    return;
                }

                dataVerifikasi = res.data;
                $('.modal-place').html(res.html ?? '');

                tampilkanModalVerifikasi(res.data);
            },
            error: function() {
                Swal.fire('Gagal', 'Detail catat meter tidak bisa diambil.', 'error');
            },
        });
    };

    function tampilkanModalVerifikasi(d) {
        const tarifBawah = Number(d.tarif_bawah_10 || 0);
        const tarifAtas = Number(d.tarif_atas_10 || 0);
        const bea = Number(d.bea_admin_efektif || 0);

        const pakai = Number(d.kubikasi || 0);
        const tagBawah = 10 * tarifBawah;
        const tagAtas = pakai > 10 ? (pakai - 10) * tarifAtas : 0;
        const total = tagBawah + tagAtas + bea;

        const foto = function(url, label) {
            if (!url) {
                return '';
            }

            return '<a href="' + url + '" target="_blank" class="col-4 text-center">' +
                '<img src="' + url + '" class="img-fluid border rounded" style="height:80px;object-fit:cover">' +
                '<div class="fs-1 text-muted">' + label + '</div></a>';
        };

        const html =
            '<div class="modal fade" id="modalVerifikasi" tabindex="-1" data-bs-backdrop="static">' +
            '<div class="modal-dialog modal-lg modal-dialog-centered">' +
            '<div class="modal-content">' +
            '<div class="modal-header border-bottom">' +
            '<h5 class="modal-title fw-semibold">Verifikasi Angka Meter</h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>' +
            '<div class="modal-body">' +

            '<div class="border rounded p-2 mb-3">' +
            '<div class="fw-semibold text-uppercase">' + (d.no_sambungan ?? '-') + ' — ' + (d.nama_pelanggan ?? '-') + '</div>' +
            '<div class="fs-2 text-muted">' + [d.kecamatan, d.kelurahan].filter(Boolean).join(' / ') + '</div>' +
            '<div class="fs-2 text-muted">Petugas: ' + (d.nama_petugas ?? '-') + '</div>' +
            '</div>' +

            '<div class="row g-2 mb-3">' +
            '<div class="col-6"><div class="border rounded p-2 text-center"><div class="fs-2 text-muted">Meter Lalu</div><div class="fs-4 fw-semibold">' + Number(d.meter_lalu || 0).toLocaleString('id-ID') + '</div></div></div>' +
            '<div class="col-6"><div class="border rounded p-2 text-center"><div class="fs-2 text-muted">Meter Ini</div><div class="fs-4 fw-semibold">' + Number(d.angka_meter || 0).toLocaleString('id-ID') + '</div></div></div>' +
            '</div>' +

            '<table class="table table-sm table-borderless fs-2 mb-3">' +
            '<tr><td>Pemakaian</td><td class="text-end fw-semibold">' + pakai.toLocaleString('id-ID') + ' m3</td></tr>' +
            '<tr><td>Tarif 0-10 m3</td><td class="text-end">' + nomorRekening(tagBawah) + '</td></tr>' +
            '<tr><td>Tarif &gt; 10 m3</td><td class="text-end">' + nomorRekening(tagAtas) + '</td></tr>' +
            '<tr><td>Bea Admin</td><td class="text-end">' + nomorRekening(bea) + '</td></tr>' +
            '<tr class="border-top"><td class="fw-semibold">Jumlah Tagihan</td><td class="text-end fw-bold">' + nomorRekening(total) + '</td></tr>' +
            '</table>' +

            '<div class="row g-1 mb-2">' +
            foto(d.foto_meter, 'Meter') + foto(d.foto_segel, 'Segel') + foto(d.foto_rumah, 'Rumah') +
            '</div>' +

            (d.sudah_ada_rekening
                ? '<div class="alert alert-warning fs-2 mb-0">Rekening periode ini sudah ada. Verifikasi akan ditolak.</div>'
                : '') +

            '</div>' +
            '<div class="modal-footer border-top">' +
            '<button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>' +
            '<button type="button" class="btn btn-sm btn-primary" id="btnVerifikasi"' +
            (d.sudah_ada_rekening ? ' disabled' : '') + '>' +
            '<i class="fa fa-check"></i> Verifikasi &amp; Terbitkan Rekening</button>' +
            '</div></div></div></div>';

        $('.modal-place').html(html);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerifikasi')).show();

        $('#btnVerifikasi').on('click', function() {
            $.ajax({
                type: 'post',
                url: "<?= base_url('Billing/CatatMeter/verifikasi') ?>",
                data: { id: d.id },
                dataType: 'json',
                beforeSend: function() { $('#btnVerifikasi').prop('disabled', true); },
                success: function(res) {
                    if (res.status !== 'success') {
                        Swal.fire('Gagal', res.msg, 'error');
                        $('#btnVerifikasi').prop('disabled', false);
                        return;
                    }

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerifikasi')).hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        html: 'Rekening <b>' + res.no_rek + '</b><br>' + nomorRekening(res.tagihan),
                    });

                    muat();
                },
                error: function() {
                    Swal.fire('Gagal', 'Verifikasi gagal diproses.', 'error');
                    $('#btnVerifikasi').prop('disabled', false);
                },
            });
        });
    }

    window.hapusCatat = function(id) {
        Swal.fire({
            title: 'Hapus catat meter?',
            text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
        }).then(function(r) {
            if (!r.isConfirmed) {
                return;
            }

            $.ajax({
                type: 'post',
                url: "<?= base_url('Billing/CatatMeter/hapus') ?>",
                data: { id: id },
                dataType: 'json',
                success: function(res) {
                    if (res.status !== 'success') {
                        Swal.fire('Gagal', res.msg, 'error');
                        return;
                    }

                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.msg, showConfirmButton: false, timer: 1400 });
                    muat();
                },
                error: function() {
                    Swal.fire('Gagal', 'Gagal menghapus.', 'error');
                },
            });
        });
    };

    window.cekRekening = function(idPelanggan) {
        $.ajax({
            type: 'get',
            url: "<?= base_url('Billing/CatatMeter/rekening') ?>",
            data: { id_pelanggan: idPelanggan },
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success') {
                    Swal.fire('Gagal', 'Rekening tidak bisa diambil.', 'error');
                    return;
                }

                $('.modal-place').html(res.html);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRekening')).show();
            },
        });
    };
})();
</script>
<?= $this->endSection() ?>
