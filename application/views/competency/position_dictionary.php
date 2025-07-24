<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Dictionary of Competency: <strong><?= $position['name'] ?></strong></h1>
    </div><!-- /.container-fluid -->
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
                                        <th colspan="7" class="text-center bg-warning"><?= $dict_i['name'] ?></th>
                                    </tr>
                                    <tr>
                                        <td colspan="7">
                                            Definisi: <br>
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
                                        <td class="text-center bg-warning">1<br>PEMULA (ENTRY)</td>
                                        <td class="text-center bg-warning">2<br>DASAR (BASIC)</td>
                                        <td class="text-center bg-warning">3<br>MAMPU (INTERMEDIATE)</td>
                                        <td class="text-center bg-warning">4<br>CAKAP (ADVANCED)</td>
                                        <td class="text-center bg-warning">5<br>AHLI (EXPERT)</td>
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