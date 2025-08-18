<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dictionary of Competency</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs rotate-tabs text-center" id="custom-tabs-tab" role="tablist">
                    <li class="nav-item" style="
                        width: 60px;           /* atur lebar kolom tab */
                        height: 200px;         /* atur tinggi tab */
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <span><strong class="rotate-text" style="
                            display: inline-block;       /* penting biar transform bisa jalan */
                            transform: rotate(-90deg);   /* -90 = counterclockwise */
                            transform-origin: center;    /* pivotnya di tengah */
                            white-space: nowrap;         /* jangan pecah baris */
                            color: black;
                        ">PERILAKU</strong></span>
                    </li>
                    <?php foreach ($dictionaries as $i_dict => $dict_i) : ?>
                        <?php $activeClass = $i_dict == 0 ? 'active' : ''; ?>
                        <?php if ($dict_i['type'] == "behavior") continue; ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeClass ?>" id="custom-tabs-<?= md5($dict_i['id']) ?>-tab"
                                data-toggle="pill" href="#custom-tabs-<?= md5($dict_i['id']) ?>"
                                role="tab" aria-controls="custom-tabs-<?= md5($dict_i['id']) ?>"
                                aria-selected="true">
                                <span class="rotate-text"><?= $dict_i['name'] ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="nav-item" style="
                        width: 60px;           /* atur lebar kolom tab */
                        height: 200px;         /* atur tinggi tab */
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <span><strong class="rotate-text" style="
                            display: inline-block;       /* penting biar transform bisa jalan */
                            transform: rotate(-90deg);   /* -90 = counterclockwise */
                            transform-origin: center;    /* pivotnya di tengah */
                            white-space: nowrap;         /* jangan pecah baris */
                            color: black;
                        ">PERAN</strong></span>
                    </li>
                    <?php foreach ($dictionaries as $i_dict => $dict_i) : ?>
                        <?php $activeClass = $i_dict == 0 ? 'active' : ''; ?>
                        <?php if ($dict_i['type'] == "role") continue; ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeClass ?>" id="custom-tabs-<?= md5($dict_i['id']) ?>-tab"
                                data-toggle="pill" href="#custom-tabs-<?= md5($dict_i['id']) ?>"
                                role="tab" aria-controls="custom-tabs-<?= md5($dict_i['id']) ?>"
                                aria-selected="true">
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
                        <div class="tab-pane fade <?= $activeClass ?>" id="custom-tabs-<?= md5($dict_i['id']) ?>" role="tabpanel" aria-labelledby="custom-tabs-<?= md5($dict_i['id']) ?>-tab">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th colspan="7" class="text-center bg-warning"><?= $dict_i['name'] ?>(<?= $dict_i['code'] ?>)</th>
                                    </tr>
                                    <tr>
                                        <td colspan="7">
                                            Definisi: <br>
                                            <?= $dict_i['definisi'] ?>
                                            <?= $dict_i['keterangan'] ? "<br><br>Keterangan:<br>" : '' ?>
                                            <?= $dict_i['keterangan'] ?? '' ?>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td rowspan="2" class="bg-warning">
                                            DIMENSI
                                        </td>
                                        <td colspan="6" class="text-center bg-warning">
                                            INDIKATOR PERILAKU
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center bg-warning">1<br>PEMULA (ENTRY)</td>
                                        <td class="text-center bg-warning">2<br>DASAR (BASIC)</td>
                                        <td class="text-center bg-warning">3<br>MAMPU (INTERMEDIATE)</td>
                                        <td class="text-center bg-warning">4<br>CAKAP (ADVANCED)</td>
                                        <td class="text-center bg-warning">5<br>AHLI (EXPERT)</td>
                                        <td class="text-center bg-warning">6<br>MASTERY</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="2" class="bg-secondary">
                                            <?= $dict_i['dimension_1'] ?>
                                        </td>
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
                                    <tr>
                                        <td rowspan="2" class="bg-secondary">
                                            <?= $dict_i['dimension_2'] ?>
                                        </td>
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
                                    <?php if ($dict_i['dimension_3']) : ?>
                                        <tr>
                                            <td rowspan="2" class="bg-secondary">
                                                <?= $dict_i['dimension_3'] ?>
                                            </td>
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
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

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
        /* Ubah dari auto ke nilai tetap */
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