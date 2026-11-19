/* Sangyani Sanatan: progress survives refresh, retries and temporary network loss. */
(function () {
    'use strict';
    var root = document.querySelector('.sp-quiz'), cfg = window.spQuiz;
    if (!root || !cfg) return;
    var $ = function (s) { return root.querySelector(s); };
    var store = { get: function (k) { try { return localStorage.getItem(k); } catch (_) { return null; } }, set: function (k, v) { try { localStorage.setItem(k, v); return true; } catch (_) { return false; } }, remove: function (k) { try { localStorage.removeItem(k); } catch (_) {} } };
    var STORE = 'spQuizToken', run = null, question = null, feedback = null, busy = false, timer = null, certificate = null;
    var practiceParent = null, fresh = false, autoNextTimer = null, readingInfo = false, mouseX = null, mouseY = null;
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
    function show(name) { closeOptionInfo(); clearInterval(timer); if (name !== 'play') { clearTimeout(autoNextTimer); autoNextTimer = null; } root.dataset.screen = name; root.querySelectorAll('[data-screen]').forEach(function (el) { el.classList.toggle('is-active', el.dataset.screen === name); }); }
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
    function closeOptionInfo() {
        root.querySelectorAll('.sp-quiz__info-popover').forEach(function (el) { el.hidden = true; });
        root.querySelectorAll('.sp-quiz__info-btn').forEach(function (el) { el.setAttribute('aria-expanded', 'false'); el.removeAttribute('aria-describedby'); delete el.dataset.pinned; });
    }
    function stopAutoAdvance() { clearTimeout(autoNextTimer); autoNextTimer = null; }
    function openOptionInfo(button, popover) {
        readingInfo = true;
        if (feedback && feedback.correct) $('[data-feedback]').textContent = 'Correct. +10 points. Select Next card when you are ready.';
        stopAutoAdvance(); popover.hidden = false; button.setAttribute('aria-expanded', 'true'); button.setAttribute('aria-describedby', popover.id);
    }
    document.addEventListener('click', function (e) { if (!e.target.closest('.sp-quiz__info-btn, .sp-quiz__info-popover')) closeOptionInfo(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeOptionInfo(); });
    document.addEventListener('visibilitychange', function () { if (document.hidden) stopAutoAdvance(); });
    root.addEventListener('toggle', function (e) { if (e.target.open && $('[data-report-wrap]').contains(e.target)) stopAutoAdvance(); }, true);
    document.addEventListener('pointermove', function (e) { mouseX = e.clientX; mouseY = e.clientY; });
    function render() {
        var hoverOriginX = mouseX, hoverOriginY = mouseY, renderedAt = performance.now();
        readingInfo = false;
        clearTimeout(autoNextTimer); autoNextTimer = null;
        if (!question) { if (run && run.cert) return showCertificate(run.cert); return show('start'); }
        show('play'); hud(); root.dataset.mood = 'idle';
        $('[data-question]').textContent = question.text; $('[data-card-tag]').textContent = question.key;
        $('[data-feedback]').textContent = ''; $('[data-explanation]').hidden = true; $('[data-report-wrap]').hidden = true;
        $('[data-report-status]').textContent = ''; $('[data-report-form]').reset();
        $('[data-next]').hidden = true; $('[data-checkpoint]').textContent = '';
        $('[data-timer-warning]').textContent = run.seconds ? 'Challenge: 30 seconds per card. The timer continues if you leave this page.' : 'Take your time. This learning session has no timer.';
        var box = $('[data-options]'); box.replaceChildren();
        question.options.forEach(function (text, index) {
            var row = document.createElement('div'); row.className = 'sp-quiz__option-row';
            var button = document.createElement('button'); button.type = 'button'; button.className = 'sp-quiz__option sp-quiz__btn'; button.dataset.choice = index;
            var key = document.createElement('span'); key.className = 'sp-quiz__key'; key.textContent = index + 1; key.dataset.k = index + 1;
            var label = document.createElement('span'); label.textContent = text; button.append(key, label); button.addEventListener('click', function () { closeOptionInfo(); answer(index); }); row.append(button);
            var meaning = question.optionInfo && question.optionInfo[index];
            if (meaning) {
                var info = document.createElement('button'); info.type = 'button'; info.className = 'sp-quiz__info-btn sp-noslide'; info.textContent = 'i';
                var popover = document.createElement('div'); popover.className = 'sp-quiz__info-popover'; popover.id = 'sp-quiz-meaning-' + index; popover.hidden = true; popover.textContent = meaning;
                info.setAttribute('aria-label', 'Meaning of ' + text); info.setAttribute('aria-expanded', 'false'); info.setAttribute('aria-controls', popover.id);
                info.addEventListener('click', function (e) {
                    e.preventDefault(); e.stopPropagation(); var open = info.dataset.pinned === 'true'; closeOptionInfo();
                    if (!open) { openOptionInfo(info, popover); info.dataset.pinned = 'true'; }
                });
                info.addEventListener('pointerenter', function (e) { if (e.pointerType === 'mouse' && performance.now() - renderedAt > 150) { closeOptionInfo(); openOptionInfo(info, popover); } });
                info.addEventListener('pointermove', function (e) { if (e.pointerType === 'mouse' && (e.clientX !== hoverOriginX || e.clientY !== hoverOriginY) && info.getAttribute('aria-expanded') !== 'true') { closeOptionInfo(); openOptionInfo(info, popover); } });
                row.addEventListener('pointerleave', function (e) { if (e.pointerType === 'mouse' && info.dataset.pinned !== 'true' && !row.contains(document.activeElement)) closeOptionInfo(); });
                info.addEventListener('focus', function () { if (info.matches(':focus-visible')) { closeOptionInfo(); openOptionInfo(info, popover); } });
                row.addEventListener('focusout', function (e) { if (!row.contains(e.relatedTarget)) closeOptionInfo(); });
                row.append(info, popover);
            }
            box.append(row);
        });
        $('[data-question]').focus();
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
        clearInterval(timer); clearTimeout(autoNextTimer); autoNextTimer = null; feedback = result; root.dataset.mood = result.correct ? 'good' : 'bad';
        root.querySelectorAll('[data-choice]').forEach(function (button) { button.disabled = true; button.classList.toggle('is-right', Number(button.dataset.choice) === result.answer); });
        $('[data-feedback]').textContent = result.correct ? 'Correct. +10 points.' : (result.expired ? 'Time has ended. ' : 'Keep learning. ') + 'The correct answer is ' + question.options[result.answer] + '. −5 points. This card will return for revision.';
        $('[data-explanation]').textContent = result.explanation; $('[data-explanation]').hidden = false; $('[data-report-wrap]').hidden = false;
        $('[data-next]').hidden = false; $('[data-next]').textContent = run.cert ? 'View completion certificate' : run.status === 'practised' ? 'Finish revision' : result.cleared ? 'Continue to next topic' : 'Next card';
        $('[data-checkpoint]').textContent = result.checkpoint ? 'Ten cards practised. A good moment to pause; your progress is saved.' : '';
        hud();
        if (sound) { var audio = new Audio(cfg.audio[result.correct ? 'correct' : 'wrong']); audio.play().catch(function () {}); }
        if (result.correct && !readingInfo) {
            $('[data-feedback]').textContent += ' Next question loading automatically.';
            // Keep feedback readable; next() retains backend checkpoint/certificate logic.
            autoNextTimer = setTimeout(function () {
                autoNextTimer = null;
                if (!readingInfo && !document.hidden && !busy && feedback === result && root.dataset.screen === 'play') next();
            }, calm ? 2200 : 1500);
        } else {
            $('[data-next]').focus({ preventScroll: true });
        }
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
        clearTimeout(autoNextTimer); autoNextTimer = null;
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

    // Certificate art: owner-supplied ShivBodh Trust stamp + gold lotus (assets/images/certificate/).
    var certArt = {};
    var certArtReady = Promise.all(['stamp', 'lotus'].map(function (k) {
        return new Promise(function (done) {
            if (!cfg.cert || !cfg.cert[k]) return done();
            var img = new Image();
            img.onload = function () { certArt[k] = img; done(); };
            img.onerror = function () { done(); };
            img.src = cfg.cert[k];
        });
    }));
    function spaced(c, text, x, y, gap) {
        var w = 0, i;
        for (i = 0; i < text.length; i++) w += c.measureText(text[i]).width + (i < text.length - 1 ? gap : 0);
        var align = c.textAlign, px = x - w / 2;
        c.textAlign = 'left';
        for (i = 0; i < text.length; i++) { c.fillText(text[i], px, y); px += c.measureText(text[i]).width + gap; }
        c.textAlign = align;
    }
    function dots(c, x1, x2, y, color) {
        c.save(); c.fillStyle = color;
        for (var x = x1; x <= x2; x += 13) { c.beginPath(); c.arc(x, y, 2.1, 0, Math.PI * 2); c.fill(); }
        c.restore();
    }
    function corner(c, x, y, sx, sy) {
        var red = '#a33633', brown = '#8a6a2f', k = 1.25;
        c.save(); c.translate(x, y); c.scale(sx * k, sy * k);
        c.strokeStyle = brown; c.lineCap = 'round';
        c.lineWidth = 2; c.beginPath(); c.moveTo(30, 52); c.lineTo(30, 118); c.moveTo(52, 30); c.lineTo(118, 30); c.stroke();
        c.lineWidth = 1.3; c.beginPath(); c.moveTo(44, 58); c.bezierCurveTo(44, 76, 54, 86, 70, 88); c.moveTo(58, 44); c.bezierCurveTo(76, 44, 86, 54, 88, 70); c.stroke();
        c.fillStyle = red;
        for (var a = 0; a < 8; a++) { c.save(); c.translate(30, 30); c.rotate(a * Math.PI / 4); c.beginPath(); c.ellipse(0, -9, 3.6, 8.2, 0, 0, Math.PI * 2); c.fill(); c.restore(); }
        c.fillStyle = '#fffaf0'; c.beginPath(); c.arc(30, 30, 6.4, 0, Math.PI * 2); c.fill(); c.lineWidth = 1.6; c.stroke();
        c.fillStyle = brown; c.beginPath(); c.arc(30, 30, 2.6, 0, Math.PI * 2); c.fill();
        c.fillStyle = red; c.beginPath(); c.moveTo(30, 92); c.lineTo(35, 98); c.lineTo(30, 104); c.lineTo(25, 98); c.closePath(); c.fill();
        c.beginPath(); c.moveTo(92, 30); c.lineTo(98, 25); c.lineTo(104, 30); c.lineTo(98, 35); c.closePath(); c.fill();
        c.restore();
    }
    // cert = null draws the specimen: every personal field is a dotted blank.
    function drawCertificate(cert, canvas) {
        var cv = canvas || $('[data-cert-canvas]');
        var c = cv.getContext('2d');
        var W = cv.width, H = cv.height;
        var maroon = '#7a2a27', gold = '#8a6a2f', ink = '#30292a', soft = '#6b5a3e';
        var serif = 'Georgia, "Times New Roman", serif', sans = 'Arial, Helvetica, sans-serif';
        c.save();
        c.clearRect(0, 0, W, H);
        c.fillStyle = '#fffaf0'; c.fillRect(0, 0, W, H);
        // Maroon band studded with gold, between gold rules.
        var o = 16, band = 30;
        c.fillStyle = '#8f2e2b'; c.fillRect(o, o, W - 2 * o, H - 2 * o);
        c.fillStyle = '#e2c27a';
        for (var y = o + 6; y < H - o; y += 13) for (var x = o + 6; x < W - o; x += 13) {
            if (x > o + band && x < W - o - band && y > o + band && y < H - o - band) continue;
            c.beginPath(); c.arc(x, y, 2.2, 0, Math.PI * 2); c.fill();
        }
        c.strokeStyle = '#c9a55a'; c.lineWidth = 3; c.strokeRect(o + 1.5, o + 1.5, W - 2 * o - 3, H - 2 * o - 3);
        var ix = o + band, iy = o + band, iw = W - 2 * ix, ih = H - 2 * iy;
        var bg = c.createRadialGradient(W / 2, H * 0.4, 60, W / 2, H * 0.45, W * 0.62);
        bg.addColorStop(0, '#fffdf7'); bg.addColorStop(0.62, '#fdf5e4'); bg.addColorStop(1, '#f6ead0');
        c.fillStyle = bg; c.fillRect(ix, iy, iw, ih);
        c.strokeStyle = '#e2c27a'; c.lineWidth = 2; c.strokeRect(ix - 3, iy - 3, iw + 6, ih + 6);
        c.strokeStyle = gold; c.lineWidth = 1.5; c.strokeRect(ix + 1, iy + 1, iw - 2, ih - 2);
        c.strokeStyle = '#b8995a'; c.lineWidth = 1; c.strokeRect(ix + 10, iy + 10, iw - 20, ih - 20);
        corner(c, ix + 6, iy + 6, 1, 1); corner(c, W - ix - 6, iy + 6, -1, 1);
        corner(c, ix + 6, H - iy - 6, 1, -1); corner(c, W - ix - 6, H - iy - 6, -1, -1);
        if (certArt.lotus) {
            var lw = 760, lh = lw * certArt.lotus.height / certArt.lotus.width;
            c.globalAlpha = 0.075; c.drawImage(certArt.lotus, (W - lw) / 2, H * 0.5 - lh * 0.44, lw, lh); c.globalAlpha = 1;
            c.drawImage(certArt.lotus, W / 2 - 55, 148, 110, 110 * certArt.lotus.height / certArt.lotus.width);
        }
        c.textAlign = 'center'; c.textBaseline = 'alphabetic';
        if (!cert) {
            c.strokeStyle = '#b8995a'; c.lineWidth = 1.5; c.strokeRect(W / 2 - 135, 72, 270, 30);
            c.fillStyle = maroon; c.font = '700 15px ' + sans; spaced(c, 'SPECIMEN \u00b7 NOT ISSUED', W / 2, 93, 3);
        }
        c.fillStyle = maroon; c.font = '700 38px ' + serif; spaced(c, 'SHIVBODH TRUST', W / 2, 270, 8);
        c.fillStyle = soft; c.font = '17px ' + sans;
        spaced(c, 'Reg. No. 132  \u00b7  NGO Darpan ID UP/2025/0636313  \u00b7  Hanuman Ghat, Varanasi', W / 2, 304, 1);
        c.fillStyle = gold; c.font = '700 19px ' + sans; spaced(c, 'CERTIFICATE OF COURSE COMPLETION', W / 2, 352, 4.5);
        c.strokeStyle = '#b8995a'; c.lineWidth = 1.5;
        c.beginPath(); c.moveTo(W / 2 - 430, 346); c.lineTo(W / 2 - 280, 346); c.moveTo(W / 2 + 280, 346); c.lineTo(W / 2 + 430, 346); c.stroke();
        c.fillStyle = ink; c.font = 'italic 104px ' + serif; c.fillText('Sangyani Sanatan', W / 2, 462);
        c.fillStyle = '#5a4b3a'; c.font = 'italic 27px ' + serif; c.fillText('This certificate is proudly presented to', W / 2, 520);
        if (cert) {
            var nameSize = 66;
            c.font = 'italic 700 ' + nameSize + 'px ' + serif;
            while (c.measureText(cert.name).width > 1080 && nameSize > 18) { nameSize--; c.font = 'italic 700 ' + nameSize + 'px ' + serif; }
            c.fillStyle = maroon; c.fillText(cert.name, W / 2, 606);
            c.strokeStyle = '#b8995a'; c.lineWidth = 2; c.beginPath(); c.moveTo(W / 2 - 470, 632); c.lineTo(W / 2 + 470, 632); c.stroke();
        } else {
            dots(c, W / 2 - 470, W / 2 + 470, 610, gold);
        }
        c.fillStyle = ink; c.font = '28px ' + serif;
        wrapCenter(c, cert ? 'for completing all ten topics and studying all 500 cards, including revision where needed, with a final score of ' + cert.score + ' points.' : 'for completing all ten topics and studying all 500 learning cards of the Sangyani Sanatan course.', W / 2, 692, 1060, 42);
        // Footer: issue date, certificate ID, stamp over the Trust line.
        var fy = 900;
        c.strokeStyle = '#d6c39a'; c.lineWidth = 1; c.beginPath(); c.moveTo(ix + 120, 784); c.lineTo(W - ix - 120, 784); c.stroke();
        c.textAlign = 'left'; c.fillStyle = soft; c.font = '700 15px ' + sans;
        spacedLeft(c, 'DATE OF ISSUE', 205, fy, 2); spacedLeft(c, 'CERTIFICATE ID', 505, fy, 2);
        c.fillStyle = ink; c.font = '600 27px ' + serif;
        if (cert) { c.fillText(cert.date, 205, fy + 40); c.fillText(cert.id, 505, fy + 40); }
        else { dots(c, 207, 430, fy + 36, gold); dots(c, 507, 830, fy + 36, gold); }
        var sx = W - 360;
        if (certArt.stamp) {
            c.save(); c.translate(sx, fy - 78); c.rotate(-11 * Math.PI / 180);
            c.globalCompositeOperation = 'multiply'; c.globalAlpha = 0.93;
            c.drawImage(certArt.stamp, -112, -112, 224, 224); c.restore();
        }
        c.strokeStyle = gold; c.lineWidth = 1.5; c.beginPath(); c.moveTo(sx - 150, fy + 22); c.lineTo(sx + 150, fy + 22); c.stroke();
        c.textAlign = 'center'; c.fillStyle = ink; c.font = '700 27px ' + serif; c.fillText('For ShivBodh Trust', sx, fy + 56);
        c.fillStyle = soft; c.font = '18px ' + sans; c.fillText('Course completion certificate', sx, fy + 82);
        c.fillStyle = '#5a4b3a'; c.font = '17px ' + sans;
        c.fillText(cert ? 'Verify at ' + cert.url.replace(/^https?:\/\//, '') : 'Your name, date of issue and certificate ID are added when you complete all ten topics.', W / 2, H - iy - 26);
        c.restore();
    }
    function spacedLeft(c, text, x, y, gap) {
        for (var i = 0; i < text.length; i++) { c.fillText(text[i], x, y); x += c.measureText(text[i]).width + gap; }
    }
    function drawWhenReady(cert, canvas) { drawCertificate(cert, canvas); certArtReady.then(function () { drawCertificate(cert, canvas); }); }

    function showCertificate(cert) {
        certificate = cert; show('cert'); drawWhenReady(cert);
        $('[data-cert-heading]').textContent = 'Your learning milestone';
        $('[data-cert-eyebrow]').textContent = 'Verified course completion';
        $('[data-cert-text]').textContent = cert.name + ' completed the Sangyani Sanatan learning course of ten topics and 500 cards, with revision where needed. Score: ' + cert.score + '. Issued by ShivBodh Trust on ' + cert.date + '. Certificate ID: ' + cert.id + '.';
        $('[data-cert-canvas]').setAttribute('aria-label', $('[data-cert-text]').textContent);
        $('[data-cert-verify]').textContent = 'Certificate ID ' + cert.id + ' · ' + cert.date;
        $('[data-cert-dashboard]').hidden = !cfg.loggedIn;
    }
    $('[data-download-png]').addEventListener('click', function () { certArtReady.then(function () { drawCertificate(certificate); var a = document.createElement('a'); a.download = 'Sangyani-Sanatan-' + certificate.id + '.png'; a.href = $('[data-cert-canvas]').toDataURL('image/png'); a.click(); }); });
    $('[data-download-pdf]').addEventListener('click', function () { certArtReady.then(function () { drawCertificate(certificate); window.print(); }); });
    // Start screen: specimen of the certificate a learner receives, drawn when it scrolls into view.
    var specimen = $('[data-cert-specimen]');
    if (specimen) {
        if ('IntersectionObserver' in window) { var seen = new IntersectionObserver(function (entries) { if (entries[0].isIntersecting) { seen.disconnect(); drawWhenReady(null, specimen); } }, { rootMargin: '300px' }); seen.observe(specimen); }
        else drawWhenReady(null, specimen);
    }
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
