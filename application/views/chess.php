<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chess — CI3 / AdminLTE3 Friendly (Single File)</title>
    <style>
        :root {
            --bg: #0f172a;
            --panel: #111827;
            --muted: #6b7280;
            --accent: #22d3ee;
            --good: #10b981;
            --bad: #ef4444;
            --square-light: #f8fafc;
            /* putih terang */
            --square-dark: #1e293b;
            /* biru gelap */
            --highlight: #22d3ee66;
            --last: #a3e63540;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            background: linear-gradient(180deg, #0b1023, #0f172a);
            color: #e5e7eb;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Inter, "Helvetica Neue", Arial, sans-serif
        }

        .wrap {
            max-width: 1100px;
            margin: 24px auto;
            padding: 16px
        }

        .title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px
        }

        .title h1 {
            font-size: 20px;
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.2px
        }

        .card {
            background: linear-gradient(180deg, #0f172a, #0b1226);
            border: 1px solid #1f2937;
            border-radius: 16px;
            box-shadow: 0 20px 45px #0008;
            overflow: hidden
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 320px
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr
            }
        }

        .board-wrap {
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: center
        }

        /* ===== BOARD ===== */
        .board {
            position: relative;
            width: min(78vh, 78vw, 720px);
            aspect-ratio: 1/1;
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            grid-template-rows: repeat(8, 1fr);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: inset 0 0 0 1px #0007, 0 8px 24px #000c
        }

        .square {
            position: relative;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: calc(min(78vh, 78vw, 720px)/8 * 0.7);
            line-height: 1
        }

        .square.light {
            background: var(--square-light)
        }

        .square.dark {
            background: var(--square-dark)
        }

        /* koordinat: hanya di edge, nempel pojok */
        .coord {
            position: absolute;
            font-size: 11px;
            font-weight: 700;
            pointer-events: none;
            opacity: .9
        }

        .coord.rank {
            top: 4px;
            left: 6px
        }

        .coord.file {
            bottom: 4px;
            right: 6px
        }

        .square.light .coord {
            color: #0f172a
        }

        .square.dark .coord {
            color: #e2e8f0
        }

        /* PIECES: pakai warna per-side supaya tidak ikut warna kotak */
        .piece {
            cursor: grab;
            transition: transform .12s ease, filter .12s ease;
            position: relative;
            z-index: 3;
            text-rendering: optimizeLegibility
        }

        .piece.w {
            color: #f8fafc;
            text-shadow: 0 1px 0 #0008, 0 0 6px #0009
        }

        .piece.b {
            color: #0b1220;
            text-shadow: 0 1px 0 #ffffffcc, 0 0 4px #ffffffe0
        }

        .square.legal::after {
            content: '';
            position: absolute;
            width: 28%;
            height: 28%;
            border-radius: 999px;
            background: var(--highlight);
            box-shadow: 0 0 0 6px #0002
        }

        .square.capture::after {
            content: '';
            position: absolute;
            inset: 0;
            border: 6px solid var(--highlight);
            border-radius: 6px
        }

        .square.lastmove {
            box-shadow: inset 0 0 0 9999px var(--last)
        }

        .square.incheck {
            box-shadow: inset 0 0 0 9999px #ef44444d
        }

        /* ===== Sidebar ===== */
        .side {
            padding: 16px;
            border-left: 1px solid #1f2937
        }

        .panel {
            background: #0b1020;
            border: 1px solid #1f2937;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 12px
        }

        .panel h3 {
            margin: 0 0 8px;
            font-size: 14px;
            color: #cbd5e1;
            letter-spacing: .2px
        }

        .row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        button,
        .btn {
            appearance: none;
            border: none;
            padding: 10px 12px;
            border-radius: 10px;
            background: #1f2937;
            color: #e5e7eb;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            box-shadow: inset 0 -2px 0 #0006;
            transition: transform .06s ease, background .15s ease
        }

        button:hover {
            background: #263244
        }

        button:active {
            transform: translateY(1px)
        }

        .btn-primary {
            background: linear-gradient(180deg, #06b6d4, #0891b2)
        }

        .btn-danger {
            background: linear-gradient(180deg, #ef4444, #b91c1c)
        }

        .btn-green {
            background: linear-gradient(180deg, #10b981, #059669)
        }

        .btn-yellow {
            background: linear-gradient(180deg, #f59e0b, #b45309)
        }

        .tog {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .tog input {
            accent-color: var(--accent)
        }

        .status {
            font-size: 14px;
            color: #cbd5e1
        }

        .mini {
            font-size: 12px;
            color: #94a3b8
        }

        .history {
            max-height: 180px;
            overflow: auto;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            background: #0a0f1e;
            border-radius: 8px;
            padding: 8px;
            border: 1px solid #1f2937
        }

        .history table {
            width: 100%;
            border-collapse: collapse
        }

        .history td {
            padding: 4px 6px;
            border-bottom: 1px dashed #1f2937;
            font-size: 12px
        }

        .fen {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #1f2937;
            background: #0a0f1e;
            color: #e5e7eb;
            font-family: monospace
        }

        /* Promotion Modal */
        dialog {
            border: none;
            border-radius: 16px;
            padding: 0;
            background: #0b1020;
            color: #e5e7eb;
            box-shadow: 0 30px 80px #000c
        }

        .modal {
            padding: 14px
        }

        .modal h4 {
            margin: 0 0 10px;
            font-size: 14px
        }

        .promo-grid {
            display: grid;
            grid-template-columns: repeat(4, 64px);
            gap: 8px
        }

        .promo {
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 10px;
            cursor: pointer;
            font-size: 40px
        }

        .promo:hover {
            background: #1b2438
        }

        .foot {
            display: flex;
            justify-content: flex-end;
            padding: 10px 14px;
            background: #0a0f1e;
            border-top: 1px solid #1f2937
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="title">
            <h1>♟️ Chess — Single File (CI3 / AdminLTE3)</h1>
            <div class="row">
                <button id="btnNew" class="btn-green">Game Baru</button>
                <button id="btnUndo">Undo</button>
                <button id="btnFlip">Flip Board</button>
            </div>
        </div>

        <div class="card grid">
            <div class="board-wrap">
                <div id="board" class="board" aria-label="Papan Catur" role="application"></div>
            </div>
            <aside class="side">
                <div class="panel">
                    <h3>Status</h3>
                    <div class="status" id="status">Giliran Putih</div>
                    <div class="mini" id="substatus">&nbsp;</div>
                </div>
                <div class="panel">
                    <h3>Mode</h3>
                    <div class="tog"><input type="checkbox" id="vsAi" /><label for="vsAi">Main vs Komputer</label></div>
                    <div class="tog"><input type="checkbox" id="aiPlaysBlack" checked /><label for="aiPlaysBlack">Komputer main Hitam</label></div>
                    <div class="tog"><input type="range" id="aiDepth" min="1" max="3" value="2" /> <span class="mini">Kedalaman AI: <b id="depthLabel">2</b></span></div>
                </div>
                <div class="panel">
                    <h3>Riwayat Langkah</h3>
                    <div class="history">
                        <table id="hist"></table>
                    </div>
                </div>
                <div class="panel">
                    <h3>FEN</h3>
                    <input id="fen" class="fen" readonly />
                    <div class="row" style="margin-top:8px">
                        <button id="btnCopyFen" class="btn-primary">Copy FEN</button>
                        <button id="btnSetFen">Set FEN</button>
                        <button id="btnExportPGN">Export PGN</button>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <!-- Promotion Dialog -->
    <dialog id="promoDlg">
        <div class="modal">
            <h4>Pilih Promosi Bidak</h4>
            <div class="promo-grid" id="promoGrid"></div>
        </div>
        <div class="foot">
            <button id="cancelPromo">Batal</button>
        </div>
    </dialog>

    <script>
        /********************
         * UTIL & CONSTANTS *
         ********************/
        const WHITE = 'w',
            BLACK = 'b';
        const PIECES = {
            p: 'p',
            n: 'n',
            b: 'b',
            r: 'r',
            q: 'q',
            k: 'k'
        };
        const PIECE_UNICODE = {
            'wp': '♙',
            'wn': '♘',
            'wb': '♗',
            'wr': '♖',
            'wq': '♕',
            'wk': '♔',
            'bp': '♟',
            'bn': '♞',
            'bb': '♝',
            'br': '♜',
            'bq': '♛',
            'bk': '♚'
        };
        const FILES = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];

        function idx(file, rank) {
            return rank * 8 + file
        }

        function frIdx(i) {
            return {
                f: i % 8,
                r: Math.floor(i / 8)
            }
        }

        function sqStr(i) {
            const {
                f,
                r
            } = frIdx(i);
            return FILES[f] + (8 - r)
        }

        function parseSq(s) {
            const f = FILES.indexOf(s[0]);
            const r = 8 - parseInt(s[1]);
            return idx(f, r)
        }

        /****************
         * CHESS ENGINE *
         ****************/
        class ChessEngine {
            constructor() {
                this.reset();
            }
            reset() {
                this.board = new Array(64).fill(null);
                this.turn = WHITE; // side to move
                this.castle = {
                    w: {
                        k: true,
                        q: true
                    },
                    b: {
                        k: true,
                        q: true
                    }
                };
                this.enPassant = -1; // target square index
                this.halfmove = 0;
                this.fullmove = 1;
                this.history = [];
                this.loadFEN("rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1");
            }
            clone() {
                const c = new ChessEngine();
                Object.assign(c, JSON.parse(JSON.stringify({
                    board: this.board,
                    turn: this.turn,
                    castle: this.castle,
                    enPassant: this.enPassant,
                    halfmove: this.halfmove,
                    fullmove: this.fullmove,
                    history: this.history
                })));
                return c
            }

            loadFEN(fen) {
                const parts = fen.trim().split(/\s+/);
                const rows = parts[0].split('/');
                this.board.fill(null);
                for (let r = 0; r < 8; r++) {
                    let file = 0;
                    const row = rows[r];
                    for (const ch of row) {
                        if (/\d/.test(ch)) {
                            file += parseInt(ch);
                            continue;
                        }
                        const color = ch === ch.toLowerCase() ? BLACK : WHITE;
                        const t = ch.toLowerCase();
                        this.board[idx(file, r)] = {
                            c: color,
                            t
                        };
                        file++;
                    }
                }
                this.turn = parts[1] === 'w' ? WHITE : BLACK;
                const rights = parts[2];
                this.castle = {
                    w: {
                        k: false,
                        q: false
                    },
                    b: {
                        k: false,
                        q: false
                    }
                };
                if (rights.includes('K')) this.castle.w.k = true;
                if (rights.includes('Q')) this.castle.w.q = true;
                if (rights.includes('k')) this.castle.b.k = true;
                if (rights.includes('q')) this.castle.b.q = true;
                this.enPassant = parts[3] !== '-' ? parseSq(parts[3]) : -1;
                this.halfmove = parseInt(parts[4] || '0');
                this.fullmove = parseInt(parts[5] || '1');
                this.history = [];
            }

            fen() {
                let s = '';
                for (let r = 0; r < 8; r++) {
                    let empty = 0;
                    for (let f = 0; f < 8; f++) {
                        const P = this.board[idx(f, r)];
                        if (!P) {
                            empty++;
                            continue;
                        }
                        if (empty) {
                            s += empty;
                            empty = 0;
                        }
                        let ch = P.t;
                        if (P.c === WHITE) ch = ch.toUpperCase();
                        s += ch;
                    }
                    if (empty) s += empty;
                    if (r < 7) s += '/';
                }
                let rights = '';
                if (this.castle.w.k) rights += 'K';
                if (this.castle.w.q) rights += 'Q';
                if (this.castle.b.k) rights += 'k';
                if (this.castle.b.q) rights += 'q';
                if (!rights) rights = '-';
                const ep = this.enPassant >= 0 ? sqStr(this.enPassant) : '-';
                return `${s} ${this.turn==='w'?'w':'b'} ${rights} ${ep} ${this.halfmove} ${this.fullmove}`;
            }

            pieceAt(i) {
                return this.board[i]
            }
            setPiece(i, p) {
                this.board[i] = p
            }
            kingSquare(color) {
                for (let i = 0; i < 64; i++) {
                    const p = this.board[i];
                    if (p && p.c === color && p.t === 'k') return i
                }
                return -1;
            }

            isSquareAttacked(square, byColor) {
                const opp = byColor;
                const {
                    f: tf,
                    r: tr
                } = frIdx(square);
                const dir = (opp === WHITE ? -1 : 1);
                for (const df of [-1, 1]) {
                    const f = tf + df,
                        r = tr + dir;
                    if (f < 0 || f > 7 || r < 0 || r > 7) continue;
                    const P = this.board[idx(f, r)];
                    if (P && P.c === opp && P.t === 'p') return true;
                }
                const jumps = [
                    [1, 2],
                    [2, 1],
                    [-1, 2],
                    [-2, 1],
                    [1, -2],
                    [2, -1],
                    [-1, -2],
                    [-2, -1]
                ];
                for (const [df, dr] of jumps) {
                    const f = tf + df,
                        r = tr + dr;
                    if (f < 0 || f > 7 || r < 0 || r > 7) continue;
                    const P = this.board[idx(f, r)];
                    if (P && P.c === opp && P.t === 'n') return true;
                }
                const diag = [
                    [1, 1],
                    [1, -1],
                    [-1, 1],
                    [-1, -1]
                ];
                for (const [df, dr] of diag) {
                    let f = tf + df,
                        r = tr + dr;
                    while (f >= 0 && f < 8 && r >= 0 && r < 8) {
                        const P = this.board[idx(f, r)];
                        if (P) {
                            if (P.c === opp && (P.t === 'b' || P.t === 'q')) return true;
                            break;
                        }
                        f += df;
                        r += dr;
                    }
                }
                const orth = [
                    [1, 0],
                    [-1, 0],
                    [0, 1],
                    [0, -1]
                ];
                for (const [df, dr] of orth) {
                    let f = tf + df,
                        r = tr + dr;
                    while (f >= 0 && f < 8 && r >= 0 && r < 8) {
                        const P = this.board[idx(f, r)];
                        if (P) {
                            if (P.c === opp && (P.t === 'r' || P.t === 'q')) return true;
                            break;
                        }
                        f += df;
                        r += dr;
                    }
                }
                for (let df = -1; df <= 1; df++)
                    for (let dr = -1; dr <= 1; dr++) {
                        if (!df && !dr) continue;
                        const f = tf + df,
                            r = tr + dr;
                        if (f < 0 || f > 7 || r < 0 || r > 7) continue;
                        const P = this.board[idx(f, r)];
                        if (P && P.c === opp && P.t === 'k') return true;
                    }
                return false;
            }

            inCheck(color) {
                const ksq = this.kingSquare(color);
                return this.isSquareAttacked(ksq, color === WHITE ? BLACK : WHITE)
            }

            generateMoves(color = this.turn) {
                const moves = [];
                const us = color;
                const them = us === WHITE ? BLACK : WHITE;
                const push = (m) => {
                    const saved = this._makeMove(m, true);
                    const illegal = this.inCheck(us);
                    this._unmakeMove(saved);
                    if (!illegal) moves.push(m);
                }
                for (let i = 0; i < 64; i++) {
                    const P = this.board[i];
                    if (!P || P.c !== us) continue;
                    const {
                        f,
                        r
                    } = frIdx(i);
                    if (P.t === 'p') {
                        const dir = us === WHITE ? -1 : 1;
                        const one = r + dir;
                        if (one >= 0 && one < 8) {
                            const fwd = idx(f, one);
                            if (!this.board[fwd]) {
                                if ((us === WHITE && one === 0) || (us === BLACK && one === 7)) {
                                    for (const promo of ['q', 'r', 'b', 'n']) push({
                                        from: i,
                                        to: fwd,
                                        piece: 'p',
                                        color: us,
                                        promotion: promo
                                    });
                                } else push({
                                    from: i,
                                    to: fwd,
                                    piece: 'p',
                                    color: us
                                });
                                const startRank = us === WHITE ? 6 : 1;
                                if (r === startRank) {
                                    const two = idx(f, r + dir * 2);
                                    if (!this.board[two]) push({
                                        from: i,
                                        to: two,
                                        piece: 'p',
                                        color: us,
                                        doublePawn: true
                                    });
                                }
                            }
                        }
                        for (const df of [-1, 1]) {
                            const ff = f + df,
                                rr = r + dir;
                            if (ff < 0 || ff > 7 || rr < 0 || rr > 7) continue;
                            const to = idx(ff, rr);
                            const T = this.board[to];
                            if (T && T.c === them) {
                                if ((us === WHITE && rr === 0) || (us === BLACK && rr === 7)) {
                                    for (const promo of ['q', 'r', 'b', 'n']) push({
                                        from: i,
                                        to: to,
                                        piece: 'p',
                                        color: us,
                                        capture: T,
                                        promotion: promo
                                    });
                                } else push({
                                    from: i,
                                    to: to,
                                    piece: 'p',
                                    color: us,
                                    capture: T
                                });
                            }
                        }
                        if (this.enPassant >= 0) {
                            const {
                                f: ef,
                                r: er
                            } = frIdx(this.enPassant);
                            if (er === r + dir && Math.abs(ef - f) === 1) {
                                const capSq = idx(ef, r);
                                const capP = this.board[capSq];
                                if (capP && capP.c === them && capP.t === 'p') push({
                                    from: i,
                                    to: this.enPassant,
                                    piece: 'p',
                                    color: us,
                                    enPassant: true,
                                    capture: capP,
                                    capSq
                                });
                            }
                        }
                    } else if (P.t === 'n') {
                        const jumps = [
                            [1, 2],
                            [2, 1],
                            [-1, 2],
                            [-2, 1],
                            [1, -2],
                            [2, -1],
                            [-1, -2],
                            [-2, -1]
                        ];
                        for (const [df, dr] of jumps) {
                            const ff = f + df,
                                rr = r + dr;
                            if (ff < 0 || ff > 7 || rr < 0 || rr > 7) continue;
                            const to = idx(ff, rr);
                            const T = this.board[to];
                            if (!T || T.c === them) push({
                                from: i,
                                to,
                                piece: 'n',
                                color: us,
                                capture: T || null
                            });
                        }
                    } else if (P.t === 'b' || P.t === 'r' || P.t === 'q') {
                        const dirs = [];
                        if (P.t !== 'r') dirs.push([1, 1], [1, -1], [-1, 1], [-1, -1]);
                        if (P.t !== 'b') dirs.push([1, 0], [-1, 0], [0, 1], [0, -1]);
                        for (const [df, dr] of dirs) {
                            let ff = f + df,
                                rr = r + dr;
                            while (ff >= 0 && ff < 8 && rr >= 0 && rr < 8) {
                                const to = idx(ff, rr);
                                const T = this.board[to];
                                if (!T) {
                                    push({
                                        from: i,
                                        to,
                                        piece: P.t,
                                        color: us
                                    });
                                } else {
                                    if (T.c === them) push({
                                        from: i,
                                        to,
                                        piece: P.t,
                                        color: us,
                                        capture: T
                                    });
                                    break;
                                }
                                ff += df;
                                rr += dr;
                            }
                        }
                    } else if (P.t === 'k') {
                        for (let df = -1; df <= 1; df++)
                            for (let dr = -1; dr <= 1; dr++) {
                                if (!df && !dr) continue;
                                const ff = f + df,
                                    rr = r + dr;
                                if (ff < 0 || ff > 7 || rr < 0 || rr > 7) continue;
                                const to = idx(ff, rr);
                                const T = this.board[to];
                                if (!T || T.c === them) push({
                                    from: i,
                                    to,
                                    piece: 'k',
                                    color: us,
                                    capture: T || null
                                });
                            }
                        if (us === WHITE && r === 7 && f === 4) {
                            if (this.castle.w.k && !this.board[idx(5, 7)] && !this.board[idx(6, 7)] && !this.isSquareAttacked(idx(4, 7), them) && !this.isSquareAttacked(idx(5, 7), them) && !this.isSquareAttacked(idx(6, 7), them)) push({
                                from: i,
                                to: idx(6, 7),
                                piece: 'k',
                                color: us,
                                castle: 'K'
                            });
                            if (this.castle.w.q && !this.board[idx(3, 7)] && !this.board[idx(2, 7)] && !this.board[idx(1, 7)] && !this.isSquareAttacked(idx(4, 7), them) && !this.isSquareAttacked(idx(3, 7), them) && !this.isSquareAttacked(idx(2, 7), them)) push({
                                from: i,
                                to: idx(2, 7),
                                piece: 'k',
                                color: us,
                                castle: 'Q'
                            });
                        }
                        if (us === BLACK && r === 0 && f === 4) {
                            if (this.castle.b.k && !this.board[idx(5, 0)] && !this.board[idx(6, 0)] && !this.isSquareAttacked(idx(4, 0), them) && !this.isSquareAttacked(idx(5, 0), them) && !this.isSquareAttacked(idx(6, 0), them)) push({
                                from: i,
                                to: idx(6, 0),
                                piece: 'k',
                                color: us,
                                castle: 'k'
                            });
                            if (this.castle.b.q && !this.board[idx(3, 0)] && !this.board[idx(2, 0)] && !this.board[idx(1, 0)] && !this.isSquareAttacked(idx(4, 0), them) && !this.isSquareAttacked(idx(3, 0), them) && !this.isSquareAttacked(idx(2, 0), them)) push({
                                from: i,
                                to: idx(2, 0),
                                piece: 'k',
                                color: us,
                                castle: 'q'
                            });
                        }
                    }
                }
                return moves;
            }

            _makeMove(m, shadow = false) {
                const snapshot = {
                    from: m.from,
                    to: m.to,
                    piece: JSON.parse(JSON.stringify(this.board[m.from])),
                    captured: this.board[m.to] ? JSON.parse(JSON.stringify(this.board[m.to])) : null,
                    enPassant: this.enPassant,
                    castle: JSON.parse(JSON.stringify(this.castle)),
                    half: this.halfmove,
                    full: this.fullmove,
                    turn: this.turn
                };
                this.halfmove++;
                if (m.enPassant) {
                    const capSq = m.capSq ?? (m.color === WHITE ? m.to + 8 : m.to - 8);
                    snapshot.captured = JSON.parse(JSON.stringify(this.board[capSq]));
                    this.board[capSq] = null;
                }
                this.board[m.to] = JSON.parse(JSON.stringify(this.board[m.from]));
                this.board[m.from] = null;
                if (m.promotion) {
                    this.board[m.to].t = m.promotion;
                    this.halfmove = 0;
                }
                if (snapshot.captured) this.halfmove = 0;
                if (snapshot.piece && snapshot.piece.t === 'p') this.halfmove = 0;
                if (m.doublePawn) {
                    this.enPassant = m.color === WHITE ? m.to + 8 : m.to - 8;
                } else {
                    this.enPassant = -1;
                }
                const moved = snapshot.piece;
                const fromFr = frIdx(m.from);
                const toFr = frIdx(m.to);
                if (moved.t === 'k') {
                    if (m.color === WHITE) {
                        this.castle.w.k = false;
                        this.castle.w.q = false;
                    } else {
                        this.castle.b.k = false;
                        this.castle.b.q = false;
                    }
                    if (m.castle) {
                        if (m.castle === 'K') {
                            this.board[idx(5, 7)] = JSON.parse(JSON.stringify(this.board[idx(7, 7)]));
                            this.board[idx(7, 7)] = null;
                        }
                        if (m.castle === 'Q') {
                            this.board[idx(3, 7)] = JSON.parse(JSON.stringify(this.board[idx(0, 7)]));
                            this.board[idx(0, 7)] = null;
                        }
                        if (m.castle === 'k') {
                            this.board[idx(5, 0)] = JSON.parse(JSON.stringify(this.board[idx(7, 0)]));
                            this.board[idx(7, 0)] = null;
                        }
                        if (m.castle === 'q') {
                            this.board[idx(3, 0)] = JSON.parse(JSON.stringify(this.board[idx(0, 0)]));
                            this.board[idx(0, 0)] = null;
                        }
                    }
                }
                const updateRookRight = (color, f, r) => {
                    if (color === WHITE) {
                        if (f === 7 && r === 7) this.castle.w.k = false;
                        if (f === 0 && r === 7) this.castle.w.q = false;
                    } else {
                        if (f === 7 && r === 0) this.castle.b.k = false;
                        if (f === 0 && r === 0) this.castle.b.q = false;
                    }
                }
                if (moved.t === 'r') {
                    updateRookRight(m.color, fromFr.f, fromFr.r);
                }
                if (snapshot.captured && snapshot.captured.t === 'r') {
                    const {
                        f,
                        r
                    } = toFr;
                    updateRookRight(snapshot.captured.c, f, r);
                }

                this.turn = this.turn === WHITE ? BLACK : WHITE;
                if (this.turn === WHITE) this.fullmove++;

                if (!shadow) this.history.push(snapshot);
                return snapshot;
            }

            _unmakeMove(snap) {
                this.board[snap.from] = snap.piece;
                this.board[snap.to] = snap.captured ? snap.captured : null;
                const movedPiece = snap.piece;
                const dist = Math.abs(frIdx(snap.from).f - frIdx(snap.to).f);
                if (movedPiece.t === 'k' && dist === 2) {
                    if (snap.turn === WHITE) {
                        if (frIdx(snap.to).f === 6 && frIdx(snap.to).r === 7) {
                            this.board[idx(7, 7)] = this.board[idx(5, 7)];
                            this.board[idx(5, 7)] = null;
                        }
                        if (frIdx(snap.to).f === 2 && frIdx(snap.to).r === 7) {
                            this.board[idx(0, 7)] = this.board[idx(3, 7)];
                            this.board[idx(3, 7)] = null;
                        }
                    } else {
                        if (frIdx(snap.to).f === 6 && frIdx(snap.to).r === 0) {
                            this.board[idx(7, 0)] = this.board[idx(5, 0)];
                            this.board[idx(5, 0)] = null;
                        }
                        if (frIdx(snap.to).f === 2 && frIdx(snap.to).r === 0) {
                            this.board[idx(0, 0)] = this.board[idx(3, 0)];
                            this.board[idx(3, 0)] = null;
                        }
                    }
                }
                this.enPassant = snap.enPassant;
                this.castle = snap.castle;
                this.halfmove = snap.half;
                this.fullmove = snap.full;
                this.turn = snap.turn;
            }

            makeMove(m) {
                const legal = this.generateMoves().filter(x => x.from === m.from && x.to === m.to && (x.promotion ? x.promotion === m.promotion : !m.promotion));
                if (!legal.length) return false;
                this._makeMove(legal[0]);
                return true;
            }

            undo() {
                if (!this.history.length) return false;
                const snap = this.history.pop();
                this._unmakeMove(snap);
                return true;
            }

            gameOver() {
                const moves = this.generateMoves();
                if (moves.length) return {
                    over: false
                };
                if (this.inCheck(this.turn)) return {
                    over: true,
                    type: 'checkmate',
                    winner: this.turn === WHITE ? BLACK : WHITE
                };
                return {
                    over: true,
                    type: 'stalemate'
                };
            }
        }

        /****************
         * SIMPLE AI
         ****************/
        const PIECE_VALUE = {
            p: 100,
            n: 320,
            b: 330,
            r: 500,
            q: 900,
            k: 20000
        };

        function evaluate(engine) {
            let score = 0;
            for (let i = 0; i < 64; i++) {
                const P = engine.board[i];
                if (!P) continue;
                score += (P.c === WHITE ? 1 : -1) * PIECE_VALUE[P.t];
            }
            const moves = engine.generateMoves();
            score += (engine.turn === WHITE ? 0.1 : -0.1) * moves.length;
            return score;
        }

        function minimax(engine, depth, alpha, beta, maximizing) {
            const over = engine.gameOver();
            if (depth === 0 || over.over) {
                if (over.over) {
                    if (over.type === 'checkmate') return (over.winner === WHITE ? 999999 : -999999);
                    return 0;
                }
                return evaluate(engine);
            }
            const moves = engine.generateMoves(engine.turn);
            moves.sort((a, b) => (b.capture ? 1 : 0) - (a.capture ? 1 : 0));
            if (maximizing) {
                let maxEval = -Infinity;
                for (const m of moves) {
                    const snap = engine._makeMove(m, true);
                    const val = minimax(engine, depth - 1, alpha, beta, false);
                    engine._unmakeMove(snap);
                    if (val > maxEval) {
                        maxEval = val;
                    }
                    alpha = Math.max(alpha, val);
                    if (beta <= alpha) break;
                }
                return maxEval;
            } else {
                let minEval = Infinity;
                for (const m of moves) {
                    const snap = engine._makeMove(m, true);
                    const val = minimax(engine, depth - 1, alpha, beta, true);
                    engine._unmakeMove(snap);
                    if (val < minEval) {
                        minEval = val;
                    }
                    beta = Math.min(beta, val);
                    if (beta <= alpha) break;
                }
                return minEval;
            }
        }
        const AI = {
            depth: 2,
            think(engine) {
                const maximizing = (engine.turn === WHITE);
                const res = minimax(engine, this.depth, -Infinity, Infinity, maximizing);
                return engine.generateMoves()[0] || null;
            }
        };

        /****************
         * UI LAYER
         ****************/
        const app = {
            engine: new ChessEngine(),
            orientation: WHITE,
            selected: null,
            legalTargets: [],
            lastMove: null,
            vsAi: false,
            aiPlaysBlack: true
        };
        const boardEl = document.getElementById('board');
        const statusEl = document.getElementById('status');
        const substatusEl = document.getElementById('substatus');
        const histEl = document.getElementById('hist');
        const fenEl = document.getElementById('fen');
        const vsAiEl = document.getElementById('vsAi');
        const aiPlaysBlackEl = document.getElementById('aiPlaysBlack');
        const aiDepthEl = document.getElementById('aiDepth');
        const depthLabel = document.getElementById('depthLabel');

        function buildBoard() {
            boardEl.innerHTML = '';
            const orientWhite = app.orientation === WHITE;
            for (let vr = 0; vr < 8; vr++) {
                for (let vf = 0; vf < 8; vf++) {
                    const rf = orientWhite ? vf : 7 - vf;
                    const rr = orientWhite ? vr : 7 - vr;
                    const sq = idx(rf, rr);
                    const sdiv = document.createElement('div');
                    sdiv.className = 'square ' + (((vr + vf) % 2 === 0) ? 'light' : 'dark');
                    sdiv.dataset.square = sq;
                    // koordinat hanya di edge
                    if ((orientWhite && rr === 7) || (!orientWhite && rr === 0)) {
                        const file = document.createElement('span');
                        file.className = 'coord file';
                        file.textContent = FILES[rf];
                        sdiv.appendChild(file);
                    }
                    if ((orientWhite && rf === 0) || (!orientWhite && rf === 7)) {
                        const rank = document.createElement('span');
                        rank.className = 'coord rank';
                        rank.textContent = orientWhite ? (8 - rr) : (rr + 1);
                        sdiv.appendChild(rank);
                    }
                    boardEl.appendChild(sdiv);
                }
            }
            refreshPieces();
        }

        function refreshPieces() {
            document.querySelectorAll('.square').forEach(sq => {
                const coordEls = [...sq.querySelectorAll('.coord')].map(e => e.outerHTML).join('');
                sq.innerHTML = coordEls;
                sq.classList.remove('lastmove', 'incheck', 'legal', 'capture');
            });
            for (let i = 0; i < 64; i++) {
                const P = app.engine.board[i];
                if (!P) continue;
                const displayIdx = mapToDisplayIndex(i);
                const cell = boardEl.children[displayIdx];
                const span = document.createElement('div');
                span.className = 'piece ' + (P.c === WHITE ? 'w' : 'b');
                span.textContent = PIECE_UNICODE[P.c + P.t];
                span.draggable = true;
                span.dataset.from = i;
                cell.appendChild(span);
            }
            if (app.lastMove) {
                const a = mapToDisplayIndex(app.lastMove.from),
                    b = mapToDisplayIndex(app.lastMove.to);
                markLastMove(a, b);
            }
            const checkColor = app.engine.turn;
            if (app.engine.inCheck(checkColor)) {
                const ksq = app.engine.kingSquare(checkColor);
                const d = mapToDisplayIndex(ksq);
                const cell = boardEl.children[d];
                cell.classList.add('incheck');
            }
            updateStatus();
            updateHistory();
            fenEl.value = app.engine.fen();
            saveState();
        }

        function markLastMove(a, b) {
            boardEl.children[a].classList.add('lastmove');
            boardEl.children[b].classList.add('lastmove');
        }

        function mapToDisplayIndex(i) {
            const {
                f,
                r
            } = frIdx(i);
            if (app.orientation === WHITE) {
                return r * 8 + f;
            } else {
                const vf = 7 - f,
                    vr = 7 - r;
                return vr * 8 + vf;
            }
        }

        function mapFromDisplayIndex(d) {
            const vf = d % 8,
                vr = Math.floor(d / 8);
            if (app.orientation === WHITE) {
                return idx(vf, vr);
            } else {
                const f = 7 - vf,
                    r = 7 - vr;
                return idx(f, r);
            }
        }

        let dragData = null;
        boardEl.addEventListener('pointerdown', (e) => {
            const piece = e.target.closest('.piece');
            if (!piece) return;
            const from = parseInt(piece.dataset.from, 10);
            const P = app.engine.pieceAt(from);
            if (!P) return;
            if (P.c !== app.engine.turn) return;
            dragData = {
                from,
                el: piece
            };
            showLegal(from);
        });
        boardEl.addEventListener('pointerup', (e) => {
            if (!dragData) return;
            const squareEl = e.target.closest('.square');
            if (squareEl) {
                const to = parseInt(squareEl.dataset.square, 10);
                tryMove(dragData.from, to);
            }
            clearLegal();
            dragData = null;
        });
        boardEl.addEventListener('click', (e) => {
            const squareEl = e.target.closest('.square');
            if (!squareEl) return;
            const logical = parseInt(squareEl.dataset.square, 10);
            const P = app.engine.pieceAt(logical);
            if (app.selected === null) {
                if (P && P.c === app.engine.turn) {
                    app.selected = logical;
                    showLegal(logical);
                }
            } else {
                if (tryMove(app.selected, logical)) {
                    app.selected = null;
                } else {
                    clearLegal();
                    if (P && P.c === app.engine.turn) {
                        app.selected = logical;
                        showLegal(logical);
                    } else {
                        app.selected = null;
                    }
                }
            }
        });

        function showLegal(from) {
            clearLegal();
            app.legalTargets = app.engine.generateMoves().filter(m => m.from === from);
            for (const m of app.legalTargets) {
                const d = mapToDisplayIndex(m.to);
                const el = boardEl.children[d];
                if (m.capture) el.classList.add('capture');
                else el.classList.add('legal');
            }
        }

        function clearLegal() {
            document.querySelectorAll('.square').forEach(s => s.classList.remove('legal', 'capture'));
            app.legalTargets = [];
        }

        function tryMove(from, to) {
            const candidates = app.engine.generateMoves().filter(m => m.from === from && m.to === to);
            if (!candidates.length) return false;
            if (candidates.some(m => m.promotion)) return openPromotion(from, to);
            if (app.engine.makeMove(candidates[0])) {
                app.lastMove = {
                    from,
                    to
                };
                refreshPieces();
                maybeAiMove();
                return true;
            }
            return false;
        }

        const promoDlg = document.getElementById('promoDlg');
        const promoGrid = document.getElementById('promoGrid');
        const cancelPromo = document.getElementById('cancelPromo');

        function openPromotion(from, to) {
            promoGrid.innerHTML = '';
            const color = app.engine.pieceAt(from)?.c || app.engine.turn;
            for (const p of ['q', 'r', 'b', 'n']) {
                const d = document.createElement('div');
                d.className = 'promo';
                d.textContent = PIECE_UNICODE[color + p];
                d.addEventListener('click', () => {
                    promoDlg.close();
                    if (app.engine.makeMove({
                            from,
                            to,
                            promotion: p
                        })) {
                        app.lastMove = {
                            from,
                            to
                        };
                        refreshPieces();
                        maybeAiMove();
                    }
                });
                promoGrid.appendChild(d);
            }
            promoDlg.showModal();
            cancelPromo.onclick = () => promoDlg.close();
            return true;
        }

        function updateStatus() {
            const turn = app.engine.turn === WHITE ? 'Putih' : 'Hitam';
            const over = app.engine.gameOver();
            if (over.over) {
                if (over.type === 'checkmate') {
                    statusEl.textContent = `Skak Mat — ${over.winner===WHITE?'Putih':'Hitam'} menang`;
                    substatusEl.textContent = 'Game selesai. Klik Game Baru untuk mengulang.';
                } else {
                    statusEl.textContent = 'Stalemate / Seri';
                    substatusEl.textContent = 'Tidak ada langkah legal.';
                }
            } else {
                statusEl.textContent = `Giliran ${turn}`;
                substatusEl.textContent = app.engine.inCheck(app.engine.turn) ? 'Skak!' : '\u00A0';
            }
        }

        function updateHistory() {
            histEl.innerHTML = '';
            let moveNum = 1;
            for (let i = 0; i < app.engine.history.length; i += 2) {
                const tr = document.createElement('tr');
                const tdNum = document.createElement('td');
                tdNum.textContent = `${moveNum}.`;
                const tdW = document.createElement('td');
                tdW.textContent = `#${i+1}`;
                const tdB = document.createElement('td');
                tdB.textContent = app.engine.history[i + 1] ? `#${i+2}` : '';
                tr.appendChild(tdNum);
                tr.appendChild(tdW);
                tr.appendChild(tdB);
                histEl.appendChild(tr);
                moveNum++;
            }
        }

        document.getElementById('btnNew').onclick = () => {
            app.engine.reset();
            app.lastMove = null;
            refreshPieces();
        };
        document.getElementById('btnUndo').onclick = () => {
            if (app.engine.undo()) {
                app.lastMove = null;
                refreshPieces();
            }
        };
        document.getElementById('btnFlip').onclick = () => {
            app.orientation = app.orientation === WHITE ? BLACK : WHITE;
            buildBoard();
        };
        document.getElementById('btnCopyFen').onclick = () => {
            fenEl.select();
            document.execCommand('copy');
        };
        document.getElementById('btnSetFen').onclick = () => {
            const s = prompt('Masukkan FEN:', app.engine.fen());
            if (!s) return;
            try {
                app.engine.loadFEN(s);
                app.lastMove = null;
                buildBoard();
            } catch (e) {
                alert('FEN tidak valid');
            }
        };
        document.getElementById('btnExportPGN').onclick = () => {
            alert('PGN sederhana belum diimplementasikan. Gunakan FEN untuk sekarang.');
        };

        vsAiEl.addEventListener('change', () => {
            app.vsAi = vsAiEl.checked;
            maybeAiMove();
            saveState();
        });
        aiPlaysBlackEl.addEventListener('change', () => {
            app.aiPlaysBlack = aiPlaysBlackEl.checked;
            maybeAiMove();
            saveState();
        });
        aiDepthEl.addEventListener('input', () => {
            AI.depth = parseInt(aiDepthEl.value, 10);
            depthLabel.textContent = AI.depth;
            saveState();
        });

        function maybeAiMove() {
            if (!app.vsAi) return;
            const aiIsBlack = app.aiPlaysBlack;
            const sideToMove = app.engine.turn;
            if ((aiIsBlack && sideToMove === BLACK) || (!aiIsBlack && sideToMove === WHITE)) {
                setTimeout(() => {
                    const mv = app.engine.generateMoves()[0];
                    if (mv) {
                        app.engine._makeMove(mv);
                        app.lastMove = {
                            from: mv.from,
                            to: mv.to
                        };
                        refreshPieces();
                    }
                }, 50);
            }
        }

        function saveState() {
            const data = {
                fen: app.engine.fen(),
                orientation: app.orientation,
                vsAi: app.vsAi,
                aiPlaysBlack: app.aiPlaysBlack,
                depth: AI.depth
            };
            try {
                localStorage.setItem('chess_ci3_singlefile', JSON.stringify(data));
            } catch (e) {}
        }

        function loadState() {
            try {
                const s = localStorage.getItem('chess_ci3_singlefile');
                if (!s) return;
                const d = JSON.parse(s);
                app.engine.loadFEN(d.fen);
                app.orientation = d.orientation || WHITE;
                app.vsAi = !!d.vsAi;
                vsAiEl.checked = app.vsAi;
                app.aiPlaysBlack = d.aiPlaysBlack !== undefined ? d.aiPlaysBlack : true;
                aiPlaysBlackEl.checked = app.aiPlaysBlack;
                AI.depth = d.depth || 2;
                aiDepthEl.value = AI.depth;
                depthLabel.textContent = AI.depth;
            } catch (e) {}
        }

        loadState();
        buildBoard();
    </script>
</body>

</html>