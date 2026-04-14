<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Replacement Table Chart</h1>
    </div>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">RTC</h3>
            </div>

            <div class="card-body">
                <form method="get" action="<?= base_url('RTC') ?>" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="year">Tahun Awal</label>
                            <input type="number" name="year" id="year" class="form-control" value="<?= (int)$year ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="button" id="btnEditRtc" class="btn btn-warning w-100">Edit</button>
                        </div>
                    </div>
                </form>

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
                            <?php foreach ($years as $yr): ?>
                                <th><?= $yr ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($rows as $row): ?>
                            <tr data-oalp-id="<?= (int)$row['id']; ?>">
                                <td><?= $no++ ?></td>
                                <td><?= $row['matrix_point'] ?: '' ?></td>
                                <td><?= $row['site'] ?: '' ?></td>
                                <td><?= $row['level'] ?: '' ?></td>
                                <td><?= $row['jabatan'] ?: '' ?></td>
                                <td><?= $row['full_name'] ?: '' ?></td>
                                <td><?= $row['nrp'] ?: '' ?></td>

                                <?php foreach ($years as $yr): ?>
                                    <?php $assigned = isset($row['years'][$yr]) ? $row['years'][$yr] : []; ?>
                                    <td class="rtc-year-cell"
                                        data-year="<?= (int)$yr; ?>"
                                        data-oalp-id="<?= (int)$row['id']; ?>">
                                        <div class="rtc-assignment-box">
                                            <?php if (!empty($assigned)): ?>
                                                <?php foreach ($assigned as $a): ?>
                                                    <div class="rtc-item"
                                                        data-nrp="<?= html_escape($a['NRP']); ?>"
                                                        data-name="<?= html_escape($a['FullName']); ?>">
                                                        <?= html_escape($a['NRP']); ?> - <?= html_escape($a['FullName']); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="rtc-assignment-empty">Belum ada kandidat</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="rtc-inline-action mt-1" style="display:none;">
                                            <button type="button" class="btn btn-xs btn-outline-primary btn-assign-candidate">
                                                Assign Candidate
                                            </button>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div id="rtcActionWrapper" class="mt-3" style="display:none;">
                    <div class="row">
                        <div class="col-md-3">
                            <button type="button" id="btnCancelRtc" class="btn btn-secondary w-100">Cancel</button>
                        </div>
                        <div class="col-md-9">
                            <button type="button" id="btnSubmitRtc" class="btn btn-primary w-100">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->

    </div>
</section>
<!-- /.content -->

<div class="modal fade" id="modalAssignCandidate" tabindex="-1" role="dialog" aria-labelledby="modalAssignCandidateLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modalAssignCandidateLabel" class="modal-title">Assign Candidate</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="modal_oalp_id">
                <input type="hidden" id="modal_year">

                <div class="form-group">
                    <label>Pilih Candidate</label>
                    <select id="candidateSelect" class="form-control" multiple="multiple" style="width:100%;"></select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" id="btnApplyCandidate" class="btn btn-primary">Apply</button>
            </div>
        </div>
    </div>
</div>

<style>
    .rtc-year-cell {
        min-width: 220px;
        vertical-align: top;
    }

    .rtc-assignment-box {
        min-height: 38px;
        padding: 6px 8px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #f8f9fa;
        font-size: 12px;
        line-height: 1.5;
    }

    .rtc-assignment-box.editable {
        background: #fffdf2;
        border-style: dashed;
    }

    .rtc-assignment-empty {
        color: #999;
        font-style: italic;
    }
</style>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));

        let isEditMode = false;
        let originalSnapshot = {};
        let currentTargetCell = null;

        initCandidateSelect();
        takeSnapshot();

        $('#btnEditRtc').on('click', function() {
            isEditMode = true;
            $('.rtc-inline-action').show();
            $('.rtc-assignment-box').addClass('editable');
            $('#rtcActionWrapper').show();
        });

        $('#btnCancelRtc').on('click', function() {
            restoreSnapshot();
            isEditMode = false;
            $('.rtc-inline-action').hide();
            $('.rtc-assignment-box').removeClass('editable');
            $('#rtcActionWrapper').hide();
        });

        $(document).on('click', '.btn-assign-candidate', function() {
            if (!isEditMode) return false;

            const $td = $(this).closest('td');
            const oalpId = $td.data('oalp-id');
            const year = $td.data('year');

            currentTargetCell = $td;

            $('#modal_oalp_id').val(oalpId);
            $('#modal_year').val(year);

            loadSelectedCandidate(oalpId, year, function() {
                $('#modalAssignCandidate').modal('show');
            });
        });

        $('#btnApplyCandidate').on('click', function() {
            const data = $('#candidateSelect').select2('data') || [];
            renderCellAssignment(currentTargetCell, data);
            $('#modalAssignCandidate').modal('hide');
        });

        $('#btnSubmitRtc').on('click', function() {
            const payload = collectPayload();

            $.ajax({
                url: "<?= site_url('RTC/save'); ?>",
                type: "POST",
                dataType: "json",
                data: {
                    base_year: <?= (int)$year; ?>,
                    rows: JSON.stringify(payload)
                },
                success: function(res) {
                    if (res.status) {
                        Swal.fire('Berhasil', res.message, 'success').then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menyimpan data.', 'error');
                    }
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan saat menyimpan.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        });

        function initCandidateSelect() {
            $('#candidateSelect').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modalAssignCandidate'),
                ajax: {
                    url: "<?= site_url('RTC/candidate_options'); ?>",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function(data) {
                        return data;
                    }
                }
            });
        }

        function loadSelectedCandidate(oalpId, year, callback) {
            $('#candidateSelect').empty().trigger('change');

            $.ajax({
                url: "<?= site_url('RTC/get_year_assignment'); ?>",
                type: "GET",
                dataType: "json",
                data: {
                    oalp_id: oalpId,
                    year: year
                },
                success: function(res) {
                    if (res.status && res.selected) {
                        $.each(res.selected, function(i, item) {
                            const option = new Option(item.text, item.id, true, true);
                            $('#candidateSelect').append(option).trigger('change');
                        });
                    }

                    const frontendSelected = getCellAssignmentData(currentTargetCell);
                    if (frontendSelected.length > 0) {
                        $('#candidateSelect').empty().trigger('change');
                        $.each(frontendSelected, function(i, item) {
                            const option = new Option(item.text, item.id, true, true);
                            $('#candidateSelect').append(option).trigger('change');
                        });
                    }

                    if (typeof callback === 'function') callback();
                },
                error: function() {
                    if (typeof callback === 'function') callback();
                }
            });
        }

        function renderCellAssignment($td, selectedData) {
            if (!$td || !$td.length) return;

            const $box = $td.find('.rtc-assignment-box');
            $box.empty();

            if (!selectedData || selectedData.length === 0) {
                $box.html('<span class="rtc-assignment-empty">Belum ada kandidat</span>');
                return;
            }

            $.each(selectedData, function(i, item) {
                const nrp = item.id || item.nrp || '';
                const text = item.text || '';
                let name = item.name || '';

                if (!name && text.indexOf(' - ') > -1) {
                    const parts = text.split(' - ');
                    parts.shift();
                    name = parts.join(' - ');
                }

                $box.append(`
                    <div class="rtc-item" data-nrp="${escapeHtml(nrp)}" data-name="${escapeHtml(name)}">
                        ${escapeHtml(nrp)} - ${escapeHtml(name)}
                    </div>
                `);
            });
        }

        function getCellAssignmentData($td) {
            const data = [];
            if (!$td || !$td.length) return data;

            $td.find('.rtc-item').each(function() {
                const nrp = $(this).data('nrp');
                const name = $(this).data('name');
                data.push({
                    id: nrp,
                    nrp: nrp,
                    name: name,
                    text: nrp + ' - ' + name
                });
            });

            return data;
        }

        function collectPayload() {
            const rows = [];

            $('#datatable tbody tr').each(function() {
                const $tr = $(this);
                const oalpId = $tr.data('oalp-id');
                if (!oalpId) return;

                const row = {
                    oalp_id: oalpId,
                    years: {}
                };

                $tr.find('td.rtc-year-cell').each(function() {
                    const $td = $(this);
                    const year = $td.data('year');
                    const nrps = [];

                    $td.find('.rtc-item').each(function() {
                        const nrp = $(this).data('nrp');
                        if (nrp) nrps.push(nrp);
                    });

                    row.years[year] = nrps;
                });

                rows.push(row);
            });

            return rows;
        }

        function takeSnapshot() {
            originalSnapshot = {};

            $('#datatable tbody tr').each(function() {
                const $tr = $(this);
                const oalpId = $tr.data('oalp-id');
                if (!oalpId) return;

                originalSnapshot[oalpId] = {};

                $tr.find('td.rtc-year-cell').each(function() {
                    const $td = $(this);
                    const year = $td.data('year');
                    originalSnapshot[oalpId][year] = getCellAssignmentData($td);
                });
            });
        }

        function restoreSnapshot() {
            $('#datatable tbody tr').each(function() {
                const $tr = $(this);
                const oalpId = $tr.data('oalp-id');
                if (!oalpId || !originalSnapshot[oalpId]) return;

                $tr.find('td.rtc-year-cell').each(function() {
                    const $td = $(this);
                    const year = $td.data('year');
                    const selected = originalSnapshot[oalpId][year] || [];
                    renderCellAssignment($td, selected);
                });
            });
        }

        function escapeHtml(text) {
            return $('<div>').text(text || '').html();
        }
    });
</script>