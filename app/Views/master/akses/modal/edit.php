<?php
$user    = $user ?? [];
$sistem  = $sistem ?? [];
$modul   = $modul ?? [];
$daftar  = $daftar_sistem ?? sistem_daftar();
$perSistem = $modul_sistem ?? [];

// Tanpa baris di t_akses_sistem, akses memakai fallback level
// (lihat sistem_akses_default()). Tampilkan itu supaya admin tahu
// kondisi awalnya, bukan melihat semua kotak kosong.
$bawaan = false;

if (empty($sistem)) {
    $bawaan = true;
    $sistem = sistem_akses_default($user['level'] ?? null);
}
?>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h1 class="modal-title fs-5" id="modalLabel">Hak Akses Modul</h1>
                    <div class="fs-2 text-muted">
                        <?= esc(strtoupper($user['nama_lengkap'] ?? '-')) ?>
                        &mdash; <?= esc($user['username'] ?? '-') ?>
                        &mdash; level <?= esc($user['level'] ?? '-') ?>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-akses">
                    <?php if ($bawaan): ?>
                        <div class="alert alert-warning py-2 fs-2">
                            <i class="fa-solid fa-circle-info"></i>
                            User ini belum punya pengaturan akses, jadi masih memakai akses bawaan level
                            <b><?= esc($user['level'] ?? '-') ?></b>.
                            Menyimpan form ini akan mengunci pilihannya secara eksplisit.
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <?php foreach ($daftar as $kode => $info): ?>
                            <?php
                            $punyaSistem = in_array($kode, $sistem, true);
                            $grupModul   = $perSistem[$kode] ?? [];
                            $dipilih     = $modul[$kode] ?? [];

                            // Belum dibatasi = semua modul.
                            $tanpaBatas = empty($dipilih);
                            ?>
                            <div class="col-lg-6">
                                <div class="border border-dark rounded h-100 d-flex flex-column kotak-sistem" data-sistem="<?= esc($kode, 'attr') ?>">
                                    <div class="border-bottom border-dark px-3 py-2 d-flex align-items-center justify-content-between">
                                        <div class="form-check m-0">
                                            <input class="form-check-input border-dark chk-sistem"
                                                type="checkbox"
                                                name="sistem[]"
                                                value="<?= esc($kode, 'attr') ?>"
                                                id="sistem_<?= esc($kode, 'attr') ?>"
                                                <?= $punyaSistem ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bolder" for="sistem_<?= esc($kode, 'attr') ?>">
                                                <i class="<?= esc($info['icon'] ?? 'ti ti-layers', 'attr') ?>"></i>
                                                <?= esc($info['label'] ?? $kode) ?>
                                            </label>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-pilih" data-pilih="semua">Semua</button>
                                            <button type="button" class="btn btn-outline-secondary btn-pilih" data-pilih="kosong">Kosongkan</button>
                                        </div>
                                    </div>

                                    <div class="p-3 flex-grow-1 blok-modul">
                                        <?php if (empty($grupModul)): ?>
                                            <p class="text-muted fs-2 mb-0">Tidak ada modul yang bisa diatur.</p>
                                        <?php else: ?>
                                            <?php foreach ($grupModul as $judulGrup => $items): ?>
                                                <div class="mb-2">
                                                    <div class="fw-semibold text-uppercase text-muted fs-2 mb-1">
                                                        <?= esc($judulGrup) ?>
                                                    </div>

                                                    <?php foreach ($items as $it): ?>
                                                        <?php
                                                        $cek = $tanpaBatas || in_array($it['modul'], $dipilih, true);
                                                        ?>
                                                        <div class="form-check ms-2">
                                                            <input class="form-check-input border-dark chk-modul"
                                                                type="checkbox"
                                                                name="modul[<?= esc($kode, 'attr') ?>][]"
                                                                value="<?= esc($it['modul'], 'attr') ?>"
                                                                id="modul_<?= esc($it['modul'], 'attr') ?>"
                                                                data-sistem="<?= esc($kode, 'attr') ?>"
                                                                <?= $cek ? 'checked' : '' ?>
                                                                <?= $punyaSistem ? '' : 'disabled' ?>>
                                                            <label class="form-check-label fs-3" for="modul_<?= esc($it['modul'], 'attr') ?>">
                                                                <?= esc($it['label']) ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-text text-muted mt-3">
                        <i class="fa-solid fa-circle-info"></i>
                        Hanya modul <b>Billing</b> dan <b>E-Kas</b> yang bisa diatur di sini.
                        Data Master dan Administrasi tetap tertutup untuk non-admin, walaupun dicentang.
                    </div>

                    <div class="border-top text-end pt-3 mt-4">
                        <input type="hidden" name="id_session" value="<?= esc($user['id_session'] ?? '', 'attr') ?>">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var $form = $('#form-akses');

        // Sinkronkan status aktif tiap sistem dengan checkbox modulnya.
        function sinkron(kotak) {
            var $kotak = $(kotak);
            var aktif = $kotak.find('.chk-sistem').is(':checked');

            $kotak.find('.chk-modul').prop('disabled', !aktif);
            $kotak.find('.btn-pilih').prop('disabled', !aktif);
            $kotak.find('.blok-modul').css('opacity', aktif ? 1 : 0.5);
        }

        $form.find('.kotak-sistem').each(function() {
            sinkron(this);
        });

        $form.on('change', '.chk-sistem', function() {
            sinkron($(this).closest('.kotak-sistem'));
        });

        $form.on('click', '.btn-pilih', function() {
            var mode = $(this).data('pilih');
            $(this).closest('.kotak-sistem')
                .find('.chk-modul')
                .prop('checked', mode === 'semua');
        });

        $form.submit(function(e) {
            e.preventDefault();

            var adaSistem = $form.find('.chk-sistem:checked').length > 0;
            if (!adaSistem) {
                Swal.fire('Perhatian', 'Pilih minimal satu sistem. Untuk mematikan akses user sepenuhnya, blokir akunnya di menu User/Akun.', 'warning');
                return;
            }

            $.ajax({
                type: 'POST',
                url: "<?= base_url('Master/AksesModul/simpan') ?>",
                data: $form.serialize(),
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Loading...',
                        html: 'Sedang Diproses',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                    });
                    Swal.showLoading();
                },
                complete: function() {
                    Swal.close();
                },
                success: function(response) {
                    if (response.status === 'success') {
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('modal')).hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.msg,
                            showConfirmButton: false,
                            timer: 1500,
                        });
                        if (typeof window.reloadAkses === 'function') {
                            window.reloadAkses();
                        }
                    } else {
                        Swal.fire('Gagal', response.msg || 'Akses gagal disimpan.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan akses.', 'error');
                },
            });
        });
    })();
</script>
