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

// Setelah DataTable inisialisasi
const $havCardBody = $('.card.card-tabs:last-child .card-body');
const initialCardHeight = $havCardBody.height(); // ambil tinggi awal
$havCardBody.css('min-height', initialCardHeight + 'px'); // kunci sebagai min-height
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
    const table = $('#employeeTable').DataTable();

    table.rows().every(function () {
        const data = this.data();
        const x = parseFloat(data[11]); // Potential
        const y = parseFloat(data[10]); // Performance

        if (isNaN(x) || isNaN(y)) return;

        for (const row of grid) {
            for (const cell of row) {
                const xMax = cell.xMin + bounds.xStep;
                const yMax = cell.yMin + bounds.yStep;

                if (x >= cell.xMin && x < xMax && y >= cell.yMin && y < yMax) {
                    data[7] = cell.label; // Update kolom status
                    this.data(data); // Update cache
                    return;
                }
            }
        }
    });

    // Redraw visual untuk munculin teks & warna di DOM
    table.rows({ page: 'current' }).every(function () {
        const $row = $(this.node());
        const data = this.data();
        const label = data[7];

        // Temukan warna dari label
        let color = '';
        outer: for (const row of grid) {
            for (const cell of row) {
                if (cell.label === label) {
                    color = cell.color;
                    break outer;
                }
            }
        }

        $row.find('td').eq(7).text(label).css('background-color', color);
    });
}

// Add this function to calculate status percentages
function calculateStatusPercentages(data, bounds) {
    const grid = buildLabelGrid(bounds);
    const statusCounts = {};
    const total = data.length;

    // Initialize counts
    for (const row of grid) {
        for (const cell of row) {
            statusCounts[cell.label] = 0;
        }
    }

    // Count each status
    for (const point of data) {
        for (const row of grid) {
            for (const cell of row) {
                const xMax = cell.xMin + bounds.xStep;
                const yMax = cell.yMin + bounds.yStep;

                if (point.x >= cell.xMin && point.x < xMax &&
                    point.y >= cell.yMin && point.y < yMax) {
                    statusCounts[cell.label]++;
                    break;
                }
            }
        }
    }

    // Calculate percentages
    const percentages = {};
    for (const [status, count] of Object.entries(statusCounts)) {
        percentages[status] = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
    }

    return percentages;
}

// Add this function to update the status summary display
function updateStatusSummary(percentages) {
    const summaryContainer = document.getElementById('statusSummary');
    summaryContainer.innerHTML = '';

    // Sort by percentage descending
    const sortedStatuses = Object.entries(percentages)
        .sort((a, b) => b[1] - a[1]);

    // Create a badge for each status
    for (const [status, percentage] of sortedStatuses) {
        const badge = document.createElement('span');
        badge.className = 'badge badge-primary';
        badge.style.backgroundColor = getStatusColor(status);
        badge.style.color = '#000';
        badge.style.fontSize = '14px';
        badge.style.padding = '8px';
        badge.innerHTML = `${status}: ${percentage}%`;
        summaryContainer.appendChild(badge);
    }
}

// Helper function to get color for a status
function getStatusColor(status) {
    for (const row of labels) {
        for (const cell of row) {
            if (cell === status) {
                const rowIndex = labels.indexOf(row);
                const colIndex = row.indexOf(cell);
                return colors[rowIndex][colIndex];
            }
        }
    }
    return '#ffffff';
}

function renderChart(chartData) {
    if (chart) chart.destroy();

    const ctx = document.getElementById('humanAssetChart').getContext('2d');
    const { data, bounds } = chartData;

    // Calculate and display status percentages
    const percentages = calculateStatusPercentages(data, bounds);
    updateStatusSummary(percentages);

    chart = new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Employee',
                data,
                backgroundColor: '#007bff',
                pointRadius: 5,
                borderColor: '#0056b3',
                borderWidth: 1
            }]
        },
        options: {
            animation: false,
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
    const scrollPos = $(window).scrollTop(); // Simpan posisi scroll sekarang
    renderChart(getFilteredChartData());
    $(window).scrollTop(scrollPos); // Kembalikan scroll ke posisi awal
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
