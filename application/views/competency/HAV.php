<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Chart Card -->
        <div class="card card-primary card-tabs">
            <div class="card-header">
                <h3 class="card-title">HAV Mapping</h3>
            </div>
            <div class="card-body">
                <canvas id="humanAssetChart" width="80%"></canvas>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="toggleLabels" checked>
                    <label class="form-check-label" for="toggleLabels">Tampilkan semua nama</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="freezeFilter">
                    <label class="form-check-label" for="freezeFilter">Freeze Filter (Lock current chart view)</label>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card card-primary card-tabs">
            <div class="card-header">
                <h3 class="card-title">HAV Data</h3>
            </div>
            <div class="card-body">
                <div class="status-summary d-flex flex-wrap" id="statusSummary">
                    <!-- Status percentages will be displayed here -->
                </div>
                <table id="employeeTable" class="table table-bordered table-striped datatable-filter-column"
                    data-filter-columns="2:multiple,3:multiple,4:multiple,5:multiple,6,10:number,11:number">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>No</th>
                            <th>Area</th>
                            <th>Level</th>
                            <th>Matrix Point</th>
                            <th>Position</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>NRP</th>
                            <th>Nama</th>
                            <th>Performance</th>
                            <th>Potential</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        foreach ($employees as $e): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('employee/employee/profile/' . md5($e['NRP'])) ?>" target="_blank"
                                        class="label label-primary"><i class="fa fa-eye"></i></a>
                                    <a href="<?= base_url('HAV/mapping/edit/' . md5($e['NRP'])) ?>" target="_blank"
                                        class="label label-primary"><i class="fa fa-edit"></i></a>
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $e['oa_name'] ?></td>
                                <td><?= $e['oal_name'] ?></td>
                                <td><?= $e['matrix_point_name'] ?></td>
                                <td><?= $e['oalp_name'] ?></td>
                                <td><?= $e['method'] ?></td>
                                <td>Calculating...</td>
                                <td><?= $e['NRP'] ?></td>
                                <td><?= $e['FullName'] ?></td>
                                <td><?= $e['avg_pstn_score'] ?></td>
                                <td><?= $e['assess_score'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<style>
    .status-summary {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }

    .status-summary .badge {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid #dee2e6;
    }
</style>

<script src="<?= base_url('assets/js/chart.umd.min.js') ?>"></script>
<script src="<?= base_url('assets/js/chartjs-plugin-datalabels.min.js') ?>"></script>
<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>
<script src="<?= base_url('assets/js/HAV/HAV.js') ?>"></script>