<?php
/**
 * Modal "Buat Pengajuan" (surat keluar).
 *
 * Catatan: aplikasi lama tidak pernah punya tabel/endpoint penyimpanan
 * surat keluar (form_open mengarah ke persuratan/save_surat_keluar yang
 * tidak ada). Di sini form tetap bisa dipakai memilih pegawai + perihal
 * dan melihat pratinjau format surat dari master_persuratan.TEMPLATE,
 * tetapi tombol Proses dinonaktifkan dengan penjelasan supaya tidak
 * terkesan menyimpan data yang sebenarnya tidak tersimpan.
 */
?>
<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Buat Surat Keluar</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Nama Karyawan</label>
                        <select id="getKaryawan" class="form-select" style="width: 100%;">
                            <option value="">[Cari &amp; Pilih Karyawan]</option>
                        </select>
                    </div>
                    <div class="col-md-12 tampilformsurat"></div>
                </div>

                <div class="alert alert-warning mt-4 mb-0" role="alert">
                    <i class="ti ti-info-circle"></i>
                    Penyimpanan surat keluar belum tersedia pada sistem ini. Form ini hanya untuk
                    menyiapkan data dan melihat pratinjau format surat.
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-primary" id="btnProses" disabled
                    title="Penyimpanan surat keluar belum tersedia">
                    <i class="ti ti-device-floppy"></i> Proses
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#getKaryawan').select2({
        dropdownParent: $('#modal'),
        placeholder: '[Cari & Pilih Karyawan]',
        width: '100%',
        ajax: {
            dataType: 'json',
            url: "<?= base_url('GetData/getKaryawan') ?>",
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

    $('#getKaryawan').on('change', function() {
        var idPegawai = $(this).val();
        $('.tampilformsurat').html('');

        if (!idPegawai) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: "<?= base_url('Persuratan/getform_suratkeluar') ?>",
            data: {
                provinsi: idPegawai
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('.tampilformsurat').html(response.data);
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Gagal', 'Form surat tidak dapat dimuat', 'error');
            }
        });
    });
});
</script>
