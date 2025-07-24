<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Monitoring <?= $month ?></h1>
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
            <form action="<?= base_url() ?>training/monitoring/submit" method="post" id="data-form">
                <div class="card-body">
                    <input type="hidden" name="month" value="<?= $month ?>">
                    <input type="hidden" name="proceed" value="Y">
                    <input type="hidden" name="json_data" id="json_data">

                    <?php
                    function trnHash($id)
                    {
                        return md5($id);
                    }

                    function renderEditable($field, $value, $hash)
                    {
                        return "<td contenteditable='true' class='editable-cell' data-trn_id_hash='{$hash}' data-name='{$field}'>" . htmlspecialchars($value) . "</td>";
                    }

                    function renderInput($type, $field, $value, $hash)
                    {
                        return "<td><input type='{$type}' class='form-control form-control-sm' data-trn_id_hash='{$hash}' data-name='{$field}' value='" . htmlspecialchars($value) . "'></td>";
                    }

                    function renderSelectStatus($selectedValue, $hash)
                    {
                        $options = ['P' => 'Pending', 'Y' => 'Done', 'N' => 'Canceled', 'R' => 'Reschedule'];
                        $html = "<td><select class='form-control form-control-sm select-status' data-trn_id_hash='{$hash}' data-name='status'>";
                        foreach ($options as $val => $label) {
                            $selected = $selectedValue === $val ? 'selected' : '';
                            $html .= "<option value='{$val}' {$selected}>{$label}</option>";
                        }
                        return $html . "</select></td>";
                    }
                    ?>

                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Status</th>
                                <th>Nama Program</th>
                                <th>PIC</th>
                                <th>Tempat</th>
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
                                <th>Actual Budget</th>
                                <th>Actual Participants</th>
                                <th>Total Participants</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trainings as $i => $training): ?>
                                <?php $hash = trnHash($training['id']); ?>
                                <tr data-row-id="<?= $i + 1 ?>">
                                    <td><?= $i + 1 ?></td>
                                    <?= renderSelectStatus($training['status'], $hash) ?>
                                    <?= renderEditable('nama_program', $training['nama_program'], $hash) ?>
                                    <?= renderEditable('departemen_pengampu', $training['departemen_pengampu'], $hash) ?>
                                    <?= renderEditable('tempat', $training['tempat'], $hash) ?>
                                    <?= renderInput('date', 'start_date', $training['start_date'], $hash) ?>
                                    <?= renderInput('date', 'end_date', $training['end_date'], $hash) ?>
                                    <?= renderEditable('keterangan', $training['keterangan'], $hash) ?>

                                    <?php
                                    $fields = [
                                        'rmho',
                                        'rhml',
                                        'rmip',
                                        'rebh',
                                        'rmtu',
                                        'rmts',
                                        'rmgm',
                                        'actual_budget',
                                        'actual_participants',
                                        'total_participants'
                                    ];
                                    foreach ($fields as $field):
                                        echo renderInput('number', $field, $training[$field] ?? 0, $hash);
                                    endforeach;
                                    ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-4">
                            <button type="button" class="btn btn-default w-100" onclick="cancelForm()">Cancel</button>
                        </div>
                        <div class="col-lg-8">
                            <button type="submit" class="btn btn-info w-100">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Scripts -->
<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>
<script src="<?= base_url('assets/js/table-form-json.js') ?>"></script>

<script>
    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));

        bindEditableTableForm(
            '#data-form',
            '#datatable',
            '#json_data', {
                month: 'input[name="month"]',
                proceed: 'input[name="proceed"]'
            }
        );

        window.cancelForm = function() {
            if (confirm('Yakin batal?')) {
                location.href = '<?= base_url() . 'training/monitoring' . ($month ? '?month=' . $month : '') ?>';
            }
        };
    });
</script>