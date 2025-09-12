<style>
    .nav-tabs .nav-link.active {
        font-weight: bold;
    }
</style>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">ORGANIZATION STRUCTURE</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline card-tabs">
            <?php
            function renderTabs($pstn_active_ids, $area_lvl_pstn, $area_lvl_pstn_mp = [], $parentKey = 0, $parent_key_id = 0)
            {
                static $counter = 0;

                if (empty($area_lvl_pstn) && empty($area_lvl_pstn_mp)) {
                    return;
                }

                $tabId = 'tab_' . md5($parentKey);
                $parent_key_hash = md5($parent_key_id);

                // Tentukan tab aktif default
                $active_default = true;
                foreach ($area_lvl_pstn as $pstn) {
                    if (in_array($pstn['id'], $pstn_active_ids)) {
                        $active_default = false;
                        break;
                    }
                }
            ?>
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="<?= $tabId ?>" role="tablist">
                        <li class="nav-item" data-toggle="modal" data-target="#addPosition" data-parent="<?= $parent_key_hash ?>">
                            <a class="nav-link" href="#<?= $parent_key_hash ?>">+ Add Position</a>
                        </li>

                        <?php foreach ($area_lvl_pstn as $i => $pstn):
                            $paneId = $tabId . '_' . $pstn['id'];
                            $active = ($active_default && $i === 0) || (!$active_default && in_array($pstn['id'], $pstn_active_ids));
                        ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $active ? 'active' : '' ?>" id="tab_<?= $paneId ?>_tab"
                                    data-toggle="pill" href="#<?= $paneId ?>" role="tab"
                                    aria-controls="<?= $paneId ?>" aria-selected="<?= $active ? 'true' : 'false' ?>">
                                    <?= htmlspecialchars($pstn['id']) ?> | <?= htmlspecialchars($pstn['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>

                        <?php foreach ($area_lvl_pstn_mp as $i => $pstn):
                            $paneId = $tabId . '_mp_' . $pstn['id'];
                        ?>
                            <li class="nav-item border border-warning">
                                <a class="nav-link" id="tab_<?= $paneId ?>_tab"
                                    data-toggle="pill" href="#<?= $paneId ?>" role="tab"
                                    aria-controls="<?= $paneId ?>" aria-selected="false">
                                    <?= htmlspecialchars($pstn['id']) ?> | <?= htmlspecialchars($pstn['name']) ?>
                                    <span class="badge badge-warning"><?= $pstn['oa_name'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="tab-content" id="<?= $tabId ?>_tabContent">
                    <?php foreach ($area_lvl_pstn as $i => $pstn):
                        $paneId = $tabId . '_' . $pstn['id'];
                        $active = ($active_default && $i === 0) || (!$active_default && in_array($pstn['id'], $pstn_active_ids));
                        $showClass = $active ? 'show active' : '';
                    ?>
                        <div class="tab-pane fade <?= $showClass ?>" id="<?= $paneId ?>" role="tabpanel" aria-labelledby="tab_<?= $paneId ?>_tab">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <strong>
                                            Area: <?= $pstn['oa_name'] ?><br>
                                            Level: <?= $pstn['oal_name'] ?><br>
                                            <?php if ($pstn['matrix_point']) : ?>
                                                Matrix Point: <span class="badge badge-warning"><?= $pstn['mp_name'] ?></span><br>
                                            <?php endif; ?>
                                        </strong>
                                        <a data-toggle="modal" data-target="#updatePosition"
                                            data-position_id="<?= md5($pstn['id']) ?>"
                                            data-area_id="<?= $pstn['area_id'] ? md5($pstn['area_id']) : "" ?>"
                                            data-type="<?= $pstn['type'] ?>"
                                            data-position_name="<?= $pstn['name'] ?>"
                                            data-matrix_point="<?= $pstn['matrix_point'] ? md5($pstn['matrix_point']) : null ?>"
                                            data-oal_id="<?= md5($pstn['oal_id']) ?>" href="#<?= $parent_key_hash ?>">
                                            <strong class="btn btn-primary btn-xs">update Position</strong>
                                        </a>
                                        <a onclick="return confirm('are you sure?')" href="<?= base_url() ?>organization_settings/position/delete/<?= md5($pstn['id']) ?>">
                                            <span class="btn btn-danger btn-xs">Delete Position</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-6">
                                        <ul>
                                            <?php if (!empty($pstn['users'])): ?>
                                                <?php foreach ($pstn['users'] as $user): ?>
                                                    <li>
                                                        <a href="<?= base_url() ?>organization_settings/position/position_user/delete/<?= md5($user['oalpu_id']) ?>" onclick="return confirm('are you sure?')">
                                                            <span class="btn btn-danger btn-xs">Unassign</span>
                                                        </a>
                                                        <?= htmlspecialchars($user['NRP']) ?> | <?= htmlspecialchars($user['FullName']) ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <a data-toggle="modal" data-target="#addUser" data-position_id="<?= md5($pstn['id']) ?>" href="#<?= $parent_key_hash ?>">
                                                <strong>+ Assign user</strong>
                                            </a>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <?php if ($pstn['children'] || $pstn['children_mp']) : ?>
                                <?php
                                $childPrefix = $pstn['type'] === 'matrix_point' ? $tabId . '_mp_' . $pstn['id'] : $tabId . '_' . $pstn['id'];
                                renderTabs($pstn_active_ids, $pstn['children'], $pstn['children_mp'], $childPrefix, $pstn['id']);
                                ?>
                            <?php else: ?>
                                <div class="card-header p-0 pt-1 border-bottom-0">
                                    <ul class="nav nav-tabs" id="<?= $tabId ?>" role="tablist">
                                        <li class="nav-item" data-toggle="modal" data-target="#addPosition" data-parent="<?= $parent_key_hash ?>">
                                            <a class="nav-link" href="#<?= $parent_key_hash ?>">+ Add Position</a>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach ($area_lvl_pstn_mp as $i => $pstn):
                        $paneId = $tabId . '_mp_' . $pstn['id'];
                    ?>
                        <div class="tab-pane fade" id="<?= $paneId ?>" role="tabpanel" aria-labelledby="tab_<?= $paneId ?>_tab">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <strong>
                                            Area: <?= $pstn['oa_name'] ?><br>
                                            Level: <?= $pstn['oal_name'] ?><br>
                                        </strong>
                                    </div>
                                    <div class="col-lg-6">
                                        <ul>
                                            <?php if (!empty($pstn['users'])): ?>
                                                <?php foreach ($pstn['users'] as $user): ?>
                                                    <li><?= htmlspecialchars($user['NRP']) ?> | <?= htmlspecialchars($user['FullName']) ?></li>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $filtered_children = array_filter($pstn['children'], function ($child) use ($pstn) {
                                return empty($child['matrix_point']) || $child['matrix_point'] == $pstn['id'];
                            });

                            $filtered_children_mp = array_filter($pstn['children_mp'] ?? [], function ($child) use ($pstn) {
                                return empty($child['matrix_point']) || $child['matrix_point'] == $pstn['id'];
                            });

                            renderTabs($pstn_active_ids, $filtered_children, $filtered_children_mp, $tabId, $pstn['id']);
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php
            }
            ?>

            <?php renderTabs($pstn_active_ids, $area_lvl_pstn); ?>
        </div>
    </div>
</section>

<!-- Add Nav Modal -->
<div class="modal fade" id="addPosition" tabindex="-1" role="dialog" aria-labelledby="addPositionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form action="<?= site_url('organization_settings/position/add') ?>" method="post">
            <input type="hidden" name="parent_id">
            <input type="hidden" id="method" name="method" value="automatic">
            <div class="modal-content">
                <div class="modal-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-tabs-three-automatic-tab" data-toggle="pill" href="#custom-tabs-three-automatic" role="tab" aria-controls="custom-tabs-three-automatic" aria-selected="true">AUTOMATIC</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-three-manual-tab" data-toggle="pill" href="#custom-tabs-three-manual" role="tab" aria-controls="custom-tabs-three-manual" aria-selected="true">MANUAL</a>
                        </li>
                    </ul>
                </div>
                <div class="modal-body">
                    <div class="tab-content" id="custom-tabs-three-tabContent">
                        <div class="tab-pane fade show active" id="custom-tabs-three-automatic" role="tabpanel" aria-labelledby="custom-tabs-three-automatic-tab">
                            <input type="hidden" name="parent_key" id="modalParentKey">

                            <!-- User Select Dropdown -->
                            <div class="form-group">
                                <label for="userPositionSelect">Select User</label>
                                <select class="form-control select2" id="userPositionSelect" style="width: 100%;">
                                    <option value="">-- Choose User --</option>
                                    <?php foreach ($users as $index => $user): ?>
                                        <option value="<?= $index ?>"><?= $user['NRP'] ?> | <?= $user['PSubarea'] ?> | <?= $user['EmployeeSubgroup'] ?> | <?= $user['OrgUnitName'] ?> | <?= $user['PositionName'] ?> | <?= $user['FullName'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Auto-filled Text Fields -->
                            <div class="form-group">
                                <label for="fullName">Full Name</label>
                                <input type="text" class="form-control automatic-column" name="FullName" id="fullName" readonly>
                            </div>
                            <div class="form-group">
                                <label for="nrp">NRP</label>
                                <input type="text" class="form-control automatic-column" name="NRP" id="nrp" readonly>
                            </div>
                            <div class="form-group">
                                <label for="psubarea">PSubarea</label>
                                <input type="text" class="form-control automatic-column" name="PSubarea" id="psubarea" readonly>
                            </div>
                            <div class="form-group">
                                <label for="employeeSubgroup">Employee Subgroup</label>
                                <input type="text" class="form-control automatic-column" name="EmployeeSubgroup" id="employeeSubgroup" readonly>
                            </div>
                            <div class="form-group">
                                <label for="orgUnitName">Org Unit Name</label>
                                <input type="text" class="form-control automatic-column" name="OrgUnitName" id="orgUnitName" readonly>
                            </div>
                            <div class="form-group">
                                <label for="PositionName">Position Name</label>
                                <input type="text" class="form-control automatic-column" name="PositionName" id="PositionName" readonly>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Add</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-three-manual" role="tabpanel" aria-labelledby="custom-tabs-three-manual-tab">
                            <!-- Optional: Manual Position Name -->
                            <div class="form-group">
                                <select class="form-control select2" id="area_lvl" name="area_lvl">
                                    <option value="">-- Choose Level --</option>
                                    <?php foreach ($area_lvl as $i_alvl => $alvl_i): ?>
                                        <option value="<?= md5($alvl_i['id']) ?>"><?= $alvl_i['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="positionName">Position Name</label>
                                <input type="text" class="form-control" name="position_name" id="positionName">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Add</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Add Nav Modal -->
<div class="modal fade" id="addUser" tabindex="-1" role="dialog" aria-labelledby="addUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form action="<?= site_url('organization_settings/position/position_user/add') ?>" method="post">
            <input type="hidden" name="position_id" id="position_id" required>
            <div class="modal-content">
                <div class="modal-body">
                    <!-- User Select Dropdown -->
                    <div class="form-group">
                        <label for="userSelect">Select User</label>
                        <select class="form-control select2" multiple="multiple" data-dropdown-css-class="select2-purple" id="userSelect" name="NRP[]" style="width: 100%;" required>
                            <option value="">-- Choose User --</option>
                            <?php foreach ($users as $index => $user): ?>
                                <option value="<?= $user['NRP'] ?>"><?= $user['NRP'] ?> | <?= $user['PSubarea'] ?> | <?= $user['EmployeeSubgroup'] ?> | <?= $user['OrgUnitName'] ?> | <?= $user['PositionName'] ?> | <?= $user['EmployeeGroup'] ?> | <?= $user['FullName'] ?></option>
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

<!-- Add Nav Modal -->
<div class="modal fade" id="updatePosition" tabindex="-1" role="dialog" aria-labelledby="updatePositionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form action="<?= site_url('organization_settings/position/update') ?>" method="post">
            <input type="hidden" name="position_id" id="position_id" required>
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update Position</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="area_id">Area</label>
                        <select class="form-control select2" id="area_id" name="area_id" required>
                            <option value="">-- Choose Area --</option>
                            <?php foreach ($area as $i_area => $area_i): ?>
                                <option value="<?= md5($area_i['id']) ?>"><?= $area_i['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="update_subordinate_area" name="update_subordinate_area">
                            <label class="form-check-label" for="update_subordinate_area">also update subordinate area</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="type" name="type">
                            <label class="form-check-label" for="type">matrix point</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="matrix_point">Matrix Point</label>
                        <select class="form-control select2" id="matrix_point" name="matrix_point">
                            <option value="">-- Choose Matrix Point --</option>
                            <?php foreach ($matrix_points as $i_mp => $mp_i): ?>
                                <option value="<?= md5($mp_i['id']) ?>"><?= $mp_i['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="area_lvl2">Level</label>
                        <select class="form-control select2" id="area_lvl2" name="area_lvl" required>
                            <option value="">-- Choose Level --</option>
                            <?php foreach ($area_lvl as $i_alvl => $alvl_i): ?>
                                <!-- <option value="<?= md5($alvl_i['id']) ?>"><?= $alvl_i['oa_name'] ?> | <?= $alvl_i['name'] ?></option> -->
                                <option value="<?= md5($alvl_i['id']) ?>"><?= $alvl_i['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="positionName">Position Name</label>
                        <input type="text" class="form-control" name="position_name" id="positionName" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .nav-tabs {
        white-space: nowrap;
        display: flex;
        flex-wrap: nowrap;
        scrollbar-width: thin;
        overflow-y: hidden;
        overflow-x: scroll;
        scrollbar-width: none;
        /* Firefox */
        -ms-overflow-style: none;
        /* IE and Edge */
    }

    .nav-tabs::-webkit-scrollbar {
        display: none;
    }

    .nav-tabs .nav-item {
        flex: 0 0 auto;
    }
</style>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>

<!-- JS to populate inputs -->
<script>
    $(document).ready(function() {
        $('#addPosition .select2').select2({
            dropdownParent: $('#addPosition')
        });

        $('#addUser .select2').select2({
            dropdownParent: $('#addUser')
        });

        FuzzySelect2.apply('#addUser .select2');

        $('#updatePosition .select2').select2({
            dropdownParent: $('#updatePosition')
        });

        const usersData = <?= json_encode($users) ?>;

        $('#userPositionSelect').on('change', function() {
            const selectedIndex = $(this).val();
            if (selectedIndex === '') {
                $('#fullName').val('');
                $('#nrp').val('');
                $('#psubarea').val('');
                $('#employeeSubgroup').val('');
                $('#orgUnitName').val('');
                $('#PositionName').val('');
                return;
            }

            const user = usersData[selectedIndex];
            $('#fullName').val(user.FullName);
            $('#nrp').val(user.NRP);
            $('#psubarea').val(user.PSubarea);
            $('#employeeSubgroup').val(user.EmployeeSubgroup);
            $('#orgUnitName').val(user.OrgUnitName);
            $('#PositionName').val(user.PositionName);
        });
    });

    $('#addPosition').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var parentKey = button.data('parent'); // Extract info from data-* attribute
        var modal = $(this);
        modal.find('#modalParentKey').val(parentKey);
    });

    $('#updatePosition').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var position_id = button.data('position_id');
        var area_id = button.data('area_id');
        var matrix_point = button.data('matrix_point');
        var position_name = button.data('position_name');
        var oal_id = button.data('oal_id');
        var type = button.data('type');
        var modal = $(this);

        modal.find('#position_id').val(position_id);
        modal.find('#positionName').val(position_name);
        modal.find('#area_lvl2').val(oal_id).trigger('change'); // trigger change for Select2
        modal.find('#area_id').val(area_id).trigger('change'); // trigger change for Select2
        modal.find('#matrix_point').val(matrix_point).trigger('change'); // trigger change for Select2
        // Atur checkbox berdasarkan type
        if (type == 'matrix_point') {
            modal.find('#type').prop('checked', true);
        } else {
            modal.find('#type').prop('checked', false);
        }
    });

    $('#addUser').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var position_id = button.data('position_id'); // Extract info from data-* attribute
        var modal = $(this);
        modal.find('#position_id').val(position_id);
    });

    $('#custom-tabs-three-manual-tab').on('shown.bs.tab', function() {
        $('#positionName').prop('required', true).prop('disabled', false);
        $('#userPositionSelect').prop('required', false).prop('disabled', true);
        $('#area_lvl').prop('required', true);
        $('.automatic-column').prop('disabled', true);
        $('#method').val('manual');
    });

    $('#custom-tabs-three-automatic-tab').on('shown.bs.tab', function() {
        $('#positionName').prop('required', false).prop('disabled', true);
        $('#userPositionSelect').prop('required', true).prop('disabled', false);
        $('#area_lvl').prop('required', false);
        $('.automatic-column').prop('disabled', false);
        $('#method').val('automatic');
    });
</script>