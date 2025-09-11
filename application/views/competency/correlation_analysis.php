<?php
$mp_1_c_names = array_column($mp_1_comp, 'name');
$mp_2_c_names = array_column($mp_2_comp, 'name');
$color = [
    60 => 'primary',
    40 => 'success',
    20 => 'warning',
    0 => 'danger',
];
?>

<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-tabs">
            <div class="card-header">
                <h3 class="card-title mt-2">Correlation Matrix</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">
                                        ACUAN: <?= $mp_1['name'] ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i_1 = 1;
                                $exist_number_1 = 0;
                                ?>
                                <?php foreach ($mp_1_comp as $i_mp_1_c => $mp_1_c_i) : ?>
                                    <?php
                                    $exist = in_array($mp_1_c_i['name'], $mp_2_c_names) ? true : false;
                                    $class = $exist ? 'class="bg-success"' : '';
                                    if ($exist) $exist_number_1++;
                                    ?>
                                    <tr <?= $class ?>>
                                        <td><?= $i_1++ ?></td>
                                        <td><?= $mp_1_c_i['name'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">
                                        PEMBANDING: <?= $mp_2['name'] ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i_2 = 1;
                                $exist_number_2 = 0;
                                ?>
                                <?php foreach ($mp_2_comp as $i_mp_2_c => $mp_2_c_i) : ?>
                                    <?php
                                    $exist = in_array($mp_2_c_i['name'], $mp_1_c_names) ? true : false;
                                    $class = $exist ? 'class="bg-success"' : '';
                                    if ($exist) $exist_number_2++;
                                    ?>
                                    <tr <?= $class ?>>
                                        <td><?= $i_2++ ?></td>
                                        <td><?= $mp_2_c_i['name'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td>Jumlah Korelasi</td>
                            <td><?= $exist_number_1 ?> / <?= $i_2 - 1 ?></td>
                        </tr>
                        <tr>
                            <td>Tingkat Korelasi</td>
                            <?php $tingkat_korelasi = number_format($exist_number_1 * 100 / ($i_2 - 1), 2); ?>
                            <?php foreach ($color as $i_color => $color_i) {
                                if ($tingkat_korelasi > $i_color) {
                                    $bg_color = $color_i;
                                    break;
                                }
                                $bg_color = $color[0];
                            } ?>
                            <td class="bg-<?= $bg_color ?>"><?= $tingkat_korelasi ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>