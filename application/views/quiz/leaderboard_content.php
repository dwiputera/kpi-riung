<?php
// Expect: $leaders = $this->quiz->leaderboard(50)
$top1 = $leaders[0] ?? null;
$top2 = $leaders[1] ?? null;
$top3 = $leaders[2] ?? null;
$rest = array_slice($leaders, 3);
function safe($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>

<section class="content p-3">
    <div class="container-fluid">

        <!-- Title -->
        <div class="row">
            <div class="col-12 text-center mb-3">
                <h2 class="mb-0"><i class="fas fa-trophy text-warning"></i> Leaderboard Akhir</h2>
                <small class="text-muted">Selamat kepada para pemenang 🎉</small>
                <?php if (!empty($quiz['title'])): ?>
                    <small class="d-block mt-1 text-secondary">Quiz: <?= htmlspecialchars($quiz['title'], ENT_QUOTES, 'UTF-8') ?></small>
                <?php endif; ?>
            </div>
        </div>

        <!-- Podium -->
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="card card-outline card-success">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-center align-items-end podium-wrap">

                            <!-- 2nd -->
                            <div class="podium-col order-1 order-md-1">
                                <div class="podium-card podium-silver <?= $top2 ? '' : 'podium-empty' ?>">
                                    <div class="podium-rank">2</div>
                                    <div class="podium-name"><?= $top2 ? safe($top2['name']) : '—' ?></div>
                                    <div class="podium-score"><?= $top2 ? (int)$top2['score'] : 0 ?></div>
                                    <div class="podium-base podium-base-silver"></div>
                                </div>
                            </div>

                            <!-- 1st -->
                            <div class="podium-col order-0 order-md-2">
                                <div class="podium-card podium-gold podium-first <?= $top1 ? '' : 'podium-empty' ?>">
                                    <div class="podium-crown"><i class="fas fa-crown"></i></div>
                                    <div class="podium-rank">1</div>
                                    <div class="podium-name"><?= $top1 ? safe($top1['name']) : '—' ?></div>
                                    <div class="podium-score"><?= $top1 ? (int)$top1['score'] : 0 ?></div>
                                    <div class="podium-base podium-base-gold"></div>
                                </div>
                            </div>

                            <!-- 3rd -->
                            <div class="podium-col order-2 order-md-3">
                                <div class="podium-card podium-bronze <?= $top3 ? '' : 'podium-empty' ?>">
                                    <div class="podium-rank">3</div>
                                    <div class="podium-name"><?= $top3 ? safe($top3['name']) : '—' ?></div>
                                    <div class="podium-score"><?= $top3 ? (int)$top3['score'] : 0 ?></div>
                                    <div class="podium-base podium-base-bronze"></div>
                                </div>
                            </div>

                        </div>

                        <div class="text-center mt-3">
                            <button id="btnConfetti" class="btn btn-success">
                                <i class="fas fa-fireworks mr-1"></i> Rayakan lagi
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Others -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-list-ol mr-1"></i> Peringkat Selanjutnya</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <a href="<?= site_url('quiz'); ?>" class="btn btn-sm btn-outline-secondary">Main Lagi</a>
                        </div><br>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:80px">#</th>
                                        <th>Nama</th>
                                        <th style="width:140px">Skor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rest)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada data tambahan.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $i = 4;
                                        foreach ($rest as $row): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= safe($row['name']) ?></td>
                                                <td><span class="badge badge-info"><?= (int)$row['score'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-muted text-center">
                        Selamat! Sampai jumpa di kuis berikutnya.
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
    /* ====== Podium Styles ====== */
    .podium-wrap {
        gap: 18px;
    }

    .podium-col {
        width: 220px;
    }

    .podium-card {
        position: relative;
        width: 100%;
        min-height: 220px;
        border-radius: 20px;
        padding: 16px 12px 56px;
        text-align: center;
        color: #fff;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .15);
        transform: translateY(20px);
    }

    .podium-first {
        transform: translateY(-10px);
    }

    .podium-gold {
        background: linear-gradient(135deg, #f7c948, #f1b000);
    }

    .podium-silver {
        background: linear-gradient(135deg, #cfd8dc, #b0bec5);
    }

    .podium-bronze {
        background: linear-gradient(135deg, #d7a86e, #b2744c);
    }

    .podium-empty {
        opacity: .4;
    }

    .podium-crown {
        position: absolute;
        top: -16px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 26px;
        color: #ffe96b;
        text-shadow: 0 2px 6px rgba(0, 0, 0, .3);
        animation: floaty 2s ease-in-out infinite;
    }

    @keyframes floaty {

        0%,
        100% {
            transform: translate(-50%, 0)
        }

        50% {
            transform: translate(-50%, -6px)
        }
    }

    .podium-rank {
        font-size: 48px;
        font-weight: 800;
        line-height: 1;
        text-shadow: 0 3px 10px rgba(0, 0, 0, .25);
    }

    .podium-name {
        margin-top: 6px;
        font-size: 18px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .podium-score {
        margin-top: 8px;
        font-size: 20px;
        font-weight: 700;
        background: rgba(255, 255, 255, .15);
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
    }

    .podium-base {
        position: absolute;
        left: 10%;
        right: 10%;
        bottom: 10px;
        height: 36px;
        border-radius: 14px;
        box-shadow: inset 0 4px 10px rgba(0, 0, 0, .15);
    }

    .podium-base-gold {
        background: linear-gradient(180deg, #ffe082, #fdd835);
    }

    .podium-base-silver {
        background: linear-gradient(180deg, #eceff1, #cfd8dc);
    }

    .podium-base-bronze {
        background: linear-gradient(180deg, #efc090, #d7a86e);
    }

    /* Responsive */
    @media (max-width: 575.98px) {
        .podium-col {
            width: 100%;
        }

        .podium-card {
            transform: none;
        }

        .podium-first {
            transform: none;
        }
    }
</style>

<!-- Confetti: CDN + fallback mini -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    (function() {
        // Fallback minimal kalau CDN gagal
        function tinyConfettiFallback() {
            // animasi sederhana: gunakan emoji 🎉
            const el = document.createElement('div');
            el.textContent = '🎉';
            el.style.position = 'fixed';
            el.style.left = '50%';
            el.style.top = '10%';
            el.style.transform = 'translateX(-50%)';
            el.style.fontSize = '48px';
            el.style.animation = 'fadeDrop 1.2s ease forwards';
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 1300);
        }
        // efek utama
        function burst() {
            if (window.confetti) {
                const duration = 1200;
                const end = Date.now() + duration;
                (function frame() {
                    confetti({
                        particleCount: 3,
                        spread: 60,
                        startVelocity: 50,
                        origin: {
                            x: Math.random(),
                            y: Math.random() * 0.2
                        }
                    });
                    if (Date.now() < end) requestAnimationFrame(frame);
                })();
                // kiri-kanan besar
                confetti({
                    particleCount: 120,
                    spread: 70,
                    origin: {
                        x: 0.2,
                        y: 0.1
                    }
                });
                confetti({
                    particleCount: 120,
                    spread: 70,
                    origin: {
                        x: 0.8,
                        y: 0.1
                    }
                });
            } else {
                tinyConfettiFallback();
            }
        }

        // auto jalan saat halaman dibuka
        window.addEventListener('load', function() {
            // delay dikit biar dramatis
            setTimeout(burst, 400);
        });

        // tombol manual
        $('#btnConfetti').on('click', burst);
    })();
</script>