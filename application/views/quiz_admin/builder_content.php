<section class="content p-3">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-edit mr-2 text-primary"></i>
                                Builder — <?= htmlspecialchars($quiz['title'] ?: 'Tanpa Judul', ENT_QUOTES, 'UTF-8') ?>
                                <small class="ml-2 text-muted">#<?= (int)$quiz['id'] ?></small>
                            </h4>
                            <div class="d-flex align-items-center">
                                <div class="mr-2">
                                    <span class="badge badge-success">PIN: <?= $quiz['pin'] ? htmlspecialchars($quiz['pin'], ENT_QUOTES, 'UTF-8') : '-' ?></span>
                                </div>
                                <input type="text"
                                    id="quizTitle"
                                    class="form-control form-control-sm"
                                    style="max-width: 360px;"
                                    value="<?= htmlspecialchars($quiz['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="Judul quiz">
                                <button id="btnSaveTitle" class="btn btn-sm btn-primary ml-2">
                                    <i class="fas fa-save mr-1"></i> Simpan Judul
                                </button>
                            </div>
                        </div>
                        <div class="card-tools">
                            <a href="<?= site_url('quiz_admin/host/' . (int)$quiz['id']); ?>" class="btn btn-sm btn-outline-secondary mr-2">
                                <i class="fas fa-sliders-h mr-1"></i> Buka Host
                            </a>
                            <button id="btnDeleteQuiz" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash mr-1"></i> Hapus Quiz
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0 datatable-filter-column" id="tblQ">
                                <thead class="thead-light text-center">
                                    <tr>
                                        <th style="width:46px">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th style="width:70px">No.</th>
                                        <th style="min-width:240px">Question</th>
                                        <th>Option A</th>
                                        <th>Option B</th>
                                        <th>Option C</th>
                                        <th>Option D</th>
                                        <th style="width:80px">Answer</th>
                                        <th style="width:100px">Time(s)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyQ">
                                    <!-- rows via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-3">
                                <button type="button" class="w-100 btn btn-default" id="btnCancel">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                            <div class="col-lg-3">
                                <button type="button" class="w-100 btn btn-danger" id="btnDeleteSelected">
                                    <i class="fas fa-trash"></i> Delete Selected
                                </button>
                            </div>
                            <div class="col-lg-3 d-flex">
                                <input type="number" class="form-control w-50" id="row_number_add" value="1" min="1">
                                <button type="button" class="w-50 btn btn-success ml-1" id="btnNewRows">
                                    <i class="fas fa-plus"></i> New
                                </button>
                            </div>
                            <div class="col-lg-3">
                                <button type="button" class="w-100 btn btn-info" id="btnSubmitFooter">
                                    <i class="fas fa-paper-plane"></i> Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    (function() {
        const quizId = <?= (int)$quiz['id'] ?>;
        const $table = $('#tblQ');
        const $tbody = $('#tbodyQ');

        /* =========================
         * Helpers
         * ========================= */
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // contenteditable cell with data-name (dipakai untuk ambil data konsisten)
        function cell(name, content, cls = '') {
            return `<td contenteditable="true" data-name="${name}" class="${cls}">${content ?? ''}</td>`;
        }

        function rowTpl(r) {
            const id = (r && r.id != null) ? r.id : '';
            return `
                <tr data-id="${escapeHtml(id)}">
                <td class="text-center align-middle not-editable">
                    <input type="checkbox" class="row-checkbox">
                </td>
                <td class="text-center align-middle not-editable c-no">-</td>

                ${cell('question',   escapeHtml(r.question), 'c-question')}
                ${cell('option_a',   escapeHtml(r.option_a), 'c-a')}
                ${cell('option_b',   escapeHtml(r.option_b), 'c-b')}
                ${cell('option_c',   escapeHtml(r.option_c), 'c-c')}
                ${cell('option_d',   escapeHtml(r.option_d), 'c-d')}
                ${cell('answer',     escapeHtml(r.answer || 'A'), 'c-answer text-center')}
                ${cell('time_limit', escapeHtml((r.time_limit != null ? r.time_limit : 15)), 'c-time text-center')}
                </tr>`;
        }

        function getCrudState() {
            // datatable-filter-column.js menyimpan state per tableId (id table)
            return window.tableCrudState?.['tblQ'] || {
                deletedIds: new Set()
            };
        }

        function getCellText($tr, name) {
            return $tr.find(`td[data-name="${name}"]`).text().trim();
        }

        function buildPayloadFromTable() {
            const dt = $table.DataTable();
            const state = getCrudState();
            const deletedIds = state.deletedIds || new Set();

            const creates = [];
            const updates = [];
            const deletes = [];

            dt.rows().every(function() {
                const $tr = $(this.node());
                const rawId = String($tr.attr('data-id') || '');

                if (!rawId) return;

                const isNew = rawId.startsWith('new_');
                const isNumeric = rawId !== '' && !isNaN(rawId);
                const isDeleted = deletedIds.has(rawId);

                const row = {
                    question: getCellText($tr, 'question'),
                    option_a: getCellText($tr, 'option_a'),
                    option_b: getCellText($tr, 'option_b'),
                    option_c: getCellText($tr, 'option_c'),
                    option_d: getCellText($tr, 'option_d'),
                    answer: (getCellText($tr, 'answer') || '').toUpperCase(),
                    time_limit: parseInt(getCellText($tr, 'time_limit'), 10) || 15
                };

                if (isDeleted) {
                    if (isNumeric) deletes.push(parseInt(rawId, 10));
                    return;
                }

                if (isNew) {
                    creates.push(row);
                } else {
                    // update butuh id
                    if (isNumeric) row.id = parseInt(rawId, 10);
                    else row.id = rawId;
                    updates.push(row);
                }
            });

            return {
                quiz_id: quizId,
                updates,
                creates,
                deletes
            };
        }

        function sendSave() {
            const payload = buildPayloadFromTable();

            $.ajax({
                url: '<?= site_url('quiz_admin/api_questions_save'); ?>',
                method: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                success: function(res) {
                    if (res && res.ok) {
                        alert('Tersimpan. (' + (res.affected || 0) + ' perubahan)');
                        $('#select-all').prop('checked', false);
                        loadRows();
                    } else {
                        alert(res?.msg || 'Gagal menyimpan');
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.msg || 'Error');
                }
            });
        }

        /* =========================
         * Load rows & init DT + CRUD engine
         * ========================= */
        function loadRows() {
            $.get('<?= site_url('quiz_admin/api_questions_list/'); ?>' + quizId, function(res) {
                if (!res || !res.ok) {
                    alert(res?.msg || 'Gagal load');
                    return;
                }

                // destroy DT lama
                if ($.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().destroy();
                }

                // bersihkan filter row lama (yang dibuat setupFilterableDatatable)
                const $thead = $table.find('thead');
                if ($thead.find('.filter-btn').length) {
                    $thead.find('tr').last().remove();
                }

                $tbody.empty();

                const rows = res.rows || [];
                if (!rows.length) {
                    // kasih 2 baris awal kosong
                    $tbody.append(rowTpl({
                        id: 'new_' + Date.now() + '_a',
                        question: '',
                        option_a: '',
                        option_b: '',
                        option_c: '',
                        option_d: '',
                        answer: 'A',
                        time_limit: 15
                    }));
                    $tbody.append(rowTpl({
                        id: 'new_' + Date.now() + '_b',
                        question: '',
                        option_a: '',
                        option_b: '',
                        option_c: '',
                        option_d: '',
                        answer: 'A',
                        time_limit: 15
                    }));
                } else {
                    rows.forEach(r => $tbody.append(rowTpl(r)));
                }

                // init DataTable + filter column (datatable-filter-column.js)
                setupFilterableDatatable($table);

                // aktifkan CRUD engine (datatable-filter-column.js)
                setupDatatableCrud($table, {
                    rowIdAttr: 'data-id',
                    newIdPrefix: 'new_',

                    rowCheckboxSelector: '.row-checkbox',
                    selectAllSelector: '#select-all',
                    btnNewSelector: '#btnNewRows',
                    rowAddCountSelector: '#row_number_add',
                    btnDeleteSelectedSelector: '#btnDeleteSelected',

                    // engine pakai ini untuk generate row baru saat klik "New"
                    columns: [{
                            name: 'question',
                            type: 'contenteditable',
                            default: ''
                        },
                        {
                            name: 'option_a',
                            type: 'contenteditable',
                            default: ''
                        },
                        {
                            name: 'option_b',
                            type: 'contenteditable',
                            default: ''
                        },
                        {
                            name: 'option_c',
                            type: 'contenteditable',
                            default: ''
                        },
                        {
                            name: 'option_d',
                            type: 'contenteditable',
                            default: ''
                        },
                        {
                            name: 'answer',
                            type: 'contenteditable',
                            default: 'A'
                        },
                        {
                            name: 'time_limit',
                            type: 'contenteditable',
                            default: 15
                        }
                    ],

                    // prefix untuk 2 kolom awal: checkbox + ID
                    rowPrefixHtml: ({
                        rowId,
                        initialData
                    }) => {
                        return `
                            <td class="text-center align-middle not-editable">
                            <input type="checkbox" class="row-checkbox">
                            </td>
                            <td class="text-center align-middle not-editable c-no">-</td>
                        `;
                    }
                });

                renumberRows();
                $table.off('draw.renumber').on('draw.renumber', renumberRows);

            }, 'json').fail(function() {
                alert('Gagal load (network/server error)');
            });
        }

        /* =========================
         * UI Actions
         * ========================= */
        $('#btnSubmitFooter').on('click', sendSave);

        $('#btnCancel').on('click', function() {
            if (confirm('Yakin batal? Perubahan yang belum disimpan akan hilang.')) {
                window.location.href = '<?= site_url('quiz_admin'); ?>';
            }
        });

        // === Update Judul ===
        $('#btnSaveTitle').on('click', function() {
            const title = ($('#quizTitle').val() || '').trim();
            if (!title) {
                alert('Judul wajib diisi');
                return;
            }

            $.post('<?= site_url('quiz_admin/api_quiz_update_title'); ?>', {
                quiz_id: quizId,
                title: title
            }, function(res) {
                if (res && res.ok) {
                    alert('Judul tersimpan.');
                } else {
                    alert(res?.msg || 'Gagal update judul');
                }
            }, 'json').fail(function(xhr) {
                alert(xhr.responseJSON?.msg || 'Error');
            });
        });

        // === Hapus Quiz ===
        $('#btnDeleteQuiz').on('click', function() {
            if (!confirm('Yakin ingin menghapus seluruh quiz ini beserta pertanyaan & jawaban?')) return;

            $.post('<?= site_url('quiz_admin/api_quiz_delete'); ?>', {
                quiz_id: quizId
            }, function(res) {
                if (res && res.ok) {
                    alert('Quiz sudah dihapus.');
                    window.location.href = '<?= site_url('quiz_admin'); ?>';
                } else {
                    alert(res?.msg || 'Gagal menghapus quiz');
                }
            }, 'json').fail(function(xhr) {
                alert(xhr.responseJSON?.msg || 'Error');
            });
        });

        // init pertama
        loadRows();

        function renumberRows() {
            if (!$.fn.DataTable.isDataTable($table)) return;
            const dt = $table.DataTable();

            // nomor urut mengikuti urutan yang tampil (search + order)
            dt.rows({
                search: 'applied',
                order: 'applied'
            }).every(function(rowIdx) {
                const node = this.node();
                if (!node) return;
                $(node).find('td.c-no').text(rowIdx + 1);
            });
        }
    })();
</script>

<script>
    /* =========================
     * Validasi Answer & Time
     * ========================= */
    $(document).on('blur', '#tblQ td[data-name="answer"]', function() {
        const v = $(this).text().trim().toUpperCase();
        if (!['A', 'B', 'C', 'D'].includes(v)) {
            alert('Answer harus A/B/C/D');
            $(this).text('A');
        } else {
            $(this).text(v); // normalize
        }
    });

    $(document).on('blur', '#tblQ td[data-name="time_limit"]', function() {
        let n = parseInt($(this).text().trim(), 10);
        if (!n || n < 1) n = 15;
        $(this).text(n);
    });

    // stop Enter di cell contenteditable (biar gak bikin baris baru)
    $(document).on('keydown', '#tblQ td[contenteditable="true"]', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            return false;
        }
    });
</script>

<style>
    #tblQ td[contenteditable="true"] {
        background: #fff;
    }

    #tblQ td.not-editable {
        background: #f9f9f9;
    }

    #tblQ tbody tr.table-danger td[contenteditable="true"] {
        text-decoration: line-through;
        opacity: .6;
    }

    #tblQ thead th input[type="checkbox"] {
        transform: scale(1.1);
    }

    #tblQ tbody td:first-child,
    #tblQ thead th:first-child {
        text-align: center;
        vertical-align: middle;
    }

    .card-header .badge {
        font-weight: 600;
    }

    .card-header h4 small {
        font-weight: 400;
    }
</style>