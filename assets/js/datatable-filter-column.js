(function ($) {
    // =========================
    // UI Overlay Helper
    // =========================
    function showTableOverlay($table, text = 'Creating row(s)...') {
        const $wrap = $table.closest('.dataTables_wrapper');

        // prevent duplicate
        if ($wrap.find('.dt-overlay').length) return;

        const overlay = `
            <div class="dt-overlay"
                style="
                    position:absolute;
                    inset:0;
                    background:rgba(255,255,255,0.75);
                    z-index:1050;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-size:14px;
                    font-weight:600;
                    color:#333;
                ">
                <div>
                <span class="spinner-border spinner-border-sm mr-2"></span>
                ${text}
                </div>
            </div>`;

        // wrapper harus relative
        if ($wrap.css('position') === 'static') {
            $wrap.css('position', 'relative');
        }

        $wrap.append(overlay);
    }

    function hideTableOverlay($table) {
        $table.closest('.dataTables_wrapper')
            .find('.dt-overlay')
            .remove();
    }

    // Global registries (scoped to window for persistence across inits)
    window.tableFilters = window.tableFilters || {}; // { [tableId]: { [colIdx]: filterObj } }
    window.tableFilterFns = window.tableFilterFns || {}; // { [tableId]: fn }

    // === NEW: Undo/Redo stacks per table ===
    window.tableUndoStacks = window.tableUndoStacks || {}; // { [tableId]: [ {changes:[{r,c,oldVal,newVal}], type:'paste'} ] }
    window.tableRedoStacks = window.tableRedoStacks || {}; // same shape
    window.lastActiveTableId = window.lastActiveTableId || null;

    function getStorageKey($table) {
        const tableId = $table.attr('id') || 'datatable';
        const pagePath = window.location.pathname.replace(/\W+/g, '_');
        const tableIndex = $table.index('table');
        return `excelFilters_${pagePath}_${tableId}_${tableIndex}`;
    }

    function getTableId($table) {
        const id = $table.attr('id') || `datatable_${Math.random().toString(36).slice(2, 8)}`;
        if (!$table.attr('id')) $table.attr('id', id);
        return id;
    }

    function ensureStacks(tableId) {
        if (!window.tableUndoStacks[tableId]) window.tableUndoStacks[tableId] = [];
        if (!window.tableRedoStacks[tableId]) window.tableRedoStacks[tableId] = [];
    }

    function pushUndo(tableId, action) {
        ensureStacks(tableId);
        window.tableUndoStacks[tableId].push(action);
        // setiap aksi baru mengosongkan redo
        window.tableRedoStacks[tableId] = [];
    }

    function applyChanges(dt, changes, useOld) {
        // useOld=true untuk undo, false untuk redo
        changes.forEach(ch => {
            const val = useOld ? ch.oldVal : ch.newVal;
            const node = dt.cell(ch.r, ch.c).node();
            if (node) node.textContent = val;
            dt.cell(ch.r, ch.c).data(val);
        });
        // jangan redraw penuh supaya cepat; draw(false) menjaga paging
        dt.draw(false);
    }

    function doUndo($table) {
        if (!$table || !$table.length) return;
        const tableId = getTableId($table);
        ensureStacks(tableId);
        const stack = window.tableUndoStacks[tableId];
        if (!stack.length) return;

        const dt = $table.DataTable();
        const action = stack.pop(); // ambil aksi terakhir
        applyChanges(dt, action.changes, /*useOld*/ true);
        window.tableRedoStacks[tableId].push(action);
    }

    function doRedo($table) {
        if (!$table || !$table.length) return;
        const tableId = getTableId($table);
        ensureStacks(tableId);
        const rstack = window.tableRedoStacks[tableId];
        if (!rstack.length) return;

        const dt = $table.DataTable();
        const action = rstack.pop();
        applyChanges(dt, action.changes, /*useOld*/ false);
        window.tableUndoStacks[tableId].push(action);
    }

    function setupFilterableDatatable($table) {
        if (!$table || !$table.length) return;
        const tableId = getTableId($table);

        // Ensure per-table store exists
        if (!window.tableFilters[tableId]) window.tableFilters[tableId] = {};
        ensureStacks(tableId);

        // Destroy prior DataTable instance safely
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().destroy();
            $table.find('thead tr:last').remove(); // remove old filter row
            $table.removeClass('dataTable');
        }

        const storageKey = getStorageKey($table);
        const isServerSide = $table.data('server') === true || $table.data('server') === 'true';

        // Build filter button row (appended as the last thead row)
        const $thead = $table.find('thead');
        const $headerRow = $thead.find('tr').last();
        const $filterRow = $('<tr>');
        $headerRow.find('th').each(function (i) {
            const $cell = $('<th class="p-0 text-center">');
            $cell.html(`
        <button type="button" class="btn btn-sm btn-light w-100 filter-btn"
                data-col="${i}" data-table="${tableId}" data-active="false">
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
            lengthMenu: [
                [5, 10, 25, 100, -1], // values
                [5, 10, 25, 100, "All"] // corresponding labels
            ],
            scrollX: true,
            orderCellsTop: true,
            fixedHeader: true,
            serverSide: isServerSide,
            initComplete: function () {
                const api = this.api();
                const $wrap = $table.closest('.dataTables_wrapper');
                // Add Clear All per-wrapper (avoid duplicates)
                if ($wrap.find('.clear-all-filters').length === 0) {
                    $('<button class="btn btn-sm btn-danger ml-2 clear-all-filters">Clear All Filters</button>')
                        .appendTo($wrap.find('.col-md-6:eq(0)'));
                }
                initExcelFilters(api, storageKey, tableId, $table);
                restoreFilters(api, storageKey, tableId, $table);
            },
            columnDefs: [{
                targets: "_all",
                render: function (data, type, row, meta) {
                    // Ensure sorting/filtering uses input/select values inside cells
                    const api = new $.fn.dataTable.Api(meta.settings);
                    const cellNode = api.cell(meta.row, meta.col).node();
                    const $input = $(cellNode).find('input, select');
                    if (type === 'sort' || type === 'filter') {
                        if ($input.length) {
                            if ($input.is('[type="checkbox"]')) return $input.prop('checked') ? 'Checked' : 'Unchecked';
                            if ($input.is('[type="number"]')) return parseFloat($input.val() || 0);
                            if ($input.is('[type="date"]')) return $input.val() || '';
                            return $input.val() || '';
                        }
                        return $(cellNode).text().trim();
                    }
                    return data;
                }
            }],
        };

        if (isServerSide) {
            dtOptions.ajax = {
                url: $table.data('url'),
                type: 'POST',
                data: function (d) {
                    d.excelFilters = window.tableFilters[tableId] || {};
                }
            };
        }

        const dt = $table.DataTable(dtOptions).on('draw', function () {
            let $select2 = $('.select2', this);
            if ($select2.length && window.FuzzySelect2 && typeof window.FuzzySelect2.apply === 'function') {
                window.FuzzySelect2.apply($select2);
            }
        });
        positionButtons(dt, $table);

        // === NEW: track last active table for keyboard shortcuts ===
        $table.on('focusin', 'td, th, input, select, textarea, [contenteditable="true"]', function () {
            window.lastActiveTableId = tableId;
        });
    }

    function initExcelFilters(api, storageKey, tableId, $table) {
        const $wrap = $table.closest('.dataTables_wrapper');

        // Namespace event handlers per tableId
        $wrap.off(`click.filterBtn.${tableId}`)
            .on(`click.filterBtn.${tableId}`, '.filter-btn', function (e) {
                e.preventDefault(); e.stopPropagation();
                const colIdx = $(this).data('col');
                const column = api.column(colIdx);
                showExcelFilterPopup($(this), column, api, storageKey, tableId, $table);
            });

        $wrap.off(`click.clearAll.${tableId}`)
            .on(`click.clearAll.${tableId}`, '.clear-all-filters', function (e) {
                e.preventDefault();
                window.tableFilters[tableId] = {};
                localStorage.removeItem(storageKey);
                rebuildFilters(api, tableId);
                $wrap.find('.filter-btn').attr('data-active', false)
                    .removeClass('btn-warning').addClass('btn-light');
            });
    }

    function restoreFilters(api, storageKey, tableId, $table) {
        const raw = localStorage.getItem(storageKey);
        if (!raw) return;
        try {
            const parsed = JSON.parse(raw) || {};
            window.tableFilters[tableId] = parsed;
        } catch (err) {
            window.tableFilters[tableId] = {};
        }

        const $wrap = $table.closest('.dataTables_wrapper');
        $.each(window.tableFilters[tableId], function (idx, filter) {
            if (!filter) return;
            updateFilterButton($wrap.find(`.filter-btn[data-col="${idx}"]`), true);
        });
        rebuildFilters(api, tableId);
    }

    function saveFilters(storageKey, tableId) {
        localStorage.setItem(storageKey, JSON.stringify(window.tableFilters[tableId] || {}));
    }

    function rebuildFilters(api, tableId) {
        // Remove previous callback for this table only
        if (window.tableFilterFns[tableId]) {
            $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(fn => fn !== window.tableFilterFns[tableId]);
        }

        const filters = window.tableFilters[tableId] || {};

        const fn = function (settings, data, dataIndex) {
            // Scope: only apply to this specific table
            if (settings.nTable !== api.table().node()) return true;

            // Evaluate all column filters for this table
            for (const [idx, filter] of Object.entries(filters)) {
                if (!filter) continue;
                const raw = getCellValue(api, dataIndex, +idx);
                const text = (raw || '').toLowerCase();
                const num = parseFloat(raw);
                const dateVal = new Date(raw);

                if (filter.selected?.length && !filter.selected.includes(raw)) return false;

                switch (filter.filterType) {
                    case 'equals': if (text !== (filter.v1 || '').toLowerCase()) return false; break;
                    case 'not_equals': if (text === (filter.v1 || '').toLowerCase()) return false; break;
                    case 'contains': if (!text.includes((filter.v1 || '').toLowerCase())) return false; break;
                    case 'not_contains': if (text.includes((filter.v1 || '').toLowerCase())) return false; break;
                    case 'begins': if (!text.startsWith((filter.v1 || '').toLowerCase())) return false; break;
                    case 'ends': if (!text.endsWith((filter.v1 || '').toLowerCase())) return false; break;
                    case 'num_equals': if (!(num == parseFloat(filter.v1))) return false; break;
                    case 'num_not_equals': if (!(num != parseFloat(filter.v1))) return false; break;
                    case 'greater': if (!(num > parseFloat(filter.v1))) return false; break;
                    case 'greater_eq': if (!(num >= parseFloat(filter.v1))) return false; break;
                    case 'less': if (!(num < parseFloat(filter.v1))) return false; break;
                    case 'less_eq': if (!(num <= parseFloat(filter.v1))) return false; break;
                    case 'between': if (!(num >= parseFloat(filter.v1) && num <= parseFloat(filter.v2))) return false; break;
                    case 'date_equals': if (formatDate(dateVal) !== filter.v1) return false; break;
                    case 'date_before': if (!(dateVal < new Date(filter.v1))) return false; break;
                    case 'date_after': if (!(dateVal > new Date(filter.v1))) return false; break;
                    case 'date_between': if (!(dateVal >= new Date(filter.v1) && dateVal <= new Date(filter.v2))) return false; break;
                    case '':              /* (None) */ break;
                    default:              /* unknown type -> ignore */ break;
                }
            }
            return true;
        };

        $.fn.dataTable.ext.search.push(fn);
        window.tableFilterFns[tableId] = fn;

        api.draw(false); // keep paging

    }

    function getCellValue(api, dataIndex, colIdx) {
        const cellNode = api.cell(dataIndex, colIdx).node();
        const $cell = $(cellNode);
        const $input = $cell.find('input, select, textarea');

        if ($input.length) {
            if ($input.is('[type="checkbox"]')) return $input.is(':checked') ? 'Checked' : 'Unchecked';
            return $input.val() || '';
        }
        return $cell.text().trim();
    }

    function showExcelFilterPopup($btn, column, api, storageKey, tableId, $table) {
        const colIdx = column.index();

        const values = [...new Set(
            column.nodes().to$().map(function () {
                const $input = $(this).find('input, select, textarea');
                if ($input.length) {
                    if ($input.is('[type="checkbox"]')) return $input.is(':checked') ? 'Checked' : 'Unchecked';
                    return $input.val();
                }
                return $(this).text().trim();
            }).get()
        )].sort();

        const filters = window.tableFilters[tableId] || {};
        const existingFilter = filters[colIdx] || { selected: [], filterType: 'contains', v1: '', v2: '' };
        const sel = (type) => existingFilter.filterType === type ? 'selected' : '';

        // Remove any other open popup for this table only
        $('.excel-filter-popup').remove();

        const html = `
      <div class="excel-filter-popup card shadow p-2" style="position:absolute; z-index:9999; width:260px;">
        <div class="popup-header bg-light p-1 mb-2" style="cursor:move;">
          <strong>Filter</strong>
          <button type="button" class="close float-right" aria-label="Close">&times;</button>
        </div>
        <input type="text" class="form-control form-control-sm mb-2 filter-search" placeholder="Search options..."/>
        <div class="options mb-2" style="max-height:150px; overflow:auto;">
          <label class="mb-0 filter-option d-block"><input type="checkbox" class="check-all"> <b>Select All</b></label><hr class="my-1">
          ${values.map(v => {
            const checked = !existingFilter.selected.length || existingFilter.selected.includes(v) ? 'checked' : '';
            const safe = (v || '(Blank)');
            return `<label><input type="checkbox" class="chk-item" value="${String(v).replace(/"/g, '&quot;')}" ${checked}> ${safe}</label><br>`;
        }).join('')}
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
        <input type="text" class="form-control form-control-sm mb-2 filter-value2 ${(['between', 'date_between'].includes(existingFilter.filterType) ? '' : 'd-none')}" value="${existingFilter.v2 || ''}" placeholder="Value 2"/>
        <div class="d-flex justify-content-end gap-2">
          <button class="btn btn-sm btn-primary apply-filter">Apply</button>
          <button class="btn btn-sm btn-secondary clear-filter">Clear</button>
        </div>
      </div>`;

        const $popup = $(html).appendTo('body');

        // Select-all default state
        const totalItems = $popup.find('.chk-item').length;
        const checkedItems = $popup.find('.chk-item:checked').length;
        $popup.find('.check-all').prop('checked', totalItems > 0 && checkedItems === totalItems);

        // Position near button
        const rect = $btn[0].getBoundingClientRect();
        const top = rect.bottom + window.scrollY + 4;
        let left = rect.left + window.scrollX;
        const width = 260;
        if (left + width > window.innerWidth) left = Math.max(0, window.innerWidth - (width + 10));
        $popup.css({ top, left });

        makePopupDraggable($popup);

        // Close
        $popup.find('.close').on('click', () => $popup.remove());

        // Search within options
        $popup.find('.filter-search').on('keyup', function () {
            const q = $(this).val().toLowerCase();
            $popup.find('.chk-item').each(function () {
                $(this).closest('label').next('br').addBack().toggle($(this).val().toLowerCase().includes(q));
            });
        });

        // Select All toggle
        $popup.find('.check-all').on('change', function () {
            $popup.find('.chk-item').prop('checked', $(this).is(':checked'));
        });

        // Auto-focus & Enter to apply
        const $v1 = $popup.find('.filter-value1');
        const $v2 = $popup.find('.filter-value2');
        $v1.focus();
        $popup.on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $popup.find('.apply-filter').trigger('click'); } });

        // Update inputs by filter type
        function updateInputType(type) {
            $v2.toggleClass('d-none', !['between', 'date_between'].includes(type));
            if (!type || type === '' || type.includes('equals') || type.includes('contains')) { $v1.attr('type', 'text'); $v2.attr('type', 'text'); }
            else if (type.startsWith('num') || ['greater', 'less', 'between', 'greater_eq', 'less_eq'].includes(type)) { $v1.attr('type', 'number'); $v2.attr('type', 'number'); }
            else if (type.startsWith('date')) { $v1.attr('type', 'date'); $v2.attr('type', 'date'); }
        }
        updateInputType(existingFilter.filterType);
        $popup.find('.filter-type').on('change', function () { updateInputType($(this).val()); });

        // Apply
        $popup.find('.apply-filter').on('click', function () {
            const selected = $popup.find('.chk-item:checked').map((_, el) => $(el).val()).get();
            const filterType = $popup.find('.filter-type').val();
            const v1 = $v1.val();
            const v2 = $v2.val();

            const store = window.tableFilters[tableId] || {};
            store[colIdx] = { selected, filterType, v1, v2 };
            window.tableFilters[tableId] = store;

            saveFilters(storageKey, tableId);
            rebuildFilters(api, tableId);
            updateFilterButton($btn, true);
            $popup.remove();
        });

        // Clear
        $popup.find('.clear-filter').on('click', function () {
            const store = window.tableFilters[tableId] || {};
            store[colIdx] = null;
            window.tableFilters[tableId] = store;
            saveFilters(storageKey, tableId);
            rebuildFilters(api, tableId);
            updateFilterButton($btn, false);
            $popup.remove();
        });

        // Click outside to close — namespaced per table
        $(document).off(`click.excelFilter.${tableId}`)
            .on(`click.excelFilter.${tableId}`, function (e) {
                if (!$(e.target).closest('.excel-filter-popup, .filter-btn').length) {
                    $popup.remove();
                    $(document).off(`click.excelFilter.${tableId}`);
                }
            });
    }

    function updateFilterButton($btn, active) {
        $btn.attr('data-active', !!active)
            .toggleClass('btn-warning', !!active)
            .toggleClass('btn-light', !active);
    }

    function positionButtons(dt, $table) {
        dt.buttons().container().appendTo($table.closest('.dataTables_wrapper').find('.col-md-6:eq(0)'));
    }

    function makePopupDraggable($popup) {
        const $header = $popup.find('.popup-header');
        let isDragging = false, offsetX = 0, offsetY = 0;
        $header.on('mousedown', function (e) {
            isDragging = true;
            offsetX = e.pageX - $popup.offset().left;
            offsetY = e.pageY - $popup.offset().top;
            $('body').addClass('unselectable');
        });
        $(document).on('mousemove', function (e) { if (isDragging) $popup.css({ top: e.pageY - offsetY, left: e.pageX - offsetX }); })
            .on('mouseup', function () { isDragging = false; $('body').removeClass('unselectable'); });
    }

    function formatDate(d) { return d instanceof Date && !isNaN(d) ? d.toISOString().split('T')[0] : ''; }

    // =========================
    // CRUD ENGINE (config-driven)
    // =========================
    window.tableCrudState = window.tableCrudState || {}; // { [tableId]: { deletedIds:Set, config:Object } }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    function escapeAttr(str) { return escapeHtml(str).replace(/`/g, '&#96;'); }

    function getRowId($row, config) {
        const attr = config && config.rowIdAttr ? config.rowIdAttr : 'data-id';
        // Prefer attribute (works even if data() cache not synced)
        let id = $row.attr(attr);
        if (!id) id = $row.data('id');
        return id;
    }

    function ensureCrudState(tableId, config) {
        if (!window.tableCrudState[tableId]) {
            window.tableCrudState[tableId] = {
                deletedIds: new Set(),
                config: config || {}
            };
        } else if (config) {
            window.tableCrudState[tableId].config = config;
        }
        return window.tableCrudState[tableId];
    }

    function resolveOptions(col) {
        if (!col) return [];
        const opt = col.options;
        if (typeof opt === 'function') return opt() || [];
        return opt || [];
    }

    function buildCellHTML(col, initialValue) {
        const name = col.name;
        const v = (initialValue ?? col.default ?? '');

        // Custom full control
        if (typeof col.render === 'function') {
            return col.render({ name, value: v, col });
        }

        const baseClass = (col.className || col.class || 'form-control form-control-sm');
        const attrsObj = col.attrs || {};
        const attrs = Object.entries(attrsObj).map(([k, val]) => `${k}="${escapeAttr(val)}"`).join(' ');

        switch ((col.type || 'text').toLowerCase()) {
            case 'editable':
            case 'contenteditable':
                // td akan di-set contenteditable, bukan div
                return escapeHtml(v);

            case 'textarea': {
                const rows = col.rows != null ? `rows="${col.rows}"` : '';
                return `<textarea class="${escapeAttr(baseClass)}" data-name="${escapeAttr(name)}" ${rows} ${attrs}>${escapeHtml(v)}</textarea>`;
            }

            case 'select': {
                const opts = resolveOptions(col);
                const multiple = col.multiple ? 'multiple' : '';
                const optionsHtml = (opts || []).map(o => {
                    const value = (typeof o === 'object') ? o.value : o;
                    const label = (typeof o === 'object') ? o.label : o;
                    const selected = String(value) === String(v) ? 'selected' : '';
                    return `<option value="${escapeAttr(value)}" ${selected}>${escapeHtml(label)}</option>`;
                }).join('');
                const cls = col.className || col.class || `${baseClass}${col.multiple ? '' : ''}`;
                return `<select class="${escapeAttr(cls)}" data-name="${escapeAttr(name)}" ${multiple} ${attrs}>${optionsHtml}</select>`;
            }

            case 'checkbox': {
                const checked = (v === true || v === 1 || v === '1' || v === 'true' || v === 'Checked') ? 'checked' : '';
                return `<input type="checkbox" data-name="${escapeAttr(name)}" ${checked} ${attrs}>`;
            }

            default: {
                // native input types: text, number, date, email, time, month, week, color, etc.
                const type = (col.type || 'text').toLowerCase();
                const min = col.min != null ? `min="${escapeAttr(col.min)}"` : '';
                const max = col.max != null ? `max="${escapeAttr(col.max)}"` : '';
                const step = col.step != null ? `step="${escapeAttr(col.step)}"` : '';
                return `<input type="${escapeAttr(type)}" class="${escapeAttr(baseClass)}" data-name="${escapeAttr(name)}" value="${escapeAttr(v)}" ${min} ${max} ${step} ${attrs}>`;
            }
        }
    }

    function readCellValue(col, $cell) {
        if (typeof col.getValue === 'function') return col.getValue($cell);

        const name = col.name;
        const $el = $cell.find(`[data-name="${name}"]`);

        if (!$el.length) {
            // fallback: if cell itself has data-name or is editable
            if ($cell.is(`[data-name="${name}"]`)) return $cell.text().trim();
            return '';
        }

        if ($el.is('input[type="checkbox"]')) {
            const tv = (col.trueValue != null) ? col.trueValue : 1;
            const fv = (col.falseValue != null) ? col.falseValue : 0;
            return $el.prop('checked') ? tv : fv;
        }

        if ($el.is('input, select, textarea')) return $el.val();

        return $el.text().trim();
    }

    function buildRowHtml(config, rowId, initialData) {
        const rowIdAttr = config.rowIdAttr || 'data-id';
        const colStart = config.colStartIndex || 0;

        // System/leading columns (checkbox, numbering, etc.)
        let prefix = '';
        if (typeof config.rowPrefixHtml === 'function') {
            prefix = config.rowPrefixHtml({ rowId, initialData, config }) || '';
        } else if (Array.isArray(config.rowPrefixHtml)) {
            prefix = config.rowPrefixHtml.join('');
        } else if (typeof config.rowPrefixHtml === 'string') {
            prefix = config.rowPrefixHtml;
        }

        // If user wants default checkbox + label, provide opt-in
        if (!prefix && config.defaultRowPrefix === true) {
            prefix = `<td><input type="checkbox" class="${escapeAttr(config.rowCheckboxClass || 'row-checkbox')}"></td><td>New</td>`;
        }

        const tds = (config.columns || []).map((col) => {
            const v = initialData && (initialData[col.name] != null) ? initialData[col.name] : (col.default ?? '');
            const isEditable = ['editable', 'contenteditable'].includes((col.type || '').toLowerCase());

            return `<td ${isEditable ? 'contenteditable="true"' : ''} data-name="${escapeAttr(col.name)}">
            ${buildCellHTML(col, v)}
            </td>`;
        }).join('');

        // colStartIndex means: table already has some fixed columns; we still output full <tr> here
        // If you use colStartIndex, set rowPrefixHtml to exactly match those fixed columns.
        return `<tr ${rowIdAttr}="${escapeAttr(rowId)}" class="${escapeAttr(config.newRowClass || 'table-success')}">${prefix}${tds}</tr>`;
    }

    function collectRowData($row, config) {
        const rowId = getRowId($row, config);
        const data = { id: rowId };

        // Read each configured column by locating element with matching data-name in the row
        (config.columns || []).forEach((col) => {
            const $cell = $row; // search within row
            data[col.name] = readCellValue(col, $cell);
        });

        return data;
    }

    function buildCrudPayload($table, config, dt) {
        const tableId = getTableId($table);
        const state = ensureCrudState(tableId, config);
        const deletedIds = state.deletedIds;
        const newPrefix = config.newIdPrefix || 'new_';

        const creates = [];
        const updates = [];
        const deletes = Array.from(deletedIds);

        dt.rows().every(function () {
            const node = this.node();
            const $row = $(node);
            const rowId = getRowId($row, config);
            if (!rowId) return;

            if (deletedIds.has(String(rowId))) return;

            const rowData = collectRowData($row, config);

            // Optional row validation hook
            if (typeof config.validateRow === 'function') {
                const ok = config.validateRow(rowData, { $row, dt, tableId });
                if (ok === false) throw new Error('VALIDATION_FAILED');
            }

            if (String(rowId).startsWith(newPrefix)) creates.push(rowData);
            else updates.push(rowData);
        });

        let payload = { creates, updates, deletes };

        if (typeof config.beforeSubmit === 'function') {
            payload = config.beforeSubmit(payload, { dt, tableId, $table }) || payload;
        }

        return payload;
    }

    function markRowDeleted($row, config, isDeleted) {
        const delClass = config.deletedRowClass || 'table-danger';
        const opacity = (config.deletedOpacity != null) ? config.deletedOpacity : 0.7;
        if (isDeleted) {
            $row.addClass(delClass).css('opacity', opacity);
        } else {
            $row.removeClass(delClass).css('opacity', '');
        }
    }

    function deleteSelectedRows($table, config) {
        const dt = $table.DataTable();
        const tableId = getTableId($table);
        const state = ensureCrudState(tableId, config);

        const rowCheckboxSelector = config.rowCheckboxSelector || '.row-checkbox';

        dt.rows().every(function () {
            const node = this.node();
            const $row = $(node);
            const $chk = $row.find(rowCheckboxSelector);
            if (!$chk.length) return;

            const id = String(getRowId($row, config) || '');
            if (!id) return;

            if ($chk.is(':checked')) {
                state.deletedIds.add(id);
                markRowDeleted($row, config, true);
            } else if (state.deletedIds.has(id)) {
                state.deletedIds.delete(id);
                markRowDeleted($row, config, false);
            }
        });
    }

    function addNewRows($table, config, count) {
        const dt = $table.DataTable();
        const tableId = getTableId($table);
        ensureCrudState(tableId, config);

        const n = Math.max(1, parseInt(count || 1, 10) || 1);

        // tampilkan overlay
        showTableOverlay($table, `Creating ${n} row(s)...`);

        // paksa browser render overlay dulu
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {

                const prefix = config.newIdPrefix || 'new_';

                for (let i = 0; i < n; i++) {
                    const newId = `${prefix}${Date.now()}_${Math.random().toString(36).slice(2, 6)}_${i}`;
                    const html = buildRowHtml(config, newId, null);
                    dt.row.add($(html)[0]);
                }

                dt.columns.adjust().draw(false);
                dt.page('last').draw('page');

                if (typeof config.onAfterRowAdded === 'function') {
                    const $nodes = $(dt.rows({ page: 'current' }).nodes());
                    config.onAfterRowAdded($nodes, { dt, tableId, $table });
                }

                // hide setelah redraw benar-benar kelar
                setTimeout(() => hideTableOverlay($table), 300);
            });
        });
    }

    function bindCrudHandlers($table, config) {
        const tableId = getTableId($table);
        ensureCrudState(tableId, config);

        const $doc = $(document);

        // Buttons (delegated)
        if (config.btnNewSelector) {
            $doc.off(`click.dtCrudNew.${tableId}`, config.btnNewSelector)
                .on(`click.dtCrudNew.${tableId}`, config.btnNewSelector, function (e) {
                    e.preventDefault();
                    const cnt = config.rowAddCountSelector ? $(config.rowAddCountSelector).val() : 1;
                    addNewRows($table, config, cnt);
                });
        }

        // Enter pada rowAddCountSelector => Add row
        if (config.rowAddCountSelector && config.btnNewSelector) {
            $doc.off(`keydown.dtCrudRowAddEnter.${tableId}`, config.rowAddCountSelector)
                .on(`keydown.dtCrudRowAddEnter.${tableId}`, config.rowAddCountSelector, function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault(); // cegah submit form
                        e.stopPropagation();
                        $(config.btnNewSelector).trigger('click'); // reuse logic yang sudah ada
                    }
                });
        }

        if (config.btnDeleteSelectedSelector) {
            $doc.off(`click.dtCrudDel.${tableId}`, config.btnDeleteSelectedSelector)
                .on(`click.dtCrudDel.${tableId}`, config.btnDeleteSelectedSelector, function (e) {
                    e.preventDefault();
                    deleteSelectedRows($table, config);
                });
        }

        if (config.selectAllSelector) {
            $doc.off(`change.dtCrudSelAll.${tableId}`, config.selectAllSelector)
                .on(`change.dtCrudSelAll.${tableId}`, config.selectAllSelector, function () {

                    const checked = $(this).is(':checked');
                    const rowCheckboxSelector = config.rowCheckboxSelector || '.row-checkbox';
                    const dt = $table.DataTable();

                    // ✅ ini yang bikin lintas halaman + hanya yang "kefilter"
                    dt.rows({ search: 'applied' }).every(function () {
                        const node = this.node();
                        if (!node) return;
                        $(node).find(rowCheckboxSelector).prop('checked', checked);
                    });
                });
        }

        // Form submit: inject json payload
        if (config.formSelector && config.jsonFieldSelector) {
            $doc.off(`submit.dtCrudSubmit.${tableId}`, config.formSelector)
                .on(`submit.dtCrudSubmit.${tableId}`, config.formSelector, function (e) {
                    try {
                        const dt = $table.DataTable();
                        const payload = buildCrudPayload($table, config, dt);
                        $(config.jsonFieldSelector).val(JSON.stringify(payload));
                    } catch (err) {
                        if (String(err.message || '').includes('VALIDATION_FAILED')) {
                            e.preventDefault();
                            return false;
                        }
                        // If unexpected error, still block submit to avoid bad payload
                        e.preventDefault();
                        console.error('DT CRUD error:', err);
                        alert('Gagal memproses data tabel. Cek console untuk detail.');
                        return false;
                    }
                });
        }

        // simpan selectedIds per table
        const state = ensureCrudState(tableId, config);
        state.selectedIds = state.selectedIds || new Set();

        $table.off(`change.dtRowChk.${tableId}`, config.rowCheckboxSelector || '.row-checkbox')
            .on(`change.dtRowChk.${tableId}`, config.rowCheckboxSelector || '.row-checkbox', function () {
                const $row = $(this).closest('tr');
                const id = String(getRowId($row, config) || '');
                if (!id) return;

                if ($(this).is(':checked')) state.selectedIds.add(id);
                else state.selectedIds.delete(id);
            });

        // re-apply saat draw (paging/sort/filter)
        $table.off(`draw.dtSelPersist.${tableId}`)
            .on(`draw.dtSelPersist.${tableId}`, function () {
                const dt = $table.DataTable();
                const rowCheckboxSelector = config.rowCheckboxSelector || '.row-checkbox';

                dt.rows({ page: 'current' }).every(function () {
                    const node = this.node();
                    if (!node) return;
                    const $row = $(node);
                    const id = String(getRowId($row, config) || '');
                    $row.find(rowCheckboxSelector).prop('checked', state.selectedIds.has(id));
                });
            });
    }

    /**
     * Public: setupDatatableCrud
     * Requirements:
     * - DataTable already initialized for $table
     * - Provide config.columns with {name,type,...}
     * - Provide selectors for buttons/form if you want engine to bind them
     */
    function setupDatatableCrud($table, config) {
        if (!$table || !$table.length) return;
        if (!$.fn.DataTable.isDataTable($table)) {
            console.warn('setupDatatableCrud: table is not a DataTable yet. Call setupFilterableDatatable first.');
            return;
        }
        const tableId = getTableId($table);
        ensureCrudState(tableId, config || {});
        bindCrudHandlers($table, config || {});
    }

    // Expose CRUD engine
    window.setupDatatableCrud = setupDatatableCrud;

    // Expose to window
    window.setupFilterableDatatable = setupFilterableDatatable;

    // === NEW: Paste Excel (TSV) ke grid + catat perubahan untuk UNDO/REDO ===
    // === Paste Excel (TSV) ke grid: hanya ROW yang terfilter + hanya COLUMN yang tampil ===
    $(document).off('paste.excelToGrid')
        .on('paste.excelToGrid', 'td[contenteditable="true"]', function (e) {
            e.preventDefault();

            const $table = $(this).closest('table');
            if (!$table.length || !$.fn.DataTable.isDataTable($table)) return;

            const dt = $table.DataTable();
            const start = dt.cell(this).index(); // {row, column} -> data index
            if (!start) return;

            const clip = (e.originalEvent || e).clipboardData;
            const text = clip ? clip.getData('text') : '';
            if (!text) return;

            // Parse TSV dari Excel
            const rows = text
                .replace(/\r/g, '')
                .split('\n')
                .filter(r => r.length > 0)
                .map(r => r.split('\t'));

            // ✅ Ambil row yang TERFILTER (search applied) + urutan yang tampil (order applied)
            const filteredRowIdxs = dt.rows({ search: 'applied', order: 'applied' }).indexes().toArray();

            // ✅ Ambil kolom yang TAMPIL saja (visible)
            const visibleColIdxs = dt.columns(':visible').indexes().toArray();

            // Posisi start row di dalam daftar filtered rows
            const startRowPos = filteredRowIdxs.indexOf(start.row);
            if (startRowPos === -1) return; // start cell bukan bagian dari filtered set (jarang terjadi)

            // Posisi start col di dalam daftar visible cols
            const startColPos = visibleColIdxs.indexOf(start.column);
            if (startColPos === -1) return; // start cell bukan visible (harusnya nggak mungkin)

            const changes = [];

            rows.forEach((cells, rOff) => {
                const targetRowPos = startRowPos + rOff;
                const r = filteredRowIdxs[targetRowPos];
                if (r === undefined) return; // stop kalau lewat batas filtered rows

                cells.forEach((val, cOff) => {
                    const targetColPos = startColPos + cOff;
                    const c = visibleColIdxs[targetColPos];
                    if (c === undefined) return; // stop kalau lewat batas visible columns

                    const oldVal = dt.cell(r, c).data();
                    const newVal = val;

                    if ((oldVal ?? '') === (newVal ?? '')) return;

                    const node = dt.cell(r, c).node();
                    if (node) node.textContent = newVal; // aman dari HTML
                    dt.cell(r, c).data(newVal);

                    changes.push({
                        r, c,
                        oldVal: String(oldVal ?? ''),
                        newVal: String(newVal ?? '')
                    });
                });
            });

            // Simpan ke undo stack kalau ada perubahan
            if (changes.length) {
                const tableId = getTableId($table);
                pushUndo(tableId, { type: 'paste', changes });
                dt.draw(false); // keep paging
            }
        });

    // === NEW: Keyboard shortcuts Undo/Redo ===
    $(document).off('keydown.dtUndoRedo')
        .on('keydown.dtUndoRedo', function (e) {
            // Deteksi Ctrl/Meta untuk Windows/Mac
            const isCtrl = e.ctrlKey || e.metaKey;
            if (!isCtrl) return;

            const key = e.key.toLowerCase();

            // Cari table aktif dari fokus atau lastActiveTableId
            let $table = $(e.target).closest('table');
            if (!$table.length && window.lastActiveTableId) {
                $table = $('#' + window.lastActiveTableId);
            }
            if (!$table.length || !$.fn.DataTable.isDataTable($table)) return;

            if (key === 'z' && !e.shiftKey) {
                e.preventDefault();
                doUndo($table);
            } else if (key === 'y' || (key === 'z' && e.shiftKey)) {
                e.preventDefault();
                doRedo($table);
            }
        });

    $(document).on('keydown', 'td[contenteditable="true"]', function (e) {
        if (e.key === "Tab") {
            e.preventDefault(); // cegah behavior default

            const $cells = $(this).closest('table')
                .find('td[contenteditable="true"]');
            const idx = $cells.index(this);

            // Tentukan target cell
            let $next;
            if (!e.shiftKey) {
                $next = $cells.eq(idx + 1);   // Tab → maju
            } else {
                $next = $cells.eq(idx - 1);   // Shift+Tab → mundur
            }

            if ($next && $next.length) {
                $next.focus();

                // Auto-select semua text di cell target
                const sel = window.getSelection();
                const range = document.createRange();
                range.selectNodeContents($next[0]);
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }
    });

    // =========================
    // Excel-like Cell Range Selection (multi-cell)
    // =========================
    (function excelLikeCellSelection() {
        // per table: store selected cells as Set("r:c")
        window.tableCellSelection = window.tableCellSelection || {}; // { [tableId]: { set:Set, anchor:{r,c}, last:{r,c} } }

        function keyOf(r, c) { return `${r}:${c}`; }
        function parseKey(k) { const [r, c] = k.split(':').map(n => parseInt(n, 10)); return { r, c }; }

        function ensureSel(tableId) {
            if (!window.tableCellSelection[tableId]) {
                window.tableCellSelection[tableId] = { set: new Set(), anchor: null, last: null };
            }
            return window.tableCellSelection[tableId];
        }

        function clearSel($table) {
            const tableId = getTableId($table);
            const sel = ensureSel(tableId);
            sel.set.clear();
            sel.anchor = null;
            sel.last = null;
            $table.find('td.dt-cell-selected').removeClass('dt-cell-selected');
            $table.find('td.dt-cell-anchor').removeClass('dt-cell-anchor');
        }

        function addCell($table, dt, r, c) {
            const tableId = getTableId($table);
            const sel = ensureSel(tableId);
            const k = keyOf(r, c);
            sel.set.add(k);
            const node = dt.cell(r, c).node();
            if (node) $(node).addClass('dt-cell-selected');
        }

        function setAnchor($table, dt, r, c) {
            const tableId = getTableId($table);
            const sel = ensureSel(tableId);
            sel.anchor = { r, c };
            sel.last = { r, c };
            $table.find('td.dt-cell-anchor').removeClass('dt-cell-anchor');
            const node = dt.cell(r, c).node();
            if (node) $(node).addClass('dt-cell-anchor');
        }

        function selectRect($table, dt, a, b, additive = false) {
            if (!a || !b) return;
            const tableId = getTableId($table);
            const sel = ensureSel(tableId);

            if (!additive) {
                // clear first
                $table.find('td.dt-cell-selected').removeClass('dt-cell-selected');
                sel.set.clear();
            }

            const rMin = Math.min(a.r, b.r);
            const rMax = Math.max(a.r, b.r);
            const cMin = Math.min(a.c, b.c);
            const cMax = Math.max(a.c, b.c);

            for (let r = rMin; r <= rMax; r++) {
                for (let c = cMin; c <= cMax; c++) {
                    addCell($table, dt, r, c);
                }
            }
            sel.last = { r: b.r, c: b.c };
        }

        function getSelectedCells($table, dt) {
            const tableId = getTableId($table);
            const sel = ensureSel(tableId);
            // sort by row then col
            const arr = Array.from(sel.set).map(parseKey).sort((x, y) => (x.r - y.r) || (x.c - y.c));
            // keep only existing nodes (datatable might redraw)
            return arr.filter(({ r, c }) => dt.cell(r, c).node());
        }

        function isEditableCell(node) {
            const $td = $(node);
            // only allow selecting editable cells by default
            return $td.is('[contenteditable="true"]') || $td.find('input,select,textarea').length;
        }

        function getCellPlainValue(dt, r, c) {
            const node = dt.cell(r, c).node();
            if (!node) return '';
            const $td = $(node);
            const $input = $td.find('input, select, textarea');

            if ($input.length) {
                if ($input.is('[type="checkbox"]')) return $input.is(':checked') ? 'Checked' : 'Unchecked';
                return ($input.val() ?? '');
            }
            return $td.text().trim();
        }

        function setCellPlainValue(dt, r, c, val) {
            const node = dt.cell(r, c).node();
            if (!node) return;

            const $td = $(node);
            const $input = $td.find('input, select, textarea');

            if ($input.length) {
                if ($input.is('[type="checkbox"]')) {
                    const checked = (String(val).toLowerCase() === 'checked' || String(val) === '1' || String(val).toLowerCase() === 'true');
                    $input.prop('checked', checked).trigger('change');
                    dt.cell(r, c).data(checked ? 'Checked' : 'Unchecked');
                    return;
                }
                $input.val(val).trigger('input').trigger('change');
                dt.cell(r, c).data(val);
                return;
            }

            // contenteditable / plain td
            node.textContent = val;
            dt.cell(r, c).data(val);
        }

        function selectionToTSV($table, dt) {
            const cells = getSelectedCells($table, dt);
            if (!cells.length) return '';

            const rows = new Map(); // r -> Map(c -> val)
            let minC = Infinity, maxC = -Infinity, minR = Infinity, maxR = -Infinity;

            cells.forEach(({ r, c }) => {
                if (!rows.has(r)) rows.set(r, new Map());
                rows.get(r).set(c, getCellPlainValue(dt, r, c));
                minC = Math.min(minC, c);
                maxC = Math.max(maxC, c);
                minR = Math.min(minR, r);
                maxR = Math.max(maxR, r);
            });

            const lines = [];
            for (let r = minR; r <= maxR; r++) {
                const m = rows.get(r) || new Map();
                const line = [];
                for (let c = minC; c <= maxC; c++) {
                    line.push(m.has(c) ? String(m.get(c) ?? '') : '');
                }
                lines.push(line.join('\t'));
            }
            return lines.join('\n');
        }

        function applyBulkSet($table, dt, updates, actionType) {
            // updates: [{r,c,oldVal,newVal}]
            if (!updates.length) return;
            const tableId = getTableId($table);
            pushUndo(tableId, { type: actionType, changes: updates });
            updates.forEach(u => setCellPlainValue(dt, u.r, u.c, u.newVal));
            dt.draw(false);
        }

        // Mouse selection (Excel-like)
        $(document).off('mousedown.dtCellSelect')
            .on('mousedown.dtCellSelect', 'table.dataTable td', function (e) {
                const $table = $(this).closest('table');
                if (!$table.length || !$.fn.DataTable.isDataTable($table)) return;

                // ignore right click
                if (e.button === 2) return;

                const dt = $table.DataTable();
                const idx = dt.cell(this).index();
                if (!idx) return;

                // by default only select editable cells
                if (!isEditableCell(this)) return;

                window.lastActiveTableId = getTableId($table);

                const tableId = getTableId($table);
                const sel = ensureSel(tableId);

                const additive = e.ctrlKey || e.metaKey;

                // Shift: select rectangle from anchor
                if (e.shiftKey && sel.anchor) {
                    selectRect($table, dt, sel.anchor, { r: idx.row, c: idx.column }, /*additive*/ additive);
                } else {
                    if (!additive) clearSel($table);
                    setAnchor($table, dt, idx.row, idx.column);
                    addCell($table, dt, idx.row, idx.column);
                }

                // drag to expand rectangle
                let dragging = true;
                $(document).on('mousemove.dtCellDrag', function (ev) {
                    if (!dragging) return;
                    const el = document.elementFromPoint(ev.clientX, ev.clientY);
                    if (!el) return;
                    const $td = $(el).closest('td');
                    if (!$td.length) return;
                    const $t2 = $td.closest('table');
                    if ($t2[0] !== $table[0]) return;

                    const idx2 = dt.cell($td[0]).index();
                    if (!idx2) return;

                    // only select editable cells
                    if (!isEditableCell($td[0])) return;

                    selectRect($table, dt, sel.anchor || { r: idx.row, c: idx.column }, { r: idx2.row, c: idx2.column }, /*additive*/ additive);
                });

                $(document).one('mouseup.dtCellDragEnd', function () {
                    dragging = false;
                    $(document).off('mousemove.dtCellDrag');
                });
            });

        // Clear selection when clicking outside table (optional, feels like Excel)
        $(document).off('mousedown.dtCellSelectOutside')
            .on('mousedown.dtCellSelectOutside', function (e) {
                if ($(e.target).closest('table.dataTable').length) return;
                if (window.lastActiveTableId) {
                    const $t = $('#' + window.lastActiveTableId);
                    if ($t.length) clearSel($t);
                }
            });

        // Keep highlight after draw (paging/sort/filter)
        $(document).off('draw.dtCellSelectRedraw')
            .on('draw.dtCellSelectRedraw', 'table.dataTable', function () {
                const $table = $(this);
                const tableId = getTableId($table);
                const sel = window.tableCellSelection?.[tableId];
                if (!sel || !sel.set || !sel.set.size) return;

                const dt = $table.DataTable();
                // remove old classes first
                $table.find('td.dt-cell-selected').removeClass('dt-cell-selected');
                $table.find('td.dt-cell-anchor').removeClass('dt-cell-anchor');

                sel.set.forEach(k => {
                    const { r, c } = parseKey(k);
                    const node = dt.cell(r, c).node();
                    if (node) $(node).addClass('dt-cell-selected');
                });

                if (sel.anchor) {
                    const node = dt.cell(sel.anchor.r, sel.anchor.c).node();
                    if (node) $(node).addClass('dt-cell-anchor');
                }
            });

        // Keyboard: Copy / Cut / Delete for selected cells
        $(document).off('keydown.dtCellOps')
            .on('keydown.dtCellOps', function (e) {
                const tableId = window.lastActiveTableId;
                if (!tableId) return;

                const $table = $('#' + tableId);
                if (!$table.length || !$.fn.DataTable.isDataTable($table)) return;

                const dt = $table.DataTable();
                const cells = getSelectedCells($table, dt);
                if (!cells.length) return;

                const isCtrl = e.ctrlKey || e.metaKey;
                const key = e.key.toLowerCase();

                // Copy
                if (isCtrl && key === 'c') {
                    e.preventDefault();
                    const tsv = selectionToTSV($table, dt);
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(tsv).catch(() => { /* ignore */ });
                    } else {
                        // fallback
                        const $tmp = $('<textarea style="position:fixed;left:-9999px;top:-9999px;"></textarea>').appendTo('body');
                        $tmp.val(tsv).select();
                        document.execCommand('copy');
                        $tmp.remove();
                    }
                    return;
                }

                // Cut
                if (isCtrl && key === 'x') {
                    e.preventDefault();
                    const tsv = selectionToTSV($table, dt);

                    // copy first
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(tsv).catch(() => { /* ignore */ });
                    } else {
                        const $tmp = $('<textarea style="position:fixed;left:-9999px;top:-9999px;"></textarea>').appendTo('body');
                        $tmp.val(tsv).select();
                        document.execCommand('copy');
                        $tmp.remove();
                    }

                    // then clear (push undo)
                    const updates = [];
                    cells.forEach(({ r, c }) => {
                        const oldVal = getCellPlainValue(dt, r, c);
                        if (oldVal === '') return;
                        updates.push({ r, c, oldVal: String(oldVal), newVal: '' });
                    });
                    applyBulkSet($table, dt, updates, 'cut');
                    return;
                }

                // Delete / Backspace
                if (key === 'delete' || key === 'backspace') {
                    // ✅ hanya bulk-clear kalau select > 1 cell
                    if (cells.length > 1) {
                        e.preventDefault();

                        const updates = [];
                        cells.forEach(({ r, c }) => {
                            const oldVal = getCellPlainValue(dt, r, c);
                            if (oldVal === '') return;
                            updates.push({ r, c, oldVal: String(oldVal), newVal: '' });
                        });

                        applyBulkSet($table, dt, updates, 'delete');
                        return;
                    }

                    // ✅ kalau cuma 1 cell: jangan clear satu cell full
                    // biarkan default behavior (hapus karakter) saat sedang edit.
                    // Tapi cegah BACKSPACE jadi "Back" browser kalau fokus bukan di input/editable.
                    const $t = $(e.target);
                    const targetEditable =
                        $t.is('input,textarea,select') ||
                        $t.is('[contenteditable="true"]') ||
                        $t.closest('[contenteditable="true"], input, textarea, select').length;

                    if (key === 'backspace' && !targetEditable) {
                        e.preventDefault(); // prevent browser back navigation
                    }
                    return;
                }
            });
    })();
})(jQuery);