<style>
    .select2 {
        width: 100% !important;
        max-width: 100%;
        box-sizing: border-box;
    }

    .editable-target {
        min-width: 70px;
        background: #fffbe6;
        cursor: text;
        direction: ltr;
        text-align: left;
        unicode-bidi: plaintext;
    }

    .editable-target:focus {
        outline: 2px solid #007bff;
        background: #ffffff;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Level Competency Matrix</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1 no-tools">
                <ul class="nav nav-tabs" id="custom-tabs-tab" role="tablist">
                    <li class="pt-2 px-3">
                        <h3 class="card-title"><strong>Levels</strong></h3>
                    </li>

                    <?php foreach ($area_lvls as $i_oal => $oal_i) : ?>
                        <?php
                        $activeClass = '';
                        if ($level_active) {
                            if ($level_active == md5($oal_i['oal_id'])) {
                                $activeClass = 'active';
                            }
                        } else {
                            $activeClass = $i_oal == 0 ? 'active' : '';
                        }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeClass ?>"
                                id="custom-tabs-<?= md5($oal_i['oal_id']) ?>-tab"
                                data-toggle="pill"
                                href="#custom-tabs-<?= md5($oal_i['oal_id']) ?>"
                                role="tab"
                                aria-controls="custom-tabs-<?= md5($oal_i['oal_id']) ?>"
                                aria-selected="true">
                                <?= $oal_i['oal_name'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="custom-tabs-tabContent">

                    <?php foreach ($area_lvls as $i_oal => $oal_i) : ?>
                        <?php
                        $activeClass = '';
                        if ($level_active) {
                            if ($level_active == md5($oal_i['oal_id'])) {
                                $activeClass = 'show active';
                            }
                        } else {
                            $activeClass = $i_oal == 0 ? 'show active' : '';
                        }

                        $positions = array_filter(
                            $area_pstns,
                            function ($oalp_i) use ($oal_i) {
                                return $oalp_i['area_lvl_id'] == $oal_i['oal_id'] || $oalp_i['equals'] == $oal_i['oal_id'];
                            }
                        );
                        ?>

                        <div class="tab-pane fade <?= $activeClass ?>"
                            id="custom-tabs-<?= md5($oal_i['oal_id']) ?>"
                            role="tabpanel"
                            aria-labelledby="custom-tabs-<?= md5($oal_i['oal_id']) ?>-tab">

                            <div class="mb-3">
                                <a href="<?= base_url('comp_settings/level_matrix/dictionary') ?>" class="btn btn-primary w-100">
                                    Dictionary of Competency
                                </a>
                            </div>

                            <form method="post"
                                action="<?= base_url('comp_settings/level_matrix/comp_lvl_target/submit') ?>"
                                class="form-level-matrix">
                                <input type="hidden" name="level_active" value="<?= md5($oal_i['oal_id']) ?>">

                                <table class="table table-bordered table-striped datatable-filter-column"
                                    data-filter-columns="1,2:multiple">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Area</th>
                                            <th>Level</th>
                                            <th>Position</th>
                                            <?php foreach ($comp_levels as $cl_i) : ?>
                                                <th><?= $cl_i['name'] ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        <?php foreach ($positions as $pstn_i) : ?>
                                            <?php $area_lvl_pstn_id = (int) ($pstn_i['id'] ?? 0); ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= $pstn_i['oa_name'] ?></td>
                                                <td><?= $pstn_i['oal_name'] ?></td>
                                                <td><?= $pstn_i['name'] ?></td>

                                                <?php foreach ($comp_levels as $cl_i) : ?>
                                                    <?php
                                                    $comp_lvl_id = (int) $cl_i['id'];
                                                    $val = isset($pstn_i['target'][$comp_lvl_id]) ? $pstn_i['target'][$comp_lvl_id] : 0;
                                                    $display_val = rtrim(rtrim((string) $val, '0'), '.');
                                                    if ($display_val === '') {
                                                        $display_val = '0';
                                                    }
                                                    ?>
                                                    <td class="editable-target"
                                                        contenteditable="true"
                                                        dir="ltr"
                                                        data-area-lvl-pstn-id="<?= $area_lvl_pstn_id ?>"
                                                        data-comp-lvl-id="<?= $comp_lvl_id ?>"><?= $display_val ?></td>

                                                    <input type="hidden"
                                                        name="targets[<?= $area_lvl_pstn_id ?>][<?= $comp_lvl_id ?>]"
                                                        value="<?= $val ?>"
                                                        class="target-input target-input-<?= $area_lvl_pstn_id ?>-<?= $comp_lvl_id ?>">
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <button type="submit"
                                            name="proceed"
                                            value="N"
                                            class="btn btn-default w-100 show-overlay-full">
                                            Cancel
                                        </button>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="hidden" name="target_json" class="target_json">
                                        <button type="submit"
                                            class="btn btn-info w-100 show-overlay-full">
                                            Submit
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });

        function normalizeTarget(val) {
            val = (val || '').toString();

            // hapus karakter aneh / zero width / nbsp
            val = val
                .replace(/\u200B/g, '')
                .replace(/\u200C/g, '')
                .replace(/\u200D/g, '')
                .replace(/\uFEFF/g, '')
                .replace(/\u00A0/g, ' ')
                .trim();

            // ubah koma ke titik
            val = val.replace(/,/g, '.');

            // sisakan angka, titik, minus
            val = val.replace(/[^\d.\-]/g, '');

            if (val === '' || val === '-' || val === '.' || val === '-.') {
                return '0';
            }

            var num = parseFloat(val);
            if (isNaN(num)) {
                return '0';
            }

            return num.toString();
        }

        function syncHiddenInput($cell) {
            var areaLvlPstnId = $cell.data('area-lvl-pstn-id');
            var compLvlId = $cell.data('comp-lvl-id');
            var rawText = $cell.text();
            var normalized = normalizeTarget(rawText);

            $('.target-input-' + areaLvlPstnId + '-' + compLvlId).val(normalized);
        }

        function placeCaretAtEnd(el) {
            el.focus();
            if (typeof window.getSelection != "undefined" &&
                typeof document.createRange != "undefined") {
                var range = document.createRange();
                range.selectNodeContents(el);
                range.collapse(false);
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }

        // Saat mengetik: jangan rewrite text di cell
        $(document).on('input', '.editable-target', function() {
            syncHiddenInput($(this));
        });

        // Saat paste: ambil plain text saja
        $(document).on('paste', '.editable-target', function(e) {
            e.preventDefault();

            var text = '';
            if (e.originalEvent.clipboardData && e.originalEvent.clipboardData.getData) {
                text = e.originalEvent.clipboardData.getData('text/plain');
            } else if (window.clipboardData && window.clipboardData.getData) {
                text = window.clipboardData.getData('Text');
            }

            document.execCommand('insertText', false, text);
        });

        // Saat focus: pastikan arah text normal
        $(document).on('focus', '.editable-target', function() {
            this.style.direction = 'ltr';
            this.style.textAlign = 'left';
        });

        // Saat blur: baru rapikan nilainya
        $(document).on('blur', '.editable-target', function() {
            var $cell = $(this);
            var normalized = normalizeTarget($cell.text());

            $cell.text(normalized);
            syncHiddenInput($cell);
        });

        // Optional: enter jangan bikin baris baru
        $(document).on('keydown', '.editable-target', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).blur();
            }
        });

        // Simpan tab aktif saat pindah tab
        $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
            var href = $(e.target).attr('href');
            var id = href.replace('#custom-tabs-', '');
            var url = new URL(window.location.href);
            url.searchParams.set('level_active', id);
            window.history.replaceState({}, '', url);
        });

        // Saat submit, bentuk juga target_json
        $(document).on('submit', '.form-level-matrix', function() {
            var data = {};
            var $form = $(this);

            $form.find('.editable-target').each(function() {
                var $cell = $(this);
                var areaLvlPstnId = $cell.data('area-lvl-pstn-id');
                var compLvlId = $cell.data('comp-lvl-id');
                var value = normalizeTarget($cell.text());

                if (!data[areaLvlPstnId]) {
                    data[areaLvlPstnId] = {};
                }

                data[areaLvlPstnId][compLvlId] = value;

                $('.target-input-' + areaLvlPstnId + '-' + compLvlId).val(value);
            });

            $form.find('.target_json').val(JSON.stringify(data));
        });
    });
</script>