<?php
// =========================
// PRE CALC: HARD SKILL %
// =========================
$percentHard = 0;

if (!empty($comp_pstn_targets)) {
    // Index target berdasarkan comp_pstn_id
    $targetsByPstnId = [];
    foreach ($comp_pstn_targets as $row) {
        $target = (float)$row['target'];
        if ($target > 0) {
            $targetsByPstnId[$row['comp_pstn_id']] = $target;
        }
    }

    // Index score berdasarkan comp_pstn_id
    $scoresByPstnId = [];
    if (!empty($comp_pstn_scores)) {
        foreach ($comp_pstn_scores as $row) {
            $scoresByPstnId[$row['comp_pstn_id']] = (float)$row['score'];
        }
    }

    $targetsHard = [];
    $scoresHard  = [];

    foreach ($targetsByPstnId as $compId => $targetVal) {
        $targetsHard[] = $targetVal;
        $scoresHard[]  = isset($scoresByPstnId[$compId]) ? $scoresByPstnId[$compId] : 0;
    }

    $totalTargetHard = array_sum($targetsHard);
    $totalScoreHard  = array_sum($scoresHard);

    if ($totalTargetHard > 0) {
        $percentHard = round(($totalScoreHard / $totalTargetHard) * 100, 1);
    }
}

// =========================
// PRE CALC: SOFT SKILL %
// =========================
$percentSoft = 0;

if (!empty($comp_lvl_targets) && !empty($comp_lvl)) {
    // target per comp_lvl_id
    $targetsById = [];
    foreach ($comp_lvl_targets as $row) {
        if ((float)$row['target'] > 0) {
            $targetsById[$row['comp_lvl_id']] = (float)$row['target'];
        }
    }

    // score per comp_lvl_id
    $scoresById = [];
    if (!empty($comp_lvl_scores)) {
        foreach ($comp_lvl_scores as $row) {
            $scoresById[$row['comp_lvl_id']] = (float)$row['clas_score'];
        }
    }

    $targetsSoft = [];
    $scoresSoft  = [];

    foreach ($comp_lvl as $comp) {
        $id = $comp['id'];
        if (isset($targetsById[$id])) {
            $targetsSoft[] = $targetsById[$id];
            $scoresSoft[]  = isset($scoresById[$id]) ? $scoresById[$id] : 0;
        }
    }

    $totalTargetSoft = array_sum($targetsSoft);
    $totalScoreSoft  = array_sum($scoresSoft);

    if ($totalTargetSoft > 0) {
        $percentSoft = round(($totalScoreSoft / $totalTargetSoft) * 100, 1);
    }
}
?>

<style>
    .td-hidden {
        width: 0 !important;
        padding-right: 0 !important;
        padding-left: 0 !important;
        border: none !important;
        overflow: hidden !important;
        white-space: nowrap !important;
        font-size: 0 !important;
    }

    .show-more-btn {
        background: none;
        border: none;
        padding: 6px 0;
        margin-top: 10px;
        font-size: 14px;
        color: #777;
        cursor: pointer;
        outline: none !important;
        width: 100%;
        text-align: center;
    }

    .show-more-btn:hover {
        color: #444;
    }

    .show-more-btn span {
        font-size: 12px;
        margin-left: 6px;
    }


    .metric-box {
        background: #f6f8fa;
        border: 1px solid #d0d7de;
        padding: 6px 12px;
        border-radius: 6px;
        display: inline-block;
        margin-top: 8px;
        font-size: 15px;
        font-weight: 700;
        color: #555;
    }

    .achievement-box {
        margin-top: 6px;
        padding: 8px 14px;
        border-radius: 6px;
        background: #e8f1ff;
        border: 1px solid #bcd4ff;
        display: inline-block;
        font-size: 15px;
        font-weight: 700;
        color: #0d47a1;
    }

    .achievement-box span {
        font-weight: 700;
    }

    @media print {

        /* Hanya card yang dicetak */
        body * {
            visibility: hidden;
            /* zoom: 98% !important; */
        }

        .printable-card,
        .printable-card * {
            visibility: visible;
        }

        .printable-card {
            position: relative;
            left: 0;
            top: 0;
            margin: 0 auto;
            max-width: 1000mm;
            /* lebar kira² A4 dengan margin */
            width: 100%;
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        /* Kunci: semua grafik ikut nge-fit ke kolomnya */
        .printable-card canvas {
            max-width: 100% !important;
            width: 100% !important;
            height: auto !important;
            /* proporsional */
        }

        /* Kalau grafik masih terasa tinggi, boleh dibatasi */
        .printable-card canvas[height] {
            max-height: 260px !important;
            /* sesuaikan kalau perlu */
        }

        /* Hide header/sidebar/footer AdminLTE saat print */
        .main-header,
        .main-sidebar,
        .main-footer {
            display: none !important;
        }

        .printable-card img {
            max-width: 100% !important;
            height: auto !important;
            display: block;
        }


        /* Agar tabel fit ke kolom */
        .printable-card table {
            width: 100% !important;
            table-layout: fixed !important;
        }

        .printable-card .table-responsive {
            overflow: visible !important;
            /* hapus scroll */
        }

        .printable-card th,
        .printable-card td {
            white-space: normal !important;
            /* wrap teks */
            word-wrap: break-word !important;
            font-size: 12px;
            /* optional biar lebih muat */
        }

        /* Atur ukuran kolom No. */
        .printable-card table th:first-child,
        .printable-card table td:first-child {
            width: 40px !important;
            max-width: 40px !important;
        }

        /* Hilangkan elemen di atas kartu saat print */
        button,
        .btn,
        .hide-before-print {
            display: none !important;
        }

        /* Buang padding/margin container bawaan AdminLTE */
        .content-wrapper,
        .content,
        .container-fluid {
            padding-top: 0 !important;
            margin-top: 5mm !important;
        }

        body {
            margin: 0 !important;
        }

        /* Jangan biarkan grafik / blok penting kepotong di tengah halaman */
        .no-page-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }

    @page {
        size: A4 portrait;
        margin: 15mm;
    }
</style>

<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="hide-before-print">
            <button type="button"
                class="btn btn-secondary btn-sm w-100"
                onclick="window.print()">
                <i class="fa fa-print"></i> Print
            </button>
            <br><br>
        </div>
        <div class="card card-primary printable-card">
            <div class="card-header">
                <h3 class="card-title"><strong>Talent Profile</strong></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4 border-right">
                        <div class="text-center">
                            <?php if ($employee['NRP'] == '10122195') : ?>
                                <img src="<?= base_url() ?>drive/employee_image/AULIANSYAH%20AFRIANTHONI" onerror="this.onerror=null; this.src='<?= base_url('drive/employee_image/default.jpg') ?>';" style="width:100%; height:auto; object-fit:cover;">
                            <?php else: ?>
                                <img src="<?= base_url() ?>drive/employee_image/<?= $employee['NRP'] ?>" onerror="this.onerror=null; this.src='<?= base_url('drive/employee_image/default.jpg') ?>';" style="width:100%; height:auto; object-fit:cover;">
                            <?php endif; ?>
                            <h3 style="color: rgb(44,44,44); font-family: Calibri;" class="m-0"><strong><?= $employee['FullName'] ?></strong></h3>
                            <h4 style="color: rgb(100,100,100); font-family: Calibri;" class="m-0"><?= $employee['NRP'] ?></h4>
                            <h4 style="color: rgb(100,100,100); font-family: Calibri;" class="m-0"><?= $employee['age'] ?> Tahun</h4>
                            <h4 style="color: rgb(100,100,100); font-family: Calibri;" class="m-0"><?= $employee['education'] ?>, <?= $employee['branch_of_study'] ?>, <?= $employee['institution'] ?></h4>
                            <hr>
                            <strong>Current Position</strong><br>
                            <span style="color: rgb(100,100,100); font-family: Calibri;"><?= $employee['oa_name'] ?>, <?= $employee['oal_name'] ?>, <?= $employee['matrix_point_name'] ?>, <?= $employee['oalp_name'] ?></span>
                            <hr>
                            <strong>Targeted Position</strong><br>
                            <span style="color: rgb(100,100,100); font-family: Calibri;"><?= $target_position['oa_name'] ?>, <?= $target_position['oal_name'] ?>, <?= $target_position['mp_name'] ?>, <?= $target_position['name'] ?></span>
                            <hr>
                            <strong>Talent Cluster</strong><br>
                            <span style="color: rgb(100,100,100); font-family: Calibri;"><?= $candidate['status'] ?? 'No Data' ?></span>
                            <hr>
                            <strong>Talent Committee Score</strong><br>
                            <span style="color: rgb(100,100,100); font-family: Calibri;"><?= $candidate['total_score'] ?? 'No Data' ?></span>
                            <hr>
                            <strong>Talent Readiness</strong><br>
                            <?php if (!$rtc) : ?>
                                <span style="color: rgb(100,100,100); font-family: Calibri;">No Data</span>
                            <?php else: ?>
                                <span style="color: rgb(100,100,100); font-family: Calibri;">R<?= $rtc['year'] - date('Y') ?></span>
                            <?php endif; ?>
                            <hr>
                            <strong>Assessment Method</strong><br>
                            <span style="color: rgb(100,100,100); font-family: Calibri;"><?= $method ?></span>
                            <?php if ($correlation_matrix) : ?>
                                <hr>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 60%;">Matrix Points</th>
                                            <th style="width: 40%;">Correlation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($correlation_matrix as $i_cm => $cm_i) : ?>
                                            <tr>
                                                <td><?= $cm_i['oalp_name'] ?></td>
                                                <td><?= $cm_i['correlation'] ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="row">
                            <div class="col-sm-6">
                                <h3 style="color: rgb(100, 100, 100)"><strong><i class="nav-icon fas fa-briefcase"></i> Career History</strong></h3>
                                <hr>
                                <?php if (!$tour_of_duties) : ?>
                                    No Data
                                    <br>
                                    <br>
                                <?php else: ?>
                                    <ul class="js-limit-list" data-max="3">
                                        <?php foreach ($tour_of_duties as $i_tod => $tod_i) : ?>
                                            <li>
                                                <strong><?= date("Y F", strtotime($tod_i['date'])) ?> - <?= $tod_i['position'] ?></strong><br>
                                                <ul>
                                                    <?php foreach ($tod_i['matrix_points'] as $i_mp => $mp_i) : ?>
                                                        <li><?= $mp_i['name'] ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <h3 style="color: rgb(100, 100, 100)"><strong><i class="nav-icon fas fa-certificate"></i> Certification</strong></h3>
                                <hr>
                                <?php $mts_cert = array_filter($mts, fn($mts_i) => $mts_i['is_certification'] == 'Y') ?>
                                <?php if (!$mts_cert) : ?>
                                    No Data
                                <?php else: ?>
                                    <ul class="js-limit-list" data-max="3">
                                        <?php foreach ($mts_cert as $i_mts => $mts_i) : ?>
                                            <li><strong><?= $mts_i['year'] ?></strong> - <?= $mts_i['nama_program'] ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-6">
                                <h3 style="color: rgb(100, 100, 100)"><strong><i class="nav-icon fas fa-chalkboard-teacher"></i> Training History</strong></h3>
                                <hr>
                                <?php $mts_train = array_filter($mts, fn($mts_i) => $mts_i['is_certification'] != 'Y') ?>
                                <?php if (!$mts_train) : ?>
                                    No Data
                                    <br>
                                    <br>
                                <?php else: ?>
                                    <ul class="js-limit-list" data-max="3">
                                        <?php foreach ($mts_train as $i_mts => $mts_i) : ?>
                                            <li><strong><?= $mts_i['year'] ?></strong> - <?= $mts_i['nama_program'] ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <h3 style="color: rgb(100, 100, 100)"><strong><i class="nav-icon fas fa-tasks"></i> Training Plan</strong></h3>
                                <hr>
                                <?php if (!$atmp) : ?>
                                    No Data
                                <?php else: ?>
                                    <?php if (!$atmp) : ?>
                                        No Data
                                    <?php else: ?>
                                        <ul class="js-limit-list" data-max="3">
                                            <?php foreach ($atmp as $i_atmp => $atmp_i) : ?>
                                                <li><?= $atmp_i['year'] ?> - <?= $atmp_i['nama_program'] ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <br>
                        <hr>
                        <div class="row">
                            <div class="col-sm-6 no-page-break text-center border-right">
                                <strong>IPA SCORE</strong>
                                <div class="position-relative" style="width: 100%;">
                                    <canvas
                                        id="ipaBar_<?= preg_replace('/\W+/', '_', $employee['NRP']) ?>"
                                        height="400"
                                        aria-label="IPA Score (0-100)"></canvas>
                                </div>
                                <div id="legendIPA_<?= preg_replace('/\W+/', '_', $employee['NRP']) ?>" class="mt-2 text-center"></div>
                            </div>
                            <div class="col-sm-6 no-page-break text-center">
                                <strong>HAV STATUS</strong>
                                <div class="position-relative" style="width: 100%;">
                                    <canvas
                                        id="HAVLine_<?= preg_replace('/\W+/', '_', $employee['NRP']) ?>"
                                        height="400"
                                        aria-label="HAV Status (0-5)"></canvas>
                                </div>
                                <div id="legendHAV_<?= preg_replace('/\W+/', '_', $employee['NRP']) ?>" class="mt-2 text-center"></div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-6 no-page-break text-center border-right">
                                <strong>HARD SKILL COMPETENCIES<br>(Current Job)</strong>
                                <canvas id="spiderChartHard" height="300"></canvas>
                                <div class="metric-box">
                                    Score & Target Range: 0 - 5
                                </div>
                                <div class="achievement-box">
                                    Achievement: <?= $percentHard ?>%
                                </div>
                            </div>
                            <div class="col-sm-6 no-page-break text-center">
                                <strong>MANAGERIAL & LEADERSHIP COMPETENCIES<br>(Job Target)</strong>
                                <canvas id="spiderChart" height="300"></canvas>
                                <div class="metric-box">
                                    Score & Target Range: 0 - 5
                                </div>
                                <div class="achievement-box">
                                    Achievement: <?= $percentSoft ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <h1 style="color: rgb(100, 100, 100)"><strong>Talent Insight</strong></h1>
                <hr>
                <p style="color: rgb(100,100,100); font-family: Arial, Helvetica, sans-serif;" class="text-justify">
                    <?= $comp_lvl_assess['talent_insight'] ?? 'No Data' ?>
                </p>
                <br>
                <h1 style="color: rgb(100, 100, 100)"><strong>Assessment Insight</strong></h1>
                <hr>
                <p style="color: rgb(100,100,100); font-family: Arial, Helvetica, sans-serif;" class="text-justify">
                    <?php if ((isset($comp_lvl_assess['assessment_insight_strength']) && $comp_lvl_assess['assessment_insight_strength']) || (isset($comp_lvl_assess['assessment_insight_development']) && $comp_lvl_assess['assessment_insight_development'])) : ?>
                        <?php if ($comp_lvl_assess['assessment_insight_strength']) : ?>
                            <?= $comp_lvl_assess['assessment_insight_strength'] ?>
                        <?php endif; ?>
                        <?= $comp_lvl_assess['assessment_insight_strength'] && $comp_lvl_assess['assessment_insight_development'] ? '<br><br>' : '' ?>
                        <?= $comp_lvl_assess['assessment_insight_development'] ?>
                    <?php else: ?>
                        No Data
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</section>

<script>
    function renderLegend(targetEl, items) {
        if (!targetEl) return;
        targetEl.innerHTML = items.map(it => `
        <span style="
            display:inline-flex;align-items:center;gap:.5rem;
            padding:.25rem .5rem;margin:.125rem .5rem .125rem 0;
            border:1px solid #e5e7eb;border-radius:999px;font-size:.875rem;">
            <span style="width:12px;height:12px;border-radius:3px;background:${it.color};
                display:inline-block;border:1px solid rgba(0,0,0,.15)">
            </span>
        ${it.label}
        </span>
    `).join('');
    }
</script>

<?php
$now   = (int)date('Y');
$years = range($now - 2, $now);
?>

<?php
$ipa_scores = $ipa_scores ?? [];

// Index by year
$ipaByYear = [];
foreach ($ipa_scores as $row) {
    $y = (int)($row['tahun'] ?? 0);
    $v = is_numeric($row['score'] ?? null) ? (int)$row['score'] : null;
    if ($y) $ipaByYear[$y] = $v;
}

// Build labels & values mengikuti $years (supaya sejajar dengan HAV chart)
// gunakan null jika kosong agar bar-nya tidak muncul (bisa ganti 0 kalau mau ditampilkan nol)
$ipa_labels = [];
$ipa_values = [];
foreach ($years as $y) {
    $ipa_labels[] = (string)$y;
    $val = $ipaByYear[$y] ?? null;
    if ($val !== null) {
        if ($val < 0) $val = 0;
        if ($val > 110) $val = 110;
    }
    $ipa_values[] = $val;
}
?>

<script>
    (function() {
        const labelsIPA = <?= json_encode($ipa_labels, JSON_UNESCAPED_UNICODE) ?>;
        const dataIPA = <?= json_encode($ipa_values, JSON_UNESCAPED_UNICODE) ?>;
        const cidIPA = "ipaBar_<?= preg_replace('/\W+/', '_', $employee['NRP']) ?>";
        const elIPA = document.getElementById(cidIPA);
        if (!elIPA) return;

        const ctxIPA = elIPA.getContext('2d');

        // Warna band berdasarkan skor
        function bandColor(val) {
            if (val == null) return 'rgb(108,117,125)'; // muted/no data
            if (val > 90) return 'rgb(40,167,69)'; // success
            if (val > 80) return 'rgb(13,110,253)'; // primary
            if (val > 70) return 'rgb(23,162,184)'; // info
            if (val > 60) return 'rgb(255,193,7)'; // warning
            return 'rgb(220,53,69)'; // danger
        }

        function bandBorder(val) {
            if (val == null) return 'rgba(108,117,125,0.35)';
            if (val > 90) return '#28a745';
            if (val > 80) return '#0d6efd';
            if (val > 70) return '#17a2b8';
            if (val > 60) return '#ffc107';
            return '#dc3545';
        }

        const ipaChart = new Chart(ctxIPA, {
            type: 'bar',
            data: {
                labels: labelsIPA,
                datasets: [{
                    label: '',
                    data: dataIPA,
                    backgroundColor: dataIPA.map(bandColor),
                    borderColor: dataIPA.map(bandBorder),
                    borderWidth: 2,
                    borderRadius: 6,
                    barThickness: 'flex'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,

                legend: {
                    display: false
                },

                // ✅ MATIKAN SEMUA EVENT (hover gak akan trigger redraw)
                events: [],

                tooltips: {
                    enabled: false
                },
                hover: {
                    mode: null
                },

                // (optional) biar gak animasi saat load
                animation: {
                    duration: 0
                },

                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            min: 0,
                            max: 100,
                            stepSize: 10,
                            fontColor: '#374151'
                        },
                        gridLines: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            fontColor: '#374151'
                        },
                        gridLines: {
                            display: false
                        }
                    }]
                }
            },

            // ✅ Label digambar SETIAP chart render, jadi gak hilang saat redraw
            plugins: [{
                afterDatasetsDraw: function(chart) {
                    const ctx = chart.ctx;
                    const dataset = chart.data.datasets[0];
                    const meta = chart.getDatasetMeta(0);
                    const chartAreaTop = chart.chartArea.top;

                    ctx.save();
                    ctx.font = 'bold 16px Calibri, Arial, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';

                    dataset.data.forEach(function(value, index) {
                        if (value == null) return;

                        const bar = meta.data[index];
                        const x = bar._model.x;

                        // default: di atas bar
                        let y = bar._model.y - 4;

                        // ✅ anti kepotong saat value mentok atas (mis. 100)
                        y = Math.max(y, chartAreaTop + 18);

                        ctx.lineWidth = 3;
                        ctx.strokeStyle = 'rgba(255,255,255,0.9)';
                        ctx.strokeText(value, x, y);

                        ctx.fillStyle = '#111';
                        ctx.fillText(value, x, y);
                    });

                    ctx.restore();
                }
            }]
        });

        // Legend kustom rentang nilai
        renderLegend(
            document.getElementById("legendIPA_<?= preg_replace('/\W+/', '_', $employee['NRP']) ?>"),
            [{
                    label: '> 90',
                    color: 'rgb(40,167,69)'
                },
                {
                    label: '81–90',
                    color: 'rgb(13,110,253)'
                },
                {
                    label: '71– 80',
                    color: 'rgb(23,162,184)'
                },
                {
                    label: '61–70',
                    color: 'rgb(255,193,7)'
                },
                {
                    label: '≤ 60',
                    color: 'rgb(220,53,69)'
                },
                {
                    label: 'No data',
                    color: 'rgb(108,117,125)'
                }
            ]
        );
    })();
</script>

<?php
$HAV_statuses = $HAV_statuses ?? [];

$hav_status_score = array(
    'Top Talent'        => 5,
    'Promotable'        => 4,
    'Prostar 1'         => 4,
    'Prostar 2'         => 4,
    'Sleeping Tiger'    => 3,
    'Solid Contributor' => 2,
    'Unfit'             => 1,
);

// Pakai sumber yang pasti ada
$hav_map = $hav_map ?? $HAV_statuses ?? [];

// Index by year -> score
$byYear = [];
foreach ($hav_map as $row) {
    $y = (int)($row['year'] ?? 0);
    $v = $row['status'] ?? null;
    if ($v && isset($hav_status_score[$v])) {
        $v = $hav_status_score[$v];
    } else {
        $v = 0;
    }
    if ($y) $byYear[$y] = $v;
}

// Biar sejajar dengan IPA: ambil 3 tahun terakhir
$now   = (int)date('Y');
$years = range($now - 2, $now);

$hav_labels = [];
$hav_values = [];
foreach ($years as $y) {
    $hav_labels[] = (string)$y;
    $hav_values[] = $byYear[$y] ?? 0; // 0 = No Data
}
?>

<script>
    (function() {
        // Data dari PHP khusus HAV
        const labelsHAV = <?= json_encode($hav_labels, JSON_UNESCAPED_UNICODE) ?>;
        const dataHAV = <?= json_encode($hav_values, JSON_UNESCAPED_UNICODE) ?>;

        // Mapping HAV status -> skor (buat legend & tooltip)
        const havStatusScore = <?= json_encode($hav_status_score, JSON_UNESCAPED_UNICODE) ?>;

        const cid = "HAVLine_<?= preg_replace('/\W+/', '_', $employee['NRP']) ?>";
        const el = document.getElementById(cid);
        if (!el) return;

        const ctx = el.getContext('2d');

        const colorMap = {
            0: '#6c757d', // No Data
            1: '#dc3545', // Unfit
            2: '#ffc107', // Solid Contributor
            3: '#17a2b8', // Sleeping Tiger
            4: '#0d6efd', // Promotable / Prostar
            5: '#28a745' // Top Talent
        };

        const pointColors = dataHAV.map(v => colorMap[v] || '#6c757d');

        // Reverse map: skor -> [label...]
        const statusReverse = {};
        Object.keys(havStatusScore).forEach(function(label) {
            const score = havStatusScore[label];
            if (!statusReverse[score]) statusReverse[score] = [];
            statusReverse[score].push(label);
        });

        function getStatusLabelFromScore(score) {
            if (score === 0) return 'No Data';
            if (statusReverse[score]) {
                return statusReverse[score].join(' / ');
            }
            return score;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labelsHAV,
                datasets: [{
                    label: 'HAV Status',
                    data: dataHAV,
                    tension: 0,
                    borderWidth: 0,
                    borderColor: '#6c757d',
                    pointRadius: 7,
                    pointHoverRadius: 7,
                    pointBackgroundColor: pointColors,
                    fill: false,
                    pointStyle: 'circle'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, chartData) {
                            const yearLabel = chartData.labels[tooltipItem.index];
                            const score = tooltipItem.yLabel;
                            const statusLbl = getStatusLabelFromScore(score);

                            if (score === 0) {
                                return 'Year ' + yearLabel + ': No Data (0)';
                            }

                            return 'Year ' + yearLabel + ': ' + statusLbl + ' (' + score + ')';
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            min: 0,
                            max: 5,
                            stepSize: 1,
                            callback: function(value) {
                                return getStatusLabelFromScore(value);
                            },
                            fontColor: '#374151',
                            fontSize: 12
                        },
                        gridLines: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            fontColor: '#374151',
                            fontSize: 12
                        },
                        gridLines: {
                            display: false
                        }
                    }]
                }
            }
        });

        // Build legend
        const legendItems = [];
        Object.keys(statusReverse)
            .map(s => parseInt(s, 10))
            .sort((a, b) => b - a) // 5 -> 1
            .forEach(function(score) {
                if (score === 0) return;
                const labels = statusReverse[score];
                if (!labels || !labels.length) return;
                legendItems.push({
                    label: labels.join(' / '),
                    color: colorMap[score] || '#6c757d'
                });
            });

        legendItems.push({
            label: 'No Data',
            color: colorMap[0] || '#6c757d'
        });

        renderLegend(
            document.getElementById("legendHAV_<?= preg_replace('/\W+/', '_', $employee['NRP']) ?>"),
            legendItems
        );
    })();
</script>

<?php
$targetsByPstnId = [];
$namesByPstnId   = [];

foreach ($comp_pstn_targets as $row) {
    $target = (float)$row['target'];
    if ($target > 0) { // hanya yang punya target
        $compId = $row['comp_pstn_id'];
        $targetsByPstnId[$compId] = $target;
        $namesByPstnId[$compId]   = $row['name'];  // simpan nama kompetensi
    }
}

// Indexing score berdasarkan comp_pstn_id
$scoresByPstnId = [];
foreach ($comp_pstn_scores as $row) {
    $compId = $row['comp_pstn_id'];
    $scoresByPstnId[$compId] = (float)$row['score'];
}

// Generate labels, targets, scores (mengikuti urutan array target)
$labels  = [];
$targets = [];
$scores  = [];

foreach ($targetsByPstnId as $compId => $targetVal) {
    $labels[]  = $namesByPstnId[$compId];
    $targets[] = $targetVal;
    $scores[]  = isset($scoresByPstnId[$compId]) ? $scoresByPstnId[$compId] : 0;
}
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        var labels = <?= json_encode($labels) ?>;
        var targets = <?= json_encode($targets) ?>;
        var scores = <?= json_encode($scores) ?>;

        var ctxHard = document.getElementById('spiderChartHard').getContext('2d');

        new Chart(ctxHard, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Score',
                        data: scores,
                        backgroundColor: 'rgba(255, 99, 132, 0.3)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Target',
                        data: targets,
                        backgroundColor: 'rgba(60,141,188,0.3)',
                        borderColor: 'rgba(60,141,188,1)',
                        pointBackgroundColor: 'rgba(60,141,188,1)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                scale: {
                    ticks: {
                        beginAtZero: true,
                        min: 0,
                        max: 5, // mau disesuaikan ke 3 juga bisa, tinggal ganti
                        stepSize: 1,
                        fontSize: 16
                    },
                    pointLabels: {
                        fontSize: 10
                    }
                },
                legend: {
                    display: true
                }
            }
        });

    });
</script>

<?php
// Indexing target & score berdasarkan comp_lvl_id
$targetsById = [];
foreach ($comp_lvl_targets as $row) {
    // Hanya yang target > 0 (tampilkan kompetensi ini saja)
    if ((float)$row['target'] > 0) {
        $targetsById[$row['comp_lvl_id']] = (float)$row['target'];
    }
}

$scoresById = [];
foreach ($comp_lvl_scores as $row) {
    $scoresById[$row['comp_lvl_id']] = (float)$row['clas_score'];
}

// Generate labels, targets, scores (hanya untuk yang ada target)
$labels  = [];
$targets = [];
$scores  = [];

foreach ($comp_lvl as $comp) {
    $id = $comp['id'];

    if (isset($targetsById[$id])) {
        // Tampilkan yang punya target saja
        $labels[]  = $comp['name'];
        $targets[] = $targetsById[$id];
        $scores[]  = isset($scoresById[$id]) ? $scoresById[$id] : 0;
    }
}
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        var labels = <?= json_encode($labels) ?>;
        var targets = <?= json_encode($targets) ?>;
        var scores = <?= json_encode($scores) ?>;

        var ctx = document.getElementById('spiderChart').getContext('2d');

        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Score',
                        data: scores,
                        backgroundColor: 'rgba(255, 99, 132, 0.3)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Target',
                        data: targets,
                        backgroundColor: 'rgba(60,141,188,0.3)',
                        borderColor: 'rgba(60,141,188,1)',
                        pointBackgroundColor: 'rgba(60,141,188,1)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                scale: {
                    ticks: {
                        beginAtZero: true,
                        min: 0,
                        max: 5,
                        stepSize: 1,
                        fontSize: 16
                    },
                    pointLabels: {
                        fontSize: 10
                    }
                },
                legend: {
                    display: true
                }
            }
        });

    });
</script>

<script>
    $(function() {
        $('.js-limit-list').each(function() {

            var $list = $(this);
            var maxItems = parseInt($list.data('max'), 10) || 3;
            var $items = $list.children('li');

            // Kalau item <= max, gak perlu tombol
            if ($items.length <= maxItems) return;

            // Ambil item ke-4 dst
            var $extraItems = $items.slice(maxItems);

            // Kondisi awal: disembunyikan
            $extraItems.hide();

            // Tombol polos di tengah
            var $btn = $(`
                <button type="button" class="show-more-btn">
                    Show More <span>▾</span>
                </button>
            `);

            $btn.insertAfter($list);

            var expanded = false;

            $btn.on('click', function() {
                if (!expanded) {
                    // Tampilkan dengan fade (tidak mengecil/membesar, cuma muncul)
                    $extraItems.stop(true, true).fadeIn(200);
                    $btn.html('Show Less <span>▴</span>');
                    expanded = true;
                } else {
                    // Sembunyikan dengan fade
                    $extraItems.stop(true, true).fadeOut(200);
                    $btn.html('Show More <span>▾</span>');
                    expanded = false;
                }
            });
        });
    });
</script>