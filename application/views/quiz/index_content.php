<section class="content p-3">
    <div class="container-fluid">
        <div class="row">

            <!-- Card Join by PIN -->
            <div class="col-lg-5">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-door-open mr-1"></i> Join Quiz</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>PIN Quiz</label>
                            <input type="text" id="pin" class="form-control" placeholder="Masukkan PIN (contoh: 123456)" maxlength="6">
                        </div>
                        <button id="btnJoin" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt mr-1"></i> Join
                        </button>
                        <small class="text-muted d-block mt-2">PIN berlaku sampai quiz berakhir.</small>
                    </div>
                </div>
            </div>

            <!-- Card List Quiz Saya -->
            <div class="col-lg-7">
                <div class="card card-outline card-info">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="fas fa-list-ul mr-1"></i> Quiz Saya</h3>
                        <a href="<?= site_url('quiz/host'); ?>" class="btn btn-sm btn-success" id="btnGoHostBlank">
                            <i class="fas fa-plus-circle mr-1"></i> Buat Quiz (Generate PIN)
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:90px">Quiz ID</th>
                                        <th>PIN</th>
                                        <th>Status</th>
                                        <th style="width:160px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($my_quizzes)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada quiz yang kamu buat.</td>
                                        </tr>
                                        <?php else: foreach ($my_quizzes as $q): ?>
                                            <tr>
                                                <td><?= (int)$q['id'] ?></td>
                                                <td><span class="badge badge-success"><?= htmlspecialchars($q['pin']) ?></span></td>
                                                <td>
                                                    <?php if ((int)$q['is_active'] === 1): ?>
                                                        <span class="badge badge-primary">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Selesai</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= site_url('quiz/host/' . $q['id']); ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-sliders-h mr-1"></i> Buka Host
                                                    </a>
                                                    <a href="<?= site_url('quiz/leaderboard/' . $q['id']); ?>" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-trophy mr-1"></i> Leaderboard
                                                    </a>
                                                </td>
                                            </tr>
                                    <?php endforeach;
                                    endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        Klik <b>Buka Host</b> untuk mengelola quiz yang dipilih (Start/Next/End/Reset).
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    // Join by PIN
    $('#btnJoin').on('click', function() {
        var pin = $('#pin').val().trim();
        if (!pin) {
            alert('PIN wajib diisi');
            return;
        }
        $.post('<?= site_url('quiz/api_join_by_pin'); ?>', {
            pin: pin
        }, function(res) {
            if (res.ok) window.location.href = '<?= site_url('quiz/play'); ?>';
            else alert(res.msg || 'Gagal join');
        }, 'json').fail(function(xhr) {
            alert(xhr.responseJSON?.msg || 'Error');
        });
    });

    // Tombol "Buat Quiz (Generate PIN)" akan membuka halaman host kosong
    // Di halaman host, klik "Generate PIN" untuk membuat quiz baru.
</script>