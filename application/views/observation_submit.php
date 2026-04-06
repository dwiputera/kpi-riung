<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0"><?= isset($observation) ? 'Edit' : 'Add' ?> Observation Result</h1>
    </div>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><?= isset($observation) ? 'Edit' : 'Add' ?> the Observation Details</h3>
            </div>

            <form method="POST" action="<?= isset($observation) ? base_url('observation/update/' . $observation['id']) : base_url('observation/save') ?>">
                <div class="card-body">

                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">Informasi Umum</h5>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="NRP">Nama Operator:</label>
                                <select name="NRP" id="NRP" class="form-control select2bs4" style="width: 100%;">
                                    <option value="">-- Pilih Karyawan --</option>
                                    <?php foreach ($operators as $i_opr => $opr_i) : ?>
                                        <option value="<?= $opr_i['NRP'] ?>" <?= isset($observation) && $observation['NRP'] == $opr_i['NRP'] ? 'selected' : '' ?>>
                                            <?= $opr_i['NRP'] ?> - R<?= substr($opr_i['PSubarea'], 1) ?> - <?= $opr_i['FullName'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="observation-fields">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">Informasi Umum</h5>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date">Tanggal & Waktu:</label>
                                        <input
                                            type="datetime-local"
                                            class="form-control"
                                            id="date"
                                            name="date"
                                            value="<?= isset($observation) ? date('Y-m-d\TH:i', strtotime($observation['date'])) : '' ?>"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="job_site">Job Site:</label>
                                        <select id="job_site" name="job_site" class="form-control select2bs4" style="width: 100%;">
                                            <option value="">-- Pilih Job Site --</option>
                                            <?php foreach ($areas as $area_i) : ?>
                                                <?php if ($area_i['name'] == 'RMHO') continue; ?>
                                                <option
                                                    value="<?= $area_i['id'] ?>"
                                                    data-name="<?= $area_i['name'] ?>"
                                                    <?= isset($observation) && $observation['job_site'] == $area_i['id'] ? 'selected' : '' ?>>
                                                    <?= $area_i['name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pit_location">Lokasi Pit:</label>
                                        <select id="pit_location" name="pit_location" class="form-control select2bs4" style="width: 100%;" disabled>
                                            <option value="">-- Pilih Lokasi Pit --</option>
                                            <?php foreach ($pits as $pit_i) : ?>
                                                <option
                                                    value="<?= $pit_i['id'] ?>"
                                                    data-area-id="<?= $pit_i['area_id'] ?>"
                                                    <?= isset($observation) && $observation['pit_location'] == $pit_i['id'] ? 'selected' : '' ?>>
                                                    <?= $pit_i['name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit_type">Tipe Unit:</label>
                                        <select id="unit_type" name="unit_type" class="form-control select2bs4" style="width: 100%;" disabled>
                                            <option value="">-- Pilih Unit --</option>
                                            <?php foreach ($equipments as $equip_i) : ?>
                                                <option
                                                    value="<?= $equip_i['id'] ?>"
                                                    data-plant="<?= $equip_i['maint_plant'] ?>"
                                                    <?= isset($observation) && $observation['unit_type'] == $equip_i['id'] ? 'selected' : '' ?>>
                                                    <?= $equip_i['equipment'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="observer">Observer:</label>
                                        <input
                                            type="text"
                                            class="form-control keep-disabled"
                                            id="observer"
                                            name="observer_display"
                                            value="<?= $this->session->userdata('NRP') ?> - <?= $this->session->userdata('full_name') ?>"
                                            disabled>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">Parameter Operasional</h5>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="haul_distance">Jarak Disposal (Meter):</label>
                                        <input type="number" class="form-control" id="haul_distance" name="haul_distance" value="<?= isset($observation) ? $observation['haul_distance'] : '' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hauler_count">Jumlah Hauler:</label>
                                        <input type="number" class="form-control" id="hauler_count" name="hauler_count" value="<?= isset($observation) ? $observation['hauler_count'] : '' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="material_type">
                                            Jenis Material:
                                            <a href="javascript:void(0)" id="btnMaterialGuide" class="ml-1">(petunjuk)</a>
                                        </label>
                                        <select class="form-control" id="material_type" name="material_type" required>
                                            <option value="BLASTING" <?= isset($observation) && $observation['material_type'] == 'BLASTING' ? 'selected' : '' ?>>BLASTING</option>
                                            <option value="RIPPING" <?= isset($observation) && $observation['material_type'] == 'RIPPING' ? 'selected' : '' ?>>RIPPING</option>
                                            <option value="SOIL" <?= isset($observation) && $observation['material_type'] == 'SOIL' ? 'selected' : '' ?>>SOIL</option>
                                            <option value="NON RIPPING NON BLASTING" <?= isset($observation) && $observation['material_type'] == 'NON RIPPING NON BLASTING' ? 'selected' : '' ?>>NON RIPPING NON BLASTING</option>
                                            <option value="FREEDIG" <?= isset($observation) && $observation['material_type'] == 'FREEDIG' ? 'selected' : '' ?>>FREEDIG</option>
                                            <option value="CLAY" <?= isset($observation) && $observation['material_type'] == 'CLAY' ? 'selected' : '' ?>>CLAY</option>
                                            <option value="MUD" <?= isset($observation) && $observation['material_type'] == 'MUD' ? 'selected' : '' ?>>MUD</option>
                                            <option value="PARTING COAL" <?= isset($observation) && $observation['material_type'] == 'PARTING COAL' ? 'selected' : '' ?>>PARTING COAL</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="front_width">Lebar Front (Meter):</label>
                                        <input type="number" class="form-control" id="front_width" name="front_width" value="<?= isset($observation) ? $observation['front_width'] : '' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit_condition">Kondisi Unit:</label>
                                        <input type="number" class="form-control" id="unit_condition" name="unit_condition" value="<?= isset($observation) ? $observation['unit_condition'] : '' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cycle_time">Cycle Time (Detik):</label>
                                        <input type="number" class="form-control" id="cycle_time" name="cycle_time" value="<?= isset($observation) ? $observation['cycle_time'] : '' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Front Condition Index (FCI):</label>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th width="60%">Parameter FCI</th>
                                                        <th class="text-center" width="20%">Baik</th>
                                                        <th class="text-center" width="20%">Buruk</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $fci_items = [
                                                        'metode_loading'         => 'Metode Loading',
                                                        'base_loader'            => 'Base Loader',
                                                        'tinggi_jenjang'         => 'Tinggi Jenjang',
                                                        'sudut_swing'            => 'Sudut Swing',
                                                        'surface_loading_point'  => 'Surface Loading Point',
                                                        'lebar_front'            => 'Lebar Front',
                                                        'fragmentasi_material'   => 'Fragmentasi Material',
                                                    ];
                                                    ?>

                                                    <?php foreach ($fci_items as $key => $label) : ?>
                                                        <tr>
                                                            <td><?= $label ?></td>
                                                            <td class="text-center align-middle">
                                                                <div class="icheck-primary d-inline">
                                                                    <input
                                                                        type="radio"
                                                                        id="<?= $key ?>_baik"
                                                                        name="fci[<?= $key ?>]"
                                                                        value="Baik"
                                                                        <?= isset($observation['fci'][$key]) && $observation['fci'][$key] == 'Baik' ? 'checked' : '' ?>
                                                                        required>
                                                                    <label for="<?= $key ?>_baik"></label>
                                                                </div>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <div class="icheck-danger d-inline">
                                                                    <input
                                                                        type="radio"
                                                                        id="<?= $key ?>_buruk"
                                                                        name="fci[<?= $key ?>]"
                                                                        value="Buruk"
                                                                        <?= isset($observation['fci'][$key]) && $observation['fci'][$key] == 'Buruk' ? 'checked' : '' ?>>
                                                                    <label for="<?= $key ?>_buruk"></label>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">Analisa & Evidence</h5>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="deviation_front">Deviasi Front:</label>
                                        <textarea class="form-control" id="deviation_front" name="deviation_front" rows="3"><?= isset($observation) ? $observation['deviation_front'] : '' ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="recommendation">Rekomendasi Perbaikan:</label>
                                        <textarea class="form-control" id="recommendation" name="recommendation" rows="3"><?= isset($observation) ? $observation['recommendation'] : '' ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="image_url">Evidence CycleTime (URL):</label>
                                        <input type="url" class="form-control" id="image_url" name="image_url" value="<?= isset($observation) ? $observation['image_url'] : '' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="evidence_loader_portrait">Evidence Loader Portrait (URL):</label>
                                        <input type="url" class="form-control" id="evidence_loader_portrait" name="evidence_loader_portrait" value="<?= isset($observation) ? $observation['evidence_loader_portrait'] : '' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="evidence_loader_landscape">Evidence Loader Landscape (URL):</label>
                                        <input type="url" class="form-control" id="evidence_loader_landscape" name="evidence_loader_landscape" value="<?= isset($observation) ? $observation['evidence_loader_landscape'] : '' ?>" required>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">Input Observasi Primary & Secondary</h5>
                                </div>

                                <!-- PRIMARY -->
                                <div class="col-md-6">
                                    <div class="card card-info">
                                        <div class="card-header">
                                            <h3 class="card-title">Input Observasi Primary</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="digging_time">Digging Time (Sec)</label>
                                                        <input type="number" step="0.01" class="form-control" id="digging_time" name="digging_time" value="<?= isset($observation) ? $observation['digging_time'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="swing_load_time">Swing Load Time (Sec)</label>
                                                        <input type="number" step="0.01" class="form-control" id="swing_load_time" name="swing_load_time" value="<?= isset($observation) ? $observation['swing_load_time'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="dump_time">Dump Time (Sec)</label>
                                                        <input type="number" step="0.01" class="form-control" id="dump_time" name="dump_time" value="<?= isset($observation) ? $observation['dump_time'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="swing_empty_primary">Swing Empty (Sec)</label>
                                                        <input type="number" step="0.01" class="form-control" id="swing_empty_primary" name="swing_empty_primary" value="<?= isset($observation) ? $observation['swing_empty_primary'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="avg_cycle_time_primary">Average Cycle Time (Sec)</label>
                                                        <input type="number" step="0.01" class="form-control" id="avg_cycle_time_primary" name="avg_cycle_time_primary" value="<?= isset($observation) ? $observation['avg_cycle_time_primary'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="duration_observation_primary">Duration Observation (Min)</label>
                                                        <input type="number" step="0.01" class="form-control" id="duration_observation_primary" name="duration_observation_primary" value="<?= isset($observation) ? $observation['duration_observation_primary'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="average_passing">Average Passing</label>
                                                        <input type="number" step="0.01" class="form-control" id="average_passing" name="average_passing" value="<?= isset($observation) ? $observation['average_passing'] : '' ?>">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECONDARY -->
                                <div class="col-md-6">
                                    <div class="card card-warning">
                                        <div class="card-header">
                                            <h3 class="card-title">Input Observasi Secondary</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="idle_time">Idle (Sec)</label>
                                                        <input type="number" step="0.01" class="form-control" id="idle_time" name="idle_time" value="<?= isset($observation) ? $observation['idle_time'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="positioning_time">Positioning (Sec)</label>
                                                        <input type="number" step="0.01" class="form-control" id="positioning_time" name="positioning_time" value="<?= isset($observation) ? $observation['positioning_time'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="wait_to_dump">Wait to Dump (Sec)</label>
                                                        <input type="number" step="0.01" class="form-control" id="wait_to_dump" name="wait_to_dump" value="<?= isset($observation) ? $observation['wait_to_dump'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="swing_empty_secondary">Swing Empty (Sec)</label>
                                                        <input type="number" step="0.01" class="form-control" id="swing_empty_secondary" name="swing_empty_secondary" value="<?= isset($observation) ? $observation['swing_empty_secondary'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="avg_cycle_time_secondary">Average Cycle Time (Sec)</label>
                                                        <input type="number" step="0.01" class="form-control" id="avg_cycle_time_secondary" name="avg_cycle_time_secondary" value="<?= isset($observation) ? $observation['avg_cycle_time_secondary'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="duration_observation_secondary">Duration Observation (Min)</label>
                                                        <input type="number" step="0.01" class="form-control" id="duration_observation_secondary" name="duration_observation_secondary" value="<?= isset($observation) ? $observation['duration_observation_secondary'] : '' ?>">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">Deviasi Observasi & Operator</h5>
                                </div>

                                <!-- DEVIASI FRONT -->
                                <div class="col-md-6">
                                    <div class="card card-danger">
                                        <div class="card-header">
                                            <h3 class="card-title">Deviasi Observasi - Deviasi Front</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="front_amblas">Front Amblas</label>
                                                        <input type="number" step="0.01" class="form-control" id="front_amblas" name="front_amblas" value="<?= isset($observation) ? $observation['front_amblas'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="front_licin">Front Licin</label>
                                                        <input type="number" step="0.01" class="form-control" id="front_licin" name="front_licin" value="<?= isset($observation) ? $observation['front_licin'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="front_menanjak">Front Menanjak</label>
                                                        <input type="number" step="0.01" class="form-control" id="front_menanjak" name="front_menanjak" value="<?= isset($observation) ? $observation['front_menanjak'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="front_berair">Front Berair</label>
                                                        <input type="number" step="0.01" class="form-control" id="front_berair" name="front_berair" value="<?= isset($observation) ? $observation['front_berair'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="front_perbaikan">Front Perbaikan</label>
                                                        <input type="number" step="0.01" class="form-control" id="front_perbaikan" name="front_perbaikan" value="<?= isset($observation) ? $observation['front_perbaikan'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="front_crowded">Front Crowded</label>
                                                        <input type="number" step="0.01" class="form-control" id="front_crowded" name="front_crowded" value="<?= isset($observation) ? $observation['front_crowded'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="front_berdebu">Front Berdebu</label>
                                                        <input type="number" step="0.01" class="form-control" id="front_berdebu" name="front_berdebu" value="<?= isset($observation) ? $observation['front_berdebu'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="front_sempit">Front Sempit</label>
                                                        <input type="number" step="0.01" class="form-control" id="front_sempit" name="front_sempit" value="<?= isset($observation) ? $observation['front_sempit'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="general_front">General Front</label>
                                                        <input type="number" step="0.01" class="form-control" id="general_front" name="general_front" value="<?= isset($observation) ? $observation['general_front'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="front_lembek">Front Lembek</label>
                                                        <input type="number" step="0.01" class="form-control" id="front_lembek" name="front_lembek" value="<?= isset($observation) ? $observation['front_lembek'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="front_undulating">Front Undulating</label>
                                                        <input type="number" step="0.01" class="form-control" id="front_undulating" name="front_undulating" value="<?= isset($observation) ? $observation['front_undulating'] : '' ?>">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- DEVIASI OPERATOR -->
                                <div class="col-md-6">
                                    <div class="card card-success">
                                        <div class="card-header">
                                            <h3 class="card-title">Deviasi Operator</h3>
                                        </div>
                                        <div class="card-body">

                                            <h6 class="font-weight-bold mb-3">Deviasi Skill</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="kombinasi_attch">Kombinasi Attch</label>
                                                        <input type="number" step="0.01" class="form-control" id="kombinasi_attch" name="kombinasi_attch" value="<?= isset($observation) ? $observation['kombinasi_attch'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="loading_method_dev">Loading Method</label>
                                                        <input type="number" step="0.01" class="form-control" id="loading_method_dev" name="loading_method_dev" value="<?= isset($observation) ? $observation['loading_method_dev'] : '' ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="font-weight-bold mb-3">Deviasi Knowledge</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="product_knowledge">Product Knowledge</label>
                                                        <input type="number" step="0.01" class="form-control" id="product_knowledge" name="product_knowledge" value="<?= isset($observation) ? $observation['product_knowledge'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="method_knowledge">Method</label>
                                                        <input type="number" step="0.01" class="form-control" id="method_knowledge" name="method_knowledge" value="<?= isset($observation) ? $observation['method_knowledge'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporting">Reporting</label>
                                                        <input type="number" step="0.01" class="form-control" id="reporting" name="reporting" value="<?= isset($observation) ? $observation['reporting'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="safety_operation">Safety Operation</label>
                                                        <input type="number" step="0.01" class="form-control" id="safety_operation" name="safety_operation" value="<?= isset($observation) ? $observation['safety_operation'] : '' ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="font-weight-bold mb-3">Deviasi Attitude OPT</h6>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="attitude_opt">Catatan Attitude OPT</label>
                                                        <textarea class="form-control" id="attitude_opt" name="attitude_opt" rows="3"><?= isset($observation) ? $observation['attitude_opt'] : '' ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- DEVIASI MATERIAL -->
                                <div class="col-md-6">
                                    <div class="card card-secondary">
                                        <div class="card-header">
                                            <h3 class="card-title">Deviasi Material</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="mat_blasting_keras">Mat Blasting Keras</label>
                                                        <input type="number" step="0.01" class="form-control" id="mat_blasting_keras" name="mat_blasting_keras" value="<?= isset($observation) ? $observation['mat_blasting_keras'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="mat_boulder_frag_besar">Mat Boulder / Frag Besar</label>
                                                        <input type="number" step="0.01" class="form-control" id="mat_boulder_frag_besar" name="mat_boulder_frag_besar" value="<?= isset($observation) ? $observation['mat_boulder_frag_besar'] : '' ?>">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- DEVIASI EQUIPMENT -->
                                <div class="col-md-6">
                                    <div class="card card-dark">
                                        <div class="card-header">
                                            <h3 class="card-title">Deviasi Equipment</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="under_truck">Under Truck</label>
                                                        <input type="number" step="0.01" class="form-control" id="under_truck" name="under_truck" value="<?= isset($observation) ? $observation['under_truck'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="unit_dm">Unit DM</label>
                                                        <input type="number" step="0.01" class="form-control" id="unit_dm" name="unit_dm" value="<?= isset($observation) ? $observation['unit_dm'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="matching_fleet">Matching Fleet</label>
                                                        <input type="number" step="0.01" class="form-control" id="matching_fleet" name="matching_fleet" value="<?= isset($observation) ? $observation['matching_fleet'] : '' ?>">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="exca_refueling">Exca Refueling</label>
                                                        <input type="number" step="0.01" class="form-control" id="exca_refueling" name="exca_refueling" value="<?= isset($observation) ? $observation['exca_refueling'] : '' ?>">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6 mb-2 mb-md-0">
                                        <a href="<?= base_url('observation') ?>" class="btn btn-default btn-block">Kembali</a>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                                            <?= isset($observation) ? 'Update' : 'Submit' ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="materialGuideModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Petunjuk Jenis Material</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body text-center p-1">
                    <img
                        src="<?= base_url('uploads/observation/materials_table.jpg') ?>"
                        class="img-fluid"
                        style="width:100%; height:auto;">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<script>
    $('#btnMaterialGuide').on('click', function() {
        $('#materialGuideModal').modal('show');
    });

    $(document).ready(function() {
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            allowClear: true,
            width: '100%'
        });

        const $nrp = $('#NRP');
        const $jobSite = $('#job_site');
        const $pitLocation = $('#pit_location');
        const $unitType = $('#unit_type');
        const $observationFields = $('#observation-fields');
        const $submitBtn = $('#submitBtn');

        // Simpan semua option pit
        const allPitOptions = [];
        $pitLocation.find('option').each(function() {
            const val = $(this).val();
            const text = $(this).text();
            const areaId = $(this).data('area-id');

            if (val !== '') {
                allPitOptions.push({
                    value: val,
                    text: text,
                    areaId: String(areaId)
                });
            }
        });

        // Simpan semua option unit
        const allUnitOptions = [];
        $unitType.find('option').each(function() {
            const val = $(this).val();
            const text = $(this).text();
            const plant = $(this).data('plant');

            if (val !== '') {
                allUnitOptions.push({
                    value: val,
                    text: text,
                    plant: String(plant)
                });
            }
        });

        function getSelectedJobSiteId() {
            return String($jobSite.val() || '');
        }

        function getSelectedJobSiteName() {
            return String($jobSite.find(':selected').data('name') || '');
        }

        function reloadPitOptions(selectedSiteId, selectedPit = '') {
            $pitLocation.empty();
            $pitLocation.append('<option value="">-- Pilih Lokasi Pit --</option>');

            if (!selectedSiteId) {
                $pitLocation.prop('disabled', true);
                $pitLocation.val('').trigger('change.select2');
                return;
            }

            const filtered = allPitOptions.filter(function(item) {
                return item.areaId === String(selectedSiteId);
            });

            filtered.forEach(function(item) {
                const isSelected = (String(selectedPit) === String(item.value)) ? 'selected' : '';
                $pitLocation.append(
                    `<option value="${item.value}" data-area-id="${item.areaId}" ${isSelected}>${item.text}</option>`
                );
            });

            $pitLocation.prop('disabled', false);
            $pitLocation.trigger('change.select2');
        }

        function reloadUnitOptions(selectedSiteName, selectedUnit = '') {
            $unitType.empty();
            $unitType.append('<option value="">-- Pilih Unit --</option>');

            if (!selectedSiteName) {
                $unitType.prop('disabled', true);
                $unitType.val('').trigger('change.select2');
                return;
            }

            const filtered = allUnitOptions.filter(function(item) {
                return item.plant === String(selectedSiteName);
            });

            filtered.forEach(function(item) {
                const isSelected = (String(selectedUnit) === String(item.value)) ? 'selected' : '';
                $unitType.append(
                    `<option value="${item.value}" data-plant="${item.plant}" ${isSelected}>${item.text}</option>`
                );
            });

            $unitType.prop('disabled', false);
            $unitType.trigger('change.select2');
        }

        function resetSiteDependentFields() {
            $pitLocation.empty()
                .append('<option value="">-- Pilih Lokasi Pit --</option>')
                .prop('disabled', true)
                .trigger('change.select2');

            $unitType.empty()
                .append('<option value="">-- Pilih Unit --</option>')
                .prop('disabled', true)
                .trigger('change.select2');
        }

        function toggleObservationFields() {
            const hasOperator = $nrp.val() !== '' && $nrp.val() !== null;

            $observationFields
                .find('input, select, textarea, button')
                .not('#submitBtn')
                .not('.keep-disabled')
                .prop('disabled', !hasOperator);

            $submitBtn.prop('disabled', !hasOperator);

            if (!hasOperator) {
                $jobSite.val('').trigger('change');
                resetSiteDependentFields();
            } else {
                const selectedSiteId = getSelectedJobSiteId();
                const selectedSiteName = getSelectedJobSiteName();

                reloadPitOptions(
                    selectedSiteId,
                    '<?= isset($observation) ? $observation["pit_location"] : "" ?>'
                );

                reloadUnitOptions(
                    selectedSiteName,
                    '<?= isset($observation) ? $observation["unit_type"] : "" ?>'
                );
            }

            $observationFields.find('.select2bs4').trigger('change.select2');
        }

        $jobSite.on('change', function() {
            const selectedSiteId = getSelectedJobSiteId();
            const selectedSiteName = getSelectedJobSiteName();

            reloadPitOptions(selectedSiteId);
            reloadUnitOptions(selectedSiteName);
        });

        $nrp.on('change', function() {
            toggleObservationFields();
        });

        // initial load
        reloadPitOptions(
            getSelectedJobSiteId(),
            '<?= isset($observation) ? $observation["pit_location"] : "" ?>'
        );

        reloadUnitOptions(
            getSelectedJobSiteName(),
            '<?= isset($observation) ? $observation["unit_type"] : "" ?>'
        );

        toggleObservationFields();
    });
</script>