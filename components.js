/**
 * Shared Header & Footer Loader
 * Loads header.html and footer.html into each page,
 * then sets the active nav link based on current page.
 */
(function () {
    // Detect current page name from URL
    var path = window.location.pathname;
    var page = path.substring(path.lastIndexOf('/') + 1) || 'index.html';
    // Remove .html extension to get page key
    var pageKey = page.replace('.html', '') || 'index';

    // Load header
    var headerPlaceholder = document.getElementById('header-placeholder');
    var footerPlaceholder = document.getElementById('footer-placeholder');

    function loadComponent(url, placeholder, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                placeholder.outerHTML = xhr.responseText;
                if (callback) callback();
            }
        };
        xhr.send();
    }

    // Track how many components have loaded
    var loaded = 0;
    function onComponentLoaded() {
        loaded++;
        // Once both header and footer are loaded, initialize script.js features
        if (loaded === 2) {
            // Set active nav link
            var navLinks = document.querySelectorAll('.navbar__link[data-page]');
            navLinks.forEach(function (link) {
                if (link.getAttribute('data-page') === pageKey) {
                    link.classList.add('navbar__link--active');
                }
            });

            // Re-initialize script.js interactive features that depend on DOM elements
            // Dispatch a custom event so script.js can re-bind
            document.dispatchEvent(new Event('componentsLoaded'));
        }
    }

    if (headerPlaceholder) {
        loadComponent('header.html', headerPlaceholder, onComponentLoaded);
    } else {
        loaded++;
    }

    if (footerPlaceholder) {
        loadComponent('footer.html', footerPlaceholder, onComponentLoaded);
    } else {
        loaded++;
    }
})();
