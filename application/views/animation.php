<!-- application/views/ascii_gallery.php -->
<section class="content ascii3d-wrap">
    <div class="container-fluid">
        <div class="card card-dark">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0">ASCII 3D Gallery</h3>
                <div class="card-tools d-flex align-items-center gap-2 flex-wrap" style="gap:.5rem">
                    <span class="mr-2">FPS: <b id="ascii3d-fps">–</b></span>

                    <!-- NEW: Charset -->
                    <select id="ascii3d-charset" class="form-control form-control-sm" title="Charset">
                        <option value=".,-~:;=!*#$@">Classic</option>
                        <option value=" .:-=+*#%@">Halftone</option>
                        <option value="`^&quot;,:;Il!i~+_-?][}{1)(|\\/*tfjrxnuvczXYUJCLQ0OZmwqpdbkhao*#MW&8%B@$">Dense</option>
                        <option value="  .oO@">Bubble</option>
                    </select>

                    <!-- NEW: Speed -->
                    <div class="d-flex align-items-center">
                        <small class="mr-2 text-muted">Speed</small>
                        <input id="ascii3d-speed" type="range" min="0.25" max="3" step="0.25" value="1" style="width:110px">
                    </div>

                    <!-- NEW: Perf mode -->
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="ascii3d-perf">
                        <label class="custom-control-label" for="ascii3d-perf" title="Reduce sampling">Perf</label>
                    </div>

                    <!-- NEW: Resolution -->
                    <select id="ascii3d-res" class="form-control form-control-sm" title="Resolution">
                        <option value="60x25">60×25</option>
                        <option value="80x30">80×30</option>
                        <option value="100x35">100×35</option>
                    </select>

                    <button class="btn btn-sm btn-secondary" id="ascii3d-save" title="Save current frame">Save</button>
                    <button class="btn btn-sm btn-primary" id="ascii3d-toggle">Pause</button>
                    <button class="btn btn-sm btn-outline-light" data-card-widget="maximize" title="Maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button class="btn btn-sm btn-tool" data-card-widget="collapse" title="Collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="ascii3d-bar text-muted small mb-2">
                    <span class="ascii3d-dot mr-2" title="running"></span>
                    <span>ASCII renderer • <b id="ascii3d-reslabel">60×25</b> chars / scene</span>
                </div>

                <!-- NEW: Per-scene toggles -->
                <div class="d-flex flex-wrap gap-2 mb-2" id="ascii3d-scenes-toggle" style="gap:.75rem"></div>

                <div id="ascii3d-scene" class="ascii3d-scene">
                    <pre id="ascii3d-donut"></pre>
                    <pre id="ascii3d-cube"></pre>
                    <pre id="ascii3d-ball"></pre>
                    <pre id="ascii3d-wave"></pre>
                    <pre id="ascii3d-spiral"></pre>
                    <pre id="ascii3d-pyramid"></pre>
                    <pre id="ascii3d-avatar"></pre>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* ===== Scoped styles: only affect this view ===== */
    .ascii3d-wrap .card {
        overflow: hidden;
    }

    .ascii3d-scene {
        display: grid;
        grid-template-columns: 1fr;
        gap: .5rem;
        place-items: center;
    }

    .ascii3d-bar {
        display: flex;
        align-items: center;
    }

    .ascii3d-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #22d3ee;
        display: inline-block;
        box-shadow: 0 0 6px rgba(34, 211, 238, .9);
        transition: opacity .2s ease;
    }

    .ascii3d-scene pre {
        margin: 0;
        background: #050505;
        border: 1px solid #111;
        border-radius: 8px;
        padding: 6px 8px 2px;
        width: 100%;
        max-width: 60ch;
        /* keep ~60 chars per line visible */
        overflow: hidden;
        color: #d1d5db;
        box-shadow: 0 1px 0 #111, inset 0 12px 24px rgba(0, 0, 0, .35);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 12px;
        /* will be adjusted by JS */
        line-height: 13px;
        white-space: pre;
        min-height: calc(13px * 25 + 8px);
        /* 25 rows + padding */
        cursor: pointer;
    }

    /* Bigger layout on ≥576px */
    @media (min-width: 576px) {
        .ascii3d-scene {
            grid-template-columns: 1fr 1fr;
        }
    }

    /* Wider layout on ≥992px */
    @media (min-width: 992px) {
        .ascii3d-scene {
            grid-template-columns: 1fr 1fr 1fr;
        }
    }

    /* NEW: Single-preview focus */
    .ascii3d-scene.pre-focus pre {
        display: none;
    }

    .ascii3d-scene.pre-focus pre.focused {
        display: block;
        max-width: none;
        width: 100%;
    }
</style>

<script>
    (function() {
        // ====== Config (scoped) ======
        let CHARSET = ".,-~:;=!*#$@"; // darkest → brightest
        let WIDTH = 60; // characters per row
        let HEIGHT = 25; // rows per frame
        let SPEED = 1; // animation speed multiplier
        let PERF = false;

        // Node refs
        const $fps = document.getElementById('ascii3d-fps');
        const $toggle = document.getElementById('ascii3d-toggle');
        const $resSel = document.getElementById('ascii3d-res');
        const $resLabel = document.getElementById('ascii3d-reslabel');
        const $charsetSel = document.getElementById('ascii3d-charset');
        const $speed = document.getElementById('ascii3d-speed');
        const $perf = document.getElementById('ascii3d-perf');
        const $save = document.getElementById('ascii3d-save');
        const $toggles = document.getElementById('ascii3d-scenes-toggle');

        const nodes = {
            donut: document.getElementById('ascii3d-donut'),
            cube: document.getElementById('ascii3d-cube'),
            ball: document.getElementById('ascii3d-ball'),
            wave: document.getElementById('ascii3d-wave'),
            spiral: document.getElementById('ascii3d-spiral'),
            pyramid: document.getElementById('ascii3d-pyramid'),
            scene: document.getElementById('ascii3d-scene'),
            avatar: document.getElementById('ascii3d-avatar'),
        };

        // Responsive monospace sizing to keep ~WIDTH ch visible
        function fitPreText() {
            const wrapW = nodes.scene.clientWidth || nodes.scene.offsetWidth || 600;
            const cols = Math.min(WIDTH, 100); // cap
            // Estimate character width ≈ 0.6em; try to keep WIDTH ch + padding inside
            const target = Math.max(10, Math.min(14, Math.floor((wrapW / 2) / (cols + 4))));
            const lineH = Math.round(target * 1.08);
            nodes.scene.querySelectorAll('pre').forEach(pre => {
                pre.style.fontSize = target + 'px';
                pre.style.lineHeight = lineH + 'px';
                pre.style.minHeight = `calc(${lineH}px * ${HEIGHT} + 8px)`;
                pre.style.maxWidth = `${WIDTH}ch`;
            });
        }
        window.addEventListener('resize', fitPreText, {
            passive: true
        });

        // ===== Utilities =====
        const clamp = (n, a, b) => n < a ? a : (n > b ? b : n);

        function bufferToOutput(buffer) {
            let out = '';
            for (let i = 0; i < buffer.length; i++) {
                out += buffer[i];
                if ((i + 1) % WIDTH === 0) out += '\n';
            }
            return out;
        }

        function rotateXYZ(x, y, z, A, B, C) {
            const cosA = Math.cos(A),
                sinA = Math.sin(A);
            const cosB = Math.cos(B),
                sinB = Math.sin(B);
            const cosC = Math.cos(C),
                sinC = Math.sin(C);
            const y1 = y * cosA - z * sinA;
            const z1 = y * sinA + z * cosA;
            const x1 = x * cosB + z1 * sinB;
            const z2 = -x * sinB + z1 * cosB;
            const x2 = x1 * cosC - y1 * sinC;
            const y2 = x1 * sinC + y1 * cosC;
            return [x2, y2, z2];
        }

        function drawLine(buf, zbuf, p1, p2) {
            let x0 = p1.x,
                y0 = p1.y,
                x1 = p2.x,
                y1 = p2.y;
            const dx = Math.abs(x1 - x0),
                dy = Math.abs(y1 - y0);
            const sx = x0 < x1 ? 1 : -1,
                sy = y0 < y1 ? 1 : -1;
            const steps = Math.max(dx, dy) || 1;
            const oozStep = (p2.ooz - p1.ooz) / steps;
            let err = dx - dy,
                ooz = p1.ooz;
            while (true) {
                if (x0 >= 0 && x0 < WIDTH && y0 >= 0 && y0 < HEIGHT) {
                    const idx = x0 + WIDTH * y0;
                    if (ooz > zbuf[idx]) {
                        zbuf[idx] = ooz;
                        const shade = clamp(Math.floor(ooz * 40), 0, CHARSET.length - 1);
                        buf[idx] = CHARSET[shade];
                    }
                }
                if (x0 === x1 && y0 === y1) break;
                const e2 = 2 * err;
                if (e2 > -dy) {
                    err -= dy;
                    x0 += sx;
                }
                if (e2 < dx) {
                    err += dx;
                    y0 += sy;
                }
                ooz += oozStep;
            }
        }
        // NEW: speed-scaled increment helper
        function inc(obj, k, v) {
            obj[k] += v * SPEED;
        }

        // ===== Renderers =====
        function renderBall(state, buf, zbuf) {
            const {
                A,
                B
            } = state;
            const R = 10,
                zOff = 18;
            const dT = PERF ? 0.18 : 0.12;
            for (let t = 0; t <= Math.PI; t += dT) {
                const ct = Math.cos(t),
                    st = Math.sin(t);
                for (let p = 0; p < Math.PI * 2; p += dT) {
                    const cp = Math.cos(p),
                        sp = Math.sin(p);
                    const x = R * st * cp,
                        y = R * st * sp,
                        z = R * ct;
                    const [xr, yr, zr] = rotateXYZ(x, y, z, A, B, 0);
                    const denom = (zr + zOff),
                        ooz = 1 / denom;
                    const xp = Math.floor(WIDTH / 2 + xr * ooz * 20);
                    const yp = Math.floor(HEIGHT / 2 + yr * ooz * 10);
                    if (xp >= 0 && xp < WIDTH && yp >= 0 && yp < HEIGHT) {
                        const idx = xp + WIDTH * yp;
                        if (ooz > zbuf[idx]) {
                            zbuf[idx] = ooz;
                            const L = ct * Math.cos(A) + st * Math.sin(A);
                            const shade = clamp(Math.floor(L * 6 + 6), 0, CHARSET.length - 1);
                            buf[idx] = CHARSET[shade];
                        }
                    }
                }
            }
            inc(state, 'A', 0.05);
            inc(state, 'B', 0.03);
        }

        function renderDonut(state, buf, zbuf) {
            const {
                A,
                B
            } = state;
            const R = 10,
                r = 4,
                zOff = 22,
                scaleX = 20,
                scaleY = 10;
            const dTheta = PERF ? 0.10 : 0.06;
            const dPhi = PERF ? 0.06 : 0.03;
            const light = (() => {
                let lx = -0.3,
                    ly = 0.6,
                    lz = -1.0;
                const len = Math.hypot(lx, ly, lz) || 1;
                return [lx / len, ly / len, lz / len];
            })();
            for (let theta = 0; theta < Math.PI * 2; theta += dTheta) {
                const cT = Math.cos(theta),
                    sT = Math.sin(theta);
                for (let phi = 0; phi < Math.PI * 2; phi += dPhi) {
                    const cP = Math.cos(phi),
                        sP = Math.sin(phi);
                    const cx = (R + r * cT) * cP,
                        cy = (R + r * cT) * sP,
                        cz = r * sT;
                    let nx = cT * cP,
                        ny = cT * sP,
                        nz = sT;
                    const nlen = Math.hypot(nx, ny, nz) || 1;
                    nx /= nlen;
                    ny /= nlen;
                    nz /= nlen;
                    const [xr, yr, zr] = rotateXYZ(cx, cy, cz, A, B, 0);
                    const [nxr, nyr, nzr] = rotateXYZ(nx, ny, nz, A, B, 0);
                    const denom = zr + zOff;
                    if (denom <= 0.0001) continue;
                    const ooz = 1 / denom;
                    const xp = Math.floor(WIDTH / 2 + xr * ooz * scaleX);
                    const yp = Math.floor(HEIGHT / 2 - yr * ooz * scaleY);
                    if (xp < 0 || xp >= WIDTH || yp < 0 || yp >= HEIGHT) continue;
                    const idx = xp + WIDTH * yp;
                    if (ooz > zbuf[idx]) {
                        zbuf[idx] = ooz;
                        let L = nxr * light[0] + nyr * light[1] + nzr * light[2];
                        L = (L + 1) * 0.5;
                        const shade = clamp(Math.floor(L * (CHARSET.length - 1)), 0, CHARSET.length - 1);
                        buf[idx] = CHARSET[shade];
                    }
                }
            }
            inc(state, 'A', 0.035);
            inc(state, 'B', 0.020);
        }

        function renderCube(state, buf, zbuf) {
            const {
                A,
                B,
                C
            } = state;
            const S = 9,
                zOff = 26,
                scaleX = 20,
                scaleY = 10;
            const verts = [
                [-1, -1, -1],
                [1, -1, -1],
                [1, 1, -1],
                [-1, 1, -1],
                [-1, -1, 1],
                [1, -1, 1],
                [1, 1, 1],
                [-1, 1, 1]
            ].map(([x, y, z]) => [x * S, y * S, z * S]);
            const edges = [
                [0, 1],
                [1, 2],
                [2, 3],
                [3, 0],
                [4, 5],
                [5, 6],
                [6, 7],
                [7, 4],
                [0, 4],
                [1, 5],
                [2, 6],
                [3, 7]
            ];
            const pts = verts.map(([x, y, z]) => {
                const [xr, yr, zr] = rotateXYZ(x, y, z, A, B, C);
                const ooz = 1 / (zr + zOff);
                return {
                    x: Math.floor(WIDTH / 2 + xr * ooz * scaleX),
                    y: Math.floor(HEIGHT / 2 - yr * ooz * scaleY),
                    ooz
                };
            });
            for (const [i, j] of edges) drawLine(buf, zbuf, pts[i], pts[j]);
            inc(state, 'A', 0.02);
            inc(state, 'B', 0.03);
            inc(state, 'C', 0.01);
        }

        function renderWave(state, buf, zbuf) {
            const t = state.t;
            const scale = 20;
            const xStep = PERF ? 2 : 1;
            for (let x = -WIDTH / 2; x < WIDTH / 2; x += xStep) {
                for (let z = -10; z < 10; z += (PERF ? 2 : 1)) {
                    const y = Math.sin((x / 5) + t) * Math.cos((z / 5) + t) * 5;
                    const [xr, yr, zr] = rotateXYZ(x, y, z + 20, 0, t * 0.2, 0);
                    const ooz = 1 / (zr + 20);
                    const xp = Math.floor(WIDTH / 2 + xr * ooz * scale);
                    const yp = Math.floor(HEIGHT / 2 - yr * ooz * (scale * 0.5));
                    if (xp >= 0 && xp < WIDTH && yp >= 0 && yp < HEIGHT) {
                        const idx = xp + WIDTH * yp;
                        if (ooz > zbuf[idx]) {
                            zbuf[idx] = ooz;
                            const L = (y + 5) / 10;
                            const shade = clamp(Math.floor(L * 10), 0, CHARSET.length - 1);
                            buf[idx] = CHARSET[shade];
                        }
                    }
                }
            }
            state.t += 0.10 * SPEED;
        }

        function renderSpiral(state, buf, zbuf) {
            const t = state.t;
            const arms = 3,
                scale = 20;
            const N = PERF ? 300 : 600;
            for (let i = 0; i < N; i++) {
                const r = Math.random() * 10;
                const angle = r * 2 + t;
                const armOffset = (Math.floor(Math.random() * arms)) * (2 * Math.PI / arms);
                const x = Math.cos(angle + armOffset) * r;
                const y = Math.sin(angle + armOffset) * r;
                const z = (Math.random() - 0.5) * 5;
                const [xr, yr, zr] = rotateXYZ(x, y, z + 20, t * 0.1, t * 0.2, 0);
                const ooz = 1 / (zr + 20);
                const xp = Math.floor(WIDTH / 2 + xr * ooz * scale);
                const yp = Math.floor(HEIGHT / 2 - yr * ooz * (scale * 0.5));
                if (xp >= 0 && xp < WIDTH && yp >= 0 && yp < HEIGHT) {
                    const idx = xp + WIDTH * yp;
                    if (ooz > zbuf[idx]) {
                        zbuf[idx] = ooz;
                        const L = (z + 3) / 6;
                        const shade = clamp(Math.floor(L * 10), 0, CHARSET.length - 1);
                        buf[idx] = CHARSET[shade];
                    }
                }
            }
            state.t += 0.05 * SPEED;
        }

        function renderPyramid(state, buf, zbuf) {
            const {
                A,
                B,
                C
            } = state;
            const verts = [
                [0, 1, 0],
                [-1, -1, -1],
                [1, -1, -1],
                [1, -1, 1],
                [-1, -1, 1]
            ].map(([x, y, z]) => [x * 9, y * 9, z * 9]);
            const edges = [
                [0, 1],
                [0, 2],
                [0, 3],
                [0, 4],
                [1, 2],
                [2, 3],
                [3, 4],
                [4, 1]
            ];
            const zOff = 22,
                scaleX = 20,
                scaleY = 10;
            const pts = verts.map(([x, y, z]) => {
                const [xr, yr, zr] = rotateXYZ(x, y, z, A, B, C);
                const ooz = 1 / (zr + zOff);
                return {
                    x: Math.floor(WIDTH / 2 + xr * ooz * scaleX),
                    y: Math.floor(HEIGHT / 2 - yr * ooz * scaleY),
                    ooz
                };
            });
            for (const [i, j] of edges) drawLine(buf, zbuf, pts[i], pts[j]);
            inc(state, 'A', 0.03);
            inc(state, 'B', 0.025);
            inc(state, 'C', 0.02);
        }

        function renderAvatar(state, buf, zbuf) {
            const t = state.t;
            const cx = WIDTH / 2,
                cy = HEIGHT / 2;

            // Geometri
            const R = 0.82,
                headR = 0.18;
            const shoulderRx = 0.46,
                shoulderRy = 0.26;

            // Cahaya untuk latar oval
            let lx = Math.cos(t * 0.6) * 0.6,
                ly = -0.2,
                lz = -0.8;
            {
                const L = Math.hypot(lx, ly, lz) || 1;
                lx /= L;
                ly /= L;
                lz /= L;
            }

            // Ring oval
            const ringPhase = (Math.sin(t * 1.8) + 1) * 0.5;
            const ringWidth = 0.010 + ringPhase * 0.010;
            const ringAt = 0.88 * R;

            const z = 9.5,
                ooz = 1 / (z + 20);
            const oozOutline = ooz + 1e-6; // outline selalu di atas

            const norm = (v) => v / (HEIGHT * 0.5);

            function inSilhouette(nx, ny) {
                const inHead = (nx * nx + (ny + 0.10) * (ny + 0.10)) <= (headR * headR);
                const sx = nx / shoulderRx,
                    sy = (ny - 0.22) / shoulderRy;
                const inShoulder = (sx * sx + sy * sy) <= 1 && ny > 0.05;
                return inHead || inShoulder;
            }

            for (let y = 0; y < HEIGHT; y++) {
                for (let x = 0; x < WIDTH; x++) {
                    const nx = norm(x - cx),
                        ny = norm(y - cy);
                    const r = Math.hypot(nx, ny);
                    if (r > R) continue;

                    const idx = x + WIDTH * y;

                    // --- Siluet & Outline (kosong) ---
                    const sil = inSilhouette(nx, ny);
                    let edge = false;
                    if (sil) {
                        // 8-neighbor untuk garis sedikit lebih tebal
                        const n = [
                            inSilhouette(norm(x + 1 - cx), ny),
                            inSilhouette(norm(x - 1 - cx), ny),
                            inSilhouette(nx, norm(y + 1 - cy)),
                            inSilhouette(nx, norm(y - 1 - cy)),
                            inSilhouette(norm(x + 1 - cx), norm(y + 1 - cy)),
                            inSilhouette(norm(x - 1 - cx), norm(y + 1 - cy)),
                            inSilhouette(norm(x + 1 - cx), norm(y - 1 - cy)),
                            inSilhouette(norm(x - 1 - cx), norm(y - 1 - cy)),
                        ];
                        edge = n.some(v => !v);
                    }

                    if (edge) {
                        // Stroke “kosong”: tulis spasi & kunci depth
                        if (oozOutline > zbuf[idx]) {
                            zbuf[idx] = oozOutline;
                            buf[idx] = ' ';
                        }
                        continue; // jangan gambar apa pun di sini
                    }
                    if (sil) {
                        // Isi siluet transparan: biarkan latar menggambar
                        continue;
                    }

                    // --- Latar oval + ring ---
                    if (ooz > zbuf[idx]) {
                        const nz = Math.sqrt(Math.max(R * R - r * r, 0));
                        const nlen = Math.hypot(nx, ny, nz) || 1;
                        const nxn = nx / nlen,
                            nyn = ny / nlen,
                            nzn = nz / nlen;
                        let L = nxn * lx + nyn * ly + nzn * lz;
                        L = Math.max(0, (L + 1) * 0.5);

                        const dr = Math.abs(r - ringAt);
                        const ring = Math.max(0, 1 - (dr / ringWidth));
                        const accent = Math.min(1, ring * 0.9);

                        const I = Math.max(0, Math.min(1, L * 0.85 + accent * 0.15));
                        const shadeIdx = Math.max(0, Math.min(CHARSET.length - 1, Math.floor(I * (CHARSET.length - 1))));

                        zbuf[idx] = ooz;
                        buf[idx] = CHARSET[shadeIdx];
                    }
                }
            }
            state.t += 0.05 * SPEED;
        }

        // ===== Engine =====
        const scenes = [{
                id: 'donut',
                node: nodes.donut,
                state: {
                    A: 0,
                    B: 0
                },
                render: renderDonut
            },
            {
                id: 'cube',
                node: nodes.cube,
                state: {
                    A: 0,
                    B: 0,
                    C: 0
                },
                render: renderCube
            },
            {
                id: 'ball',
                node: nodes.ball,
                state: {
                    A: 0,
                    B: 0
                },
                render: renderBall
            },
            {
                id: 'wave',
                node: nodes.wave,
                state: {
                    t: 0
                },
                render: renderWave
            },
            {
                id: 'spiral',
                node: nodes.spiral,
                state: {
                    t: 0
                },
                render: renderSpiral
            },
            {
                id: 'pyramid',
                node: nodes.pyramid,
                state: {
                    A: 0,
                    B: 0,
                    C: 0
                },
                render: renderPyramid
            },
            {
                id: 'avatar',
                node: nodes.avatar,
                state: {
                    t: 0
                },
                render: renderAvatar
            },
        ];

        let total = WIDTH * HEIGHT;
        let buffer = new Array(total).fill(' ');
        let zBuffer = new Float32Array(total).fill(-Infinity);

        function clearBuffers() {
            buffer.fill(' ');
            zBuffer.fill(-Infinity);
        }

        function reinitBuffers() {
            total = WIDTH * HEIGHT;
            buffer = new Array(total).fill(' ');
            zBuffer = new Float32Array(total).fill(-Infinity);
            $resLabel.textContent = `${WIDTH}×${HEIGHT}`;
            fitPreText();
        }

        // ===== Controls wiring =====
        $charsetSel.addEventListener('change', e => {
            CHARSET = e.target.value;
        });
        $speed.addEventListener('input', e => {
            SPEED = parseFloat(e.target.value);
        });
        $perf.addEventListener('change', e => {
            PERF = e.target.checked;
        });

        $resSel.addEventListener('change', e => {
            const [w, h] = e.target.value.split('x').map(Number);
            WIDTH = w;
            HEIGHT = h;
            reinitBuffers();
        });

        // NEW: per-scene toggle switches
        scenes.forEach(s => {
            const id = `chk-${s.id}`;
            const w = document.createElement('div');
            w.className = 'custom-control custom-switch mr-3 mb-1';
            w.innerHTML =
                `<input type="checkbox" class="custom-control-input" id="${id}" checked>
       <label class="custom-control-label" for="${id}">${s.id}</label>`;
            $toggles.appendChild(w);
            s.enabled = true;
            w.querySelector('input').addEventListener('change', e => s.enabled = e.target.checked);
        });

        // NEW: single-preview focus on click
        let focusId = null;
        nodes.scene.addEventListener('click', e => {
            const pre = e.target.closest('pre');
            if (!pre) return;
            const scene = scenes.find(s => s.node === pre);
            if (!scene) return;
            focusId = (focusId === scene.id) ? null : scene.id;
            nodes.scene.classList.toggle('pre-focus', !!focusId);
            scenes.forEach(s => {
                s.node.classList.toggle('focused', focusId === s.id);
                s.enabled = !focusId || (focusId === s.id);
            });
        });

        // NEW: export current visible frames
        $save.addEventListener('click', () => {
            const parts = scenes
                .filter(s => s.enabled)
                .map(s => `=== ${s.id.toUpperCase()} ===\n${s.node.textContent}`);
            const blob = new Blob([parts.join('\n\n')], {
                type: 'text/plain'
            });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = `ascii3d-${WIDTH}x${HEIGHT}-${Date.now()}.txt`;
            a.click();
            URL.revokeObjectURL(a.href);
        });

        // Play/pause button
        let playing = true;
        $toggle.addEventListener('click', () => {
            playing = !playing;
            $toggle.textContent = playing ? 'Pause' : 'Play';
            document.querySelector('.ascii3d-dot').style.opacity = playing ? '1' : '.35';
            last = performance.now();
        });

        // NEW: auto-pause when hidden / off-screen
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                playing = false;
                $toggle.textContent = 'Play';
                document.querySelector('.ascii3d-dot').style.opacity = '.35';
            }
        });
        new IntersectionObserver((entries) => {
            const vis = entries[0].isIntersecting;
            if (!vis) {
                playing = false;
                $toggle.textContent = 'Play';
                document.querySelector('.ascii3d-dot').style.opacity = '.35';
            }
        }, {
            threshold: 0.05
        }).observe(document.querySelector('.ascii3d-wrap'));

        // ===== Main loop =====
        let last = performance.now(),
            fps = 0;

        function frame(now) {
            if (playing) {
                const dt = now - last;
                last = now;
                fps = Math.round(1000 / dt);
                $fps.textContent = isFinite(fps) ? String(fps) : '–';

                for (const s of scenes) {
                    if (!s.enabled) {
                        s.node.textContent = '';
                        continue;
                    }
                    clearBuffers();
                    s.render(s.state, buffer, zBuffer);
                    s.node.textContent = bufferToOutput(buffer);
                }
            }
            requestAnimationFrame(frame);
        }

        // Boot
        fitPreText();
        requestAnimationFrame(frame);
    })();
</script>