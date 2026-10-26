/**
 * Dharma Calendar: month grid + day list + Peeth / Acharya filter.
 * Data comes from the JSON block rendered by sp_dcal_render() (inc/dharma-calendar.php).
 */
(function () {
    'use strict';

    var KIND = { event: 'Event', video: 'Video', news: 'News' };
    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) { n.className = cls; }
        if (text) { n.textContent = text; }
        return n;
    }
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function ymd(y, m, d) { return y + '-' + pad(m + 1) + '-' + pad(d); }
    function nice(s) {
        var p = s.split('-');
        return parseInt(p[2], 10) + ' ' + MONTHS[parseInt(p[1], 10) - 1].slice(0, 3) + ' ' + p[0];
    }
    function safeUrl(u) { return /^https?:\/\//i.test(u || '') ? u : ''; }

    function entry(it, today) {
        var a = el('article', 'sp-dcal__item');
        var thumb = safeUrl(it.thumb);
        if (thumb) {
            var img = el('img', 'sp-dcal__thumb');
            img.src = thumb; img.alt = ''; img.loading = 'lazy'; img.width = 160; img.height = 90;
            a.appendChild(img);
        }
        var body = el('div');
        var meta = el('p', 'sp-dcal__meta');
        meta.appendChild(el('span', 'sp-dcal__tag sp-dcal__tag--' + (KIND[it.kind] ? it.kind : 'news'), KIND[it.kind] || 'News'));
        if (it.date < today) { meta.appendChild(el('span', 'sp-dcal__tag sp-dcal__tag--past', 'Past')); }
        if (it.date > today) { meta.appendChild(el('span', 'sp-dcal__tag sp-dcal__tag--soon', 'Upcoming')); }
        meta.appendChild(document.createTextNode(' ' + nice(it.date)));
        body.appendChild(meta);
        var h = el('h3');
        var link = el('a', '', it.title);
        link.href = safeUrl(it.url) || safeUrl(it.link) || '#';
        link.target = '_blank'; link.rel = 'nofollow noopener';
        h.appendChild(link);
        body.appendChild(h);
        body.appendChild(el('p', 'sp-dcal__src', it.profile + (it.source ? ' · ' + it.source : '')));
        a.appendChild(body);
        return a;
    }

    function init(root) {
        var dataNode = root.querySelector('.sp-dcal__data');
        if (!dataNode) { return; }
        var data;
        try { data = JSON.parse(dataNode.textContent); } catch (e) { return; }
        var all = data.items || [];
        var today = data.today;
        var calNode = root.querySelector('[data-sp-dcal-cal]');
        var listNode = root.querySelector('[data-sp-dcal-list]');
        var titleNode = root.querySelector('[data-sp-dcal-title]');
        var filterNode = root.querySelector('[data-sp-dcal-filter]');
        var t = today.split('-');
        var state = { y: +t[0], m: +t[1] - 1, day: '', key: '' };

        function items() {
            return state.key ? all.filter(function (it) { return (it.keys || []).indexOf(state.key) !== -1; }) : all;
        }

        function renderList() {
            var list = items();
            listNode.textContent = '';
            var show;
            if (state.day) {
                show = list.filter(function (it) { return it.date === state.day; });
                titleNode.textContent = nice(state.day);
                var back = el('button', 'sp-dcal__back', 'Show upcoming and recent');
                back.type = 'button';
                back.addEventListener('click', function () { state.day = ''; render(); });
                listNode.appendChild(back);
            } else {
                var up = list.filter(function (it) { return it.date >= today; }).sort(function (a, b) { return a.date < b.date ? -1 : 1; });
                var past = list.filter(function (it) { return it.date < today; }).sort(function (a, b) { return a.date > b.date ? -1 : 1; });
                show = up.slice(0, 6).concat(past.slice(0, 10));
                titleNode.textContent = 'Upcoming and recent';
            }
            if (!show.length) {
                listNode.appendChild(el('p', 'sp-dharma-muted', 'No verified entry for this selection yet.'));
                return;
            }
            show.forEach(function (it) { listNode.appendChild(entry(it, today)); });
        }

        function renderCal() {
            var list = items();
            var byDay = {};
            list.forEach(function (it) { byDay[it.date] = (byDay[it.date] || 0) + 1; });
            calNode.textContent = '';
            var nav = el('div', 'sp-dcal__nav');
            var prev = el('button', '', '\u2039'); prev.type = 'button'; prev.setAttribute('aria-label', 'Previous month');
            var next = el('button', '', '\u203A'); next.type = 'button'; next.setAttribute('aria-label', 'Next month');
            prev.addEventListener('click', function () { state.m--; if (state.m < 0) { state.m = 11; state.y--; } renderCal(); });
            next.addEventListener('click', function () { state.m++; if (state.m > 11) { state.m = 0; state.y++; } renderCal(); });
            nav.appendChild(prev);
            nav.appendChild(el('strong', '', MONTHS[state.m] + ' ' + state.y));
            nav.appendChild(next);
            calNode.appendChild(nav);

            var grid = el('div', 'sp-dcal__days');
            grid.setAttribute('role', 'grid');
            ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(function (d) { grid.appendChild(el('span', 'sp-dcal__dn', d.charAt(0))); });
            var first = new Date(state.y, state.m, 1).getDay();
            var days = new Date(state.y, state.m + 1, 0).getDate();
            for (var i = 0; i < first; i++) { grid.appendChild(el('span')); }
            for (var d = 1; d <= days; d++) {
                var key = ymd(state.y, state.m, d);
                var n = byDay[key] || 0;
                var cell = el(n ? 'button' : 'span', 'sp-dcal__d', String(d));
                if (key === today) { cell.className += ' is-today'; }
                if (n) {
                    cell.type = 'button';
                    cell.className += ' has-ev' + (key < today ? ' is-past' : '') + (key === state.day ? ' is-sel' : '');
                    cell.setAttribute('aria-label', nice(key) + ': ' + n + (n > 1 ? ' entries' : ' entry'));
                    cell.appendChild(el('i', 'sp-dcal__dot'));
                    (function (k) {
                        cell.addEventListener('click', function () { state.day = k; render(); });
                    }(key));
                }
                grid.appendChild(cell);
            }
            calNode.appendChild(grid);
            var legend = el('p', 'sp-dcal__legend');
            legend.appendChild(el('span', 'is-up', 'Upcoming'));
            legend.appendChild(el('span', 'is-past', 'Past (archive)'));
            legend.appendChild(el('span', 'is-today', 'Today'));
            calNode.appendChild(legend);
        }

        function render() { renderCal(); renderList(); }

        if (filterNode) {
            filterNode.addEventListener('change', function () { state.key = filterNode.value; state.day = ''; render(); });
        }
        render();
    }

    function boot() {
        var nodes = document.querySelectorAll('[data-sp-dcal]');
        for (var i = 0; i < nodes.length; i++) { init(nodes[i]); }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}());
