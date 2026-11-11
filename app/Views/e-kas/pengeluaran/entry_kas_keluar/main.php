<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>
<div class="card card-body border border-dark">
    <div class="row">
        <div class="col-6">
            <h3 class="card-title"><?= $title ?></h3>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="alert alert-warning text-black text-center small py-2">
            <strong>Perhatian..!</strong> Masukkan data-data dengan baik dan benar, baca terlebih dahulu sebelum diproses!
        </div>
    </div>
    <form id="form-input">
        <div class="row">
            <div class="col-6">
                <div class="row">
                    <div class="col-7">
                        <div class="mb-2">
                            <label class="form-label mb-1 small">No. Voucher</label>
                            <input type="text" class="form-control form-control-sm" name="no_vocer" placeholder="Kosongkan jika ingin dibuat otomatis">
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="mb-2">
                            <label class="form-label mb-1 small">Tanggal Pembayaran</label>
                            <input type="date" class="form-control form-control-sm" name="tanggal_vocer" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-2">
                            <label class="form-label mb-1 small">No. Cek / Giro</label>
                            <input type="text" class="form-control form-control-sm" name="no_giro" placeholder="No. Cek/Giro...">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-2">
                            <label class="form-label mb-1 small">Beban Bagian Seksi</label>
                            <input type="text" class="form-control form-control-sm" name="bagian_direksi" placeholder="Beban Bagian Seksi...">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-2">
                            <label class="form-label mb-1 small">Penjelasan Singkat</label>
                            <textarea type="text" class="form-control form-control-sm" name="keterangan" placeholder="Penjelasan Singkat..." rows="10"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-2">
                            <label class="form-label mb-1 small">Bank</label>
                            <select class="form-select form-select-sm" name="bank" id="bank">
                                <option value="" selected>[ Pilih Bank ]</option>
                                <option value="BRI">BRI</option>
                                <option value="MANDIRI">MANDIRI</option>
                                <option value="SULUT">SULUT</option>
                                <option value="BTN">BTN</option>
                                <option value="BNI">BNI</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-2">
                            <label class="form-label mb-1 small">Status Bayar</label>
                            <select class="form-select form-select-sm" name="status">
                                <option value="" selected>[ Pilih Status Bayar ]</option>
                                <option value="Y">Pengeluaran Bukan Hutang</option>
                                <option value="N">Pengeluaran Hutang Lalu</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-2">
                            <label class="form-label mb-1 small">Dibayarkan Kepada</label>
                            <input type="text" class="form-control form-control-sm" name="dibayar_kepada" placeholder="Dibayarkan Kepada...">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-2">
                            <label class="form-label mb-1 small">Yang Menerima</label>
                            <input type="text" class="form-control form-control-sm" name="yg_menerima" placeholder="Yang Menerima...">
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="card mb-0">
                            <div class="card-body border border-dark py-2 px-3 rounded">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <h6 class="fw-semibold mb-0 text-dark">PERKIRAAN</h6>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3 d-flex justify-content-end">
                                            <button type="submit" class="btn btn-success btn-sm me-2 sr-only" id="btnSave">
                                                <i class="fa fa-save"></i>
                                                Simpan
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm" id="btnAdd">
                                                <i class="fa fa-plus"></i>
                                                Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="display-none" style="height: 250px; overflow-y: scroll">
                                    <div class="card card-body border border-dark">
                                        <h6 class="fw-normal mb-0 text-muted text-center">Tambah Perkiraan</h6>
                                    </div>
                                </div>
                                <div class="display-plus d-none mx-0 pe-2" style="height: 250px; overflow-y: auto; overflow-x: hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
<?= $this->section('script') ?>
<script>
    $(document).ready(function () {
        getDataBank();
    });

    $('#btnAdd').click(function(e) {
        e.preventDefault();
        var row = $('.display-plus .card').length + 1;
        var card = `
                    <div class="card card-body border border-dark mb-2 px-2 py-2 shadow-none">
                        <div class="row px-1">
                            <div class="col-10">
                                <div class="row">
                                    <div class="mb-2">
                                        <select class="form-select form-select-sm perkiraan-filter" name="idperkiraanhijauh[]">
                                            <option value="" selected>[ PILIH DAN CARI NO PERKIRAAN / NAMA PERKIRAAN ]</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm border border-dark text-center" name="debethijauh[]" placeholder="DEBET">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm border border-dark text-end" name="kredithijauh[]" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-2 text-center my-auto">
                                <button class="btn btn-danger btn-sm w-100 remove-card">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>`;
        $('#btnSave').removeClass('sr-only');
        $('.display-none').addClass('d-none');
        $('.display-plus').removeClass('d-none').append(card);


        $('.perkiraan-filter').last().select2({
            placeholder: "[ PILIH DAN CARI NO PERKIRAAN / NAMA PERKIRAAN ]",
            allowClear: true,
            ajax: {
                url: '<?= base_url('GetData/getPerkiraan') ?>',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {

                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.text
                            };
                        })
                    };
                }
            }
        });
    });

    $(document).on('click', '.remove-card', function() {
        $(this).closest('.card').remove();
    });

    $(document).on('click', '.remove-card', function(e) {
        e.preventDefault();
        $(this).closest('.card').remove();

        if ($('.display-plus .card').length == 0) {
            $('#btnSave').addClass('sr-only');
            $('.display-none').removeClass('d-none');
            $('.display-plus').addClass('d-none');
            $('#btnAdd').removeClass('d-none');
        }
    });


    $('#form-input').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: "<?= base_url('E_Kas/Pengeluaran/EntryKas_Keluar/simpan_merah') ?>",
            type: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    })
                } else if (response.status == false) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pengingat!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
            }
        });
    });

    function getDataBank() {
        $.ajax({
            type: "get",
            url: "<?= base_url('GetData/getBank') ?>",
            dataType: "json",
            success: function (response) {
                var html = '<option value="" selected>[ Pilih Bank ]</option>';
                $.each(response, function (index, value) { 
                    html += `<option value="${value.id}">${value.text}</option>`;
                });
                $('#LIST_BANK').html(html);
            }
        });
    }
</script>
<?= $this->endSection() ?>