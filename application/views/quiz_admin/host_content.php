<section class="content p-3">
    <div class="container-fluid">

        <!-- Header + PIN -->
        <div class="card card-outline card-success mb-3">
            <div class="card-header bg-success text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-gamepad mr-1"></i>
                    <?= htmlspecialchars($quiz['title'] ?: 'Tanpa Judul', ENT_QUOTES, 'UTF-8') ?>
                </h3>
            </div>
            <div class="card-body text-center">
                <div class="mb-2">
                    <span class="d-inline-block px-4 py-2"
                        style="font-size:42px; letter-spacing:6px; font-weight:700; border:2px dashed #28a745; border-radius:8px;">
                        <span id="activePin">
                            <?= (($quiz['pin'] ?? '') !== '')
                                ? htmlspecialchars($quiz['pin'], ENT_QUOTES, 'UTF-8')
                                : '—' ?>
                        </span>
                    </span>
                </div>
                <div class="mb-2">
                    <button id="btnGenPin" class="btn btn-outline-success btn-lg">
                        <i class="fas fa-key mr-1"></i> Generate PIN
                    </button>
                    <a href="<?= site_url('quiz_admin/leaderboard/' . md5((string)$quiz['id'])) ?>"
                        class="btn btn-outline-info btn-lg ml-2">
                        <i class="fas fa-trophy mr-1"></i> Lihat Leaderboard
                    </a>
                </div>
                <small class="text-muted d-block">PIN hanya berlaku selama quiz aktif. Saat "End", PIN akan hilang.</small>
            </div>
        </div>

        <!-- Kontrol Host -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-sliders-h mr-1"></i> Kontrol Host</h3>
            </div>
            <div class="card-body">
                <!-- Tombol besar & kondisional -->
                <div id="ctrlBtns" class="d-flex flex-wrap align-items-center gap-2 mb-3<?= empty($quiz['pin']) ? ' d-none' : '' ?>">
                    <!-- muncul saat IDLE -->
                    <button id="btnStart" class="btn btn-primary btn-lg ctl-when-idle">
                        <i class="fas fa-play mr-1"></i> Start
                    </button>

                    <!-- muncul saat RUNNING -->
                    <button id="btnNext" class="btn btn-warning btn-lg ctl-when-running d-none">
                        <i class="fas fa-forward mr-1"></i> Next
                    </button>
                    <button id="btnEnd" class="btn btn-danger btn-lg ctl-when-running d-none">
                        <i class="fas fa-stop mr-1"></i> End
                    </button>

                    <!-- selalu muncul, tapi tetap ada confirm -->
                    <button id="btnReset" class="btn btn-success btn-lg ml-auto">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>

                <div class="mb-2 text-muted" id="status">Game belum aktif.</div>

                <small id="qNumberHost" class="text-muted d-block mb-1"></small>

                <h4 id="qTitle" class="mb-2"></h4>
                <ul id="qOpts" class="list-unstyled mb-0"></ul>

                <div class="progress my-3" style="height:10px;">
                    <div id="timeBar" class="progress-bar bg-info" style="width:0%"></div>
                </div>
                <div class="small text-muted" id="timerText">Sisa waktu: -</div>
            </div>
        </div>

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-trophy mr-1"></i> Leaderboard</h3>&nbsp;
                <span class="badge badge-secondary" id="topInfo">Idle</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 datatable-filter-column" id="lbTable">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th>NRP</th>
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
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> Pemain Mencurigakan</h3>&nbsp;
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
</section>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));
        updateControlsVisibility(); // <- cek awal
    });

    let activeQuizId = null;
    const pageQuizId = <?= (int)($quiz['id'] ?? 0) ?>; // quiz yang lagi dibuka
    let currentQuestionId = null;
    let timeLimit = 0;
    let remaining = 0;
    let tmr = null;

    // 1) Tarik state host aktif
    $.get('<?= site_url('quiz_admin/api_host_state'); ?>', function(res) {
        if (res.ok && res.quiz_id) {
            activeQuizId = res.quiz_id;
            if (res.pin) $('#activePin').text(res.pin);
        } else {
            $('#activePin').text('—');
        }
        updateControlsVisibility();
    }, 'json');

    // === Util ===
    function updateTimerUI() {
        const pct = timeLimit ? Math.max(0, Math.min(100, Math.round((remaining / timeLimit) * 100))) : 0;
        $('#timeBar').css('width', pct + '%');
        $('#timerText').text('Sisa waktu: ' + (remaining ?? 0) + ' dtk');
    }

    // 2) Poll pertanyaan aktif (SELALU pakai endpoint player + override)
    function poll() {
        const targetQuizId = activeQuizId || pageQuizId;
        if (!targetQuizId) return;

        $.get('<?= site_url('quiz/api_current'); ?>', {
            quiz_id: targetQuizId
        }, function(res) {
            // IDLE
            if (!res || !res.ok || !res.active) {
                setControlsMode('idle');
                $('#topInfo').text('Idle').removeClass('badge-success').addClass('badge-secondary');
                $('#status').text('Game belum aktif.');
                $('#qTitle').text('');
                $('#qNumberHost').text('');
                $('#qOpts').empty();
                currentQuestionId = null;
                timeLimit = 0;
                remaining = 0;
                clearInterval(tmr);
                updateTimerUI();
                return;
            }

            // RUNNING
            setControlsMode('running');
            $('#topInfo').text('Running').removeClass('badge-secondary').addClass('badge-success');

            $('#status').text('Soal aktif.');
            $('#qTitle').text(res.question || '');
            const opts = res.options || {};
            $('#qOpts').html(Object.entries(opts)
                .map(([k, v]) => `<li><strong>${k}.</strong> ${v || ''}</li>`).join(''));

            // Tampilkan nomor soal x/total di host
            if (typeof res.number !== 'undefined' && typeof res.total !== 'undefined') {
                $('#qNumberHost').text(res.number + '/' + res.total);
                // opsional: badge status ikut menampilkan nomor
                $('#topInfo')
                    .text('Running — ' + res.number + '/' + res.total)
                    .removeClass('badge-secondary')
                    .addClass('badge-success');
            } else {
                $('#qNumberHost').text('');
                $('#topInfo').text('Running').removeClass('badge-secondary').addClass('badge-success');
            }

            if (currentQuestionId !== res.question_id) {
                currentQuestionId = res.question_id;
                timeLimit = typeof res.time_remaining === 'number' ? res.time_remaining : 0;
                remaining = timeLimit;
                clearInterval(tmr);
                if (timeLimit > 0) {
                    tmr = setInterval(() => {
                        remaining = Math.max(0, remaining - 1);
                        updateTimerUI();
                    }, 1000);
                }
            } else if (typeof res.time_remaining === 'number') {
                if (res.time_remaining > timeLimit) timeLimit = res.time_remaining;
                remaining = res.time_remaining;
            }
            updateTimerUI();
        }, 'json').fail(xhr => console.warn('poll failed', xhr));
    }

    // Toggle tampilan tombol sesuai state
    function setControlsMode(mode) {
        // mode: 'idle' | 'running'
        const running = (mode === 'running');
        $('.ctl-when-idle').toggleClass('d-none', running);
        $('.ctl-when-running').toggleClass('d-none', !running);
    }

    // Panggil ini di akhir cabang poll():
    //  - saat game idle:
    setControlsMode('idle');
    //  - saat game running:
    setControlsMode('running');

    // 3) Leaderboard (pakai endpoint player)
    function refreshLeaderboard() {
        const targetQuizId = activeQuizId || pageQuizId;
        if (!targetQuizId) return;

        $.get('<?= site_url('quiz/api_leaderboard'); ?>/' + targetQuizId, function(res) {
            if (!res.ok) return;

            const hasDT = $.fn.DataTable.isDataTable('#lbTable');
            const rows = (res.rows || []).map((r, i) => ([
                i + 1,
                r.nrp || '-', // << NRP
                r.name || '-', // << Nama
                `<span class="badge badge-info">${parseInt(r.score || 0, 10)}</span>`
            ]));

            if (hasDT) {
                const dt = $('#lbTable').DataTable();
                dt.clear();
                rows.forEach(row => dt.row.add(row));
                dt.draw(false);
            } else {
                // fallback (kalau belum ke-init)
                const $body = $('#lbBody').empty();
                (res.rows || []).forEach((r, i) => {
                    $body.append(
                        `<tr>
             <td>${i + 1}</td>
             <td>${r.nrp || '-'}</td>
             <td>${r.name || '-'}</td>
             <td><span class="badge badge-info">${parseInt(r.score || 0, 10)}</span></td>
           </tr>`
                    );
                });
            }
        }, 'json');
    }

    function hasPin() {
        var txt = ($('#activePin').text() || '').trim();
        return /^\d{6}$/.test(txt);
    }

    function updateControlsVisibility() {
        var show = hasPin();
        // pakai class + show/hide + disable (3 lapis)
        $('#ctrlBtns').toggleClass('d-none', !show);
        if (show) {
            $('#ctrlBtns').show();
        } else {
            $('#ctrlBtns').hide();
        }
        $('#ctrlBtns button').prop('disabled', !show);
    }

    $('#btnStart, #btnNext, #btnEnd, #btnReset').on('click.ctrlGuard', function(e) {
        if (!hasPin()) {
            e.stopImmediatePropagation();
            alert('Generate PIN dulu ya.');
            return false;
        }
    });

    // Generate PIN on demand
    $('#btnGenPin').on('click', function() {
        var $btn = $(this).prop('disabled', true);
        $.post('<?= site_url('quiz_admin/api_generate_pin'); ?>', {}, function(res) {
            if (res && res.ok) {
                $('#activePin').text(res.pin);
                updateControlsVisibility();
            } else {
                alert((res && res.msg) ? res.msg : 'Gagal generate PIN');
            }
        }, 'json').fail(function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : 'Error');
        }).always(function() {
            $btn.prop('disabled', false);
        });
    });

    // 4) Kontrol host → endpoint ADMIN (benar)
    $('#btnStart').on('click', function() {
        var $b = $(this).prop('disabled', true);
        $.post('<?= site_url('quiz_admin/api_start'); ?>', {}, function(res) {
                if (!res || !res.ok) {
                    alert((res && res.msg) ? res.msg : 'Gagal start');
                    return;
                }
                poll();
                refreshLeaderboard();
            }, 'json')
            .fail(function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : 'Error');
            })
            .always(function() {
                $b.prop('disabled', false);
            });
    });

    $('#btnNext').on('click', function() {
        var $b = $(this).prop('disabled', true);
        $.post('<?= site_url('quiz_admin/api_next'); ?>', {}, function(res) {
                if (!res || !res.ok) {
                    alert((res && res.msg) ? res.msg : 'Gagal next');
                    return;
                }
                if (res.ended) {
                    $('#activePin').text('—');
                    setControlsMode('idle');
                    updateControlsVisibility();
                } else {
                    currentQuestionId = null;
                }
                poll();
                refreshLeaderboard();
            }, 'json')
            .fail(function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : 'Error');
            })
            .always(function() {
                $b.prop('disabled', false);
            });
    });

    $('#btnEnd').on('click', function() {
        if (!confirm('Akhiri quiz sekarang? PIN akan dihapus.')) return;
        var $b = $(this).prop('disabled', true);
        $.post('<?= site_url('quiz_admin/api_end'); ?>', {}, function(res) {
                if (!res || !res.ok) {
                    alert((res && res.msg) ? res.msg : 'Gagal end');
                    return;
                }
                $('#activePin').text('—');
                setControlsMode('idle');
                updateControlsVisibility();
                poll();
                refreshLeaderboard();
            }, 'json')
            .fail(function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : 'Error');
            })
            .always(function() {
                $b.prop('disabled', false);
            });
    });

    $('#btnReset').on('click', function() {
        if (!confirm('Reset skor & jawaban semua pemain?')) return;
        var $b = $(this).prop('disabled', true);
        $.post('<?= site_url('quiz_admin/api_reset'); ?>', {}, function(res) {
                if (!res || !res.ok) {
                    alert((res && res.msg) ? res.msg : 'Gagal reset');
                    return;
                }
                currentQuestionId = null;
                timeLimit = 0;
                remaining = 0;
                poll();
                refreshLeaderboard();
            }, 'json')
            .fail(function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : 'Error');
            })
            .always(function() {
                $b.prop('disabled', false);
            });
    });

    // 5) Focus table (tetap)
    function refreshFocus() {
        $.get('<?= site_url('quiz_admin/api_focus_stats'); ?>', function(res) {
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

    // Init loops
    setInterval(poll, 1000);
    setInterval(refreshLeaderboard, 2000);
    setInterval(refreshFocus, 10000);

    // First run
    poll();
    refreshLeaderboard();
    refreshFocus();
</script>

<!-- Tambahkan (atau gabung) CSS ini -->
<style>
    .pin-display {
        display: inline-block;
        background: #f8f9fa;
        border-radius: .75rem;
        padding: .25em .5em;
        font-weight: 700;
        letter-spacing: .20em;
        line-height: 1;
        font-size: clamp(40px, 8vw, 96px);
        user-select: all;
    }

    /* tombol besar & nyaman diklik di layar besar maupun proyektor */
    #ctrlBtns .btn.btn-lg {
        min-width: 160px;
        padding: .85rem 1.3rem;
        font-weight: 700;
        letter-spacing: .2px;
        border-radius: .75rem;
    }

    /* util gap sederhana (kalau Bootstrap-mu belum ada .gap-2) */
    .gap-2 {
        gap: .5rem;
    }

    #ctrlBtns.d-none {
        display: none !important;
    }
</style>