<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Choose Table</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">

                <div class="text-center mb-3">
                    <button id="btnDownloadDb" class="btn btn-success btn-sm w-100">
                        <i class="fa fa-download"></i> Download DB (.sql)
                    </button>
                </div>

                <!-- Hidden iframe -->
                <iframe id="downloadFrame" style="display:none;"></iframe>

                <table id="" class="table table-bordered table-striped datatable-filter-column" data-filter-columns="4:multiple,5,6">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>No</th>
                            <th>Matrix Point</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $i_t => $t_i) : ?>
                            <tr>
                                <td><a href="<?= base_url() ?>admin/database/table/<?= $i_t ?>" class="label label-primary btn btn-primary btn-xs w-100"><span><i class="fa fa-list"></i></span></a></td>
                                <td><?= $i_t ?></td>
                                <td><?= $t_i ?></td>
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

<script>
    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });
    });
</script>

<script>
    (function() {
        function getCookie(name) {
            const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
            return m ? decodeURIComponent(m[1]) : null;
        }

        $('#btnDownloadDb').on('click', function(e) {
            e.preventDefault();

            // Tampilkan overlay manual (download tidak memicu page unload)
            if (typeof showOverlayFull === 'function') {
                showOverlayFull();
            }

            // Token unik untuk siklus ini
            const token = String(Date.now());

            // Bersihkan cookie lama
            document.cookie = 'downloadToken=; Max-Age=0; path=/';

            // Mulai polling cookie
            let tries = 0;
            const maxTries = 240; // 2 menit @ 500ms
            const iv = setInterval(function() {
                tries++;
                if (getCookie('downloadToken') === token) {
                    clearInterval(iv);
                    // Bersihkan cookie & hide overlay
                    document.cookie = 'downloadToken=; Max-Age=0; path=/';
                    if (typeof hideOverlayFull === 'function') {
                        hideOverlayFull();
                    }
                } else if (tries >= maxTries) {
                    clearInterval(iv);
                    if (typeof hideOverlayFull === 'function') {
                        hideOverlayFull();
                    }
                    // Swal.fire('Info','Tidak bisa memastikan status download. Overlay ditutup.','info');
                }
            }, 500);

            // Trigger download via hidden iframe
            const url = "<?= base_url('admin/database/download') ?>" + "?t=" + encodeURIComponent(token);
            document.getElementById('downloadFrame').src = url;
        });
    })();
</script>