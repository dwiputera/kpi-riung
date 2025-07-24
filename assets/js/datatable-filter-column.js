function setupFilterableDatatable($table) {
    if ($table.hasClass('dataTable')) return;

    const config = ($table.data('filter-columns') || '').split(',');
    const isServerSide = $table.data('server') === true || $table.data('server') === 'true';
    const $thead = $table.find('thead');
    const $headerRow = $thead.find('tr').first();
    const columnModes = {};

    config.forEach(entry => {
        const [indexStr, mode = 'single'] = entry.trim().split(':');
        const index = parseInt(indexStr);
        if (!isNaN(index)) {
            columnModes[index] = mode.toLowerCase();
        }
    });

    const $filterRow = $('<tr>');
    const filterCells = [];

    $headerRow.find('th').each(function (i) {
        const $cell = $('<th>').addClass('p-0');
        const mode = columnModes[i];
        if (mode === 'checkbox') {
            $cell.html(`
                <div class="text-center">
                    <input type="checkbox" class="check-all-box"/>
                </div>
            `);
        } else if (mode === 'number') {
            $cell.html(`
                <div class="d-flex align-items-center gap-1">
                    <select class="form-control form-control-sm number-filter-operator w-100">
                        <option value='null'>ALL</option>
                        <option value="=">=</option>
                        <option value=">">&gt;</option>
                        <option value="<">&lt;</option>
                        <option value="range">Range</option>
                    </select>
                    <input type="number" class="form-control form-control-sm number-filter-value1 w-100 d-none" placeholder="..."/>
                    <input type="number" class="form-control form-control-sm number-filter-value2 w-100 d-none" placeholder="..."/>
                </div>
            `);
        } else if (mode === 'multiple' || mode === 'single') {
            const isMultiple = mode === 'multiple';
            $cell.html(`
                <select ${isMultiple ? 'multiple' : ''} 
                        class="form-control form-control-sm select2" 
                        style="width: 100%">
                    ${isMultiple ? '' : '<option value="">All</option>'}
                </select>
            `);
        } else {
            $cell.html('<input type="text" placeholder="Filter..." class="form-control form-control-sm"/>');
        }

        filterCells.push($cell[0]);
    });

    $filterRow.append(filterCells);
    $thead.append($filterRow);

    // Event untuk check/uncheck semua baris
    $filterRow.on('change', '.check-all-box', function () {
        const isChecked = $(this).is(':checked');
        $table.DataTable().rows({ search: 'applied' }).nodes()
            .to$().find('.row-checkbox')
            .prop('checked', isChecked).trigger('change');
    });


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
            initializeFilters(this.api());
        },
    };

    if (isServerSide) {
        dtOptions.ajax = {
            url: $table.data('url'),
            type: 'POST',
            data: function (d) {
                $filterRow.find('th').each(function (i) {
                    const $th = $(this);
                    if ($th.find('.number-filter-operator').length) {
                        const op = $th.find('.number-filter-operator').val();
                        const val1 = $th.find('.number-filter-value1').val();
                        const val2 = $th.find('.number-filter-value2').val();
                        d[`columns_filter[${i}]`] = JSON.stringify({ op, val1, val2 });
                    } else {
                        const $input = $th.find('input, select');
                        if ($input.length) {
                            d[`columns_filter[${i}]`] = $input.val();
                        }
                    }
                });
            }
        };
    }

    dtOptions.columnDefs = [{
        targets: '_all',
        render: function (data, type, row, meta) {
            const el = $('<div>').html(data);
            const input = el.find('input');
            const editable = el.find('[contenteditable]');

            if (type === 'display') return data; // don't touch UI

            if (input.length) {
                const val = input.val();
                const typeAttr = input.attr('type');

                // If date input, parse as YYYY-MM-DD for sorting
                if (typeAttr === 'date' && val) {
                    return val;
                }
                return val;
            }

            if (editable.length) return editable.text();

            return el.text(); // fallback
        }
    }];

    const dt = $table.DataTable(dtOptions);
    positionButtons(dt, $table);
}

function initializeFilters(api) {
    const columnModes = {};
    const config = ($(api.table().node()).data('filter-columns') || '').split(',');
    config.forEach(entry => {
        const [indexStr, mode = 'single'] = entry.trim().split(':');
        const index = parseInt(indexStr);
        if (!isNaN(index)) columnModes[index] = mode.toLowerCase();
    });

    const $filterRow = $(api.table().header()).find('tr').last();
    const isServerSide = api.init().serverSide;

    api.columns().every(function (i) {
        const column = this;
        const $th = $filterRow.find('th').eq(i);
        const mode = columnModes[i];

        if (mode === 'checkbox') return; // ❗ Skip checkbox

        if ($th.find('.number-filter-operator').length) {
            const $op = $th.find('.number-filter-operator');
            const $val1 = $th.find('.number-filter-value1');
            const $val2 = $th.find('.number-filter-value2');

            $op.on('change', function () {
                if ($(this).val() === 'null') {
                    $val1.addClass('d-none').val('');
                    $val2.addClass('d-none').val('');
                } else {
                    $val1.removeClass('d-none');
                    if ($(this).val() === 'range') {
                        $val2.removeClass('d-none');
                    } else {
                        $val2.addClass('d-none').val('');
                    }
                }
                $val1.trigger('change');
            });

            $val1.add($val2).on('change keyup', function () {
                if (isServerSide) {
                    api.draw();
                } else {
                    const op = $op.val();
                    const v1 = parseFloat($val1.val());
                    const v2 = parseFloat($val2.val());
                    applyNumberFilter(column, op, v1, v2);
                }
            });
        } else {
            const $input = $th.find('input, select');
            const mode = $input.is('select[multiple]') ? 'multiple' :
                $input.is('select') ? 'single' : null;

            if (mode && !isServerSide) {
                populateSelectFilter($input, column);
            }

            setupFilterEventHandler($input, column, mode, isServerSide, api);
        }
    });

    FuzzySelect2?.apply?.('.select2');
}

function populateSelectFilter($select, column) {
    const options = column
        .data()
        .map(val => {
            const el = $('<div>').html(val);
            const input = el.find('input');
            if (input.length) return input.val();

            const editable = el.find('[contenteditable]');
            if (editable.length) return editable.text();

            return el.text();
        })
        .unique()
        .sort()
        .toArray();
    options.forEach(val => {
        if (val) $select.append(`<option value="${val}">${val}</option>`);
    });
}

function setupFilterEventHandler($input, column, mode, isServerSide, api) {
    const event = $input.is('select') ? 'change' : 'keyup change clear';

    $input.on(event, function () {
        if (isServerSide) {
            api.draw();
        } else {
            applyClientSideFilter(column, $(this).val(), mode);
        }
    });
}

function applyClientSideFilter(column, value, mode) {
    if (mode === 'multiple') {
        const regex = value && value.length
            ? value.map(val => $.fn.dataTable.util.escapeRegex(val)).join('|')
            : '';
        column.search(regex ? `^(${regex})$` : '', true, false).draw();
    } else if (mode === 'single') {
        const escaped = $.fn.dataTable.util.escapeRegex(value);
        column.search(escaped ? `^${escaped}$` : '', true, false).draw();
    } else {
        if (column.search() !== value) {
            column.search(value).draw();
        }
    }
}

function applyNumberFilter(column, operator, val1, val2) {
    column.search('', false, false); // reset

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        const colVal = parseFloat(data[column.index()]);
        if (isNaN(colVal)) return false;

        switch (operator) {
            case '=': return colVal === val1;
            case '>': return colVal > val1;
            case '<': return colVal < val1;
            case 'range': return colVal >= val1 && colVal <= val2;
        }
        return true;
    });

    column.draw();

    // Prevent stacking filters
    setTimeout(() => {
        $.fn.dataTable.ext.search.pop();
    }, 0);
}

function positionButtons(dt, $table) {
    dt.buttons().container().appendTo(
        $table.closest('.dataTables_wrapper').find('.col-md-6:eq(0)')
    );
}
