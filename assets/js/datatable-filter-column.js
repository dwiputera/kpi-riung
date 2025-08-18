/*
 * DataTables Excel-like Filters (Multi-table Safe)
 * Drop-in script — supports multiple tables per page, per-table filter state,
 * and localStorage persistence by page+table.
 *
 * Requirements: jQuery, DataTables (+ Buttons extension if you keep the buttons)
 *
 * Public API: setupFilterableDatatable($("table#yourId"))
 */

(function ($) {
    // Global registries (scoped to window for persistence across inits)
    window.tableFilters = window.tableFilters || {}; // { [tableId]: { [colIdx]: filterObj } }
    window.tableFilterFns = window.tableFilterFns || {}; // { [tableId]: fn }

    function getStorageKey($table) {
        const tableId = $table.attr('id') || 'datatable';
        const pagePath = window.location.pathname.replace(/\W+/g, '_');
        const tableIndex = $table.index('table');
        return `excelFilters_${pagePath}_${tableId}_${tableIndex}`;
    }

    function setupFilterableDatatable($table) {
        if (!$table || !$table.length) return;
        const tableId = $table.attr('id') || `datatable_${Math.random().toString(36).slice(2, 8)}`;
        if (!$table.attr('id')) $table.attr('id', tableId);

        // Ensure per-table store exists
        if (!window.tableFilters[tableId]) window.tableFilters[tableId] = {};

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
            }]
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

        const dt = $table.DataTable(dtOptions);
        positionButtons(dt, $table);
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

        api.draw();
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
            return `<label><input type="checkbox" class="chk-item" value="${String(v).replace(/"/g, '&quot;')}" ${checked}> ${safe}</label>`;
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
                $(this).parent().toggle($(this).val().toLowerCase().includes(q));
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

    // Expose to window
    window.setupFilterableDatatable = setupFilterableDatatable;

})(jQuery);
