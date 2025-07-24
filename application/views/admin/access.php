<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Access Management</title>
    <!-- AdminLTE 3 CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/adminlte3/plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/adminlte3/dist/css/adminlte.min.css') ?>">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper" style="margin-left: 0;">

            <!-- Content Header -->
            <section class="content-header">
                <div class="container-fluid">
                    <h1>Access Management</h1>
                </div>
            </section>

            <!-- Main content -->
            <section class="content container-fluid">

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
                <?php endif; ?>

                <form action="<?= base_url('admin/access/save') ?>" method="post">

                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Role \ Menu</th>
                                <?php foreach ($menus as $menu): ?>
                                    <th><?= htmlspecialchars($menu->menu_name) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($role->role_name) ?></strong></td>
                                    <?php foreach ($menus as $menu):
                                        $perm = isset($perm_lookup[$role->id][$menu->id]) ? $perm_lookup[$role->id][$menu->id] : null;
                                    ?>
                                        <td style="white-space: nowrap;">
                                            <input type="hidden" name="roles[]" value="<?= $role->id ?>">
                                            <input type="hidden" name="menus[]" value="<?= $menu->id ?>">

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="perm_<?= $role->id ?>_<?= $menu->id ?>_create" name="perm_<?= $role->id ?>_<?= $menu->id ?>_create" value="1" <?= ($perm && $perm->can_create) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="perm_<?= $role->id ?>_<?= $menu->id ?>_create" title="Create">C</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="perm_<?= $role->id ?>_<?= $menu->id ?>_read" name="perm_<?= $role->id ?>_<?= $menu->id ?>_read" value="1" <?= ($perm && $perm->can_read) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="perm_<?= $role->id ?>_<?= $menu->id ?>_read" title="Read">R</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="perm_<?= $role->id ?>_<?= $menu->id ?>_update" name="perm_<?= $role->id ?>_<?= $menu->id ?>_update" value="1" <?= ($perm && $perm->can_update) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="perm_<?= $role->id ?>_<?= $menu->id ?>_update" title="Update">U</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="perm_<?= $role->id ?>_<?= $menu->id ?>_delete" name="perm_<?= $role->id ?>_<?= $menu->id ?>_delete" value="1" <?= ($perm && $perm->can_delete) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="perm_<?= $role->id ?>_<?= $menu->id ?>_delete" title="Delete">D</label>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <button type="submit" class="btn btn-primary mt-3">Save Permissions</button>
                </form>

            </section>
        </div>
    </div>

    <!-- AdminLTE 3 JS -->
    <script src="<?= base_url('assets/adminlte3/plugins/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/adminlte3/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/adminlte3/dist/js/adminlte.min.js') ?>"></script>
</body>

</html>