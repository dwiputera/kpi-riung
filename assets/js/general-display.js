$(function () {
    // Inject buttons into all card headers except those with .no-tools
    $('.card-header').not('.no-tools').each(function () {
        if (!$(this).find('.card-tools').length) {
            $(this).append(`
                <div class="card-tools">
                    <!-- Minimize -->
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <!-- Fullscreen -->
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                    <!-- Close -->
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
        }
    });
});
