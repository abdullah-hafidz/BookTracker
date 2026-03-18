document.addEventListener('DOMContentLoaded', function () {

    // ---- Delete confirmation ----
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(btn.getAttribute('data-confirm') || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // ---- Cover image preview on file select ----
    var fileInput   = document.getElementById('cover');
    var previewWrap = document.getElementById('previewWrap');
    var previewImg  = document.getElementById('previewImg');
    var previewName = document.getElementById('previewName');

    if (fileInput && previewWrap && previewImg) {
        fileInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewWrap.style.display = 'flex';
                if (previewName) previewName.textContent = file.name;
            };
            reader.readAsDataURL(file);
        });
    }

    // ---- Theme toggle ----
    var toggle = document.getElementById('themeToggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            var html    = document.documentElement;
            var current = html.getAttribute('data-theme');
            var next    = current === 'dark' ? 'light' : 'dark';

            // Update UI immediately (no reload)
            html.setAttribute('data-theme', next);
            toggle.setAttribute('data-theme', next);

            // Persist to DB via server
            fetch(window.APP_URL + '/?action=save-theme', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    'theme=' + next
            });
        });
    }

});
