<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tetris — HTML + JS (Lengkap)</title>
    <style>
        :root {
            --cell: 28px;
            --gap: 2px;
            --bg: #0f1115;
            --panel: #171a21;
            --panel2: #1e2230;
            --accent: #00c2ff;
            --text: #e8eefb;
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
        }

        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px
        }

        .app {
            display: grid;
            gap: 12px;
            grid-template-columns: 220px auto 220px;
            align-items: start
        }

        .card {
            background: var(--panel);
            border: 1px solid #23283a;
            border-radius: 14px;
            padding: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .25)
        }

        .title {
            margin: 0 0 10px;
            font-size: 18px;
            font-weight: 700
        }

        /* Board */
        .board {
            position: relative;
            background: #2b3146;
            padding: 4px;
            border-radius: 12px
        }

        .grid {
            display: grid;
            gap: var(--gap);
            background: #36405e;
            padding: var(--gap);
            border-radius: 10px;
            grid-template-columns: repeat(10, var(--cell));
            grid-template-rows: repeat(20, var(--cell));
        }

        .cell {
            width: var(--cell);
            height: var(--cell);
            border-radius: 6px;
            border: 1px solid rgba(0, 0, 0, .35)
        }

        .I {
            background: #00c2ff;
        }

        .O {
            background: #ffd500;
        }

        .T {
            background: #a64dff;
        }

        .S {
            background: #00e676;
        }

        .Z {
            background: #ff5252;
        }

        .J {
            background: #3d5afe;
        }

        .L {
            background: #ff9100;
        }

        .ghost {
            background: linear-gradient(180deg, rgba(255, 255, 255, .25), rgba(255, 255, 255, .08));
        }

        /* Mini 4x4 matrix */
        .mini {
            display: grid;
            gap: 2px;
            background: #36405e;
            padding: 2px;
            border-radius: 8px;
            grid-template-columns: repeat(4, 18px);
            grid-template-rows: repeat(4, 18px)
        }

        .mini .cell {
            width: 18px;
            height: 18px;
            border-radius: 4px
        }

        /* Overlay */
        .overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .55);
            border-radius: 12px
        }

        .overlay>.panel {
            background: var(--panel2);
            padding: 18px 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #2a3149
        }

        /* Controls (mobile) */
        .controls {
            display: none;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-top: 10px
        }

        .btn {
            background: #263049;
            border: 1px solid #2f3a5a;
            color: var(--text);
            padding: 10px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer
        }

        .btn:active {
            transform: scale(.98)
        }

        /* Responsive */
        @media(max-width:980px) {
            .app {
                grid-template-columns: auto;
            }

            .side {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px
            }

            .right {
                order: 3
            }

            .left {
                order: 2
            }

            .center {
                order: 1
            }

            .controls {
                display: grid
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="app">
            <!-- LEFT HUD -->
            <div class="side left card">
                <h3 class="title">Status</h3>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px">
                    <div class="card" style="padding:10px">
                        <div>Score</div>
                        <div id="score" style="font-size:22px; font-variant-numeric:tabular-nums">0</div>
                    </div>
                    <div class="card" style="padding:10px">
                        <div>High</div>
                        <div id="high" style="font-size:22px; font-variant-numeric:tabular-nums">0</div>
                    </div>
                    <div class="card" style="padding:10px">
                        <div>Level</div>
                        <div id="level" style="font-size:22px">1</div>
                    </div>
                    <div class="card" style="padding:10px">
                        <div>Lines</div>
                        <div id="lines" style="font-size:22px">0</div>
                    </div>
                </div>
                <div class="card" style="margin-top:10px">
                    <h4 style="margin:0 0 6px">Hold (C)</h4>
                    <div id="hold" class="mini"></div>
                </div>
            </div>

            <!-- CENTER BOARD -->
            <div class="center card board">
                <div id="grid" class="grid"></div>
                <div id="overlay" class="overlay">
                    <div class="panel">
                        <h2 id="ov-title" style="margin:0 0 8px">TETRIS</h2>
                        <div id="ov-sub" style="opacity:.85; margin-bottom:10px">Tekan <b>R</b> untuk mulai</div>
                        <div style="text-align:left; font-size:14px; opacity:.9">
                            <div>←/A & →/D = Geser</div>
                            <div>↓/S = Soft drop, <b>Spasi</b> = Hard drop</div>
                            <div>↑/W/X = Rotasi searah jarum jam, Z/Q = lawan</div>
                            <div>C = Hold, P = Pause, R = Restart</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT HUD -->
            <div class="side right card">
                <h3 class="title">Next</h3>
                <div id="next" style="display:grid; gap:8px"></div>
                <div class="controls">
                    <button class="btn" id="btn-left">←</button>
                    <button class="btn" id="btn-rotl">⟲</button>
                    <button class="btn" id="btn-drop">⤓</button>
                    <button class="btn" id="btn-rotr">⟳</button>
                    <button class="btn" id="btn-right">→</button>
                    <button class="btn" id="btn-hold" style="grid-column: span 2">Hold</button>
                    <button class="btn" id="btn-hard" style="grid-column: span 3">Hard Drop</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ====== GAME CONSTANTS ======
        const COLS = 10,
            ROWS = 20,
            PREVIEW = 5;
        const TYPES = ["I", "O", "T", "S", "Z", "J", "L"];
        const LINE_SCORES = [0, 100, 300, 500, 800];
        const COLORS = {
            I: "I",
            O: "O",
            T: "T",
            S: "S",
            Z: "Z",
            J: "J",
            L: "L",
            ghost: "ghost"
        };

        // 4x4 rotation matrices
        const SHAPES = {
            I: [
                [
                    [0, 0, 0, 0],
                    [1, 1, 1, 1],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 0, 1, 0],
                    [0, 0, 1, 0],
                    [0, 0, 1, 0],
                    [0, 0, 1, 0]
                ],
                [
                    [0, 0, 0, 0],
                    [1, 1, 1, 1],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 0, 1, 0],
                    [0, 0, 1, 0],
                    [0, 0, 1, 0],
                    [0, 0, 1, 0]
                ],
            ],
            O: [
                [
                    [0, 1, 1, 0],
                    [0, 1, 1, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 1, 0],
                    [0, 1, 1, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 1, 0],
                    [0, 1, 1, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 1, 0],
                    [0, 1, 1, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
            ],
            T: [
                [
                    [0, 1, 0, 0],
                    [1, 1, 1, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 0, 0],
                    [0, 1, 1, 0],
                    [0, 1, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 0, 0, 0],
                    [1, 1, 1, 0],
                    [0, 1, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 0, 0],
                    [1, 1, 0, 0],
                    [0, 1, 0, 0],
                    [0, 0, 0, 0]
                ],
            ],
            S: [
                [
                    [0, 1, 1, 0],
                    [1, 1, 0, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 0, 0],
                    [0, 1, 1, 0],
                    [0, 0, 1, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 1, 0],
                    [1, 1, 0, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 0, 0],
                    [0, 1, 1, 0],
                    [0, 0, 1, 0],
                    [0, 0, 0, 0]
                ],
            ],
            Z: [
                [
                    [1, 1, 0, 0],
                    [0, 1, 1, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 0, 1, 0],
                    [0, 1, 1, 0],
                    [0, 1, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [1, 1, 0, 0],
                    [0, 1, 1, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 0, 1, 0],
                    [0, 1, 1, 0],
                    [0, 1, 0, 0],
                    [0, 0, 0, 0]
                ],
            ],
            J: [
                [
                    [1, 0, 0, 0],
                    [1, 1, 1, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 1, 0],
                    [0, 1, 0, 0],
                    [0, 1, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 0, 0, 0],
                    [1, 1, 1, 0],
                    [0, 0, 1, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 0, 0],
                    [0, 1, 0, 0],
                    [1, 1, 0, 0],
                    [0, 0, 0, 0]
                ],
            ],
            L: [
                [
                    [0, 0, 1, 0],
                    [1, 1, 1, 0],
                    [0, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 1, 0, 0],
                    [0, 1, 0, 0],
                    [0, 1, 1, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [0, 0, 0, 0],
                    [1, 1, 1, 0],
                    [1, 0, 0, 0],
                    [0, 0, 0, 0]
                ],
                [
                    [1, 1, 0, 0],
                    [0, 1, 0, 0],
                    [0, 1, 0, 0],
                    [0, 0, 0, 0]
                ],
            ],
        };

        // ====== STATE ======
        let board = emptyBoard();
        let cur = null; // {type, rotation, x, y}
        let queue = makeBag();
        let hold = null;
        let canHold = true;
        let score = 0,
            lines = 0,
            level = 1;
        let status = "menu";
        let last = 0,
            acc = 0;
        let raf = null;
        const highEl = document.querySelector('#high');
        let high = Number(localStorage.getItem('tetrisHigh') || 0);
        highEl.textContent = high;

        // ====== HELPERS ======
        function emptyBoard() {
            return Array.from({
                length: ROWS
            }, () => Array(COLS).fill(0));
        }

        function shuffle(a) {
            for (let i = a.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [a[i], a[j]] = [a[j], a[i]];
            }
            return a
        }

        function makeBag() {
            return shuffle([...TYPES]);
        }

        function startingPiece(type) {
            return {
                type,
                rotation: 0,
                x: 3,
                y: 0
            };
        }

        function canPlace(p, dx = 0, dy = 0, dr = 0) {
            const rot = ((p.rotation + dr) % 4 + 4) % 4;
            const sh = SHAPES[p.type][rot];
            const px = p.x + dx,
                py = p.y + dy;
            for (let r = 0; r < 4; r++) {
                for (let c = 0; c < 4; c++) {
                    if (sh[r][c]) {
                        const x = px + c,
                            y = py + r;
                        if (x < 0 || x >= COLS || y >= ROWS) return false;
                        if (y >= 0 && board[y][x] !== 0) return false;
                    }
                }
            }
            return true;
        }

        function merge(p) {
            const sh = SHAPES[p.type][p.rotation];
            for (let r = 0; r < 4; r++) {
                for (let c = 0; c < 4; c++) {
                    if (sh[r][c]) {
                        const x = p.x + c,
                            y = p.y + r;
                        if (y >= 0) board[y][x] = p.type;
                    }
                }
            }
        }

        function clearLines() {
            let cnt = 0;
            const nb = [];
            for (let r = 0; r < ROWS; r++) {
                if (board[r].every(v => v !== 0)) cnt++;
                else nb.push(board[r]);
            }
            while (nb.length < ROWS) nb.unshift(Array(COLS).fill(0));
            board = nb;
            return cnt;
        }

        function getGhostY(p) {
            let y = p.y;
            while (canPlace({
                    ...p,
                    y: y + 1
                })) y++;
            return y;
        }

        function speedMs(lv) {
            return Math.max(1000 - (lv - 1) * 75, 80);
        } // simple curve

        // ====== RENDER ======
        const gridEl = document.getElementById('grid');
        const scoreEl = document.getElementById('score');
        const linesEl = document.getElementById('lines');
        const levelEl = document.getElementById('level');
        const overlayEl = document.getElementById('overlay');
        const ovTitle = document.getElementById('ov-title');
        const ovSub = document.getElementById('ov-sub');
        const nextEl = document.getElementById('next');
        const holdEl = document.getElementById('hold');

        function draw() {
            // base board
            gridEl.innerHTML = '';
            for (let r = 0; r < ROWS; r++) {
                for (let c = 0; c < COLS; c++) {
                    const d = document.createElement('div');
                    d.className = 'cell' + (board[r][c] ? (' ' + COLORS[board[r][c]]) : '');
                    gridEl.appendChild(d);
                }
            }
            // ghost + current
            if (cur) {
                const gy = getGhostY(cur);
                const gshape = SHAPES[cur.type][cur.rotation];
                for (let r = 0; r < 4; r++) {
                    for (let c = 0; c < 4; c++) {
                        if (gshape[r][c]) {
                            const x = cur.x + c,
                                y = gy + r;
                            if (y >= 0 && x >= 0 && x < COLS && y < ROWS) {
                                const idx = y * COLS + x;
                                gridEl.children[idx].className = 'cell ghost';
                            }
                        }
                    }
                }
                const shape = SHAPES[cur.type][cur.rotation];
                for (let r = 0; r < 4; r++) {
                    for (let c = 0; c < 4; c++) {
                        if (shape[r][c]) {
                            const x = cur.x + c,
                                y = cur.y + r;
                            if (y >= 0 && x >= 0 && x < COLS && y < ROWS) {
                                const idx = y * COLS + x;
                                gridEl.children[idx].className = 'cell ' + COLORS[cur.type];
                            }
                        }
                    }
                }
            }
        }

        function drawMini(target, type) {
            target.innerHTML = '';
            const m = document.createElement('div');
            m.className = 'mini';
            target.appendChild(m);
            const mat = type ? SHAPES[type][0] : Array.from({
                length: 4
            }, () => Array(4).fill(0));
            for (let r = 0; r < 4; r++) {
                for (let c = 0; c < 4; c++) {
                    const d = document.createElement('div');
                    d.className = 'cell' + (mat[r][c] ? (" " + COLORS[type]) : '');
                    m.appendChild(d);
                }
            }
        }

        function refreshHUD() {
            scoreEl.textContent = score;
            linesEl.textContent = lines;
            levelEl.textContent = level;
        }

        function drawNext() {
            nextEl.innerHTML = '';
            for (let i = 0; i < Math.min(PREVIEW, queue.length); i++) {
                const slot = document.createElement('div');
                slot.className = 'card';
                slot.style.padding = '8px';
                const box = document.createElement('div');
                drawMini(slot, queue[i]);
                nextEl.appendChild(slot);
            }
        }

        function drawHold() {
            drawMini(holdEl, hold || undefined);
        }

        // ====== GAME FLOW ======
        function spawn() {
            if (queue.length < PREVIEW + 1) queue.push(...makeBag());
            const type = queue.shift();
            cur = startingPiece(type);
            canHold = true;
            if (!canPlace(cur)) gameOver();
        }

        function lock() {
            merge(cur);
            const got = clearLines();
            if (got) {
                score += (LINE_SCORES[got] || 0) * level;
                lines += got;
                const nl = Math.floor(lines / 10) + 1;
                if (nl !== level) level = nl;
                if (score > high) {
                    high = score;
                    localStorage.setItem('tetrisHigh', String(high));
                    highEl.textContent = high;
                }
                refreshHUD();
            }

            // TOP-OUT CHECK: kalau baris paling atas terisi, game over
            for (let c = 0; c < COLS; c++) {
                if (board[0][c] !== 0) {
                    gameOver();
                    return;
                }
            }

            cur = null;
            spawn();
            drawNext();
            draw();
        }

        function gameOver() {
            status = 'gameover';
            overlayEl.style.display = 'flex';
            ovTitle.textContent = 'Game Over';
            ovSub.textContent = 'Tekan R untuk main lagi';
            cancelLoop();
        }

        function start() {
            board = emptyBoard();
            queue = makeBag();
            cur = null;
            hold = null;
            canHold = true;
            score = 0;
            lines = 0;
            level = 1;
            status = 'playing';
            overlayEl.style.display = 'none';
            refreshHUD();
            drawNext();
            spawn();
            startLoop();
            drawHold();
        }

        // ====== LOOP ======
        function startLoop() {
            last = 0;
            acc = 0;
            if (raf) cancelAnimationFrame(raf);
            raf = requestAnimationFrame(tick);
        }

        function cancelLoop() {
            if (raf) cancelAnimationFrame(raf);
            raf = null;
        }

        function tick(t) {
            if (!last) last = t;
            const dt = t - last;
            last = t;
            acc += dt;
            const sp = speedMs(level);
            while (acc >= sp) {
                acc -= sp;
                step();
            }
            raf = requestAnimationFrame(tick);
        }

        function step() {
            if (!cur) return;
            if (canPlace(cur, 0, 1)) cur.y++;
            else lock();
            draw();
        }

        // ====== INPUT ======
        function tryMove(dx, dy) {
            if (!cur) return;
            if (canPlace(cur, dx, dy)) {
                cur.x += dx;
                cur.y += dy;
                draw();
            }
        }

        function rotate(dir) {
            if (!cur) return;
            const nr = ((cur.rotation + dir) % 4 + 4) % 4;
            const kicks = [{
                x: 0,
                y: 0
            }, {
                x: -1,
                y: 0
            }, {
                x: 1,
                y: 0
            }, {
                x: -2,
                y: 0
            }, {
                x: 2,
                y: 0
            }, {
                x: 0,
                y: -1
            }];
            for (const k of kicks) {
                const test = {
                    ...cur,
                    rotation: nr,
                    x: cur.x + k.x,
                    y: cur.y + k.y
                };
                if (canPlace(test)) {
                    cur = test;
                    draw();
                    return;
                }
            }
        }

        function hardDrop() {
            if (!cur) return;
            let dropped = 0;
            while (canPlace(cur, 0, 1)) {
                cur.y++;
                dropped++;
            }
            score += dropped * 2;
            lock();
            refreshHUD();
        }

        function softDrop() {
            if (!cur) return;
            if (canPlace(cur, 0, 1)) {
                cur.y++;
                score += 1;
                draw();
                refreshHUD();
            } else {
                lock();
            }
        }

        function doHold() {
            if (!cur || !canHold) return;
            canHold = false;
            if (hold == null) {
                hold = cur.type;
                cur = null;
                spawn();
            } else {
                const swap = hold;
                hold = cur.type;
                const np = startingPiece(swap);
                if (!canPlace(np)) return gameOver();
                cur = np;
            }
            drawHold();
            draw();
        }

        window.addEventListener('keydown', e => {
            const k = e.key.toLowerCase();
            if (k === 'p') {
                if (status === 'playing') {
                    status = 'paused';
                    overlayEl.style.display = 'flex';
                    ovTitle.textContent = 'Paused';
                    ovSub.textContent = 'Tekan P untuk lanjut';
                    cancelLoop();
                } else if (status === 'paused') {
                    status = 'playing';
                    overlayEl.style.display = 'none';
                    startLoop();
                }
                return;
            }
            if (k === 'r') {
                start();
                return;
            }
            if (status !== 'playing') return;
            if (k === 'arrowleft' || k === 'a') tryMove(-1, 0);
            else if (k === 'arrowright' || k === 'd') tryMove(1, 0);
            else if (k === 'arrowdown' || k === 's') softDrop();
            else if (k === 'arrowup' || k === 'w' || k === 'x') rotate(1);
            else if (k === 'z' || k === 'q') rotate(-1);
            else if (k === ' ') hardDrop();
            else if (k === 'c') doHold();
        });

        // Mobile buttons
        function bind(id, fn) {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', fn);
        }
        bind('btn-left', () => tryMove(-1, 0));
        bind('btn-right', () => tryMove(1, 0));
        bind('btn-rotl', () => rotate(-1));
        bind('btn-rotr', () => rotate(1));
        bind('btn-drop', () => softDrop());
        bind('btn-hard', () => hardDrop());
        bind('btn-hold', () => doHold());

        // Initial paint
        refreshHUD();
        draw();
        drawNext();
        drawHold();
    </script>
</body>

</html>