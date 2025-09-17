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

$matrix_points = array_column($matrix_points, null, 'id');
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">MTS(Monthly Training Schedule)</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Year Change</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="<?= base_url() ?>training/MTS" method="get">
                <div class="card-body">
                    <div class="form-group">
                        <label>Year:</label>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="input-group date" id="year" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-target="#year" value="<?= $year ?>" name="year" />
                                    <div class="input-group-append" data-target="#year" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                <?php echo form_error('year', '<div class="text-danger small">', '</div>'); ?>
                            </div>
                            <div class="col-lg-4">
                                <button type="submit" class="btn btn-primary w-100">Change</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </form>
        </div>
        <!-- /.card -->

        <?php if ($trainings) : ?>
            <div class="row">
                <div class="col-lg-6">
                    <!-- DONUT CHART -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Chart MTS & ATMP</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="donutChart_mts_atmp" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

                </div>
                <div class="col-lg-6">
                    <!-- DONUT CHART -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Chart Status MTS</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="donutChart_mts_status" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>

            <?php if ($trainings) : ?>
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Training List</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <a href="<?= base_url() ?>training/MTS/edit/<?= $year ?>" class="btn btn-primary w-100">Edit</a><br><br>
                        <table id="datatable_training" class="table table-bordered table-striped datatable-filter-column">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>STATUS</th>
                                    <th>ASSIGN ATMP</th>
                                    <th>ATMP</th>
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
                                <?php $i = 1; ?>
                                <?php $status_str = ['P' => 'Pending', 'Y' => 'Done', 'N' => 'Canceled', 'R' => 'Reschedule']; ?>
                                <?php $status_bg = ['P' => 'none', 'Y' => 'primary', 'N' => 'danger', 'R' => 'warning']; ?>
                                <?php foreach ($trainings as $training) : ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td class="bg-<?= $status_bg[$training['status']] ?>"><?= $status_str[$training['status']] ?></td>
                                        <td><a href="<?= base_url() ?>training/MTS/ATMP/<?= md5($training['id']) ?>?year=<?= $year ?>" class="btn btn-primary btn-sm" target="_blank"><?= 'assign' ?></a></td>
                                        <td><?= $training['atmp_nama_program'] ?></td>
                                        <td><?= $training['month'] ? $months[$training['month']] : '' ?></td>
                                        <td><?= $training['departemen_pengampu'] ? $matrix_points[$training['departemen_pengampu']]['name'] : '' ?></td>
                                        <td><?= $training['nama_program'] ?></td>
                                        <td><?= $training['batch'] ?></td>
                                        <td><?= $training['jenis_kompetensi'] ?></td>
                                        <td><?= $training['sasaran_kompetensi'] ?></td>
                                        <td><?= $training['level_kompetensi'] ?></td>
                                        <td><?= $training['target_peserta'] ?></td>
                                        <td><?= $training['staff_nonstaff'] ?></td>
                                        <td><?= $training['kategori_program'] ?></td>
                                        <td><?= $training['fasilitator'] ?></td>
                                        <td><?= $training['nama_penyelenggara_fasilitator'] ?></td>
                                        <td><?= $training['tempat'] ?></td>
                                        <td><?= $training['online_offline'] ?></td>
                                        <td><?= $training['actual_start_date'] ?></td>
                                        <td><?= $training['actual_end_date'] ?></td>
                                        <td><?= $training['days'] ?></td>
                                        <td><?= $training['hours'] ?></td>
                                        <td><?= $training['total_hours'] ?></td>
                                        <td><?= $training['rmho'] ?></td>
                                        <td><?= $training['rmip'] ?></td>
                                        <td><?= $training['rebh'] ?></td>
                                        <td><?= $training['rmtu'] ?></td>
                                        <td><?= $training['rmts'] ?></td>
                                        <td><?= $training['rmgm'] ?></td>
                                        <td><?= $training['rhml'] ?></td>
                                        <td><?= $training['total_jobsite'] ?></td>
                                        <td>
                                            <a href="<?= base_url() ?>training/MTS/participants/<?= md5($training['id']) ?>?year=<?= $year ?>" class="btn btn-primary btn-sm" target="_blank"><?= $training['total_participant'] ?? 0 ?></a>
                                        </td>
                                        <td><?= $training['grand_total_hours'] ?></td>
                                        <td><?= $training['biaya_pelatihan_per_orang'] ?></td>
                                        <td><?= $training['biaya_pelatihan'] ?></td>
                                        <td><?= $training['training_kit_per_orang'] ?></td>
                                        <td><?= $training['training_kit'] ?></td>
                                        <td><?= $training['nama_hotel'] ?></td>
                                        <td><?= $training['biaya_penginapan_per_orang'] ?></td>
                                        <td><?= $training['biaya_penginapan'] ?></td>
                                        <td><?= $training['meeting_package_per_orang'] ?></td>
                                        <td><?= $training['meeting_package'] ?></td>
                                        <td><?= $training['makan_per_orang'] ?></td>
                                        <td><?= $training['makan'] ?></td>
                                        <td><?= $training['snack_per_orang'] ?></td>
                                        <td><?= $training['snack'] ?></td>
                                        <td><?= $training['tiket_per_orang'] ?></td>
                                        <td><?= $training['tiket'] ?></td>
                                        <td><?= $training['grand_total'] ?></td>
                                        <td><?= $training['keterangan'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            <?php endif; ?>
            <!-- /.card -->
        <?php endif; ?>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<!-- Page specific script -->
<script>
    $(document).ready(function() {
        $('#year').datetimepicker({
            format: 'YYYY', // Only year
            viewMode: 'years',
        });

        // Trigger submit saat tahun berubah dari picker
        $('#year').on('change.datetimepicker', function(e) {
            $(this).find('input').closest('form').submit();
        });

        setupFilterableDatatable($('.datatable-filter-column'));

        $(function() {
            const donutData_mts_atmp = {
                labels: ['ATMP :  <?= $chart_mts_atmp['atmp']['percentage'] ?>%', 'MTS & ATMP :  <?= $chart_mts_atmp['mts_atmp']['percentage'] ?>%', 'MTS :  <?= $chart_mts_atmp['mts']['percentage'] ?>%'],
                datasets: [{
                    data: [<?= $chart_mts_atmp['atmp']['value'] ?>, <?= $chart_mts_atmp['mts_atmp']['value'] ?>, <?= $chart_mts_atmp['mts']['value'] ?>],
                    backgroundColor: ['#f56954', '#8a50a1', '#007bff'],
                }]
            };

            const donutOptions = {
                maintainAspectRatio: false,
                responsive: true,
                cutout: '60%' // This makes it a donut (vs. full pie)
            };

            new Chart($('#donutChart_mts_atmp'), {
                type: 'doughnut',
                data: donutData_mts_atmp,
                options: donutOptions
            });
        });

        $(function() {
            const donutData_mts_status = {
                labels: [
                    'Done :  <?= $chart_mts_status['mts_y']['percentage'] ?>%',
                    'Reschedule :  <?= $chart_mts_status['mts_r']['percentage'] ?>%',
                    'Cancelled :  <?= $chart_mts_status['mts_n']['percentage'] ?>%',
                    'Pending :  <?= $chart_mts_status['mts_p']['percentage'] ?>%'
                ],
                datasets: [{
                    data: [
                        <?= $chart_mts_status['mts_y']['value'] ?>,
                        <?= $chart_mts_status['mts_r']['value'] ?>,
                        <?= $chart_mts_status['mts_n']['value'] ?>,
                        <?= $chart_mts_status['mts_p']['value'] ?>
                    ],
                    backgroundColor: [
                        '#007bff',
                        '#ffc107',
                        '#f56954',
                        '#6c757d',
                    ],
                }]
            };

            const donutOptions = {
                maintainAspectRatio: false,
                responsive: true,
                cutout: '60%' // This makes it a donut (vs. full pie)
            };

            new Chart($('#donutChart_mts_status'), {
                type: 'doughnut',
                data: donutData_mts_status,
                options: donutOptions
            });
        });
    });
</script>