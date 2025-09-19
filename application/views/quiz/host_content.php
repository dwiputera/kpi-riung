<section class="content p-3">
    <div class="container-fluid">
        <div class="row">

            <div class="col-lg-7">
                <div class="card card-outline card-success mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title mb-0"><i class="fas fa-plus-circle mr-1"></i> Buat Quiz Baru</h3>
                        <button id="btnCreateQuiz" class="btn btn-sm btn-success">Generate PIN</button>
                    </div>
                    <div class="card-body">
                        <div>PIN Aktif: <span id="activePin" class="badge badge-success">—</span></div>
                        <div>Quiz ID: <span id="activeQuizId" class="badge badge-info">—</span></div>
                        <small class="text-muted">PIN hanya berlaku selama quiz aktif.</small>
                    </div>
                </div>
                <div class="card card-primary">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0"><i class="fas fa-sliders-h mr-1"></i> Kontrol Host</h3>
                        <div class="card-tools">
                            <button id="btnStart" class="btn btn-sm btn-primary"><i class="fas fa-play mr-1"></i>Start</button>
                            <button id="btnNext" class="btn btn-sm btn-warning"><i class="fas fa-forward mr-1"></i>Next</button>
                            <button id="btnEnd" class="btn btn-sm btn-danger"><i class="fas fa-stop mr-1"></i>End</button>
                            <button id="btnReset" class="btn btn-sm btn-success"><i class="fas fa-undo mr-1"></i>Reset</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2 text-muted" id="status">Game belum aktif.</div>
                        <h4 id="qTitle" class="mb-2"></h4>
                        <ul id="qOpts" class="list-unstyled mb-0"></ul>

                        <div class="progress my-3" style="height:10px;">
                            <div id="timeBar" class="progress-bar bg-info" style="width:0%"></div>
                        </div>
                        <div class="small text-muted" id="timerText">Sisa waktu: -</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card card-outline card-info">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title mb-0"><i class="fas fa-trophy mr-1"></i> Leaderboard</h3>
                        <span class="badge badge-secondary" id="topInfo">Idle</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:60px">#</th>
                                        <th>Nama</th>
                                        <th style="width:120px">Skor</th>
                                    </tr>
                                </thead>
                                <tbody id="lbBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-muted"><small>Skor benar berbasis kecepatan (maks 100).</small></div>
                </div>
                <div class="card card-outline card-danger mt-3">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> Pemain Mencurigakan</h3>
                        <button id="btnRefreshFocus" class="btn btn-xs btn-outline-danger">Refresh</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Blur</th>
                                        <th>Last Heartbeat</th>
                                        <th>Flag</th>
                                    </tr>
                                </thead>
                                <tbody id="focusBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    let activeQuizId = null;

    // Ambil quiz host aktif (kalau refresh halaman)
    $.get('<?= site_url('quiz/api_host_state'); ?>', function(res) {
        if (res.ok && res.quiz_id) {
            activeQuizId = res.quiz_id;
            $('#activeQuizId').text(res.quiz_id);
            if (res.pin) $('#activePin').text(res.pin);
        }
    }, 'json');

    const pollInterval = 1000;
    let timeLimit = 0,
        remaining = 0,
        tmr = null;

    function refreshStatus() {
        if (!activeQuizId) {
            $('#status').text('Belum ada quiz aktif. Buat dulu PIN.');
            return;
        }
        $.get('<?= site_url('quiz/api_current'); ?>', {
            quiz_id: activeQuizId
        }, function(res) {
            if (!res.ok || !res.active) {
                $('#status').text('Game belum aktif.');
                $('#qTitle').text('');
                $('#qOpts').empty();
                $('#timeBar').css('width', '0%');
                $('#timerText').text('Sisa waktu: -');
                $('#topInfo').text('Idle').removeClass('badge-success').addClass('badge-secondary');
                clearInterval(tmr);
                timeLimit = 0;
                remaining = 0;
                return;
            }
            $('#status').text('Soal ID: ' + res.question_id);
            $('#qTitle').text(res.question);
            $('#qOpts').html(`<li>A: ${res.options.A}</li><li>B: ${res.options.B}</li><li>C: ${res.options.C}</li><li>D: ${res.options.D}</li>`);
            $('#topInfo').text('Running').removeClass('badge-secondary').addClass('badge-success');

            if (typeof res.time_remaining === 'number') {
                // reset limit saat pindah soal
                if (timeLimit === 0 || remaining === 0 || res.time_remaining > timeLimit) {
                    timeLimit = res.time_remaining;
                }
                remaining = res.time_remaining;
                updateTimerUI();
                clearInterval(tmr);
                tmr = setInterval(() => {
                    remaining--;
                    if (remaining < 0) remaining = 0;
                    updateTimerUI();
                }, 1000);
            }
        }, 'json');
    }

    function updateTimerUI() {
        const pct = timeLimit ? Math.max(0, Math.min(100, Math.round((remaining / timeLimit) * 100))) : 0;
        $('#timeBar').css('width', pct + '%');
        $('#timerText').text('Sisa waktu: ' + remaining + ' dtk');
    }

    function refreshLeaderboard() {
        if (!activeQuizId) return;
        $.get('<?= site_url('quiz/api_leaderboard'); ?>/' + activeQuizId, function(res) {
            if (!res.ok) return;
            const body = $('#lbBody').empty();
            res.rows.forEach((r, i) => body.append(`<tr><td>${i+1}</td><td>${r.name}</td><td><span class="badge badge-info">${r.score}</span></td></tr>`));
        }, 'json');
    }

    // Controls
    $('#btnStart').on('click', () => {
        $.post('<?= site_url('quiz/api_start'); ?>', {}, function(res) {
            if (res.ok) {
                alert('Game dimulai.');
                timeLimit = 0;
                remaining = 0;
                refreshStatus();
            } else {
                alert(res.msg || 'Gagal start');
            }
        }, 'json');
    });
    $('#btnNext').on('click', () => {
        $.post('<?= site_url('quiz/api_next'); ?>', {}, function(res) {
            if (res.ok) {
                if (res.ended) {
                    alert('Soal habis. Game berakhir.');
                }
                timeLimit = 0;
                remaining = 0;
                refreshStatus();
            } else {
                alert(res.msg || 'Gagal next');
            }
        }, 'json');
    });
    $('#btnEnd').on('click', () => {
        $.post('<?= site_url('quiz/api_end'); ?>', {}, function(res) {
            if (res.ok) {
                alert('Game diakhiri.');
                timeLimit = 0;
                remaining = 0;
                refreshStatus();
            }
        }, 'json');
    });
    $('#btnReset').on('click', () => {
        if (!confirm('Reset akan menghapus jawaban & skor. Lanjutkan?')) return;
        $.post('<?= site_url('quiz/api_reset'); ?>', {}, function(res) {
            if (res.ok) {
                alert('Reset berhasil.');
                refreshStatus();
                refreshLeaderboard();
            }
        }, 'json');
    });

    setInterval(refreshStatus, pollInterval);
    setInterval(refreshLeaderboard, pollInterval);
    refreshStatus();
    refreshLeaderboard();

    function refreshFocus() {
        $.get('<?= site_url('quiz/api_focus_stats'); ?>', function(res) {
            if (!res.ok) return;
            const body = $('#focusBody').empty();
            res.rows.forEach(r => {
                if (r.blur_count >= 3 || r.suspicious == 1) {
                    body.append(`<tr>
            <td>${r.name}</td>
            <td><span class="badge badge-warning">${r.blur_count}</span></td>
            <td>${r.last_heartbeat || '-'}</td>
            <td>${r.suspicious==1 ? '<span class="badge badge-danger">Yes</span>' : '-'}</td>
          </tr>`);
                }
            });
            if (body.children().length === 0) {
                body.append('<tr><td colspan="4" class="text-center text-muted">Tidak ada aktivitas mencurigakan.</td></tr>');
            }
        }, 'json');
    }
    $('#btnRefreshFocus').on('click', refreshFocus);
    setInterval(refreshFocus, 10000); // auto refresh tiap 10s
    refreshFocus();

    $('#btnCreateQuiz').on('click', function() {
        $.post('<?= site_url('quiz/api_quiz_create'); ?>', {}, function(res) {
            if (res.ok) {
                activeQuizId = res.quiz_id;
                $('#activePin').text(res.pin);
                $('#activeQuizId').text(res.quiz_id);
                alert('Quiz dibuat! PIN: ' + res.pin);
                // Optional: langsung refresh status/leaderboard
                refreshStatus();
                refreshLeaderboard();
            } else {
                alert(res.msg || 'Gagal membuat quiz');
            }
        }, 'json');
    });
</script>