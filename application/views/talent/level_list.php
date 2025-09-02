<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Choose Level</h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <?php if ($levels) : ?>
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Levels</h3>
                </div>
                <div class="card-body">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>LEVEL NAME</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($levels as $level) : ?>
                                <tr data-id="<?= md5($level['id']) ?>" style="cursor:pointer;">
                                    <td class="p-2"><?= $i++ ?></td>
                                    <td><?= $level['name'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    /* Hover row khusus biar lebih tebal dari bawaan DataTables */
    #datatable tbody tr:hover {
        background-color: #d0e3ff !important;
        /* warna biru muda */
    }
</style>

<script>
    $(function() {
        let table = $("#datatable").DataTable({
            autoWidth: false,
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
            lengthChange: true,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            scrollX: true,
            orderCellsTop: true,
            fixedHeader: true,
        }).buttons().container().appendTo('#datatable_wrapper .col-md-6:eq(0)');

        // Filter kolom
        $('#datatable thead tr:eq(1) th').each(function(i) {
            let input = $(this).find('input');
            if (input.length) {
                $(input).on('keyup change', function() {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            }
        });

        // Klik row
        $('#datatable tbody').on('click', 'tr', function() {
            let id = $(this).data('id');
            if (id) {
                window.location.href = "<?= site_url('talent/candidate_list/') ?>" + id;
            }
        });
    });
</script>