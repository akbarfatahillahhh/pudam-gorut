<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah Absensi</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAbsen">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="nama" class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="nama" class="form-label">Unit</label>
                            <select name="unit" id="unit" class="form-select">
                                <option value="all" selected>Semua Unit</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-5">
                        <div class="table-responsive" style="height: 450px; overflow-y: auto;">
                            <table class="table table-sm table-bordered table-hover table-striped border-dark text-nowrap mb-0 align-middle" id="tableAbsen">
                                <thead class="text-white bg-dark">
                                    <tr>
                                        <th class="text-center align-middle" width="3%">#</th>
                                        <th class="text-center align-middle">Nama Pegawai</th>
                                        <th class="text-center align-middle">KETERANGAN</th>
                                    </tr>
                                </thead>
                                <tbody class="text-black">
                                    <td class="text-center align-middle" colspan="100%">Tidak ada data</td>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        getDataPegawai();
    });

    $('#unit').select2({
        width: '100%',
        dropdownParent: $('#modal'),
        placeholder: '[ Pilih Unit ]',
        allowClear: true,
        ajax: {
            url: "<?= base_url('GetData/getUnitByTitikKoordinat') ?>",
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                let units = $.map(data, function(item) {
                    return {
                        id: item.id,
                        text: item.text
                    };
                });

                units.unshift({
                    id: 'all',
                    text: 'SEMUA UNIT'
                });

                return {
                    results: units
                };
            }
        }
    });

    $('#unit').on('change', function(e) {
        e.preventDefault();
        getDataPegawai();
    });

    function getDataPegawai() {
        var unit = $('#unit').val();
        $.ajax({
            type: "post",
            url: "<?= base_url('Absensi/getDataPegawai') ?>",
            data: {
                unit: unit
            },
            dataType: "json",
            success: function(response) {
                $('#tableAbsen tbody').html('');
                var no = 1;
                response.forEach(function(item) {
                    $('#tableAbsen tbody').append(`
                            <tr>
                                <td class="text-black text-center fw-bold">${no++}</td>
                                <td class="text-uppercase text-black fw-bold">${item.nama_lengkap}</td>
                                <td class="text-uppercase text-black text-center fw-bold">${item.aksi}</td>
                            </tr>
                        `);
                });
            }
        });
    }

    $('.absen-datang').click(function(e) {
        e.preventDefault();
        var latitude = $(this).data('latitude');
        var longtitude = $(this).data('longtitude');
        var id_pegawai = $(this).data('pegawai');
        var value = $(this).val();

        console.log(latitude, longtitude, id_pegawai, value);
    });

    $('#formAbsen').submit(function(e) {
        e.preventDefault();
        if ($('input[type="radio"]:checked', '#tableAbsen tbody').length < $('#tableAbsen tbody tr').length || $('#unit').val() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Gagal',
                text: 'Isikan semua Kehadihan terlebih dahulu',
                showConfirmButton: false,
                timer: 1500
            });
            return false;
        }
        $.ajax({
            type: "post",
            url: "<?= base_url('Absensi/simpanAbsensi') ?>",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.status == 'success') {
                    //   Swal.fire({
                    //       icon: 'success',
                    //       title: 'Berhasil',
                    //       text: response.message,
                    //       showConfirmButton: false,
                    //       timer: 1500
                    //   }).then(() => {
                    //       $('#modal').modal('hide');
                    //       $('#table').DataTable().ajax.reload();
                    //   });
                } else if (response.status == 'error') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gagal',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }
        });
    })
</script>