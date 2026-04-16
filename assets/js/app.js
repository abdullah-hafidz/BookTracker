document.addEventListener('DOMContentLoaded', function () {

    // ---- Click anywhere on a book card → go to detail page ----
    document.querySelectorAll('.book-card').forEach(function (card) {
        card.addEventListener('click', function (e) {
            if (!e.target.closest('a, button, form')) {
                var link = card.querySelector('.book-title a');
                if (link) window.location = link.href;
            }
        });
    });

    // ---- Delete confirmation ----
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(btn.getAttribute('data-confirm') || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

});
