let waitInterval = null;

function showOverlayFull() {
    if ($('#global-overlay').length === 0) {
        const overlay = `
            <div id="global-overlay" style="
                position: fixed;
                top: 0; left: 0;
                width: 100vw; height: 100vh;
                background: rgba(255, 255, 255, 0.7);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                font-family: sans-serif;
            ">
                <i class="fas fa-2x fa-sync-alt fa-spin mb-3"></i>
                <div id="wait-text" style="font-size: 1.2rem; font-weight: bold; color: #333;">Please wait.</div>
            </div>
        `;
        $('body').append(overlay);
    } else {
        $('#global-overlay').show();
    }

    // Animate "Please wait."
    const baseText = "Please wait";
    let dots = 0;
    waitInterval = setInterval(() => {
        dots = (dots + 1) % 4;
        $('#wait-text').text(baseText + '.'.repeat(dots));
    }, 500);
}

function hideOverlayFull() {
    if (waitInterval) clearInterval(waitInterval);
    $('#global-overlay').fadeOut(200, function () {
        $(this).remove(); // remove overlay after fadeOut
    });
}
