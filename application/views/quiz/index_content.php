<section class="content p-3">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="fas fa-gamepad mr-1"></i> Join Quiz</h3>
                <!-- opsional: link ke admin dashboard -->
                <a class="btn btn-outline-secondary" href="<?= site_url('quiz_admin'); ?>">
                    <i class="fas fa-sliders-h mr-1"></i> Admin
                </a>
            </div>
        </div>

        <!-- Join via PIN -->
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-sign-in-alt mr-1"></i> Masuk dengan PIN</h3>
            </div>
            <div class="card-body">
                <div class="form-row align-items-center">
                    <div class="col-sm-6 mb-2">
                        <input type="text"
                            id="joinPin"
                            class="form-control form-control-lg"
                            maxlength="6"
                            placeholder="Masukkan PIN 6 digit">
                    </div>
                    <div class="col-sm-6 mb-2">
                        <button id="btnJoin" class="btn btn-lg btn-success btn-block">
                            <i class="fas fa-door-open mr-1"></i> Join
                        </button>
                    </div>
                </div>
                <small class="text-muted">Minta PIN dari host, lalu masukkan di sini.</small>
            </div>
        </div>

    </div>
</section>

<script>
    function doJoin() {
        const raw = ($('#joinPin').val() || '').trim();
        const pin = raw.replace(/\D/g, '').padStart(6, '0');
        if (!pin || pin.length !== 6) {
            alert('PIN harus 6 digit.');
            return;
        }

        const $btn = $('#btnJoin').prop('disabled', true).text('Joining…');
        $.post('<?= site_url('quiz/api_join_by_pin'); ?>', {
            pin
        }, function(res) {
            if (res && res.ok) {
                if (res.ended) {
                    // quiz sudah selesai → langsung ke leaderboard
                    const slug = res.leaderboard_hash || res.quiz_id; // fallback kalau hash belum ada
                    window.location.href = '<?= site_url('quiz/leaderboard/'); ?>' + slug;
                } else {
                    // quiz aktif → ke play
                    window.location.href = '<?= site_url('quiz/play'); ?>';
                }
            } else {
                alert(res?.msg || 'Gagal join');
            }
        }, 'json').fail(function(xhr) {
            alert(xhr.responseJSON?.msg || 'Error');
        }).always(function() {
            $btn.prop('disabled', false).html('<i class="fas fa-door-open mr-1"></i> Join');
        });
    }

    $('#btnJoin').on('click', doJoin);
    $('#joinPin').on('keyup', function(e) {
        if (e.key === 'Enter') doJoin();
    });
</script>

<style>
    #joinPin {
        letter-spacing: 2px;
        text-align: center;
    }
</style>