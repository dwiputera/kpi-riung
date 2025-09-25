<style>
    body,
    .card,
    #q,
    #opts {
        user-select: none;
        -webkit-user-select: none;
    }
</style>

<section class="content p-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-question-circle mr-1"></i> Pertanyaan</h3>
                        <span class="badge badge-info" id="myScoreBadge">Skor: 0</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" id="state">Menunggu host memulai…</p>
                        <small class="text-muted d-block mb-1" id="qNumber"></small>
                        <h4 id="q" class="mb-3"></h4>

                        <div class="progress mb-2" style="height:10px;">
                            <div id="timeBar" class="progress-bar bg-success" role="progressbar" style="width:0%"></div>
                        </div>
                        <div class="small text-muted mb-3" id="timer">Sisa waktu: -</div>

                        <div id="opts" class="row"><!-- tombol jawaban --></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-user mr-1"></i> Status Pemain</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-1 text-muted">Skor kamu saat ini:</p>
                        <h2 id="myScore" class="mb-0">0</h2>
                    </div>
                </div>
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Tips</h3>
                    </div>
                    <div class="card-body">Semakin cepat menjawab, semakin besar poin (maks 100).</div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    let currentQuestionId = null;
    let answered = false;
    let pollInterval = 1000; // 1 detik
    let countdown = null;
    let timeLimit = null,
        remaining = null;

    // NEW: simpan urutan acak per soal agar konsisten sampai soal berganti
    let optionOrder = null;

    function shuffle(arr) {
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr;
    }

    function setScore(v) {
        $('#myScore').text(v || 0);
        $('#myScoreBadge').text('Skor: ' + (v || 0));
    }

    function renderOptions(data) {
        const opts = data.options;
        const container = $('#opts').empty();
        const displayLabels = ['A', 'B', 'C', 'D']; // label tampilan saja

        // Pastikan optionOrder sudah ada (ditetapkan di renderQuestion)
        if (!optionOrder) optionOrder = shuffle(['A', 'B', 'C', 'D']);

        optionOrder.forEach((origKey, idx) => {
            const txt = opts[origKey];
            const label = displayLabels[idx]; // label tampilan (A/B/C/D urutan visual)

            const col = $('<div/>', {
                class: 'col-12 col-md-6 mb-2'
            }).appendTo(container);
            const btn = $('<button/>', {
                class: 'btn btn-outline-primary btn-lg btn-block text-left',
                html: `<strong>${label}.</strong> ${txt}`
            }).appendTo(col);

            // Penting: submit huruf ASLI (origKey), bukan label tampilan
            btn.on('click', function() {
                if (answered) return;
                answered = true;
                btn.addClass('disabled');
                $.post('<?= site_url('quiz/api_answer'); ?>', {
                    question_id: currentQuestionId,
                    chosen: origKey // <<< kirim A/B/C/D ASLI
                }, function(res) {
                    if (res.ok) {
                        const msg = (res.correct ? '✅ Benar!' : '❌ Salah!') +
                            (typeof res.added !== 'undefined' ? `\n+${res.added} poin` : '') +
                            (typeof res.score !== 'undefined' ? `\nSkor kamu: ${res.score}` : '');
                        // alert(msg);
                        if (typeof res.score !== 'undefined') setScore(res.score);
                    } else {
                        alert(res.msg || 'Gagal submit');
                        answered = false;
                        btn.removeClass('disabled');
                    }
                }, 'json').fail(function(xhr) {
                    alert(xhr.responseJSON?.msg || 'Error submit');
                    answered = false;
                    btn.removeClass('disabled');
                });
            });
        });
    }

    function startTimer(seconds) {
        clearInterval(countdown);
        timeLimit = seconds;
        remaining = seconds;
        updateTimerUI();
        countdown = setInterval(() => {
            remaining--;
            if (remaining < 0) remaining = 0;
            updateTimerUI();
        }, 1000);
    }

    function updateTimerUI() {
        $('#timer').text('Sisa waktu: ' + remaining + ' dtk');
        const pct = timeLimit ? Math.max(0, Math.min(100, Math.round((remaining / timeLimit) * 100))) : 0;
        $('#timeBar').css('width', pct + '%').toggleClass('bg-danger', pct <= 20).toggleClass('bg-success', pct > 20);
    }

    function renderQuestion(data) {
        $('#state').text('');
        $('#q').text(data.question);
        answered = false;
        currentQuestionId = data.question_id;

        // NEW: tampilkan nomor soal, misalnya "1/10"
        if (data.number && data.total) {
            $('#qNumber').text(data.number + '/' + data.total);
        } else {
            $('#qNumber').text(''); // fallback kalau tidak ada
        }

        // acak urutan untuk soal ini
        optionOrder = shuffle(['A', 'B', 'C', 'D']);
        renderOptions(data);

        if (data.time_remaining !== null) startTimer(data.time_remaining);
        else {
            $('#timer').text('');
            $('#timeBar').css('width', '0%');
            clearInterval(countdown);
        }
    }

    function poll() {
        $.get('<?= site_url('quiz/api_current'); ?>', function(res) {
            if (!res.ok) return;
            if (typeof res.my_score !== 'undefined') setScore(res.my_score);

            if (!res.active) {
                if (currentQuestionId !== null && (res.leaderboard_hash || res.quiz_id)) {
                    const slug = res.leaderboard_hash || res.quiz_id; // fallback kalau hash belum ada
                    window.location.href = '<?= site_url('quiz/leaderboard/'); ?>' + slug;
                    return;
                }
                // belum start
                $('#state').text('Menunggu host memulai…');
                $('#q').text('');
                $('#opts').empty();
                $('#timer').text('');
                $('#timeBar').css('width', '0%');
                currentQuestionId = null;
                answered = false;
                return;
            }
            if (currentQuestionId !== res.question_id) renderQuestion(res);
        }, 'json');
    }


    setInterval(poll, pollInterval);
    poll();

    // Heartbeat tiap 15 detik
    setInterval(function() {
        $.post('<?= site_url('quiz/api_heartbeat'); ?>', {}, function() {}, 'json');
    }, 15000);

    // Deteksi tab blur/focus
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            $.post('<?= site_url('quiz/api_focus'); ?>', {
                action: 'blur'
            });
        } else {
            $.post('<?= site_url('quiz/api_focus'); ?>', {
                action: 'focus'
            });
        }
    });

    // Cegah klik kanan dan kombinasi copy/paste dasar (opsional)
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && ['c', 'v', 'x', 's', 'p'].includes(e.key.toLowerCase())) e.preventDefault();
        if (e.key === 'PrintScreen') e.preventDefault();
    });

    // Deteksi devtools sederhana (heuristik; tidak 100% akurat)
    (function devtoolsCheck() {
        let threshold = 160; // heuristik
        setInterval(function() {
            const devtoolsOpen = (window.outerWidth - window.innerWidth > threshold) ||
                (window.outerHeight - window.innerHeight > threshold);
            if (devtoolsOpen) {
                // Naikkan blur_count sebagai sinyal
                $.post('<?= site_url('quiz/api_focus'); ?>', {
                    action: 'blur'
                });
            }
        }, 3000);
    })();
</script>