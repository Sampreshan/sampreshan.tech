/**
 * Anusaran walk.
 *
 * The paduka pair enters at the very edge of the button and walks slowly to
 * the other edge. Behind it, a footprint appears under each letter slot, and a
 * moment later the letter types out of that footprint (typewriter caret), so
 * SAMPRESHAN rises from the paduka's prints. When the paduka has left the
 * button the full word stays for HOLD ms, the button goes blank, and the walk
 * repeats from the other side. Loops forever.
 */
(function () {
    'use strict';

    var WALK = 5200;  // ms for the paduka to cross the whole button
    var TYPE = 420;   // ms between footprint and its letter
    var HOLD = 3000;  // full word on screen
    var BLANK = 900;  // empty button before the next walk

    function run(root) {
        if (root.dataset.spAnuOn) { return; }
        root.dataset.spAnuOn = '1';
        var foot = root.querySelector('.sp-anu__paduka');
        var caret = document.createElement('span');
        caret.className = 'sp-anu__caret';
        caret.setAttribute('aria-hidden', 'true');
        root.appendChild(caret);
        var rtl = false;

        function walk() {
            var ls = Array.prototype.slice.call(root.querySelectorAll('.sp-anu__l'));
            if (!ls.length || !root.isConnected) { return; }
            ls.forEach(function (l) { l.classList.remove('is-on', 'is-print'); });
            root.classList.remove('is-done', 'is-typing');
            root.classList.toggle('is-rtl', rtl);

            var box = root.getBoundingClientRect();
            var W = box.width;
            var fw = foot.offsetWidth;
            var slots = ls.map(function (l) {
                var b = l.getBoundingClientRect();
                return { el: l, cx: b.left - box.left + b.width / 2, left: b.left - box.left, right: b.right - box.left, done: false };
            });
            if (rtl) { slots.reverse(); }
            var from = rtl ? W : -fw;
            var to = rtl ? -fw : W;
            var t0 = null;
            root.classList.add('is-walking');

            function typed(s) {
                s.el.classList.add('is-on');
                root.classList.add('is-typing');
                caret.style.transform = 'translateX(' + Math.round(rtl ? s.left - 2 : s.right) + 'px)';
            }

            function frame(ts) {
                if (t0 === null) { t0 = ts; }
                var p = Math.min(1, (ts - t0) / WALK);
                var x = from + (to - from) * p;
                foot.style.transform = 'translateX(' + Math.round(x) + 'px)';
                var heel = rtl ? x + fw * 0.7 : x + fw * 0.3;
                slots.forEach(function (s) {
                    if (s.done) { return; }
                    if (rtl ? heel <= s.cx : heel >= s.cx) {
                        s.done = true;
                        s.el.classList.add('is-print');
                        setTimeout(function () { typed(s); }, TYPE);
                    }
                });
                if (p < 1) { requestAnimationFrame(frame); return; }
                root.classList.remove('is-walking');
                setTimeout(function () {
                    root.classList.remove('is-typing');
                    root.classList.add('is-done');
                    setTimeout(function () {
                        ls.forEach(function (l) { l.classList.remove('is-on', 'is-print'); });
                        root.classList.remove('is-done');
                        rtl = !rtl;
                        setTimeout(walk, BLANK);
                    }, HOLD);
                }, TYPE + 200);
            }
            requestAnimationFrame(frame);
        }
        walk();
    }

    function init(scope) {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
        (scope || document).querySelectorAll('[data-sp-anu-walk]').forEach(run);
    }

    window.spAnusaranWalk = init;
    document.addEventListener('sp:infinite-loaded', function (e) { init(e.detail && e.detail.list); });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); });
    } else {
        init();
    }
})();
