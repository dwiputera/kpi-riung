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
            <?php if ($type == 'mts') : ?>
                <form action="<?= base_url() ?>training/MTS/participants/<?= md5($mts['id']) ?>?action=status_change" method="post" id="data-form">
                <?php endif; ?>
                <div class="card-body">
                    <a data-toggle="modal" data-target="#addUser" class="btn btn-primary w-100" href="#addUserModal">
                        <strong>+ Assign user</strong>
                    </a><br><br>
                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th>No</th>
                                <?php if ($type == 'mts') : ?>
                                    <th>Status</th>
                                <?php endif; ?>
                                <th>NRP</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php $status_str = ['P' => 'Pending', 'Y' => 'Done', 'N' => 'Canceled']; ?>
                            <?php $status_bg = ['P' => 'none', 'Y' => 'primary', 'N' => 'danger']; ?>
                            <?php foreach ($participants as $training_user) : ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <?php if ($type == 'mts') : ?>
                                        <td>
                                            <select class="form-control form-control-sm status-select"
                                                name="training_users[<?= md5($training_user['id']) ?>]">
                                                <option value="P" <?= $training_user['status'] == 'P' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Y" <?= $training_user['status'] == 'Y' ? 'selected' : '' ?>>Done</option>
                                                <option value="N" <?= $training_user['status'] == 'N' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </td>
                                    <?php endif; ?>
                                    <td><?= $training_user['NRP'] ?></td>
                                    <td><?= $training_user['FullName'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <?php if ($type == 'mts') : ?>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-3">
                                <button type="button" class="w-100 btn btn-default" onclick="cancelForm()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                            <div class="col-lg-9">
                                <button type="submit" class="w-100 btn btn-info">
                                    <i class="fas fa-paper-plane"></i> Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
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

<style>
    .status-select option {
        background-color: #fff !important;
        color: #000 !important;
    }
</style>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    setupFilterableDatatable($('.datatable-filter-column'));

    $(document).ready(function() {
        $('#addUser .select2').select2({
            dropdownParent: $('#addUser')
        });

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
            location.href = '<?= base_url("training/MTS" . ($year ? '?year=' . $year : '')) ?>';
        }
    }
</script>