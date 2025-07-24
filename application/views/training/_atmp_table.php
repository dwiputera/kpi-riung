<input type="hidden" name="year" value="<?= $year ?>">
<input type="hidden" name="proceed" value="Y">
<input type="hidden" name="json_data" id="json_data">

<table id="datatable" class="table table-bordered table-striped datatable-filter-column" data-filter-columns="1:checkbox,2:multiple,3:multiple">
    <thead>
        <tr>
            <th>No</th>
            <th>MTS</th>
            <th>MONTH</th>
            <th>DEPARTEMEN PENGAMPU</th>
            <th>NAMA PROGRAM</th>
            <th>BATCH</th>
            <th>JENIS KOMPETENSI</th>
            <th>SASARAN KOMPETENSI</th>
            <th>LEVEL KOMPETENSI</th>
            <th>TARGET PESERTA</th>
            <th>STAFF/NONSTAFF</th>
            <th>KATEGORI PROGRAM</th>
            <th>FASILITATOR</th>
            <th>NAMA PENYELENGGARA/FASILITATOR</th>
            <th>TEMPAT</th>
            <th>ONLINE / OFFLINE</th>
            <th>START DATE</th>
            <th>END DATE</th>
            <th>DAYS</th>
            <th>HOURS</th>
            <th>TOTAL HOURS</th>
            <th>RMHO</th>
            <th>RMIP</th>
            <th>REBH</th>
            <th>RMTU</th>
            <th>RMTS</th>
            <th>RMGM</th>
            <th>RHML</th>
            <th>TOTAL JOBSITE</th>
            <th>TOTAL PARTICIPANTS</th>
            <th>GRAND TOTAL HOURS</th>
            <th>BIAYA PELATIHAN/ ORANG</th>
            <th>BIAYA PELATIHAN</th>
            <th>TRAINING KIT/ORANG</th>
            <th>TRAINING KIT</th>
            <th>NAMA HOTEL</th>
            <th>BIAYA PENGINAPAN /ORANG</th>
            <th>BIAYA PENGINAPAN</th>
            <th>MEETING PACKAGE/ORANG</th>
            <th>MEETING PACKAGE</th>
            <th>MAKAN/ORANG</th>
            <th>MAKAN</th>
            <th>SNACK/ORANG</th>
            <th>SNACK</th>
            <th>TIKET/ORANG</th>
            <th>TIKET</th>
            <th>GRAND TOTAL</th>
            <th>KETERANGAN</th>
        </tr>
    </thead>
    <?php
    function renderInput($type, $name, $value, $hash, $class = '')
    {
        return "<input type=\"{$type}\" class=\"form-control form-control-sm {$class}\" data-trn_id_hash=\"{$hash}\" data-name=\"{$name}\" value=\"{$value}\">";
    }
    function renderCell($name, $value, $hash)
    {
        return "<td contenteditable=\"true\" class=\"editable-cell\" data-trn_id_hash=\"{$hash}\" data-name=\"{$name}\">{$value}</td>";
    }
    ?>

    <tbody>
        <?php foreach ($trainings as $i => $training): ?>
            <?php $hash = md5($training['id']); ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td class="text-center">
                    <input type="checkbox" class="row-checkbox form-check-input" data-trn_id_hash="<?= $hash ?>" data-name="mts" <?= $training['mts'] == 'Y' ? 'checked' : '' ?>>
                </td>
                <?= renderCell('month', $training['month'], $hash) ?>
                <?= renderCell('departemen_pengampu', $training['departemen_pengampu'], $hash) ?>
                <?= renderCell('nama_program', $training['nama_program'], $hash) ?>
                <td><?= renderInput('number', 'batch', $training['batch'], $hash) ?></td>
                <?= renderCell('jenis_kompetensi', $training['jenis_kompetensi'], $hash) ?>
                <?= renderCell('sasaran_kompetensi', $training['sasaran_kompetensi'], $hash) ?>
                <td><?= renderInput('number', 'level_kompetensi', $training['level_kompetensi'], $hash) ?></td>
                <?= renderCell('target_peserta', $training['target_peserta'], $hash) ?>
                <?= renderCell('staff_nonstaff', $training['staff_nonstaff'], $hash) ?>
                <?= renderCell('kategori_program', $training['kategori_program'], $hash) ?>
                <?= renderCell('fasilitator', $training['fasilitator'], $hash) ?>
                <?= renderCell('nama_penyelenggara_fasilitator', $training['nama_penyelenggara_fasilitator'], $hash) ?>
                <?= renderCell('tempat', $training['tempat'], $hash) ?>
                <?= renderCell('online_offline', $training['online_offline'], $hash) ?>
                <td><?= renderInput('date', 'start_date', $training['start_date'], $hash) ?></td>
                <td><?= renderInput('date', 'end_date', $training['end_date'], $hash) ?></td>

                <?php
                $numberFields = [
                    'days',
                    'hours',
                    'total_hours',
                    'rmho',
                    'rmip',
                    'rebh',
                    'rmtu',
                    'rmts',
                    'rmgm',
                    'rhml',
                    'total_jobsite',
                    'total_participants',
                    'grand_total_hours',
                    'biaya_pelatihan_per_orang',
                    'biaya_pelatihan',
                    'training_kit_per_orang',
                    'training_kit',
                    'biaya_penginapan_per_orang',
                    'biaya_penginapan',
                    'meeting_package_per_orang',
                    'meeting_package',
                    'makan_per_orang',
                    'makan',
                    'snack_per_orang',
                    'snack',
                    'tiket_per_orang',
                    'tiket',
                    'grand_total'
                ];
                foreach ($numberFields as $field) {
                    echo "<td>" . renderInput('number', $field, $training[$field], $hash) . "</td>";
                }
                ?>
                <?= renderCell('nama_hotel', $training['nama_hotel'], $hash) ?>
                <?= renderCell('keterangan', $training['keterangan'], $hash) ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>