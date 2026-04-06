<style>
    .row.smallbox-row>[class*="col-"] {
        display: flex;
    }

    .row.smallbox-row .small-box {
        width: 100%;
        min-height: 100px;
    }

    .row.smallbox-row .small-box .inner h3 {
        font-size: 1.5rem !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .row.smallbox-row .small-box .inner p {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Observation Results</h1>
    </div>
</div>
<!-- /.content-header -->

<?php
function e($value)
{
    return htmlspecialchars((string)($value ?? '-'), ENT_QUOTES, 'UTF-8');
}

function fci_summary_badge($fciJson)
{
    if (empty($fciJson)) {
        return '<span class="badge badge-secondary">-</span>';
    }

    $fci = json_decode($fciJson, true);

    if (!is_array($fci) || empty($fci)) {
        return '<span class="badge badge-secondary">-</span>';
    }

    $baik = 0;
    $buruk = 0;

    foreach ($fci as $val) {
        if ($val === 'Baik') {
            $baik++;
        } elseif ($val === 'Buruk') {
            $buruk++;
        }
    }

    return '
        <span class="badge badge-success mr-1">Baik: ' . $baik . '</span>
        <span class="badge badge-danger">Buruk: ' . $buruk . '</span>
    ';
}

function sum_obs_keys($obs, $keys = [])
{
    $total = 0;
    foreach ($keys as $key) {
        $total += (float)($obs[$key] ?? 0);
    }
    return $total;
}

/**
 * =========================
 * PREPARE DATA FOR SUMMARY & CHARTS
 * =========================
 */
$totalObservation = count($observations);

$materialCounts = [];
$jobSiteCounts   = [];
$dateCounts      = [];
$fciCounts       = ['Baik' => 0, 'Buruk' => 0];

$sumPrimaryCycle   = 0;
$sumSecondaryCycle = 0;
$countPrimaryCycle = 0;
$countSecondaryCycle = 0;

$sumDiggingTime = 0;
$countDiggingTime = 0;

$frontDeviationTotals = [
    'Front Amblas'     => 0,
    'Front Licin'      => 0,
    'Front Menanjak'   => 0,
    'Front Berair'     => 0,
    'Front Perbaikan'  => 0,
    'Front Crowded'    => 0,
    'Front Berdebu'    => 0,
    'Front Sempit'     => 0,
    'General Front'    => 0,
    'Front Lembek'     => 0,
    'Front Undulating' => 0,
];

$operatorDeviationTotals = [
    'Kombinasi Attch'  => 0,
    'Loading Method'   => 0,
    'Product Knowledge' => 0,
    'Method'           => 0,
    'Reporting'        => 0,
    'Safety Operation' => 0,
];

$totalFrontDeviation = 0;
$totalOperatorDeviation = 0;

foreach ($observations as $obs) {
    // Material Type
    $material = !empty($obs['material_type']) ? $obs['material_type'] : 'Unknown';
    if (!isset($materialCounts[$material])) {
        $materialCounts[$material] = 0;
    }
    $materialCounts[$material]++;

    // Job Site
    $jobSite = !empty($obs['area_name']) ? $obs['area_name'] : 'Unknown';
    if (!isset($jobSiteCounts[$jobSite])) {
        $jobSiteCounts[$jobSite] = 0;
    }
    $jobSiteCounts[$jobSite]++;

    // Trend by Date
    $dateKey = !empty($obs['date']) ? date('Y-m-d', strtotime($obs['date'])) : 'Unknown';
    if (!isset($dateCounts[$dateKey])) {
        $dateCounts[$dateKey] = 0;
    }
    $dateCounts[$dateKey]++;

    // FCI
    if (!empty($obs['fci'])) {
        $fci = json_decode($obs['fci'], true);
        if (is_array($fci)) {
            foreach ($fci as $val) {
                if ($val === 'Baik') {
                    $fciCounts['Baik']++;
                } elseif ($val === 'Buruk') {
                    $fciCounts['Buruk']++;
                }
            }
        }
    }

    // Avg Primary / Secondary Cycle
    if (isset($obs['avg_cycle_time_primary']) && $obs['avg_cycle_time_primary'] !== null && $obs['avg_cycle_time_primary'] !== '') {
        $sumPrimaryCycle += (float)$obs['avg_cycle_time_primary'];
        $countPrimaryCycle++;
    }

    if (isset($obs['avg_cycle_time_secondary']) && $obs['avg_cycle_time_secondary'] !== null && $obs['avg_cycle_time_secondary'] !== '') {
        $sumSecondaryCycle += (float)$obs['avg_cycle_time_secondary'];
        $countSecondaryCycle++;
    }

    if (isset($obs['digging_time']) && $obs['digging_time'] !== null && $obs['digging_time'] !== '') {
        $sumDiggingTime += (float)$obs['digging_time'];
        $countDiggingTime++;
    }

    // Front deviations
    $frontDeviationTotals['Front Amblas']     += (float)($obs['front_amblas'] ?? 0);
    $frontDeviationTotals['Front Licin']      += (float)($obs['front_licin'] ?? 0);
    $frontDeviationTotals['Front Menanjak']   += (float)($obs['front_menanjak'] ?? 0);
    $frontDeviationTotals['Front Berair']     += (float)($obs['front_berair'] ?? 0);
    $frontDeviationTotals['Front Perbaikan']  += (float)($obs['front_perbaikan'] ?? 0);
    $frontDeviationTotals['Front Crowded']    += (float)($obs['front_crowded'] ?? 0);
    $frontDeviationTotals['Front Berdebu']    += (float)($obs['front_berdebu'] ?? 0);
    $frontDeviationTotals['Front Sempit']     += (float)($obs['front_sempit'] ?? 0);
    $frontDeviationTotals['General Front']    += (float)($obs['general_front'] ?? 0);
    $frontDeviationTotals['Front Lembek']     += (float)($obs['front_lembek'] ?? 0);
    $frontDeviationTotals['Front Undulating'] += (float)($obs['front_undulating'] ?? 0);

    // Operator deviations
    $operatorDeviationTotals['Kombinasi Attch']   += (float)($obs['kombinasi_attch'] ?? 0);
    $operatorDeviationTotals['Loading Method']    += (float)($obs['loading_method_dev'] ?? 0);
    $operatorDeviationTotals['Product Knowledge'] += (float)($obs['product_knowledge'] ?? 0);
    $operatorDeviationTotals['Method']            += (float)($obs['method_knowledge'] ?? 0);
    $operatorDeviationTotals['Reporting']         += (float)($obs['reporting'] ?? 0);
    $operatorDeviationTotals['Safety Operation']  += (float)($obs['safety_operation'] ?? 0);

    $totalFrontDeviation += sum_obs_keys($obs, [
        'front_amblas',
        'front_licin',
        'front_menanjak',
        'front_berair',
        'front_perbaikan',
        'front_crowded',
        'front_berdebu',
        'front_sempit',
        'general_front',
        'front_lembek',
        'front_undulating'
    ]);

    $totalOperatorDeviation += sum_obs_keys($obs, [
        'kombinasi_attch',
        'loading_method_dev',
        'product_knowledge',
        'method_knowledge',
        'reporting',
        'safety_operation'
    ]);
}

ksort($dateCounts);
arsort($frontDeviationTotals);

$avgPrimaryCycle   = $countPrimaryCycle > 0 ? round($sumPrimaryCycle / $countPrimaryCycle, 2) : 0;
$avgSecondaryCycle = $countSecondaryCycle > 0 ? round($sumSecondaryCycle / $countSecondaryCycle, 2) : 0;
$avgDiggingTime    = $countDiggingTime > 0 ? round($sumDiggingTime / $countDiggingTime, 2) : 0;

$mostMaterial = !empty($materialCounts) ? array_keys($materialCounts, max($materialCounts))[0] : '-';
$mostJobSite  = !empty($jobSiteCounts) ? array_keys($jobSiteCounts, max($jobSiteCounts))[0] : '-';
$topFrontDeviation = !empty($frontDeviationTotals) ? array_keys($frontDeviationTotals, max($frontDeviationTotals))[0] : '-';

// ambil top 6 deviasi front
$topFrontDeviationChart = array_slice($frontDeviationTotals, 0, 6, true);
?>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <?php if ($this->session->flashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Small Boxes -->
        <div class="row smallbox-row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $totalObservation ?></h3>
                        <p>Total Observation</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $fciCounts['Baik'] ?></h3>
                        <p>Total FCI Baik</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $fciCounts['Buruk'] ?></h3>
                        <p>Total FCI Buruk</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 style="font-size:1.4rem;"><?= e($mostJobSite) ?></h3>
                        <p>Job Site Terbanyak</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row smallbox-row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 style="font-size:1.5rem;"><?= e($mostMaterial) ?></h3>
                        <p>Material Dominan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-mountain"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3><?= number_format($avgPrimaryCycle, 2) ?></h3>
                        <p>Avg Primary Cycle (Sec)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-stopwatch"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-dark">
                    <div class="inner">
                        <h3><?= number_format($avgSecondaryCycle, 2) ?></h3>
                        <p>Avg Secondary Cycle (Sec)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-indigo" style="background:#6610f2;color:#fff;">
                    <div class="inner">
                        <h3 style="font-size:1.3rem;"><?= e($topFrontDeviation) ?></h3>
                        <p>Deviasi Front Dominan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Observation per Material Type</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="materialChart" style="min-height:320px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Observation per Job Site</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="jobSiteChart" style="min-height:320px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row">
            <div class="col-md-8">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Trend Observation per Date</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" style="min-height:320px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Komposisi FCI</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="fciChart" style="min-height:320px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 3 -->
        <div class="row">
            <div class="col-md-4">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Rata-rata Cycle Time</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="cycleCompareChart" style="min-height:320px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Top Deviasi Front</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="frontDeviationChart" style="min-height:320px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-outline card-dark">
                    <div class="card-header">
                        <h3 class="card-title">Deviasi Operator</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="operatorDeviationChart" style="min-height:320px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Optional insight boxes -->
        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-stopwatch"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Avg Digging Time</span>
                        <span class="info-box-number"><?= number_format($avgDiggingTime, 2) ?> Sec</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-road"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Deviasi Front</span>
                        <span class="info-box-number"><?= number_format($totalFrontDeviation, 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-user-cog"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Deviasi Operator</span>
                        <span class="info-box-number"><?= number_format($totalOperatorDeviation, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">Submitted Observation Results</h3>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <a href="<?= base_url('observation/add') ?>" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Add Observation Result
                    </a>
                </div>

                <!-- Filter Range Tanggal -->
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Filter Data</h3>
                    </div>
                    <div class="card-body">
                        <form id="filterRangeForm" method="GET" action="<?= base_url('observation') ?>">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="date_from">Tanggal Dari</label>
                                        <input
                                            type="date"
                                            class="form-control"
                                            id="date_from"
                                            name="date_from"
                                            value="<?= htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="date_to">Tanggal Sampai</label>
                                        <input
                                            type="date"
                                            class="form-control"
                                            id="date_to"
                                            name="date_to"
                                            value="<?= htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-primary mr-2" id="btnSubmitFilter">
                                            <i class="fas fa-search"></i> Tampilkan
                                        </button>

                                        <a href="<?= base_url('observation') ?>" class="btn btn-default" id="btnResetFilter">
                                            <i class="fas fa-sync-alt"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php
                $activeDateFrom = !empty($filters['date_from']) ? date('d-m-Y', strtotime($filters['date_from'])) : null;
                $activeDateTo   = !empty($filters['date_to']) ? date('d-m-Y', strtotime($filters['date_to'])) : null;
                ?>

                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped table-hover datatable-filter-column">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>NRP Operator</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Job Site</th>
                                <th>Pit Location</th>
                                <th>Unit Type</th>
                                <th>Hauler Count</th>
                                <th>Material Type</th>
                                <th>Primary CT</th>
                                <th>Secondary CT</th>
                                <th>FCI</th>
                                <th>Deviation Front</th>
                                <th>Recommendation</th>
                                <th>Observer</th>
                                <th width="14%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($observations)) : ?>
                                <?php $i = 1; ?>
                                <?php foreach ($observations as $obs) : ?>
                                    <?php if ($obs['area_name'] == 'RMHO') continue; ?>
                                    <tr data-id="<?= (int)$obs['id'] ?>">
                                        <td><?= $i++ ?></td>
                                        <td><?= e($obs['NRP'] ?? '-') ?></td>
                                        <td><?= !empty($obs['date']) ? date('d-m-Y', strtotime($obs['date'])) : '-' ?></td>
                                        <td><?= !empty($obs['date']) ? date('H:i', strtotime($obs['date'])) : '-' ?></td>
                                        <td><?= e($obs['area_name'] ?? '-') ?></td>
                                        <td><?= e($obs['pit_location'] ?? '-') ?></td>
                                        <td><?= e($obs['unit_type'] ?? '-') ?></td>
                                        <td><?= e($obs['hauler_count'] ?? '-') ?></td>
                                        <td><?= e($obs['material_type'] ?? '-') ?></td>
                                        <td><?= e($obs['avg_cycle_time_primary'] ?? '-') ?></td>
                                        <td><?= e($obs['avg_cycle_time_secondary'] ?? '-') ?></td>
                                        <td><?= fci_summary_badge($obs['fci'] ?? null) ?></td>
                                        <td style="min-width:220px;">
                                            <?= nl2br(e($obs['deviation_front'] ?? '-')) ?>
                                        </td>
                                        <td style="min-width:220px;">
                                            <?= nl2br(e($obs['recommendation'] ?? '-')) ?>
                                        </td>
                                        <td><?= e($obs['observer_name'] ?? ($obs['observer'] ?? '-')) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= base_url('observation/edit/' . $obs['id']) ?>" class="btn btn-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="<?= base_url('observation/delete/' . $obs['id']) ?>" class="btn btn-danger btn-delete-observation">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="pageLoadingOverlay" style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(255,255,255,0.75);
        z-index:99999;
        align-items:center;
        justify-content:center;
    ">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;"></div>
            <div class="mt-3 font-weight-bold text-dark">Loading data observation...</div>
        </div>
    </div>
</section>
<!-- /.content -->

<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>
<script src="<?= base_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>

<script>
    function showPageLoadingOverlay(message = 'Loading data observation...') {
        $('#pageLoadingOverlay').find('.font-weight-bold').text(message);
        $('#pageLoadingOverlay').css('display', 'flex');
    }

    function hidePageLoadingOverlay() {
        $('#pageLoadingOverlay').hide();
    }

    $(function() {
        $('#filterRangeForm').on('submit', function(e) {
            const dateFrom = $('#date_from').val();
            const dateTo = $('#date_to').val();

            if (dateFrom && dateTo && dateFrom > dateTo) {
                e.preventDefault();
                alert('Tanggal "Dari" tidak boleh lebih besar dari tanggal "Sampai".');
                return false;
            }

            showPageLoadingOverlay('Memuat data berdasarkan range tanggal...');
        });

        $('#btnResetFilter').on('click', function() {
            showPageLoadingOverlay('Mereset filter data...');
        });

        $(document).on('click', '.btn-delete-observation', function() {
            showPageLoadingOverlay('Memproses penghapusan data...');
        });

        $(window).on('pageshow', function() {
            hidePageLoadingOverlay();
        });
    });
</script>

<script>
    $(function() {
        const tableSelector = $('.datatable-filter-column');
        setupFilterableDatatable(tableSelector);

        const dt = $('#datatable').DataTable();

        $(document).on('click', '.btn-delete-observation', function(e) {
            if (!confirm('Are you sure you want to delete this observation?')) {
                e.preventDefault();
            }
        });

        // =========================
        // RAW DATA FROM PHP
        // =========================
        const rawObservations = <?= json_encode($observations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        const observationMap = {};
        rawObservations.forEach(function(item) {
            observationMap[String(item.id)] = item;
        });

        const defaultColors = [
            '#007bff', '#28a745', '#ffc107', '#dc3545',
            '#17a2b8', '#6f42c1', '#fd7e14', '#20c997',
            '#343a40', '#6610f2', '#e83e8c'
        ];

        function num(val) {
            if (val === null || val === undefined || val === '') return 0;
            const n = parseFloat(val);
            return isNaN(n) ? 0 : n;
        }

        function safeText(val, fallback = 'Unknown') {
            return (val !== null && val !== undefined && val !== '') ? String(val) : fallback;
        }

        function dateOnly(val) {
            if (!val) return 'Unknown';
            const d = new Date(val);
            if (isNaN(d.getTime())) return 'Unknown';

            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function getAppliedObservations() {
            const rows = dt.rows({
                search: 'applied'
            }).nodes().toArray();

            const filtered = [];

            rows.forEach(function(row) {
                const id = $(row).attr('data-id');
                if (id && observationMap[id]) {
                    filtered.push(observationMap[id]);
                }
            });

            return filtered;
        }

        function buildStats(dataRows) {
            const materialCounts = {};
            const jobSiteCounts = {};
            const dateCounts = {};
            const fciCounts = {
                'Baik': 0,
                'Buruk': 0
            };

            const frontDeviationTotals = {
                'Front Amblas': 0,
                'Front Licin': 0,
                'Front Menanjak': 0,
                'Front Berair': 0,
                'Front Perbaikan': 0,
                'Front Crowded': 0,
                'Front Berdebu': 0,
                'Front Sempit': 0,
                'General Front': 0,
                'Front Lembek': 0,
                'Front Undulating': 0
            };

            const operatorDeviationTotals = {
                'Kombinasi Attch': 0,
                'Loading Method': 0,
                'Product Knowledge': 0,
                'Method': 0,
                'Reporting': 0,
                'Safety Operation': 0
            };

            let sumPrimaryCycle = 0;
            let sumSecondaryCycle = 0;
            let countPrimaryCycle = 0;
            let countSecondaryCycle = 0;

            dataRows.forEach(function(obs) {
                const material = safeText(obs.material_type);
                materialCounts[material] = (materialCounts[material] || 0) + 1;

                const jobSite = safeText(obs.area_name);
                jobSiteCounts[jobSite] = (jobSiteCounts[jobSite] || 0) + 1;

                const dtKey = dateOnly(obs.date);
                dateCounts[dtKey] = (dateCounts[dtKey] || 0) + 1;

                if (obs.fci) {
                    try {
                        const fci = JSON.parse(obs.fci);
                        if (fci && typeof fci === 'object') {
                            Object.keys(fci).forEach(function(key) {
                                if (fci[key] === 'Baik') fciCounts.Baik++;
                                if (fci[key] === 'Buruk') fciCounts.Buruk++;
                            });
                        }
                    } catch (e) {}
                }

                if (obs.avg_cycle_time_primary !== null && obs.avg_cycle_time_primary !== '') {
                    sumPrimaryCycle += num(obs.avg_cycle_time_primary);
                    countPrimaryCycle++;
                }

                if (obs.avg_cycle_time_secondary !== null && obs.avg_cycle_time_secondary !== '') {
                    sumSecondaryCycle += num(obs.avg_cycle_time_secondary);
                    countSecondaryCycle++;
                }

                frontDeviationTotals['Front Amblas'] += num(obs.front_amblas);
                frontDeviationTotals['Front Licin'] += num(obs.front_licin);
                frontDeviationTotals['Front Menanjak'] += num(obs.front_menanjak);
                frontDeviationTotals['Front Berair'] += num(obs.front_berair);
                frontDeviationTotals['Front Perbaikan'] += num(obs.front_perbaikan);
                frontDeviationTotals['Front Crowded'] += num(obs.front_crowded);
                frontDeviationTotals['Front Berdebu'] += num(obs.front_berdebu);
                frontDeviationTotals['Front Sempit'] += num(obs.front_sempit);
                frontDeviationTotals['General Front'] += num(obs.general_front);
                frontDeviationTotals['Front Lembek'] += num(obs.front_lembek);
                frontDeviationTotals['Front Undulating'] += num(obs.front_undulating);

                operatorDeviationTotals['Kombinasi Attch'] += num(obs.kombinasi_attch);
                operatorDeviationTotals['Loading Method'] += num(obs.loading_method_dev);
                operatorDeviationTotals['Product Knowledge'] += num(obs.product_knowledge);
                operatorDeviationTotals['Method'] += num(obs.method_knowledge);
                operatorDeviationTotals['Reporting'] += num(obs.reporting);
                operatorDeviationTotals['Safety Operation'] += num(obs.safety_operation);
            });

            const sortedDateCounts = {};
            Object.keys(dateCounts).sort().forEach(function(key) {
                sortedDateCounts[key] = dateCounts[key];
            });

            const sortedFront = Object.entries(frontDeviationTotals)
                .sort((a, b) => b[1] - a[1]);

            const topFront = sortedFront.slice(0, 6);

            return {
                materialLabels: Object.keys(materialCounts),
                materialData: Object.values(materialCounts),

                jobSiteLabels: Object.keys(jobSiteCounts),
                jobSiteData: Object.values(jobSiteCounts),

                trendLabels: Object.keys(sortedDateCounts),
                trendData: Object.values(sortedDateCounts),

                fciLabels: Object.keys(fciCounts),
                fciData: Object.values(fciCounts),

                cycleCompareLabels: ['Primary Cycle Time', 'Secondary Cycle Time'],
                cycleCompareData: [
                    countPrimaryCycle ? +(sumPrimaryCycle / countPrimaryCycle).toFixed(2) : 0,
                    countSecondaryCycle ? +(sumSecondaryCycle / countSecondaryCycle).toFixed(2) : 0
                ],

                frontDeviationLabels: topFront.map(item => item[0]),
                frontDeviationData: topFront.map(item => item[1]),

                operatorDeviationLabels: Object.keys(operatorDeviationTotals),
                operatorDeviationData: Object.values(operatorDeviationTotals)
            };
        }

        function emptyChartTextPlugin() {
            return {
                beforeDraw: function(chart) {
                    const hasData = chart.data.datasets.some(ds => (ds.data || []).some(v => Number(v) > 0));
                    if (hasData) return;

                    const ctx = chart.chart.ctx;
                    const width = chart.chart.width;
                    const height = chart.chart.height;

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = '14px sans-serif';
                    ctx.fillStyle = '#999';
                    ctx.fillText('Tidak ada data pada filter saat ini', width / 2, height / 2);
                    ctx.restore();
                }
            };
        }

        Chart.plugins.register(emptyChartTextPlugin());

        // =========================
        // INIT CHART INSTANCES
        // =========================
        const materialChart = new Chart(document.getElementById('materialChart'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Total Observation',
                    data: [],
                    backgroundColor: defaultColors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                }
            }
        });

        const jobSiteChart = new Chart(document.getElementById('jobSiteChart'), {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: defaultColors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        const trendChart = new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Observation',
                    data: [],
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.20)',
                    fill: true,
                    lineTension: 0.2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                }
            }
        });

        const fciChart = new Chart(document.getElementById('fciChart'), {
            type: 'pie',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        const cycleCompareChart = new Chart(document.getElementById('cycleCompareChart'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Rata-rata Detik',
                    data: [],
                    backgroundColor: ['#6c757d', '#343a40']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });

        const frontDeviationChart = new Chart(document.getElementById('frontDeviationChart'), {
            type: 'horizontalBar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Total Deviasi',
                    data: [],
                    backgroundColor: '#ffc107'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });

        const operatorDeviationChart = new Chart(document.getElementById('operatorDeviationChart'), {
            type: 'radar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Total Deviasi Operator',
                    data: [],
                    backgroundColor: 'rgba(220, 53, 69, 0.20)',
                    borderColor: '#dc3545',
                    pointBackgroundColor: '#dc3545'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scale: {
                    ticks: {
                        beginAtZero: true
                    }
                }
            }
        });

        function updateAllCharts() {
            const filteredRows = getAppliedObservations();
            const stats = buildStats(filteredRows);

            materialChart.data.labels = stats.materialLabels;
            materialChart.data.datasets[0].data = stats.materialData;
            materialChart.data.datasets[0].backgroundColor = stats.materialLabels.map((_, i) => defaultColors[i % defaultColors.length]);
            materialChart.update();

            jobSiteChart.data.labels = stats.jobSiteLabels;
            jobSiteChart.data.datasets[0].data = stats.jobSiteData;
            jobSiteChart.data.datasets[0].backgroundColor = stats.jobSiteLabels.map((_, i) => defaultColors[i % defaultColors.length]);
            jobSiteChart.update();

            trendChart.data.labels = stats.trendLabels;
            trendChart.data.datasets[0].data = stats.trendData;
            trendChart.update();

            fciChart.data.labels = stats.fciLabels;
            fciChart.data.datasets[0].data = stats.fciData;
            fciChart.update();

            cycleCompareChart.data.labels = stats.cycleCompareLabels;
            cycleCompareChart.data.datasets[0].data = stats.cycleCompareData;
            cycleCompareChart.update();

            frontDeviationChart.data.labels = stats.frontDeviationLabels;
            frontDeviationChart.data.datasets[0].data = stats.frontDeviationData;
            frontDeviationChart.update();

            operatorDeviationChart.data.labels = stats.operatorDeviationLabels;
            operatorDeviationChart.data.datasets[0].data = stats.operatorDeviationData;
            operatorDeviationChart.update();
        }

        // pertama kali load
        updateAllCharts();

        // saat table berubah karena filter/search/sort/paging/draw
        $('#datatable').on('draw.dt', function() {
            updateAllCharts();
        });

        // jaga-jaga kalau plugin filter trigger redraw async
        setTimeout(function() {
            updateAllCharts();
        }, 300);
    });
</script>