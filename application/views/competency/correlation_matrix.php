<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-tabs">
            <div class="card-header">
                <h3 class="card-title mt-2">Correlation Matrix</h3>
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
                                    <?php
                                    $percentage = $correlation_matrix[$ord]['correlations'][$ord2];
                                    $targetUrl = site_url('comp_settings/correlation_matrix/analysis/' . md5($ord) . '/' . md5($ord2));
                                    ?>
                                    <td
                                        class="cell-link bg-<?= $percentage > 60 ? 'primary' : ($percentage > 40 ? 'success' : ($percentage > 20 ? 'warning' : 'danger')) ?>"
                                        data-href="<?= $targetUrl ?>"
                                        role="link"
                                        tabindex="0"
                                        title="Lihat detail">
                                        <?= $percentage ?>%
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<style>
    td.cell-link {
        cursor: pointer;
        transition: filter 0.2s ease-in-out;
    }

    td.cell-link:hover {
        filter: brightness(0.5);
    }
</style>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });

        $(document).on('click', '.datatable-filter-column td.cell-link', function(e) {
            const href = $(this).data('href');
            if (!href) return;

            if (e.ctrlKey || e.metaKey) {
                window.open(href, '_blank');
            } else {
                window.location.href = href;
            }
        });

        $(document).on('keydown', '.datatable-filter-column td.cell-link', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const href = $(this).data('href');
                if (href) window.location.href = href;
            }
        });
    });
</script>