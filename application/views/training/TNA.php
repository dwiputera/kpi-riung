<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">TNA</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- general form elements -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Upload TNA</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="<?= base_url() ?>training/TNA/do_upload" method="post" enctype="multipart/form-data">
                <div class="card-body">
                    <div class="form-group">
                        <label>Tahun:</label>
                        <div class="input-group date" id="year" data-target-input="nearest">
                            <input type="text" class="form-control datetimepicker-input" data-target="#year" value="<?= date("YYYY") ?>" name="year" />
                            <div class="input-group-append" data-target="#year" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <?php echo form_error('year', '<div class="text-danger small">', '</div>'); ?>
                    </div>
                    <div class="form-group">
                        <!-- <label for="userfile">File input</label> -->
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="userfile" name="userfile" accept=".xls,.xlsx,.csv">>
                                <label class="custom-file-label" for="userfile">Choose file</label>
                            </div>
                            <div class="input-group-append">
                                <button type="submit" class="input-group-text">Upload</button>
                            </div>
                        </div>
                        <?php echo form_error('userfile', '<div class="text-danger small">', '</div>'); ?>
                    </div>
                </div>
                <!-- /.card-body -->
            </form>
        </div>
        <!-- /.card -->

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Uploaded TNA</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Uploaded At</th>
                            <th>Uploaded By</th>
                            <th>Year</th>
                            <th>File</th>
                            <th>Download</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($tnas as $tna) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= date("F j, Y, g:i a", strtotime($tna['uploaded_at'])) ?></td>
                                <td><?= $tna['uploaded_by'] ?></td>
                                <td><?= $tna['year'] ?></td>
                                <td><?= $tna['file_name'] ?></td>
                                <td>
                                    <a href="<?= base_url(); ?>training/TNA/download/<?= md5($tna['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= base_url('training/tna/delete/' . md5($tna['id'])); ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this file?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<!-- Page specific script -->
<script>
    $(function() {
        $("#datatable").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#datatable_wrapper .col-md-6:eq(0)');
    });

    $(function() {
        bsCustomFileInput.init();
    });

    //Date picker
    $('#year').datetimepicker({
        format: 'YYYY', // Only year
        viewMode: 'years',
    });
</script>