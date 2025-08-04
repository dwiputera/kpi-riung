<?php
function renderInput($type, $name, $value, $hash, $class = '')
{
    return "<input type=\"{$type}\" class=\"form-control form-control-sm {$class}\" data-trn_id_hash=\"{$hash}\" data-name=\"{$name}\" value=\"{$value}\">";
}

function renderCell($name, $value, $hash)
{
    return "<td contenteditable=\"true\" class=\"editable-cell\" data-trn_id_hash=\"{$hash}\" data-name=\"{$name}\">{$value}</td>";
}

$numberFields = [
    'days',
    'hours',
    'total_hours',
    'rmho',
    'rmip',
    'rebh',
    'rmtu',
    'rmts',
    'rmgm',
    'rhml',
    'total_jobsite',
    'total_participants',
    'grand_total_hours',
    'biaya_pelatihan_per_orang',
    'biaya_pelatihan',
    'training_kit_per_orang',
    'training_kit',
    'biaya_penginapan_per_orang',
    'biaya_penginapan',
    'meeting_package_per_orang',
    'meeting_package',
    'makan_per_orang',
    'makan',
    'snack_per_orang',
    'snack',
    'tiket_per_orang',
    'tiket',
    'grand_total'
];
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit MTS <?= $year ?></h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Trainings</h3>
            </div>
            <form action="<?= base_url() ?>training/MTS/submit" method="post" id="data-form">
                <div class="card-body">
                    <input type="hidden" name="year" value="<?= $year ?>">
                    <input type="hidden" name="proceed" value="Y">
                    <input type="hidden" name="json_data" id="json_data">

                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>No</th>
                                <th>STATUS</th>
                                <th>MONTH</th>
                                <th>DEPARTEMEN PENGAMPU</th>
                                <th>NAMA PROGRAM</th>
                                <th>BATCH</th>
                                <th>JENIS KOMPETENSI</th>
                                <th>SASARAN KOMPETENSI</th>
                                <th>LEVEL KOMPETENSI</th>
                                <th>TARGET PESERTA</th>
                                <th>STAFF/NONSTAFF</th>
                                <th>KATEGORI PROGRAM</th>
                                <th>FASILITATOR</th>
                                <th>NAMA PENYELENGGARA/FASILITATOR</th>
                                <th>TEMPAT</th>
                                <th>ONLINE / OFFLINE</th>
                                <th>START DATE</th>
                                <th>END DATE</th>
                                <th>DAYS</th>
                                <th>HOURS</th>
                                <th>TOTAL HOURS</th>
                                <th>RMHO</th>
                                <th>RMIP</th>
                                <th>REBH</th>
                                <th>RMTU</th>
                                <th>RMTS</th>
                                <th>RMGM</th>
                                <th>RHML</th>
                                <th>TOTAL JOBSITE</th>
                                <th>TOTAL PARTICIPANTS</th>
                                <th>GRAND TOTAL HOURS</th>
                                <th>BIAYA PELATIHAN/ ORANG</th>
                                <th>BIAYA PELATIHAN</th>
                                <th>TRAINING KIT/ORANG</th>
                                <th>TRAINING KIT</th>
                                <th>NAMA HOTEL</th>
                                <th>BIAYA PENGINAPAN /ORANG</th>
                                <th>BIAYA PENGINAPAN</th>
                                <th>MEETING PACKAGE/ORANG</th>
                                <th>MEETING PACKAGE</th>
                                <th>MAKAN/ORANG</th>
                                <th>MAKAN</th>
                                <th>SNACK/ORANG</th>
                                <th>SNACK</th>
                                <th>TIKET/ORANG</th>
                                <th>TIKET</th>
                                <th>GRAND TOTAL</th>
                                <th>KETERANGAN</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trainings as $i => $training): ?>
                                <?php $hash = md5($training['id']); ?>
                                <tr data-id="<?= $training['id'] ?>" data-hash="<?= $hash ?>">
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <select class="form-control form-control-sm status-select <?=
                                                                                                    $training['status'] == 'Y' ? 'bg-success text-white' : ($training['status'] == 'P' ? 'bg-secondary text-white' : ($training['status'] == 'N' ? 'bg-danger text-white' :
                                                                                                        'bg-warning text-dark')) ?>"
                                            data-name="status" data-trn_id_hash="<?= $hash ?>">
                                            <option value="P" <?= $training['status'] == 'P' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Y" <?= $training['status'] == 'Y' ? 'selected' : '' ?>>Done</option>
                                            <option value="N" <?= $training['status'] == 'N' ? 'selected' : '' ?>>Cancelled</option>
                                            <option value="R" <?= $training['status'] == 'R' ? 'selected' : '' ?>>Reschedule</option>
                                        </select>
                                    </td>
                                    <?= renderCell('month', $training['month'], $hash) ?>
                                    <?= renderCell('departemen_pengampu', $training['departemen_pengampu'], $hash) ?>
                                    <?= renderCell('nama_program', $training['nama_program'], $hash) ?>
                                    <td><?= renderInput('number', 'batch', $training['batch'], $hash) ?></td>
                                    <?= renderCell('jenis_kompetensi', $training['jenis_kompetensi'], $hash) ?>
                                    <?= renderCell('sasaran_kompetensi', $training['sasaran_kompetensi'], $hash) ?>
                                    <td><?= renderInput('number', 'level_kompetensi', $training['level_kompetensi'], $hash) ?></td>
                                    <?= renderCell('target_peserta', $training['target_peserta'], $hash) ?>
                                    <?= renderCell('staff_nonstaff', $training['staff_nonstaff'], $hash) ?>
                                    <?= renderCell('kategori_program', $training['kategori_program'], $hash) ?>
                                    <?= renderCell('fasilitator', $training['fasilitator'], $hash) ?>
                                    <?= renderCell('nama_penyelenggara_fasilitator', $training['nama_penyelenggara_fasilitator'], $hash) ?>
                                    <?= renderCell('tempat', $training['tempat'], $hash) ?>
                                    <?= renderCell('online_offline', $training['online_offline'], $hash) ?>
                                    <td><?= renderInput('date', 'start_date', $training['start_date'], $hash) ?></td>
                                    <td><?= renderInput('date', 'end_date', $training['end_date'], $hash) ?></td>
                                    <?php foreach ($numberFields as $field): ?>
                                        <td><?= renderInput('number', $field, $training[$field], $hash) ?></td>
                                    <?php endforeach; ?>
                                    <?= renderCell('nama_hotel', $training['nama_hotel'], $hash) ?>
                                    <?= renderCell('keterangan', $training['keterangan'], $hash) ?>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="markRowDeleted(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
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
                            <button type="button" class="w-100 btn btn-danger" onclick="deleteSelectedRows()">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <div class="col-lg-3">
                            <button type="button" class="w-100 btn btn-success" onclick="createTrainingRow()">
                                <i class="fas fa-plus"></i> New Training
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

<style>
    .status-select option {
        background-color: #fff !important;
        color: #000 !important;
    }
</style>

<!-- Scripts -->
<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    let deletedRows = [];

    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));

        // Terapkan warna awal setelah halaman selesai load
        $('.status-select').each(function() {
            applyStatusColor($(this)); // <- Warna td & select langsung diterapkan
        });

        $('#select-all').on('click', function() {
            $('.row-checkbox').prop('checked', this.checked);
        });

        $('#data-form').on('submit', function() {
            let allRows = collectTableData();
            let payload = {
                updates: allRows.filter(r => !String(r.id).startsWith('new_')),
                deletes: deletedRows,
                creates: allRows.filter(r => String(r.id).startsWith('new_'))
            };
            $('#json_data').val(JSON.stringify(payload));
        });
    });

    // Fungsi reusable untuk set warna td & select
    function applyStatusColor($select) {
        let val = $select.val();
        let td = $select.closest('td');

        // Hapus semua warna dulu
        td.removeClass('bg-success bg-secondary bg-danger bg-warning text-white text-dark');
        $select.removeClass('bg-success bg-secondary bg-danger bg-warning text-white text-dark');

        // Tambahkan warna sesuai status
        if (val === 'Y') {
            td.addClass('bg-success text-white');
            $select.addClass('bg-success text-white');
        } else if (val === 'P') {
            td.addClass('bg-secondary text-white');
            $select.addClass('bg-secondary text-white');
        } else if (val === 'N') {
            td.addClass('bg-danger text-white');
            $select.addClass('bg-danger text-white');
        } else if (val === 'R') {
            td.addClass('bg-warning text-dark');
            $select.addClass('bg-warning text-dark');
        }
    }

    // Event change untuk update warna jika select berubah
    $(document).on('change', '.status-select', function() {
        applyStatusColor($(this));
    });

    function cancelForm() {
        if (confirm('Yakin batal?')) {
            location.href = '<?= base_url('training/MTS' . ($year ? '?year=' . $year : '')) ?>';
        }
    }

    function markRowDeleted(btn) {
        let row = $(btn).closest('tr');
        let id = row.data('id');
        let checkbox = row.find('.row-checkbox');

        if (deletedRows.includes(id)) {
            // ✅ Restore if already marked deleted
            deletedRows = deletedRows.filter(x => x !== id);
            row.removeClass('table-danger').css('opacity', '1');
            checkbox.prop('checked', false); // uncheck when restored
        } else {
            // ✅ Mark as deleted
            deletedRows.push(id);
            row.addClass('table-danger').css('opacity', '0.7');
            checkbox.prop('checked', true); // auto-check when deleted
        }
    }


    function deleteSelectedRows() {
        $('#datatable tbody tr').each(function() {
            let row = $(this);
            let id = row.data('id');
            let isChecked = row.find('.row-checkbox').prop('checked');

            if (isChecked) {
                // ✅ Always mark checked rows as deleted
                if (!deletedRows.includes(id)) {
                    deletedRows.push(id);
                }
                row.addClass('table-danger').css('opacity', '0.7');
            } else {
                // ✅ Unchecked rows: restore if previously marked deleted
                if (deletedRows.includes(id)) {
                    deletedRows = deletedRows.filter(x => x !== id);
                    row.removeClass('table-danger').css('opacity', '1');
                }
            }
        });
    }

    function createTrainingRow() {
        let newId = 'new_' + Date.now();
        let row = `<tr data-id="${newId}" class="table-success">
            <td><input type="checkbox" class="row-checkbox"></td>
            <td>New</td>
            <td>
                <select class="form-control form-control-sm status-select bg-secondary text-white" data-name="status">
                    <option value="P" selected>Pending</option>
                    <option value="Y">Done</option>
                    <option value="N">Cancelled</option>
                    <option value="R">Reschedule</option>
                </select>
            </td>
            <td contenteditable="true" data-name="month"></td>
            <td contenteditable="true" data-name="departemen_pengampu"></td>
            <td contenteditable="true" data-name="nama_program"></td>
            <td><input type="number" class="form-control form-control-sm" data-name="batch"></td>
            <td contenteditable="true" data-name="jenis_kompetensi"></td>
            <td contenteditable="true" data-name="sasaran_kompetensi"></td>
            <td><input type="number" class="form-control form-control-sm" data-name="level_kompetensi"></td>
            <td contenteditable="true" data-name="target_peserta"></td>
            <td contenteditable="true" data-name="staff_nonstaff"></td>
            <td contenteditable="true" data-name="kategori_program"></td>
            <td contenteditable="true" data-name="fasilitator"></td>
            <td contenteditable="true" data-name="nama_penyelenggara_fasilitator"></td>
            <td contenteditable="true" data-name="tempat"></td>
            <td contenteditable="true" data-name="online_offline"></td>
            <td><input type="date" class="form-control form-control-sm" data-name="start_date"></td>
            <td><input type="date" class="form-control form-control-sm" data-name="end_date"></td>
            <?php foreach ($numberFields as $field): ?>
                <td><input type="number" class="form-control form-control-sm" data-name="<?= $field ?>"></td>
            <?php endforeach; ?>
            <td contenteditable="true" data-name="nama_hotel"></td>
            <td contenteditable="true" data-name="keterangan"></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="markRowDeleted(this)"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        $('#datatable tbody').append(row);
    }

    function collectTableData() {
        let data = [];
        let table = $('#datatable').DataTable();

        table.rows().every(function() {
            let row = $(this.node());
            data.push(collectRow(row));
        });

        $('#datatable tbody tr').each(function() {
            let row = $(this);
            let id = row.data('id');
            if (!data.find(r => r.id === id)) {
                data.push(collectRow(row));
            }
        });

        return data;
    }

    function collectRow(row) {
        let id = row.data('id');
        let rowData = {
            id: id
        };

        row.find('td[contenteditable], input, select').each(function() {
            let name = $(this).data('name');
            if (name) {
                if ($(this).is('input') || $(this).is('select')) {
                    rowData[name] = $(this).val();
                } else {
                    rowData[name] = $(this).text();
                }
            }
        });

        return rowData;
    }
</script>