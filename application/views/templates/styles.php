<!-- Google Font: Source Sans Pro -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome -->
<link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<!-- Tempusdominus Bootstrap 4 -->
<link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
<!-- iCheck -->
<link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<!-- JQVMap -->
<link rel="stylesheet" href="<?= base_url() ?>assets/AdminLTE-3.2.0/plugins/jqvmap/jqvmap.min.css">
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