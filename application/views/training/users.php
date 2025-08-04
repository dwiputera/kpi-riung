<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0"><?= strtoupper($type) ?> Training Participants: <strong><?= $$type['nama_program'] ?></strong></h1>
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">ATMP List</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <?php if ($$type) : ?>
                    <table id="datatable_training" class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td>NAMA PROGRAM</td>
                                <td><?= $$type['nama_program'] ?></td>
                            </tr>
                            <tr>
                                <td>BATCH</td>
                                <td><?= $$type['batch'] ?></td>
                            </tr>
                            <tr>
                                <td>MONTH</td>
                                <td><?= $$type['month'] ?></td>
                            </tr>
                            <tr>
                                <td>START DATE</td>
                                <td><?= $$type['start_date'] ?></td>
                            </tr>
                            <tr>
                                <td>END DATE</td>
                                <td><?= $$type['end_date'] ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <!-- /.card-body -->
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Participant List</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <a data-toggle="modal" data-target="#addUser" class="btn btn-primary w-100" href="#addUserModal">
                    <strong>+ Assign user</strong>
                </a><br><br>
                <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NRP</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php $status_str = ['P' => 'Pending', 'Y' => 'Done', 'N' => 'Canceled', 'R' => 'Reschedule']; ?>
                        <?php $status_bg = ['P' => 'none', 'Y' => 'primary', 'N' => 'danger', 'R' => 'warning']; ?>
                        <?php foreach ($participants as $training_user) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $training_user['NRP'] ?></td>
                                <td><?= $training_user['FullName'] ?></td>
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

<!-- Add Nav Modal -->
<div class="modal fade" id="addUser" tabindex="-1" role="dialog" aria-labelledby="addUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form action="<?= base_url() ?>training/<?= strtoupper($type) ?>/participants/<?= md5($$type['id']) ?>?year=<?= $year ?>&action=assign" method="post">
            <div class="modal-content">
                <div class="modal-body">
                    <!-- User Select Dropdown -->
                    <div class="form-group">
                        <label for="userSelect">Select User</label>
                        <select class="form-control select2" multiple="multiple" data-dropdown-css-class="select2-purple" id="userSelect" name="NRP[]" style="width: 100%;">
                            <option value="">-- Choose User --</option>
                            <?php foreach ($users as $index => $user): ?>
                                <option value="<?= $user['NRP'] ?>" <?= in_array($user['NRP'], array_column($participants, 'NRP')) ? 'selected' : '' ?>><?= $user['NRP'] ?> | <?= $user['PSubarea'] ?> | <?= $user['EmployeeSubgroup'] ?> | <?= $user['OrgUnitName'] ?> | <?= $user['PositionName'] ?> | <?= $user['FullName'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    setupFilterableDatatable($('.datatable-filter-column'));

    $(document).ready(function() {
        $('#addUser .select2').select2({
            dropdownParent: $('#addUser')
        });
    });
</script>