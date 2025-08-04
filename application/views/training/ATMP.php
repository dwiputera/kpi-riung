<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0">ATMP(Annual Training Master Plan)</h1>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
		<div class="card card-primary">
			<div class="card-header">
				<h3 class="card-title">Year Change</h3>
			</div>
			<!-- /.card-header -->
			<!-- form start -->
			<form action="<?= base_url() ?>training/ATMP" method="get">
				<div class="card-body">
					<div class="form-group">
						<label>Year:</label>
						<div class="row">
							<div class="col-lg-8">
								<div class="input-group date" id="year" data-target-input="nearest">
									<input type="text" class="form-control datetimepicker-input" data-target="#year" value="<?= $year ?>" name="year" />
									<div class="input-group-append" data-target="#year" data-toggle="datetimepicker">
										<div class="input-group-text"><i class="fa fa-calendar"></i></div>
									</div>
								</div>
								<?php echo form_error('year', '<div class="text-danger small">', '</div>'); ?>
							</div>
							<div class="col-lg-4">
								<button type="submit" class="btn btn-primary w-100">Change</button>
							</div>
						</div>
					</div>
				</div>
				<!-- /.card-body -->
			</form>
		</div>
		<!-- /.card -->

		<div class="card card-primary">
			<div class="card-header">
				<h3 class="card-title">Upload ATMP</h3>
			</div>
			<!-- /.card-header -->
			<!-- form start -->
			<form action="<?= base_url() ?>training/ATMP/do_upload" method="post" enctype="multipart/form-data">
				<div class="card-body">
					<div class="form-group">
						<input type="hidden" name="year" value="<?= $year ?>">
						<!-- <label for="userfile">File input</label> -->
						<div class="input-group">
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="userfile" name="userfile" accept=".xls,.xlsx,.csv">>
								<label class="custom-file-label" for="userfile">Choose file</label>
							</div>
							<div class="input-group-append">
								<button type="submit" class="input-group-text" onClick="confirm('Upload ulang akan menghapus semua data training lama di tahun <?= $year ?>')">Submit</button>
							</div>
						</div>
						<?php echo form_error('userfile', '<div class="text-danger small">', '</div>'); ?>
					</div>
				</div>
				<!-- /.card-body -->
			</form>
		</div>
		<!-- /.card -->

		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Uploaded ATMP</h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<table id="datatable" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>No</th>
							<th>Uploaded At</th>
							<th>Uploaded By</th>
							<th>Year</th>
							<th>File</th>
							<th>Download</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody>
						<?php $i = 1; ?>
						<?php foreach ($atmps as $atmp) : ?>
							<tr>
								<td><?= $i++ ?></td>
								<td><?= date("F j, Y, g:i a", strtotime($atmp['uploaded_at'])) ?></td>
								<td><?= $atmp['uploaded_by'] ?></td>
								<td><?= $atmp['year'] ?></td>
								<td><?= $atmp['file_name'] ?></td>
								<td>
									<a href="<?= base_url(); ?>training/ATMP/download/<?= md5($atmp['id']) ?>" class="btn btn-sm btn-primary">
										<i class="fas fa-download"></i>
									</a>
								</td>
								<td>
									<a href="<?= base_url('training/ATMP/delete/' . md5($atmp['id'])); ?>"
										class="btn btn-sm btn-danger"
										onclick="return confirm('Are you sure you want to delete this file?');">
										<i class="fas fa-trash"></i>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->

		<?php if ($trainings) : ?>
			<div class="card card-primary">
				<div class="card-header">
					<h3 class="card-title">Training List</h3>
				</div>
				<!-- /.card-header -->
				<div class="card-body">
					<a href="<?= base_url() ?>training/ATMP/edit/<?= $year ?>" class="btn btn-primary w-100">Edit</a><br><br>
					<table id="datatable_training" class="table table-bordered table-striped datatable-filter-column">
						<thead>
							<tr>
								<th>No</th>
								<th>MTS</th>
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
							</tr>
						</thead>
						<tbody>
							<?php $i = 1; ?>
							<?php $status_str = ['P' => 'Pending', 'Y' => 'Done', 'N' => 'Canceled', 'R' => 'Reschedule']; ?>
							<?php $status_bg = ['P' => 'none', 'Y' => 'primary', 'N' => 'danger', 'R' => 'warning']; ?>
							<?php foreach ($trainings as $training) : ?><tr>
									<td><?= $i++ ?></td>
									<td><a href="<?= base_url() ?>training/ATMP/MTS/<?= md5($training['id']) ?>?year=<?= $year ?>" class="btn btn-primary btn-sm"><?= count($training['mts']) ?></a></td>
									<td><?= $training['month'] ?></td>
									<td><?= $training['departemen_pengampu'] ?></td>
									<td><?= $training['nama_program'] ?></td>
									<td><?= $training['batch'] ?></td>
									<td><?= $training['jenis_kompetensi'] ?></td>
									<td><?= $training['sasaran_kompetensi'] ?></td>
									<td><?= $training['level_kompetensi'] ?></td>
									<td><?= $training['target_peserta'] ?></td>
									<td><?= $training['staff_nonstaff'] ?></td>
									<td><?= $training['kategori_program'] ?></td>
									<td><?= $training['fasilitator'] ?></td>
									<td><?= $training['nama_penyelenggara_fasilitator'] ?></td>
									<td><?= $training['tempat'] ?></td>
									<td><?= $training['online_offline'] ?></td>
									<td><?= $training['start_date'] ?></td>
									<td><?= $training['end_date'] ?></td>
									<td><?= $training['days'] ?></td>
									<td><?= $training['hours'] ?></td>
									<td><?= $training['total_hours'] ?></td>
									<td><?= $training['rmho'] ?></td>
									<td><?= $training['rmip'] ?></td>
									<td><?= $training['rebh'] ?></td>
									<td><?= $training['rmtu'] ?></td>
									<td><?= $training['rmts'] ?></td>
									<td><?= $training['rmgm'] ?></td>
									<td><?= $training['rhml'] ?></td>
									<td><?= $training['total_jobsite'] ?></td>
									<td>
										<a href="<?= base_url() ?>training/ATMP/participant/list?training_id=<?= md5($training['id']) ?>" class="btn btn-primary btn-sm"><?= $training['total_participant'] ?? 0 ?></a>
									</td>
									<td><?= $training['grand_total_hours'] ?></td>
									<td><?= $training['biaya_pelatihan_per_orang'] ?></td>
									<td><?= $training['biaya_pelatihan'] ?></td>
									<td><?= $training['training_kit_per_orang'] ?></td>
									<td><?= $training['training_kit'] ?></td>
									<td><?= $training['nama_hotel'] ?></td>
									<td><?= $training['biaya_penginapan_per_orang'] ?></td>
									<td><?= $training['biaya_penginapan'] ?></td>
									<td><?= $training['meeting_package_per_orang'] ?></td>
									<td><?= $training['meeting_package'] ?></td>
									<td><?= $training['makan_per_orang'] ?></td>
									<td><?= $training['makan'] ?></td>
									<td><?= $training['snack_per_orang'] ?></td>
									<td><?= $training['snack'] ?></td>
									<td><?= $training['tiket_per_orang'] ?></td>
									<td><?= $training['tiket'] ?></td>
									<td><?= $training['grand_total'] ?></td>
									<td><?= $training['keterangan'] ?></td>
								</tr>

							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<!-- /.card-body -->
			</div>
			<!-- /.card -->
		<?php endif; ?>
	</div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
	//Date picker
	$('#year').datetimepicker({
		format: 'YYYY', // Only year
		viewMode: 'years',
	});

	setupFilterableDatatable($('.datatable-filter-column'));

	// $(function() {
	// 	bsCustomFileInput.init();
	// });
</script>