<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Choose Position</h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Positions</h3>
            </div>
            <div class="card-body">
                <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                    <thead>
                        <tr>
                            <th><i class="fa fa-list"></i>&nbsp;Show Candidate List By</th>
                            <th>No</th>
                            <th>Position</th>
                            <th>Area</th>
                            <th>Level</th>
                            <th>Matrix Point</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($positions as $pstn) : ?>
                            <?php if ($pstn['id'] == 1) continue; ?>
                            <tr>
                                <td class="row">
                                    <a href="<?= base_url() ?>talent/candidate_list/<?= md5($pstn['id']) ?>?method=AC" class="btn btn-xs btn-primary col-lg-6"><strong>Assessment Center</strong></a>
                                    <a href="<?= base_url() ?>talent/candidate_list/<?= md5($pstn['id']) ?>?method=PR" class="btn btn-xs btn-info col-lg-6"><strong>Potential Review</strong></a>
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $pstn['name'] ?></td>
                                <td><?= $pstn['oa_name'] ?></td>
                                <td><?= $pstn['oal_name'] ?></td>
                                <td><?= $pstn['mp_name'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));
    })
</script>