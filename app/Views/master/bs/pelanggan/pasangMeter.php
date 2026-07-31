<div class="card border border-dark">
    <div class="card-body p-4">
        <a href="<?= base_url('master/pelanggan') ?>" class="badge bg-danger text-light mb-4">Kembali</a>
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4"><?= $title ?></h5>
            </div>
            <!-- <div class="col-6 text-end">

                <div class="dropdown open">
                    <button
                        class="btn btn-primary dropdown-toggle"
                        type="button"
                        id="triggerId"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false">
                        <i class="fa fa-plus-circle"></i> Tambah
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId">
                        <button class="dropdown-item text-black" id="btnAdd">
                            <i class="fa fa-user-plus text-primary pe-2"></i>
                            Pelanggan Baru
                        </button>
                        <button class="dropdown-item text-black" href="#">
                            <i class="fa fa-user-cog text-warning pe-2"></i>
                            Pelanggan Belum Terpasang Meter
                        </button>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-plus-circle"></i> Tambah
                </button>
            </div> -->
        </div>

        <div class="row mb-3">
            <div class="col-lg-3">
                <label for="" class="form-label">Filter Pelanggan</label>
                <select name="" id="filter-pelanggan" class="form-control">
                    <option value="wilayah" selected>Berdasarkan Wilayah</option>
                    <option value="kelompok">Berdasarkan Kelompok</option>
                    <option value="golongan">Berdasarkan Golongan</option>
                </select>
            </div>
            <div class="col-lg-3 filter-wilayah">
                <label for="" class="form-label">Kecamatan</label>
                <select name="" id="kecamatan" class="form-control">
                    <option value="">[ Pilih Kecamatan ]</option>
                </select>
            </div>
            <div class="col-lg-3 filter-wilayah">
                <label for="" class="form-label">Kelurahan/Desa</label>
                <select name="" id="kelurahan" class="form-control">
                    <option value="">[ Pilih Kelurahan/Desa ]</option>
                </select>
            </div>
            <div class="col-lg-3 filter-kelompok">
                <label for="" class="form-label">Kelompok</label>
                <select name="" id="kelompok" class="form-control">
                    <option value="">[ Pilih Kelompok ]</option>
                </select>
            </div>
            <div class="col-lg-3 filter-golongan">
                <label for="" class="form-label">Golongan</label>
                <select name="" id="golongan" class="form-control">
                    <option value="">[ Pilih Golongan ]</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="table" class="table w-100 table-sm table-bordered table-hover table-striped border-dark text-nowrap mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="border-top border-dark">#</th>
                        <th class="border-top border-dark">NO & TANGGAL REGISTRASI</th>
                        <th class="border-top border-dark">PELANGGAN</th>
                        <th class="border-top border-dark">KELOMPOK & JENIS TARIF</th>
                        <th class="border-top border-dark">ALAMAT</th>
                        <th class="border-top border-dark">BEA. PEMASANGAN</th>
                        <th class="border-top border-dark">STATUS PASANG</th>
                        <th class="border-top border-dark">STATUS PASANG</th>
                        <!-- <th class="border-top border-dark">Kelompok</th>
                        <th class="border-top border-dark">Golongan</th>
                        <th class="border-top border-dark">#</th> -->
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-place"></div>

<script>
    function setAktifPelanggan(id) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pelanggan/setAktifPelanggan') ?>",
            data: {
                id: id
            },
            dataType: "json",
            success: function(response) {
                Swal.fire({
                    title: 'Berhasil',
                    text: 'Pelanggan berhasil diaktifkan',
                    icon: 'success',
                    showConfirmButton: true,
                    timer: 1500
                })
                $('#table').DataTable().ajax.reload(null, false);


            }
        });
    }
    $(document).ready(function() {

        $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "<?= base_url('Master/Pelanggan/dataPasangMeter') ?>",
                type: 'POST'
            },
            columns: [{
                    class: 'text-center align-middle py-1 text-dark',
                    render: function(row, type, set, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: null,
                    name: 'tgl_registrasi, no_reg',
                    class: 'text-center align-middle py-1 text-dark',
                    render: function(data, type, row) {
                        return `<small class="text-center badge bg-primary">${row.no_reg}</small><br><strong>${row.tgl_registrasi}</strong>`;
                    }
                },
                {
                    data: 'nama_lengkap',
                    name: 'nama_lengkap'
                },
                {
                    data: 'kelompok_jasa',
                    name: 'kelompok_jasa',
                    class: 'text-center align-middle py-1 text-dark',
                    render: function(data, type, row) {
                        return `
                            ${row.kelompok_jasa} - ${row.golongan}
                        `;
                    }
                },
                {
                    data: 'alamat',
                    name: 'alamat'
                },
                {
                    data: null,
                    name: 'biaya_pemasangan',
                    render: function(data, type, row) {
                        // Pastikan konversi ke angka
                        const biaya = parseFloat(row.biaya_pemasangan);
                        if (isNaN(biaya)) {
                            return '-'; // Jika data tidak valid, tampilkan tanda
                        }
                        return 'Rp ' + biaya.toLocaleString();
                    }
                },
                {
                    data: null,
                    name: 'status_pasang_meter',
                    class: 'text-center align-middle py-1 text-dark',
                    render: function(data, type, row) {

                        return `${row.status_pasang == 'Y' ? 'Sudah Terpasang' : 'Belum Terpasang'}`;
                    }
                },
                {
                    data: 'aktif',
                    name: 'aktif',
                    class: 'text-center align-middle py-1 text-dark'
                }
            ]
        });
    });
</script>