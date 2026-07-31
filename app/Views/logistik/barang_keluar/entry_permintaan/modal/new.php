<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah Entry <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <div class="row">
                        <div class="col-lg-2 mb-2">
                            <label for="tanggal_permintaan" class="form-label">Tanggal Permintaan</label>
                            <input type="date" name="tanggal_permintaan" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="no_permintaan" class="form-label">No. Permintaan</label>
                            <input type="text" name="no_permintaan" class="form-control" value="PMT-<?= rand(100000, 999999) ?>">
                        </div>
                        <div class="col-lg-4 mb-2">
                            <label for="penerima" class="form-label">Karyawan</label>
                            <select type="text" name="penerima" class="form-control" id="penerima">
                                <option value="">[ Pilih Karyawan ]</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive overflow-auto">
                            <table id="table" class="table table-sm table-bordered table-hover border-dark text-nowrap mb-0 align-middle mt-2">
                                <thead>
                                    <tr>
                                        <th class="text-center text-black" width="5%">No.</th>
                                        <th class="text-center text-black" width="40%">Barang</th>
                                        <th class="text-center text-black" width="10%">Satuan</th>
                                        <th class="text-center text-black" width="15%">Stok Akhir</th>
                                        <th class="text-center text-black" width="15%">Jml. Permintaan</th>
                                        <th class="text-center text-black" width="5%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <tr>
                                        <td class="text-center text-black">1.</td>
                                        <td class="text-center text-black px-2">
                                            <select name="rows[1][barang]" class="form-control select21">
                                                <option value="">Pilih barang</option>
                                            </select>
                                        </td>
                                        <td class="text-center text-black" id="row_1_satuan">-</td>
                                        <td class="text-center text-black px-2">
                                            <input type="number" name="rows[1][stokAkhir]" class="form-control text-center stokAkhir" id="row_1_stokAkhir" placeholder="[ OTOMATIS ]" readonly>
                                        </td>
                                        <td class="text-center text-black px-2">
                                            <input type="number" name="rows[1][qty]" class="form-control text-center qty">
                                        </td>
                                        <td class="text-center text-black">
                                            <button type="button" class="btn btn-sm btn-primary addRow">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="border-top text-end pt-3 mt-4">
                        <button type="submit" class="btn btn-sm btn-success">
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
    $('#penerima').select2({
        width: '100%',
        dropdownParent: $('#modal'),
        ajax: {
            url: "<?= base_url('Logistik/BarangKeluar/Permintaan/penerima') ?>",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term
                }
            },
            processResults: function(data) {
                return {
                    results: data
                };
            }
        }
    });
</script>
<script>
    $('#form-insert').submit(function(e) {
        e.preventDefault();
        console.log($(this).serialize());
        if ($('#penerima').val() == '') {
            Swal.fire('Gagal!', 'Penerima belum dipilih', 'warning');
            return false;
        }

        $.ajax({
            type: "POST",
            url: "<?= base_url('Logistik/BarangKeluar/Permintaan/master') ?>",
            data: $(this).serialize(),
            dataType: "json",
            beforeSend: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Loading...',
                    html: 'Sedang Diproses',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        showConfirmButton: true,
                        timer: 1500
                    }).then(() => {
                        $('#modal').modal('hide');
                        location.reload();
                    });
                }
            }
        });
    });

    function row(rowCount) {
        return `
        <tr>
            <td class="text-center text-black">${rowCount}.</td>
            <td class="text-center text-black px-2">
                <select name="rows[${rowCount}][barang]" class="form-control select21">
                    <option value="">Pilih barang</option>
                </select>
            </td>
            <td class="text-center text-black" id="row_${rowCount}_satuan">Satuan</td>
            <td class="text-center text-black px-2">
                <input type="number" name="rows[${rowCount}][stokAkhir]" id="row_${rowCount}_stokAkhir" class="form-control text-center stokAkhir" placeholder="[ OTOMATIS ]" readonly>
            </td>
            <td class="text-center text-black px-2">
                <input type="number" name="rows[${rowCount}][qty]" class="form-control text-center qty"> 
            </td>
            <td class="text-center text-black">
                <button type="button" class="btn btn-sm btn-primary addRow">
                    <i class="fa fa-plus"></i>
                </button>
            </td>
        </tr>
    `;
    }

    $(document).ready(function() {
        let rowCount = 1;
        $('.select21').select2({
            placeholder: 'Pilih barang',
            width: '100%',
            dropdownParent: $('#modal'),
            ajax: {
                url: "<?= base_url('GetData/getLogistikBarang') ?>",
                type: 'GET',
                dataType: 'json',
                data: function(params) {
                    return {
                        search: params.term,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    }
                }
            }
        }).on('change', function(e) {
            $.ajax({
                type: "GET",

                url: "<?= base_url('logistik/BarangKeluar/permintaan/ambilData') ?>",
                data: {
                    id: $(this).val()
                },
                dataType: "json",
                success: function(response) {
                    $('#row_' + 1 + '_satuan').text(response.satuan);
                    $('#row_' + 1 + '_stokAkhir').val(response.stok_akhir);
                }
            });
        })

        $('#tableBody').on('click', '.addRow', function() {

            const qty = $(`#tableBody tr:last .qty`).val();
            const stokAkhir = $('#tableBody tr:last .stokAkhir').val();

            if (qty > stokAkhir) {
                $(`#tableBody tr:last .qty`).val(stokAkhir);

                return Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian..!',
                    text: 'Jumlah Permintaan tidak boleh melebihi Stok Akhir',
                });
            }

            if ($(`#tableBody tr:last .select21`).val() == '') {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian..!',
                    text: 'Pilih item terlebih dahulu',
                });
            };

            if ($(`#tableBody tr:last .qty`).val() == '') {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian..!',
                    text: 'Jumlah Permintaan Harus Diisi',
                });
            }

            if ($(`#tableBody tr:last .qty`).val() == 0) {
                $(`#tableBody tr:last .qty`).val(1)
                return Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian..!',
                    text: 'Jumlah Permintaan harus lebih besar dari 0',
                });
            }

            rowCount++;

            const newRow = row(rowCount);

            $('#tableBody').append(newRow);
            $('#tableBody tr:eq(-2) .addRow').removeClass('addRow btn-primary').addClass('removeRow btn-danger b').html('<i class="fa fa-minus"></i>');

            $(`#tableBody tr:last .select21`).select2({
                placeholder: 'Pilih barang',
                width: '100%',
                dropdownParent: $('#modal'),
                ajax: {
                    url: "<?= base_url('GetData/getLogistikBarang') ?>",
                    type: 'GET',
                    dataType: 'json',
                    data: function(params) {
                        return {
                            search: params.term,
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        }
                    }
                }
            }).on('change', function(e) {
                $.ajax({
                    type: "GET",
                    url: "<?= base_url('logistik/BarangKeluar/permintaan/ambilData') ?>",
                    data: {
                        id: $(this).val()
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#row_' + rowCount + '_satuan').text(response.satuan);
                        $('#row_' + rowCount + '_stokAkhir').val(response.stok_akhir);
                    }
                });
            })
        });

        $('#tableBody').on('click', '.removeRow', function() {
            $(this).closest('tr').remove();

            $('#tableBody tr').each(function(index) {
                $(this).find('td:first').text(index + 1 + '.');
            });

            rowCount = $('#tableBody tr').length;
        });
    });
</script>