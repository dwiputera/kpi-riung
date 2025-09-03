<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Position Dictionary of Competency</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Dictionary of Competency</h3>
            </div>
            <!-- /.card-header -->
            <form action="<?= base_url("comp_settings/position_matrix/dictionary_submit") ?>" method="post">
                <div class="card-body">
                    <table id="datatable_training" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Matrix Point</th>
                                <th>Name</th>
                                <th>Definition</th>
                                <th>Level</th>
                                <th>Proficiency</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($dictionaries as $dict) : ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= $dict['oalp_name'] ?></td>
                                    <td><?= $dict['name'] ?></td>
                                    <td contenteditable="true" data-id="<?= $dict['id'] ?>" data-column="definition"><?= $dict['definition'] ?></td>
                                    <td>1</td>
                                    <td contenteditable="true" data-id="<?= $dict['id'] ?>" data-column="level_1"><?= $dict['level_1'] ?></td>
                                </tr>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= $dict['oalp_name'] ?></td>
                                    <td><?= $dict['name'] ?></td>
                                    <td></td>
                                    <td>2</td>
                                    <td contenteditable="true" data-id="<?= $dict['id'] ?>" data-column="level_2"><?= $dict['level_2'] ?></td>
                                </tr>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= $dict['oalp_name'] ?></td>
                                    <td><?= $dict['name'] ?></td>
                                    <td></td>
                                    <td>3</td>
                                    <td contenteditable="true" data-id="<?= $dict['id'] ?>" data-column="level_3"><?= $dict['level_3'] ?></td>
                                </tr>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= $dict['oalp_name'] ?></td>
                                    <td><?= $dict['name'] ?></td>
                                    <td></td>
                                    <td>4</td>
                                    <td contenteditable="true" data-id="<?= $dict['id'] ?>" data-column="level_4"><?= $dict['level_4'] ?></td>
                                </tr>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= $dict['oalp_name'] ?></td>
                                    <td><?= $dict['name'] ?></td>
                                    <td></td>
                                    <td>5</td>
                                    <td contenteditable="true" data-id="<?= $dict['id'] ?>" data-column="level_5"><?= $dict['level_5'] ?></td>
                                </tr>
                                <?php $i++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-lg-4">
                            <button type="submit" name="proceed" value="N" class="btn btn-default w-100 show-overlay-full">Cancel</button>
                        </div>
                        <div class="col-lg-8">
                            <input type="hidden" name="target_json" id="target_json">
                            <button type="submit" id="submitBtn" class="btn btn-info w-100 show-overlay-full">Submit</button>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </form>
        </div>
        <!-- /.card -->
    </div><!-- /.container-fluid -->
</section>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    setupFilterableDatatable($('.datatable-filter-column'));

    $('form').on('submit', function(e) {
        const data = {};

        $('.datatable-filter-column').each(function() {
            const table = $(this).DataTable();

            table.rows().every(function() {
                const $row = $(this.node());

                $row.find('td[contenteditable="true"]').each(function() {
                    const $td = $(this);
                    const id = $td.data('id');
                    const column = $td.data('column');
                    const value = $td.text().trim();

                    if (id && column) {
                        if (!data[id]) data[id] = {};
                        data[id][column] = value;
                    }
                });
            });
        });

        // Cari input `target_json` di dalam form yang sedang disubmit
        $(this).find('[name="target_json"]').val(JSON.stringify(data));
    });
</script>