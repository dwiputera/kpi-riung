<?php
$months = [
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December'
];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit MTS <strong><?= $year ?></strong></h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Trainings</h3>
            </div>
            <form action="<?= base_url() ?>training/MTS/submit" method="post" id="data-form">
                <div class="card-body">
                    <?php if (!$advanced) : ?>
                        <a href="<?= base_url() ?>training/MTS/edit/<?= $year ?>?advanced=true" class="btn btn-warning w-100">Edit Advanced</a><br><br>
                    <?php else : ?>
                        <button type="button" class="btn btn-info w-100" data-toggle="modal" data-target="#modal-inputGuide">
                            Petunjuk Pengisian
                        </button><br><br>
                    <?php endif; ?>
                    <input type="hidden" name="year" value="<?= $year ?>">
                    <input type="hidden" name="proceed" value="Y">
                    <input type="hidden" name="json_data" id="json_data">

                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>No</th>
                                <th>STATUS</th>
                                <th>MONTH</th>
                                <th>DEPARTEMEN PENGAMPU</th>
                                <th>NAMA PROGRAM</th>
                                <th>BATCH</th>
                                <th>JENIS KOMPETENSI</th>
                                <th>SASARAN KOMPETENSI</th>
                                <th>LEVEL KOMPETENSI</th>
                                <th>TARGET PESERTA</th>
                                <th>STAFF/NONSTAFF</th>
                                <th>KATEGORI PROGRAM</th>
                                <th>FASILITATOR</th>
                                <th>NAMA PENYELENGGARA/FASILITATOR</th>
                                <th>TEMPAT</th>
                                <th>ONLINE / OFFLINE</th>
                                <th>START DATE</th>
                                <th>END DATE</th>
                                <th>DAYS</th>
                                <th>HOURS</th>
                                <th>TOTAL HOURS</th>
                                <th>RMHO</th>
                                <th>RMIP</th>
                                <th>REBH</th>
                                <th>RMTU</th>
                                <th>RMTS</th>
                                <th>RMGM</th>
                                <th>RHML</th>
                                <th>TOTAL JOBSITE</th>
                                <th>TOTAL PARTICIPANTS</th>
                                <th>GRAND TOTAL HOURS</th>
                                <th>BIAYA PELATIHAN/ ORANG</th>
                                <th>BIAYA PELATIHAN</th>
                                <th>TRAINING KIT/ORANG</th>
                                <th>TRAINING KIT</th>
                                <th>NAMA HOTEL</th>
                                <th>BIAYA PENGINAPAN /ORANG</th>
                                <th>BIAYA PENGINAPAN</th>
                                <th>MEETING PACKAGE/ORANG</th>
                                <th>MEETING PACKAGE</th>
                                <th>MAKAN/ORANG</th>
                                <th>MAKAN</th>
                                <th>SNACK/ORANG</th>
                                <th>SNACK</th>
                                <th>TIKET/ORANG</th>
                                <th>TIKET</th>
                                <th>GRAND TOTAL</th>
                                <th>KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trainings as $i => $training): ?>
                                <?php $hash = md5($training['id']); ?>
                                <tr data-id="<?= $training['id'] ?>" data-hash="<?= $hash ?>">
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td><?= $i + 1 ?></td>
                                    <?php if (!$advanced) : ?>
                                        <td>
                                            <select class="form-control form-control-sm status-select"
                                                data-name="status" data-trn_id_hash="<?= $hash ?>">
                                                <option value="P" <?= $training['status'] == 'P' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Y" <?= $training['status'] == 'Y' ? 'selected' : '' ?>>Done</option>
                                                <option value="N" <?= $training['status'] == 'N' ? 'selected' : '' ?>>Cancelled</option>
                                                <option value="R" <?= $training['status'] == 'R' ? 'selected' : '' ?>>Reschedule</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select data-name="month">
                                                <option value=""></option>
                                                <?php foreach ($months as $i_m => $m_i) : ?>
                                                    <option value="<?= $i_m ?>" <?= $training['month'] == $i_m ? 'selected' : '' ?>><?= $m_i ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select data-name="departemen_pengampu">
                                                <option value=""></option>
                                                <?php foreach ($matrix_points as $i_mp => $mp_i) : ?>
                                                    <option value="<?= $mp_i['id'] ?>" <?= $training['departemen_pengampu'] == $mp_i['id'] ? 'selected' : '' ?>><?= $mp_i['name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td contenteditable="true" data-name="nama_program"><?= $training['nama_program'] ?></td>
                                        <td><input type="number" class="form-control form-control-sm" name="batch" value="<?= $training['batch'] ?>" data-name="batch"></td>
                                        <td contenteditable="true" data-name="jenis_kompetensi"><?= $training['jenis_kompetensi'] ?></td>
                                        <td contenteditable="true" data-name="sasaran_kompetensi"><?= $training['sasaran_kompetensi'] ?></td>
                                        <td><input type="number" class="form-control form-control-sm" name="level_kompetensi" value="<?= $training['level_kompetensi'] ?>" data-name="level_kompetensi"></td>
                                        <td contenteditable="true" data-name="target_peserta"><?= $training['target_peserta'] ?></td>
                                        <td contenteditable="true" data-name="staff_nonstaff"><?= $training['staff_nonstaff'] ?></td>
                                        <td contenteditable="true" data-name="kategori_program"><?= $training['kategori_program'] ?></td>
                                        <td contenteditable="true" data-name="fasilitator"><?= $training['fasilitator'] ?></td>
                                        <td contenteditable="true" data-name="nama_penyelenggara_fasilitator"><?= $training['nama_penyelenggara_fasilitator'] ?></td>
                                        <td contenteditable="true" data-name="tempat"><?= $training['tempat'] ?></td>
                                        <td contenteditable="true" data-name="online_offline"><?= $training['online_offline'] ?></td>
                                        <td><input type="date" class="form-control form-control-sm" name="start_date" value="<?= $training['start_date'] ?>" data-name="start_date"></td>
                                        <td><input type="date" class="form-control form-control-sm" name="end_date" value="<?= $training['end_date'] ?>" data-name="end_date"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="days" value="<?= $training['days'] ?>" data-name="days"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="hours" value="<?= $training['hours'] ?>" data-name="hours"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="total_hours" value="<?= $training['total_hours'] ?>" data-name="total_hours"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="rmho" value="<?= $training['rmho'] ?>" data-name="rmho"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="rmip" value="<?= $training['rmip'] ?>" data-name="rmip"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="rebh" value="<?= $training['rebh'] ?>" data-name="rebh"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="rmtu" value="<?= $training['rmtu'] ?>" data-name="rmtu"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="rmts" value="<?= $training['rmts'] ?>" data-name="rmts"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="rmgm" value="<?= $training['rmgm'] ?>" data-name="rmgm"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="rhml" value="<?= $training['rhml'] ?>" data-name="rhml"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="total_jobsite" value="<?= $training['total_jobsite'] ?>" data-name="total_jobsite"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="total_participants" value="<?= $training['total_participants'] ?>" data-name="total_participants"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="grand_total_hours" value="<?= $training['grand_total_hours'] ?>" data-name="grand_total_hours"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="biaya_pelatihan_per_orang" value="<?= $training['biaya_pelatihan_per_orang'] ?>" data-name="biaya_pelatihan_per_orang"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="biaya_pelatihan" value="<?= $training['biaya_pelatihan'] ?>" data-name="biaya_pelatihan"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="training_kit_per_orang" value="<?= $training['training_kit_per_orang'] ?>" data-name="training_kit_per_orang"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="training_kit" value="<?= $training['training_kit'] ?>" data-name="training_kit"></td>
                                        <td contenteditable="true" data-name="nama_hotel"><?= $training['nama_hotel'] ?></td>
                                        <td><input type="number" class="form-control form-control-sm" name="biaya_penginapan_per_orang" value="<?= $training['biaya_penginapan_per_orang'] ?>" data-name="biaya_penginapan_per_orang"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="biaya_penginapan" value="<?= $training['biaya_penginapan'] ?>" data-name="biaya_penginapan"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="meeting_package_per_orang" value="<?= $training['meeting_package_per_orang'] ?>" data-name="meeting_package_per_orang"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="meeting_package" value="<?= $training['meeting_package'] ?>" data-name="meeting_package"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="makan_per_orang" value="<?= $training['makan_per_orang'] ?>" data-name="makan_per_orang"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="makan" value="<?= $training['makan'] ?>" data-name="makan"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="snack_per_orang" value="<?= $training['snack_per_orang'] ?>" data-name="snack_per_orang"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="snack" value="<?= $training['snack'] ?>" data-name="snack"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="tiket_per_orang" value="<?= $training['tiket_per_orang'] ?>" data-name="tiket_per_orang"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="tiket" value="<?= $training['tiket'] ?>" data-name="tiket"></td>
                                        <td><input type="number" class="form-control form-control-sm" name="grand_total" value="<?= $training['grand_total'] ?>" data-name="grand_total"></td>
                                        <td contenteditable="true" data-name="keterangan"><?= $training['keterangan'] ?></td>
                                    <?php else : ?>
                                        <td contenteditable="true" data-name="status"><?= $training['status'] ?></td>
                                        <td contenteditable="true" data-name="month"><?= $training['month'] ?></td>
                                        <td contenteditable="true" data-name="departemen_pengampu"><?= $training['departemen_pengampu'] ?></td>
                                        <td contenteditable="true" data-name="nama_program"><?= $training['nama_program'] ?></td>
                                        <td contenteditable="true" data-name="batch"><?= $training['batch'] ?></td>
                                        <td contenteditable="true" data-name="jenis_kompetensi"><?= $training['jenis_kompetensi'] ?></td>
                                        <td contenteditable="true" data-name="sasaran_kompetensi"><?= $training['sasaran_kompetensi'] ?></td>
                                        <td contenteditable="true" data-name="level_kompetensi"><?= $training['level_kompetensi'] ?></td>
                                        <td contenteditable="true" data-name="target_peserta"><?= $training['target_peserta'] ?></td>
                                        <td contenteditable="true" data-name="staff_nonstaff"><?= $training['staff_nonstaff'] ?></td>
                                        <td contenteditable="true" data-name="kategori_program"><?= $training['kategori_program'] ?></td>
                                        <td contenteditable="true" data-name="fasilitator"><?= $training['fasilitator'] ?></td>
                                        <td contenteditable="true" data-name="nama_penyelenggara_fasilitator"><?= $training['nama_penyelenggara_fasilitator'] ?></td>
                                        <td contenteditable="true" data-name="tempat"><?= $training['tempat'] ?></td>
                                        <td contenteditable="true" data-name="online_offline"><?= $training['online_offline'] ?></td>
                                        <td contenteditable="true" data-name="start_date"><?= $training['start_date'] ?></td>
                                        <td contenteditable="true" data-name="end_date"><?= $training['end_date'] ?></td>
                                        <td contenteditable="true" data-name="days"><?= $training['days'] ?></td>
                                        <td contenteditable="true" data-name="hours"><?= $training['hours'] ?></td>
                                        <td contenteditable="true" data-name="total_hours"><?= $training['total_hours'] ?></td>
                                        <td contenteditable="true" data-name="rmho"><?= $training['rmho'] ?></td>
                                        <td contenteditable="true" data-name="rmip"><?= $training['rmip'] ?></td>
                                        <td contenteditable="true" data-name="rebh"><?= $training['rebh'] ?></td>
                                        <td contenteditable="true" data-name="rmtu"><?= $training['rmtu'] ?></td>
                                        <td contenteditable="true" data-name="rmts"><?= $training['rmts'] ?></td>
                                        <td contenteditable="true" data-name="rmgm"><?= $training['rmgm'] ?></td>
                                        <td contenteditable="true" data-name="rhml"><?= $training['rhml'] ?></td>
                                        <td contenteditable="true" data-name="total_jobsite"><?= $training['total_jobsite'] ?></td>
                                        <td contenteditable="true" data-name="total_participants"><?= $training['total_participants'] ?></td>
                                        <td contenteditable="true" data-name="grand_total_hours"><?= $training['grand_total_hours'] ?></td>
                                        <td contenteditable="true" data-name="biaya_pelatihan_per_orang"><?= $training['biaya_pelatihan_per_orang'] ?></td>
                                        <td contenteditable="true" data-name="biaya_pelatihan"><?= $training['biaya_pelatihan'] ?></td>
                                        <td contenteditable="true" data-name="training_kit_per_orang"><?= $training['training_kit_per_orang'] ?></td>
                                        <td contenteditable="true" data-name="training_kit"><?= $training['training_kit'] ?></td>
                                        <td contenteditable="true" data-name="nama_hotel"><?= $training['nama_hotel'] ?></td>
                                        <td contenteditable="true" data-name="biaya_penginapan_per_orang"><?= $training['biaya_penginapan_per_orang'] ?></td>
                                        <td contenteditable="true" data-name="biaya_penginapan"><?= $training['biaya_penginapan'] ?></td>
                                        <td contenteditable="true" data-name="meeting_package_per_orang"><?= $training['meeting_package_per_orang'] ?></td>
                                        <td contenteditable="true" data-name="meeting_package"><?= $training['meeting_package'] ?></td>
                                        <td contenteditable="true" data-name="makan_per_orang"><?= $training['makan_per_orang'] ?></td>
                                        <td contenteditable="true" data-name="makan"><?= $training['makan'] ?></td>
                                        <td contenteditable="true" data-name="snack_per_orang"><?= $training['snack_per_orang'] ?></td>
                                        <td contenteditable="true" data-name="snack"><?= $training['snack'] ?></td>
                                        <td contenteditable="true" data-name="tiket_per_orang"><?= $training['tiket_per_orang'] ?></td>
                                        <td contenteditable="true" data-name="tiket"><?= $training['tiket'] ?></td>
                                        <td contenteditable="true" data-name="grand_total"><?= $training['grand_total'] ?></td>
                                        <td contenteditable="true" data-name="keterangan"><?= $training['keterangan'] ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-3">
                            <button type="button" class="w-100 btn btn-default" onclick="cancelForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                        <div class="col-lg-3">
                            <button type="button" class="w-100 btn btn-danger" onclick="deleteSelectedRows()">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <div class="col-lg-3">
                            <button type="button" class="w-100 btn btn-success" onclick="createRow()">
                                <i class="fas fa-plus"></i> New
                            </button>
                        </div>
                        <div class="col-lg-3">
                            <button type="submit" class="w-100 btn btn-info">
                                <i class="fas fa-paper-plane"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-inputGuide">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Petunjuk Pengisian</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h1>Angka</h1>
                <p>isi cell angka dengan <strong>angka saja</strong>, <strong>jangan pakai titik(".")</strong>, jangan pakai huruf atau karakter lainnya</p>
                <hr>
                <h1>Tanggal</h1>
                <p>isi cell tanggal dengan dengan format <strong>"YYYY-MM-DD"</strong>, contoh: <strong>2025-01-20 (20 Januari 2025)</strong>. jangan pakai format karakter lainnya</p>
                <hr>
                <h1>Status</h1>
                <p>isi status dengan <strong>value</strong> yang bersangkutan di table ini</p>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-secondary">
                            <td>Pending</td>
                            <td>P</td>
                        </tr>
                        <tr class="bg-primary">
                            <td>Done</td>
                            <td>Y</td>
                        </tr>
                        <tr class="bg-danger">
                            <td>Cancelled</td>
                            <td>N</td>
                        </tr>
                        <tr class="bg-warning">
                            <td>Reschedule</td>
                            <td>R</td>
                        </tr>
                    </tbody>
                </table>
                <hr>
                <h1>Bulan</H1>
                <p>isi bulan dengan <strong>value</strong> yang bersangkutan di table ini</p>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Departemen Pengampu</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($months as $i_m => $m_i) : ?>
                            <tr>
                                <td><?= $m_i ?></td>
                                <td><?= $i_m ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <hr>
                <h1>Departemen Pengampu</H1>
                <p>isi departemen pengampu dengan <strong>value</strong> yang bersangkutan di table ini</p>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Departemen Pengampu</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matrix_points as $i_mp => $mp_i) : ?>
                            <tr>
                                <td><?= $mp_i['name'] ?></td>
                                <td><?= $mp_i['id'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <hr>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<style>
    .status-select option {
        background-color: #fff !important;
        color: #000 !important;
    }
</style>

<!-- Scripts -->
<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    let deletedRows = [];

    $(document).ready(function() {
        setupFilterableDatatable($('.datatable-filter-column'));

        // Terapkan warna awal setelah halaman selesai load
        $('.status-select').each(function() {
            applyStatusColor($(this)); // Warna td & select langsung diterapkan
        });

        // Panggil applyStatusColor saat DataTable selesai digambar ulang
        $('#datatable').on('draw.dt', function() {
            $('.status-select').each(function() {
                applyStatusColor($(this));
            });
        });

        // Select all
        $('#select-all').on('click', function() {
            $('.row-checkbox').prop('checked', this.checked);
        });

        // Submit handler
        $('#data-form').on('submit', function() {
            let allRows = collectTableData();
            let payload = {
                updates: allRows.filter(r => !String(r.id).startsWith('new_') && !deletedRows.includes(r.id)),
                deletes: deletedRows,
                creates: allRows.filter(r => String(r.id).startsWith('new_') && !deletedRows.includes(r.id))
            };
            $('#json_data').val(JSON.stringify(payload));
        });
    });

    // Fungsi reusable untuk set warna td & select
    function applyStatusColor($select) {
        let val = $select.val();
        let td = $select.closest('td');

        // Hapus semua warna dulu
        // td.removeClass('bg-success bg-secondary bg-danger bg-warning text-white text-dark');
        $select.removeClass('bg-success bg-secondary bg-danger bg-warning text-white text-dark');

        // Tambahkan warna sesuai status
        if (val === 'Y') {
            // td.addClass('bg-success text-white');
            $select.addClass('bg-success text-white');
        } else if (val === 'P') {
            // td.addClass('bg-secondary text-white');
            $select.addClass('bg-secondary text-white');
        } else if (val === 'N') {
            // td.addClass('bg-danger text-white');
            $select.addClass('bg-danger text-white');
        } else if (val === 'R') {
            // td.addClass('bg-warning text-dark');
            $select.addClass('bg-warning text-dark');
        }
    }

    $(document).on('change', '.status-select', function() {
        applyStatusColor($(this));
    });

    function cancelForm() {
        if (confirm('Yakin batal?')) {
            location.href = '<?= base_url('training/MTS/' . ($year ? '?year=' . $year : '')) ?>';
        }
    }

    function markRowDeleted(btn) {
        let row = $(btn).closest('tr');
        let id = row.data('id');
        let checkbox = row.find('.row-checkbox');

        if (deletedRows.includes(id)) {
            // ✅ Restore if already marked deleted
            deletedRows = deletedRows.filter(x => x !== id);
            row.removeClass('table-danger').css('opacity', '1');
            checkbox.prop('checked', false); // uncheck when restored
        } else {
            // ✅ Mark as deleted
            deletedRows.push(id);
            row.addClass('table-danger').css('opacity', '0.7');
            checkbox.prop('checked', true); // auto-check when deleted
        }
    }

    function deleteSelectedRows() {
        let table = $('#datatable').DataTable();

        // Loop melalui semua baris di DataTable, termasuk yang ada di halaman lain
        table.rows().every(function() {
            let row = $(this.node());
            let id = row.data('id');
            let isChecked = row.find('.row-checkbox').prop('checked');

            if (isChecked) {
                // Tandai baris yang dipilih untuk dihapus
                if (!deletedRows.includes(id)) {
                    deletedRows.push(id);
                }
                row.addClass('table-danger').css('opacity', '0.7');
            } else {
                // Jika baris tidak dipilih, pastikan untuk menghapus status 'deleted'
                if (deletedRows.includes(id)) {
                    deletedRows = deletedRows.filter(x => x !== id);
                    row.removeClass('table-danger').css('opacity', '1');
                }
            }
        });

        // Pastikan DataTable diupdate setelah penghapusan
        table.draw(false); // Redraw the table to ensure changes are applied across pages
    }

    // Create a new row dynamically
    function createRow() {
        const table = $('#datatable').DataTable();
        const newId = 'new_' + Date.now();

        // Construct the new row
        let row = `<tr data-id="${newId}" class="table-success">
            <td><input type="checkbox" class="row-checkbox"></td>
            <td>New</td>
            <?php if (!$advanced) : ?>
                <td>
                    <select class="form-control form-control-sm status-select"
                        data-name="status"">
                        <option value="P">Pending</option>
                        <option value="Y">Done</option>
                        <option value="N">Cancelled</option>
                        <option value="R">Reschedule</option>
                    </select>
                </td>
                <td>
                    <select data-name="month">
                        <option value=""></option>
                        <?php foreach ($months as $i_m => $m_i) : ?>
                            <option value="<?= $i_m ?>"><?= $m_i ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select data-name="departemen_pengampu">
                        <option value=""></option>
                        <?php foreach ($matrix_points as $i_mp => $mp_i) : ?>
                            <option value="<?= $mp_i['id'] ?>"><?= $mp_i['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td contenteditable="true" data-name="nama_program"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="batch"></td>
                <td contenteditable="true" data-name="jenis_kompetensi"></td>
                <td contenteditable="true" data-name="sasaran_kompetensi"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="level_kompetensi"></td>
                <td contenteditable="true" data-name="target_peserta"></td>
                <td contenteditable="true" data-name="staff_nonstaff"></td>
                <td contenteditable="true" data-name="kategori_program"></td>
                <td contenteditable="true" data-name="fasilitator"></td>
                <td contenteditable="true" data-name="nama_penyelenggara_fasilitator"></td>
                <td contenteditable="true" data-name="tempat"></td>
                <td contenteditable="true" data-name="online_offline"></td>
                <td><input type="date" class="form-control form-control-sm" data-name="start_date"></td>
                <td><input type="date" class="form-control form-control-sm" data-name="end_date"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="days"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="hours"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="total_hours"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="rmho"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="rmip"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="rebh"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="rmtu"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="rmts"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="rmgm"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="rhml"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="total_jobsite"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="total_participants"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="grand_total_hours"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="biaya_pelatihan_per_orang"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="biaya_pelatihan"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="training_kit_per_orang"></td>
                <td><input type="number" class="form-control form-control-sm" data-name="training_kit"></td>
                <td contenteditable="true" data-name="nama_hotel"></td>
                <td><input type="number" class="form-control form-control-sm" name="biaya_penginapan_per_orang"  data-name="biaya_penginapan_per_orang"></td>
                <td><input type="number" class="form-control form-control-sm" name="biaya_penginapan"  data-name="biaya_penginapan"></td>
                <td><input type="number" class="form-control form-control-sm" name="meeting_package_per_orang"  data-name="meeting_package_per_orang"></td>
                <td><input type="number" class="form-control form-control-sm" name="meeting_package"  data-name="meeting_package"></td>
                <td><input type="number" class="form-control form-control-sm" name="makan_per_orang"  data-name="makan_per_orang"></td>
                <td><input type="number" class="form-control form-control-sm" name="makan"  data-name="makan"></td>
                <td><input type="number" class="form-control form-control-sm" name="snack_per_orang"  data-name="snack_per_orang"></td>
                <td><input type="number" class="form-control form-control-sm" name="snack"  data-name="snack"></td>
                <td><input type="number" class="form-control form-control-sm" name="tiket_per_orang"  data-name="tiket_per_orang"></td>
                <td><input type="number" class="form-control form-control-sm" name="tiket"  data-name="tiket"></td>
                <td><input type="number" class="form-control form-control-sm" name="grand_total"  data-name="grand_total"></td>
                <td contenteditable="true" data-name="keterangan"></td>
            <?php else : ?>
                <td contenteditable="true" data-name="status"></td>
                <td contenteditable="true" data-name="month"></td>
                <td contenteditable="true" data-name="departemen_pengampu"></td>
                <td contenteditable="true" data-name="nama_program"></td>
                <td contenteditable="true" data-name="batch"></td>
                <td contenteditable="true" data-name="jenis_kompetensi"></td>
                <td contenteditable="true" data-name="sasaran_kompetensi"></td>
                <td contenteditable="true" data-name="level_kompetensi"></td>
                <td contenteditable="true" data-name="target_peserta"></td>
                <td contenteditable="true" data-name="staff_nonstaff"></td>
                <td contenteditable="true" data-name="kategori_program"></td>
                <td contenteditable="true" data-name="fasilitator"></td>
                <td contenteditable="true" data-name="nama_penyelenggara_fasilitator"></td>
                <td contenteditable="true" data-name="tempat"></td>
                <td contenteditable="true" data-name="online_offline"></td>
                <td contenteditable="true" data-name="start_date"></td>
                <td contenteditable="true" data-name="end_date"></td>
                <td contenteditable="true" data-name="days"></td>
                <td contenteditable="true" data-name="hours"></td>
                <td contenteditable="true" data-name="total_hours"></td>
                <td contenteditable="true" data-name="rmho"></td>
                <td contenteditable="true" data-name="rmip"></td>
                <td contenteditable="true" data-name="rebh"></td>
                <td contenteditable="true" data-name="rmtu"></td>
                <td contenteditable="true" data-name="rmts"></td>
                <td contenteditable="true" data-name="rmgm"></td>
                <td contenteditable="true" data-name="rhml"></td>
                <td contenteditable="true" data-name="total_jobsite"></td>
                <td contenteditable="true" data-name="total_participants"></td>
                <td contenteditable="true" data-name="grand_total_hours"></td>
                <td contenteditable="true" data-name="biaya_pelatihan_per_orang"></td>
                <td contenteditable="true" data-name="biaya_pelatihan"></td>
                <td contenteditable="true" data-name="training_kit_per_orang"></td>
                <td contenteditable="true" data-name="training_kit"></td>
                <td contenteditable="true" data-name="nama_hotel"></td>
                <td contenteditable="true" data-name="biaya_penginapan_per_orang"></td>
                <td contenteditable="true" data-name="biaya_penginapan"></td>
                <td contenteditable="true" data-name="meeting_package_per_orang"></td>
                <td contenteditable="true" data-name="meeting_package"></td>
                <td contenteditable="true" data-name="makan_per_orang"></td>
                <td contenteditable="true" data-name="makan"></td>
                <td contenteditable="true" data-name="snack_per_orang"></td>
                <td contenteditable="true" data-name="snack"></td>
                <td contenteditable="true" data-name="tiket_per_orang"></td>
                <td contenteditable="true" data-name="tiket"></td>
                <td contenteditable="true" data-name="grand_total"></td>
                <td contenteditable="true" data-name="keterangan"></td>
            <?php endif; ?>
            
        </tr>`;

        // Add the row to the table using DataTables API
        const node = table.row.add($(row)).draw(false).node();

        // Set the custom data-id attribute and class after row is added
        $(node).attr('data-id', newId).addClass('table-success');

        // Ensure columns are adjusted after the new row is added
        table.columns.adjust().draw(false);

        // First, go to the last page
        table.page('last').draw('page');
    }

    // Collect table data (existing + new)
    function collectTableData() {
        let data = [];
        let table = $('#datatable').DataTable();

        // Existing DataTable rows
        table.rows().every(function() {
            data.push(collectRow($(this.node())));
        });

        // Newly appended rows
        $('#datatable tbody tr').each(function() {
            let id = $(this).data('id');
            if (!data.find(r => r.id === id)) data.push(collectRow($(this)));
        });

        return data;
    }


    // Extract data from a row
    function collectRow(row) {
        let id = row.data('id');
        let rowData = {
            id: id
        };

        // Loop through all td elements that are contenteditable, input, or select
        row.find('td[contenteditable], input, select').each(function() {
            let name = $(this).data('name');
            if (name) {
                if ($(this).is('input')) {
                    // Handle input elements
                    rowData[name] = $(this).val();
                } else if ($(this).is('select')) {
                    // Handle select elements
                    rowData[name] = $(this).val();
                } else {
                    // Handle contenteditable cells
                    rowData[name] = $(this).text();
                }
            }
        });

        return rowData;
    }
</script>