/**
 * Sangyani Sanatan Quiz client.
 * The server owns scoring; this file only renders cards, runs the timer,
 * plays feedback sounds and draws the certificate.
 */
(function () {
    'use strict';

    var cfg = window.spQuiz;
    var root = document.querySelector('.sp-quiz');
    if (!cfg || !root) { return; }

    var STORE = 'spQuizToken';
    var MUTE = 'spQuizMuted';
    var $ = function (sel) { return root.querySelector(sel); };

    var run = null;
    var question = null;
    var pending = null; // response waiting for "Next"
    var timerId = null;
    var deadline = 0;
    var busy = false;
    var muted = localStorage.getItem(MUTE) === '1';

    /* ---------- helpers ---------- */

    function show(name) {
        root.querySelectorAll('.sp-quiz__screen').forEach(function (s) {
            s.classList.toggle('is-active', s.getAttribute('data-screen') === name);
        });
        window.scrollTo({ top: root.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
    }

    function api(path, body, method) {
        return fetch(cfg.api + path, {
            method: method || 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
            body: method === 'GET' ? undefined : JSON.stringify(body || {})
        }).then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok) { throw new Error((data && data.message) || 'Something went wrong.'); }
                return data;
            });
        });
    }

    function error(msg) {
        var el = $('[data-error]');
        el.textContent = msg;
        el.hidden = !msg;
    }

    /* ---------- sound ---------- */

    var voices = {
        correct: new Audio(cfg.audio.correct),
        wrong: new Audio(cfg.audio.wrong)
    };
    voices.correct.volume = 0.9;
    voices.wrong.volume = 0.55;
    var ctx = null;

    function chime(notes, type, gain) {
        try {
            ctx = ctx || new (window.AudioContext || window.webkitAudioContext)();
            notes.forEach(function (freq, i) {
                var o = ctx.createOscillator();
                var g = ctx.createGain();
                var t = ctx.currentTime + i * 0.12;
                o.type = type;
                o.frequency.value = freq;
                g.gain.setValueAtTime(0, t);
                g.gain.linearRampToValueAtTime(gain, t + 0.02);
                g.gain.exponentialRampToValueAtTime(0.0001, t + 0.6);
                o.connect(g).connect(ctx.destination);
                o.start(t);
                o.stop(t + 0.65);
            });
        } catch (e) { /* audio not available */ }
    }

    function play(kind) {
        if (muted) { return; }
        if (kind === 'correct') {
            chime([523.25, 659.25, 783.99], 'sine', 0.18);
        } else if (kind === 'wrong') {
            chime([329.63, 261.63], 'triangle', 0.08);
        } else {
            chime([523.25, 659.25, 783.99, 1046.5], 'sine', 0.16);
            return;
        }
        var v = voices[kind];
        v.currentTime = 0;
        setTimeout(function () { v.play().catch(function () { }); }, 250);
    }

    function paintMute() {
        var b = $('[data-mute]');
        b.textContent = muted ? 'Sound off' : 'Sound on';
        b.setAttribute('aria-pressed', muted ? 'true' : 'false');
    }

    $('[data-mute]').addEventListener('click', function () {
        muted = !muted;
        localStorage.setItem(MUTE, muted ? '1' : '0');
        paintMute();
    });
    paintMute();

    /* ---------- HUD + card ---------- */

    function hud() {
        $('[data-hud-level]').textContent = 'Level ' + run.level + ' of ' + run.levels;
        $('[data-hud-title]').textContent = run.levelTitle;
        $('[data-hud-score]').textContent = run.score;
        var pct = run.levelTotal ? Math.round(run.levelDone / run.levelTotal * 100) : 0;
        var bar = $('[data-progress]');
        bar.setAttribute('aria-valuenow', pct);
        bar.firstElementChild.style.width = pct + '%';
        $('[data-progress-text]').textContent = run.levelDone + ' of ' + run.levelTotal + ' questions cleared in this level';
    }

    function renderQuestion() {
        var card = $('[data-card]');
        card.classList.remove('is-correct', 'is-wrong');
        card.classList.remove('is-flip');
        void card.offsetWidth;
        card.classList.add('is-flip');
        $('[data-question]').textContent = question.text;
        $('[data-feedback]').textContent = '';
        $('[data-feedback]').className = 'sp-quiz__feedback';
        $('[data-next]').hidden = true;
        var box = $('[data-options]');
        box.innerHTML = '';
        question.options.forEach(function (text, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'sp-quiz__option';
            b.setAttribute('data-choice', i);
            var key = document.createElement('span');
            key.className = 'sp-quiz__key';
            key.textContent = String(i + 1);
            var label = document.createElement('span');
            label.textContent = text;
            b.appendChild(key);
            b.appendChild(label);
            b.addEventListener('click', function () { answer(i); });
            box.appendChild(b);
        });
        hud();
        startTimer();
        $('[data-question]').focus({ preventScroll: true });
    }

    function startTimer() {
        stopTimer();
        var total = run.seconds * 1000;
        deadline = Date.now() + total;
        var bar = $('[data-timer-bar]');
        var txt = $('[data-timer]');
        timerId = setInterval(function () {
            var left = Math.max(0, deadline - Date.now());
            bar.style.width = (left / total * 100) + '%';
            bar.parentNode.classList.toggle('is-low', left < 8000);
            txt.textContent = Math.ceil(left / 1000);
            if (left <= 0) {
                stopTimer();
                answer(-1);
            }
        }, 100);
    }

    function stopTimer() {
        if (timerId) { clearInterval(timerId); timerId = null; }
    }

    /* ---------- flow ---------- */

    function answer(choice) {
        if (busy || pending) { return; }
        busy = true;
        stopTimer();
        var buttons = root.querySelectorAll('.sp-quiz__option');
        buttons.forEach(function (b) { b.disabled = true; });
        api('answer', { token: run.token, choice: choice }).then(function (res) {
            busy = false;
            pending = res;
            run = res.run;
            var card = $('[data-card]');
            buttons[res.answer].classList.add('is-right');
            var fb = $('[data-feedback]');
            if (res.correct) {
                card.classList.add('is-correct');
                fb.textContent = 'Uttam! Excellent, keep it up. +' + res.points + ' points';
                fb.classList.add('is-good');
                play('correct');
            } else {
                card.classList.add('is-wrong');
                if (choice >= 0) { buttons[choice].classList.add('is-wrong'); }
                fb.textContent = (choice < 0 ? 'Time is up. ' : '') + 'Satya Pratiksharat: the truth awaits you. ' + res.points + ' points. This card will return later in the level.';
                fb.classList.add('is-soft');
                play('wrong');
            }
            $('[data-hud-score]').textContent = run.score;
            var next = $('[data-next]');
            next.textContent = res.cleared ? (run.status === 'completed' ? 'Receive your certificate' : 'See level result') : 'Next card';
            next.hidden = false;
            next.focus({ preventScroll: true });
        }).catch(function (e) {
            busy = false;
            alert(e.message);
            reset();
        });
    }

    function next() {
        if (!pending) { return; }
        var res = pending;
        pending = null;
        if (run.status === 'completed') {
            localStorage.removeItem(STORE);
            play('cert');
            showCertificate(run.cert, true);
            return;
        }
        question = res.question;
        if (res.cleared) {
            $('[data-level-heading]').textContent = 'Level ' + run.levelsCleared + ' complete';
            $('[data-level-summary]').textContent = 'Score so far: ' + run.score + ' points. Next: level ' + run.level + ', ' + run.levelTitle + '.';
            play('cert');
            show('level');
            return;
        }
        renderQuestion();
    }

    function begin(res) {
        run = res.run;
        question = res.question;
        localStorage.setItem(STORE, run.token);
        show('play');
        renderQuestion();
    }

    function reset() {
        stopTimer();
        pending = null;
        run = null;
        localStorage.removeItem(STORE);
        show('start');
    }

    $('[data-next]').addEventListener('click', next);
    $('[data-continue]').addEventListener('click', function () { show('play'); renderQuestion(); });
    $('[data-quit]').addEventListener('click', function () {
        if (!run || !window.confirm('End this game? Your progress in it will be closed.')) { return; }
        api('restart', { token: run.token }).finally(reset);
    });

    document.addEventListener('keydown', function (e) {
        if (!$('[data-screen="play"]').classList.contains('is-active')) { return; }
        if (/^[1-4]$/.test(e.key) && !pending) { answer(Number(e.key) - 1); }
        if (e.key === 'Enter' && pending) { e.preventDefault(); next(); }
    });

    var form = $('[data-start-form]');
    var nameInput = form.querySelector('input');
    nameInput.value = cfg.name || '';
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        error('');
        var btn = $('[data-start-btn]');
        btn.disabled = true;
        api('start', { name: nameInput.value, token: localStorage.getItem(STORE) || '' })
            .then(begin)
            .catch(function (err) { error(err.message); })
            .finally(function () { btn.disabled = false; });
    });

    var saved = localStorage.getItem(STORE);
    if (saved || cfg.loggedIn) {
        var note = $('[data-resume-note]');
        note.textContent = 'If you have a game in progress, Begin will resume it where you left off.';
        note.hidden = false;
    }

    /* ---------- certificate ---------- */

    function wrapCenter(c, text, x, y, max, lh) {
        var words = text.split(' ');
        var line = '';
        var lines = [];
        words.forEach(function (w) {
            var test = line ? line + ' ' + w : w;
            if (c.measureText(test).width > max && line) { lines.push(line); line = w; } else { line = test; }
        });
        lines.push(line);
        lines.forEach(function (l, i) { c.fillText(l, x, y + i * lh); });
    }

    function drawCertificate(cert) {
        var cv = $('[data-cert-canvas]');
        var c = cv.getContext('2d');
        var W = cv.width;
        var H = cv.height;
        var saffron = '#c2410c';
        var gold = '#b7791f';
        var ink = '#3b2412';

        var bg = c.createRadialGradient(W / 2, H / 2, 100, W / 2, H / 2, W * 0.7);
        bg.addColorStop(0, '#fffaf0');
        bg.addColorStop(1, '#fbe8c8');
        c.fillStyle = bg;
        c.fillRect(0, 0, W, H);

        c.strokeStyle = saffron;
        c.lineWidth = 14;
        c.strokeRect(36, 36, W - 72, H - 72);
        c.strokeStyle = gold;
        c.lineWidth = 3;
        c.strokeRect(66, 66, W - 132, H - 132);
        c.setLineDash([2, 10]);
        c.strokeRect(84, 84, W - 168, H - 168);
        c.setLineDash([]);

        [[110, 110], [W - 110, 110], [110, H - 110], [W - 110, H - 110]].forEach(function (p) {
            c.save();
            c.translate(p[0], p[1]);
            c.fillStyle = saffron;
            for (var k = 0; k < 8; k++) {
                c.rotate(Math.PI / 4);
                c.beginPath();
                c.ellipse(0, 14, 6, 14, 0, 0, Math.PI * 2);
                c.fill();
            }
            c.fillStyle = gold;
            c.beginPath();
            c.arc(0, 0, 7, 0, Math.PI * 2);
            c.fill();
            c.restore();
        });

        c.textAlign = 'center';
        c.fillStyle = saffron;
        c.font = '96px serif';
        c.fillText('\u0950', W / 2, 220);

        c.fillStyle = ink;
        c.font = '600 30px Georgia, serif';
        c.fillText('SHIVBODH TRUST', W / 2, 278);
        c.font = 'italic 26px Georgia, serif';
        c.fillStyle = gold;
        c.fillText('Certificate of Achievement', W / 2, 322);

        c.fillStyle = saffron;
        c.font = '700 74px Georgia, serif';
        c.fillText('Sangyani Sanatan', W / 2, 430);

        c.fillStyle = ink;
        c.font = '26px Georgia, serif';
        c.fillText('This certificate is proudly presented to', W / 2, 500);

        c.font = 'italic 700 68px Georgia, serif';
        c.fillStyle = '#7c2d12';
        c.fillText(cert.name, W / 2, 590);
        c.strokeStyle = gold;
        c.lineWidth = 2;
        c.beginPath();
        c.moveTo(W / 2 - 380, 615);
        c.lineTo(W / 2 + 380, 615);
        c.stroke();

        c.fillStyle = ink;
        c.font = '27px Georgia, serif';
        wrapCenter(c, 'for clearing all ten levels of the Sangyani Sanatan Quiz, answering every one of its 500 questions on Sanatan Dharma correctly, with a final score of ' + cert.score + ' points.', W / 2, 675, 1080, 40);

        c.font = '24px Georgia, serif';
        c.textAlign = 'left';
        c.fillText('Date of issue', 210, 900);
        c.font = '600 28px Georgia, serif';
        c.fillText(cert.date, 210, 940);
        c.textAlign = 'right';
        c.font = '24px Georgia, serif';
        c.fillText('Certificate ID', W - 210, 900);
        c.font = '600 28px Georgia, serif';
        c.fillText(cert.id, W - 210, 940);

        // Seal
        c.save();
        c.translate(W / 2, 915);
        c.fillStyle = saffron;
        c.beginPath();
        for (var i = 0; i < 32; i++) {
            var r = i % 2 ? 78 : 90;
            var a = i / 32 * Math.PI * 2;
            c[i ? 'lineTo' : 'moveTo'](Math.cos(a) * r, Math.sin(a) * r);
        }
        c.closePath();
        c.fill();
        c.strokeStyle = '#fde68a';
        c.lineWidth = 3;
        c.beginPath();
        c.arc(0, 0, 64, 0, Math.PI * 2);
        c.stroke();
        c.fillStyle = '#fff7ed';
        c.textAlign = 'center';
        c.font = '700 20px Georgia, serif';
        c.fillText('SHIVBODH', 0, -8);
        c.fillText('TRUST', 0, 18);
        c.font = '14px Georgia, serif';
        c.fillText('ISSUED', 0, 40);
        c.restore();

        c.textAlign = 'center';
        c.fillStyle = '#8a6a4a';
        c.font = '19px Georgia, serif';
        c.fillText('Verify at ' + cert.url.replace(/^https?:\/\//, ''), W / 2, H - 112);
    }

    function showCertificate(cert, fresh) {
        drawCertificate(cert);
        $('[data-cert-eyebrow]').textContent = fresh ? 'All ten levels cleared' : 'Verified certificate';
        $('[data-cert-heading]').textContent = fresh ? 'You are a Sangyani Sanatan' : cert.name + ', Sangyani Sanatan';
        $('[data-cert-verify]').textContent = 'Certificate ID ' + cert.id + ', issued by ShivBodh Trust on ' + cert.date + '.';
        $('[data-cert-dashboard]').hidden = !cfg.loggedIn;
        root.dataset.certFile = 'Sangyani-Sanatan-' + cert.name.replace(/[^A-Za-z0-9]+/g, '-') + '.png';
        show('cert');
    }

    $('[data-download-png]').addEventListener('click', function () {
        var a = document.createElement('a');
        a.download = root.dataset.certFile || 'Sangyani-Sanatan-certificate.png';
        a.href = $('[data-cert-canvas]').toDataURL('image/png');
        document.body.appendChild(a);
        a.click();
        a.remove();
    });

    $('[data-download-pdf]').addEventListener('click', function () {
        var holder = document.createElement('div');
        holder.className = 'sp-quiz-print';
        var img = new Image();
        img.alt = 'Sangyani Sanatan certificate';
        img.src = $('[data-cert-canvas]').toDataURL('image/png');
        holder.appendChild(img);
        document.body.appendChild(holder);
        img.onload = function () {
            window.print();
            setTimeout(function () { holder.remove(); }, 500);
        };
    });

    var params = new URLSearchParams(window.location.search);
    var certId = params.get('certificate');
    if (certId && /^SBT-[A-Z0-9]{8}$/.test(certId)) {
        api('certificate/' + certId, null, 'GET')
            .then(function (cert) { showCertificate(cert, false); })
            .catch(function (e) { error(e.message); });
    }
})();
