<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Level</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <?php if ($levels) : ?>
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Levels</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <!-- <a data-toggle="modal" data-target="#addLevel" href="#addLevelModal">
                        <strong class="btn btn-primary w-100">+ add</strong>
                    </a><br><br> -->

                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>LEVEL NAME</th>
                                <!-- <th>ACTION</th> -->
                            </tr>
                            <tr>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <!-- <th></th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($levels as $level) : ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= $level['name'] ?></td>
                                    <!-- <td><a href="<?= base_url() ?>organization_settings/level/delete/<?= md5($level['id']) ?>" class="btn btn-danger btn-xs" onclick="return confirm('are you sure?')">delete</a></td> -->
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        <?php endif; ?>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<!-- Add Nav Modal -->
<div class="modal fade" id="addLevel" tabindex="-1" role="dialog" aria-labelledby="addLevelLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= site_url('organization_settings/level/add/') ?>" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Position</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="area">Area</label>
                        <select class="form-control select2" id="area" name="area" required>
                            <option value="">-- Choose Area --</option>
                            <?php foreach ($areas as $i_area => $area_i): ?>
                                <option value="<?= md5($area_i['id']) ?>"><?= $area_i['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="parent_lvl">Parent Level</label>
                        <select class="form-control select2" id="parent_lvl" name="parent_lvl" required>
                            <option value="">-- Choose Level --</option>
                            <option value="0">None</option>
                            <?php foreach ($levels as $i_lvl => $lvl_i): ?>
                                <option value="<?= md5($lvl_i['id']) ?>"><?= $lvl_i['oa_name'] ?> | <?= $lvl_i['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="levelName">Level Name</label>
                        <input type="text" class="form-control" name="level_name" id="levelName" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Page specific script -->
<script>
    $(function() {
        $("#datatable").DataTable({
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
            lengthChange: true,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            scrollX: true,
            orderCellsTop: true, // 👈 IMPORTANT for multiple thead rows
            fixedHeader: true, // Optional: keeps header visible on scroll
        }).buttons().container().appendTo('#datatable_wrapper .col-md-6:eq(0)');
    });

    $('#datatable thead tr:eq(1) th').each(function(i) {
        let input = $(this).find('input');
        if (input.length) {
            $(input).on('keyup change', function() {
                if ($('#datatable').DataTable().column(i).search() !== this.value) {
                    $('#datatable').DataTable()
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        }
    });

    $(document).ready(function() {
        $('#addLevel .select2').select2({
            dropdownParent: $('#addLevel')
        });
    });
</script>