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
                                    <span class="badge badge-success">PIN: <?= $quiz['pin'] ? htmlspecialchars($quiz['pin']) : '-' ?></span>
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
                                        <th style="width:70px">ID</th>
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
        const $tbody = $('#tbodyQ');
        let deletedRows = [];

        /* ===== helpers ===== */
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function cell(content, cls = '') {
            return `<td contenteditable="true" class="${cls}">${content ?? ''}</td>`;
        }

        function rowTpl(r) {
            const id = r.id || '';
            return `
            <tr data-id="${id}" data-deleted="0">
                <td class="text-center align-middle not-editable">
                <input type="checkbox" class="row-checkbox">
                </td>
                <td class="text-center align-middle not-editable">${id || '-'}</td>
                ${cell(escapeHtml(r.question),'c-question')}
                ${cell(escapeHtml(r.option_a),'c-a')}
                ${cell(escapeHtml(r.option_b),'c-b')}
                ${cell(escapeHtml(r.option_c),'c-c')}
                ${cell(escapeHtml(r.option_d),'c-d')}
                ${cell(escapeHtml(r.answer || ''),'c-answer text-center')}
                ${cell(r.time_limit ?? 15,'c-time text-center')}
            </tr>`;
        }

        function addRow() {
            const tempId = 'new_' + Date.now() + '_' + Math.random().toString(36).slice(2, 6);
            const r = {
                id: tempId,
                question: '',
                option_a: '',
                option_b: '',
                option_c: '',
                option_d: '',
                answer: 'A',
                time_limit: 15
            };
            const $node = $(rowTpl(r)).attr('data-id', tempId);
            if ($.fn.DataTable.isDataTable('#tblQ')) {
                $('#tblQ').DataTable().row.add($node).draw(false);
            } else {
                $tbody.append($node);
            }
        }

        /* ===== payload collector ===== */
        function collect() {
            const items = [];
            $('#tblQ tbody tr').each(function() {
                const $tr = $(this);
                const rawId = $tr.data('id');
                const id = rawId ? (isNaN(rawId) ? String(rawId) : parseInt(rawId, 10)) : 0;
                const deletedFlag = String($tr.attr('data-deleted')) === '1' || deletedRows.includes(String(id));
                const get = (sel) => $tr.find(sel).text().trim();
                const item = {
                    id,
                    question: get('.c-question'),
                    option_a: get('.c-a'),
                    option_b: get('.c-b'),
                    option_c: get('.c-c'),
                    option_d: get('.c-d'),
                    answer: get('.c-answer').toUpperCase(),
                    time_limit: parseInt(get('.c-time'), 10) || 15
                };
                if (deletedFlag) item._delete = true;
                items.push(item);
            });
            return {
                quiz_id: quizId,
                items
            };
        }

        /* ===== load rows & init datatable ===== */
        function loadRows() {
            $.get('<?= site_url('quiz_admin/api_questions_list/'); ?>' + quizId, function(res) {
                if (!res.ok) {
                    alert(res.msg || 'Gagal load');
                    return;
                }

                // destroy DT lama agar bersih
                if ($.fn.DataTable.isDataTable('#tblQ')) {
                    $('#tblQ').DataTable().destroy();
                }

                // Hapus baris filter lama (kalau ada)
                const $thead = $('#tblQ thead');
                if ($thead.find('.filter-btn').length) {
                    $thead.find('tr').last().remove();
                }

                $tbody.empty();
                if (!res.rows.length) {
                    addRow();
                    addRow();
                } else {
                    res.rows.forEach(r => $tbody.append(rowTpl(r)));
                }

                // init DataTable + filter per kolom
                setupFilterableDatatable($('#tblQ'));
            }, 'json');
        }

        /* ===== actions ===== */
        // Select-All (header)
        $(document).on('click', '#select-all', function() {
            const checked = this.checked;
            $('#tblQ tbody .row-checkbox').prop('checked', checked);
        });

        // Delete Selected → tandai baris (bukan hapus DOM)
        $('#btnDeleteSelected').on('click', function() {
            const dt = $.fn.DataTable.isDataTable('#tblQ') ? $('#tblQ').DataTable() : null;

            function mark($tr, yes) {
                const id = String($tr.data('id') || '');
                $tr.attr('data-deleted', yes ? '1' : '0')
                    .toggleClass('table-danger', !!yes)
                    .css('opacity', yes ? .7 : 1);
                if (id) {
                    if (yes && !deletedRows.includes(id)) deletedRows.push(id);
                    if (!yes) deletedRows = deletedRows.filter(x => x !== id);
                }
            }

            if (dt) {
                dt.rows().every(function() {
                    const $tr = $(this.node());
                    const checked = $tr.find('.row-checkbox').prop('checked');
                    mark($tr, checked);
                });
                dt.draw(false);
            } else {
                $('#tblQ tbody tr').each(function() {
                    const $tr = $(this);
                    const checked = $tr.find('.row-checkbox').prop('checked');
                    mark($tr, checked);
                });
            }
        });

        // Cancel → balik ke index
        $('#btnCancel').on('click', function() {
            if (confirm('Yakin batal? Perubahan yang belum disimpan akan hilang.')) {
                window.location.href = '<?= site_url('quiz_admin'); ?>';
            }
        });

        // New rows
        $('#btnNewRows').on('click', function() {
            const n = Math.max(1, parseInt($('#row_number_add').val(), 10) || 1);
            for (let i = 0; i < n; i++) addRow();
            if ($.fn.DataTable.isDataTable('#tblQ')) {
                const dt = $('#tblQ').DataTable();
                dt.columns.adjust().draw(false);
                dt.page('last').draw('page');
            }
        });

        function buildPayload() {
            const updates = [],
                creates = [],
                deletes = [];
            const dt = $.fn.DataTable.isDataTable('#tblQ') ? $('#tblQ').DataTable() : null;

            const readRow = ($tr) => {
                const rawId = $tr.data('id');
                const isDeleted = String($tr.attr('data-deleted')) === '1';
                const get = (sel) => $tr.find(sel).text().trim();
                const row = {
                    question: get('.c-question'),
                    option_a: get('.c-a'),
                    option_b: get('.c-b'),
                    option_c: get('.c-c'),
                    option_d: get('.c-d'),
                    answer: (get('.c-answer') || '').toUpperCase(),
                    time_limit: parseInt(get('.c-time'), 10) || 15
                };
                const isNumericId = rawId && !isNaN(rawId);

                if (isDeleted) {
                    if (isNumericId) deletes.push(parseInt(rawId, 10));
                    return;
                }
                if (isNumericId) {
                    row.id = parseInt(rawId, 10);
                    updates.push(row);
                } else {
                    creates.push(row);
                }
            };

            if (dt) {
                dt.rows().every(function() {
                    readRow($(this.node()));
                });
            } else {
                $('#tblQ tbody tr').each(function() {
                    readRow($(this));
                });
            }

            return {
                quiz_id: <?= (int)$quiz['id'] ?>,
                updates,
                creates,
                deletes
            };
        }

        function sendSave() {
            const payload = buildPayload();
            $.ajax({
                url: '<?= site_url('quiz_admin/api_questions_save'); ?>',
                method: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                success: function(res) {
                    if (res.ok) {
                        alert('Tersimpan. (' + (res.affected || 0) + ' perubahan)');
                        // reset UI
                        $('#select-all').prop('checked', false);
                        loadRows();
                    } else {
                        alert(res.msg || 'Gagal menyimpan');
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.msg || 'Error');
                }
            });
        }
        $('#btnSubmitFooter').on('click', sendSave);

        // === Update Judul ===
        $('#btnSaveTitle').on('click', function() {
            const title = ($('#quizTitle').val() || '').trim();
            if (!title) {
                alert('Judul wajib diisi');
                return;
            }

            $.post('<?= site_url('quiz_admin/api_quiz_update_title'); ?>', {
                    quiz_id: <?= (int)$quiz['id'] ?>,
                    title
                },
                function(res) {
                    if (res && res.ok) {
                        alert('Judul tersimpan.');
                    } else {
                        alert(res?.msg || 'Gagal update judul');
                    }
                }, 'json'
            ).fail(function(xhr) {
                alert(xhr.responseJSON?.msg || 'Error');
            });
        });

        // === Hapus Quiz ===
        $('#btnDeleteQuiz').on('click', function() {
            if (!confirm('Yakin ingin menghapus seluruh quiz ini beserta pertanyaan & jawaban?')) return;

            $.post('<?= site_url('quiz_admin/api_quiz_delete'); ?>', {
                    quiz_id: <?= (int)$quiz['id'] ?>
                },
                function(res) {
                    if (res && res.ok) {
                        alert('Quiz sudah dihapus.');
                        window.location.href = '<?= site_url('quiz_admin'); ?>';
                    } else {
                        alert(res?.msg || 'Gagal menghapus quiz');
                    }
                }, 'json'
            ).fail(function(xhr) {
                alert(xhr.responseJSON?.msg || 'Error');
            });
        });

        // init pertama
        loadRows();
    })();

    // Validasi Answer & Time saat selesai edit
    $(document).on('blur', '#tblQ .c-answer', function() {
        const v = $(this).text().trim().toUpperCase();
        if (!['A', 'B', 'C', 'D'].includes(v)) {
            alert('Answer harus A/B/C/D');
            $(this).text('A');
        }
    });
    $(document).on('blur', '#tblQ .c-time', function() {
        let n = parseInt($(this).text().trim(), 10);
        if (!n || n < 1) n = 15;
        $(this).text(n);
    });
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