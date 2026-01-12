<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPIHCLA | Riung Mitra Lestari</title>
    <?php $this->load->view('templates/styles.php'); ?>
    <style>
        /* Overlay full screen */
        .overlay-loading {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        }

        /* Hidden using bootstrap d-none */
        .overlay-loading.d-none {
            display: none !important;
        }

        .overlay-loading-inner {
            width: 780px;
            /* LEBIH BESAR */
            max-width: 95%;
            background: #0f172a;
            color: #e2e8f0;
            padding: 35px 45px;
            border-radius: 1rem;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.55);
            text-align: left;
        }

        /* Title text */
        .overlay-loading-text {
            font-size: 1.15rem;
            /* lebih besar */
            margin-bottom: 15px;
            font-weight: 500;
        }

        /* Terminal box — BESAR BANGET */
        .overlay-terminal {
            background: #020617;
            color: #cbd5e1;
            border-radius: 0.75rem;
            padding: 20px 24px;
            max-height: 420px;
            /* TINGGI BESAR */
            min-height: 300px;
            /* MINIMAL TETAP BESAR */
            overflow-y: auto;
            font-family: "Source Code Pro", monospace;
            font-size: 0.95rem;
            /* lebih besar */
            margin-bottom: 25px;
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.25);
            white-space: pre-wrap;
            line-height: 1.45rem;
        }

        /* Progress bar */
        .progress {
            height: 12px !important;
            /* lebih besar */
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .progress-bar {
            transition: width 0.1s linear;
        }
    </style>

    <style>
        /* =========================================================
   DataTables Excel-like Enhancements Styles
   - Filter popup + draggable state
   - Overlay helper
   - Cell selection (Excel-like)
   - Contenteditable UX
   ========================================================= */

        /* Prevent text selection when dragging popup */
        body.unselectable,
        body.unselectable * {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
        }

        /* Overlay (JS already injects inline styles; these are fallback/consistency) */
        .dataTables_wrapper {
            position: relative;
        }

        .dataTables_wrapper .dt-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.75);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        /* Filter button row */
        table.dataTable thead .filter-btn {
            border-radius: 0;
        }

        table.dataTable thead .filter-btn.btn-warning {
            font-weight: 700;
        }

        /* Excel filter popup */
        .excel-filter-popup {
            border-radius: 10px;
            overflow: hidden;
        }

        .excel-filter-popup .popup-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            user-select: none;
            /* biar enak di-drag */
            -webkit-user-select: none;
        }

        .excel-filter-popup .popup-header .close {
            font-size: 22px;
            line-height: 1;
            background: transparent;
            border: 0;
            opacity: 0.6;
        }

        .excel-filter-popup .popup-header .close:hover {
            opacity: 1;
        }

        .excel-filter-popup .options label {
            cursor: pointer;
        }

        .excel-filter-popup .options input[type="checkbox"] {
            margin-right: 6px;
        }

        /* Contenteditable cells UX */
        table.dataTable td[contenteditable="true"] {
            cursor: text;
            outline: none;
        }

        table.dataTable td[contenteditable="true"]:focus {
            /* focus ring */
            box-shadow: inset 0 0 0 2px rgba(13, 81, 180, 0.65);
        }

        /* Excel-like multi-cell selection highlight */
        table.dataTable td.dt-cell-selected {
            outline: 2px solid rgba(13, 81, 180, 0.45);
            outline-offset: -2px;
            background: rgba(13, 81, 180, 0.07);
        }

        table.dataTable td.dt-cell-anchor {
            outline: 2px solid rgba(13, 81, 180, 0.85);
            outline-offset: -2px;
        }

        /* Make selected text inside selected cells not messy */
        table.dataTable td.dt-cell-selected ::selection {
            background: rgba(13, 81, 180, 0.20);
        }

        /* Optional: nicer spacing for DataTables buttons area */
        .dataTables_wrapper .dt-buttons .btn {
            margin-right: 4px;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <?php $this->load->view('templates/scripts.php'); ?>

    <?php if ($this->session->flashdata('swal')): ?>
        <?php
        $swal = $this->session->flashdata('swal');
        $allowed = ['success', 'error', 'warning', 'info', 'question'];
        $icon = in_array($swal['type'] ?? '', $allowed, true) ? $swal['type'] : 'info';

        $payload = [
            'toast' => true,
            'position' => 'top-end',
            'icon' => $icon,
            'title' => (string)($swal['message'] ?? ''), // pakai title/text, bukan html
            'showConfirmButton' => false,
            'timer' => 3000
        ];
        ?>
        <script>
            window.addEventListener('load', function() {
                $('.preloader').fadeOut();
                Swal.fire(<?= json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
            });
        </script>
    <?php endif; ?>

    <div class="wrapper">
        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?= base_url() ?>assets/AdminLTE-3.2.0/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url() ?>auth/logout" role="button">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
                        <i class="fas fa-th-large"></i>
                    </a>
                </li> -->
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <!-- <a href="index3.html" class="brand-link">
                <img src="<?= base_url() ?>assets/AdminLTE-3.2.0/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">KPIHCLA RML</span>
            </a> -->
            <a href="#" class="brand-link">
                <div class="text-center">
                    <span class="brand-text font-weight-bold" style="
                        font-family: 'Poppins', sans-serif;
                        font-size: 1.1rem;
                        color: white;
                        text-shadow:
                            -1px -1px 0 #000,
                            1px -1px 0 #000,
                            -1px  1px 0 #000,
                            1px  1px 0 #000;">
                        KPI<span style="
                            color: #00bcd4;
                            text-shadow:
                                -1px -1px 0 #000,
                                1px -1px 0 #000,
                                -1px  1px 0 #000,
                                1px  1px 0 #000;">HCLA</span> RML
                    </span>
                </div>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <!-- <div class="image">
                        <img src="<?= base_url() ?>assets/AdminLTE-3.2.0/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                    </div> -->
                    <div class="info">
                        <a href="#" class="d-block"><?= $this->session->userdata('full_name') ?></a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <?php $roles = $this->m_menu->get_menu($this->session->userdata('NRP')); ?>
                        <?php foreach ($roles as $role) : ?>
                            <?php if ($role['description'] && $role['name'] != 'general') : ?>
                                <li class="nav-header text-center"><strong><?= $role['description'] ?></strong></li>
                            <?php endif; ?>
                            <?php foreach ($role['menus'] as $menu) : ?>
                                <?php if (!count($menu['children'])) : ?>
                                    <li class="nav-item">
                                        <a href="<?= base_url() . $menu['url'] ?>" class="nav-link show-overlay-full pl-0 <?= $this->uri->segment(1) == $menu['url'] || $this->uri->segment(1) . '/' . $this->uri->segment(2) == $menu['url'] ? 'active' : '' ?>">
                                            <i class="nav-icon fas fa-<?= $menu['icon'] ?>"></i>
                                            <p><?= $menu['name'] ?></p>
                                        </a>
                                    </li>
                                <?php else : ?>
                                    <li class="nav-item <?= $this->uri->segment(1) == $menu['url'] ? 'menu-open' : '' ?>">
                                        <a href="<?= base_url() . $menu['url'] ?>" class="nav-link pl-0 <?= $this->uri->segment(1) == $menu['url'] ? 'active' : '' ?>">
                                            <i class="nav-icon fas fa-<?= $menu['icon'] ?>"></i>
                                            <p>
                                                <?= $menu['name'] ?>
                                                <i class="right fas fa-angle-left"></i>
                                            </p>
                                        </a>
                                        <ul class="nav nav-treeview">
                                            <?php foreach ($menu['children'] as $child) : ?>
                                                <li class="nav-item">
                                                    <a href="<?= base_url() . $child['url'] ?>" class="nav-link show-overlay-full <?= $this->uri->segment(1) . '/' . $this->uri->segment(2) == $child['url'] ? 'active' : '' ?>">
                                                        <i class="far fa-circle nav-icon"></i>
                                                        <p><?= $child['name'] ?></p>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </ul>
                    <br><br><br><br><br>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <?php $this->load->view($content) ?>
        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer">
            <strong>Copyright &copy; 2025-<?= date('Y') ?> <a href="#">HCLA PT. Riung Mitra Lestari</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0.0
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
    </div>
    <!-- ./wrapper -->

    <!-- Overlay Loading -->
    <div id="overlay-loading" class="overlay-loading d-none">
        <div class="overlay-loading-inner">

            <div class="overlay-loading-text">
                Loading KPIHCLA System... Please wait
            </div>

            <pre id="overlay-terminal" class="overlay-terminal">
[BOOT] Starting KPIHCLA Loader v2.1
        </pre>

            <div class="progress">
                <div id="overlay-progress-bar" class="progress-bar bg-info" role="progressbar"></div>
            </div>
        </div>
    </div>

</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var params = new URLSearchParams(window.location.search);

        if (params.get('overlay') === 'loading') {
            var duration = parseInt(params.get('duration'), 10);
            if (isNaN(duration) || duration <= 0) duration = 2000;

            var $overlay = $('#overlay-loading');
            var $bar = $('#overlay-progress-bar');
            var $terminal = $('#overlay-terminal');

            var steps = [
                "[INFO] Initializing core modules...",
                "[INFO] Checking user authentication...",
                "[INFO] Session verified successfully.",
                "[INFO] Loading Human Capital parameters...",
                "[INFO] Fetching hierarchical access scheme...",
                "[INFO] Reading KPI Master Structure...",
                "[INFO] Syncing role permissions...",
                "[INFO] Loading staff database...",
                "[INFO] Calculating performance indicators...",
                "[INFO] Merging KPI with daily operational data...",
                "[INFO] Rendering KPI dashboard components...",
                "[INFO] Applying RML design system...",
                "[INFO] Checking unresolved tasks...",
                "[INFO] Finalizing UI & preparing view...",
                "[INFO] Loading job family competency maps...",
                "[INFO] Syncing employee movement records...",
                "[INFO] Preparing manpower analytics module...",
                "[INFO] Validating organizational structure...",
                "[INFO] Verifying matrix points configuration...",
                "[INFO] Integrating training database...",
                "[INFO] Resolving department hierarchy...",
                "[INFO] Fetching site-level KPI references...",
                "[INFO] Performing data normalization...",
                "[INFO] Mapping KPI relationships...",
                "[INFO] Indexing employee identifiers...",
                "[INFO] Building strategic KPI alignment...",
                "[INFO] Parsing historical KPI snapshots...",
                "[INFO] Checking cross-functional dependencies...",
                "[INFO] Syncing performance review datasets...",
                "[INFO] Updating KPI scoring algorithm...",
                "[INFO] Loading dashboard widgets...",
                "[INFO] Loading plant & maintenance metrics...",
                "[INFO] Running compliance checks...",
                "[INFO] Validating user interface components...",
                "[INFO] Initializing cache memory...",
                "[INFO] Fetching chart rendering assets...",
                "[INFO] Consolidating analytical layers...",
                "[INFO] Loading advanced filtering engine...",
                "[INFO] Compiling page layout structure...",
                "[INFO] Preparing final data bindings...",
                "[INFO] Applying responsive layout rules...",
                "[INFO] Running interface stability tests...",
                "[INFO] Registering system events...",
                "[INFO] Compressing dashboard payload...",
                "[INFO] Applying security hardening...",
                "[INFO] Loading finishing assets...",
                "[INFO] Optimizing rendering pipeline...",
                "[INFO] Preparing environment for display...",
                "[DONE] KPIHCLA Dashboard loaded successfully!"
            ];

            var lastStepIndex = -1;
            var start = Date.now();

            // Interval log terminal
            var stepIntervalMs = Math.min(duration / steps.length, Math.random() * 2000);

            function updateTerminal(stepIndex) {
                // Jika sudah mencapai step terakhir → lanjut step berikutnya pakai stepIndex = 0, 1, 2, dst
                if (stepIndex <= lastStepIndex && !(lastStepIndex === steps.length - 1 && stepIndex === 0)) {
                    return;
                }

                lastStepIndex = stepIndex;

                // ⬇️ ⬇️ TAMBAHKAN LOG BARU, BUKAN MENGGANTI LIST
                var currentText = $terminal.text().trim();
                var newLine = steps[stepIndex];
                var logs = currentText + "\n" + newLine;

                $terminal.text(logs);

                var el = $terminal.get(0);
                el.scrollTop = el.scrollHeight;
            }

            $overlay.removeClass('d-none').hide().fadeIn(200);

            function animateProgress() {
                var now = Date.now();
                var elapsed = now - start;
                var progress = Math.min(1, elapsed / duration);
                var percent = Math.round(progress * 100);

                // Progress bar tetap ngikut duration besar
                $bar.css('width', percent + '%');

                // ✅ STEP BERULANG: begitu mentok terakhir, balik lagi ke 0
                var totalSteps = steps.length;
                var stepByTime = Math.floor(elapsed / stepIntervalMs);
                var stepIndex = stepByTime % steps.length; // looping

                updateTerminal(stepIndex);

                if (progress < 1) {
                    requestAnimationFrame(animateProgress);
                } else {
                    setTimeout(function() {
                        $overlay.fadeOut(300, function() {
                            $bar.css('width', '0%');
                            $overlay.addClass('d-none');
                            $terminal.text('[BOOT] Starting KPIHCLA Loader v2.1');
                            lastStepIndex = -1; // reset buat next overlay
                        });

                        if (window.history.replaceState) {
                            params.delete('overlay');
                            params.delete('duration');
                            var newQuery = params.toString();
                            var newUrl = window.location.pathname +
                                (newQuery ? '?' + newQuery : '') +
                                window.location.hash;
                            window.history.replaceState(null, '', newUrl);
                        }
                    }, 250);
                }
            }

            requestAnimationFrame(animateProgress);
        }
    });
</script>

</html>