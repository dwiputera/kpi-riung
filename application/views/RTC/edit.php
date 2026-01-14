<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Current RTC</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Year Change</h3>
            </div>

            <form action="<?= base_url('RTC/edit') ?>" method="get">
                <div class="card-body">
                    <div class="form-group">
                        <label>Year:</label>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="input-group date" id="year" data-target-input="nearest">
                                    <input type="text"
                                        class="form-control datetimepicker-input"
                                        data-target="#year"
                                        value="<?= isset($year) ? (int)$year : (int)date('Y') ?>"
                                        name="year" />
                                    <div class="input-group-append" data-target="#year" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                <?php echo form_error('year', '<div class="text-danger small">', '</div>'); ?>
                                <small class="text-muted">
                                    Akan menampilkan kolom: <b>Year + 1</b> & <b>Year + 2</b>.
                                </small>
                            </div>

                            <div class="col-lg-4">
                                <button type="submit" class="btn btn-primary w-100">Change</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Replacement Table Chart</h3>
            </div>

            <form action="<?= base_url('RTC/submit') ?>" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">

                <div class="card-body">
                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>MATRIX POINT</th>
                                <th>SITE</th>
                                <th>LEVEL</th>
                                <th>JABATAN</th>
                                <th>FULL NAME</th>
                                <th>NRP</th>
                                <th><?= (int)$year1 ?></th>
                                <th><?= (int)$year2 ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $i  = 1;
                            $y1 = (int)$year1;
                            $y2 = (int)$year2;

                            $rtcIndex = [];
                            if (!empty($rtcs)) {
                                foreach ($rtcs as $r) {
                                    $yr  = (int)$r['year'];
                                    $pid = (int)$r['oalp_id'];
                                    $rtcIndex[$yr][$pid][] = (string)$r['NRP'];
                                }
                            }

                            function esc_attr($s)
                            {
                                return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
                            }
                            ?>

                            <?php foreach ($positions as $pos_i): ?>
                                <?php
                                $pid  = (int)$pos_i['id'];

                                $sel1 = array_values(array_unique($rtcIndex[$y1][$pid] ?? []));
                                $sel2 = array_values(array_unique($rtcIndex[$y2][$pid] ?? []));

                                $sel1_json = esc_attr(json_encode($sel1));
                                $sel2_json = esc_attr(json_encode($sel2));
                                ?>

                                <tr data-id="<?= $pid ?>">
                                    <td><?= $i++ ?></td>
                                    <td><?= $pos_i['mp_name'] ?></td>
                                    <td><?= $pos_i['oa_name'] ?></td>
                                    <td><?= $pos_i['oal_name'] ?></td>
                                    <td><?= $pos_i['oalp_name'] ?></td>
                                    <td><?= $pos_i['FullName'] ?></td>
                                    <td><?= $pos_i['NRP'] ?></td>

                                    <!-- Year +1 -->
                                    <td data-name="year_<?= $y1 ?>">
                                        <button type="button"
                                            class="btn btn-xs btn-primary btn-block mb-2 rtc-btn-edit"
                                            data-year="<?= $y1 ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <input type="hidden"
                                            class="rtc-hidden"
                                            data-name="year_<?= $y1 ?>"
                                            value="<?= $sel1_json ?>">

                                        <ul class="rtc-list list-unstyled mb-0"></ul>
                                    </td>

                                    <!-- Year +2 -->
                                    <td data-name="year_<?= $y2 ?>">
                                        <button type="button"
                                            class="btn btn-xs btn-primary btn-block mb-2 rtc-btn-edit"
                                            data-year="<?= $y2 ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <input type="hidden"
                                            class="rtc-hidden"
                                            data-name="year_<?= $y2 ?>"
                                            value="<?= $sel2_json ?>">

                                        <ul class="rtc-list list-unstyled mb-0"></ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-3">
                            <button type="button" class="w-100 btn btn-default" onclick="cancelForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>

                        <div class="col-lg-9">
                            <button type="submit" class="w-100 btn btn-info">
                                <i class="fas fa-paper-plane"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</section>

<!-- Modal RTC -->
<div class="modal fade" id="rtcModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih NRP RTC - <span id="rtcModalYearLabel"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <select id="rtcModalSelect" class="form-control" multiple="multiple" style="width:100%"></select>
                <small class="text-muted d-block mt-2">Ketik untuk mencari (fuzzy) & pilih multiple.</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="rtcModalSave">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/css/select2.min.css') ?>">
<script src="<?= base_url('assets/plugins/select2/js/select2.full.min.js') ?>"></script>

<script>
    $(function() {
        // year picker (tahun saja)
        $('#year').datetimepicker({
            format: 'YYYY',
            viewMode: 'years',
            icons: {
                time: 'far fa-clock',
                date: 'far fa-calendar',
                up: 'fas fa-chevron-up',
                down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right',
                today: 'fas fa-calendar-check',
                clear: 'far fa-trash-alt',
                close: 'far fa-times-circle'
            }
        });
    });
</script>

<script>
    /** Cancel */
    function cancelForm() {
        if (confirm('Yakin batal?')) location.href = '<?= base_url('RTC') ?>';
    }

    /** Tahun aktif (dinamis) */
    const RTC_YEAR1 = <?= (int)$year1 ?>;
    const RTC_YEAR2 = <?= (int)$year2 ?>;
    const RTC_COL1 = 'year_' + RTC_YEAR1;
    const RTC_COL2 = 'year_' + RTC_YEAR2;

    /** USERS options (select2) */
    window.RTC_USER_OPTIONS = (function() {
        const raw = <?= json_encode($users ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        return (raw || []).map(u => ({
            id: String(u.NRP ?? u['NRP'] ?? ''),
            text: (u.FullName || u['FullName']) ?
                `${u.NRP ?? u['NRP']} - ${u.FullName ?? u['FullName']}` : String(u.NRP ?? u['NRP'] ?? '')
        })).filter(x => x.id);
    })();
    window.RTC_USER_MAP = (function() {
        const m = {};
        (window.RTC_USER_OPTIONS || []).forEach(o => m[o.id] = o.text);
        return m;
    })();

    /**
     * Store global (aman untuk paging)
     * RTC_STORE[oalp_id][year] = [nrp,...]
     */
    window.RTC_STORE = window.RTC_STORE || {};

    /** Utils */
    function safeParseJsonArray(v) {
        if (!v) return [];
        try {
            const x = JSON.parse(v);
            return Array.isArray(x) ? x.map(String).filter(Boolean) : [];
        } catch (e) {
            return [];
        }
    }

    function uniq(arr) {
        const seen = {};
        const out = [];
        (arr || []).forEach(v => {
            v = String(v || '').trim();
            if (!v) return;
            if (seen[v]) return;
            seen[v] = true;
            out.push(v);
        });
        return out;
    }

    function intYear(y) {
        return parseInt(y, 10) || 0;
    }

    /** DataTables nodes (semua halaman) */
    function getAllRowNodes() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#datatable')) {
            const dt = $('#datatable').DataTable();
            return dt.rows().nodes(); // semua pages
        }
        return $('#datatable tbody tr').toArray();
    }

    /** Init STORE dari table (all rows) */
    function initStoreFromTable() {
        const nodes = getAllRowNodes();

        $(nodes).each(function() {
            const $tr = $(this);
            const id = $tr.attr('data-id');
            if (!id) return;

            window.RTC_STORE[id] = window.RTC_STORE[id] || {};

            const v1 = safeParseJsonArray($tr.find(`input.rtc-hidden[data-name="${RTC_COL1}"]`).val());
            const v2 = safeParseJsonArray($tr.find(`input.rtc-hidden[data-name="${RTC_COL2}"]`).val());

            window.RTC_STORE[id][RTC_YEAR1] = uniq(v1);
            window.RTC_STORE[id][RTC_YEAR2] = uniq(v2);
        });
    }

    /** Render cell (row visible) dari STORE */
    function renderCellFromStore($tr, year) {
        const id = $tr.attr('data-id');
        if (!id) return;

        const list = (window.RTC_STORE[id] && window.RTC_STORE[id][year]) ? window.RTC_STORE[id][year] : [];
        const colName = 'year_' + year;

        const $cell = $tr.find(`td[data-name="${colName}"]`);
        if (!$cell.length) return;

        // sync hidden
        $cell.find('input.rtc-hidden').val(JSON.stringify(list));

        // render UL
        const $ul = $cell.find('.rtc-list');
        $ul.empty();

        if (!list.length) {
            $ul.append(`<li class="text-muted"><em>-</em></li>`);
            return;
        }

        list.forEach(nrp => {
            const label = window.RTC_USER_MAP[nrp] || nrp;
            $ul.append(`<li class="mb-1"><span class="badge badge-light">${label}</span></li>`);
        });
    }

    function renderVisibleRowsFromStore() {
        $('#datatable tbody tr').each(function() {
            const $tr = $(this);
            renderCellFromStore($tr, RTC_YEAR1);
            renderCellFromStore($tr, RTC_YEAR2);
        });
    }

    /** Modal state */
    window.RTC_MODAL_STATE = {
        rowId: null,
        year: null
    };

    function initRtcModalSelect2() {
        const $sel = $('#rtcModalSelect');
        if ($sel.data('select2')) return;

        $sel.select2({
            width: '100%',
            placeholder: 'Pilih NRP',
            allowClear: true,
            data: window.RTC_USER_OPTIONS
        });

        // optional fuzzy wrapper
        if (window.FuzzySelect2 && typeof window.FuzzySelect2.apply === 'function') {
            window.FuzzySelect2.apply($sel);
        }
    }

    function openRtcModal(rowId, year) {
        initRtcModalSelect2();

        const yy = intYear(year);

        window.RTC_MODAL_STATE.rowId = String(rowId);
        window.RTC_MODAL_STATE.year = yy;

        $('#rtcModalYearLabel').text(yy);

        const current = (window.RTC_STORE[rowId] && window.RTC_STORE[rowId][yy]) ?
            window.RTC_STORE[rowId][yy] : [];

        $('#rtcModalSelect').val(current).trigger('change');
        $('#rtcModal').modal('show');
    }

    $(document).on('click', '.rtc-btn-edit', function() {
        const $btn = $(this);
        const $tr = $btn.closest('tr');
        const id = $tr.attr('data-id');
        if (!id) return;

        openRtcModal(id, $btn.data('year'));
    });

    $('#rtcModalSave').on('click', function() {
        const rowId = window.RTC_MODAL_STATE.rowId;
        const year = window.RTC_MODAL_STATE.year;
        if (!rowId || !year) {
            $('#rtcModal').modal('hide');
            return;
        }

        const picked = uniq($('#rtcModalSelect').val() || []);

        window.RTC_STORE[rowId] = window.RTC_STORE[rowId] || {};
        window.RTC_STORE[rowId][year] = picked;

        // update row yang sedang tampil (kalau visible)
        const $tr = $('#datatable tbody tr[data-id="' + rowId + '"]');
        if ($tr.length) renderCellFromStore($tr, year);

        $('#rtcModal').modal('hide');
    });

    /** Payload dari STORE (semua row, semua halaman) */
    function buildRtcPayloadFromStore() {
        const updates = [];

        Object.keys(window.RTC_STORE || {}).forEach(id => {
            const y1 = window.RTC_STORE[id][RTC_YEAR1] || [];
            const y2 = window.RTC_STORE[id][RTC_YEAR2] || [];

            updates.push({
                id: String(id),
                [RTC_COL1]: y1,
                [RTC_COL2]: y2
            });
        });

        return {
            creates: [],
            updates: updates,
            deletes: [],
            year1: RTC_YEAR1,
            year2: RTC_YEAR2
        };
    }

    $(function() {
        // init datatable + filter column
        if (typeof setupFilterableDatatable === 'function') {
            setupFilterableDatatable($('.datatable-filter-column'));
        }

        // init store dari semua row (across pages)
        initStoreFromTable();

        // render visible
        renderVisibleRowsFromStore();

        // redraw/paging/search -> render ulang yang visible dari STORE
        $('#datatable').on('draw.dt', function() {
            renderVisibleRowsFromStore();
        });

        // submit: pakai STORE
        $('#data-form').on('submit', function() {
            const payload = buildRtcPayloadFromStore();
            $('#json_data').val(JSON.stringify(payload));
        });
    });
</script>