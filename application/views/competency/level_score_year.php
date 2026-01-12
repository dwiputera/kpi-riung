<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Soft Skill Score: <strong><?= $assess_method['name'] ?></strong></h1>
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Year Change</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="<?= base_url() ?>comp_settings/level_score/year/<?= md5($assess_method['id']) ?>" method="get">
                <div class="card-body">
                    <div class="form-group">
                        <label>Year:</label>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="input-group date" id="year" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-target="#year" value="<?= $year ?>" name="year" />
                                    <div class="input-group-append" data-target="#year" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                <?php echo form_error('year', '<div class="text-danger small">', '</div>'); ?>
                            </div>
                            <div class="col-lg-4">
                                <button type="submit" class="btn btn-primary w-100">Change</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </form>
        </div>
        <!-- /.card -->

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><strong><?= $assess_method['name'] ?></strong></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <a href="<?= base_url() ?>comp_settings/level_score/year_edit/<?= md5($assess_method['id']) ?>?year=<?= $year ?>" class="btn btn-primary w-100"><i class="fa fa-edit"></i> Edit</a><br><br>
                <table id="" class="table table-bordered table-striped datatable-filter-column" data-filter-columns="4:multiple,5,6">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Full Name</th>
                            <th>NRP</th>
                            <th>Jabatan</th>
                            <th>Level</th>
                            <th>Area</th>
                            <?php foreach ($comp_lvl as $i_cl => $cl_i) : ?>
                                <th colspan="3"><?= $cl_i['name'] ?></th>
                            <?php endforeach; ?>
                            <th>Vendor</th>
                            <th>Recommendation</th>
                            <th>Remarks</th>
                            <th>Score</th>
                            <th>Assessment Insight (Strength)</th>
                            <th>Assessment Insight (Development)</th>
                            <th>Talent Insight</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <?php foreach ($comp_lvl as $i_cl => $cl_i) : ?>
                                <th>Plan</th>
                                <th>Actual</th>
                                <th>Gap</th>
                            <?php endforeach; ?>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($employees as $i_e => $e_i) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $e_i['FullName'] ?></td>
                                <td><?= $e_i['NRP'] ?></td>
                                <td><?= $e_i['oalp_name'] ?></td>
                                <td><?= $e_i['oal_name'] ?></td>
                                <td><?= $e_i['oa_name'] ?></td>
                                <?php foreach ($comp_lvl as $i_cl => $cl_i) : ?>
                                    <?php
                                    $plan  = $e_i['cl_target'][$cl_i['id']];
                                    $actual = $e_i['cl_score'][$cl_i['id']];
                                    $gap   = (is_numeric($actual) && is_numeric($plan)) ? ($actual - $plan) : null;
                                    $bg    = is_null($gap) ? 'secondary' : ($gap > 0 ? 'success' : ($gap < 0 ? 'danger' : 'primary'));
                                    ?>
                                    <td><?= is_null($plan) ? '' : $plan ?></td>
                                    <td><?= is_null($actual) ? '' : $actual ?></td>
                                    <td class="bg-<?= $bg ?>"><?= is_null($gap) ? '' : $gap ?></td>
                                <?php endforeach; ?>
                                <td><?= $e_i['vendor'] ?></td>
                                <td><?= $e_i['recommendation'] ?></td>
                                <td><?= $e_i['remarks'] ?></td>
                                <td><?= $e_i['score'] ?></td>
                                <td>
                                    <div class="wysiwyg-preview-scroll">
                                        <?= $e_i['assessment_insight_strength'] ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="wysiwyg-preview-scroll">
                                        <?= $e_i['assessment_insight_development'] ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="wysiwyg-preview-scroll">
                                        <?= $e_i['talent_insight'] ?>
                                    </div>
                                </td>
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

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<style>
    .wysiwyg-preview-scroll {
        max-height: 90px;
        /* bisa kamu adjust 70–120 */
        overflow: auto;
        padding: 6px 8px;
        border: 1px dashed #ddd;
        border-radius: 4px;
        background: #fafafa;
        font-size: 0.875rem;
    }

    .wysiwyg-preview-scroll p:last-child,
    .wysiwyg-preview-scroll ul:last-child,
    .wysiwyg-preview-scroll ol:last-child {
        margin-bottom: 0;
    }
</style>

<script>
    //Date picker
    $('#year').datetimepicker({
        format: 'YYYY', // Only year
        viewMode: 'years',
    });

    // Trigger submit saat tahun berubah dari picker
    $('#year').on('change.datetimepicker', function(e) {
        $(this).find('input').closest('form').submit();
    });

    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });
    });
</script>