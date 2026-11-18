<?php
/**
 * Bagian form surat keluar, dimuat setelah pegawai dipilih.
 *
 * @var object $pegawai
 */
?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="tglsurat">Tanggal Surat</label>
        <input type="date" name="tglsurat" id="tglsurat" value="<?= date('Y-m-d') ?>" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="getPerhalSurat">Perihal</label>
        <select id="getPerhalSurat" name="perihal" class="form-select" style="width: 100%;">
            <option value="">[Pilih Perihal Surat]</option>
        </select>
    </div>
    <div class="col-12">
        <div class="row mb-2">
            <div class="col-3 text-muted">NIK</div>
            <div class="col-9 text-dark"><?= esc($pegawai->NIK ?? '-') ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-3 text-muted">Nama</div>
            <div class="col-9 text-dark fw-semibold"><?= esc($pegawai->NAMA ?? '-') ?></div>
        </div>
    </div>
    <div class="col-12" id="pratinjauSurat" style="display: none;">
        <label class="form-label">Pratinjau Format Surat</label>
        <div class="border border-dark rounded p-3" style="max-height: 320px; overflow-y: auto;"></div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#getPerhalSurat').select2({
        dropdownParent: $('#modal'),
        placeholder: '[Pilih Perihal Surat]',
        width: '100%',
        ajax: {
            dataType: 'json',
            url: "<?= base_url('GetData/getPerihalSurat') ?>",
            delay: 500,
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            }
        }
    });

    $('#getPerhalSurat').on('change', function() {
        var idSurat = $(this).val();
        var $panel = $('#pratinjauSurat');

        if (!idSurat) {
            $panel.hide().find('div.border').html('');
            return;
        }

        $.ajax({
            type: 'POST',
            url: "<?= base_url('Persuratan/template_surat') ?>",
            data: {
                id: idSurat
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $panel.show().find('div.border').html(response.data);
                } else {
                    $panel.hide().find('div.border').html('');
                    Swal.fire('Informasi', response.message, 'info');
                }
            },
            error: function() {
                $panel.hide().find('div.border').html('');
                Swal.fire('Gagal', 'Pratinjau surat tidak dapat dimuat', 'error');
            }
        });
    });
});
</script>
