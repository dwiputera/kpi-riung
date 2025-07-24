<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">ORGANIZATION STRUCTURE</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline card-tabs">
            <?php function renderTabs($area_lvl_pstn, $parentKey = null)
            {
                static $counter = 0;
                $tabId = 'tab_' . md5($parentKey . $counter++);
            ?>
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="<?= $tabId ?>" role="tablist">
                        <?php foreach ($area_lvl_pstn as $i_alpstn => $alpstn_i):
                            $paneId = $tabId . '_' . $alpstn_i['id'];
                            $activeClass = $i_alpstn === 0 ? 'active' : '';
                        ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeClass ?>" id="tab_<?= $paneId ?>_tab"
                                    data-toggle="pill" href="#<?= $paneId ?>" role="tab"
                                    aria-controls="<?= $paneId ?>"
                                    aria-selected="<?= $i_alpstn === 0 ? 'true' : 'false' ?>">
                                    <?= htmlspecialchars($alpstn_i['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <button class="nav-item" data-toggle="modal"
                            data-target="#addNavModal" data-parent="<?= $parentKey ?>">
                            + Add
                        </button>
                    </ul>
                </div>

                <div class="tab-content" id="<?= $tabId ?>_content">
                    <?php foreach ($area_lvl_pstn as $i_alpstn => $alpstn_i):
                        $paneId = $tabId . '_' . $alpstn_i['id'];
                        $showClass = $i_alpstn === 0 ? 'show active' : '';
                    ?>
                        <div class="tab-pane fade <?= $showClass ?>" id="<?= $paneId ?>" role="tabpanel" aria-labelledby="tab_<?= $paneId ?>_tab">
                            <div class="card-body">
                                <?php if ($alpstn_i['users']) : ?>
                                    <?php foreach ($alpstn_i['users'] as $user) : ?>
                                        <strong><?= htmlspecialchars($user['NRP']) ?></strong><br>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <button class="btn btn-primary">+ add user</button>
                            </div>

                            <?php if (!empty($alpstn_i['children'])): ?>
                                <?= renderTabs($alpstn_i['children'], $paneId) ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php } ?>

            <?php renderTabs($area_lvl_pstn); ?>
        </div>
    </div>
</section>

<!-- Add Nav Modal -->
<div class="modal fade" id="addNavModal" tabindex="-1" role="dialog" aria-labelledby="addNavModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= site_url('organization_settings/add_nav_item') ?>" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNavModalLabel">Add New Position</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="parent_key" id="modalParentKey">
                    <div class="form-group">
                        <label for="positionName">Position Name</label>
                        <input type="text" class="form-control" name="position_name" id="positionName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $('#addNavModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var parentKey = button.data('parent'); // Extract info from data-* attribute
        var modal = $(this);
        modal.find('#modalParentKey').val(parentKey);
    });
</script>