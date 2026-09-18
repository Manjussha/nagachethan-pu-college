(function () {
    var busy = document.querySelector('.busy');

    function showBusy() {
        if (busy) busy.hidden = false;
    }

    // Spinner while a photo uploads
    document.querySelectorAll('form.js-busy').forEach(function (form) {
        form.addEventListener('submit', showBusy);
    });

    // "Change photo" / "Replace photo" buttons upload as soon as a file is picked
    document.querySelectorAll('.js-autosubmit').forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.files.length) {
                showBusy();
                input.form.submit();
            }
        });
    });

    // Ask before delete / undo
    document.querySelectorAll('form.js-confirm').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
        });
    });

    // Thumbnails of the chosen photos before upload
    document.querySelectorAll('.js-preview').forEach(function (input) {
        var box = input.closest('.drop').querySelector('.drop__previews');
        input.addEventListener('change', function () {
            box.innerHTML = '';
            Array.prototype.slice.call(input.files, 0, 20).forEach(function (file) {
                if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return;
                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = '';
                box.appendChild(img);
            });
        });
    });
})();
