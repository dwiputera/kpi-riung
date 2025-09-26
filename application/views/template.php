<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPIHCLA | Riung Mitra Lestari</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="preconnect" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/summernote/summernote-bs4.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

    <style>
        .card.card-maximized {
            overflow-y: auto !important;
        }

        html.maximized-card,
        body.maximized-card {
            overflow: auto !important;
        }

        table th {
            white-space: nowrap;
        }

        /* Layout dasar: biar p bisa shrink & di-scroll */
        .nav-sidebar .nav-link {
            display: flex;
            align-items: center;
        }

        .nav-sidebar .nav-link p {
            flex: 1 1 auto;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: clip !important;
            /* <-- tidak pakai ellipsis */
            scrollbar-width: none;
        }

        .nav-sidebar .nav-link p::-webkit-scrollbar {
            display: none;
        }

        /* Saat hover, izinkan horizontal scroll (tanpa ellipsis) */
        .nav-sidebar .nav-link p.marquee-active {
            text-overflow: clip !important;
            /* pastikan nggak balik ke ... */
            overflow-x: auto;
        }

        /* (Opsional) efek fade di ujung kanan, bukan "..." */
        .nav-sidebar .nav-link p {
            -webkit-mask-image: linear-gradient(to right, #000 85%, transparent);
            mask-image: linear-gradient(to right, #000 85%, transparent);
        }

        .nav-sidebar .nav-link p.marquee-active {
            -webkit-mask-image: none;
            mask-image: none;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <!-- jQuery -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/jquery/jquery.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/jquery-ui/jquery-ui.min.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/select2/js/select2.full.min.js"></script>
    <!-- ChartJS -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/chart.js/Chart.min.js"></script>
    <!-- Sparkline -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/sparklines/sparkline.js"></script>
    <!-- jQuery Knob Chart -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/jquery-knob/jquery.knob.min.js"></script>
    <!-- daterangepicker -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/moment/moment.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/daterangepicker/daterangepicker.js"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    <!-- Summernote -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/summernote/summernote-bs4.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <!-- DataTables  & Plugins -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/jszip/jszip.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/pdfmake/pdfmake.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/pdfmake/vfs_fonts.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
    <!-- bs-custom-file-input -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- AdminLTE App -->
    <script src="<?= base_url() ?>assets/AdminLTE-3.2.0/dist/js/adminlte.min.js"></script>

    <script src="<?= base_url('assets/js/overlay.js') ?>"></script>
    <script src="<?= base_url('assets/js/general-display.js') ?>"></script>

    <script>
        $(document).on('click', '.show-overlay-full', function() {
            showOverlayFull();
        });

        // Hide overlay when user navigates back or forward
        window.addEventListener('pageshow', function() {
            hideOverlayFull(); // or $('.overlay').hide();
        });

        // OR: listen to popstate if you’re using pushState/navigation
        window.addEventListener('popstate', function() {
            hideOverlayFull();
        });

        (function() {
            const SPEED = 60; // px/detik

            $(document).on('mouseenter', '.nav-sidebar .nav-link p', function() {
                const el = this;
                el.classList.add('marquee-active');

                const max = el.scrollWidth - el.clientWidth;
                if (max <= 0) return;

                if (el.dataset._scrolling === '1') return;
                el.dataset._scrolling = '1';

                let raf = null,
                    lastTs = null,
                    pos = 0;

                const step = (ts) => {
                    if (el.dataset._scrolling !== '1') return;
                    if (lastTs == null) lastTs = ts;
                    const dt = (ts - lastTs) / 1000; // detik
                    lastTs = ts;

                    pos += SPEED * dt;
                    if (pos >= max) {
                        pos = max; // pastikan berhenti pas di ujung
                        // kalau mau looping, kasih delay sebentar lalu reset
                        setTimeout(() => {
                            if (el.dataset._scrolling === '1') pos = 0;
                        }, 3000);
                    }

                    el.scrollLeft = pos;
                    raf = requestAnimationFrame(step);
                    el._raf = raf;
                };

                raf = requestAnimationFrame(step);
                el._raf = raf;
            });

            $(document).on('mouseleave', '.nav-sidebar .nav-link p', function() {
                const el = this;
                el.dataset._scrolling = '0';
                if (el._raf) cancelAnimationFrame(el._raf);

                // balik pelan ke awal
                const start = el.scrollLeft;
                const duration = 150;
                const t0 = performance.now();
                const back = (t) => {
                    const p = Math.min(1, (t - t0) / duration);
                    el.scrollLeft = start * (1 - p);
                    if (p < 1) requestAnimationFrame(back);
                    else el.classList.remove('marquee-active'); // pulihkan mask
                };
                requestAnimationFrame(back);
            });
        })();
    </script>

    <script>
        function getJwtExp(token) {
            try {
                const base64Payload = token.split('.')[1]; // ambil bagian payload
                const payload = JSON.parse(atob(base64Payload.replace(/-/g, '+').replace(/_/g, '/')));
                return payload.exp; // ambil field exp
            } catch (e) {
                return null;
            }
        }

        function decodeJwt(token) {
            try {
                const base64Payload = token.split('.')[1];
                const payload = JSON.parse(atob(base64Payload.replace(/-/g, '+').replace(/_/g, '/')));
                return payload;
            } catch (e) {
                return null;
            }
        }

        let lastToken = null;

        function refreshToken() {
            $.ajax({
                url: "<?= base_url('auth/check_token') ?>",
                method: "GET",
                dataType: "json",
                success: function(res) {
                    if (res.status === 'ok' && res.token) {
                        if (lastToken && lastToken !== res.token) {
                            console.log("🎉 Token sudah diperbarui!");
                        } else if (lastToken) {
                            console.log("ℹ️ Token masih sama, belum butuh refresh.");
                        }
                        lastToken = res.token;

                        scheduleTokenRefresh(res.token);
                    } else {
                        window.location.href = "<?= base_url() ?>";
                    }
                }
            });
        }

        function scheduleTokenRefreshDebug(token) {
            console.log("⏱ Debug mode: token refresh akan dicoba tiap 3 detik");

            setInterval(function() {
                console.log("⏳ Cek token sekarang...");
                refreshToken(token);
            }, 3 * 1000); // 3 detik untuk debug
        }

        function scheduleTokenRefresh(token) {
            const exp = getJwtExp(token);
            if (!exp) return;

            const now = Math.floor(Date.now() / 1000);
            const timeLeft = exp - now;

            console.log("Token exp:", exp, "Sekarang:", now, "Sisa:", timeLeft, "detik");
            if (timeLeft <= 300) {
                console.log("⚠️ Token hampir habis, refresh sekarang!");
                refreshToken(token);
            } else {
                const refreshIn = (timeLeft - 120) * 1000;
                console.log("✅ Jadwalkan refresh dalam", refreshIn / 1000, "detik");
                setTimeout(function() {
                    refreshToken(token);
                }, refreshIn);
            }
        }

        const tokenFromSession = "<?= $this->session->userdata('token') ?>";
        if (tokenFromSession) {
            // Pakai salah satu:
            // scheduleTokenRefreshDebug(tokenFromSession); // untuk test cepat
            scheduleTokenRefresh(tokenFromSession); // mode normal
        }
    </script>

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
</body>

</html>