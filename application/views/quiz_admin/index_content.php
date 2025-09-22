<section class="content p-3">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="fas fa-gamepad mr-1"></i> Quiz (Admin)</h3>
                <button class="btn btn-success" id="btnNewQuiz">
                    <i class="fas fa-plus-circle mr-1"></i> Buat Quiz
                </button>
            </div>
        </div>

        <!-- Daftar quiz milik saya -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 datatable-filter-column" id="quizTable" data-server="false">
                                <thead class="thead-light text-center">
                                    <tr>
                                        <th>No.</th>
                                        <th>Created At</th>
                                        <th>Judul</th>
                                        <th>PIN</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; ?>
                                    <?php foreach ($my_quizzes as $q): ?>
                                        <?php
                                        $title = htmlspecialchars($q['title'] ?? '(tanpa judul)', ENT_QUOTES, 'UTF-8');
                                        $pin   = htmlspecialchars($q['pin'] ?? '-', ENT_QUOTES, 'UTF-8');
                                        $isActive = !empty($q['is_active']);
                                        ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td class="text-nowrap"><?= $q['created_at'] ?></td>
                                            <td><?= $title ?></td>
                                            <td><span class="badge badge-info"><?= $pin ?></span></td>
                                            <td>
                                                <?php if ($isActive): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Ended</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('quiz_admin/host/' . (int)$q['id']) ?>">
                                                    <i class="fas fa-sliders-h mr-1"></i> Host
                                                </a>
                                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('quiz_admin/builder/' . (int)$q['id']) ?>">
                                                    <i class="fas fa-edit mr-1"></i> Builder
                                                </a>
                                                <a class="btn btn-sm btn-outline-info" href="<?= site_url('quiz_admin/leaderboard/' . md5((string)$q['id'])) ?>">
                                                    <i class="fas fa-trophy mr-1"></i> Leaderboard
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Modal Buat Quiz -->
<div class="modal fade" id="modalNewQuiz" tabindex="-1" role="dialog" aria-labelledby="modalNewQuizLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNewQuizLabel">
                    <i class="fas fa-plus-circle mr-1"></i> Buat Quiz Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="quizTitle">Judul Quiz</label>
                    <input type="text" id="quizTitle" class="form-control" maxlength="200" placeholder="Misal: Pre-Test Safety Induksi">
                    <small class="form-text text-muted">Wajib diisi. Maks 200 karakter.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btnCreateNow" class="btn btn-primary">
                    <i class="fas fa-check mr-1"></i> Buat
                </button>
                <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    // Modal create
    $('#btnNewQuiz').on('click', function() {
        $('#quizTitle').val('');
        $('#modalNewQuiz').modal('show');
        setTimeout(() => $('#quizTitle').focus(), 200);
    });

    $('#btnCreateNow').on('click', function() {
        const title = ($('#quizTitle').val() || '').trim();
        if (!title) {
            alert('Judul wajib diisi');
            return;
        }

        $.post('<?= site_url('quiz_admin/api_quiz_create'); ?>', {
            title
        }, function(res) {
            if (res && res.ok) {
                // langsung ke builder quiz baru
                window.location.href = '<?= site_url('quiz_admin/builder/'); ?>' + res.quiz_id;
            } else {
                alert(res?.msg || 'Gagal membuat quiz');
            }
        }, 'json').fail(function(xhr) {
            alert(xhr.responseJSON?.msg || 'Error');
        });
    });

    // DataTable + filter per kolom
    $(function() {
        setupFilterableDatatable($('#quizTable'));
    });
</script>