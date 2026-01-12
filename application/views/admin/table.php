<br>
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Table <strong>"<?= $table ?>"</strong></h3>
            </div>

            <form action="<?= base_url() ?>admin/database/table_submit/<?= $table_id ?>" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">

                <div class="card-body">
                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>NO</th>
                                <?php foreach ($columns as $i_col => $col_i) : ?>
                                    <th><?= $col_i ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($rows as $i_row => $row_i) : ?>
                                <tr data-id="<?= $row_i['id'] ?>">
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td><?= $i++ ?></td>

                                    <?php foreach ($columns as $i_col => $col_i) : ?>
                                        <?php
                                        $isId = ($col_i === 'id');
                                        // Engine CRUD baca data-name, bukan data-column
                                        // Untuk id: tidak editable
                                        ?>
                                        <td <?= $isId ? '' : 'contenteditable="true" data-name="' . $col_i . '"' ?>>
                                            <?= $row_i[$col_i] ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-3">
                            <button type="button" class="w-100 btn btn-default" onclick="cancelForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>

                        <div class="col-lg-3">
                            <button type="button" class="w-100 btn btn-danger" id="btn-delete-selected">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>

                        <div class="col-lg-3 d-flex">
                            <input type="number" class="form-control w-50" id="row_number_add" name="row_number_add" value="1">
                            <button type="button" class="w-50 btn btn-success" id="btn-new-row">
                                <i class="fas fa-plus"></i> New
                            </button>
                        </div>

                        <div class="col-lg-3">
                            <button type="submit" class="w-100 btn btn-info">
                                <i class="fas fa-paper-plane"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</section>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    function cancelForm() {
        if (confirm('Yakin batal?')) {
            location.href = '<?= base_url('admin/database') ?>';
        }
    }

    // kolom dinamis dari PHP (AMAN: pakai json_encode biar ga invalid token)
    window.TABLE_COLUMNS = <?= json_encode(array_values($columns), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    // exclude id supaya tidak bisa diisi dan tidak menimpa rowId (new_...)
    window.DT_DYNAMIC_COLUMNS = (window.TABLE_COLUMNS || [])
        .filter(c => c !== 'id')
        .map(c => ({
            name: c,
            type: 'editable'
        }));

    const hasIdAsFirst = (window.TABLE_COLUMNS || [])[0] === 'id';

    window.DT_CRUD_CONFIG = {
        tableSelector: '#datatable',
        formSelector: '#data-form',
        jsonFieldSelector: '#json_data',

        btnNewSelector: '#btn-new-row',
        btnDeleteSelectedSelector: '#btn-delete-selected',
        rowAddCountSelector: '#row_number_add',
        selectAllSelector: '#select-all',
        rowCheckboxSelector: '.row-checkbox',

        rowPrefixHtml: () => `
        <td><input type="checkbox" class="row-checkbox"></td>
        <td>New</td>
        ${hasIdAsFirst ? '<td></td>' : ''}
        `,

        deletedRowClass: 'table-danger',
        deletedOpacity: 0.7,
        newRowClass: 'table-success',

        columns: window.DT_DYNAMIC_COLUMNS,
        beforeSubmit: (payload) => {
            payload.creates = (payload.creates || []).map(r => {
                const x = {
                    ...r
                };
                delete x.id;
                return x;
            });
            return payload;
        }
    };

    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));
        setupDatatableCrud($('#datatable'), window.DT_CRUD_CONFIG);
    });
</script>