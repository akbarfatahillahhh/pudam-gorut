<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered h-95">
        <div class="modal-content h-95">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">EXPORT DATA PELANGGAN</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-lg-3">
                        <label for="" class="form-label">Filter Pelanggan</label>
                        <select name="" id="pdf-filter-pelanggan" class="form-control">
                            <option value="wilayah" selected>Berdasarkan Wilayah</option>
                            <option value="kelompok">Berdasarkan Kelompok</option>
                            <option value="golongan">Berdasarkan Golongan</option>
                        </select>
                    </div>
                    <div class="col-lg-3 pdf-filter-wilayah">
                        <label for="" class="form-label">Kecamatan</label>
                        <select name="" id="pdf-kecamatan" class="form-control">
                            <option value="ALL">[ Pilih Kecamatan ]</option>
                        </select>
                    </div>
                    <div class="col-lg-3 pdf-filter-wilayah">
                        <label for="" class="form-label">Kelurahan/Desa</label>
                        <select name="" id="pdf-kelurahan" class="form-control">
                            <option value="ALL">[ Pilih Kelurahan/Desa ]</option>
                        </select>
                    </div>
                    <div class="col-lg-3 pdf-filter-kelompok">
                        <label for="" class="form-label">Kelompok</label>
                        <select name="" id="pdf-kelompok" class="form-control">
                            <option value="ALL">[ Pilih Kelompok ]</option>
                        </select>
                    </div>
                    <div class="col-lg-3 pdf-filter-golongan">
                        <label for="" class="form-label">Golongan</label>
                        <select name="" id="pdf-golongan" class="form-control">
                            <option value="ALL">[ Pilih Golongan ]</option>
                        </select>
                    </div>
                    <div class="col-lg-2 mt-auto">
                        <button class="btn btn-primary" id="btnGeneratePdf">
                            <i class="fa fa-file-pdf"></i>
                            Proses Data
                        </button>
                    </div>
                </div>
                <div class="h-90 w-100" id="place-pdf">
                    <div class="py-5 border-top border-dark my-auto text-center">
                        <h1 class="pt-5"><i class="fa fa-file-pdf"></i></h1>
                        <h5 class="pb-5">PREVIEW LAPORAN</h5>
                    </div>
                    <iframe src="" class="h-90 w-100"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('.pdf-filter-kelompok, .pdf-filter-golongan').hide();
        var pdfFilterPelanggan = $('#pdf-filter-pelanggan').find('option:selected').val();
        pdfFiltering(pdfFilterPelanggan);

    });

    $('#btnGeneratePdf').click(function(e) {
        e.preventDefault();
        var pdfFilterPelanggan = $('#pdf-filter-pelanggan').find('option:selected').val();
        var pdfFilterKecamatan = $('#pdf-kecamatan').find('option:selected').val();
        var pdfFilterKelurahan = $('#pdf-kelurahan').find('option:selected').val();
        var pdfFilterKelompok = $('#pdf-kelompok').find('option:selected').val();
        var pdfFilterGolongan = $('#pdf-golongan').find('option:selected').val();

        if (pdfFilterPelanggan == 'wilayah') {
            if (pdfFilterKecamatan == 'ALL' || pdfFilterKecamatan == '' || pdfFilterKecamatan == null || pdfFilterKecamatan == undefined) {
                Swal.fire('Maaf!', 'Kecamatan Harus dipilih!', 'warning');
                return false;
            }
            if (pdfFilterKelurahan == 'ALL' || pdfFilterKelurahan == '' || pdfFilterKelurahan == null || pdfFilterKelurahan == undefined) {
                Swal.fire('Maaf!', 'Kelurahan/Desa Harus dipilih!', 'warning');
                return false;
            }
        } else if (pdfFilterPelanggan == 'kelompok') {
            if (pdfFilterKelompok == 'ALL' || pdfFilterKelompok == '' || pdfFilterKelompok == null || pdfFilterKelompok == undefined) {
                Swal.fire('Maaf!', 'Kelompok Harus dipilih!', 'warning');
                return false;
            }
        } else if (pdfFilterPelanggan == 'golongan') {
            if (pdfFilterGolongan == 'ALL' || pdfFilterGolongan == '' || pdfFilterGolongan == null || pdfFilterGolongan == undefined) {
                Swal.fire('Maaf!', 'Golongan Harus dipilih!', 'warning');
                return false;
            }
        }

        $('#place-pdf iframe').hide();
        $('#place-pdf div').remove();

        $('#place-pdf').prepend(`
            <div class="loading text-center text-black h3">
                <div class="py-5 border border-dark my-auto text-center">
                    <h1 class="pt-5"><i class="fas fa-spinner"></i></h1>
                    <h5 class="pb-5">Loading...</h5>
                </div>
            </div>
        `);

        $('#place-pdf iframe').on('load', function() {
            $('#place-pdf .loading').remove();
            $('#place-pdf iframe').show();
        }).attr('src', "<?= base_url('Master/Pelanggan/export_pdf') ?>/" + pdfFilterPelanggan + "/" + pdfFilterKecamatan + "/" + pdfFilterKelurahan + "/" + pdfFilterKelompok + "/" + pdfFilterGolongan);
    });

    $('#pdf-filter-pelanggan').change(function(e) {
        e.preventDefault();
        var pdfFilterGolongan = $(this).val();
        if (pdfFilterGolongan == 'wilayah') {
            $('.pdf-filter-wilayah').show();
            $('.pdf-filter-kelompok, .pdf-filter-golongan').hide();
            pdfFiltering(pdfFilterGolongan);
        } else if (pdfFilterGolongan == 'kelompok') {
            $('.pdf-filter-kelompok').show();
            $('.pdf-filter-wilayah, .pdf-filter-golongan').hide();
            pdfFiltering(pdfFilterGolongan);
        } else if (pdfFilterGolongan == 'golongan') {
            $('.pdf-filter-golongan').show();
            $('.pdf-filter-wilayah, .pdf-filter-kelompok').hide();
            pdfFiltering(pdfFilterGolongan);
        }
    });

    function pdfFiltering(data) {
        $.ajax({
            type: "post",
            url: "<?= base_url('GetData/Filtering') ?>",
            data: {
                data: data
            },
            dataType: "json",
            success: function(response) {
                if (data === 'wilayah') {
                    if (response.kecamatan) {
                        $('#pdf-kecamatan').html(`<option value="ALL">[ Pilih Kecamatan ]</option>`);
                        $.each(response.kecamatan, function(index, value) {
                            $('#pdf-kecamatan').append(`<option value="${value.id}">${value.text}</option>`);
                        });
                        $('#pdf-kecamatan').select2({
                            width: '100%',
                            dropdownParent: $("#modal")
                        });
                    }
                } else if (data === 'kelompok') {
                    if (response.kelompok) {
                        $('#pdf-kelompok').html(`<option value="ALL">[ Pilih Kelompok ]</option>`);
                        $.each(response.kelompok, function(index, value) {
                            $('#pdf-kelompok').append(`<option value="${value.id}">${value.text}</option>`);
                        });
                        $('#pdf-kelompok').select2({
                            width: '100%',
                            dropdownParent: $("#modal")
                        });
                    }
                } else if (data === 'golongan') {
                    if (response.golongan) {
                        $('#pdf-golongan').html(`<option value="ALL">[ Pilih Golongan ]</option>`);
                        $.each(response.golongan, function(index, value) {
                            $('#pdf-golongan').append(`<option value="${value.id}">${value.text}</option>`);
                        });
                        $('#pdf-golongan').select2({
                            width: '100%',
                            dropdownParent: $("#modal")
                        });
                    }
                }
            }
        });
    }

    $('#pdf-kecamatan').change(function(e) {
        e.preventDefault();
        var filterKelurahan = $(this).find('option:selected').val();
        $.ajax({
            type: "post",
            url: "<?= base_url('GetData/FilterKelurahan') ?>",
            data: {
                id: filterKelurahan
            },
            dataType: "json",
            success: function(response) {
                $('#pdf-kelurahan').html(`<option value="ALL">[ Semua Kelurahan/Desa ]</option>`);
                $.each(response.kelurahan, function(index, value) {
                    $('#pdf-kelurahan').append(`<option value="${value.id}">${value.text}</option>`);
                });
                $('#pdf-kelurahan').select2({
                    width: '100%',
                    dropdownParent: $("#modal")
                });
            }
        });
    });
</script>