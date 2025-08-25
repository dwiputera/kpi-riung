<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-tabs">
            <div class="card-header">
                <h3 class="card-title mt-2">Correlation_matrix</h3>
            </div>
            <div class="card-body">
                <!-- <div class="table-responsive"> -->
                <table class="table table-striped datatable-filter-column mb-0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Departements</th>
                            <?php foreach ($correlation_matrix as $cm) : ?>
                                <th><?= $cm['name'] ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($correlation_matrix as $cm) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $cm['name'] ?></td>
                                <?php foreach ($correlation_matrix as $cm2) : ?>
                                    <?php $percentage = $cm['correlations'][$cm2['id']]; ?>
                                    <td class="bg-<?= $percentage > 60 ? 'primary' : ($percentage > 40 ? 'success' : ($percentage > 20 ? 'warning' : 'danger')) ?>"><?= $percentage ?>%</td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- </div> -->
            </div>
        </div>
    </div>
</section>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });
    })
</script>