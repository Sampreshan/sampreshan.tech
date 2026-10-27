/* Sangyani Sanatan: progress survives refresh, retries and temporary network loss. */
(function () {
    'use strict';
    var root = document.querySelector('.sp-quiz'), cfg = window.spQuiz;
    if (!root || !cfg) return;
    var $ = function (s) { return root.querySelector(s); };
    var store = { get: function (k) { try { return localStorage.getItem(k); } catch (_) { return null; } }, set: function (k, v) { try { localStorage.setItem(k, v); return true; } catch (_) { return false; } }, remove: function (k) { try { localStorage.removeItem(k); } catch (_) {} } };
    var STORE = 'spQuizToken', run = null, question = null, feedback = null, busy = false, timer = null, certificate = null;
    var practiceParent = null, fresh = false;
    var calm = store.get('spQuizCalm') === '1' || matchMedia('(prefers-reduced-motion: reduce)').matches;
    var sound = store.get('spQuizSound') === '1';
    function motion() { root.dataset.calm = String(calm); $('[data-calm]').checked = calm; }
    function soundSetting() { $('[data-sound]').checked = sound; $('[data-mute]').textContent = sound ? 'Sound on' : 'Sound off'; $('[data-mute]').setAttribute('aria-pressed', String(sound)); }
    motion(); soundSetting();
    root.querySelectorAll('[name="mode"]').forEach(function (input) { input.addEventListener('change', function () { $('[data-mode-note]').textContent = input.value === 'challenge' ? 'Challenge: 30 seconds per card.' : 'Self-paced learning, with no timer.'; }); });
    $('[data-calm]').addEventListener('change', function (e) { calm = e.target.checked; store.set('spQuizCalm', calm ? '1' : '0'); motion(); });
    $('[data-sound]').addEventListener('change', function (e) { sound = e.target.checked; store.set('spQuizSound', sound ? '1' : '0'); soundSetting(); });
    $('[data-mute]').addEventListener('click', function () { sound = !sound; store.set('spQuizSound', sound ? '1' : '0'); soundSetting(); });
    document.addEventListener('visibilitychange', function () { root.dataset.paused = String(document.hidden); });
    function show(name) { clearInterval(timer); root.dataset.screen = name; root.querySelectorAll('[data-screen]').forEach(function (el) { el.classList.toggle('is-active', el.dataset.screen === name); }); }
    function error(message, play) { var el = $(play ? '[data-play-error]' : '[data-error]'); el.textContent = message || ''; el.hidden = !message; if (play) $('[data-recover]').hidden = !message; }
    async function api(path, data) {
        var controller = new AbortController(), timeout = setTimeout(function () { controller.abort(); }, 15000);
        try {
            var response = await fetch(cfg.api + path, { method: 'POST', credentials: 'same-origin', cache: 'no-store', signal: controller.signal, headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce }, body: JSON.stringify(data) });
            var body = await response.json();
            if (!response.ok) throw new Error(body.message || 'Please try again.');
            return body;
        } catch (e) { if (e.name === 'AbortError' || e instanceof TypeError) throw new Error('Connection interrupted. Your progress is saved. Resume to check your last answer.'); throw e; }
        finally { clearTimeout(timeout); }
    }
    function saveToken(token) {
        if (!store.set(STORE, token)) error('Browser storage is unavailable. Keep this tab open or sign in to preserve your progress.', true);
    }
    function hud() {
        $('[data-hud-num]').textContent = run.level;
        $('[data-hud-title]').textContent = run.levelTitle;
        $('[data-hud-level]').textContent = (run.mode === 'practice' ? 'Revision' : run.mode === 'learn' ? 'Self-paced learning' : 'Timed challenge') + ' · Topic ' + run.level + ' of ' + run.levels;
        $('[data-hud-score]').textContent = run.score;
        var pct = Math.max(0, Math.min(100, Math.round(run.levelDone / run.levelTotal * 100)));
        $('[data-progress]').setAttribute('aria-valuenow', pct); $('[data-progress]').firstElementChild.style.width = pct + '%';
        $('[data-progress-text]').textContent = run.levelDone + ' of ' + run.levelTotal + ' cards learned in this topic';
        var list = $('[data-revision-list]'); list.replaceChildren();
        (run.review || []).forEach(function (item) { var detail = document.createElement('details'), title = document.createElement('summary'), note = document.createElement('p'); title.textContent = item.question; note.textContent = item.answer + '. ' + item.note; detail.append(title, note); list.append(detail); });
        $('[data-notebook]').textContent = (run.review || []).length ? (run.review.length + ' cards to revisit.') : 'No outstanding cards. Incorrect answers will appear here.';
        $('[data-claim]').hidden = !cfg.loggedIn;
        $('[data-practice]').hidden = run.mode === 'practice';
    }
    function render() {
        if (!question) { if (run && run.cert) return showCertificate(run.cert); return show('start'); }
        show('play'); hud(); root.dataset.mood = 'idle';
        $('[data-question]').textContent = question.text; $('[data-card-tag]').textContent = question.key;
        $('[data-feedback]').textContent = ''; $('[data-explanation]').hidden = true; $('[data-report-wrap]').hidden = true;
        $('[data-report-status]').textContent = ''; $('[data-report-form]').reset();
        $('[data-next]').hidden = true; $('[data-checkpoint]').textContent = '';
        $('[data-timer-warning]').textContent = run.seconds ? 'Challenge: 30 seconds per card. The timer continues if you leave this page.' : 'Take your time. This learning session has no timer.';
        var box = $('[data-options]'); box.replaceChildren();
        question.options.forEach(function (text, index) {
            var button = document.createElement('button'); button.type = 'button'; button.className = 'sp-quiz__option sp-quiz__btn'; button.dataset.choice = index;
            var key = document.createElement('span'); key.className = 'sp-quiz__key'; key.textContent = index + 1; key.dataset.k = index + 1;
            var label = document.createElement('span'); label.textContent = text; button.append(key, label); button.addEventListener('click', function () { answer(index); }); box.append(button);
        });
        $('[data-question]').focus({ preventScroll: true });
        $('.sp-quiz__timer').hidden = question.remaining === null;
        if (question.remaining !== null && !feedback) {
            var deadline = Date.now() + question.remaining * 1000, warned = false;
            function tick() {
                var seconds = Math.max(0, Math.ceil((deadline - Date.now()) / 1000)); $('[data-timer]').textContent = seconds;
                $('[data-timer-bar]').style.strokeDashoffset = 119.4 * (1 - seconds / 30);
                if (seconds <= 10 && !warned) { $('[data-timer-warning]').textContent = 'Ten seconds or less remaining.'; warned = true; }
                if (seconds === 0 && !busy) { clearInterval(timer); answer(-1); }
            }
            timer = setInterval(tick, 250); tick();
        }
    }
    function paintFeedback(result) {
        clearInterval(timer); feedback = result; root.dataset.mood = result.correct ? 'good' : 'bad';
        root.querySelectorAll('[data-choice]').forEach(function (button) { button.disabled = true; button.classList.toggle('is-right', Number(button.dataset.choice) === result.answer); });
        $('[data-feedback]').textContent = result.correct ? 'Correct. +10 points.' : (result.expired ? 'Time has ended. ' : 'Keep learning. ') + 'The correct answer is ' + question.options[result.answer] + '. −5 points. This card will return for revision.';
        $('[data-explanation]').textContent = result.explanation; $('[data-explanation]').hidden = false; $('[data-report-wrap]').hidden = false;
        $('[data-next]').hidden = false; $('[data-next]').textContent = run.cert ? 'View completion certificate' : run.status === 'practised' ? 'Finish revision' : result.cleared ? 'Continue to next topic' : 'Next card';
        $('[data-checkpoint]').textContent = result.checkpoint ? 'Ten cards practised. A good moment to pause; your progress is saved.' : '';
        hud();
        if (sound) { var audio = new Audio(cfg.audio[result.correct ? 'correct' : 'wrong']); audio.play().catch(function () {}); }
        $('[data-next]').focus({ preventScroll: true });
    }
    function accept(data) {
        run = data.run; $('[name="name"]').value = run.player; $('[name="name"]').required = false; question = data.question; feedback = data.feedback || null; saveToken(run.token); fresh = false;
        if (run.cert && !feedback) return showCertificate(run.cert);
        if (run.status === 'practised' && !feedback) return finishPractice();
        render(); if (feedback) paintFeedback(feedback);
    }
    async function resume() {
        if (busy) return; busy = true; error('', true);
        try { accept(await api('start', { token: fresh ? '' : (store.get(STORE) || (run && run.token) || ''), name: $('[name="name"]').value, mode: $('[name="mode"]:checked').value, fresh: fresh })); }
        catch (e) { error(e.message, root.dataset.screen === 'play'); } finally { busy = false; }
    }
    async function answer(choice) {
        if (busy || feedback || !question) return; busy = true; clearInterval(timer);
        root.querySelectorAll('[data-choice]').forEach(function (button) { button.disabled = true; });
        try { var data = await api('answer', { token: run.token, questionId: question.id, choice: choice }); run = data.run; paintFeedback(data); error('', true); }
        catch (e) { error(e.message, true); } finally { busy = false; }
    }
    async function action(name, extra) {
        if (busy || !run) return; busy = true;
        try { var data = await api('action', Object.assign({ token: run.token, action: name }, extra || {})); error('', true); return data; }
        catch (e) { error(e.message, true); } finally { busy = false; }
    }
    async function next() {
        if (!feedback || busy) return;
        if (run.cert) return showCertificate(run.cert);
        if (run.status === 'practised') return finishPractice();
        var data = await action('next', { questionId: feedback.questionId }); if (data) accept(data);
    }
    function finishPractice() {
        if (practiceParent) { saveToken(practiceParent); practiceParent = null; store.remove('spQuizPracticeParent'); }
        show('start'); note('Revision complete. Your main journey is saved. Begin or resume when you are ready.');
    }
    function note(text) { $('[data-resume-note]').hidden = false; $('[data-resume-note]').textContent = text; $('[data-new]').hidden = !store.get(STORE); $('[data-forget]').hidden = !store.get(STORE); }
    $('[data-start-form]').addEventListener('submit', function (e) { e.preventDefault(); resume(); });
    $('[data-next]').addEventListener('click', next); $('[data-continue]').addEventListener('click', next);
    $('[data-recover]').addEventListener('click', resume);
    $('[data-quit]').addEventListener('click', function () { if (busy) return; clearInterval(timer); show('start'); note('Your progress is saved. Begin or resume to continue. Timed challenge cards keep their original deadline.'); });
    $('[data-new]').addEventListener('click', function () { if (!confirm('Begin a new journey on this browser? Your previous account history will remain.')) return; store.remove(STORE); fresh = true; $('[name="name"]').required = true; show('start'); $('[data-new]').hidden = true; $('[data-resume-note]').hidden = true; error(''); });
    $('[data-forget]').addEventListener('click', async function () {
        if (busy || !confirm('Permanently delete this saved journey and any certificate it issued?')) return; busy = true;
        try { await api('action', { token: store.get(STORE), action: 'forget' }); store.remove(STORE); store.remove('spQuizPracticeParent'); run = null; question = null; feedback = null; fresh = true; $('[data-new]').hidden = true; $('[data-forget]').hidden = true; $('[data-resume-note]').textContent = 'Saved journey deleted.'; }
        catch(e) { error(e.message); } finally { busy = false; }
    });
    $('[data-practice]').addEventListener('click', async function () {
        if (busy || !confirm('Pause your journey for a short revision session? Your main progress will be kept.')) return;
        var parent = run.token, data = await action('practice'); if (data) { practiceParent = parent; store.set('spQuizPracticeParent', parent); accept(data); }
    });
    $('[data-claim]').addEventListener('click', async function () { var data = await action('claim'); if (data) { accept(data); $('[data-checkpoint]').textContent = 'Saved to your account and private dashboard.'; } });
    $('[data-rename]').addEventListener('click', async function () { var name = prompt('Certificate recipient name (up to 60 characters)', run.player); if (name) { var data = await action('rename', { name: name }); if (data) accept(data); } });
    $('[data-report-form]').addEventListener('submit', async function (e) { e.preventDefault(); var data = await action('report', { key: question.key, message: $('#sp-report').value }); if (data) { $('[data-report-status]').textContent = 'Thank you. Your question has been sent for editorial review.'; $('#sp-report').value = ''; } });
    document.addEventListener('keydown', function (e) {
        if (/^(INPUT|TEXTAREA|SELECT|BUTTON)$/.test(document.activeElement.tagName) || document.activeElement.isContentEditable || e.altKey || e.ctrlKey || e.metaKey) return;
        if (root.dataset.screen !== 'play') return;
        if (/^[1-4]$/.test(e.key) && !feedback) { e.preventDefault(); answer(Number(e.key) - 1); }
        if (e.key === 'Enter' && feedback) { e.preventDefault(); next(); }
    });
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
        c.fillText('Certificate of Course Completion', W / 2, 322);

        c.fillStyle = saffron;
        c.font = '700 74px Georgia, serif';
        c.fillText('Sangyani Sanatan', W / 2, 430);

        c.fillStyle = ink;
        c.font = '26px Georgia, serif';
        c.fillText('This certificate is proudly presented to', W / 2, 500);

        var nameSize = 68;
        c.font = 'italic 700 ' + nameSize + 'px Georgia, serif';
        while (c.measureText(cert.name).width > 1180 && nameSize > 18) { nameSize--; c.font = 'italic 700 ' + nameSize + 'px Georgia, serif'; }
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
        wrapCenter(c, 'for completing all ten topics and studying all 500 cards, including revision where needed, with a final score of ' + cert.score + ' points.', W / 2, 675, 1080, 40);

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

    function showCertificate(cert) {
        certificate = cert; show('cert'); drawCertificate(cert);
        $('[data-cert-heading]').textContent = 'Your learning milestone';
        $('[data-cert-eyebrow]').textContent = 'Verified course completion';
        $('[data-cert-text]').textContent = cert.name + ' completed the Sangyani Sanatan learning course of ten topics and 500 cards, with revision where needed. Score: ' + cert.score + '. Issued by ShivBodh Trust on ' + cert.date + '. Certificate ID: ' + cert.id + '.';
        $('[data-cert-canvas]').setAttribute('aria-label', $('[data-cert-text]').textContent);
        $('[data-cert-verify]').textContent = 'Certificate ID ' + cert.id + ' · ' + cert.date;
        $('[data-cert-dashboard]').hidden = !cfg.loggedIn;
    }
    $('[data-download-png]').addEventListener('click', function () { var a = document.createElement('a'); a.download = 'Sangyani-Sanatan-' + certificate.id + '.png'; a.href = $('[data-cert-canvas]').toDataURL('image/png'); a.click(); });
    $('[data-download-pdf]').addEventListener('click', function () { window.print(); });
    $('[data-share]').addEventListener('click', async function () {
        try { if (navigator.share) await navigator.share({ title: 'Sangyani Sanatan completion', url: certificate.url }); else { await navigator.clipboard.writeText(certificate.url); $('[data-cert-verify]').textContent = 'Certificate link copied.'; } }
        catch (e) { if (e.name !== 'AbortError') $('[data-cert-verify]').textContent = certificate.url; }
    });
    practiceParent = store.get('spQuizPracticeParent');
    $('[name="name"]').value = cfg.name || '';
    $('[name="name"]').required = !store.get(STORE);
    if (store.get(STORE)) note('Your saved journey is ready. Begin or resume to continue.');
    show('start');
    var certId = new URLSearchParams(location.search).get('certificate');
    if (certId) {
        fetch(cfg.api + 'certificate/' + encodeURIComponent(certId), { cache: 'no-store' }).then(async function (response) { var data = await response.json(); if (!response.ok) throw new Error(data.message || 'Certificate unavailable.'); showCertificate(data); }).catch(function (e) { error(e.message); });
    }
})();
