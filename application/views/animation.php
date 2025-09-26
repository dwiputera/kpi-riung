<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>ASCII 3D Gallery</title>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            background: black;
            color: white;
            font-family: monospace;
            height: 100vh;
            overflow: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #scene {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        pre {
            font-size: 12px;
            line-height: 13px;
            white-space: pre;
            width: 480px;
            height: 286px;
            padding-top: 5px;
            margin: 10px 0;
        }

        h1 {
            color: #0f0;
            margin: 10px 0;
            font-family: monospace;
        }
    </style>
</head>

<body>
    <h1>ASCII 3D Gallery</h1>
    <div id="scene">
        <pre id="donut"></pre>
        <pre id="cube"></pre>
        <pre id="ball"></pre>
        <pre id="wave"></pre>
        <pre id="spiral"></pre>
    </div>

    <script>
        const WIDTH = 60;
        const HEIGHT = 25;
        const CHARSET = ".,-~:;=!*#$@";

        // Donut (original)
        function initDonut() {
            const donut = document.getElementById("donut");
            let A = 0,
                B = 0;

            setInterval(() => {
                const buffer = Array(WIDTH * HEIGHT).fill(' ');
                const zBuffer = Array(WIDTH * HEIGHT).fill(0);

                for (let theta = 0; theta < Math.PI * 2; theta += 0.07) {
                    for (let phi = 0; phi < Math.PI * 2; phi += 0.02) {
                        const sinPhi = Math.sin(phi),
                            cosPhi = Math.cos(phi);
                        const sinTheta = Math.sin(theta),
                            cosTheta = Math.cos(theta);
                        const sinA = Math.sin(A),
                            cosA = Math.cos(A);
                        const sinB = Math.sin(B),
                            cosB = Math.cos(B);

                        const circleX = cosTheta + 2;
                        const D = 1 / (sinPhi * circleX * sinA + sinTheta * cosA + 5);
                        const t = sinPhi * circleX * cosA - sinTheta * sinA;

                        const x = Math.floor(WIDTH / 2 + 30 * D * (cosPhi * circleX * cosB - t * sinB));
                        const y = Math.floor(HEIGHT / 2 + 15 * D * (cosPhi * circleX * sinB + t * cosB));
                        const o = Math.floor(
                            8 * ((sinTheta * sinA - sinPhi * cosTheta * cosA) * cosB -
                                sinPhi * cosTheta * sinA - sinTheta * cosA - cosPhi * cosTheta * sinB)
                        );

                        const index = x + WIDTH * y;
                        if (y >= 0 && y < HEIGHT && x >= 0 && x < WIDTH && D > zBuffer[index]) {
                            zBuffer[index] = D;
                            buffer[index] = CHARSET[o > 0 ? o : 0] || '.';
                        }
                    }
                }

                donut.textContent = bufferToOutput(buffer);
                A += 0.04;
                B += 0.08;
            }, 25);
        }

        // Cube (original)
        function initCube() {
            const cube = document.getElementById("cube");
            let A = 0,
                B = 0,
                C = 0;

            const vertices = [
                [-1, -1, -1],
                [1, -1, -1],
                [1, 1, -1],
                [-1, 1, -1],
                [-1, -1, 1],
                [1, -1, 1],
                [1, 1, 1],
                [-1, 1, 1]
            ];

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

            setInterval(() => {
                const buffer = Array(WIDTH * HEIGHT).fill(' ');
                const zBuffer = Array(WIDTH * HEIGHT).fill(-Infinity);
                const projected = vertices.map(v => project(v, A, B, C));

                for (const [i, j] of edges) {
                    drawLine(buffer, zBuffer, projected[i], projected[j]);
                }

                cube.textContent = bufferToOutput(buffer);
                A += 0.02;
                B += 0.03;
                C += 0.01;
            }, 25);
        }

        // Ball (sphere)
        function initBall() {
            const ball = document.getElementById("ball");
            let A = 0,
                B = 0;

            setInterval(() => {
                const buffer = Array(WIDTH * HEIGHT).fill(' ');
                const zBuffer = Array(WIDTH * HEIGHT).fill(0);
                const R = 10,
                    K1 = 15;

                for (let theta = 0; theta < Math.PI; theta += 0.1) {
                    for (let phi = 0; phi < Math.PI * 2; phi += 0.1) {
                        const x = R * Math.sin(theta) * Math.cos(phi);
                        const y = R * Math.sin(theta) * Math.sin(phi);
                        const z = R * Math.cos(theta) + K1;

                        const [x1, y1, z2] = rotateXYZ(x, y, z, A, B, 0);
                        const ooz = 1 / z2;
                        const xp = Math.floor(WIDTH / 2 + x1 * ooz * 20);
                        const yp = Math.floor(HEIGHT / 2 + y1 * ooz * 10);
                        const L = Math.cos(theta) * Math.cos(A) + Math.sin(theta) * Math.sin(A);

                        if (L > 0) {
                            const idx = xp + WIDTH * yp;
                            if (xp >= 0 && xp < WIDTH && yp >= 0 && yp < HEIGHT && ooz > zBuffer[idx]) {
                                zBuffer[idx] = ooz;
                                buffer[idx] = CHARSET[Math.floor(L * 8)] || '@';
                            }
                        }
                    }
                }

                ball.textContent = bufferToOutput(buffer);
                A += 0.05;
                B += 0.03;
            }, 25);
        }

        // 3D Sine Wave
        function initWave() {
            const wave = document.getElementById("wave");
            let time = 0;

            setInterval(() => {
                const buffer = Array(WIDTH * HEIGHT).fill(' ');
                const zBuffer = Array(WIDTH * HEIGHT).fill(0);
                const scale = 10;

                for (let x = -WIDTH / 2; x < WIDTH / 2; x++) {
                    for (let z = -10; z < 10; z++) {
                        const y = Math.sin((x / 5) + time) * Math.cos((z / 5) + time) * 5;
                        const [xr, yr, zr] = rotateXYZ(x, y, z + 20, 0, time * 0.2, 0);
                        const ooz = 1 / (zr + 20);
                        const xp = Math.floor(WIDTH / 2 + xr * ooz * scale);
                        const yp = Math.floor(HEIGHT / 2 - yr * ooz * scale * 0.5);
                        const L = (y + 5) / 10;

                        const idx = xp + WIDTH * yp;
                        if (xp >= 0 && xp < WIDTH && yp >= 0 && yp < HEIGHT && ooz > zBuffer[idx]) {
                            zBuffer[idx] = ooz;
                            buffer[idx] = CHARSET[Math.floor(L * 10)] || '@';
                        }
                    }
                }

                wave.textContent = bufferToOutput(buffer);
                time += 0.1;
            }, 25);
        }

        // Spiral Galaxy
        function initSpiral() {
            const spiral = document.getElementById("spiral");
            let time = 0;

            setInterval(() => {
                const buffer = Array(WIDTH * HEIGHT).fill(' ');
                const zBuffer = Array(WIDTH * HEIGHT).fill(0);
                const scale = 8;
                const arms = 3;

                for (let i = 0; i < 500; i++) {
                    const r = Math.random() * 10;
                    const angle = r * 2 + time;
                    const armOffset = Math.floor(Math.random() * arms) * (2 * Math.PI / arms); // ← fixed

                    const x = Math.cos(angle + armOffset) * r;
                    const y = Math.sin(angle + armOffset) * r;
                    const z = (Math.random() - 0.5) * 5;

                    const [xr, yr, zr] = rotateXYZ(x, y, z + 20, time * 0.1, time * 0.2, 0);
                    const ooz = 1 / (zr + 20);
                    const xp = Math.floor(WIDTH / 2 + xr * ooz * scale);
                    const yp = Math.floor(HEIGHT / 2 - yr * ooz * scale * 0.5);
                    const L = (z + 3) / 6;

                    const idx = xp + WIDTH * yp;
                    if (xp >= 0 && xp < WIDTH && yp >= 0 && yp < HEIGHT && ooz > zBuffer[idx]) {
                        zBuffer[idx] = ooz;
                        buffer[idx] = CHARSET[Math.floor(L * 10)] || '*';
                    }
                }

                spiral.textContent = bufferToOutput(buffer);
                time += 0.05;
            }, 25);
        }

        // Helper functions
        function project([x, y, z], A, B, C) {
            const [xr, yr, zr] = rotateXYZ(x, y, z, A, B, C);
            const scale = 5 / (5 + zr);
            return {
                x: Math.floor(WIDTH / 2 + xr * scale * 20),
                y: Math.floor(HEIGHT / 2 + yr * scale * 10),
                z: zr
            };
        }

        function rotateXYZ(x, y, z, A, B, C) {
            const cosA = Math.cos(A),
                sinA = Math.sin(A);
            const cosB = Math.cos(B),
                sinB = Math.sin(B);
            const cosC = Math.cos(C),
                sinC = Math.sin(C);

            // Rotate around X axis
            const y1 = y * cosA - z * sinA;
            const z1 = y * sinA + z * cosA;

            // Rotate around Y axis
            const x1 = x * cosB + z1 * sinB;
            const z2 = -x * sinB + z1 * cosB;

            // Rotate around Z axis
            const x2 = x1 * cosC - y1 * sinC;
            const y2 = x1 * sinC + y1 * cosC;

            return [x2, y2, z2];
        }

        function drawLine(buffer, zBuffer, v1, v2) {
            const dx = Math.abs(v2.x - v1.x);
            const dy = Math.abs(v2.y - v1.y);
            const sx = v1.x < v2.x ? 1 : -1;
            const sy = v1.y < v2.y ? 1 : -1;

            let err = dx - dy;
            let x = v1.x,
                y = v1.y;
            const steps = Math.max(dx, dy);
            const zStep = (v2.z - v1.z) / steps;
            let z = v1.z;

            while (true) {
                if (x >= 0 && x < WIDTH && y >= 0 && y < HEIGHT) {
                    const idx = x + WIDTH * y;
                    if (z > zBuffer[idx]) {
                        zBuffer[idx] = z;
                        const charIdx = Math.floor((z + 5) * 2);
                        buffer[idx] = CHARSET[charIdx % CHARSET.length] || '@';
                    }
                }

                if (x === v2.x && y === v2.y) break;
                const e2 = 2 * err;
                if (e2 > -dy) {
                    err -= dy;
                    x += sx;
                }
                if (e2 < dx) {
                    err += dx;
                    y += sy;
                }
                z += zStep;
            }
        }

        function bufferToOutput(buffer) {
            let output = '';
            for (let i = 0; i < buffer.length; i++) {
                output += buffer[i];
                if ((i + 1) % WIDTH === 0) output += '\n';
            }
            return output;
        }

        // Initialize all animations
        window.onload = function() {
            initDonut();
            initCube();
            initBall();
            initWave();
            initSpiral();
        };
    </script>
</body>

</html>