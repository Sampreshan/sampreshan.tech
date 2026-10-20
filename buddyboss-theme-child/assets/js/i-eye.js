/* Sampreshan I-Eye mark: blink on click/tap, idle blinks, iris follows pointer. */
(function () {
    'use strict';
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function blink(el, twice) {
        if (el.classList.contains('is-blinking')) { return; }
        el.classList.add('is-blinking');
        setTimeout(function () {
            el.classList.remove('is-blinking');
            if (twice) { setTimeout(function () { blink(el, false); }, 140); }
        }, 130);
    }

    function init() {
        var eyes = document.querySelectorAll('.sp-i-eye');
        if (!eyes.length) { return; }

        Array.prototype.forEach.call(eyes, function (el) {
            el.addEventListener('click', function () { blink(el, true); });

            if (reduce) { return; }
            (function idle() {
                setTimeout(function () {
                    if (!document.hidden) { blink(el, Math.random() < 0.25); }
                    idle();
                }, 2800 + Math.random() * 3500);
            })();
        });

        if (reduce) { return; }
        var raf = 0;
        document.addEventListener('pointermove', function (e) {
            if (raf) { return; }
            raf = requestAnimationFrame(function () {
                raf = 0;
                Array.prototype.forEach.call(eyes, function (el) {
                    var look = el.querySelector('.sp-i-eye__look');
                    if (!look) { return; }
                    var r = el.getBoundingClientRect();
                    var dx = e.clientX - (r.left + r.width / 2);
                    var dy = e.clientY - (r.top + r.height / 2);
                    var d = Math.sqrt(dx * dx + dy * dy) || 1;
                    var k = Math.min(1, d / 300);
                    look.style.transform = 'translate(' + (dx / d * 14 * k).toFixed(1) + 'px,' + (dy / d * 7 * k).toFixed(1) + 'px)';
                });
            });
        }, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
