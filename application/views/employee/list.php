<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><strong>Employees</strong></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="" class="table table-bordered table-striped datatable-filter-column" data-filter-columns="4:multiple,5,6">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>No</th>
                            <th>NRP</th>
                            <th>Name</th>
                            <th>Matrix Point</th>
                            <th>Position</th>
                            <th>Level</th>
                            <th>Area</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($employees as $i_e => $e_i) : ?>
                            <tr>
                                <td>
                                    <?php if (!empty($user) && (is_array($user) ? ($user['role'] ?? '') === 'admin' : $user === 'admin')): ?>
                                        <button type="button"
                                            class="btn btn-xs btn-primary btn-assign"
                                            data-nrp="<?= $e_i['NRP'] ?>"
                                            data-fullname="<?= $e_i['FullName'] ?>"
                                            data-matrix="<?= $e_i['matrix_point_name'] ?>"
                                            data-position="<?= $e_i['oalp_name'] ?>"
                                            data-level="<?= $e_i['oal_name'] ?>"
                                            data-area="<?= $e_i['oa_name'] ?>"
                                            data-current_id="<?= $e_i['area_lvl_pstn_id'] ?? '' ?>">
                                            <i class="fa fa-user-check"></i> assign position
                                        </button>
                                    <?php else: ?>
                                        <a href="<?= base_url() ?>employee/employee/profile/<?= md5($e_i['NRP']) ?>"
                                            class="btn btn-xs btn-outline-primary" target="_blank">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $e_i['NRP'] ?></td>
                                <td><?= $e_i['FullName'] ?></td>
                                <td><?= $e_i['matrix_point_name'] ?></td>
                                <td><?= $e_i['oalp_name'] ?></td>
                                <td><?= $e_i['oal_name'] ?></td>
                                <td><?= $e_i['oa_name'] ?></td>
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


<?php if (!empty($user) && (is_array($user) ? ($user['role'] ?? '') === 'admin' : $user === 'admin')): ?>
    <!-- Modal: Assign Position -->
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <form id="formAssign" method="post" action="<?= base_url('organization_settings/employee_position/assign_position') ?>">
                <!-- sesuaikan endpoint di atas -->
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title" id="assignModalLabel"><strong>Assign Position</strong></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-white">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- Info karyawan terpilih -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="callout callout-info py-2">
                                    <div class="d-md-flex justify-content-between align-items-center">
                                        <div>
                                            <div><strong>NRP:</strong> <span id="emp_nrp">-</span></div>
                                            <div><strong>Name:</strong> <span id="emp_fullname">-</span></div>
                                        </div>
                                        <div class="mt-2 mt-md-0">
                                            <div><strong>Matrix Point:</strong> <span id="emp_matrix">-</span></div>
                                            <div><strong>Current:</strong> <span id="emp_curpos">-</span> | <strong>Level:</strong> <span id="emp_level">-</span> | <strong>Area:</strong> <span id="emp_area">-</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0 datatable-filter-column" id="tablePositions" data-filter-columns="2:multiple,3,4,5">
                                <thead>
                                    <tr>
                                        <th style="width:60px; text-align:center;">Select</th>
                                        <th>#</th>
                                        <th>Matrix Point</th>
                                        <th>Position</th>
                                        <th>Level</th>
                                        <th>Area</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $j = 1;
                                    foreach ($positions as $p): ?>
                                        <tr>
                                            <td class="text-center align-middle">
                                                <input type="radio"
                                                    name="position_pick"
                                                    class="radio-position"
                                                    value="<?= htmlspecialchars($p['id'] ?? '', ENT_QUOTES) ?>"
                                                    data-posid="<?= htmlspecialchars($p['id'] ?? '', ENT_QUOTES) ?>"
                                                    data-pmatrix="<?= htmlspecialchars($p['mp_name'] ?? '', ENT_QUOTES) ?>"
                                                    data-pposition="<?= htmlspecialchars($p['name'] ?? '', ENT_QUOTES) ?>"
                                                    data-plevel="<?= htmlspecialchars($p['oal_name'] ?? '', ENT_QUOTES) ?>"
                                                    data-parea="<?= htmlspecialchars($p['oa_name'] ?? '', ENT_QUOTES) ?>">
                                            </td>
                                            <td><?= $j++ ?></td>
                                            <td><?= $p['mp_name'] ?? '' ?></td>
                                            <td><?= $p['name'] ?? '' ?></td>
                                            <td><?= $p['oal_name'] ?? '' ?></td>
                                            <td><?= $p['oa_name'] ?? '' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="nrp" id="inp_nrp">
                        <input type="hidden" name="position_id" id="inp_position_id">
                        <input type="hidden" name="mode" id="inp_mode" value="assign">
                    </div>

                    <div class="modal-footer">
                        <div class="d-flex align-items-center w-100">
                            <div class="mr-3">
                                <span class="text-muted">Terpilih:</span>
                                <span id="picked_position" class="font-weight-bold">Belum ada</span>
                            </div>
                            <div class="ml-auto">
                                <button type="button" id="btnUnassign" class="btn btn-outline-danger btn-sm">
                                    <i class="fa fa-times"></i> Unassign
                                </button>
                                <button type="submit" id="btnSaveAssign" class="btn btn-primary btn-sm" disabled>
                                    <i class="fa fa-save"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        // inisialisasi datatable filter
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });

        // buka modal
        $(document).on('click', '.btn-assign', function() {
            const btn = $(this);
            const nrp = btn.data('nrp') || '';
            const fullname = btn.data('fullname') || '';
            const matrix = btn.data('matrix') || '';
            const curpos = btn.data('position') || '';
            const level = btn.data('level') || '';
            const area = btn.data('area') || '';
            const curId = btn.data('current_id') || ''; // optional

            // isi header info
            $('#emp_nrp').text(nrp);
            $('#emp_fullname').text(fullname);
            $('#emp_matrix').text(matrix);
            $('#emp_curpos').text(curpos);
            $('#emp_level').text(level);
            $('#emp_area').text(area);

            // reset form
            $('#inp_nrp').val(nrp);
            $('#inp_position_id').val('');
            $('#inp_mode').val('assign');
            $('#picked_position').text('Belum ada');
            $('#btnSaveAssign').prop('disabled', true);

            // reset radio
            $('#tablePositions input.radio-position').prop('checked', false);

            // pre-check jika ada current id
            if (curId) {
                const $radio = $('#tablePositions input.radio-position[value="' + curId + '"]');
                if ($radio.length) {
                    $radio.prop('checked', true).trigger('change');
                }
            }

            $('#assignModal').modal('show');
        });

        // pilih radio -> set hidden & enable save
        $(document).on('change', '#tablePositions input.radio-position', function() {
            const r = $(this);
            const posId = r.data('posid');
            const mpName = r.data('pmatrix') || '';
            const posName = r.data('pposition') || '';
            const lvlName = r.data('plevel') || '';
            const area = r.data('parea') || '';

            $('#inp_mode').val('assign');
            $('#inp_position_id').val(posId);
            $('#picked_position').text(`${posName} | Level: ${lvlName} | Area: ${area} | MP: ${mpName}`);
            $('#btnSaveAssign').prop('disabled', false);
        });

        // tombol Unassign
        $('#btnUnassign').on('click', function() {
            const nrp = $('#inp_nrp').val();
            if (!nrp) return;

            Swal.fire({
                icon: 'question',
                title: 'Unassign posisi?',
                text: 'Data assignment untuk NRP ini akan dikosongkan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, unassign',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    // set mode unassign & submit
                    $('#inp_mode').val('unassign');
                    // pastikan position_id kosong
                    $('#inp_position_id').val('');
                    $('#formAssign').trigger('submit');
                }
            });
        });

        // validasi submit (hanya untuk mode assign)
        $('#formAssign').on('submit', function(e) {
            const mode = $('#inp_mode').val();
            if (mode === 'assign' && !$('#inp_position_id').val()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih posisi dulu',
                    toast: true,
                    position: 'top-end',
                    timer: 2500,
                    showConfirmButton: false
                });
                return false;
            }
        });
    });
</script>