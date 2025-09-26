<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><strong>Matrix Point</strong></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="" class="table table-bordered table-striped datatable-filter-column" data-filter-columns="4:multiple,5,6">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>No</th>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Area</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($matrix_points as $i_mp => $mp_i) : ?>
                            <tr>
                                <td>
                                    <button type="button"
                                        class="btn btn-xs btn-primary btn-transfer"
                                        data-source_id="<?= htmlspecialchars($mp_i['id'] ?? $mp_i['area_lvl_pstn_id'] ?? '', ENT_QUOTES) ?>"
                                        data-position="<?= htmlspecialchars($mp_i['name'] ?? '', ENT_QUOTES) ?>"
                                        data-level="<?= htmlspecialchars($mp_i['oal_name'] ?? '', ENT_QUOTES) ?>"
                                        data-area="<?= htmlspecialchars($mp_i['oa_name'] ?? '', ENT_QUOTES) ?>">
                                        <i class="fa fa-random"></i> transfer matrix point
                                    </button>
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $mp_i['name'] ?></td>
                                <td><?= $mp_i['oal_name'] ?></td>
                                <td><?= $mp_i['oa_name'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<!-- Modal: Transfer Matrix Point -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="formAssign" method="post" action="<?= base_url('organization_settings/matrix_point/transfer_matrix_point') ?>">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="assignModalLabel"><strong>Transfer Matrix Point</strong></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="text-white">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Info posisi Sumber -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="callout callout-info py-2">
                                <div><strong>FROM (Current MP):</strong>
                                    <span id="src_position">-</span> |
                                    <strong>Level:</strong> <span id="src_level">-</span> |
                                    <strong>Area:</strong> <span id="src_area">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Posisi Tujuan -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0 datatable-filter-column" id="tablePositions" data-filter-columns="2:multiple,3,4,5">
                            <thead>
                                <tr>
                                    <th style="width:60px; text-align:center;">Select</th>
                                    <th>#</th>
                                    <th>Matrix Point</th>
                                    <th>Position</th>
                                    <th>Level</th>
                                    <th>Area</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $j = 1;
                                foreach ($positions as $p): ?>
                                    <tr>
                                        <td class="text-center align-middle">
                                            <input type="radio"
                                                name="position_pick"
                                                class="radio-position"
                                                value="<?= htmlspecialchars($p['id'] ?? '', ENT_QUOTES) ?>"
                                                data-posid="<?= htmlspecialchars($p['id'] ?? '', ENT_QUOTES) ?>"
                                                data-pmatrix="<?= htmlspecialchars($p['mp_name'] ?? '', ENT_QUOTES) ?>"
                                                data-pposition="<?= htmlspecialchars($p['name'] ?? '', ENT_QUOTES) ?>"
                                                data-plevel="<?= htmlspecialchars($p['oal_name'] ?? '', ENT_QUOTES) ?>"
                                                data-parea="<?= htmlspecialchars($p['oa_name'] ?? '', ENT_QUOTES) ?>">
                                        </td>
                                        <td><?= $j++ ?></td>
                                        <td><?= $p['mp_name'] ?? '' ?></td>
                                        <td><?= $p['name'] ?? '' ?></td>
                                        <td><?= $p['oal_name'] ?? '' ?></td>
                                        <td><?= $p['oa_name'] ?? '' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden fields -->
                    <input type="hidden" name="source_id" id="inp_source_id">
                    <input type="hidden" name="target_id" id="inp_target_id">
                    <input type="hidden" name="mode" id="inp_mode" value="transfer">
                </div>

                <div class="modal-footer">
                    <div class="d-flex align-items-center w-100">
                        <div class="mr-3">
                            <span class="text-muted">TO (Target):</span>
                            <span id="picked_position" class="font-weight-bold">Belum dipilih</span>
                        </div>
                        <div class="ml-auto">
                            <button type="submit" id="btnSaveAssign" class="btn btn-primary btn-sm show-overlay-full" disabled>
                                <i class="fa fa-exchange-alt"></i> Transfer
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        // init datatable filter
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });

        // buka modal Transfer
        $(document).on('click', '.btn-transfer', function() {
            const btn = $(this);
            const srcId = btn.data('source_id') || '';
            const pos = btn.data('position') || '';
            const lvl = btn.data('level') || '';
            const area = btn.data('area') || '';

            // isi info sumber
            $('#src_position').text(pos);
            $('#src_level').text(lvl);
            $('#src_area').text(area);

            // reset form
            $('#inp_source_id').val(srcId);
            $('#inp_target_id').val('');
            $('#inp_mode').val('transfer');
            $('#picked_position').text('Belum dipilih');
            $('#btnSaveAssign').prop('disabled', true);

            // reset radio
            $('#tablePositions input.radio-position').prop('checked', false);

            $('#assignModal').modal('show');
        });

        // pilih target
        $(document).on('change', '#tablePositions input.radio-position', function() {
            const r = $(this);
            const posId = r.data('posid');
            const mpName = r.data('pmatrix') || '';
            const posName = r.data('pposition') || '';
            const lvlName = r.data('plevel') || '';
            const area = r.data('parea') || '';

            $('#inp_mode').val('transfer');
            $('#inp_target_id').val(posId);
            $('#picked_position').text(`${posName} | Level: ${lvlName} | Area: ${area} | MP: ${mpName}`);
            $('#btnSaveAssign').prop('disabled', false);
        });

        // Validasi submit (transfer butuh target)
        $('#formAssign').on('submit', function(e) {
            const mode = $('#inp_mode').val();
            if (mode === 'transfer' && !$('#inp_target_id').val()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih posisi tujuan dulu',
                    toast: true,
                    position: 'top-end',
                    timer: 2500,
                    showConfirmButton: false
                });
                return false;
            }
        });
    });
</script>