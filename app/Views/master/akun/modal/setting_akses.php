<?php
$isAdmin = (($level ?? '') === 'admin');
$akses = $akses ?? [];
$daftar_sistem = $daftar_sistem ?? sistem_daftar();
?>
<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalLabel">Setting Akses</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-setting-akses">
                    <div class="mb-3 text-black">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label mb-1">Nama Lengkap</label>
                                <input type="text" class="form-control-plaintext fw-semibold" value="<?= esc($nama_lengkap ?? '-') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1">Level</label>
                                <input type="text" class="form-control-plaintext fw-semibold" value="<?= esc($level ?? '-') ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="border border-dark p-3 rounded mb-3">
                        <label class="fw-bolder mb-2 d-block">Sistem yang Boleh Diakses</label>
                        <?php foreach ($daftar_sistem as $kode => $info): ?>
                            <?php
                            $checked = $isAdmin || in_array($kode, $akses);
                            $disabled = $isAdmin;
                            ?>
                            <div class="form-check">
                                <input class="form-check-input border-dark"
                                    type="checkbox"
                                    name="sistem[]"
                                    value="<?= esc($kode) ?>"
                                    id="sistem_<?= esc($kode) ?>"
                                    <?= $checked ? 'checked' : '' ?>
                                    <?= $disabled ? 'disabled' : '' ?>>
                                <label class="form-check-label" for="sistem_<?= esc($kode) ?>">
                                    <?php if (!empty($info['icon'])): ?>
                                        <i class="<?= esc($info['icon']) ?>"></i>
                                    <?php endif; ?>
                                    <?= esc($info['label'] ?? $kode) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($isAdmin): ?>
                            <div class="form-text text-muted mt-2">
                                <i class="fa-solid fa-circle-info"></i>
                                Admin otomatis punya akses ke semua sistem.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="border border-dark p-3 rounded">
                        <label class="fw-bolder mb-2 d-block">Ringkasan Menu Tiap Sistem</label>
                        <div class="row g-3 text-black">
                            <?php foreach ($daftar_sistem as $kode => $info): ?>
                                <div class="col-md-6">
                                    <div class="border border-secondary rounded p-3 h-100">
                                        <div class="fw-bolder mb-2">
                                            <?php if (!empty($info['icon'])): ?>
                                                <i class="<?= esc($info['icon']) ?>"></i>
                                            <?php endif; ?>
                                            <?= esc($info['label'] ?? $kode) ?>
                                        </div>
                                        <?php foreach (sistem_menu($kode) as $grup): ?>
                                            <div class="mb-2">
                                                <div class="fw-semibold">
                                                    <?= esc($grup['judul'] ?? '') ?>
                                                    <?php if (!empty($grup['admin'])): ?>
                                                        <span class="badge bg-dark fs-1 rounded">admin</span>
                                                    <?php endif; ?>
                                                </div>
                                                <ul class="list-unstyled ms-3 mb-0">
                                                    <?php foreach (($grup['items'] ?? []) as $item): ?>
                                                        <li>
                                                            <?php if (!empty($item['icon'])): ?>
                                                                <i class="<?= esc($item['icon']) ?>"></i>
                                                            <?php endif; ?>
                                                            <?= esc($item['label'] ?? '') ?>
                                                            <?php if (!empty($item['children'])): ?>
                                                                <ul class="list-unstyled ms-3">
                                                                    <?php foreach ($item['children'] as $anak): ?>
                                                                        <li class="text-muted">
                                                                            <i class="fa-solid fa-angle-right"></i>
                                                                            <?= esc($anak['label'] ?? '') ?>
                                                                        </li>
                                                                    <?php endforeach; ?>
                                                                </ul>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-text text-muted mt-2">
                            <i class="fa-solid fa-circle-info"></i>
                            Menu Data Master hanya muncul untuk level admin.
                        </div>
                    </div>

                    <div class="border-top text-end pt-3 mt-4">
                        <input type="hidden" name="id_session" value="<?= esc($id_session ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-success" <?= $isAdmin ? 'disabled' : '' ?>>
                            <i class="fa fa-save"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#form-setting-akses').submit(function (e) {
            e.preventDefault();

            $.ajax({
                type: "post",
                url: "<?= base_url('Master/Akun/save_setting_akses') ?>",
                data: $(this).serialize(),
                dataType: "json",
                success: function (response) {
                    if (response.status === 'success') {
                        $('#modal').modal('hide');
                        location.reload();
                    } else {
                        var pesan = response.message || 'Gagal menyimpan akses.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: pesan
                            });
                        } else {
                            alert(pesan);
                        }
                    }
                },
                error: function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menyimpan akses.'
                        });
                    } else {
                        alert('Terjadi kesalahan saat menyimpan akses.');
                    }
                }
            });
        });
    });
</script>
