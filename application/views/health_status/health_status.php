<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Current Health Status</h1>
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Health Status</h3>
            </div>

            <form action="<?= base_url() ?>tour_of_duty/submit" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">

                <div class="card-body">
                    <a href="<?= base_url() ?>health_status/edit" class="btn btn-primary w-100">Edit</a><br><br>
                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>NO</th>
                                <th>NRP</th>
                                <th>FULL NAME</th>
                                <th>MATRIX POINT</th>
                                <th>SITE</th>
                                <th>LEVEL</th>
                                <th>JABATAN</th>
                                <th>TAHUN</th>
                                <th>STATUS KESEHATAN</th>
                                <th>STATUS KESEHATAN STRING</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php $status_bg = [null => 'none', 5 => 'primary', 4 => 'info', 3 => 'secondary', 2 => 'warning', 1 => 'danger']; ?>
                            <?php foreach ($health_status as $i_hs => $hs_i) : ?>
                                <tr>
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td><?= $i++ ?></td>
                                    <td><?= $hs_i['NRP'] ?></td>
                                    <td><?= $hs_i['FullName'] ?></td>
                                    <td><?= $hs_i['matrix_point_name'] ?></td>
                                    <td><?= $hs_i['oa_name'] ?></td>
                                    <td><?= $hs_i['oal_name'] ?></td>
                                    <td><?= $hs_i['oalp_name'] ?></td>
                                    <td><?= $hs_i['year'] ?></td>
                                    <td class="bg-<?= $status_bg[$hs_i['status_id']] ?>"><?= $hs_i['hs_name'] ? strtoupper($hs_i['hs_name']) : '' ?></td>
                                    <td><?= $hs_i['status_string'] ?></td>
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
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));
    });
</script>