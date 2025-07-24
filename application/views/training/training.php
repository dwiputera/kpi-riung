<?php
$context = strtolower($context);
?>
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit <?= ucfirst($context) ?> <?= $context === 'atmp' ? $year : $month ?></h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Trainings</h3>
            </div>
            <form action="<?= base_url("training/{$context}/submit") ?>" method="post" id="data-form">
                <div class="card-body">
                    <input type="hidden" name="context" value="<?= $context ?>">
                    <?php if ($context === 'atmp'): ?>
                        <input type="hidden" name="year" value="<?= $year ?>">
                    <?php else: ?>
                        <input type="hidden" name="month" value="<?= $month ?>">
                    <?php endif; ?>
                    <input type="hidden" name="proceed" value="Y">
                    <input type="hidden" name="json_data" id="json_data">

                    <?php
                    if ($context === 'atmp') {
                        $this->load->view('training/_atmp_table', compact('trainings'));
                    } elseif ($context === 'mts') {
                        $this->load->view('training/_mts_table', compact('trainings'));
                    } elseif ($context === 'monitoring') {
                        $this->load->view('training/_monitoring_table', compact('trainings'));
                    }
                    ?>
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
                year: 'input[name="year"]',
                proceed: 'input[name="proceed"]'
            }
        );

        window.cancelForm = function() {
            if (confirm('Yakin batal?')) {
                location.href = '<?= base_url("training/{$context}") ?>';
            }
        };
    });
</script>