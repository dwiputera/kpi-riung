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
                <h3 class="card-title">Month Change</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="<?= base_url() ?>training/MTS" method="get">
                <div class="card-body">
                    <div class="form-group">
                        <label>Month:</label>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="input-group date" id="month" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-target="#month" value="<?= $month ?>" name="month" />
                                    <div class="input-group-append" data-target="#month" data-toggle="datetimepicker">
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

        <?php if ($trainings) : ?>
            <!-- DONUT CHART -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Chart MTS</h3>

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
                    <canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Trainings</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <a href="<?= base_url() ?>training/MTS/edit/<?= $month ?>" class="btn btn-primary w-100">Edit</a><br><br>
                    <table id="datatable_training" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Fixed</th>
                                <th>Nama Program</th>
                                <th>PIC</th>
                                <th>Tempat Pelaksanaan</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Note</th>
                                <th>RMHO</th>
                                <th>RHML</th>
                                <th>RMIP</th>
                                <th>REBH</th>
                                <th>RMTU</th>
                                <th>RMTS</th>
                                <th>RMGM</th>
                                <th>Total Participants</th>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php $status_str = ['P' => 'Pending', 'Y' => 'Done', 'N' => 'Canceled', 'R' => 'Reschedule']; ?>
                            <?php $status_bg = ['P' => 'none', 'Y' => 'primary', 'N' => 'danger', 'R' => 'warning']; ?>
                            <?php foreach ($trainings as $training) : ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td class="bg-<?= $status_bg[$training['fixed']] ?>"><?= $training['fixed'] ?></td>
                                    <td><?= $training['nama_program'] ?></td>
                                    <td><?= $training['departemen_pengampu'] ?></td>
                                    <td><?= $training['tempat'] ?></td>
                                    <td><?= $training['start_date'] ?></td>
                                    <td><?= $training['end_date'] ?></td>
                                    <td><?= $training['keterangan'] ?></td>
                                    <td><?= $training['rmho'] ?></td>
                                    <td><?= $training['rhml'] ?></td>
                                    <td><?= $training['rmip'] ?></td>
                                    <td><?= $training['rebh'] ?></td>
                                    <td><?= $training['rmtu'] ?></td>
                                    <td><?= $training['rmts'] ?></td>
                                    <td><?= $training['rmgm'] ?></td>
                                    <td><?= $training['rmho'] + $training['rhml'] + $training['rmip'] + $training['rebh'] + $training['rmtu'] + $training['rmts'] + $training['rmgm'] ?></td>
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
    $('#month').datetimepicker({
        format: 'YYYY-MM',
        viewMode: 'months'
    });

    <?php if ($trainings) : ?>
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

        $(function() {
            const donutData = {
                labels: ['Not Fixed :  <?= $chart['fixed_not']['percentage'] ?>%', 'Fixed :  <?= $chart['fixed']['percentage'] ?>%'],
                datasets: [{
                    data: [<?= $chart['fixed_not']['value'] ?>, <?= $chart['fixed']['value'] ?>],
                    backgroundColor: ['#f56954', '#007bff'],
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
    <?php endif; ?>
</script>