<section class="content p-3">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Gabung ke Quiz</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>PIN Quiz</label>
                            <input type="text" id="pin" class="form-control" placeholder="Masukkan PIN (contoh: 123456)" maxlength="6">
                        </div>
                        <button id="btnJoin" class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt mr-1"></i> Join</button>
                    </div>
                    <div class="card-footer text-muted">PIN berlaku sampai quiz berakhir.</div>
                </div>
                <a class="btn btn-outline-secondary btn-block" href="<?= site_url('quiz/host'); ?>">Saya Host</a>
            </div>
        </div>
    </div>
</section>

<script>
    $('#btnJoin').on('click', function() {
        var pin = $('#pin').val().trim();
        if (!pin) {
            alert('PIN wajib diisi');
            return;
        }
        $.post('<?= site_url('quiz/api_join_by_pin'); ?>', {
            pin: pin
        }, function(res) {
            if (res.ok) {
                window.location.href = '<?= site_url('quiz/play'); ?>';
            } else {
                alert(res.msg || 'Gagal join');
            }
        }, 'json').fail(function(xhr) {
            alert(xhr.responseJSON?.msg || 'Error');
        });
    });
</script>