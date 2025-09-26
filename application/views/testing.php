<?php

/**
 * View: Excavator Cycle Timer (Primary + Secondary)
 * Framework: CodeIgniter 3 + AdminLTE3 (uses styles.php & scripts.php already loaded in main template)
 * Behavior ringkas:
 *  - Single master timer (Start/Pause) pakai requestAnimationFrame.
 *  - Satu work aktif (primary/secondary). Switching menutup segmen lama & log (hanya saat running).
 *  - Urutan primary: Digging → Swing Load → Dumping → Swing Empty.
 *  - Next (N): lanjut ke primary berikutnya. Saat di Swing Empty → close cycle (auto-save), timer lanjut.
 *  - Secondary bisa dipilih kapan saja (shared timer).
 *  - Tabel gabungan semua truck (ada kolom Truck). Export CSV semua data.
 */ ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Excavator Cycle Timer</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                    <li class="breadcrumb-item active">Cycle Timer</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid xc2-container">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Primary & Secondary Work (Single Timer) — <strong>Normalized: Phase + Work Type</strong></h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Controls -->
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <button id="xc2-start" class="btn btn-primary mr-2">
                                <i class="fas fa-play"></i> Start / Pause
                            </button>
                            <button id="xc2-next" class="btn btn-success mr-2">
                                <i class="fas fa-step-forward"></i> Next Primary (N)
                            </button>
                            <button id="xc2-save" class="btn btn-default mr-2" title="Save cycle now without stopping the master timer">
                                <i class="fas fa-save"></i> Save Cycle
                            </button>
                            <button id="xc2-reset" class="btn btn-danger mr-2">
                                <i class="fas fa-undo"></i> Reset (clear current counters)
                            </button>

                            <div class="ml-auto d-flex align-items-center" style="gap:8px;">
                                <button id="xc2-export" class="btn btn-default">
                                    <i class="fas fa-file-export"></i> Export CSV
                                </button>
                                <button id="xc2-clear" class="btn btn-outline-danger">
                                    <i class="fas fa-trash"></i> Clear All
                                </button>
                            </div>
                        </div>

                        <!-- Fixed Dump Truck selector (DT-1 .. DT-5) -->
                        <div class="d-flex flex-wrap align-items-center mb-3" style="gap:8px;">
                            <label class="mb-0">Dump Truck:</label>
                            <select id="xc2-truck" style="min-width:200px;">
                                <option value="DT-1">DT-1</option>
                                <option value="DT-2">DT-2</option>
                                <option value="DT-3">DT-3</option>
                                <option value="DT-4">DT-4</option>
                                <option value="DT-5">DT-5</option>
                            </select>
                        </div>

                        <small class="text-muted d-block mb-3">
                            Satu timer untuk semua aktivitas. Pilih <b>Work Type</b> di bawah (primary atau secondary) — waktu akan masuk ke kategori yang aktif.
                            Gunakan <b>Next Primary</b> untuk melanjutkan urutan primary. Setelah mencapai <b>Swing Empty</b>, cycle akan <b>auto-saved</b> dan counter di-reset untuk cycle berikutnya (timer tidak pause).
                            Shortcut: tekan <b>N</b> untuk Next Primary.
                        </small>

                        <div class="row">
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-12 col-md-12 mb-2">
                                                <div class="xc2-display" id="xc2-total">Total: 00:00.000</div>
                                            </div>
                                            <div class="col-6 mb-2">
                                                <div class="xc2-display" id="xc2-cycle">Cycle: 00:00.000</div>
                                            </div>
                                            <div class="col-6 mb-2">
                                                <div class="xc2-display" id="xc2-phase">Phase: 00:00.000</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="mb-2">Work Type (choose one active)</label>
                                    <div class="xc2-work-grid">
                                        <!-- Primary -->
                                        <button class="btn btn-outline-info xc2-work" data-kind="p" data-index="0">1) Digging</button>
                                        <button class="btn btn-outline-info xc2-work" data-kind="p" data-index="1">2) Swing Load</button>
                                        <button class="btn btn-outline-info xc2-work" data-kind="p" data-index="2">3) Dumping</button>
                                        <button class="btn btn-outline-info xc2-work" data-kind="p" data-index="3">4) Swing Empty</button>
                                        <!-- Secondary -->
                                        <button class="btn btn-outline-secondary xc2-work" data-kind="s" data-index="0">Dig to Prepare</button>
                                        <button class="btn btn-outline-secondary xc2-work" data-kind="s" data-index="1">Positioning</button>
                                        <button class="btn btn-outline-secondary xc2-work" data-kind="s" data-index="2">Clearing</button>
                                        <button class="btn btn-outline-secondary xc2-work" data-kind="s" data-index="3">Wait to Dump</button>
                                        <button class="btn btn-outline-secondary xc2-work" data-kind="s" data-index="4">Idle</button>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-6 col-sm-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info"><i class="far fa-clock"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Avg Cycle Time</span>
                                                <span class="info-box-number" id="xc2-avg">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success"><i class="fas fa-sync"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Cycles Recorded</span>
                                                <span class="info-box-number" id="xc2-count">0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="xc2-notes">Notes (local only)</label>
                                    <textarea id="xc2-notes" class="form-control" rows="4" placeholder="Catatan shift, operator, kondisi material, dll..."></textarea>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover" id="xc2-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Truck</th>
                                                <th>Cycle</th>
                                                <th>Phase</th>
                                                <th>Work Type</th>
                                                <th>Duration</th>
                                                <th class="text-right">Date/Time</th>
                                            </tr>
                                        </thead>
                                        <tbody id="xc2-rows"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div><!-- /.card-body -->
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .xc2-display {
        font-variant-numeric: tabular-nums;
        font-weight: 800;
        letter-spacing: .5px;
        font-size: 44px;
        background: #0f1730;
        color: #e9edf5;
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, .08);
    }

    .xc2-work-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 8px;
    }

    .xc2-work.active {
        outline: 2px solid #17a2b8;
    }
</style>

<script>
    (function() {
        // ===== Utils =====
        const $ = (s, c = document) => c.querySelector(s);
        const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));
        const fmt = (ms) => {
            const sign = ms < 0 ? '-' : '';
            ms = Math.abs(ms);
            const m = Math.floor(ms / 60000),
                s = Math.floor((ms % 60000) / 1000),
                ms3 = Math.floor(ms % 1000);
            return `${sign}${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}.${String(ms3).padStart(3,'0')}`;
        };

        // ===== Constants =====
        const PRIMARY = ['Digging', 'Swing Load', 'Dumping', 'Swing Empty'];
        const SECOND = ['Dig to Prepare', 'Positioning', 'Clearing', 'Wait to Dump', 'Idle'];
        const FIXED_TRUCKS = ['DT-1', 'DT-2', 'DT-3', 'DT-4', 'DT-5'];

        // ===== Master timer state =====
        let running = false,
            startAt = null,
            totalBase = 0,
            rafId = null;

        // ===== Cycle & Phase timers =====
        let cycleBase = 0,
            cycleStartAt = null,
            phaseBase = 0,
            phaseStartAt = null;

        // ===== Active work =====
        let activeKind = 'p',
            activeIndex = 0,
            lastSwitchAt = null,
            everStarted = false;
        let lastPrimaryIndex = null; // null kalau belum pernah primary

        // ===== Truck selection (fixed) =====
        let currentTruck = 'DT-1';

        // ===== Accumulators (optional) =====
        let accP = [0, 0, 0, 0],
            accS = [0, 0, 0, 0, 0];

        // ===== Logging =====
        // row: {truck, cycle, phase, workType, ms, ts}
        let cycleNo = 1,
            rowsLog = [];
        let kpiCycleCount = 0,
            kpiCycleSum = 0; // global

        // ===== Storage Keys =====
        const STORE_ROWS = 'xc2_rows_v1';
        const STORE_META = 'xc2_meta_v1';

        // ===== Elements =====
        const elTotal = $('#xc2-total'),
            elCycle = $('#xc2-cycle'),
            elPhase = $('#xc2-phase');
        const elRows = $('#xc2-rows'),
            elAvg = $('#xc2-avg'),
            elCount = $('#xc2-count');
        const elNotes = $('#xc2-notes'),
            workBtns = $$('.xc2-work'),
            selTruck = $('#xc2-truck');

        // restore notes
        try {
            elNotes.value = localStorage.getItem('xc2_notes_v1') || '';
        } catch (e) {}
        elNotes.addEventListener('input', () => localStorage.setItem('xc2_notes_v1', elNotes.value));

        // restore rows
        try {
            const saved = JSON.parse(localStorage.getItem(STORE_ROWS) || '[]');
            if (Array.isArray(saved)) rowsLog = saved;
        } catch (e) {}

        // restore meta
        try {
            const meta = JSON.parse(localStorage.getItem(STORE_META) || '{}');
            if (meta && typeof meta === 'object') {
                cycleNo = meta.cycleNo ?? 1;
                kpiCycleCount = meta.kpiCycleCount ?? 0;
                kpiCycleSum = meta.kpiCycleSum ?? 0;
                activeKind = meta.activeKind ?? 'p';
                activeIndex = Number.isInteger(meta.activeIndex) ? meta.activeIndex : 0;
                everStarted = !!meta.everStarted;
                lastPrimaryIndex = (typeof meta.lastPrimaryIndex === 'number') ? meta.lastPrimaryIndex : null;
                currentTruck = FIXED_TRUCKS.includes(meta.currentTruck) ? meta.currentTruck : 'DT-1';
            }
        } catch (e) {}

        // ===== Helpers =====
        const nowMs = () => Date.now();

        function persist() {
            try {
                localStorage.setItem(STORE_ROWS, JSON.stringify(rowsLog));
            } catch (e) {}
            try {
                localStorage.setItem(STORE_META, JSON.stringify({
                    cycleNo,
                    kpiCycleCount,
                    kpiCycleSum,
                    activeKind,
                    activeIndex,
                    everStarted,
                    lastPrimaryIndex,
                    currentTruck
                }));
            } catch (e) {}
        }

        function tick() {
            const t = nowMs();
            const total = totalBase + (running && startAt ? (t - startAt) : 0);
            const cyc = cycleBase + (running && cycleStartAt ? (t - cycleStartAt) : 0);
            const phs = phaseBase + (running && phaseStartAt ? (t - phaseStartAt) : 0);
            if (elTotal) elTotal.textContent = `Total: ${fmt(total)}`;
            if (elCycle) elCycle.textContent = `Cycle: ${fmt(cyc)}`;
            if (elPhase) elPhase.textContent = `Phase: ${fmt(phs)}`;
        }

        function loop() {
            tick();
            rafId = requestAnimationFrame(loop);
        }

        function startLoop() {
            if (!rafId) loop();
        }

        function stopLoop() {
            if (rafId) {
                cancelAnimationFrame(rafId);
                rafId = null;
            }
            tick();
        }

        function accrueBucketsSinceSwitch() {
            if (!running || lastSwitchAt == null) return;
            const d = nowMs() - lastSwitchAt;
            if (activeKind === 'p') accP[activeIndex] += d;
            else accS[activeIndex] += d;
            lastSwitchAt = nowMs();
        }

        function finalizeSegment(labelPhase, labelWorkType, ms) {
            if (!running || !everStarted) return;
            if (!ms || ms <= 0) return;
            const row = {
                truck: currentTruck || 'DT-1',
                cycle: cycleNo,
                phase: labelPhase,
                workType: labelWorkType,
                ms,
                ts: new Date().toISOString()
            };
            rowsLog.push(row);
            persist();
            appendRow(row); // tampilkan live
        }

        function appendRow(r) {
            if (!elRows) return;
            const tr = document.createElement('tr');
            tr.innerHTML = `
      <td>${r.truck}</td>
      <td>${r.cycle}</td>
      <td>${r.phase}</td>
      <td>${r.workType}</td>
      <td>${fmt(r.ms)}</td>
      <td class="text-right">${new Date(r.ts).toLocaleString()}</td>`;
            // terbaru di atas
            elRows.prepend(tr);
        }

        function renderTruckOptions() {
            if (!selTruck) return;
            // set nilai dari meta, kalau tidak ada, default DT-1
            if (!FIXED_TRUCKS.includes(currentTruck)) currentTruck = 'DT-1';
            selTruck.value = currentTruck;

            // Select2 optional
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                const $sel = window.jQuery(selTruck);
                if ($sel.data('select2')) $sel.select2('destroy');
                $sel.select2({
                    theme: 'bootstrap4',
                    width: 'resolve'
                });
                $sel.off('change.xc2').on('change.xc2', function() {
                    currentTruck = this.value;
                    persist();
                });
            } else {
                selTruck.onchange = (e) => {
                    currentTruck = e.target.value;
                    persist();
                };
            }
        }

        function renderTableFull() {
            if (!elRows) return;
            elRows.innerHTML = '';

            // Supaya hasil akhir DESC saat pakai prepend:
            // sort ASC dulu, lalu PREPEND → DOM jadi DESC (terbaru di atas)
            const ordered = rowsLog.slice().sort((a, b) => new Date(a.ts) - new Date(b.ts));
            ordered.forEach(r => appendRow(r));

            // KPI dari counter global (nggak perlu re-hitungan dari rows)
            updateKPI();
        }

        // ===== Work switching =====
        function setActiveWork(kind, index) {
            const t = nowMs();

            if (!running && !everStarted) {
                running = true;
                everStarted = true;
                startAt = t;
                cycleStartAt = t;
                phaseStartAt = t;
                lastSwitchAt = t;
                activeKind = kind;
                activeIndex = index;
                if (kind === 'p') lastPrimaryIndex = index;
                highlightActive();
                startLoop();
                persist();
                return;
            }

            if (!running) {
                activeKind = kind;
                activeIndex = index;
                if (kind === 'p') lastPrimaryIndex = index;
                highlightActive();
                persist();
                return;
            }

            // running: tutup segmen lama bila ada durasi
            const currentPhase = (activeKind === 'p') ? PRIMARY[activeIndex] : SECOND[activeIndex];
            const currentType = (activeKind === 'p') ? 'primary' : 'secondary';
            const phMs = phaseBase + (phaseStartAt ? (t - phaseStartAt) : 0);
            if (phaseStartAt != null && phMs > 0) {
                finalizeSegment(currentPhase, currentType, phMs);
            }
            accrueBucketsSinceSwitch();

            activeKind = kind;
            activeIndex = index;
            if (kind === 'p') lastPrimaryIndex = index;
            lastSwitchAt = t;
            phaseBase = 0;
            phaseStartAt = t;

            highlightActive();
            persist();
        }

        function highlightActive() {
            workBtns.forEach(b => b.classList.remove('active'));
            workBtns.forEach(b => {
                if (b.dataset.kind === activeKind && Number(b.dataset.index) === activeIndex) b.classList.add('active');
            });
        }

        // ===== Controls =====
        function toggleStart() {
            const t = nowMs();
            if (!running) {
                running = true;
                if (!everStarted) {
                    everStarted = true;
                    startAt = t;
                    cycleStartAt = t;
                    phaseStartAt = t;
                    lastSwitchAt = t;
                } else {
                    startAt = t;
                    cycleStartAt = t;
                    phaseStartAt = t;
                    lastSwitchAt = t;
                }
                $('#xc2-start').innerHTML = '<i class="fas fa-pause"></i> Pause';
                startLoop();
                persist();
            } else {
                accrueBucketsSinceSwitch(); // no log on pause
                totalBase += (t - startAt);
                cycleBase += (t - cycleStartAt);
                phaseBase += (t - phaseStartAt);

                running = false;
                startAt = null;
                cycleStartAt = null;
                phaseStartAt = null;
                $('#xc2-start').innerHTML = '<i class="fas fa-play"></i> Start';
                stopLoop();
                persist();
            }
        }

        function nextPrimary() {
            const t = nowMs();

            // kalau sedang secondary → lanjut primary berikutnya dari last primary
            if (activeKind === 's') {
                const base = (typeof lastPrimaryIndex === 'number') ? lastPrimaryIndex : -1;
                const target = (base >= 0) ? Math.min(base + 1, 3) : 0;

                if (!running) {
                    activeKind = 'p';
                    activeIndex = target;
                    lastPrimaryIndex = activeIndex;
                    highlightActive();
                    persist();
                    return;
                }

                const currentPhase = SECOND[activeIndex];
                const phMs = phaseBase + (phaseStartAt ? (t - phaseStartAt) : 0);
                if (phaseStartAt != null && phMs > 0) finalizeSegment(currentPhase, 'secondary', phMs);
                accrueBucketsSinceSwitch();

                activeKind = 'p';
                activeIndex = target;
                lastPrimaryIndex = activeIndex;
                lastSwitchAt = t;
                phaseBase = 0;
                phaseStartAt = t;
                highlightActive();
                persist();
                return;
            }

            // aktif primary (standar)
            if (!running) {
                activeIndex = Math.min(activeIndex + 1, 3);
                lastPrimaryIndex = activeIndex;
                highlightActive();
                persist();
                return;
            }

            const currentPhase = PRIMARY[activeIndex];
            const phMs = phaseBase + (phaseStartAt ? (t - phaseStartAt) : 0);
            if (phaseStartAt != null && phMs > 0) finalizeSegment(currentPhase, 'primary', phMs);
            accrueBucketsSinceSwitch();

            if (activeIndex < 3) {
                activeIndex += 1;
                lastPrimaryIndex = activeIndex;
                lastSwitchAt = t;
                phaseBase = 0;
                phaseStartAt = t;
                highlightActive();
                persist();
            } else {
                // Swing Empty → close cycle
                if (running) {
                    totalBase += (t - startAt);
                    startAt = t;
                    cycleBase += (t - cycleStartAt);
                    cycleStartAt = t;
                    phaseBase += (t - phaseStartAt);
                    phaseStartAt = t;
                }
                const cycleDur = cycleBase;
                kpiCycleCount += 1;
                kpiCycleSum += cycleDur;

                // reset cycle; tetap primary Digging
                cycleBase = 0;
                cycleStartAt = t;
                phaseBase = 0;
                phaseStartAt = t;
                activeKind = 'p';
                activeIndex = 0;
                lastPrimaryIndex = 0;
                lastSwitchAt = t;
                cycleNo += 1;

                highlightActive();
                persist();
                // KPI label akan di-set di renderTableFull (langsung panggil untuk refresh daftar)
                // renderTableFull();
                updateKPI(); // cukup update KPI, table jangan di-reorder

            }
        }

        function saveCycleNow() {
            if (!running) return;

            const t = nowMs();
            const currentPhase = (activeKind === 'p') ? PRIMARY[activeIndex] : SECOND[activeIndex];
            const currentType = (activeKind === 'p') ? 'primary' : 'secondary';
            const phMs = phaseBase + (phaseStartAt ? (t - phaseStartAt) : 0);
            if (phaseStartAt != null && phMs > 0) finalizeSegment(currentPhase, currentType, phMs);
            accrueBucketsSinceSwitch();

            if (running) {
                totalBase += (t - startAt);
                startAt = t;
                cycleBase += (t - cycleStartAt);
                cycleStartAt = t;
                phaseBase += (t - phaseStartAt);
                phaseStartAt = t;
            }
            const cycleDur = cycleBase;
            kpiCycleCount += 1;
            kpiCycleSum += cycleDur;

            // reset cycle; TETAP di phase aktif sekarang (tidak pindah)
            cycleBase = 0;
            cycleStartAt = t;
            phaseBase = 0;
            phaseStartAt = t;
            lastSwitchAt = t;

            if (activeKind === 'p') lastPrimaryIndex = activeIndex;

            highlightActive();
            persist();
            // renderTableFull();
            updateKPI(); // cukup update KPI
        }

        function resetAll() {
            running = false;
            stopLoop();
            startAt = null;
            totalBase = 0;
            cycleStartAt = null;
            cycleBase = 0;
            phaseStartAt = null;
            phaseBase = 0;
            lastSwitchAt = null;
            accP = [0, 0, 0, 0];
            accS = [0, 0, 0, 0, 0];
            everStarted = false;
            tick();
            persist();
        }

        function clearAll() {
            if (!confirm('Hapus semua data rows + KPI?')) return;
            rowsLog = [];
            cycleNo = 1;
            kpiCycleCount = 0;
            kpiCycleSum = 0;
            persist();
            renderTableFull();
        }

        function exportCSV() {
            // ekspor SEMUA data (gabungan) + kolom Truck
            const rows = [
                ['truck', 'cycle_no', 'phase', 'work type', 'duration_ms', 'timestamp']
            ];
            rowsLog.forEach(r => rows.push([r.truck, r.cycle, r.phase, r.workType, r.ms, r.ts]));
            const csv = rows.map(r => r.map(v => {
                const s = String(v);
                return /[",\n\r]/.test(s) ? `"${s.replace(/"/g,'""')}"` : s;
            }).join(',')).join('\r\n');

            const blob = new Blob([csv], {
                type: 'text/csv'
            });
            const url = URL.createObjectURL(blob);
            const a = Object.assign(document.createElement('a'), {
                href: url,
                download: 'excavator_cycle_rows.csv'
            });
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        }

        // ===== Initial render =====
        renderTruckOptions();
        renderTableFull();
        tick();

        // ===== Bindings =====
        $('#xc2-start').addEventListener('click', toggleStart);
        $('#xc2-next').addEventListener('click', nextPrimary);
        $('#xc2-save').addEventListener('click', saveCycleNow); // pastikan hanya binding ini (tidak ganda)
        $('#xc2-reset').addEventListener('click', resetAll);
        $('#xc2-export').addEventListener('click', exportCSV);
        $('#xc2-clear').addEventListener('click', clearAll);

        workBtns.forEach(btn => {
            btn.addEventListener('click', () => setActiveWork(btn.dataset.kind, Number(btn.dataset.index)));
        });

        document.addEventListener('keydown', (e) => {
            const tag = (document.activeElement && document.activeElement.tagName) || '';
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
            if (e.key === 'n' || e.key === 'N') nextPrimary();
        });

        // restore highlight
        highlightActive();

        function updateKPI() {
            if (elCount) elCount.textContent = kpiCycleCount;
            if (elAvg) elAvg.textContent = kpiCycleCount ? fmt(Math.round(kpiCycleSum / kpiCycleCount)) : '-';
        }
    })();
</script>