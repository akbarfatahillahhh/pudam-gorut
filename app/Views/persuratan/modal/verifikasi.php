<?php
/**
 * Modal verifikasi pengajuan pegawai.
 *
 * @var object      $pengajuan
 * @var string|null $jabatan
 * @var bool        $buktiAda
 */
$status = $pengajuan->persetujuan;
?>
<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Verifikasi Pengajuan Pegawai</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="row mb-2">
                            <div class="col-4 text-muted">Nama</div>
                            <div class="col-8 text-dark fw-semibold"><?= esc($pengajuan->nama_peg ?? '-') ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 text-muted">NIK</div>
                            <div class="col-8 text-dark"><?= esc($pengajuan->nik ?? '-') ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 text-muted">Jabatan</div>
                            <div class="col-8 text-dark"><?= esc($jabatan ?? '-') ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 text-muted">Bukti Terlampir</div>
                            <div class="col-8">
                                <?php if ($buktiAda) : ?>
                                    <a href="<?= base_url('bukti_terlampir/' . $pengajuan->bukti_terlampir) ?>"
                                        target="_blank" rel="noopener" class="btn btn-sm btn-warning">
                                        <i class="ti ti-paperclip"></i> Lihat Bukti
                                    </a>
                                <?php else : ?>
                                    <span class="text-muted">Tidak ada berkas</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row mb-2">
                            <div class="col-5 text-muted">Dikirim</div>
                            <div class="col-7 text-dark">
                                <?= $pengajuan->posting ? esc(date('d-m-Y H:i:s', strtotime($pengajuan->posting))) : '-' ?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 text-muted">Pengajuan</div>
                            <div class="col-7 text-dark text-uppercase"><?= esc($pengajuan->jenis ?? '-') ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 text-muted">Terhitung Mulai</div>
                            <div class="col-7 text-dark">
                                <?= $pengajuan->terhitung_mulai ? esc(date('d-m-Y', strtotime($pengajuan->terhitung_mulai))) : '-' ?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 text-muted">Rencana Masuk</div>
                            <div class="col-7 text-dark">
                                <?= $pengajuan->mulai_masuk ? esc(date('d-m-Y', strtotime($pengajuan->mulai_masuk))) : '-' ?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 text-muted">Status</div>
                            <div class="col-7">
                                <?php if ($status === 'Y') : ?>
                                    <span class="badge bg-success">Diterima</span>
                                <?php elseif ($status === 'T') : ?>
                                    <span class="badge bg-danger">Ditolak</span>
                                <?php else : ?>
                                    <span class="badge bg-primary">Pengajuan</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <strong>Keterangan:</strong>
                        <p class="text-justify mb-0 text-dark"><?= nl2br(esc($pengajuan->keterangan ?? '-')) ?></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-danger" id="btnTolak"
                    data-id="<?= esc($pengajuan->id) ?>" <?= $status === 'T' ? 'disabled' : '' ?>>
                    <i class="ti ti-x"></i> Tolak Pengajuan
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="btnTerima"
                    data-id="<?= esc($pengajuan->id) ?>" <?= $status === 'Y' ? 'disabled' : '' ?>>
                    <i class="ti ti-check"></i> Terima Pengajuan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function simpanVerifikasi(id, keputusan, label) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: 'Pengajuan akan ' + label + '.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, ' + label,
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }
            $.ajax({
                type: 'POST',
                url: "<?= base_url('Persuratan/verifikasi') ?>",
                data: {
                    id: id,
                    keputusan: keputusan
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Berhasil', response.message, 'success');
                        tutupModalPersuratan();
                        $('#datapengajuan').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Gagal', 'Verifikasi tidak dapat disimpan', 'error');
                }
            });
        });
    }

    $('#btnTolak').click(function() {
        simpanVerifikasi($(this).data('id'), 'T', 'ditolak');
    });

    $('#btnTerima').click(function() {
        simpanVerifikasi($(this).data('id'), 'Y', 'diterima');
    });
});
</script>
