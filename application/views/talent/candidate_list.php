<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Table Card -->
        <div class="card card-primary card-tabs">
            <div class="card-header">
                <h3 class="card-title mt-2">TABEL PERTIMBANGAN PENILAIAN KANDIDAT: <strong><?= $position['mp_name'] ?> - <?= $position['oal_name'] ?> - <?= $position['name'] ?></strong></h3>
            </div>
            <div class="card-body">
                <table id="employeeTable" class="table table-bordered table-striped datatable-filter-column"
                    data-filter-columns="2:multiple,3:multiple,4:multiple,5:multiple,6,10:number,11:number">
                    <thead>
                        <tr>
                            <th colspan="8" class="text-center">Bobot</th>
                            <th colspan="1" class="text-center">100%</th>
                            <?php foreach ($percentage as $i_pcnt => $pcnt_i) : ?>
                                <th colspan="3" class="text-center"><?= $pcnt_i ?>%</th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th colspan="9"></th>
                            <th colspan="3">Kompetensi Teknis</th>
                            <th colspan="3">Kompetensi Peran dan Perilaku</th>
                            <th colspan="3">IPA</th>
                            <th colspan="3">Tour of Duty</th>
                            <th colspan="3">Culture Fit</th>
                            <th colspan="3">Usia Karyawan</th>
                            <th colspan="3">Status Kesehatan</th>
                            <th colspan="3">Kategori HAV Mapping</th>
                            <th colspan="3">Score Hasil Asesmen</th>
                            <th colspan="3">Korelasi Kompetensi</th>
                        </tr>
                        <!-- BARIS INI HARUS FULL 36 <th> -->
                        <tr>
                            <th>Action</th>
                            <th>No</th>
                            <th>Area</th>
                            <th>Level</th>
                            <th>Matrix Point</th>
                            <th>Position</th>
                            <th>NRP</th>
                            <th>Nama</th>
                            <th>Total Score</th>
                            <th>Info</th>
                            <th>Nilai</th>
                            <th>NxB</th>
                            <th>Info</th>
                            <th>Nilai</th>
                            <th>NxB</th>
                            <th>Info</th>
                            <th>Nilai</th>
                            <th>NxB</th>
                            <th>Info</th>
                            <th>Nilai</th>
                            <th>NxB</th>
                            <th>Info</th>
                            <th>Nilai</th>
                            <th>NxB</th>
                            <th>Info</th>
                            <th>Nilai</th>
                            <th>NxB</th>
                            <th>Info</th>
                            <th>Nilai</th>
                            <th>NxB</th>
                            <th>Info</th>
                            <th>Nilai</th>
                            <th>NxB</th>
                            <th>Info</th>
                            <th>Nilai</th>
                            <th>NxB</th>
                            <th>Info</th>
                            <th>Nilai</th>
                            <th>NxB</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        foreach ($employees as $e): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('talent/profile/' . md5($position['id']) . '/' . md5($e['NRP'])) ?>" target="_blank"
                                        class="label label-primary"><i class="fa fa-eye"></i>
                                    </a>
                                </td>
                                <td><?= $i++ ?></td>
                                <td><?= $e['oa_name'] ?></td>
                                <td><?= $e['oal_name'] ?></td>
                                <td><?= $e['mp_name'] ?></td>
                                <td><?= $e['oalp_name'] ?></td>
                                <td><?= $e['NRP'] ?></td>
                                <td><?= $e['FullName'] ?></td>
                                <td><?= $e['total_score'] ?></td>
                                <td><?= $e['kompetensi_teknis'] ?></td>
                                <td><?= $e['score_kompetensi_teknis'] ?></td>
                                <td><?= $e['score_nxb_kompetensi_teknis'] ?></td>
                                <td><?= $e['job_fit_score'] ?></td>
                                <td><?= $e['score_job_fit_score'] ?></td>
                                <td><?= $e['score_nxb_job_fit_score'] ?></td>
                                <td><?= $e['avg_ipa_score'] ?></td>
                                <td><?= $e['score_avg_ipa_score'] ?></td>
                                <td><?= $e['score_nxb_avg_ipa_score'] ?></td>
                                <td><?= $e['tour_of_duty'] ?></td>
                                <td><?= $e['score_tour_of_duty'] ?></td>
                                <td><?= $e['score_nxb_tour_of_duty'] ?></td>
                                <td><?= $e['culture_fit'] ?></td>
                                <td><?= $e['score_culture_fit'] ?></td>
                                <td><?= $e['score_nxb_culture_fit'] ?></td>
                                <td><?= $e['age'] ?></td>
                                <td><?= $e['score_age'] ?></td>
                                <td><?= $e['score_nxb_age'] ?></td>
                                <td><?= $e['status_kesehatan'] ?></td>
                                <td><?= $e['score_status_kesehatan'] ?></td>
                                <td><?= $e['score_nxb_status_kesehatan'] ?></td>
                                <td><?= $e['status'] ?></td>
                                <td><?= $e['score_kategori_hav_mapping'] ?></td>
                                <td><?= $e['score_nxb_kategori_hav_mapping'] ?></td>
                                <td><?= $e['assess_score'] ?></td>
                                <td><?= $e['score_assess_score'] ?></td>
                                <td><?= $e['score_nxb_assess_score'] ?></td>
                                <td><?= $e['correlation_matrix'] ?></td>
                                <td><?= $e['score_correlation_matrix'] ?></td>
                                <td><?= $e['score_nxb_correlation_matrix'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script src="<?= base_url('assets/js/chart.umd.min.js') ?>"></script>
<script src="<?= base_url('assets/js/chartjs-plugin-datalabels.min.js') ?>"></script>
<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    setupFilterableDatatable($('.datatable-filter-column'));
</script>