<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">MONITORING</h1>
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
                <h3 class="card-title">Month Change</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="<?= base_url() ?>training/monitoring" method="get">
                <div class="card-body">
                    <div class="form-group">
                        <label>Month:</label>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="input-group date" id="year_month" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-target="#year_month" value="<?= $year_month ?>" name="year_month" />
                                    <div class="input-group-append" data-target="#year_month" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                <?php echo form_error('month', '<div class="text-danger small">', '</div>'); ?>
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

        <?php if ($trainings['mtd']) : ?>
            <div class="row">
                <div class="col-lg-4">
                    <!-- DONUT CHART -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Status <?= $year_month_str ?></h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas><br>
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="alert alert-primary p-0 m-0">
                                        &nbsp; Done:
                                    </div>
                                </div>
                                <div class="col-lg-3 text-right">
                                    <?= number_format($chart_status['mtd']['done']['value']) ?>
                                </div>
                                <div class="col-lg-3">
                                    <div class="alert alert-danger p-0 m-0">
                                        &nbsp; Cancel:
                                    </div>
                                </div>
                                <div class="col-lg-3 text-right">
                                    <?= number_format($chart_status['mtd']['cancel']['value']) ?>
                                </div>
                                <div class="col-lg-3">
                                    <div class="alert alert-warning p-0 m-0">
                                        &nbsp; Reschecule:
                                    </div>
                                </div>
                                <div class="col-lg-3 text-right">
                                    <?= number_format($chart_status['mtd']['reschedule']['value']) ?>
                                </div>
                                <div class="col-lg-3">
                                    <div class="alert alert-secondary p-0 m-0">
                                        &nbsp; Pending:
                                    </div>
                                </div>
                                <div class="col-lg-3 text-right">
                                    <?= number_format($chart_status['mtd']['pending']['value']) ?>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>

                <div class="col-lg-4">
                    <!-- BAR CHART -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Budget <?= $year_month_str ?></h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart">
                                <canvas id="barChart_budget" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas><br>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="alert alert-primary p-0 m-0">
                                        &nbsp; Plan:
                                    </div>
                                </div>
                                <div class="col-lg-6 text-right">
                                    Rp. <?= number_format($chart_budget['mtd']['grand_total']['value']) ?>
                                </div>
                                <div class="col-lg-2">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="alert alert-warning p-0 m-0">
                                        &nbsp; Actual:
                                    </div>
                                </div>
                                <div class="col-lg-6 text-right">
                                    Rp. <?= number_format($chart_budget['mtd']['actual_budget']['value']) ?>
                                </div>
                                <div class="col-lg-2 text-right">
                                    <?= number_format($chart_budget['mtd']['actual_budget']['percentage']) ?>%
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- BAR CHART -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Participants <?= $year_month_str ?></h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart">
                                <canvas id="barChart_participants" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas><br>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="alert alert-primary p-0 m-0">
                                        &nbsp; Plan:
                                    </div>
                                </div>
                                <div class="col-lg-6 text-right">
                                    <?= number_format($chart_participants['mtd']['total_participants']['value']) ?>
                                </div>
                                <div class="col-lg-2">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="alert alert-warning p-0 m-0">
                                        &nbsp; Actual:
                                    </div>
                                </div>
                                <div class="col-lg-6 text-right">
                                    <?= number_format($chart_participants['mtd']['actual_participants']['value']) ?>
                                </div>
                                <div class="col-lg-2 text-right">
                                    <?= number_format($chart_participants['mtd']['actual_participants']['percentage']) ?>%
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($trainings['ytd']) : ?>
            <div class="row">
                <div class="col-lg-4">
                    <!-- DONUT CHART -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Status <?= $year ?></h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="donutChart_ytd" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas><br>
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="alert alert-primary p-0 m-0">
                                        &nbsp; Done:
                                    </div>
                                </div>
                                <div class="col-lg-3 text-right">
                                    <?= number_format($chart_status['ytd']['done']['value']) ?>
                                </div>
                                <div class="col-lg-3">
                                    <div class="alert alert-danger p-0 m-0">
                                        &nbsp; Cancel:
                                    </div>
                                </div>
                                <div class="col-lg-3 text-right">
                                    <?= number_format($chart_status['ytd']['cancel']['value']) ?>
                                </div>
                                <div class="col-lg-3">
                                    <div class="alert alert-warning p-0 m-0">
                                        &nbsp; Reschecule:
                                    </div>
                                </div>
                                <div class="col-lg-3 text-right">
                                    <?= number_format($chart_status['ytd']['reschedule']['value']) ?>
                                </div>
                                <div class="col-lg-3">
                                    <div class="alert alert-secondary p-0 m-0">
                                        &nbsp; Pending:
                                    </div>
                                </div>
                                <div class="col-lg-3 text-right">
                                    <?= number_format($chart_status['ytd']['pending']['value']) ?>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>

                <div class="col-lg-4">
                    <!-- BAR CHART -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Budget <?= $year ?></h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart">
                                <canvas id="barChart_budget_ytd" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas><br>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="alert alert-primary p-0 m-0">
                                        &nbsp; Plan:
                                    </div>
                                </div>
                                <div class="col-lg-6 text-right">
                                    Rp. <?= number_format($chart_budget['ytd']['grand_total']['value']) ?>
                                </div>
                                <div class="col-lg-2">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="alert alert-warning p-0 m-0">
                                        &nbsp; Actual:
                                    </div>
                                </div>
                                <div class="col-lg-6 text-right">
                                    Rp. <?= number_format($chart_budget['ytd']['actual_budget']['value']) ?>
                                </div>
                                <div class="col-lg-2 text-right">
                                    <?= number_format($chart_budget['ytd']['actual_budget']['percentage']) ?>%
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- BAR CHART -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Participants <?= $year ?></h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart">
                                <canvas id="barChart_participants_ytd" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas><br>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="alert alert-primary p-0 m-0">
                                        &nbsp; Plan:
                                    </div>
                                </div>
                                <div class="col-lg-6 text-right">
                                    <?= number_format($chart_participants['ytd']['total_participants']['value']) ?>
                                </div>
                                <div class="col-lg-2">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="alert alert-warning p-0 m-0">
                                        &nbsp; Actual:
                                    </div>
                                </div>
                                <div class="col-lg-6 text-right">
                                    <?= number_format($chart_participants['ytd']['actual_participants']['value']) ?>
                                </div>
                                <div class="col-lg-2 text-right">
                                    <?= number_format($chart_participants['ytd']['actual_participants']['percentage']) ?>%
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Trainings</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <a href="<?= base_url() ?>training/monitoring/edit/<?= $month ?>" class="btn btn-primary w-100">Edit</a><br><br>
                    <table id="datatable_training" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>STATUS</th>
                                <th>MONTH</th>
                                <th>DEPARTEMEN PENGAMPU</th>
                                <th>NAMA PROGRAM</th>
                                <th>TEMPAT</th>
                                <th>START DATE</th>
                                <th>END DATE</th>
                                <th>NOTE</th>
                                <th>DAYS</th>
                                <th>HOURS</th>
                                <th>TOTAL HOURS</th>
                                <th>PLAN PARTICIPANT</th>
                                <th>ACTUAL PARTICIPANT</th>
                                <th>GRAND TOTAL HOURS</th>
                                <th>PLAN BUDGET</th>
                                <th>ACTUAL BUDGET</th>
                            </tr>
                            <tr>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php $status_str = ['P' => 'Pending', 'Y' => 'Done', 'N' => 'Canceled', 'R' => 'Reschedule']; ?>
                            <?php $status_bg = ['P' => 'none', 'Y' => 'primary', 'N' => 'danger', 'R' => 'warning']; ?>
                            <?php foreach ($trainings['ytd'] as $training) : ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td class="bg-<?= $status_bg[$training['status']] ?>"><?= $status_str[$training['status']] ?></td>
                                    <td><?= $training['month'] ?></td>
                                    <td><?= $training['departemen_pengampu'] ?></td>
                                    <td><?= $training['nama_program'] ?></td>
                                    <td><?= $training['tempat'] ?></td>
                                    <td><?= $training['start_date'] ?></td>
                                    <td><?= $training['end_date'] ?></td>
                                    <td><?= $training['keterangan'] ?></td>
                                    <td><?= $training['days'] ?></td>
                                    <td><?= $training['hours'] ?></td>
                                    <td><?= $training['days'] * $training['hours'] ?></td>
                                    <td><?= $training['rmho'] + $training['rhml'] + $training['rmip'] + $training['rebh'] + $training['rmtu'] + $training['rmts'] + $training['rmgm'] ?></td>
                                    <td><?= $training['actual_participants'] ?></td>
                                    <td><?= $training['days'] * $training['hours'] * $training['actual_participants'] ?></td>
                                    <td><?= $training['grand_total'] ?></td>
                                    <td><?= $training['actual_budget'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        <?php endif; ?>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<!-- Page specific script -->
<script>
    //Date picker
    $('#year_month').datetimepicker({
        format: 'YYYY-MM',
        viewMode: 'months'
    });

    // Trigger submit saat tahun berubah dari picker
    $('#year_month').on('change.datetimepicker', function(e) {
        $(this).find('input').closest('form').submit();
    });

    <?php if ($trainings['ytd']) : ?>
        $(function() {
            $("#datatable_training").DataTable({
                "autoWidth": false,
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                scrollX: true,
                orderCellsTop: true, // 👈 IMPORTANT for multiple thead rows
                fixedHeader: true, // Optional: keeps header visible on scroll
            }).buttons().container().appendTo('#datatable_training_wrapper .col-md-6:eq(0)');
        });

        $('#datatable_training thead tr:eq(1) th').each(function(i) {
            let input = $(this).find('input');
            if (input.length) {
                $(input).on('keyup change', function() {
                    if ($('#datatable_training').DataTable().column(i).search() !== this.value) {
                        $('#datatable_training').DataTable()
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            }
        });
    <?php endif; ?>

    <?php if ($trainings['mtd']) : ?>
        // chart MTD
        $(function() {
            const donutData = {
                labels: ['Done : <?= $chart_status['mtd']['done']['percentage'] ?>%', 'Pending : <?= $chart_status['mtd']['pending']['percentage'] ?>%', 'Cancel : <?= $chart_status['mtd']['cancel']['percentage'] ?>%', 'Reschedule : <?= $chart_status['mtd']['reschedule']['percentage'] ?>%'],
                datasets: [{
                    data: [<?= $chart_status['mtd']['done']['value'] ?>, <?= $chart_status['mtd']['pending']['value'] ?>, <?= $chart_status['mtd']['cancel']['value'] ?>, <?= $chart_status['mtd']['reschedule']['value'] ?>],
                    backgroundColor: ['#007bff', '#adb5bd', '#f56954', '#ffc107', ],
                }]
            };

            const donutOptions = {
                maintainAspectRatio: false,
                responsive: true,
                cutout: '60%' // This makes it a donut (vs. full pie)
            };

            new Chart($('#donutChart'), {
                type: 'doughnut',
                data: donutData,
                options: donutOptions
            });
        });

        var barChart_budget_Canvas = $('#barChart_budget').get(0).getContext('2d')

        var barChart_budget_Data = {
            labels: ['<?= $year_month_str ?>'],
            datasets: [{
                    label: 'Plan',
                    backgroundColor: '#007bff',
                    borderColor: '#007bff',
                    data: [<?= $chart_budget['mtd']['grand_total']['value'] ?>]
                },
                {
                    label: 'Actual',
                    backgroundColor: '#ffc107',
                    borderColor: '#ffc107',
                    data: [<?= $chart_budget['mtd']['actual_budget']['value'] ?>]
                }
            ]
        }

        var barChart_budget_Options = {
            responsive: true,
            maintainAspectRatio: false,
            datasetFill: false,
            tooltips: {
                callbacks: {
                    // This formats the value in the tooltip
                    label: function(tooltipItem, data) {
                        return tooltipItem.yLabel.toLocaleString(); // Adds thousands separator
                    }
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: false, // Ensure it doesn't start at 0
                        min: 0,
                        callback: function(value) {
                            // Check for billions
                            if (value >= 1000000000) {
                                return (value / 1000000000).toFixed(1) + 'B'; // Limit to 1 decimal for billions
                            }
                            // Check for millions
                            if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M'; // Limit to 1 decimal for millions
                            }
                            // Check for thousands
                            if (value >= 1000) {
                                return (value / 1000).toFixed(1) + 'K'; // Limit to 1 decimal for thousands
                            }
                            // If the value is less than 1000, just return the value
                            return value.toLocaleString();
                        }
                    }
                }]
            }
        }

        new Chart(barChart_budget_Canvas, {
            type: 'bar',
            data: barChart_budget_Data,
            options: barChart_budget_Options
        })

        var barChart_participants_Canvas = $('#barChart_participants').get(0).getContext('2d')

        var barChart_participants_Data = {
            labels: ['<?= $year_month_str ?>'],
            datasets: [{
                    label: 'Plan',
                    backgroundColor: '#007bff',
                    borderColor: '#007bff',
                    data: [<?= $chart_participants['mtd']['total_participants']['value'] ?>]
                },
                {
                    label: 'Actual',
                    backgroundColor: '#ffc107',
                    borderColor: '#ffc107',
                    data: [<?= $chart_participants['mtd']['actual_participants']['value'] ?>]
                }
            ]
        }

        var barChart_participants_Options = {
            responsive: true,
            maintainAspectRatio: false,
            datasetFill: false,
            tooltips: {
                callbacks: {
                    // This formats the value in the tooltip
                    label: function(tooltipItem, data) {
                        return tooltipItem.yLabel.toLocaleString(); // Adds thousands separator
                    }
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: false, // Ensure it doesn't start at 0
                        min: 0,
                        callback: function(value) {
                            // Check for billions
                            if (value >= 1000000000) {
                                return (value / 1000000000).toFixed(1) + 'B'; // Limit to 1 decimal for billions
                            }
                            // Check for millions
                            if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M'; // Limit to 1 decimal for millions
                            }
                            // Check for thousands
                            if (value >= 1000) {
                                return (value / 1000).toFixed(1) + 'K'; // Limit to 1 decimal for thousands
                            }
                            // If the value is less than 1000, just return the value
                            return value.toLocaleString();
                        }
                    }
                }]
            }
        }

        new Chart(barChart_participants_Canvas, {
            type: 'bar',
            data: barChart_participants_Data,
            options: barChart_participants_Options
        })
    <?php endif; ?>

    <?php if ($trainings['ytd']) : ?>
        // chart YTD
        $(function() {
            const donutData_ytd = {
                labels: ['Done : <?= $chart_status['ytd']['done']['percentage'] ?>%', 'Pending : <?= $chart_status['ytd']['pending']['percentage'] ?>%', 'Cancel : <?= $chart_status['ytd']['cancel']['percentage'] ?>%', 'Reschedule : <?= $chart_status['ytd']['reschedule']['percentage'] ?>%'],
                datasets: [{
                    data: [<?= $chart_status['ytd']['done']['value'] ?>, <?= $chart_status['ytd']['pending']['value'] ?>, <?= $chart_status['ytd']['cancel']['value'] ?>, <?= $chart_status['ytd']['reschedule']['value'] ?>],
                    backgroundColor: ['#007bff', '#adb5bd', '#f56954', '#ffc107', ],
                }]
            };

            const donutOptions_ytd = {
                maintainAspectRatio: false,
                responsive: true,
                cutout: '60%' // This makes it a donut (vs. full pie)
            };

            new Chart($('#donutChart_ytd'), {
                type: 'doughnut',
                data: donutData_ytd,
                options: donutOptions_ytd
            });
        });

        var barChart_budget_ytd_Canvas = $('#barChart_budget_ytd').get(0).getContext('2d')

        var barChart_budget_ytd_Data = {
            labels: ['<?= $year_month_str ?>'],
            datasets: [{
                    label: 'Plan',
                    backgroundColor: '#007bff',
                    borderColor: '#007bff',
                    data: [<?= $chart_budget['ytd']['grand_total']['value'] ?>]
                },
                {
                    label: 'Actual',
                    backgroundColor: '#ffc107',
                    borderColor: '#ffc107',
                    data: [<?= $chart_budget['ytd']['actual_budget']['value'] ?>]
                }
            ]
        }

        var barChart_budget_ytd_Options = {
            responsive: true,
            maintainAspectRatio: false,
            datasetFill: false,
            tooltips: {
                callbacks: {
                    // This formats the value in the tooltip
                    label: function(tooltipItem, data) {
                        return tooltipItem.yLabel.toLocaleString(); // Adds thousands separator
                    }
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: false, // Ensure it doesn't start at 0
                        min: 0,
                        callback: function(value) {
                            // Check for billions
                            if (value >= 1000000000) {
                                return (value / 1000000000).toFixed(1) + 'B'; // Limit to 1 decimal for billions
                            }
                            // Check for millions
                            if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M'; // Limit to 1 decimal for millions
                            }
                            // Check for thousands
                            if (value >= 1000) {
                                return (value / 1000).toFixed(1) + 'K'; // Limit to 1 decimal for thousands
                            }
                            // If the value is less than 1000, just return the value
                            return value.toLocaleString();
                        }
                    }
                }]
            }
        }

        new Chart(barChart_budget_ytd_Canvas, {
            type: 'bar',
            data: barChart_budget_ytd_Data,
            options: barChart_budget_ytd_Options
        })

        var barChart_participants_ytd_Canvas = $('#barChart_participants_ytd').get(0).getContext('2d')

        var barChart_participants_ytd_Data = {
            labels: ['<?= $year_month_str ?>'],
            datasets: [{
                    label: 'Plan',
                    backgroundColor: '#007bff',
                    borderColor: '#007bff',
                    data: [<?= $chart_participants['ytd']['total_participants']['value'] ?>]
                },
                {
                    label: 'Actual',
                    backgroundColor: '#ffc107',
                    borderColor: '#ffc107',
                    data: [<?= $chart_participants['ytd']['actual_participants']['value'] ?>]
                }
            ]
        }

        var barChart_participants_ytd_Options = {
            responsive: true,
            maintainAspectRatio: false,
            datasetFill: false,
            tooltips: {
                callbacks: {
                    // This formats the value in the tooltip
                    label: function(tooltipItem, data) {
                        return tooltipItem.yLabel.toLocaleString(); // Adds thousands separator
                    }
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: false, // Ensure it doesn't start at 0
                        min: 0,
                        callback: function(value) {
                            // Check for billions
                            if (value >= 1000000000) {
                                return (value / 1000000000).toFixed(1) + 'B'; // Limit to 1 decimal for billions
                            }
                            // Check for millions
                            if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M'; // Limit to 1 decimal for millions
                            }
                            // Check for thousands
                            if (value >= 1000) {
                                return (value / 1000).toFixed(1) + 'K'; // Limit to 1 decimal for thousands
                            }
                            // If the value is less than 1000, just return the value
                            return value.toLocaleString();
                        }
                    }
                }]
            }
        }

        new Chart(barChart_participants_ytd_Canvas, {
            type: 'bar',
            data: barChart_participants_ytd_Data,
            options: barChart_participants_ytd_Options
        })
    <?php endif; ?>
</script>