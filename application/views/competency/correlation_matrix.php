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
                <table class="table table-striped table-bordered datatable-filter-column mb-0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Departements</th>
                            <?php foreach ($order as $ord) : ?>
                                <th class="text-wrap"><?= $correlation_matrix[$ord]['name'] ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($order as $ord) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $correlation_matrix[$ord]['name'] ?></td>
                                <?php foreach ($order as $ord2) : ?>
                                    <?php $percentage = $correlation_matrix[$ord]['correlations'][$ord2]; ?>
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