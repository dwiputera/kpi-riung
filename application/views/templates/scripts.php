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