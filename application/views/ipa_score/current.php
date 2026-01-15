<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Current IPA Score <?= (int)$year ?></h1>
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">IPA Score</h3>
            </div>

            <div class="card-body">
                <a href="<?= base_url() ?>ipa_score/edit?year=<?= (int)$year ?>" class="btn btn-primary w-100">Edit</a><br><br>

                <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>NRP</th>
                            <th>FULL NAME</th>
                            <th>MATRIX POINT</th>
                            <th>SITE</th>
                            <th>LEVEL</th>
                            <th>JABATAN</th>
                            <th>TAHUN</th>
                            <th>SCORE</th>
                            <th>GRADE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>

                        <?php
                        // contoh grading sederhana (silakan ubah sesuai standar kamu)
                        // A: >= 90, B: 80-89.99, C: 70-79.99, D: < 70
                        function ipa_grade($score)
                        {
                            if ($score === null || $score === '') return ['', 'none'];
                            $s = (float)$score;

                            if ($s >= 90) return ['A', 'success'];
                            if ($s >= 80) return ['B', 'primary'];
                            if ($s >= 70) return ['C', 'warning'];
                            return ['D', 'danger'];
                        }
                        ?>

                        <?php if (!empty($ipa_scores)) : ?>
                            <?php foreach ($ipa_scores as $is_i) : ?>
                                <?php
                                $score = $is_i['score'];
                                [$grade, $badge] = ipa_grade($score);
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($is_i['NRP'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= $is_i['FullName'] ?></td>
                                    <td><?= $is_i['mp_name'] ?></td>
                                    <td><?= $is_i['oa_name'] ?></td>
                                    <td><?= $is_i['oal_name'] ?></td>
                                    <td><?= $is_i['oalp_name'] ?></td>
                                    <td><?= htmlspecialchars($is_i['tahun'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= is_numeric($score) ? rtrim(rtrim(number_format((float)$score, 2, '.', ''), '0'), '.') : '' ?></td>
                                    <td>
                                        <?php if ($grade) : ?>
                                            <span class="badge badge-<?= $badge ?>"><?= $grade ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Data tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));
    });
</script>