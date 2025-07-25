const rawLabels = [
    ['Solid Contributor', 'Promotable', 'Prostar 2', 'Top Talent'],
    ['Solid Contributor', 'Promotable', 'Promotable', 'Prostar 1'],
    ['Solid Contributor', 'Promotable', 'Promotable', 'Promotable'],
    ['Unfit', 'Sleeping Tiger', 'Sleeping Tiger', 'Sleeping Tiger']
];

const rawColors = [
    ['#ffffcc', '#ccffcc', '#e0ffe0', '#ccf2ff'],
    ['#ffffcc', '#ccffcc', '#ccffcc', '#e0ffe0'],
    ['#ffffcc', '#ccffcc', '#ccffcc', '#ccffcc'],
    ['#ffcccc', '#ffffcc', '#ffffcc', '#ffffcc']
];

const labels = [...rawLabels].reverse();
const colors = [...rawColors].reverse();

let chart;
let showLabels = true;
let isFilterFrozen = false;
let frozenData = [];
let frozenBounds = null;

// Initialize
setupFilterableDatatable($('.datatable-filter-column'));
const table = $('#employeeTable').DataTable();

// Custom Chart.js Plugin for Matrix Background
const CustomMatrixBackground = {
    id: 'customMatrixBackground',
    beforeDraw(chart) {
        const { ctx, scales: { x, y } } = chart;
        const xStep = (x.max - x.min) / 4;
        const yStep = (y.max - y.min) / 4;

        for (let row = 0; row < 4; row++) {
            for (let col = 0; col < 4; col++) {
                const x0 = x.min + col * xStep;
                const y0 = y.min + row * yStep;
                const xStart = x.getPixelForValue(x0);
                const xEnd = x.getPixelForValue(x0 + xStep);
                const yStart = y.getPixelForValue(y0 + yStep);
                const yEnd = y.getPixelForValue(y0);

                ctx.fillStyle = colors[row][col];
                ctx.fillRect(xStart, yStart, xEnd - xStart, yEnd - yStart);

                ctx.fillStyle = 'rgba(0,0,0,0.2)';
                ctx.font = 'bold 22px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(labels[row][col], (xStart + xEnd) / 2, (yStart + yEnd) / 2);
            }
        }
    }
};

function calculateChartBounds(data, padding = 5) {
    const xs = data.map(d => d.x);
    const ys = data.map(d => d.y);

    const xMin = Math.floor(Math.min(...xs) - padding);
    const xMax = Math.ceil(Math.max(...xs) + padding);
    const yMin = Math.floor(Math.min(...ys) - padding);
    const yMax = Math.ceil(Math.max(...ys) + padding);

    return {
        xMin, xMax, yMin, yMax,
        xStep: (xMax - xMin) / 4,
        yStep: (yMax - yMin) / 4
    };
}


function getFilteredChartData() {
    const currentData = table.rows({ filter: 'applied' }).data().toArray().map(row => {
        const x = parseFloat(row[11]);
        const y = parseFloat(row[10]);
        if (!isNaN(x) && !isNaN(y)) {
            return { label: row[9] || '(Tanpa Nama)', x, y };
        }
        return null;
    }).filter(Boolean);

    if (isFilterFrozen) {
        // When frozen, use current data but keep original bounds
        return {
            data: currentData,
            bounds: frozenBounds
        };
    } else {
        // When not frozen, calculate new bounds
        const bounds = calculateChartBounds(currentData);
        frozenBounds = bounds; // Update frozen bounds
        return {
            data: currentData,
            bounds: bounds
        };
    }
}

function buildLabelGrid(bounds) {
    return labels.map((row, i) => row.map((label, j) => ({
        label,
        color: colors[i][j],
        xMin: bounds.xMin + j * bounds.xStep,
        yMin: bounds.yMin + i * bounds.yStep
    })));
}

function updateTableLabelColumn(bounds) {
    const grid = buildLabelGrid(bounds);

    $('#employeeTable tbody tr').each(function () {
        const $cols = $(this).find('td');
        const x = parseFloat($cols.eq(11).text());
        const y = parseFloat($cols.eq(10).text());

        if (isNaN(x) || isNaN(y)) return;

        for (const row of grid) {
            for (const cell of row) {
                const xMax = cell.xMin + bounds.xStep;
                const yMax = cell.yMin + bounds.yStep;

                if (x >= cell.xMin && x < xMax && y >= cell.yMin && y < yMax) {
                    $cols.eq(7).text(cell.label).css('background-color', cell.color);
                    return;
                }
            }
        }
    });
}

function renderChart(chartData) {
    if (chart) chart.destroy();

    const ctx = document.getElementById('humanAssetChart').getContext('2d');
    const { data, bounds } = chartData;

    chart = new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: [{
                data,
                backgroundColor: '#007bff',
                pointRadius: 5,
                borderColor: '#0056b3',
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                datalabels: {
                    display: () => showLabels,
                    align: 'top',
                    anchor: 'end',
                    font: { size: 10 },
                    formatter: val => val.label,
                    color: '#000'
                },
                tooltip: {
                    enabled: () => !showLabels,
                    callbacks: {
                        label: ctx => `${ctx.raw.label} (Potential: ${ctx.raw.x}, Performance: ${ctx.raw.y})`
                    }
                },
                title: {
                    display: true,
                    text: 'HUMAN ASSET VALUE MAP'
                }
            },
            scales: {
                x: {
                    title: { display: true, text: 'potential' },
                    min: bounds.xMin,
                    max: bounds.xMax,
                    ticks: { stepSize: bounds.xStep }
                },
                y: {
                    title: { display: true, text: 'performance' },
                    min: bounds.yMin,
                    max: bounds.yMax,
                    ticks: { stepSize: bounds.yStep }
                }
            }
        },
        plugins: [ChartDataLabels, CustomMatrixBackground]
    });

    updateTableLabelColumn(bounds);
}

// Initial Render
renderChart(getFilteredChartData());

// Redraw on table interaction
table.on('draw', () => {
    renderChart(getFilteredChartData());
});

// Toggle labels on checkbox change
document.getElementById('toggleLabels').addEventListener('change', e => {
    showLabels = e.target.checked;
    chart.update();
});

document.getElementById('freezeFilter').addEventListener('change', e => {
    isFilterFrozen = e.target.checked;

    if (isFilterFrozen) {
        // Store current bounds when freezing
        const currentData = getFilteredChartData().data;
        frozenBounds = calculateChartBounds(currentData);
    }

    // Always render with current filter state
    renderChart(getFilteredChartData());
});
