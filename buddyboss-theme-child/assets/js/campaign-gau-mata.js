/**
 * Gau Mata campaign: flowers drift in from four sides to the centre.
 * Uses SampreShan kit icons (no emoji). Skipped for reduced motion.
 */
(function () {
    'use strict';
    var box = document.querySelector('[data-sp-flowers]');
    if (!box || !window.spCampaign || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        return;
    }
    var icons = window.spCampaign.flowers || [];
    var sides = ['Top', 'Bottom', 'Left', 'Right'];
    if (!icons.length) {
        return;
    }
    function flower() {
        if (document.hidden || box.childElementCount > 24) {
            return;
        }
        var el = document.createElement('div');
        var img = document.createElement('img');
        var side = sides[Math.floor(Math.random() * sides.length)];
        var duration = (Math.random() * 1.5 + 3).toFixed(2);
        el.className = 'magic-flower';
        img.src = icons[Math.floor(Math.random() * icons.length)];
        img.alt = '';
        el.appendChild(img);
        el.style.setProperty('--start-pos', (Math.floor(Math.random() * 80) + 10) + '%');
        el.style.animation = 'spc-flyToCenter' + side + ' ' + duration + 's ease-in-out forwards';
        box.appendChild(el);
        window.setTimeout(function () { el.remove(); }, duration * 1000);
    }
    window.setInterval(flower, 400);
}());
