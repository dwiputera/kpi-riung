<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?= isset($title) ? $title : 'User Access'; ?></h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">

        <div class="card card-primary card-outline">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="userAccessTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="roles-tab" data-toggle="pill" href="#roles-pane" role="tab">Roles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="role-menu-tab" data-toggle="pill" href="#role-menu-pane" role="tab">Role Menu Access</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="user-role-tab" data-toggle="pill" href="#user-role-pane" role="tab">User Roles</a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="userAccessTabContent">

                    <div class="tab-pane fade show active" id="roles-pane" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Form Role</h3>
                                    </div>
                                    <div class="card-body">
                                        <form id="form-role">
                                            <input type="hidden" name="id" id="role_id">

                                            <div class="form-group">
                                                <label>Role Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" id="role_name" class="form-control">
                                            </div>

                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea name="description" id="role_description" class="form-control" rows="3"></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save"></i> Save
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm" id="btn-reset-role">
                                                <i class="fas fa-sync"></i> Reset
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">List Roles</h3>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-striped" id="table_roles">
                                            <thead>
                                                <tr>
                                                    <th width="60">ID</th>
                                                    <th>Name</th>
                                                    <th>Description</th>
                                                    <th width="120">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($roles as $r): ?>
                                                    <tr>
                                                        <td><?= $r->id; ?></td>
                                                        <td><?= html_escape($r->name); ?></td>
                                                        <td><?= html_escape($r->description); ?></td>
                                                        <td>
                                                            <button
                                                                type="button"
                                                                class="btn btn-warning btn-xs btn-edit-role"
                                                                data-id="<?= $r->id; ?>"
                                                                data-name="<?= html_escape($r->name); ?>"
                                                                data-description="<?= html_escape($r->description); ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="btn btn-danger btn-xs btn-delete-role"
                                                                data-id="<?= $r->id; ?>"
                                                                data-name="<?= html_escape($r->name); ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="role-menu-pane" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Pilih Role <span class="text-danger">*</span></label>
                                    <select class="form-control" id="access_role_id">
                                        <option value="">-- Pilih Role --</option>
                                        <?php foreach ($roles as $r): ?>
                                            <option value="<?= $r->id; ?>"><?= html_escape($r->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <?php
                        $parents = [];
                        $children = [];

                        foreach ($menus as $m) {
                            if (empty($m->parent_id)) {
                                $parents[] = $m;
                            } else {
                                $children[$m->parent_id][] = $m;
                            }
                        }
                        ?>

                        <div class="border rounded p-3 bg-white" id="menu_tree_wrapper" style="max-height:450px; overflow-y:auto;">
                            <?php foreach ($parents as $parent): ?>
                                <div class="mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input menu-check" id="menu_<?= $parent->id; ?>" value="<?= $parent->id; ?>">
                                        <label class="custom-control-label" for="menu_<?= $parent->id; ?>">
                                            <strong><?= html_escape($parent->name); ?></strong>
                                            <small class="text-muted">(<?= html_escape($parent->url); ?>)</small>
                                        </label>
                                    </div>

                                    <?php if (!empty($children[$parent->id])): ?>
                                        <div class="pl-4 mt-2">
                                            <?php foreach ($children[$parent->id] as $child): ?>
                                                <div class="custom-control custom-checkbox mb-1">
                                                    <input type="checkbox" class="custom-control-input menu-check child-of-<?= $parent->id; ?>" id="menu_<?= $child->id; ?>" value="<?= $child->id; ?>" data-parent="<?= $parent->id; ?>">
                                                    <label class="custom-control-label" for="menu_<?= $child->id; ?>">
                                                        <?= html_escape($child->name); ?>
                                                        <small class="text-muted">(<?= html_escape($child->url); ?>)</small>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-save-role-menu">
                                <i class="fas fa-save"></i> Save Access
                            </button>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="user-role-pane" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Form User Role</h3>
                                    </div>
                                    <div class="card-body">
                                        <form id="form-user-role">
                                            <input type="hidden" name="id" id="user_role_id">

                                            <div class="form-group">
                                                <label>NRP <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="text" name="nrp" id="nrp" class="form-control" placeholder="Masukkan NRP">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-info" id="btn-search-user">
                                                            <i class="fas fa-search"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small id="user_name_info" class="form-text text-muted">Nama user akan muncul di sini.</small>
                                            </div>

                                            <div class="form-group">
                                                <label>Role <span class="text-danger">*</span></label>
                                                <select name="role_id" id="user_role_role_id" class="form-control">
                                                    <option value="">-- Pilih Role --</option>
                                                    <?php foreach ($roles as $r): ?>
                                                        <option value="<?= $r->id; ?>"><?= html_escape($r->name); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save"></i> Save
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm" id="btn-reset-user-role">
                                                <i class="fas fa-sync"></i> Reset
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">List User Roles</h3>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-striped" id="table_user_roles">
                                            <thead>
                                                <tr>
                                                    <th width="60">ID</th>
                                                    <th>NRP</th>
                                                    <th>Name</th>
                                                    <th>Role</th>
                                                    <th width="120">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</section>

<script>
    $(function() {
        if ($('#table_roles').length) {
            if (typeof setupFilterableDatatable === 'function') {
                setupFilterableDatatable($('#table_roles'));
            } else {
                $('#table_roles').DataTable();
            }
        }

        loadUserRolesTable();

        function toast(icon, title) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 2500
            });
        }

        function resetRoleForm() {
            $('#role_id').val('');
            $('#role_name').val('');
            $('#role_description').val('');
        }

        function resetUserRoleForm() {
            $('#user_role_id').val('');
            $('#nrp').val('');
            $('#user_role_role_id').val('');
            $('#user_name_info').text('Nama user akan muncul di sini.');
        }

        $('#btn-reset-role').on('click', function() {
            resetRoleForm();
        });

        $('#btn-reset-user-role').on('click', function() {
            resetUserRoleForm();
        });

        $(document).on('click', '.btn-edit-role', function() {
            $('#role_id').val($(this).data('id'));
            $('#role_name').val($(this).data('name'));
            $('#role_description').val($(this).data('description'));
        });

        $('#form-role').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '<?= site_url('User_access/save_role'); ?>',
                type: 'POST',
                dataType: 'JSON',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.status) {
                        toast('success', res.message);
                        setTimeout(function() {
                            location.reload();
                        }, 700);
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                }
            });
        });

        $(document).on('click', '.btn-delete-role', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');

            Swal.fire({
                title: 'Hapus role?',
                text: 'Role "' + name + '" akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= site_url('User_access/delete_role'); ?>',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            id: id
                        },
                        success: function(res) {
                            if (res.status) {
                                toast('success', res.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 700);
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                        }
                    });
                }
            });
        });

        $('#access_role_id').on('change', function() {
            var roleId = $(this).val();
            $('.menu-check').prop('checked', false);

            if (roleId === '') return;

            $.ajax({
                url: '<?= site_url('User_access/get_role_menu_access'); ?>',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    role_id: roleId
                },
                success: function(res) {
                    if (res.status && res.data.length > 0) {
                        $.each(res.data, function(i, menuId) {
                            $('#menu_' + menuId).prop('checked', true);
                        });
                    }
                }
            });
        });

        $(document).on('change', '.menu-check', function() {
            var parent = $(this).data('parent');
            var currentVal = $(this).val();

            if ($('#menu_' + currentVal).length && !parent) {
                $('.child-of-' + currentVal).prop('checked', $(this).is(':checked'));
            }

            if (parent && $(this).is(':checked')) {
                $('#menu_' + parent).prop('checked', true);
            }
        });

        $('#btn-save-role-menu').on('click', function() {
            var roleId = $('#access_role_id').val();
            var menuIds = [];

            $('.menu-check:checked').each(function() {
                menuIds.push($(this).val());
            });

            if (roleId === '') {
                Swal.fire('Warning', 'Silakan pilih role terlebih dahulu.', 'warning');
                return;
            }

            $.ajax({
                url: '<?= site_url('User_access/save_role_menu_access'); ?>',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    role_id: roleId,
                    menu_ids: menuIds
                },
                success: function(res) {
                    if (res.status) {
                        toast('success', res.message);
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                }
            });
        });

        $('#btn-search-user').on('click', function() {
            searchUserByNrp();
        });

        $('#nrp').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                searchUserByNrp();
            }
        });

        function searchUserByNrp() {
            var q = $('#nrp').val().trim();

            if (q === '') {
                $('#user_name_info').text('Nama user akan muncul di sini.');
                return;
            }

            $.ajax({
                url: '<?= site_url('User_access/search_user'); ?>',
                type: 'GET',
                dataType: 'JSON',
                data: {
                    q: q
                },
                success: function(res) {
                    if (res.status && res.data.length > 0) {
                        var exact = null;

                        $.each(res.data, function(i, row) {
                            if (row.NRP == q) {
                                exact = row;
                                return false;
                            }
                        });

                        if (exact) {
                            $('#nrp').val(exact.NRP);
                            $('#user_name_info').text(exact.name ? exact.name : '(tanpa nama)');
                        } else {
                            $('#user_name_info').text('User ditemukan: ' + res.data[0].name + ' (' + res.data[0].NRP + ')');
                        }
                    } else {
                        $('#user_name_info').text('User tidak ditemukan.');
                    }
                }
            });
        }

        $('#form-user-role').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '<?= site_url('User_access/save_user_role'); ?>',
                type: 'POST',
                dataType: 'JSON',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.status) {
                        toast('success', res.message);
                        resetUserRoleForm();
                        loadUserRolesTable();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                }
            });
        });

        function loadUserRolesTable() {
            if ($.fn.DataTable.isDataTable('#table_user_roles')) {
                $('#table_user_roles').DataTable().destroy();
            }

            $('#table_user_roles tbody').html('');

            $.ajax({
                url: '<?= site_url('User_access/get_user_roles'); ?>',
                type: 'POST',
                dataType: 'JSON',
                success: function(res) {
                    if (res.status) {
                        var html = '';

                        $.each(res.data, function(i, row) {
                            html += '<tr>' +
                                '<td>' + row.id + '</td>' +
                                '<td>' + (row.NRP ? row.NRP : '') + '</td>' +
                                '<td>' + (row.user_name ? row.user_name : '') + '</td>' +
                                '<td>' + (row.role_name ? row.role_name : '') + '</td>' +
                                '<td>' +
                                '<button type="button" class="btn btn-warning btn-xs btn-edit-user-role" data-id="' + row.id + '" data-nrp="' + row.NRP + '" data-role_id="' + row.role_id + '" data-user_name="' + (row.user_name ? row.user_name : '') + '">' +
                                '<i class="fas fa-edit"></i>' +
                                '</button> ' +
                                '<button type="button" class="btn btn-danger btn-xs btn-delete-user-role" data-id="' + row.id + '">' +
                                '<i class="fas fa-trash"></i>' +
                                '</button>' +
                                '</td>' +
                                '</tr>';
                        });

                        $('#table_user_roles tbody').html(html);

                        if (typeof setupFilterableDatatable === 'function') {
                            setupFilterableDatatable($('#table_user_roles'));
                        } else {
                            $('#table_user_roles').DataTable();
                        }
                    }
                }
            });
        }

        $(document).on('click', '.btn-edit-user-role', function() {
            $('#user_role_id').val($(this).data('id'));
            $('#nrp').val($(this).data('nrp'));
            $('#user_role_role_id').val($(this).data('role_id'));
            $('#user_name_info').text($(this).data('user_name') ? $(this).data('user_name') : 'Nama user akan muncul di sini.');
        });

        $(document).on('click', '.btn-delete-user-role', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'Hapus mapping user role?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= site_url('User_access/delete_user_role'); ?>',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            id: id
                        },
                        success: function(res) {
                            if (res.status) {
                                toast('success', res.message);
                                loadUserRolesTable();
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        }
                    });
                }
            });
        });
    });
</script>