/**
 * Site-wide button slide effect.
 * Adds a <span class="sp-slide"> inside every CTA / button so the hover fill
 * slides through the inside of the button. New buttons added later are
 * picked up automatically. Opt out with class "sp-noslide".
 */
(function () {
    'use strict';

    var SEL = [
        'a.button', 'a.btn', 'a[class*="-btn"]', 'a[class*="__btn"]', 'a[class*="button"]',
        'a[class*="cta"]', 'a[class*="__play"]', 'a[role="button"]',
        'button', '.bb-button', '.wp-block-button__link'
    ].join(',');

    function eligible(el) {
        if (el.classList.contains('sp-slidable') || el.closest('.sp-noslide, #wpadminbar, .mfp-close')) { return false; }
        if (!el.textContent || el.textContent.trim().length < 2) { return false; }
        var r = el.getBoundingClientRect();
        return r.width >= 48 && r.height >= 24;
    }

    function wire(el) {
        if (!eligible(el)) { return; }
        var cs = getComputedStyle(el);
        if (cs.position === 'static') { el.style.position = 'relative'; }
        el.classList.add('sp-slidable');
        var s = document.createElement('span');
        s.className = 'sp-slide';
        s.setAttribute('aria-hidden', 'true');
        el.insertBefore(s, el.firstChild);
    }

    function scan(root) {
        if (root.matches && root.matches(SEL)) { wire(root); }
        if (root.querySelectorAll) { root.querySelectorAll(SEL).forEach(wire); }
    }

    function init() {
        scan(document.body);
        var queued = false;
        new MutationObserver(function () {
            if (queued) { return; }
            queued = true;
            requestAnimationFrame(function () { queued = false; scan(document.body); });
        }).observe(document.body, { childList: true, subtree: true });
        // Buttons hidden at load (other screens, menus) get wired on first hover.
        document.addEventListener('pointerover', function (e) {
            var t = e.target.closest && e.target.closest(SEL);
            if (t) { wire(t); }
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
