<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Choose Matrix Point</h3>
            </div>
            <form id="data-form" action="<?= base_url() ?><?= $admin ? 'comp_settings' : 'competency' ?>/position_matrix" method="post">
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="" class="table table-bordered table-striped datatable-filter-column" data-filter-columns="4:multiple,5,6">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>No</th>
                                <th>Matrix Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($matrix_points as $i_mp => $mp_i) : ?>
                                <tr>
                                    <td class="text-center"><input type="checkbox" name="matrix_points[]" id="" value="<?= $mp_i['id'] ?>"></td>
                                    <td><?= $i++ ?></td>
                                    <td><?= $mp_i['name'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="submit" class="w-100 btn btn-info show-overlay-full">
                        Continue
                    </button>
                </div>
            </form>
        </div>
        <!-- /.card -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });
    });
</script>