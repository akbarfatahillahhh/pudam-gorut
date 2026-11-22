<?= $this->extend('layouts/main_billing') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row align-items-end">
            <div class="col-lg-4 col-md-6 mb-2">
                <h5 class="card-title fw-semibold mb-0"><?= esc($title) ?></h5>
                <p class="text-muted mb-0 fs-2">Cari pelanggan, periksa tagihan, lalu proses pembayaran.</p>
            </div>
            <div class="col-lg-4 col-md-6 mb-2">
                <label class="form-label">Cari Pelanggan</label>
                <input type="search" id="cariPelanggan" class="form-control"
                    placeholder="Nomor sambungan atau nama pelanggan...">
            </div>
            <div class="col-lg-2 col-md-6 mb-2">
                <label class="form-label">Tampilkan</label>
                <select id="filterJenis" class="form-select">
                    <option value="semua">Semua Pelanggan</option>
                    <option value="tunggakan">Punya Tunggakan</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6 mb-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-primary w-100" id="btnCari">
                    <i class="ti ti-search"></i>
                    Cari
                </button>
            </div>
        </div>

        <div class="table-responsive mt-2">
            <table id="tableLoket" class="table table-sm table-bordered border-dark align-middle mb-0">
                <thead class="text-black fs-2">
                    <tr>
                        <th class="text-center" width="4%">#</th>
                        <th class="text-center" width="12%">NO SAMBUNGAN</th>
                        <th>PELANGGAN</th>
                        <th width="16%">ALAMAT</th>
                        <th class="text-center" width="12%">GOLONGAN</th>
                        <th class="text-center" width="12%">TUNGGAKAN</th>
                        <th class="text-center" width="12%">#</th>
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
    let tabelLoket = null;

    $(document).ready(function() {
        tabelLoket = $('#tableLoket').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: 'post',
                url: "<?= base_url('Billing/Loket/data') ?>",
                data: function(d) {
                    d.cari = $('#cariPelanggan').val();
                    d.jenis = $('#filterJenis').val();
                },
            },
            columns: [
                { data: null, class: 'text-center align-middle', searchable: false },
                { data: 'no_sambungan', class: 'text-center align-middle' },
                { data: 'nama_pelanggan', class: 'align-middle text-uppercase' },
                {
                    data: null,
                    class: 'align-middle',
                    searchable: false,
                    render: function(data, type, row) {
                        const alamat = row.alamat ?? '-';
                        const wilayah = [row.kecamatan, row.kelurahan].filter(Boolean).join(', ');
                        return '<div>' + alamat + '</div>' +
                            (wilayah ? '<div class="text-muted fs-1">' + wilayah + '</div>' : '');
                    },
                },
                {
                    data: null,
                    class: 'text-center align-middle',
                    searchable: false,
                    render: function(data, type, row) {
                        return (row.kd_jenis ?? '') +
                            (row.kelompok_jasa ? '<div class="text-muted fs-1">' + row.kelompok_jasa + '</div>' : '');
                    },
                },
                { data: 'tunggakan', class: 'text-center align-middle', searchable: false },
                { data: 'aksi', class: 'text-center align-middle', searchable: false, orderable: false },
            ],
            order: [[2, 'asc']],
            pageLength: 25,
            language: {
                emptyTable: 'Tidak ada pelanggan.',
                zeroRecords: 'Pelanggan tidak ditemukan.',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ baris',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ pelanggan',
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

        $('#btnCari').click(function() {
            tabelLoket.ajax.reload();
        });

        $('#cariPelanggan').on('keyup', function(e) {
            if (e.key === 'Enter') {
                tabelLoket.ajax.reload();
            }
        });

        $('#filterJenis').change(function() {
            tabelLoket.ajax.reload();
        });
    });

    function bukaTagihan(idPelanggan) {
        $.ajax({
            type: 'get',
            url: "<?= base_url('Billing/Loket/tagihan') ?>",
            data: { id_pelanggan: idPelanggan },
            dataType: 'json',
            success: function(response) {
                if (response.status !== 'success') {
                    Swal.fire('Gagal', response.msg ?? 'Data tidak ditemukan.', 'error');
                    return;
                }

                $('.modal-place').html(response.html);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalLoket')).show();
            },
            error: function() {
                Swal.fire('Gagal', 'Tidak bisa mengambil data tagihan.', 'error');
            },
        });
    }

    function bayar(idPemakaian, total) {
        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            html: 'Bayar tagihan sebesar <b>Rp ' + total.toLocaleString('id-ID') + '</b>?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Bayar',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#198754',
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                type: 'post',
                url: "<?= base_url('Billing/Loket/bayar') ?>",
                data: { id_pemakaian: idPemakaian },
                dataType: 'json',
                success: function(response) {
                    if (response.status !== 'success') {
                        Swal.fire('Gagal', response.msg, 'error');
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.msg,
                        showConfirmButton: false,
                        timer: 1500,
                    });

                    $('#tableTagihanLoket').DataTable().ajax.reload(null, false);

                    if (typeof tabelLoket !== 'undefined' && tabelLoket !== null) {
                        tabelLoket.ajax.reload(null, false);
                    }
                },
                error: function() {
                    Swal.fire('Gagal', 'Pembayaran gagal diproses.', 'error');
                },
            });
        });
    }

    function lihatKwitansi(idPemakaian) {
        $.ajax({
            type: 'get',
            url: "<?= base_url('Billing/Loket/kwitansi') ?>",
            data: { id_pemakaian: idPemakaian },
            dataType: 'json',
            success: function(response) {
                if (response.status !== 'success') {
                    Swal.fire('Gagal', response.msg, 'error');
                    return;
                }

                $('.modal-place').append(
                    '<div class="modal fade" id="modalKwitansi" tabindex="-1">' +
                    '<div class="modal-dialog modal-xl modal-dialog-centered" style="height:90vh;">' +
                    '<div class="modal-content" style="height:90vh;">' +
                    '<div class="modal-header border-bottom">' +
                    '<h5 class="modal-title">Kwitansi Pembayaran</h5>' +
                    '<a class="btn btn-sm btn-danger ms-auto" target="_blank" ' +
                    'href="<?= base_url('Billing/Loket/kwitansi_pdf') ?>/' + idPemakaian + '">' +
                    '<i class="fa fa-file-pdf"></i> PDF</a>' +
                    '<button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>' +
                    '</div>' +
                    '<div class="modal-body p-0">' + response.html + '</div>' +
                    '</div></div></div>'
                );

                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalKwitansi')).show();
            },
            error: function() {
                Swal.fire('Gagal', 'Kwitansi tidak bisa ditampilkan.', 'error');
            },
        });
    }

    $(document).on('hidden.bs.modal', '#modalKwitansi', function() {
        $(this).remove();
    });
</script>
<?= $this->endSection() ?>
