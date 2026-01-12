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

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit ATMP <strong><?= $year ?></strong></h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Trainings</h3>
            </div>

            <form id="data-form" action="<?= base_url() ?>training/ATMP/submit" method="post">
                <div class="card-body">
                    <?php if (!$advanced) : ?>
                        <a href="<?= base_url() ?>training/ATMP/edit/<?= $year ?>?advanced=true" class="btn btn-warning w-100">Edit Advanced</a><br><br>
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
                                        <td contenteditable="true" data-name="level_kompetensi"><?= $training['level_kompetensi'] ?></td>
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
                            <button type="button" class="w-100 btn btn-danger" id="btn-delete-selected">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <div class="col-lg-3 d-flex">
                            <input type="number" class="form-control w-50" id="row_number_add" name="row_number_add" value="1">
                            <button type="button" class="w-50 btn btn-success" id="btn-new-row">
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
                <h1>Bulan</h1>
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
                <h1>Departemen Pengampu</h1>
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

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    window.IS_ADVANCED = <?= $advanced ? 'true' : 'false' ?>;

    // Cancel tetap seperti sebelumnya
    function cancelForm() {
        if (confirm('Yakin batal?')) {
            location.href = '<?= base_url('training/ATMP/' . ($year ? '?year=' . $year : '')) ?>';
        }
    }

    // Options untuk select (month & departemen_pengampu) diproduksi dari PHP (mode non-advanced)
    window.OPT_MONTHS = [{
            value: "",
            label: ""
        },
        <?php foreach ($months as $k => $v) : ?> {
                value: "<?= $k ?>",
                label: "<?= addslashes($v) ?>"
            },
        <?php endforeach; ?>
    ];

    window.OPT_DEPARTEMEN_PENGAMPU = [{
            value: "",
            label: ""
        },
        <?php foreach ($matrix_points as $mp) : ?> {
                value: "<?= $mp['id'] ?>",
                label: "<?= addslashes($mp['name']) ?>"
            },
        <?php endforeach; ?>
    ];

    const E = (arr) => arr.map(name => ({
        name,
        type: 'editable'
    }));
    const N = (arr, step = 1) => arr.map(name => ({
        name,
        type: 'number',
        step
    }));
    const D = (arr) => arr.map(name => ({
        name,
        type: 'date'
    }));

    const NON_ADV_COLUMNS = [
        // 1️⃣ MONTH
        {
            name: 'month',
            type: 'select',
            options: () => window.OPT_MONTHS
        },

        // 2️⃣ DEPARTEMEN
        {
            name: 'departemen_pengampu',
            type: 'select',
            options: () => window.OPT_DEPARTEMEN_PENGAMPU
        },

        // 3️⃣ NAMA PROGRAM
        {
            name: 'nama_program',
            type: 'editable'
        },

        // 4️⃣ BATCH  ✅ PINDAHKAN KE SINI
        {
            name: 'batch',
            type: 'number',
            step: 1
        },

        // 5️⃣ TEKS LANJUTAN
        ...E([
            'jenis_kompetensi',
            'sasaran_kompetensi',
            'level_kompetensi',
            'target_peserta',
            'staff_nonstaff',
            'kategori_program',
            'fasilitator',
            'nama_penyelenggara_fasilitator',
            'tempat',
            'online_offline'
        ]),

        // 6️⃣ DATE
        ...D(['start_date', 'end_date']),

        // 7️⃣ NUMBER
        ...N([
            'days',
            'rmho', 'rmip', 'rebh', 'rmtu', 'rmts', 'rmgm', 'rhml',
            'total_jobsite', 'total_participants',
            'biaya_pelatihan_per_orang', 'biaya_pelatihan',
            'training_kit_per_orang', 'training_kit',
            'biaya_penginapan_per_orang', 'biaya_penginapan',
            'meeting_package_per_orang', 'meeting_package',
            'makan_per_orang', 'makan',
            'snack_per_orang', 'snack',
            'tiket_per_orang', 'tiket',
            'grand_total'
        ]),

        // 8️⃣ DECIMAL
        ...N(['hours', 'total_hours', 'grand_total_hours'], 0.01),

        // 9️⃣ TERAKHIR
        {
            name: 'nama_hotel',
            type: 'editable'
        },
        {
            name: 'keterangan',
            type: 'editable'
        }
    ];

    const ADV_COLUMNS = [
        'month', 'departemen_pengampu', 'nama_program', 'batch',
        'jenis_kompetensi', 'sasaran_kompetensi', 'level_kompetensi',
        'target_peserta', 'staff_nonstaff', 'kategori_program',
        'fasilitator', 'nama_penyelenggara_fasilitator', 'tempat',
        'online_offline', 'start_date', 'end_date', 'days', 'hours',
        'total_hours', 'rmho', 'rmip', 'rebh', 'rmtu', 'rmts', 'rmgm', 'rhml',
        'total_jobsite', 'total_participants', 'grand_total_hours',
        'biaya_pelatihan_per_orang', 'biaya_pelatihan',
        'training_kit_per_orang', 'training_kit', 'nama_hotel',
        'biaya_penginapan_per_orang', 'biaya_penginapan',
        'meeting_package_per_orang', 'meeting_package',
        'makan_per_orang', 'makan', 'snack_per_orang', 'snack',
        'tiket_per_orang', 'tiket', 'grand_total', 'keterangan'
    ].map(name => ({
        name,
        type: 'editable'
    }));

    const columns = window.IS_ADVANCED ? ADV_COLUMNS : NON_ADV_COLUMNS;

    // ====== CONFIG CRUD (columns-driven) ======
    window.DT_CRUD_CONFIG = {
        tableSelector: '#datatable',
        formSelector: '#data-form',
        jsonFieldSelector: '#json_data',

        btnNewSelector: '#btn-new-row',
        btnDeleteSelectedSelector: '#btn-delete-selected',
        rowAddCountSelector: '#row_number_add',
        selectAllSelector: '#select-all',
        rowCheckboxSelector: '.row-checkbox',

        rowPrefixHtml: () => `
            <td><input type="checkbox" class="row-checkbox"></td>
            <td>New</td>
        `,

        deletedRowClass: 'table-danger',
        deletedOpacity: 0.7,
        newRowClass: 'table-success',

        // ✅ INI WAJIB
        columns: columns,

        onAfterRowAdded: ($row) => {
            $row.find('.select2').each(function() {
                if (window.FuzzySelect2 && typeof window.FuzzySelect2.apply === 'function') {
                    window.FuzzySelect2.apply($(this));
                }
            });
        }
    };

    $(function() {
        // init DataTables + filter column (fungsi existing)
        setupFilterableDatatable($('.datatable-filter-column'));

        // init CRUD engine baru
        setupDatatableCrud($('#datatable'), window.DT_CRUD_CONFIG);
    });
</script>