<?php
$is_comp_settings = strpos($this->uri->uri_string(), 'comp_settings/') !== false;
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h1 class="m-0">Dictionary of Competency: <strong><?= $position['name'] ?></strong></h1>

            <?php if ($is_comp_settings) : ?>
                <div class="mt-2 mt-md-0">
                    <button type="button"
                        class="btn btn-primary"
                        data-toggle="modal"
                        data-target="#modal-addCompetency"
                        data-hash_area_lvl_pstn_id="<?= md5($position['id']) ?>">
                        Add Competency
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs rotate-tabs text-center" id="custom-tabs-tab" role="tablist">
                    <?php foreach ($dictionaries as $i_dict => $dict_i) : ?>
                        <?php $activeClass = $i_dict == 0 ? 'active' : ''; ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeClass ?>"
                                id="custom-tabs-<?= md5($dict_i['id']) ?>-tab"
                                data-toggle="pill"
                                href="#custom-tabs-<?= md5($dict_i['id']) ?>"
                                role="tab"
                                aria-controls="custom-tabs-<?= md5($dict_i['id']) ?>"
                                aria-selected="<?= $i_dict == 0 ? 'true' : 'false' ?>">
                                <span class="rotate-text"><?= $dict_i['name'] ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="custom-tabs-tabContent">
                    <?php foreach ($dictionaries as $i_dict => $dict_i) : ?>
                        <?php $activeClass = $i_dict == 0 ? 'show active' : ''; ?>
                        <div class="tab-pane fade <?= $activeClass ?>"
                            id="custom-tabs-<?= md5($dict_i['id']) ?>"
                            role="tabpanel"
                            aria-labelledby="custom-tabs-<?= md5($dict_i['id']) ?>-tab">

                            <?php if ($is_comp_settings) : ?>
                                <div class="mb-3 d-flex flex-wrap">
                                    <button type="button"
                                        class="btn btn-warning mr-2 mb-2"
                                        data-toggle="modal"
                                        data-target="#modal-editCompetency"
                                        data-hash_comp_pstn_id="<?= md5($dict_i['id']) ?>"
                                        data-hash_area_lvl_pstn_id="<?= md5($position['id']) ?>"
                                        data-comp_pstn_name="<?= htmlspecialchars($dict_i['name'], ENT_QUOTES) ?>"
                                        data-definition="<?= htmlspecialchars($dict_i['definition'], ENT_QUOTES) ?>"
                                        data-level_1="<?= htmlspecialchars($dict_i['level_1'], ENT_QUOTES) ?>"
                                        data-level_2="<?= htmlspecialchars($dict_i['level_2'], ENT_QUOTES) ?>"
                                        data-level_3="<?= htmlspecialchars($dict_i['level_3'], ENT_QUOTES) ?>"
                                        data-level_4="<?= htmlspecialchars($dict_i['level_4'], ENT_QUOTES) ?>"
                                        data-level_5="<?= htmlspecialchars($dict_i['level_5'], ENT_QUOTES) ?>">
                                        Edit Competency
                                    </button>

                                    <a href="<?= base_url('comp_settings/position_matrix/comp_pstn/delete/' . md5($dict_i['id']) . '?redirect=' . urlencode(current_url())) ?>"
                                        class="btn btn-danger"
                                        onclick="return confirm('Yakin ingin menghapus?');">
                                        Delete Competency
                                    </a>
                                </div>
                            <?php endif; ?>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th colspan="5" class="text-center bg-warning"><?= $dict_i['name'] ?></th>
                                    </tr>
                                    <tr>
                                        <td colspan="5">
                                            <strong>Definisi:</strong><br>
                                            <?= $dict_i['definition'] ?>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center bg-warning">
                                            INDIKATOR PERILAKU
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center bg-warning">1<br>MENGINGAT</td>
                                        <td class="text-center bg-warning">2<br>MEMAHAMI</td>
                                        <td class="text-center bg-warning">3<br>MENGAPLIKASIKAN</td>
                                        <td class="text-center bg-warning">4<br>MENGANALISIS</td>
                                        <td class="text-center bg-warning">5<br>MENGEVALUASI &amp; MENCIPTA</td>
                                    </tr>
                                    <tr>
                                        <td><?= $dict_i['level_1'] ?></td>
                                        <td><?= $dict_i['level_2'] ?></td>
                                        <td><?= $dict_i['level_3'] ?></td>
                                        <td><?= $dict_i['level_4'] ?></td>
                                        <td><?= $dict_i['level_5'] ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($dictionaries)) : ?>
                        <div class="alert alert-warning mb-0">
                            Belum ada competency untuk position ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<?php if ($is_comp_settings) : ?>
    <!-- Modal Add Competency -->
    <div class="modal fade" id="modal-addCompetency">
        <div class="modal-dialog modal-lg">
            <form action="<?= base_url('comp_settings/position_matrix/comp_pstn/add') ?>" method="post">
                <input type="hidden" name="hash_area_lvl_pstn_id" id="add_hash_area_lvl_pstn_id" required>

                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Competency</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="add_comp_pstn_name">Competency Name</label>
                            <input type="text" class="form-control" name="comp_pstn_name" id="add_comp_pstn_name" required>
                        </div>

                        <div class="form-group">
                            <label for="add_definition">Definition</label>
                            <textarea class="form-control" name="definition" id="add_definition" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_level_1">Level 1</label>
                                    <textarea class="form-control" name="level_1" id="add_level_1" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_level_2">Level 2</label>
                                    <textarea class="form-control" name="level_2" id="add_level_2" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_level_3">Level 3</label>
                                    <textarea class="form-control" name="level_3" id="add_level_3" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_level_4">Level 4</label>
                                    <textarea class="form-control" name="level_4" id="add_level_4" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="add_level_5">Level 5</label>
                            <textarea class="form-control" name="level_5" id="add_level_5" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <input type="hidden" name="redirect_url"
                            value="<?= current_url() ?>">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Competency -->
    <div class="modal fade" id="modal-editCompetency">
        <div class="modal-dialog modal-lg">
            <form action="<?= base_url('comp_settings/position_matrix/comp_pstn/edit') ?>" method="post">
                <input type="hidden" name="hash_area_lvl_pstn_id" id="edit_hash_area_lvl_pstn_id" required>
                <input type="hidden" name="hash_comp_pstn_id" id="edit_hash_comp_pstn_id" required>

                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Competency</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_comp_pstn_name">Competency Name</label>
                            <input type="text" class="form-control" name="comp_pstn_name" id="edit_comp_pstn_name" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_definition">Definition</label>
                            <textarea class="form-control" name="definition" id="edit_definition" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_level_1">Level 1</label>
                                    <textarea class="form-control" name="level_1" id="edit_level_1" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_level_2">Level 2</label>
                                    <textarea class="form-control" name="level_2" id="edit_level_2" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_level_3">Level 3</label>
                                    <textarea class="form-control" name="level_3" id="edit_level_3" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_level_4">Level 4</label>
                                    <textarea class="form-control" name="level_4" id="edit_level_4" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="edit_level_5">Level 5</label>
                            <textarea class="form-control" name="level_5" id="edit_level_5" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <input type="hidden" name="redirect_url"
                            value="<?= current_url() ?>">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<style>
    .rotate-tabs {
        overflow-x: auto;
        white-space: nowrap;
        display: flex;
        flex-wrap: nowrap;
        scrollbar-width: thin;
    }

    .rotate-tabs .nav-item {
        flex: 0 0 auto;
    }

    .rotate-tabs .nav-link {
        height: 200px;
        width: 60px;
        padding: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        border-radius: 0;
    }

    .rotate-tabs .rotate-text {
        transform: rotate(-90deg);
        display: inline-block;
        white-space: nowrap;
        text-overflow: ellipsis;
        line-height: 1.2;
        max-width: 180px;
    }
</style>

<?php if ($is_comp_settings) : ?>
    <script>
        $(function() {
            $('#modal-addCompetency').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                $('#add_hash_area_lvl_pstn_id').val(button.data('hash_area_lvl_pstn_id'));
                $('#add_comp_pstn_name').val('');
                $('#add_definition').val('');
                $('#add_level_1').val('');
                $('#add_level_2').val('');
                $('#add_level_3').val('');
                $('#add_level_4').val('');
                $('#add_level_5').val('');
            });

            $('#modal-editCompetency').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);

                $('#edit_hash_area_lvl_pstn_id').val(button.data('hash_area_lvl_pstn_id'));
                $('#edit_hash_comp_pstn_id').val(button.data('hash_comp_pstn_id'));
                $('#edit_comp_pstn_name').val(button.data('comp_pstn_name'));
                $('#edit_definition').val(button.data('definition'));
                $('#edit_level_1').val(button.data('level_1'));
                $('#edit_level_2').val(button.data('level_2'));
                $('#edit_level_3').val(button.data('level_3'));
                $('#edit_level_4').val(button.data('level_4'));
                $('#edit_level_5').val(button.data('level_5'));
            });
        });
    </script>
<?php endif; ?>