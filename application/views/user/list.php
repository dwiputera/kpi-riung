<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">User Management</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Upload Excel Update Users</h3>
            </div>
            <form action="<?= base_url('user/upload_excel') ?>" method="post" enctype="multipart/form-data">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-9">
                            <label>File Excel (.xls, .xlsx, .csv)</label>
                            <input type="file" name="excel_file" class="form-control" accept=".xls,.xlsx,.csv" required>
                            <small class="text-muted">
                                Pers.No. = NRP. Jika sudah ada akan update, jika belum ada akan insert.
                            </small>
                        </div>
                        <div class="col-lg-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-upload"></i> Upload Excel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Users List</h3>
            </div>

            <form action="<?= base_url('user/submit') ?>" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">
                <input type="hidden" name="mode" value="simple">

                <div class="card-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-warning" id="btn-enable-edit">
                            <i class="fas fa-edit"></i> Edit Mode
                        </button>
                        <button type="button" class="btn btn-secondary" id="btn-disable-edit" style="display:none;">
                            <i class="fas fa-lock"></i> Lock Mode
                        </button>

                        <a href="<?= base_url('user/list_advanced') ?>" class="btn btn-info ml-2">
                            <i class="fas fa-table"></i> Edit Advanced
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-bordered table-striped table-sm datatable-filter-column">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NRP</th>
                                    <th>FULL NAME</th>
                                    <th>BIRTH DATE</th>
                                    <th>PASSWORD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u) : ?>
                                    <tr data-id="<?= $u['id'] ?>">
                                        <td><?= $u['id'] ?></td>
                                        <td data-name="NRP"><?= htmlspecialchars($u['NRP'] ?? '') ?></td>
                                        <td data-name="FullName"><?= htmlspecialchars($u['FullName'] ?? '') ?></td>
                                        <td data-name="BirthDate"><?= htmlspecialchars($u['BirthDate'] ?? '') ?></td>
                                        <td data-name="password"><?= !empty($u['password']) ? '********' : '' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-paper-plane"></i> Submit Update
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>

<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    const editableFields = ['NRP', 'FullName', 'BirthDate', 'password'];
    let isEditMode = false;

    function applyEditModeToVisibleRows() {
        $('#datatable tbody tr').each(function() {
            $(this).find('td[data-name]').each(function() {
                const field = $(this).data('name');

                if (editableFields.includes(field)) {
                    $(this).attr('contenteditable', isEditMode ? 'true' : 'false');
                    $(this).toggleClass('bg-light', isEditMode);
                }
            });
        });

        if (isEditMode) {
            $('#btn-enable-edit').hide();
            $('#btn-disable-edit').show();
        } else {
            $('#btn-enable-edit').show();
            $('#btn-disable-edit').hide();
        }
    }

    function setEditMode(enabled) {
        isEditMode = enabled;
        applyEditModeToVisibleRows();
    }

    function collectPayload() {
        const updates = [];

        $('#datatable tbody tr').each(function() {
            const $tr = $(this);
            const row = {
                id: $tr.data('id')
            };

            $tr.find('td[data-name]').each(function() {
                const field = $(this).data('name');
                row[field] = $(this).text().trim();
            });

            updates.push(row);
        });

        return {
            updates: updates,
            creates: [],
            deletes: []
        };
    }

    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));
        setEditMode(false);

        $('#btn-enable-edit').on('click', function() {
            setEditMode(true);
        });

        $('#btn-disable-edit').on('click', function() {
            setEditMode(false);
        });

        // Penting: setiap DataTable redraw / pindah page / search / sort
        $('#datatable').on('draw.dt', function() {
            applyEditModeToVisibleRows();
        });

        $('#data-form').on('submit', function() {
            $('#json_data').val(JSON.stringify(collectPayload()));
        });
    });
</script>