<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel"><?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-5">
                        <div class="table-responsive-off" style="height: 550px; overflow-y: auto; overflow-x: auto;">
                            <table class="table table-sm table-bordered table-hover border-dark table-striped small pt-3" id="table-barang">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th class="text-center align-middle text-white fw-normal">KODE BARANG</th>
                                        <th class="text-center align-middle text-white fw-normal">BARANG</th>
                                        <th class="text-center align-middle text-white fw-normal">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center align-middle text-danger" colspan="100%">DATA KOSONG</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-7">
                        <form class="forms">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <div class="form-group">
                                        <label for="tanggal" class="text-black small">TANGGAL</label>
                                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="form-group">
                                        <label for="no_opname" class="text-black small">NO OPNAME</label>
                                        <input type="text" name="no_opname" id="no_opname" class="form-control form-control-sm" value=" OPTB-<?= date('Ymd') . rand(100, 999) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive" style="height: 500px; overflow-y: auto; overflow-x: auto;">
                                <table class="table table-bordered table-hover table-sm table-striped border-dark" id="table-barang-opname">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th class="text-center align-middle fw-normal">NO</th>
                                            <th class="text-center align-middle fw-normal">KODE BARANG</th>
                                            <th class="text-center align-middle fw-normal">BARANG</th>
                                            <th class="text-center align-middle fw-normal">STOK AKHIR</th>
                                            <th class="text-center align-middle fw-normal">STOK SEKARANG</th>
                                            <th class="text-center align-middle fw-normal">#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center align-middle fw-semibold text-black" colspan="100%">NO DATA</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <button class="btn btn-success" id="btnTambahBarang" type="submit">
                                    <i class="fa fa-save"></i>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#table-barang').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        ajax: {
            url: '<?= base_url('Logistik/Gudang/OpnameStok/dataBarang') ?>',
            type: 'POST',
        },
        columns: [{
                data: 'barang_kode',
                name: 'barang_kode',
                class: 'text-center text-black py-1 border-bottom border-dark',
                width: '25%'
            },
            {
                data: 'barang_nama',
                name: 'barang_nama',
                class: 'text-black py-1 border-bottom border-dark'
            },
            {
                data: 'aksi',
                name: 'aksi',
                class: 'text-center text-black py-1 border-bottom border-dark',
                searchable: false,
                width: '15%'
            },
        ]
    })

    function pilihBarang(el, kode) {
        const tbody = $('#table-barang-opname tbody');
        const btnPilih = $(el);

        const existingRow = tbody.find(`tr[data-kode="${kode}"]`);
        if (existingRow.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Barang ini sudah ditambahkan!',
                showConfirmButton: true,
                confirmButtonText: 'OK'
            });
            return;
        }

        const noDataRow = tbody.find('tr:contains("NO DATA")');
        if (noDataRow.length > 0) {
            noDataRow.remove();
        }

        $.ajax({
            url: '<?= base_url('Logistik/Gudang/OpnameStok/pilihBarang') ?>',
            type: 'POST',
            data: {
                kode: kode
            },
            dataType: 'json',
            success: function(response) {
                if (Array.isArray(response.data) && response.data.length > 0) {
                    btnPilih.removeClass('btn-primary').addClass('btn-success').html('<i class="fa-solid fa-circle-check"></i> Pilih');
                    let no = tbody.find('tr').length + 1;
                    $.each(response.data, function(index, item) {
                        $('#table-barang-opname').append(
                            `<tr data-kode="${item.barang_kode}">
                                <td class="text-center text-black">${no++}</td>
                                <td class="text-center text-black">${item.barang_kode}</td>
                                <td class="text-black">${item.barang_nama}</td>
                                <td class="text-center text-black">${item.stok_akhir}</td>
                                <td width="30%">
                                    <input type="number" class="form-control form-control-sm bg-white text-center" name="qty[${item.barang_kode}]" value="0">
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-sm" id="btnDelete" type="button" data-kode="${item.barang_kode}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`
                        )
                    });
                }
            }
        })

        let btnActive = {};

        $('#table-barang').on('click', '.btnPilihBarang', function() {
            const btnPilih = $(this);
            const kode = btnPilih.data('kode');
            console.log(btnActive[kode]);
            if (btnActive[kode]) {
                delete btnActive[kode];
                btnPilih.removeClass('btn-success')
                    .addClass('btn-primary')
                    .html('<i class="fa-solid fa-circle-check"></i> Pilih');
            } else {
                btnActive[kode] = true;
                btnPilih.removeClass('btn-primary')
                    .addClass('btn-success')
                    .html('<i class="fa-solid fa-check-circle"></i> Dipilih');
            }
        });

        $('#table-barang').on('draw.dt', function() {
            $('#table-barang').find('.btnPilihBarang').each(function() {
                const btnPilih = $(this);
                const kode = btnPilih.data('kode');

                if (btnActive[kode]) {
                    btnPilih.removeClass('btn-primary')
                        .addClass('btn-success')
                        .html('<i class="fa-solid fa-check-circle"></i> Dipilih');
                } else {
                    btnPilih.removeClass('btn-success')
                        .addClass('btn-primary')
                        .html('<i class="fa-solid fa-circle-check"></i> Pilih');
                }
            });
        });


        $(document).on('click', '#btnDelete', function(e) {
            e.preventDefault();
            const kode = $(this).data('kode');
            const btnPilihUhuy = $(`#btnPilihBarang[data-kode="${kode}"]`);
            $('#table-barang-opname tbody').find(`tr[data-kode="${kode}"]`).remove();
            if ($('#table-barang-opname tbody tr').length == 0) {
                $('#table-barang-opname tbody').append(
                    `<tr>
                        <td class="text-center align-middle" colspan="100%" >NO DATA</td>
                    </tr>`
                );
            }
            btnPilihUhuy.removeClass('btn-success').addClass('btn-primary').html('<i class="fa-solid fa-circle-check"></i> Pilih');
        });
    }

    $('.forms').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "<?= base_url('Logistik/Gudang/OpnameStok/simpanOpnameBarang') ?>",
            data: $(this).serialize(),
            dataType: "json",
            beforeSend: function() {
                $('#btnSave').attr('disabled', 'disabled');
                $('#btnSave').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
            },
            success: function(response) {
                $('#btnSave').attr('disabled', false);
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
                if (response.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    })
                }
            },
        });
    })
</script>