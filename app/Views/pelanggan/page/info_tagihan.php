<div class="row mb-3">
    <div class="col-lg-2">
        <label for="" class="form-label">Tahun</label>
        <select id="tagihan-tahun" class="form-select">
            <option value="ALL" selected="selected">Semua</option>
            <option value="2025">2025</option>
            <option value="2024">2024</option>
            <option value="2023">2023</option>
            <option value="2022">2022</option>
        </select>
    </div>
    <div class="col-lg-2">
        <label for="" class="form-label">Bulan</label>
        <select id="tagihan-bulan" class="form-select">
            <option value="ALL" selected="selected">Semua</option>
            <option value="01">Januari</option>
            <option value="02">Februari</option>
            <option value="03">Maret</option>
            <option value="04">April</option>
            <option value="05">Mei</option>
            <option value="06">Juni</option>
            <option value="07">Juli</option>
            <option value="08">Agustus</option>
            <option value="09">September</option>
            <option value="10">Oktober</option>
            <option value="11">November</option>
            <option value="12">Desember</option>
        </select>
    </div>
    <div class="col-lg-2">
        <label for="" class="form-label">Status</label>
        <select id="tagihan-status" class="form-select">
            <option value="ALL">Semua</option>
            <option value="1">Lunas</option>
            <option value="0">Menunggak</option>
        </select>
    </div>
</div>

<div class="mb-2">
    <button class="btn btn-sm btn-danger" id="exportPdf">
        <i class="fa fa-file-pdf"></i>
        PDF
    </button>
    <button class="btn btn-sm btn-success" id="exportExcel">
        <i class="fa fa-file-excel"></i>
        Excel
    </button>
</div>

<div class="table-responsive" style="max-height: 350px; overflow-y: auto">
    <table class="table table-sm table-bordered table-hover table-striped border-dark text-nowrap mb-0 align-middle" id="table-tagihan">
        <thead>
            <tr>
                <th class="text-center text-black" width="5%">#</th>
                <th class="text-center text-black" width="25%">Pelanggan</th>
                <th class="text-center text-black" width="8%">Rekening</th>
                <th class="text-center text-black">Bulan</th>
                <th class="text-center text-black">Tahun</th>
                <th class="text-center text-black">Status</th>
                <th class="text-center text-black">Jumlah</th>
                <th class="text-center text-black" width="8%">#</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('#tagihan-tahun, #tagihan-bulan').select2({
            width: '100%',
            dropdownParent: $("#modal")
        });

        get_data_tagihan();
    });

    $('#tagihan-tahun, #tagihan-bulan, #tagihan-status').change(function(e) {
        e.preventDefault();
        get_data_tagihan()
    });

    function get_data_tagihan() {
        let tahun = $('#tagihan-tahun').val();
        let bulan = $('#tagihan-bulan').val();
        let status = $('#tagihan-status').val();

        $.ajax({
            type: "get",
            url: "<?= base_url('Pelanggan/get_data_tagihan') ?>",
            data: {
                tahun: tahun,
                bulan: bulan,
                status: status,
                id_pelanggan: '<?= $id_pelanggan ?>'
            },
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
                $('#table-tagihan tbody').html(response.html);
            }
        });

    }

    function bayar(id_pemakaian, jlh_tagihan, denda) {
        Swal.fire({
            title: 'Konfirmasi',
            text: "Anda yakin ingin membayar tagihan ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Bayar!'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    type: "post",
                    url: "Pelanggan/Bayar",
                    data: {
                        id_pemakaian: id_pemakaian,
                        jlh_tagihan: jlh_tagihan,
                        denda: denda
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil',
                                icon: 'success',
                                text: 'Tagihan berhasil dibayar'
                            })
                            setTimeout(() => {

                                get_data_tagihan()
                            }, 2000);
                        }
                    }
                });
            }
        })
    }

    function delete_tagihan(id_pemakaian) {

        console.log(id_pemakaian)
        Swal.fire({
            title: 'Konfirmasi',
            text: "Anda yakin ingin menghapus tagihan ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('Pelanggan/deleteTagihan') ?>",
                    data: {
                        id_pemakaian: id_pemakaian
                    },
                    dataType: "json",
                    success: (response) => {
                        Swal.fire({
                            title: 'Berhasil',
                            icon: 'success',
                            text: 'Tagihan berhasil diHapus'
                        })
                        setTimeout(() => {

                            get_data_tagihan()
                        }, 2000);
                    }
                });
            }
        })
    }
</script>