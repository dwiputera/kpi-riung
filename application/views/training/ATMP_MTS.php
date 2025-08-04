<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">MTS By ATMP</h1>
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
                <h3 class="card-title">ATMP: <strong><?= $atmp['nama_program'] ?></strong></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="datatable_training" class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <td>NAMA PROGRAM</td>
                            <td><?= $atmp['nama_program'] ?></td>
                        </tr>
                        <tr>
                            <td>BATCH</td>
                            <td><?= $atmp['batch'] ?></td>
                        </tr>
                        <tr>
                            <td>MONTH</td>
                            <td><?= $atmp['month'] ?></td>
                        </tr>
                        <tr>
                            <td>START DATE</td>
                            <td><?= $atmp['start_date'] ?></td>
                        </tr>
                        <tr>
                            <td>END DATE</td>
                            <td><?= $atmp['end_date'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- /.card-header -->
        </div>
        <!-- /.card -->

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">MTS List</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <button type="button" class="btn btn-primary w-100" data-toggle="modal" data-target="#modal-assignMTS">
                    ASSIGN MTS
                </button><br><br>
                <table id="datatable_training_mts" class="table table-bordered table-striped datatable-filter-column">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Action</th>
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
                        <?php foreach ($atmp['mts'] as $training) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><a href="<?= base_url() ?>training/ATMP/MTS/<?= md5($atmp['id']) ?>?year=<?= $year ?>&mts_hash=<?= md5($training['id']) ?>&action=unassign" class="btn btn-danger btn-sm" onclick="return confirm('are you sure?')">unassign</a></td>
                                <td><?= $training['month'] ?></td>
                                <td><?= $training['departemen_pengampu'] ?></td>
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
                                <td><?= $training['start_date'] ?></td>
                                <td><?= $training['end_date'] ?></td>
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
                                <td><?= $training['total_participant'] ?? 0 ?></td>
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
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<div class="modal fade" id="modal-assignMTS">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Assign MTS untuk: <strong><?= $atmp['nama_program'] ?></strong></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="datatable_training_mts_modal" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Action</th>
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
                        <?php foreach ($mts as $training) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><a href="<?= base_url() ?>training/ATMP/MTS/<?= md5($atmp['id']) ?>?year=<?= $year ?>&mts_hash=<?= md5($training['id']) ?>&action=assign" class="btn btn-primary btn-sm">assign</a></td>
                                <td><?= $training['month'] ?></td>
                                <td><?= $training['departemen_pengampu'] ?></td>
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
                                <td><?= $training['start_date'] ?></td>
                                <td><?= $training['end_date'] ?></td>
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
                                <td><?= $training['total_participant'] ?? 0 ?></td>
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
            <!-- /.modal-content -->
        </div>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<!-- Page specific script -->
<script>
    $(document).ready(function() {
        $('#year').datetimepicker({
            format: 'YYYY', // Only year
            viewMode: 'years',
        });

        setupFilterableDatatable($('#datatable_training_mts'));
        const dtOptions = {
            autoWidth: false,
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
            lengthChange: true,
            pageLength: 10,
            scrollX: true,
            orderCellsTop: true,
            fixedHeader: true,
        };
        $('#datatable_training_mts_modal').DataTable(dtOptions);
        // $('#modal-assignMTS').on('shown.bs.modal', function() {
        //     setupFilterableDatatable($('#datatable_training_mts_modal'));
        // });
        // $('#modal-assignMTS').on('hide.bs.modal', function() {
        //     setupFilterableDatatable($('#datatable_training_mts'));
        // });
    });
</script>