<div class="modal fade" id="modalDuplikat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold">Rekening <?= esc($no_rek) ?> &mdash; <?= count($rows) ?> pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning fs-2">
                    Pilih <b>satu</b> rekening yang nomornya dipertahankan. Sisanya akan diberi nomor baru
                    berurutan setelah nomor terbesar periode ini.
                </div>

                <div class="table-responsive" style="max-height: 55vh; overflow:auto;">
                    <table class="table table-sm table-bordered border-dark align-middle mb-0">
                        <thead class="text-black fs-2">
                            <tr>
                                <th class="text-center" width="5%">#</th>
                                <th class="text-center" width="8%">PILIH</th>
                                <th class="text-center" width="14%">NO SAMBUNGAN</th>
                                <th>PELANGGAN</th>
                                <th width="22%">ALAMAT</th>
                                <th class="text-center" width="14%">TAGIHAN</th>
                                <th class="text-center" width="10%">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="text-black fs-2">
                            <?php foreach ($rows as $i => $r): ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td class="text-center">
                                        <input type="radio" class="form-check-input" name="pertahankan"
                                            value="<?= (int) $r['id_pemakaian'] ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                    </td>
                                    <td class="text-center"><?= esc($r['no_sambungan'] ?? '-') ?></td>
                                    <td class="text-uppercase"><?= esc($r['nama_pelanggan'] ?? '-') ?></td>
                                    <td><?= esc($r['alamat'] ?? '-') ?></td>
                                    <td class="text-end">Rp <?= number_format((float) $r['jlh_tagihan'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <?= ($r['status'] ?? '0') === '1'
                                            ? '<span class="badge bg-success">Lunas</span>'
                                            : '<span class="badge bg-danger">Menunggak</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-sm btn-primary" id="btnPerbaikiNorek">
                    <i class="fa fa-wrench"></i> Perbaiki Nomor Rekening
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#btnPerbaikiNorek').on('click', function() {
        const pertahankan = $('input[name="pertahankan"]:checked').val();

        Swal.fire({
            title: 'Perbaiki nomor rekening?',
            html: 'Satu rekening dipertahankan pada nomor <b><?= esc($no_rek) ?></b>, ' +
                'sisanya diberi nomor baru. Tindakan ini langsung mengubah data.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Perbaiki',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0d6efd',
        }).then(function(r) {
            if (!r.isConfirmed) {
                return;
            }

            $.ajax({
                type: 'post',
                url: "<?= base_url('Billing/Tagihan/perbaiki_norek') ?>",
                data: {
                    no_rek: '<?= esc($no_rek, 'js') ?>',
                    bulan: <?= (int) $bulan ?>,
                    tahun: <?= (int) $tahun ?>,
                    pertahankan: pertahankan,
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status !== 'success') {
                        Swal.fire('Gagal', res.msg, 'error');
                        return;
                    }

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDuplikat')).hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        html: res.msg + '<br>Nomor baru: <b>' + res.nomor_baru.join(', ') + '</b>',
                    });

                    $('#tableDuplikat').DataTable().ajax.reload(null, false);
                },
                error: function() {
                    Swal.fire('Gagal', 'Nomor rekening gagal diperbaiki.', 'error');
                },
            });
        });
    });
</script>
