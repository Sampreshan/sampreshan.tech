/**
 * Realistic 3D Guru Parampara tree (v2) for Peetham pages.
 *
 * The tree plays by itself: the founder glows at the roots, then each
 * Acharya's lamp lights in turn on the canopy with the name and period shown
 * beside it, joined by a golden thread. Below, the Kaal Khanda bar gives each
 * Acharya a band as wide as the years of the tenure; beside the tree the full
 * list is always readable. Names and periods are only those printed by PHP
 * from inc/parampara-data.php; nothing here invents a date.
 */
const blocks = document.querySelectorAll('[data-sp-pp3t]');
const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const NOW_YEAR = new Date().getFullYear();

function hasWebGL() {
    try {
        const c = document.createElement('canvas');
        return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
    } catch (e) {
        return false;
    }
}

function whenNear(el, cb) {
    const check = () => {
        if (el.getBoundingClientRect().top < window.innerHeight + 400) {
            window.removeEventListener('scroll', check);
            window.removeEventListener('resize', check);
            cb();
        }
    };
    window.addEventListener('scroll', check, { passive: true });
    window.addEventListener('resize', check);
    check();
}

/**
 * Year span of a printed period, for the width of its band only.
 * Returns [start, end] (BCE as negative) or null when the text has no years.
 */
function span(period) {
    const s = String(period || '').toLowerCase().replace(/[\u2013\u2014]/g, '-');
    const cent = s.match(/(\d{1,2})(?:st|nd|rd|th)\s+century/);
    if (cent) {
        const c = parseInt(cent[1], 10);
        return /bce|\bbc\b/.test(s) ? [-c * 100, -(c - 1) * 100] : [(c - 1) * 100, c * 100];
    }
    const toks = [];
    for (const m of s.matchAll(/(present)|(\d{1,4})\s*(bce|bc\b|ce|ad\b)?/g)) {
        if (m[1]) {
            toks.push({ y: NOW_YEAR, era: 'ce' });
        } else {
            toks.push({ y: parseInt(m[2], 10), era: m[3] ? (m[3].startsWith('b') ? 'bce' : 'ce') : '' });
        }
    }
    if (!toks.length) { return null; }
    toks.forEach((t, i) => {
        if (!t.era) {
            const next = toks.slice(i + 1).find((x) => x.era);
            const prev = toks.slice(0, i).reverse().find((x) => x.era);
            t.era = (next || prev || { era: 'ce' }).era;
        }
    });
    const ys = toks.slice(0, 2).map((t) => (t.era === 'bce' ? -t.y : t.y));
    const a = ys[0];
    const b = ys.length > 1 ? ys[1] : a + 1;
    return [Math.min(a, b), Math.max(a, b, Math.min(a, b) + 1)];
}

function yearLabel(y) {
    return y < 0 ? -y + ' BCE' : y + ' CE';
}

function glowTexture(THREE) {
    const c = document.createElement('canvas');
    c.width = c.height = 64;
    const g = c.getContext('2d');
    const r = g.createRadialGradient(32, 32, 0, 32, 32, 32);
    r.addColorStop(0, 'rgba(255,255,255,1)');
    r.addColorStop(0.25, 'rgba(255,214,140,.85)');
    r.addColorStop(1, 'rgba(255,170,60,0)');
    g.fillStyle = r;
    g.fillRect(0, 0, 64, 64);
    const t = new THREE.CanvasTexture(c);
    t.colorSpace = THREE.SRGBColorSpace;
    return t;
}

function buildEra(root, line, select) {
    const bar = root.querySelector('.sp-pp3t__bar');
    const ticks = root.querySelector('.sp-pp3t__ticks');
    const spans = line.map((n) => (n.gap ? null : span(n.period)));
    const known = spans.filter(Boolean);
    if (!known.length) {
        root.querySelector('.sp-pp3t__era').hidden = true;
        return () => { };
    }
    const total = known.reduce((a, s) => a + (s[1] - s[0]), 0);
    const minW = total * 0.012;
    const widths = spans.map((s) => (s ? Math.max(s[1] - s[0], minW) : minW));
    const sum = widths.reduce((a, b) => a + b, 0);
    const segs = [];
    let off = 0;
    line.forEach((n, i) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'sp-pp3t__seg' + (spans[i] ? '' : ' is-unknown') + (n.status ? ' is-' + n.status : '');
        b.style.flexGrow = String(widths[i]);
        b.title = (n.n ? 'No. ' + n.n + ': ' : '') + n.name + (n.period ? ', ' + n.period : '');
        b.setAttribute('aria-label', b.title);
        b.addEventListener('click', () => select(i + 1, true));
        bar.appendChild(b);
        segs.push({ el: b, from: off / sum, to: (off + widths[i]) / sum, s: spans[i] });
        off += widths[i];
    });
    // Century marks, placed inside the band that holds that year.
    const lo = Math.min(...known.map((s) => s[0]));
    const hi = Math.max(...known.map((s) => s[1]));
    const step = hi - lo > 1600 ? 500 : hi - lo > 700 ? 200 : 100;
    for (let y = Math.ceil(lo / step) * step; y <= hi; y += step) {
        const seg = segs.find((g) => g.s && y >= g.s[0] && y < g.s[1]);
        if (!seg) { continue; }
        const x = seg.from + (seg.to - seg.from) * ((y - seg.s[0]) / (seg.s[1] - seg.s[0]));
        const t = document.createElement('span');
        t.style.left = (x * 100).toFixed(2) + '%';
        t.textContent = yearLabel(y);
        ticks.appendChild(t);
    }
    return (k) => {
        segs.forEach((g, i) => {
            g.el.classList.toggle('is-lit', i < k);
            g.el.classList.toggle('is-current', i === k - 1);
        });
    };
}

async function build(root) {
    const data = JSON.parse(root.querySelector('.sp-pp3t__data').textContent);
    const v = '?ver=' + encodeURIComponent(data.ver);
    // Vendored three.js (r170) is pinned; loaders import it by the same URL,
    // so no version query here or the library would load twice.
    const lib = data.base + 'vendor/three/';
    const [THREE, { GLTFLoader }, { OrbitControls }] = await Promise.all([
        import(lib + 'three.module.min.js'),
        import(lib + 'GLTFLoader.js'),
        import(lib + 'OrbitControls.js'),
    ]);

    const stage = root.querySelector('.sp-pp3t__stage');
    const canvas = root.querySelector('.sp-pp3t__canvas');
    const tagsEl = root.querySelector('.sp-pp3t__tags');
    const list = root.querySelector('.sp-pp3t__list');
    const playBtn = root.querySelector('.sp-pp3t__play');
    const now = {
        num: root.querySelector('.sp-pp3t__num'),
        name: root.querySelector('.sp-pp3t__name'),
        period: root.querySelector('.sp-pp3t__period'),
        note: root.querySelector('.sp-pp3t__note'),
        link: root.querySelector('.sp-pp3t__link'),
    };
    const line = data.line;
    const N = line.length;
    const H = 10;

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, window.innerWidth < 700 ? 1.5 : 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    const scene = new THREE.Scene();
    const HORIZON = 0xf2d9b0;
    scene.fog = new THREE.Fog(HORIZON, 30, 70);

    // Evening sky: a gradient dome from soft blue to warm horizon.
    const sky = new THREE.Mesh(
        new THREE.SphereGeometry(90, 32, 16),
        new THREE.ShaderMaterial({
            side: THREE.BackSide,
            depthWrite: false,
            fog: false,
            uniforms: { top: { value: new THREE.Color(0x4f8fd0) }, bottom: { value: new THREE.Color(HORIZON) } },
            vertexShader: 'varying float h; void main(){ h = normalize(position).y; gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.0); }',
            fragmentShader: 'uniform vec3 top; uniform vec3 bottom; varying float h; void main(){ gl_FragColor = vec4(mix(bottom, top, smoothstep(-0.05, 0.55, h)), 1.0); }',
        })
    );
    scene.add(sky);

    scene.add(new THREE.HemisphereLight(0xdcecff, 0x5a4a2a, 1.25));
    const sun = new THREE.DirectionalLight(0xffe2b0, 2.6);
    sun.position.set(8, 16, 6);
    sun.castShadow = true;
    sun.shadow.mapSize.set(1024, 1024);
    Object.assign(sun.shadow.camera, { left: -12, right: 12, top: 12, bottom: -12, near: 1, far: 50 });
    sun.shadow.bias = -0.0008;
    scene.add(sun);

    // Grass meadow fading into the fog.
    const ground = new THREE.Mesh(
        new THREE.CircleGeometry(60, 64),
        new THREE.MeshStandardMaterial({ color: 0x6d8a3f, roughness: 1 })
    );
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    scene.add(ground);

    // The tree, scaled to the scene height, with a gentle wind in the crown.
    const gltf = await new GLTFLoader().loadAsync(data.base + 'models/parampara-tree-real.glb' + v);
    const tree = gltf.scene;
    let box = new THREE.Box3().setFromObject(tree);
    const s = H / (box.max.y - box.min.y);
    tree.scale.setScalar(s);
    tree.position.set(-(box.min.x + box.max.x) / 2 * s, -box.min.y * s, -(box.min.z + box.max.z) / 2 * s);
    const wind = { value: 0 };
    tree.traverse((o) => {
        if (!o.isMesh) { return; }
        o.castShadow = true;
        o.receiveShadow = true;
        const mat = o.material;
        mat.side = THREE.DoubleSide;
        mat.transparent = false;
        mat.alphaTest = 0.5;
        mat.roughness = 0.85;
        mat.metalness = 0;
        const minY = box.min.y;
        const ht = box.max.y - box.min.y;
        mat.onBeforeCompile = (sh) => {
            sh.uniforms.uWind = wind;
            sh.vertexShader = 'uniform float uWind;\n' + sh.vertexShader.replace(
                '#include <begin_vertex>',
                '#include <begin_vertex>\n' +
                'float hk = clamp((position.y - (' + minY.toFixed(3) + ')) / ' + ht.toFixed(3) + ', 0.0, 1.0);\n' +
                'float sway = hk * hk * ' + (ht * 0.012).toFixed(4) + ';\n' +
                'transformed.x += sin(uWind * 1.3 + position.y * 0.7 + position.z * 0.5) * sway;\n' +
                'transformed.z += cos(uWind * 1.1 + position.x * 0.6) * sway * 0.7;'
            );
        };
    });
    scene.add(tree);
    tree.updateMatrixWorld(true);

    // Canopy points: the leaf half of the texture atlas, in world space.
    const leaves = [];
    const bark = [];
    const p = new THREE.Vector3();
    tree.traverse((o) => {
        if (!o.isMesh) { return; }
        const pos = o.geometry.attributes.position;
        const uv = o.geometry.attributes.uv;
        for (let i = 0; i < pos.count; i++) {
            p.fromBufferAttribute(pos, i).applyMatrix4(o.matrixWorld);
            (uv && uv.getY(i) < 0.5 ? leaves : bark).push(p.clone());
        }
    });
    const avgY = (a) => a.reduce((t, q) => t + q.y, 0) / Math.max(1, a.length);
    const crown = avgY(leaves) >= avgY(bark) ? leaves : bark;
    box = new THREE.Box3().setFromPoints(crown);
    const rMax = Math.max(...crown.map((q) => Math.hypot(q.x, q.z))) || 1;

    // Lamps: a few climb the trunk, then a spiral over the crown from the
    // outer rim to the top, each set on the foliage it lands on.
    const trunkLamps = Math.min(4, Math.max(1, Math.round(N * 0.08)));
    const points = [];
    for (let i = 0; i < N; i++) {
        if (i < trunkLamps) {
            const y = 1 + (box.min.y - 1.2) * (i + 1) / (trunkLamps + 0.5);
            const a = i * 2.2;
            points.push(new THREE.Vector3(Math.cos(a) * 0.45, y, Math.sin(a) * 0.45 + 0.1));
            continue;
        }
        const t = (i - trunkLamps) / Math.max(1, N - trunkLamps - 1);
        const a = t * Math.PI * 2 * Math.max(2.5, Math.min(7, N / 12));
        const rr = rMax * (0.92 - 0.82 * t);
        const tx = Math.cos(a) * rr;
        const tz = Math.sin(a) * rr;
        let best = null;
        let bestD = Infinity;
        for (const q of crown) {
            const d = (q.x - tx) ** 2 + (q.z - tz) ** 2 - q.y * 0.02;
            if (d < bestD) { bestD = d; best = q; }
        }
        points.push(new THREE.Vector3(tx, (best ? best.y : box.max.y) + 0.22, tz));
    }

    const start = new THREE.Vector3(0, 0.35, 0.9);
    const curve = new THREE.CatmullRomCurve3([start, ...points], false, 'centripetal');
    const thread = new THREE.Mesh(
        new THREE.TubeGeometry(curve, Math.max(80, N * 10), 0.03, 6, false),
        new THREE.MeshBasicMaterial({ color: 0xffcf6b, transparent: true, opacity: 0.9, fog: false })
    );
    scene.add(thread);
    const threadCount = thread.geometry.index.count;

    const glow = glowTexture(THREE);
    const makeLamp = (pt, size) => {
        const core = new THREE.Mesh(
            new THREE.SphereGeometry(size, 16, 10),
            new THREE.MeshBasicMaterial({ color: 0x6b5a46 })
        );
        core.position.copy(pt);
        const halo = new THREE.Sprite(new THREE.SpriteMaterial({
            map: glow, color: 0xffc85c, transparent: true, depthWrite: false,
            blending: THREE.AdditiveBlending, opacity: 0, fog: false,
        }));
        halo.scale.setScalar(size * 7);
        halo.position.copy(pt);
        scene.add(core, halo);
        return { core, halo, size };
    };
    const founderLamp = makeLamp(start, 0.3);
    const lamps = points.map((pt) => makeLamp(pt, N > 80 ? 0.1 : 0.14));

    // Fireflies drifting round the crown.
    const FF = 70;
    const ffGeo = new THREE.BufferGeometry();
    const ffBase = new Float32Array(FF * 3);
    for (let i = 0; i < FF; i++) {
        const a = Math.random() * Math.PI * 2;
        const r = Math.random() * rMax * 1.1;
        ffBase.set([Math.cos(a) * r, 1 + Math.random() * (box.max.y + 0.5), Math.sin(a) * r], i * 3);
    }
    ffGeo.setAttribute('position', new THREE.BufferAttribute(ffBase.slice(), 3));
    const fireflies = new THREE.Points(ffGeo, new THREE.PointsMaterial({
        map: glow, size: 0.35, color: 0xffd98a, transparent: true, depthWrite: false,
        blending: THREE.AdditiveBlending, opacity: 0.8, fog: false,
    }));
    scene.add(fireflies);

    const camera = new THREE.PerspectiveCamera(40, 1, 0.1, 200);
    const controls = new OrbitControls(camera, canvas);
    controls.enableDamping = true;
    controls.enablePan = false;
    controls.minDistance = 3.5;
    controls.maxDistance = 34;
    controls.minPolarAngle = Math.PI * 0.2;
    controls.maxPolarAngle = Math.PI * 0.52;
    controls.autoRotate = !reduce;
    controls.autoRotateSpeed = 0.5;
    controls.target.set(0, H * 0.45, 0);

    // Labels floating beside the lamps (current one large, two before it faint).
    const tags = [0, 1, 2].map((i) => {
        const el = document.createElement('div');
        el.className = 'sp-pp3t__tag sp-pp3t__tag--' + i;
        tagsEl.appendChild(el);
        return el;
    });
    // Zooming in names every lamp, so the whole line can be read on the tree.
    const minis = [];
    for (let j = 0; j <= N; j++) {
        const el = document.createElement('div');
        el.className = 'sp-pp3t__mini' + (j > 0 && line[j - 1].gap ? ' is-gap' : '');
        const nd = j === 0 ? data.founder : line[j - 1];
        const lbl = j === 0 ? data.founder.chip : (nd.n ? nd.n + '. ' : '');
        el.textContent = lbl + (j === 0 ? ': ' : '') + nd.name;
        if (nd.period) {
            const sp = document.createElement('span');
            sp.textContent = nd.period;
            el.appendChild(sp);
        }
        el.hidden = true;
        tagsEl.appendChild(el);
        minis.push(el);
    }
    let baseDist = 18;
    root.querySelectorAll('[data-zoom]').forEach((b) => b.addEventListener('click', () => {
        setPlaying(false);
        const dir = camera.position.clone().sub(controls.target);
        const d = dir.length() * (b.dataset.zoom === 'in' ? 0.7 : 1.4);
        camera.position.copy(controls.target).add(dir.setLength(Math.max(controls.minDistance, Math.min(controls.maxDistance, d))));
    }));
    const eraSet = buildEra(root, line, (k, user) => select(k, user));
    const items = list.querySelectorAll('button[data-k]');

    const C = {
        dim: new THREE.Color(0x8a6a3a), lit: new THREE.Color(0xffc85c), gap: new THREE.Color(0x777777),
        present: new THREE.Color(0xff8a2a), cur: new THREE.Color(0xffffff),
    };
    let k = 0;
    let threadGoal = 0;
    let threadNow = 0;
    const goal = new THREE.Vector3(0, H * 0.5, 0);

    function nodeAt(j) {
        return j === 0 ? { name: data.founder.name, period: data.founder.period, note: data.founder.note, label: data.founder.chip } : {
            name: line[j - 1].name,
            period: line[j - 1].period,
            label: line[j - 1].n ? 'No. ' + line[j - 1].n : line[j - 1].branch,
            url: line[j - 1].url,
            note: [line[j - 1].chip, line[j - 1].note].filter(Boolean).join('. '),
        };
    }

    function select(j, user) {
        k = Math.max(0, Math.min(N, j));
        if (user) { setPlaying(false); }
        lamps.forEach((l, i) => {
            const n = line[i];
            let c = i < k ? (n.status ? C.present : C.lit) : C.dim;
            if (n.gap) { c = C.gap; }
            if (i === k - 1) { c = C.cur; }
            l.core.material.color.copy(c);
            l.halo.material.opacity = i < k && !n.gap ? (i === k - 1 ? 1 : 0.55) : 0;
            l.halo.scale.setScalar(l.size * (i === k - 1 ? 14 : 7));
            l.core.scale.setScalar(i < k || n.gap ? 1 : 0.6);
        });
        founderLamp.core.material.color.copy(k === 0 ? C.cur : C.lit);
        founderLamp.halo.material.opacity = 1;
        threadGoal = threadCount * (k / Math.max(1, N));

        const nd = nodeAt(k);
        now.num.textContent = nd.label || '';
        now.name.textContent = nd.name;
        now.period.textContent = nd.period || '';
        now.note.textContent = nd.note || '';
        now.note.hidden = !nd.note;
        now.link.hidden = !nd.url;
        if (nd.url) { now.link.href = nd.url; }

        tags.forEach((el, i) => {
            const j = k - i;
            if (j < 0 || (j > 0 && line[j - 1].gap && i)) { el.hidden = true; return; }
            const t = nodeAt(j);
            el.hidden = false;
            el.innerHTML = '';
            const strong = document.createElement('strong');
            strong.textContent = t.name;
            el.appendChild(strong);
            if (t.period) {
                const sp = document.createElement('span');
                sp.textContent = t.period;
                el.appendChild(sp);
            }
            el.dataset.k = String(j);
        });

        items.forEach((b) => b.classList.toggle('is-current', +b.dataset.k === k));
        const cur = list.querySelector('button[data-k="' + k + '"]');
        if (cur) {
            const li = cur.parentElement;
            list.scrollTo({ top: li.offsetTop - list.clientHeight / 2 + li.clientHeight / 2, behavior: reduce ? 'auto' : 'smooth' });
        }
        eraSet(k);
        const pt = k === 0 ? start : points[k - 1];
        goal.set(pt.x * 0.25, H * 0.42 + (pt.y / H) * H * 0.12, pt.z * 0.25);
    }

    // Autoplay through time; a tap anywhere hands control to the reader.
    let playing = !reduce;
    let last = performance.now();
    const stepMs = Math.max(1100, Math.min(2600, 90000 / Math.max(1, N)));
    function setPlaying(on) {
        playing = on;
        playBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
        playBtn.textContent = on ? playBtn.dataset.labelPause : playBtn.dataset.labelPlay;
        controls.autoRotate = on && !reduce;
        last = performance.now();
    }
    playBtn.addEventListener('click', () => {
        if (!playing && k >= N) { select(0); }
        setPlaying(!playing);
    });
    items.forEach((b) => b.addEventListener('click', () => select(+b.dataset.k, true)));
    canvas.addEventListener('pointerdown', () => setPlaying(false));

    function resize() {
        const w = stage.clientWidth;
        const h = stage.clientHeight;
        if (!w || !h) { return; }
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        const dist = (w / h < 0.9 ? 25 : w / h < 1.4 ? 19 : 18);
        baseDist = dist;
        const dir = camera.position.clone().sub(controls.target);
        if (dir.lengthSq() < 1) { dir.set(0.35, 0.42, 1); }
        camera.position.copy(controls.target).add(dir.normalize().multiplyScalar(dist));
    }
    camera.position.set(6, H * 0.75, 17);
    new ResizeObserver(resize).observe(stage);
    resize();
    setPlaying(playing);
    select(playing ? 0 : N);
    root.classList.add('is-ready');

    const v3 = new THREE.Vector3();
    function placeTags() {
        const w = stage.clientWidth;
        const h = stage.clientHeight;
        const placed = [];
        const sr = stage.getBoundingClientRect();
        [playBtn, root.querySelector('.sp-pp3t__zoom'), root.querySelector('.sp-pp3t__now')].forEach((b) => {
            const q = b.getBoundingClientRect();
            if (q.width) { placed.push({ l: q.left - sr.left, t: q.top - sr.top, r: q.right - sr.left, b: q.bottom - sr.top }); }
        });
        tags.forEach((el, i) => {
            if (el.hidden) { return; }
            const j = +el.dataset.k;
            v3.copy(j === 0 ? start : points[j - 1]).project(camera);
            if (v3.z > 1) { el.style.opacity = '0'; return; }
            const x = (v3.x * 0.5 + 0.5) * w;
            const y = (-v3.y * 0.5 + 0.5) * h;
            const right = x < w * 0.62;
            const tw = el.offsetWidth;
            const left = Math.max(6, Math.min(w - tw - 6, right ? x + 14 : x - tw - 14));
            const top = Math.max(6, Math.min(h - el.offsetHeight - 6, y - el.offsetHeight / 2));
            const r = { l: left, t: top, r: left + tw, b: top + el.offsetHeight };
            // Earlier lamps give way when their label would cover a newer one.
            const clash = i > 0 && placed.some((q) => r.l < q.r && r.r > q.l && r.t < q.b && r.b > q.t);
            el.style.transform = 'translate(' + left.toFixed(1) + 'px,' + top.toFixed(1) + 'px)';
            el.style.opacity = clash ? '0' : '';
            if (!clash) { placed.push(r); }
        });
        // Close up, every lamp in view gets its name; nearest lamps win space.
        const zoomed = camera.position.distanceTo(controls.target) < baseDist * 0.72;
        root.classList.toggle('is-zoomed', zoomed);
        const order = [];
        for (let j = 0; j <= N; j++) {
            const el = minis[j];
            const shown = tags.some((tg) => !tg.hidden && +tg.dataset.k === j);
            if (!zoomed || shown) { el.hidden = true; continue; }
            const p = j === 0 ? start : points[j - 1];
            v3.copy(p).project(camera);
            if (v3.z > 1 || Math.abs(v3.x) > 1.05 || Math.abs(v3.y) > 1.05) { el.hidden = true; continue; }
            order.push({ j, x: (v3.x * 0.5 + 0.5) * w, y: (-v3.y * 0.5 + 0.5) * h, d: camera.position.distanceTo(p) });
        }
        order.sort((a, b) => a.d - b.d);
        order.forEach((o) => {
            const el = minis[o.j];
            el.hidden = false;
            const tw = el.offsetWidth;
            const th = el.offsetHeight;
            const left = Math.max(4, Math.min(w - tw - 4, o.x + 8));
            const top = Math.max(4, Math.min(h - th - 4, o.y - th / 2));
            const r = { l: left, t: top, r: left + tw, b: top + th };
            if (placed.some((q) => r.l < q.r && r.r > q.l && r.t < q.b && r.b > q.t)) { el.hidden = true; return; }
            placed.push(r);
            el.style.transform = 'translate(' + left.toFixed(1) + 'px,' + top.toFixed(1) + 'px)';
        });
    }

    function onScreen() {
        const r = stage.getBoundingClientRect();
        return r.bottom > 0 && r.top < window.innerHeight && !document.hidden;
    }
    const ffPos = ffGeo.attributes.position;
    function tick(t) {
        requestAnimationFrame(tick);
        if (!onScreen()) { last = t; return; }
        if (playing && t - last > (k >= N ? 6000 : stepMs)) {
            last = t;
            select(k >= N ? 0 : k + 1);
        }
        wind.value = t / 1000;
        for (let i = 0; i < FF; i++) {
            ffPos.setXYZ(i,
                ffBase[i * 3] + Math.sin(t / 1700 + i) * 0.4,
                ffBase[i * 3 + 1] + Math.sin(t / 1300 + i * 1.7) * 0.3,
                ffBase[i * 3 + 2] + Math.cos(t / 1900 + i) * 0.4);
        }
        ffPos.needsUpdate = true;
        fireflies.material.opacity = 0.55 + Math.sin(t / 500) * 0.25;
        threadNow += (threadGoal - threadNow) * 0.08;
        thread.geometry.setDrawRange(0, Math.round(threadNow / 3) * 3);
        if (k > 0) {
            const l = lamps[k - 1];
            l.halo.scale.setScalar(l.size * (13 + Math.sin(t / 300) * 2));
        }
        controls.target.lerp(goal, 0.04);
        controls.update();
        renderer.render(scene, camera);
        placeTags();
    }
    requestAnimationFrame(tick);
}

// Without WebGL the list and notes stay as the readable version of the tree.
function fallback(root) {
    root.classList.add('is-flat');
    root.querySelector('.sp-pp3t__stage').hidden = true;
}
blocks.forEach((root) => { if (!hasWebGL()) { fallback(root); } });
if (blocks.length && hasWebGL()) {
    blocks.forEach((root) => {
        whenNear(root, () => {
            build(root).catch((err) => {
                window.console && console.warn('Parampara tree unavailable:', err);
                fallback(root);
            });
        });
    });
}
