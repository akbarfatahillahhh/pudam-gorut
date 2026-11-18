<?php
/**
 * Sub-halaman Master Tunjangan (tab kedua Slip Gaji).
 */
?>
<?= $this->include('slip_gaji/_helper') ?>
<div class="card-body p-3">
    <div class="row">
        <div class="col-lg-4 mb-3">
            <h6 class="fw-semibold mb-3">Form Tunjangan</h6>
            <form id="formTunjangan" autocomplete="off">
                <input type="hidden" name="id" id="tunjanganId">
                <div class="mb-3">
                    <label class="form-label" for="tunjanganJenis">Jenis Tunjangan</label>
                    <input type="text" class="form-control" name="jenis_tunjangan" id="tunjanganJenis"
                        maxlength="100" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="tunjanganJumlah">Jumlah (Rp)</label>
                    <input type="number" min="0" step="1" class="form-control" name="jumlah"
                        id="tunjanganJumlah" required>
                </div>
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="ti ti-device-floppy"></i>
                    Simpan
                </button>
                <button type="button" class="btn btn-sm btn-warning" id="btnResetTunjangan">
                    <i class="ti ti-rotate"></i>
                    Reset
                </button>
            </form>
        </div>

        <div class="col-lg-8 mb-3">
            <h6 class="fw-semibold mb-3">Daftar Tunjangan</h6>
            <div class="table-responsive">
                <table id="tableTunjangan" class="table table-sm table-bordered border-dark text-nowrap align-middle w-100">
                    <thead class="text-black">
                        <tr>
                            <th class="text-center align-middle border border-dark" width="5%">#</th>
                            <th class="text-center align-middle border border-dark">Tunjangan</th>
                            <th class="text-center align-middle border border-dark">Jumlah (Rp)</th>
                            <th class="text-center align-middle border border-dark" width="15%">#</th>
                        </tr>
                    </thead>
                    <tbody class="text-black">
                        <tr>
                            <td colspan="4" class="text-center align-middle">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        var table = $('#tableTunjangan').DataTable({
            processing: true,
            ajax: {
                type: 'get',
                url: "<?= base_url('SlipGaji/getDataTunjangan') ?>"
            },
            columns: [{
                    data: null,
                    class: 'text-center align-middle border border-dark',
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'jenis_tunjangan',
                    class: 'align-middle border border-dark',
                    render: function(data) {
                        return slipGajiEscape(data);
                    }
                },
                {
                    data: 'jumlah',
                    class: 'text-end align-middle border border-dark',
                    render: function(data) {
                        return slipGajiRupiah(data);
                    }
                },
                {
                    data: 'id',
                    class: 'text-center align-middle border border-dark',
                    searchable: false,
                    orderable: false,
                    render: function(data) {
                        return '<button type="button" class="btn btn-sm btn-warning btn-edit-tunjangan" data-id="' + data + '">' +
                            '<i class="ti ti-pencil"></i></button> ' +
                            '<button type="button" class="btn btn-sm btn-danger btn-hapus-tunjangan" data-id="' + data + '">' +
                            '<i class="ti ti-trash"></i></button>';
                    }
                }
            ],
            order: [
                [0, 'asc']
            ],
            language: {
                emptyTable: 'Tidak ada data tunjangan',
                zeroRecords: 'Tidak ada data tunjangan',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                paginate: {
                    previous: 'Sebelumnya',
                    next: 'Berikutnya'
                }
            }
        });

        function resetForm() {
            $('#formTunjangan')[0].reset();
            $('#tunjanganId').val('');
        }

        $('#btnResetTunjangan').on('click', resetForm);

        $('#formTunjangan').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                type: 'post',
                url: "<?= base_url('SlipGaji/saveTunjangan') ?>",
                data: $(this).serialize(),
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Menyimpan ...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire('Berhasil', response.msg, 'success');
                        resetForm();
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal', response.msg || 'Data gagal disimpan', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data (' + xhr.status + ')', 'error');
                }
            });
        });

        $('#tableTunjangan tbody').on('click', '.btn-edit-tunjangan', function() {
            var row = table.row($(this).closest('tr')).data();
            if (!row) {
                return;
            }

            $('#tunjanganId').val(row.id);
            $('#tunjanganJenis').val(row.jenis_tunjangan);
            $('#tunjanganJumlah').val(row.jumlah);
            $('#tunjanganJenis').trigger('focus');
        });

        $('#tableTunjangan tbody').on('click', '.btn-hapus-tunjangan', function() {
            var row = table.row($(this).closest('tr')).data();
            if (!row) {
                return;
            }

            Swal.fire({
                title: 'Hapus tunjangan?',
                text: 'Tunjangan "' + row.jenis_tunjangan + '" akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: "<?= base_url('SlipGaji/deleteTunjangan') ?>",
                    data: {
                        id: row.id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            Swal.fire('Berhasil', response.msg, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Gagal', response.msg || 'Data gagal dihapus', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data (' + xhr.status + ')', 'error');
                    }
                });
            });
        });
    });
</script>
