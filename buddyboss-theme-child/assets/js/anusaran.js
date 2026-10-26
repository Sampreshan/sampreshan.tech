/**
 * Anusaran walk: the paduka pair steps across the button; each step leaves a
 * footprint dash that becomes the next letter of SAMPRESHAN. At the far edge
 * the full word stands, then the walk repeats in the other direction.
 */
(function () {
    'use strict';

    var STEP = 260;   // ms per letter
    var HOLD = 1800;  // full word on screen
    var GAP = 500;    // blank before the next walk

    function run(root) {
        if (root.dataset.spAnuOn) { return; }
        root.dataset.spAnuOn = '1';
        var foot = root.querySelector('.sp-anu__paduka');
        var rtl = false;

        function letters() { return root.querySelectorAll('.sp-anu__l'); }

        function place(el, ls) {
            var r = root.getBoundingClientRect();
            var b = el.getBoundingClientRect();
            var x = rtl ? b.left - r.left - foot.offsetWidth * 0.2 : b.right - r.left - foot.offsetWidth * 0.8;
            foot.style.transform = 'translateX(' + Math.round(x) + 'px)';
        }

        function walk() {
            var ls = Array.prototype.slice.call(letters());
            ls.forEach(function (l) { l.classList.remove('is-on', 'is-print'); });
            root.classList.remove('is-done');
            root.classList.toggle('is-rtl', rtl);
            var order = rtl ? ls.slice().reverse() : ls;
            if (!order.length) { return; }
            var r = root.getBoundingClientRect();
            foot.style.transition = 'none';
            foot.style.transform = 'translateX(' + (rtl ? Math.round(r.width) : -foot.offsetWidth) + 'px)';
            void foot.offsetWidth;
            foot.style.transition = '';
            root.classList.add('is-walking');

            order.forEach(function (l, i) {
                setTimeout(function () {
                    place(l);
                    l.classList.add('is-print');
                }, i * STEP);
                setTimeout(function () { l.classList.add('is-on'); }, i * STEP + STEP * 0.8);
            });
            var end = order.length * STEP;
            setTimeout(function () {
                foot.style.transform = 'translateX(' + (rtl ? -foot.offsetWidth : Math.round(r.width)) + 'px)';
            }, end);
            setTimeout(function () {
                root.classList.remove('is-walking');
                root.classList.add('is-done');
            }, end + STEP);
            setTimeout(function () {
                order.forEach(function (l) { l.classList.remove('is-on', 'is-print'); });
                root.classList.remove('is-done');
                rtl = !rtl;
                setTimeout(walk, GAP);
            }, end + STEP + HOLD);
        }
        walk();
    }

    function init(scope) {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
        (scope || document).querySelectorAll('[data-sp-anu-walk]').forEach(run);
    }

    window.spAnusaranWalk = init;
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); });
    } else {
        init();
    }
})();
