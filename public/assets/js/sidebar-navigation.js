(function () {
    'use strict';
    if (window.sampathSidebarNavigation) return;
    window.sampathSidebarNavigation = true;

    function submenu(link) {
        var next = link.nextElementSibling;
        return next && next.tagName === 'UL' ? next : null;
    }
    function setOpen(link, open) {
        var list = submenu(link);
        if (!list) return;
        list.style.display = open ? 'block' : 'none';
        link.classList.toggle('subdrop', open);
        link.setAttribute('aria-expanded', String(open));
    }
    function initialize() {
        document.querySelectorAll('#sidebar-menu a').forEach(function (link) {
            var list = submenu(link);
            if (!list) return;
            link.parentElement.classList.add('submenu');
            setOpen(link, !!list.querySelector('.active') || list.style.display === 'block');
        });
    }
    // Capture only menu controls so old per-page jQuery handlers cannot toggle
    // the same control a second time. Normal destination links keep navigation.
    document.addEventListener('click', function (event) {
        var link = event.target.closest('#sidebar-menu a');
        if (link && submenu(link)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            var open = link.getAttribute('aria-expanded') !== 'true';
            Array.from(link.parentElement.parentElement.children).forEach(function (item) {
                var sibling = item.querySelector(':scope > a');
                if (sibling && sibling !== link) setOpen(sibling, false);
            });
            setOpen(link, open);
            return;
        }
        if (event.target.closest('#toggle_btn, #mobile_btn')) {
            event.preventDefault();
            event.stopImmediatePropagation();
            if (window.matchMedia('(max-width: 991px)').matches) {
                document.querySelectorAll('.main-wrapper').forEach(function (wrapper) { wrapper.classList.toggle('slide-nav'); });
                document.documentElement.classList.toggle('menu-opened');
                var sidebar = document.getElementById('sidebar');
                if (sidebar) sidebar.classList.toggle('sidebar-mobile-open');
            } else {
                document.body.classList.toggle('mini-sidebar');
            }
        }
    }, true);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
