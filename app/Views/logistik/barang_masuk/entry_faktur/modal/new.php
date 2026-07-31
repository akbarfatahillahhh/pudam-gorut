<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5 text-black" id="modalLabel">Entry Faktur Pembelian</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <div class="row">
                        <div class="col-lg-3 mb-2">
                            <label for="no_permintaan" class="form-label text-black">No. Faktur</label>
                            <input type="text" name="no_faktur" class="form-control" placeholder="No. Faktur/Nota" required>
                        </div>
                        <div class="col-lg-2 mb-2">
                            <label for="tanggal_permintaan" class="form-label text-black">Tanggal Faktur</label>
                            <input type="date" name="tanggal_faktur" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="penerima" class="form-label text-black">Penyedia</label>
                            <input type="text" name="penyedia" class="form-control" placeholder="Nama Penyedia" required>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <table id="table"
                            class="table table-sm table-bordered border-dark text-nowrap mb-0 align-middle mt-2">
                            <thead>
                                <tr>
                                    <th class="text-center text-black" width="3%">No.</th>
                                    <th class="text-center text-black" width="30%">Barang</th>
                                    <th class="text-center text-black">Satuan</th>
                                    <th class="text-center text-black">Stok Akhir</th>
                                    <th class="text-center text-black">QTY</th>
                                    <th class="text-center text-black">Harga Satuan</th>
                                    <th class="text-center text-black">#</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr>
                                    <td class="text-center">1.</td>
                                    <td class="text-center">
                                        <select name="rows[1][barang]" class="form-control form-control-sm select2" required>
                                            <option value="">Pilih barang</option>
                                        </select>
                                    </td>
                                    <td class="text-center text-black" id="row_1_satuan"><b>-</b></td>
                                    <td class="text-center">
                                        <input type="number" name="rows[1][stokAkhir]" class="form-control text-center form-control-sm" id="row_1_stokAkhir" readonly>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" name="rows[1][qty]" class="form-control text-center form-control-sm qty" required>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" name="rows[1][harga]" class="form-control text-end form-control-sm harga" id="row_1_harga" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary addRow">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
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
    $('#form-insert').submit(function(e) {
        e.preventDefault();
        if ($(this).serialize() == '') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Form tidak boleh kosong',
                showConfirmButton: true
            });
            return false;
        };
        $.ajax({
            type: "get",
            url: "<?= base_url('Logistik/BarangMasuk/Penerimaan/master') ?>",
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
                // Swal.close();
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

                if (response.status === 'error') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message,
                        showConfirmButton: true
                    });
                }
            }
        });
    });

    function row(rowCount) {
        return `
        <tr>
            <td class="text-center">${rowCount}.</td>
            <td class="text-center">
                <select name="rows[${rowCount}][barang]" class="form-control form-control-sm select2">
                    <option value="">Pilih barang</option>
                </select>
            </td>
            <td class="text-center" id="row_${rowCount}_satuan">Satuan</td>
            <td class="text-center">
                <input type="number" name="rows[${rowCount}][stokAkhir]" id="row_${rowCount}_stokAkhir" class="form-control text-center form-control-sm" readonly>
            </td>
            <td class="text-center">
                <input type="number" name="rows[${rowCount}][qty]" class="form-control text-center form-control-sm qty">
            </td>
            <td class="text-center">
                <input type="number" name="rows[${rowCount}][harga]" class="form-control text-center form-control-sm harga">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-primary addRow">
                    <i class="fa fa-plus"></i>
                </button>
            </td>
        </tr>
    `;
    }

    $(document).ready(function() {
        let rowCount = 1;

        $('.select2').select2({
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
                url: "<?= base_url('Logistik/BarangKeluar/Permintaan/ambilData') ?>",
                data: {
                    id: $(this).val()
                },
                dataType: "json",
                success: function(response) {
                    $('#row_' + 1 + '_satuan').text(response.satuan);
                    $('#row_' + 1 + '_stokAkhir').val(response.stok_akhir);
                    $('#row_' + 1 + '_harga').val(response.harga);
                }
            });
        })


        $('#tableBody').on('click', '.addRow', function() {
            if ($('#tableBody tr:last .select2').val() == '') {
                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Pilih item terlebih dahulu',
                });
            }

            if ($('#tableBody tr:last .qty').val() < 0) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'QTY Harus Diisi atau lebih dari 0',
                });
            }

            if ($('#tableBody tr:last .harga').val() < 0) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Harga Harus Diisi atau lebih dari 0',
                });
            }

            rowCount++;
            const newRow = row(rowCount);
            $('#tableBody').append(newRow);
            $('#tableBody tr:eq(-2) .addRow').removeClass('addRow btn-primary').addClass('removeRow btn-danger b').html('<i class="fa fa-minus"></i>');

            $(`#tableBody tr:last .select2`).select2({
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
                    url: "<?= base_url('Logistik/BarangKeluar/Permintaan/ambilData') ?>",
                    data: {
                        id: $(this).val()
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#row_' + rowCount + '_satuan').text(response.satuan);
                        $('#row_' + rowCount + '_stokAkhir').val(response.stok_akhir);
                        $('#row_' + rowCount + '_harga').val(response.harga);
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