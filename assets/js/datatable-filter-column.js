let currentFilters = {};

function setupFilterableDatatable($table) {
    const tableId = $table.attr('id') || 'datatable';

    // ✅ Destroy jika sudah ada DataTable aktif
    if ($.fn.DataTable.isDataTable($table)) {
        $table.DataTable().destroy();     // Hancurkan instance lama
        $table.find('thead tr:last').remove(); // Hapus row filter lama
        $table.removeClass('dataTable');  // Reset class bawaan
    }
    const pagePath = window.location.pathname.replace(/\W+/g, '_'); // nama halaman unik
    const tableIndex = $table.index('table'); // urutan tabel di halaman
    const storageKey = `excelFilters_${pagePath}_${tableId}_${tableIndex}`;
    const isServerSide = $table.data('server') === true || $table.data('server') === 'true';
    const $thead = $table.find('thead');
    const $headerRow = $thead.find('tr').first();
    const $filterRow = $('<tr>');

    // Tambahkan baris filter button
    $headerRow.find('th').each(function (i) {
        const $cell = $('<th class="p-0 text-center">');
        $cell.html(`
            <button type="button" class="btn btn-sm btn-light w-100 filter-btn" data-col="${i}" data-active="false">
                <i class="fas fa-filter"></i>
            </button>
        `);
        $filterRow.append($cell);
    });
    $thead.append($filterRow);

    const dtOptions = {
        autoWidth: false,
        buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
        lengthChange: true,
        pageLength: 10,
        scrollX: true,
        orderCellsTop: true,
        fixedHeader: true,
        serverSide: isServerSide,
        initComplete: function () {
            initExcelFilters(this.api(), storageKey);
            restoreFilters(this.api(), storageKey); // 🔥 Restore filter saat init
        },
        columnDefs: [
            {
                targets: "_all",
                render: function (data, type, row, meta) {
                    const api = new $.fn.dataTable.Api(meta.settings);
                    const cellNode = api.cell(meta.row, meta.col).node();
                    const $input = $(cellNode).find('input, select');

                    if (type === 'sort' || type === 'filter') {
                        if ($input.length) {
                            if ($input.is('[type="checkbox"]')) {
                                return $input.prop('checked') ? 'Checked' : 'Unchecked';
                            }
                            if ($input.is('[type="number"]')) return parseFloat($input.val() || 0);
                            if ($input.is('[type="date"]')) return $input.val() || '';
                            return $input.val() || '';
                        }
                        return $(cellNode).text().trim();
                    }
                    return data;
                }
            }
        ]
    };

    if (isServerSide) {
        dtOptions.ajax = {
            url: $table.data('url'),
            type: 'POST',
            data: function (d) {
                d.excelFilters = currentFilters;
            }
        };
    }

    const dt = $table.DataTable(dtOptions);
    positionButtons(dt, $table);

    // ✅ Tambahkan tombol "Clear All Filters" setelah inisialisasi tabel
    $('<button class="btn btn-sm btn-danger ml-2 clear-all-filters">Clear All Filters</button>')
        .appendTo($table.closest('.dataTables_wrapper').find('.col-md-6:eq(0)'));

    // ✅ Event listener tombol clear all
    $(document).off('click.clearAll').on('click.clearAll', '.clear-all-filters', function (e) {
        e.preventDefault();  // STOP reload halaman
        currentFilters = {};
        localStorage.removeItem(storageKey);
        rebuildFilters(dt);
        $('.filter-btn').attr('data-active', false).removeClass('btn-warning').addClass('btn-light');
    });
}

function initExcelFilters(api, storageKey) {
    $(document).off('click', '.filter-btn').on('click', '.filter-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const colIdx = $(this).data('col');
        const column = api.column(colIdx);
        showExcelFilterPopup($(this), column, api, storageKey);
    });
}

function restoreFilters(api, storageKey) {
    const saved = localStorage.getItem(storageKey);
    if (!saved) return;

    currentFilters = JSON.parse(saved);
    $.each(currentFilters, (idx, filter) => {
        if (!filter) return;
        applyFilterLogic(api, idx, filter);
        updateFilterButton($(`.filter-btn[data-col="${idx}"]`), true);
    });
    api.draw();
}

function saveFilters(storageKey) {
    localStorage.setItem(storageKey, JSON.stringify(currentFilters));
}

function applyFilterLogic(api, colIdx, filter) {
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        const raw = getCellValue(api, dataIndex, colIdx);
        const text = raw.toLowerCase();
        const num = parseFloat(raw);
        const dateVal = new Date(raw);

        // Checkbox filter
        if (filter.selected?.length && !filter.selected.includes(raw)) return false;

        switch (filter.filterType) {
            case 'equals': return text === filter.v1.toLowerCase();
            case 'not_equals': return text !== filter.v1.toLowerCase();
            case 'contains': return text.includes(filter.v1.toLowerCase());
            case 'not_contains': return !text.includes(filter.v1.toLowerCase());
            case 'begins': return text.startsWith(filter.v1.toLowerCase());
            case 'ends': return text.endsWith(filter.v1.toLowerCase());
            case 'num_equals': return num == parseFloat(filter.v1);
            case 'num_not_equals': return num != parseFloat(filter.v1);
            case 'greater': return num > parseFloat(filter.v1);
            case 'greater_eq': return num >= parseFloat(filter.v1);
            case 'less': return num < parseFloat(filter.v1);
            case 'less_eq': return num <= parseFloat(filter.v1);
            case 'between': return num >= parseFloat(filter.v1) && num <= parseFloat(filter.v2);
            case 'date_equals': return formatDate(dateVal) === filter.v1;
            case 'date_before': return dateVal < new Date(filter.v1);
            case 'date_after': return dateVal > new Date(filter.v1);
            case 'date_between': return dateVal >= new Date(filter.v1) && dateVal <= new Date(filter.v2);
        }
        return true;
    });
}

function getCellValue(api, dataIndex, colIdx) {
    const cellNode = api.cell(dataIndex, colIdx).node();
    const $cell = $(cellNode);
    const $input = $cell.find('input, select, textarea');

    if ($input.length) {
        if ($input.is('[type="checkbox"]')) {
            return $input.is(':checked') ? 'Checked' : 'Unchecked'; // ✅ khusus checkbox
        }
        return $input.val() || '';
    }
    return $cell.text().trim();
}

function showExcelFilterPopup($btn, column, api, storageKey) {
    const colIdx = column.index();
    const values = [...new Set(
        column.nodes().to$().map(function () {
            const $input = $(this).find('input, select, textarea');
            if ($input.length) {
                if ($input.is('[type="checkbox"]')) {
                    return $input.is(':checked') ? 'Checked' : 'Unchecked';
                }
                return $input.val();
            }
            return $(this).text().trim();
        }).get()
    )].sort();

    const existingFilter = currentFilters[colIdx] || { selected: [], filterType: 'contains', v1: '', v2: '' };
    const sel = (type) => existingFilter.filterType === type ? 'selected' : '';

    // Popup HTML
    let html = `
    <div class="excel-filter-popup card shadow p-2" style="position:absolute; z-index:9999; width:260px;">
        <div class="popup-header bg-light p-1 mb-2" style="cursor:move;">
            <strong>Filter</strong>
            <button type="button" class="close float-right">&times;</button>
        </div>
        <input type="text" class="form-control form-control-sm mb-2 filter-search" placeholder="Search options..."/>
        <div class="options mb-2" style="max-height:150px; overflow:auto;">
            <label class="mb-0"><input type="checkbox" class="check-all"> <b>Select All</b></label><hr class="my-1">
    `;

    values.forEach(v => {
        const checked = !existingFilter.selected.length || existingFilter.selected.includes(v) ? 'checked' : '';
        html += `<label><input type="checkbox" class="chk-item" value="${v}" ${checked}> ${v || '(Blank)'}</label><br>`;
    });

    html += `
        </div><hr>
        <small class="text-muted">Custom Filter:</small>
        <select class="form-control form-control-sm mb-2 filter-type">
            <optgroup label="Text Filters">
                <option value="">(None)</option>
                <option value="equals" ${sel('equals')}>Equals</option>
                <option value="not_equals" ${sel('not_equals')}>Does Not Equal</option>
                <option value="contains" ${sel('contains')}>Contains</option>
                <option value="not_contains" ${sel('not_contains')}>Does Not Contain</option>
                <option value="begins" ${sel('begins')}>Begins With</option>
                <option value="ends" ${sel('ends')}>Ends With</option>
            </optgroup>
            <optgroup label="Number Filters">
                <option value="num_equals" ${sel('num_equals')}>Equals</option>
                <option value="num_not_equals" ${sel('num_not_equals')}>Does Not Equal</option>
                <option value="greater" ${sel('greater')}>Greater Than</option>
                <option value="greater_eq" ${sel('greater_eq')}>Greater Than or Equal</option>
                <option value="less" ${sel('less')}>Less Than</option>
                <option value="less_eq" ${sel('less_eq')}>Less Than or Equal</option>
                <option value="between" ${sel('between')}>Between</option>
            </optgroup>
            <optgroup label="Date Filters">
                <option value="date_equals" ${sel('date_equals')}>Equals</option>
                <option value="date_before" ${sel('date_before')}>Before</option>
                <option value="date_after" ${sel('date_after')}>After</option>
                <option value="date_between" ${sel('date_between')}>Between</option>
            </optgroup>
        </select>
        <input type="text" class="form-control form-control-sm mb-1 filter-value1" value="${existingFilter.v1 || ''}" placeholder="Value 1"/>
        <input type="text" class="form-control form-control-sm mb-2 filter-value2 ${['between', 'date_between'].includes(existingFilter.filterType) ? '' : 'd-none'}" value="${existingFilter.v2 || ''}" placeholder="Value 2"/>
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-sm btn-primary apply-filter">Apply</button>
            <button class="btn btn-sm btn-secondary clear-filter">Clear</button>
        </div>
    </div>`;

    $('.excel-filter-popup').remove();
    const $popup = $(html).appendTo('body');

    // ✅ SET DEFAULT SELECT ALL
    const totalItems = $popup.find('.chk-item').length;
    const checkedItems = $popup.find('.chk-item:checked').length;
    $popup.find('.check-all').prop('checked', totalItems > 0 && checkedItems === totalItems);

    // Posisi popup
    const rect = $btn[0].getBoundingClientRect();
    const top = rect.bottom + window.scrollY + 4;
    let left = rect.left + window.scrollX;
    if (left + 260 > window.innerWidth) left = window.innerWidth - 270;
    $popup.css({ top, left });

    makePopupDraggable($popup);
    $popup.find('.close').on('click', () => $popup.remove());

    // Search filter
    $popup.find('.filter-search').on('keyup', function () {
        const q = $(this).val().toLowerCase();
        $popup.find('.chk-item').each(function () {
            $(this).parent().toggle($(this).val().toLowerCase().includes(q));
        });
    });

    // Select All
    $popup.find('.check-all').on('change', function () {
        $popup.find('.chk-item').prop('checked', $(this).is(':checked'));
    });

    // Autofocus + Enter langsung apply
    const $v1 = $popup.find('.filter-value1');
    $v1.focus();
    $popup.on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $popup.find('.apply-filter').trigger('click'); } });

    // Update tipe input sesuai filter
    const $v2 = $popup.find('.filter-value2');
    const updateInputType = (type) => {
        $v2.toggleClass('d-none', !['between', 'date_between'].includes(type));
        if (!type || type.startsWith('equals') || type.includes('contains')) { $v1.attr('type', 'text'); $v2.attr('type', 'text'); }
        else if (type.startsWith('num') || type === 'greater' || type === 'less' || type === 'between') { $v1.attr('type', 'number'); $v2.attr('type', 'number'); }
        else if (type.startsWith('date')) { $v1.attr('type', 'date'); $v2.attr('type', 'date'); }
    };
    updateInputType(existingFilter.filterType);
    $popup.find('.filter-type').on('change', function () { updateInputType($(this).val()); });

    // Apply Filter
    $popup.find('.apply-filter').on('click', function () {
        const selected = $popup.find('.chk-item:checked').map((_, el) => $(el).val()).get();
        currentFilters[colIdx] = { selected, filterType: $popup.find('.filter-type').val(), v1: $v1.val(), v2: $v2.val() };
        saveFilters(storageKey); // ✅ simpan filter setelah apply
        rebuildFilters(api);
        updateFilterButton($btn, true);
        $popup.remove();
    });

    // Clear Filter
    $popup.find('.clear-filter').on('click', function () {
        currentFilters[colIdx] = null;
        saveFilters(storageKey); // ✅ simpan filter setelah clear
        rebuildFilters(api);
        updateFilterButton($btn, false);
        $popup.remove();
    });

    $(document).on('click.excelFilter', function (e) {
        if (!$(e.target).closest('.excel-filter-popup, .filter-btn').length) {
            $popup.remove();
            $(document).off('click.excelFilter');
        }
    });
}

function rebuildFilters(api) {
    $.fn.dataTable.ext.search = [];
    $.each(currentFilters, function (idx, filter) {
        if (!filter) return;
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            const raw = getCellValue(api, dataIndex, idx);
            const text = raw.toLowerCase(), num = parseFloat(raw), dateVal = new Date(raw);
            if (filter.selected?.length && !filter.selected.includes(raw)) return false;
            switch (filter.filterType) {
                case 'equals': return text === filter.v1.toLowerCase();
                case 'not_equals': return text !== filter.v1.toLowerCase();
                case 'contains': return text.includes(filter.v1.toLowerCase());
                case 'not_contains': return !text.includes(filter.v1.toLowerCase());
                case 'begins': return text.startsWith(filter.v1.toLowerCase());
                case 'ends': return text.endsWith(filter.v1.toLowerCase());
                case 'num_equals': return num == parseFloat(filter.v1);
                case 'num_not_equals': return num != parseFloat(filter.v1);
                case 'greater': return num > parseFloat(filter.v1);
                case 'greater_eq': return num >= parseFloat(filter.v1);
                case 'less': return num < parseFloat(filter.v1);
                case 'less_eq': return num <= parseFloat(filter.v1);
                case 'between': return num >= parseFloat(filter.v1) && num <= parseFloat(filter.v2);
                case 'date_equals': return formatDate(dateVal) === filter.v1;
                case 'date_before': return dateVal < new Date(filter.v1);
                case 'date_after': return dateVal > new Date(filter.v1);
                case 'date_between': return dateVal >= new Date(filter.v1) && dateVal <= new Date(filter.v2);
            }
            return true;
        });
    });
    api.draw();
}

function updateFilterButton($btn, active) {
    $btn.attr('data-active', active);
    $btn.toggleClass('btn-warning', active).toggleClass('btn-light', !active);
}

function positionButtons(dt, $table) {
    dt.buttons().container().appendTo($table.closest('.dataTables_wrapper').find('.col-md-6:eq(0)'));
}

function makePopupDraggable($popup) {
    const $header = $popup.find('.popup-header');
    let isDragging = false, offsetX = 0, offsetY = 0;
    $header.on('mousedown', function (e) { isDragging = true; offsetX = e.pageX - $popup.offset().left; offsetY = e.pageY - $popup.offset().top; $('body').addClass('unselectable'); });
    $(document).on('mousemove', function (e) { if (isDragging) $popup.css({ top: e.pageY - offsetY, left: e.pageX - offsetX }); })
        .on('mouseup', function () { isDragging = false; $('body').removeClass('unselectable'); });
}

function formatDate(d) { return d instanceof Date && !isNaN(d) ? d.toISOString().split('T')[0] : ''; }
