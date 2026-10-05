/**
 * Header notification bell + AJAX Anusaran (follow) toggle.
 * Loaded for logged-in members only. Data comes from admin-ajax because
 * pages may be served from the per-user page cache.
 */
(function () {
    'use strict';

    var cfg = window.SampreshanNotif;
    if (!cfg) { return; }

    function post(action, data) {
        var body = new FormData();
        body.append('action', action);
        Object.keys(data || {}).forEach(function (k) { body.append(k, data[k]); });
        return fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
            .then(function (r) { return r.json(); });
    }

    var root = document.querySelector('[data-sp-notif]');
    if (root) {
        var toggle = root.querySelector('[data-sp-notif-toggle]');
        var panel  = root.querySelector('.sp-notif__panel');
        var list   = root.querySelector('[data-sp-notif-list]');
        var empty  = root.querySelector('[data-sp-notif-empty]');
        var badge  = root.querySelector('[data-sp-notif-badge]');
        var unread = 0;

        var render = function (res) {
            if (!res || !res.success) { return; }
            unread = res.data.unread;
            badge.hidden = unread < 1;
            list.textContent = '';
            res.data.items.forEach(function (item) {
                var li = document.createElement('li');
                li.className = 'sp-notif__item' + (item.new ? ' is-new' : '');
                var a = document.createElement('a');
                a.href = item.link;
                a.textContent = item.text;
                var time = document.createElement('small');
                time.textContent = item.ago;
                li.appendChild(a);
                li.appendChild(time);
                list.appendChild(li);
            });
            empty.hidden = res.data.items.length > 0;
        };

        var close = function () {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        };

        post('sp_notifications_list', { nonce: cfg.nonce }).then(render).catch(function () {});

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!panel.hidden) { close(); return; }
            panel.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
            if (unread > 0) {
                post('sp_notifications_read', { nonce: cfg.nonce }).then(function () {
                    unread = 0;
                    badge.hidden = true;
                }).catch(function () {});
            }
        });
        document.addEventListener('click', function (e) {
            if (!root.contains(e.target)) { close(); }
        });
        document.addEventListener('keydown', function (e) {
            if ('Escape' === e.key && !panel.hidden) { close(); toggle.focus(); }
        });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target.closest ? e.target.closest('[data-sp-dharma-follow]') : null;
        if (!form) { return; }
        e.preventDefault();
        var btn = form.querySelector('button');
        btn.disabled = true;
        post('sp_toggle_dharma_follow', {
            profile_id: form.elements.profile_id.value,
            _wpnonce: form.elements._wpnonce.value
        }).then(function (res) {
            btn.disabled = false;
            if (!res || !res.success) { form.submit(); return; }
            btn.textContent = res.data.label;
            btn.classList.toggle('is-following', res.data.following);
            var count = document.querySelector('[data-sp-follow-count]');
            if (count && res.data.countText) { count.textContent = res.data.countText; }
        }).catch(function () { btn.disabled = false; form.submit(); });
    });
})();
