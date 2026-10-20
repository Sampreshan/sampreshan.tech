/* Sampreshan Shankh: click / tap to "blow" (waves + recoil, optional tone). */
(function () {
    'use strict';
    var ctx = null;

    // Synthesized conch-like tone: breathy low fundamental with a slow swell.
    function tone() {
        var AC = window.AudioContext || window.webkitAudioContext;
        if (!AC) { return; }
        ctx = ctx || new AC();
        var t = ctx.currentTime, dur = 1.6;
        var out = ctx.createGain();
        out.gain.setValueAtTime(0.0001, t);
        out.gain.exponentialRampToValueAtTime(0.22, t + 0.25);
        out.gain.setValueAtTime(0.22, t + dur - 0.5);
        out.gain.exponentialRampToValueAtTime(0.0001, t + dur);
        var lp = ctx.createBiquadFilter();
        lp.type = 'lowpass';
        lp.frequency.value = 1400;
        lp.connect(out);
        out.connect(ctx.destination);
        [[233, 1], [466, 0.45], [699, 0.2], [932, 0.08]].forEach(function (h) {
            var o = ctx.createOscillator(), g = ctx.createGain();
            o.type = 'sawtooth';
            o.frequency.setValueAtTime(h[0] * 0.97, t);
            o.frequency.linearRampToValueAtTime(h[0], t + 0.3);
            g.gain.value = h[1] * 0.25;
            o.connect(g);
            g.connect(lp);
            o.start(t);
            o.stop(t + dur);
        });
    }

    function blow(el) {
        el.classList.remove('is-blowing');
        void el.offsetWidth; // restart animation on repeat clicks
        el.classList.add('is-blowing');
        if (el.getAttribute('data-sound') === '1') {
            try { tone(); } catch (e) { /* audio unavailable */ }
        }
        clearTimeout(el._spT);
        el._spT = setTimeout(function () { el.classList.remove('is-blowing'); }, 1500);
    }

    function init() {
        Array.prototype.forEach.call(document.querySelectorAll('.sp-shankh'), function (el) {
            el.addEventListener('click', function () { blow(el); });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
