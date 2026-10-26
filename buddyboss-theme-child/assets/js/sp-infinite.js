/**
 * Site-wide infinite scroll.
 *
 * Any list rendered with WordPress pagination (a.next.page-numbers) loads the
 * next page automatically as the reader scrolls near the end. The list is the
 * element marked [data-sp-infinite]; otherwise the element just before the
 * pagination block. Without JS the normal page links keep working.
 */
(function () {
    'use strict';
    if (!('IntersectionObserver' in window) || !window.fetch) { return; }

    function pagerOf(next) {
        return next.closest('nav, .navigation, .pagination, .sp-users__pagination') || next.parentNode;
    }

    function listFor(pager) {
        var marked = document.querySelectorAll('[data-sp-infinite]');
        for (var i = 0; i < marked.length; i++) {
            if (marked[i].compareDocumentPosition(pager) & Node.DOCUMENT_POSITION_FOLLOWING) { return marked[i]; }
        }
        var prev = pager.previousElementSibling;
        return prev && prev.children.length ? prev : null;
    }

    function sameList(doc, list) {
        if (list.hasAttribute('data-sp-infinite')) { return doc.querySelector('[data-sp-infinite]'); }
        var cls = list.className && String(list.className).split(/\s+/)[0];
        return cls ? doc.querySelector('.' + cls) : null;
    }

    function init(next) {
        var pager = pagerOf(next);
        var list = listFor(pager);
        if (!list) { return; }
        var url = next.href;
        var busy = false;
        var sentinel = document.createElement('div');
        sentinel.className = 'sp-infinite__status';
        sentinel.setAttribute('aria-live', 'polite');
        pager.parentNode.insertBefore(sentinel, pager);
        pager.hidden = true;

        var io = new IntersectionObserver(function (entries) {
            if (!entries[0].isIntersecting || busy || !url) { return; }
            busy = true;
            sentinel.textContent = 'Loading more';
            fetch(url, { credentials: 'same-origin' })
                .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.text(); })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var incoming = sameList(doc, list);
                    if (incoming) {
                        while (incoming.firstElementChild) { list.appendChild(document.adoptNode(incoming.firstElementChild)); }
                    }
                    var more = doc.querySelector('a.next.page-numbers');
                    url = more ? more.href : null;
                    sentinel.textContent = '';
                    if (!url) { io.disconnect(); sentinel.remove(); }
                    document.dispatchEvent(new CustomEvent('sp:infinite-loaded', { detail: { list: list } }));
                    busy = false;
                })
                .catch(function () {
                    // Fall back to the normal page links.
                    io.disconnect(); sentinel.remove(); pager.hidden = false;
                });
        }, { rootMargin: '600px 0px' });
        io.observe(sentinel);
    }

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('a.next.page-numbers'), init);
    });
}());
