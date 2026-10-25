/**
 * Guru Parampara tree: rotate (present-first <-> founder-first) and reveal.
 * Order preference is remembered per browser.
 */
(function () {
    'use strict';

    var KEY = 'spParamparaOrder';
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function setOrder(tree, order) {
        tree.setAttribute('data-order', order);
        var btn = tree.querySelector('[data-sp-pp-rotate]');
        var label = tree.querySelector('[data-sp-pp-rotate-label]');
        if (btn) {
            btn.setAttribute('aria-pressed', order === 'founder' ? 'true' : 'false');
        }
        if (label) {
            label.textContent = order === 'founder' ? label.getAttribute('data-label-founder') : label.getAttribute('data-label-present');
        }
    }

    function reveal(tree) {
        var nodes = tree.querySelectorAll('.sp-pp__node');
        if (reduce || !('IntersectionObserver' in window)) {
            return;
        }
        tree.classList.add('is-ready');
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-in');
                    io.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0 });
        nodes.forEach(function (n) { io.observe(n); });
        // Folded nodes become visible only when opened.
        tree.querySelectorAll('.sp-pp__fold details').forEach(function (d) {
            d.addEventListener('toggle', function () {
                if (d.open) {
                    d.querySelectorAll('.sp-pp__node').forEach(function (n) { n.classList.add('is-in'); });
                }
            });
        });
    }

    function init(tree) {
        var saved = null;
        try { saved = window.localStorage.getItem(KEY); } catch (e) { /* storage blocked */ }
        setOrder(tree, saved === 'founder' ? 'founder' : 'present');
        reveal(tree);

        var btn = tree.querySelector('[data-sp-pp-rotate]');
        var stage = tree.querySelector('.sp-pp__stage');
        if (!btn || !stage) {
            return;
        }
        btn.addEventListener('click', function () {
            var next = tree.getAttribute('data-order') === 'founder' ? 'present' : 'founder';
            try { window.localStorage.setItem(KEY, next); } catch (e) { /* storage blocked */ }
            if (reduce) {
                setOrder(tree, next);
                return;
            }
            stage.classList.remove('is-flipping');
            void stage.offsetWidth;
            stage.classList.add('is-flipping');
            window.setTimeout(function () {
                setOrder(tree, next);
                tree.querySelectorAll('.sp-pp__node').forEach(function (n) { n.classList.add('is-in'); });
            }, 320);
            stage.addEventListener('animationend', function done() {
                stage.classList.remove('is-flipping');
                stage.removeEventListener('animationend', done);
            });
            var top = tree.getBoundingClientRect().top + window.pageYOffset - 90;
            if (window.pageYOffset > top) {
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    }

    function boot() {
        document.querySelectorAll('[data-sp-pp]').forEach(init);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}());
