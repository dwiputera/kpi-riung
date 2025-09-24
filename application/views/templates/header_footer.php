<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPIHCLA | Riung Mitra Lestari</title>
    <?php $this->load->view('templates/styles.php'); ?>
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
</body>

</html>