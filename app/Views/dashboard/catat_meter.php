<div>
    <div class="row">
        <div class="col-lg-2">
            <label for="" class="form-label">Periode</label>
            <input type="date" id="catatMeter-dateStart" class="form-control" value="<?= date('2024-m-01') ?>">
        </div>
        <div class="col-lg-2">
            <label for="" class="form-label">S/D</label>
            <input type="date" id="catatMeter-dateEnd" class="form-control" value="<?= date('2024-m-d') ?>">
        </div>
        <div class="col-lg-3">
            <label for="" class="form-label">Petugas</label>
            <select id="catatMeter-petugas" class="form-select">
                <option value="all">Semua Petugas</option>
            </select>
        </div>
        <div class="col-lg-2">
            <label for="" class="form-label">Status Verifikasi</label>
            <select id="catatMeter-status" class="form-select">
                <option value="all">Semua</option>
                <option value="N" selected>Pending</option>
            <option value="Y">Disetujui</option>
            </select>
        </div>
        <div class="col-lg-2 visually-hidden">
            <label for="" class="form-label">Preview</label>
            <select id="catatMeter-preview" class="form-select">
                <option value="simpel">Simpel</option>
                <option value="detail" selected>Detail</option>
            </select>
        </div>

    </div>
    <div class="mt-3">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered table-hover border-dark mb-0 small pt-3" id="catatMeter-table">
                <thead>
                    <tr>
                        <th class="text-center align-middle text-black border-top border-dark">#</th>
                        <th class="text-center align-middle text-black border-top border-dark" width="10%">Periode</th>
                        <th class="text-center align-middle text-black border-top border-dark" width="10%">Kecamata<br>Kel/Desa</th>
                        <th class="text-center align-middle text-black border-top border-dark" width="20%">Nama Pelanggan</th>
                        <th class="text-center align-middle text-black border-top border-dark" width="10%">Golongan</th>
                        <th class="text-center align-middle text-black border-top border-dark" width="10%">Angka<br>Meter</th>
                        <th class="text-center align-middle text-black border-top border-dark" width="10%">Tanggal<br>Pencatatan</th>
                        <th class="text-center align-middle text-black border-top border-dark" width="25%">Foto Meter/Foto Rumah</th>
                        <th class="text-center align-middle text-black border-top border-dark" width="10%">Status</th>
                        <th class="text-center align-middle text-black border-top border-dark" width="20%">Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center align-middle text-black py-4" colspan="100%">DATA KOSONG</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<script>
    $(document).ready(function() {
        $('#catatMeter-dateStart, #catatMeter-dateEnd, #catatMeter-petugas, #catatMeter-status, #catatMeter-preview').change(function(e) {
            e.preventDefault();
            getDataCatatMeter()
        });
        getDataCatatMeter()
    });

    function getDataCatatMeter() {
        var petugas = $('#catatMeter-petugas').val();
        $.ajax({
            type: "post",
            url: "<?= base_url('Home/data_catat_meter') ?>",
            data: {
                awal: $('#catatMeter-dateStart').val(),
                akhir: $('#catatMeter-dateEnd').val(),
                petugas: petugas,
                status: $('#catatMeter-status').val(),
                preview: $('#catatMeter-preview').val(),
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


                
                $('#catatMeter-petugas').html(`<option value="all">Semua Petugas</option>`);
                $.each(response.petugas, function (indexPetugas, valuePetugas) {
                    var selected = (valuePetugas.PETUGASSESSION == petugas) ? 'selected' : '';
                    $('#catatMeter-petugas').append(`<option value="${valuePetugas.PETUGASSESSION}" ${selected}>${valuePetugas.NAMAPETUGAS}</option>`);
                });
                $('#catatMeter-petugas').select2();

                if ($.fn.DataTable.isDataTable('#catatMeter-table')) {
                    $('#catatMeter-table').DataTable().clear().destroy();
                }
                $('#catatMeter-table tbody').empty();

                if ($('#catatMeter-preview').val() == 'simpel') {
                } else if ($('#catatMeter-preview').val() == 'detail') {
                    $.each(response.data, function(index, value) {
                        var html = '';
    
                        html += `<tr>`
                        if (value.STATUSVERIFIKASI == 'Y') {
                            html += `<td class="text-center align-middle text-black">
                                        <button type="button" class="btn btn-sm btn-dark" title="Cek Rekening">
                                            Cek Rekening
                                        </button>
                                    </td>`
                        } else {
                            html += `<td class="text-center align-middle text-black">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary rounded" title="Verifikasi" onclick="verifikasiCatatMeter(${value.IDCATAT})">
                                                <i class="fa fa-search"></i>
                                            </button>
                                            <div class="pe-1"></div>
                                            <button type="button" class="btn btn-sm btn-danger rounded" title="Hapus" onclick="hapusCatatMeter(${value.IDCATAT})">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>`
                        }
                        html += `<td class="text-center align-middle text-black">${['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'][value.PERIODE_BULAN - 1]} ${value.PERIODE_TAHUN}</td>`
                        html += `<td class="text-center align-middle text-black"><span class="badge bg-dark fs-1">${value.NMKECAMATAN}</span><div class="pt-1"></div><span class="badge bg-warning text-black fs-1">${value.NMKELURAHAN}</span></td>`
                        html += `<td class="align-middle text-black text-uppercase">${value.NAMA_PELANGGAN}</td>`
                        html += `<td class="text-center align-middle text-black">${value.GOLONGAN}</td>`
                        if (value.ANGKA_METER == null) {
                            html += `<td class="text-center align-middle text-black">-</td>`
                        } else {
                            html += `<td class="text-center align-middle text-black">${new Intl.NumberFormat('id-ID', { style: 'decimal', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(parseFloat(value.ANGKA_METER))}</td>`
                        }
                        html += `<td class="text-center align-middle text-black">${new Date(value.TANGGALCATAT).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' })}</td>`
    
                        html += `<td class="text-center align-middle text-black">
                                    <div class="row">
                                        <a class="col-6 text-center" href="https://mywater.mokaraja.net/foto_meter/20250303_155652_FOTO_4187_1740988612_9dbaf17eaad07a3422c8.jpg" data-toggle="lightbox" data-title="Foto Meter | GTR-0739 GTR-0739|MIRNAWATI TOONAW" data-footer=" Tanggal Foto: 03 Maret 2025">
                                            <img class="w-100" style="object-fit: cover; height: 60px" src="https://mywater.mokaraja.net/foto_meter/${value.FOTOMETER}" loading="lazy" referrerpolicy="no-referrer">
                                            <br>
                                            <label class="badge bg-dark py-1 px-1" style="font-size: 0.55rem">Foto Meter</label>
                                        </a>
                                        <a class="col-6 text-center" href="https://mywater.mokaraja.net/foto_meter/no_photo.png" data-toggle="lightbox" data-title="Foto Rumah | GTR-0739 GTR-0739|MIRNAWATI TOONAW" data-footer=" Tanggal Foto: 03 Maret 2025">
                                            <img class="w-100" style="object-fit: cover; height: 60px" src="https://mywater.mokaraja.net/foto_meter/${value.FOTORUMAH}" loading="lazy" referrerpolicy="no-referrer">
                                            <br>
                                            <label class="badge bg-dark py-1 px-1" style="font-size: 0.55rem">Foto Rumah</label>
                                        </a>
                                    </div>
                                </td>`

                        if (value.STATUSVERIFIKASI == 'Y') {
                            html += `<td class="text-center align-middle text-black"><span class="badge bg-success fs-1">DISETUJUI</span></td>`
                        } else {
                            html += `<td class="text-center align-middle text-black"><span class="badge bg-danger fs-1">PENDING</span></td>`
                        }

                        html += `<td class="text-center align-middle text-black text-uppercase">${value.NAMAPETUGAS}</td>`
                        html += `</tr>`
    
                        $('#catatMeter-table tbody').append(html);
                    });
                }

                Swal.close();
                $('#catatMeter-table').DataTable({
                    "responsive": true,
                    "pageLength": 25,
                    "order": [[0, 'asc']]
                });
            }
        });
    }

    function hapusCatatMeter(id_catat_meter) { 
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data tidak dapat dikembalikan lagi!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('Home/hapus_catat_meter') ?>",
                    dataType: "json",    
                    data: {
                        id_catat_meter: id_catat_meter
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil dihapus',
                            timer: 1500
                        });
                        getDataCatatMeter();
                        loadDataForCard();
                    }
                });
            }
        });
    }

    function verifikasiCatatMeter(id_catat_meter) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Home/modal_verifikasi_catat_meter') ?>",
            data: {
                id_catat_meter: id_catat_meter
            },
            dataType: "json",
            success: function (response) {
                $('.modal-place').html(response.page);
                $('#modal').modal('show');
            }
        });
    }
</script>