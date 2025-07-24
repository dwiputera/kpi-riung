<?php if ($form_type == 'choose_method') : ?>
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Choose Assessment</h1>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    Performance
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <input type="hidden" name="type" value="performance">
                        <div class="row">
                            <div class="col-lg-8">
                                <select class="form-control form-control-lg w-100" name="tahun">
                                    <?php
                                    $currentYear = date('Y');
                                    for ($year = $currentYear + 5; $year >= 2020; $year--) : ?>
                                        <option value="<?= $year ?>" <?= $year == $currentYear ? 'selected' : '' ?>><?= $year ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <button type="submit" class="btn btn-primary w-100">Continue</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    Potential
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6"><a class="btn btn-primary btn-lg w-100" href="<?= base_url() ?>HAV/mapping/edit/<?= $NRP_hash ?>/<?= md5($methods[0]['id']) ?>"><?= $methods[0]['name'] ?></a></div>
                        <div class="col-lg-6"><a class="btn btn-primary btn-lg w-100" href="<?= base_url() ?>HAV/mapping/edit/<?= $NRP_hash ?>/<?= md5($methods[1]['id']) ?>"><?= $methods[1]['name'] ?></a></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php elseif ($form_type == 'edit_performance') : ?>
    <br>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-tabs">
                <div class="card-body">
                    <form action="<?= base_url() ?>HAV/mapping/submit/<?= $NRP_hash ?>" method="POST" id="form">
                        <input type="hidden" name="type" value="performance">
                        <input type="hidden" name="id_hash" value="<?= $comp_pstn_assess ? md5($comp_pstn_assess['id']) : '' ?>">
                        <input type="hidden" name="tahun" value="<?= $this->input->post('tahun') ?>">
                        <table id="datatable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tahun</th>
                                    <th>Score Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= $this->input->post('tahun') ?></td>
                                    <td><input type="text" name="score" value="<?= $comp_pstn_assess['score'] ?? null ?>"></td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <div class="row">
                            <div class="col-lg-3">
                                <button type="submit" name="proceed" value="N" class="btn btn-default w-100 show-overlay-full">Cancel</button>
                            </div>
                            <div class="col-lg-3">
                                <button type="submit" name="proceed" value="D" class="btn btn-danger w-100" onclick="return confirm('are you sure?')">Delete</button>
                            </div>
                            <div class="col-lg-6">
                                <button type="submit" id="submitBtn" class="btn btn-info w-100 show-overlay-full">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php else : ?>
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Human Asset Value Edit: <strong>[<?= $employee['NRP'] ?>] <?= $employee['FullName'] ?></strong></h1>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-tabs">
                <div class="card-body">
                    <form action="<?= base_url() ?>HAV/mapping/submit/<?= $NRP_hash ?>/<?= $method_id_hash ?>" method="post" id="form">
                        <input type="hidden" name="type" value="potential">
                        <input type="hidden" name="NRP" value="<?= $employee['NRP'] ?>">
                        <input type="hidden" name="method_id" value="<?= $employee['method_id'] ?>">
                        <table id="datatable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Competency</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Method</td>
                                    <td><?= $employee['method'] ?></td>
                                </tr>
                                <tr>
                                    <td>Vendor</td>
                                    <td contenteditable="true" name="vendor"><?= $employee['vendor'] ?? "" ?></td>
                                </tr>
                                <tr>
                                    <td>Tahun</td>
                                    <td contenteditable="true" name="tahun"><?= $employee['tahun'] ?? "" ?></td>
                                </tr>
                                <tr>
                                    <td>Assessment Score</td>
                                    <td contenteditable="true" name="assess_score"><?= $employee['assess_score'] ?? "" ?></td>
                                </tr>
                                <tr>
                                    <td>Recommendation</td>
                                    <td contenteditable="true" name="recommendation"><?= $employee['recommendation'] ?? "" ?></td>
                                </tr>
                                <?php $employee['score'] = array_column($employee['score'], null, 'comp_lvl_id'); ?>
                                <?php foreach ($comp_lvls as $i_cl => $cl_i) : ?>
                                    <tr>
                                        <td><?= $cl_i['name'] ?></td>
                                        <td contenteditable="true" name="score[<?= $cl_i['id'] ?>]"><?= $employee['score'][$cl_i['id']]['score'] ?? 0 ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <br>
                        <div class="row">
                            <div class="col-lg-3">
                                <button type="submit" name="proceed" value="N" class="btn btn-default w-100 show-overlay-full">Cancel</button>
                            </div>
                            <div class="col-lg-3">
                                <button type="submit" name="proceed" value="D" class="btn btn-danger w-100" onclick="return confirm('are you sure?')">Delete</button>
                            </div>
                            <div class="col-lg-6">
                                <input type="hidden" name="target_json" id="target_json">
                                <button type="submit" id="submitBtn" class="btn btn-info w-100 show-overlay-full">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        $('form').on('submit', function() {
            const data = {};
            $('[contenteditable][name]').each(function() {
                const name = $(this).attr('name');
                const value = $(this).text().trim();

                // Untuk input bertipe score[25]
                if (name.startsWith('score[')) {
                    const match = name.match(/^score\[(\d+)\]$/);
                    if (match) {
                        const id = match[1];
                        if (!data.score) data.score = {};
                        data.score[id] = value;
                    }
                } else {
                    data[name] = value;
                }
            });

            $('[name="target_json"]').val(JSON.stringify(data));
        });
    </script>
<?php endif; ?>