<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$is_comp_settings = strpos($this->uri->uri_string(), 'comp_settings/') !== false;
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dictionary of Competency</h1>
            </div>
            <div class="col-sm-6 text-right">
                <?php if ($is_comp_settings) : ?>
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-toggle="modal"
                        data-target="#modalAddCompetency">
                        <i class="fas fa-plus"></i> Add Competency
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs rotate-tabs text-center" id="custom-tabs-tab" role="tablist">
                    <li class="nav-item tab-label-static">
                        <span>
                            <strong class="rotate-text static-tab-text">PERILAKU</strong>
                        </span>
                    </li>

                    <?php
                    $behavior_index = 0;
                    foreach ($dictionaries as $dict_i) :
                        if ($dict_i['type'] != "behavior") continue;
                        $activeClass = $behavior_index == 0 ? 'active' : '';
                    ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeClass ?>"
                                id="custom-tabs-<?= md5($dict_i['id']) ?>-tab"
                                data-toggle="pill"
                                href="#custom-tabs-<?= md5($dict_i['id']) ?>"
                                role="tab"
                                aria-controls="custom-tabs-<?= md5($dict_i['id']) ?>"
                                aria-selected="true">
                                <span class="rotate-text"><?= $dict_i['name'] ?></span>
                            </a>
                        </li>
                    <?php
                        $behavior_index++;
                    endforeach;
                    ?>

                    <li class="nav-item tab-label-static">
                        <span>
                            <strong class="rotate-text static-tab-text">PERAN</strong>
                        </span>
                    </li>

                    <?php
                    $role_index = 0;
                    foreach ($dictionaries as $dict_i) :
                        if ($dict_i['type'] != "role") continue;
                        $activeClass = ($behavior_index == 0 && $role_index == 0) ? 'active' : '';
                    ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeClass ?>"
                                id="custom-tabs-<?= md5($dict_i['id']) ?>-tab"
                                data-toggle="pill"
                                href="#custom-tabs-<?= md5($dict_i['id']) ?>"
                                role="tab"
                                aria-controls="custom-tabs-<?= md5($dict_i['id']) ?>"
                                aria-selected="true">
                                <span class="rotate-text"><?= $dict_i['name'] ?></span>
                            </a>
                        </li>
                    <?php
                        $role_index++;
                    endforeach;
                    ?>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="custom-tabs-tabContent">
                    <?php
                    $content_index = 0;
                    foreach ($dictionaries as $dict_i) :
                        $activeClass = $content_index == 0 ? 'show active' : '';

                        $has_dim_1 = !empty(trim($dict_i['dimension_1'] ?? ''));
                        $has_dim_2 = !empty(trim($dict_i['dimension_2'] ?? ''));
                        $has_dim_3 = !empty(trim($dict_i['dimension_3'] ?? ''));
                    ?>
                        <div class="tab-pane fade <?= $activeClass ?>"
                            id="custom-tabs-<?= md5($dict_i['id']) ?>"
                            role="tabpanel"
                            aria-labelledby="custom-tabs-<?= md5($dict_i['id']) ?>-tab">

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th colspan="7" class="bg-warning">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                <span>
                                                    <?= $dict_i['name'] ?> (<?= $dict_i['code'] ?>)
                                                </span>

                                                <?php if ($is_comp_settings) : ?>
                                                    <div class="btn-group mt-2 mt-md-0">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-primary btn-edit-competency"
                                                            data-toggle="modal"
                                                            data-target="#modalEditCompetency"

                                                            data-hash_comp_lvl_id="<?= md5($dict_i['id']) ?>"
                                                            data-area_lvl_id="<?= htmlspecialchars($dict_i['area_lvl_id'] ?? '', ENT_QUOTES) ?>"
                                                            data-name="<?= htmlspecialchars($dict_i['name'] ?? '', ENT_QUOTES) ?>"
                                                            data-code="<?= htmlspecialchars($dict_i['code'] ?? '', ENT_QUOTES) ?>"
                                                            data-type="<?= htmlspecialchars($dict_i['type'] ?? '', ENT_QUOTES) ?>"
                                                            data-definisi="<?= htmlspecialchars($dict_i['definisi'] ?? '', ENT_QUOTES) ?>"
                                                            data-keterangan="<?= htmlspecialchars($dict_i['keterangan'] ?? '', ENT_QUOTES) ?>"

                                                            data-dimension_1="<?= htmlspecialchars($dict_i['dimension_1'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_1_t="<?= htmlspecialchars($dict_i['indicator_1_1_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_1_b="<?= htmlspecialchars($dict_i['indicator_1_1_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_2_t="<?= htmlspecialchars($dict_i['indicator_1_2_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_2_b="<?= htmlspecialchars($dict_i['indicator_1_2_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_3_t="<?= htmlspecialchars($dict_i['indicator_1_3_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_3_b="<?= htmlspecialchars($dict_i['indicator_1_3_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_4_t="<?= htmlspecialchars($dict_i['indicator_1_4_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_4_b="<?= htmlspecialchars($dict_i['indicator_1_4_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_5_t="<?= htmlspecialchars($dict_i['indicator_1_5_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_5_b="<?= htmlspecialchars($dict_i['indicator_1_5_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_6_t="<?= htmlspecialchars($dict_i['indicator_1_6_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_1_6_b="<?= htmlspecialchars($dict_i['indicator_1_6_b'] ?? '', ENT_QUOTES) ?>"

                                                            data-dimension_2="<?= htmlspecialchars($dict_i['dimension_2'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_1_t="<?= htmlspecialchars($dict_i['indicator_2_1_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_1_b="<?= htmlspecialchars($dict_i['indicator_2_1_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_2_t="<?= htmlspecialchars($dict_i['indicator_2_2_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_2_b="<?= htmlspecialchars($dict_i['indicator_2_2_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_3_t="<?= htmlspecialchars($dict_i['indicator_2_3_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_3_b="<?= htmlspecialchars($dict_i['indicator_2_3_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_4_t="<?= htmlspecialchars($dict_i['indicator_2_4_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_4_b="<?= htmlspecialchars($dict_i['indicator_2_4_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_5_t="<?= htmlspecialchars($dict_i['indicator_2_5_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_5_b="<?= htmlspecialchars($dict_i['indicator_2_5_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_6_t="<?= htmlspecialchars($dict_i['indicator_2_6_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_2_6_b="<?= htmlspecialchars($dict_i['indicator_2_6_b'] ?? '', ENT_QUOTES) ?>"

                                                            data-dimension_3="<?= htmlspecialchars($dict_i['dimension_3'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_1_t="<?= htmlspecialchars($dict_i['indicator_3_1_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_1_b="<?= htmlspecialchars($dict_i['indicator_3_1_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_2_t="<?= htmlspecialchars($dict_i['indicator_3_2_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_2_b="<?= htmlspecialchars($dict_i['indicator_3_2_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_3_t="<?= htmlspecialchars($dict_i['indicator_3_3_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_3_b="<?= htmlspecialchars($dict_i['indicator_3_3_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_4_t="<?= htmlspecialchars($dict_i['indicator_3_4_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_4_b="<?= htmlspecialchars($dict_i['indicator_3_4_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_5_t="<?= htmlspecialchars($dict_i['indicator_3_5_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_5_b="<?= htmlspecialchars($dict_i['indicator_3_5_b'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_6_t="<?= htmlspecialchars($dict_i['indicator_3_6_t'] ?? '', ENT_QUOTES) ?>"
                                                            data-indicator_3_6_b="<?= htmlspecialchars($dict_i['indicator_3_6_b'] ?? '', ENT_QUOTES) ?>">
                                                            <i class="fas fa-edit"></i> Edit Competency
                                                        </button>

                                                        <a href="<?= base_url('comp_settings/level_matrix/comp_lvl/delete/' . md5($dict_i['id'])) ?>"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Yakin ingin menghapus competency ini?')">
                                                            <i class="fas fa-trash"></i> Delete Competency
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td colspan="7">
                                            <strong>Definisi:</strong><br>
                                            <?= $dict_i['definisi'] ?>

                                            <?php if (!empty($dict_i['keterangan'])) : ?>
                                                <br><br>
                                                <strong>Keterangan:</strong><br>
                                                <?= $dict_i['keterangan'] ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td rowspan="2" class="bg-warning">DIMENSI</td>
                                        <td colspan="6" class="text-center bg-warning">INDIKATOR PERILAKU</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center bg-warning">1<br>PEMULA (ENTRY)</td>
                                        <td class="text-center bg-warning">2<br>DASAR (BASIC)</td>
                                        <td class="text-center bg-warning">3<br>MAMPU (INTERMEDIATE)</td>
                                        <td class="text-center bg-warning">4<br>CAKAP (ADVANCED)</td>
                                        <td class="text-center bg-warning">5<br>AHLI (EXPERT)</td>
                                        <td class="text-center bg-warning">6<br>MASTERY</td>
                                    </tr>

                                    <?php if ($has_dim_1) : ?>
                                        <tr>
                                            <td rowspan="2" class="bg-secondary"><?= $dict_i['dimension_1'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_1_1_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_1_2_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_1_3_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_1_4_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_1_5_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_1_6_t'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= $dict_i['indicator_1_1_b'] ?></td>
                                            <td><?= $dict_i['indicator_1_2_b'] ?></td>
                                            <td><?= $dict_i['indicator_1_3_b'] ?></td>
                                            <td><?= $dict_i['indicator_1_4_b'] ?></td>
                                            <td><?= $dict_i['indicator_1_5_b'] ?></td>
                                            <td><?= $dict_i['indicator_1_6_b'] ?></td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php if ($has_dim_2) : ?>
                                        <tr>
                                            <td rowspan="2" class="bg-secondary"><?= $dict_i['dimension_2'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_2_1_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_2_2_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_2_3_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_2_4_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_2_5_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_2_6_t'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= $dict_i['indicator_2_1_b'] ?></td>
                                            <td><?= $dict_i['indicator_2_2_b'] ?></td>
                                            <td><?= $dict_i['indicator_2_3_b'] ?></td>
                                            <td><?= $dict_i['indicator_2_4_b'] ?></td>
                                            <td><?= $dict_i['indicator_2_5_b'] ?></td>
                                            <td><?= $dict_i['indicator_2_6_b'] ?></td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php if ($has_dim_3) : ?>
                                        <tr>
                                            <td rowspan="2" class="bg-secondary"><?= $dict_i['dimension_3'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_3_1_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_3_2_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_3_3_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_3_4_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_3_5_t'] ?></td>
                                            <td class="bg-secondary"><?= $dict_i['indicator_3_6_t'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= $dict_i['indicator_3_1_b'] ?></td>
                                            <td><?= $dict_i['indicator_3_2_b'] ?></td>
                                            <td><?= $dict_i['indicator_3_3_b'] ?></td>
                                            <td><?= $dict_i['indicator_3_4_b'] ?></td>
                                            <td><?= $dict_i['indicator_3_5_b'] ?></td>
                                            <td><?= $dict_i['indicator_3_6_b'] ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php
                        $content_index++;
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($is_comp_settings) : ?>
    <!-- Modal Add Competency -->
    <div class="modal fade" id="modalAddCompetency" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="<?= base_url('comp_settings/level_matrix/comp_lvl/add') ?>" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Competency</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title">Informasi Utama</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Area Level ID</label>
                                        <input type="text" class="form-control" name="area_lvl_id">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Code</label>
                                        <input type="text" class="form-control" name="code">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Type</label>
                                        <select class="form-control" name="type">
                                            <option value="">-- Pilih Type --</option>
                                            <option value="behavior">Behavior</option>
                                            <option value="role">Role</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Definisi</label>
                                        <textarea class="form-control" name="definisi" rows="4"></textarea>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Keterangan</label>
                                        <textarea class="form-control" name="keterangan" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php for ($d = 1; $d <= 3; $d++) : ?>
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Dimension <?= $d ?></h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Dimension <?= $d ?></label>
                                        <input type="text" class="form-control" name="dimension_<?= $d ?>">
                                    </div>

                                    <div class="row">
                                        <?php for ($i = 1; $i <= 6; $i++) : ?>
                                            <div class="col-md-6">
                                                <div class="card card-light">
                                                    <div class="card-header">
                                                        <strong>Indicator <?= $d ?>.<?= $i ?></strong>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label>Title</label>
                                                            <input type="text" class="form-control" name="indicator_<?= $d ?>_<?= $i ?>_t">
                                                        </div>
                                                        <div class="form-group mb-0">
                                                            <label>Behavior</label>
                                                            <textarea class="form-control" name="indicator_<?= $d ?>_<?= $i ?>_b" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Competency -->
    <div class="modal fade" id="modalEditCompetency" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="<?= base_url('comp_settings/level_matrix/comp_lvl/edit') ?>" method="post">

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Competency</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="hash_comp_lvl_id" id="edit_hash_comp_lvl_id">

                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Informasi Utama</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Area Level ID</label>
                                        <input type="text" class="form-control" name="area_lvl_id" id="edit_area_lvl_id">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name" id="edit_name">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Code</label>
                                        <input type="text" class="form-control" name="code" id="edit_code">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Type</label>
                                        <select class="form-control" name="type" id="edit_type">
                                            <option value="">-- Pilih Type --</option>
                                            <option value="behavior">Behavior</option>
                                            <option value="role">Role</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Definisi</label>
                                        <textarea class="form-control" name="definisi" id="edit_definisi" rows="4"></textarea>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Keterangan</label>
                                        <textarea class="form-control" name="keterangan" id="edit_keterangan" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php for ($d = 1; $d <= 3; $d++) : ?>
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Dimension <?= $d ?></h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Dimension <?= $d ?></label>
                                        <input type="text" class="form-control" name="dimension_<?= $d ?>" id="edit_dimension_<?= $d ?>">
                                    </div>

                                    <div class="row">
                                        <?php for ($i = 1; $i <= 6; $i++) : ?>
                                            <div class="col-md-6">
                                                <div class="card card-light">
                                                    <div class="card-header">
                                                        <strong>Indicator <?= $d ?>.<?= $i ?></strong>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label>Title</label>
                                                            <input type="text" class="form-control" name="indicator_<?= $d ?>_<?= $i ?>_t" id="edit_indicator_<?= $d ?>_<?= $i ?>_t">
                                                        </div>
                                                        <div class="form-group mb-0">
                                                            <label>Behavior</label>
                                                            <textarea class="form-control" name="indicator_<?= $d ?>_<?= $i ?>_b" id="edit_indicator_<?= $d ?>_<?= $i ?>_b" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
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

    .tab-label-static {
        width: 60px;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .static-tab-text {
        color: black;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    #modalEditCompetency .card,
    #modalAddCompetency .card {
        margin-bottom: 1rem;
    }
</style>

<?php if ($is_comp_settings) : ?>
    <script>
        $(document).on('click', '.btn-edit-competency', function() {
            const btn = $(this);

            $('#edit_hash_comp_lvl_id').val(btn.data('hash_comp_lvl_id'));
            $('#edit_area_lvl_id').val(btn.data('area_lvl_id'));
            $('#edit_name').val(btn.data('name'));
            $('#edit_code').val(btn.data('code'));
            $('#edit_type').val(btn.data('type'));
            $('#edit_definisi').val(btn.data('definisi'));
            $('#edit_keterangan').val(btn.data('keterangan'));

            for (let d = 1; d <= 3; d++) {
                $('#edit_dimension_' + d).val(btn.data('dimension_' + d));

                for (let i = 1; i <= 6; i++) {
                    $('#edit_indicator_' + d + '_' + i + '_t').val(btn.data('indicator_' + d + '_' + i + '_t'));
                    $('#edit_indicator_' + d + '_' + i + '_b').val(btn.data('indicator_' + d + '_' + i + '_b'));
                }
            }
        });
    </script>
<?php endif; ?>