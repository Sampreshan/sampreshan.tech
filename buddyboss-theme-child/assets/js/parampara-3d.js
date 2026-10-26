/**
 * 3D Guru Parampara tree for Peetham pages.
 *
 * The founder sits at the roots; every Acharya is a lamp that rises up the
 * tree in order, joined by a golden thread. The Kaal Khanda slider moves
 * through time. three.js loads only when the tree is about to be seen, and
 * the 2D tree on the page remains the version for no-WebGL readers.
 */
const blocks = document.querySelectorAll('[data-sp-pp3d]');
const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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

async function build(root) {
    const data = JSON.parse(root.querySelector('.sp-pp3d__data').textContent);
    const v = '?ver=' + encodeURIComponent(data.ver);
    const lib = data.base + 'vendor/three/';
    const [THREE, { GLTFLoader }, { OrbitControls }] = await Promise.all([
        import(lib + 'three.module.min.js' + v),
        import(lib + 'GLTFLoader.js' + v),
        import(lib + 'OrbitControls.js' + v),
    ]);

    const view = root.querySelector('.sp-pp3d__view');
    const canvas = root.querySelector('.sp-pp3d__canvas');
    const range = root.querySelector('.sp-pp3d__range');
    const card = {
        num: root.querySelector('.sp-pp3d__num'),
        name: root.querySelector('.sp-pp3d__name'),
        period: root.querySelector('.sp-pp3d__period'),
        link: root.querySelector('.sp-pp3d__link'),
    };
    const line = data.line;
    const N = line.length;
    const H = 10;

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;

    const scene = new THREE.Scene();
    scene.fog = new THREE.Fog(0x1a0f08, 22, 46);
    const camera = new THREE.PerspectiveCamera(42, 1, 0.1, 120);
    camera.position.set(0, H * 0.55, 19);

    scene.add(new THREE.HemisphereLight(0xffe7c2, 0x2a1a0e, 1.6));
    const sun = new THREE.DirectionalLight(0xffd59a, 2.2);
    sun.position.set(6, 14, 8);
    scene.add(sun);

    // Ground: a soft earth disc for the roots.
    const ground = new THREE.Mesh(
        new THREE.CircleGeometry(9, 64),
        new THREE.MeshStandardMaterial({ color: 0x3b2614, roughness: 1 })
    );
    ground.rotation.x = -Math.PI / 2;
    scene.add(ground);

    // Tree model (Kenney Nature Kit, CC0), scaled to the scene height.
    const gltf = await new GLTFLoader().loadAsync(data.base + 'models/parampara-tree.glb' + v);
    const tree = gltf.scene;
    const box = new THREE.Box3().setFromObject(tree);
    const s = H / (box.max.y - box.min.y);
    tree.scale.setScalar(s);
    tree.position.set(-(box.min.x + box.max.x) / 2 * s, -box.min.y * s, -(box.min.z + box.max.z) / 2 * s);
    scene.add(tree);
    tree.updateMatrixWorld(true);

    // Canopy bounds from the leaf material, so lamps sit on its surface.
    const leaf = new THREE.Box3();
    tree.traverse((o) => {
        if (o.isMesh && /leaf/i.test(o.material && o.material.name)) {
            leaf.expandByObject(o);
        }
    });
    if (leaf.isEmpty()) {
        leaf.set(new THREE.Vector3(-3, H * 0.4, -3), new THREE.Vector3(3, H, 3));
    }
    const cy = (leaf.min.y + leaf.max.y) / 2;
    const ry = (leaf.max.y - leaf.min.y) / 2;
    const rx = Math.max(leaf.max.x - leaf.min.x, leaf.max.z - leaf.min.z) / 2;

    // Lamp positions: a golden-angle spiral from the trunk up over the canopy.
    const points = [];
    const yStart = 0.9;
    const yEnd = leaf.max.y + 0.15;
    for (let i = 0; i < N; i++) {
        const t = N === 1 ? 1 : i / (N - 1);
        const y = yStart + t * (yEnd - yStart);
        let r;
        if (y < leaf.min.y + 0.2) {
            r = 0.55 + (y / leaf.min.y) * 0.35;
        } else {
            const k = Math.min(1, Math.abs(y - cy) / ry);
            r = Math.max(0.5, rx * Math.sqrt(1 - k * k)) + 0.35;
        }
        const a = i * 2.39996;
        points.push(new THREE.Vector3(Math.cos(a) * r, y, Math.sin(a) * r));
    }

    // Golden thread joining the line in order.
    const start = new THREE.Vector3(0, 0.35, 0);
    const curve = new THREE.CatmullRomCurve3([start, ...points], false, 'centripetal');
    const segs = Math.max(64, N * 8);
    const thread = new THREE.Mesh(
        new THREE.TubeGeometry(curve, segs, 0.035, 6, false),
        new THREE.MeshBasicMaterial({ color: 0xf6c35b, transparent: true, opacity: 0.85 })
    );
    scene.add(thread);
    const threadIndex = thread.geometry.index.count;

    // Founder at the roots.
    const founder = new THREE.Mesh(
        new THREE.SphereGeometry(0.42, 32, 16),
        new THREE.MeshStandardMaterial({ color: 0xffb347, emissive: 0xff8c1a, emissiveIntensity: 1.4 })
    );
    founder.position.copy(start);
    scene.add(founder);

    // One instanced mesh for every Acharya lamp.
    const lamps = new THREE.InstancedMesh(
        new THREE.SphereGeometry(N > 80 ? 0.13 : 0.18, 16, 10),
        new THREE.MeshBasicMaterial({ color: 0xffffff }),
        N
    );
    const m = new THREE.Matrix4();
    points.forEach((p, i) => lamps.setMatrixAt(i, m.makeTranslation(p.x, p.y, p.z)));
    scene.add(lamps);

    const halo = new THREE.Mesh(
        new THREE.SphereGeometry(0.42, 24, 12),
        new THREE.MeshBasicMaterial({ color: 0xffd27a, transparent: true, opacity: 0.35, depthWrite: false })
    );
    scene.add(halo);

    const C = {
        lit: new THREE.Color(0xffc85c),
        dim: new THREE.Color(0x6b5a46),
        gap: new THREE.Color(0x4a4a4a),
        present: new THREE.Color(0xff7a1a),
        sel: new THREE.Color(0xffffff),
    };

    const controls = new OrbitControls(camera, canvas);
    controls.enableDamping = true;
    controls.enablePan = false;
    controls.minDistance = 7;
    controls.maxDistance = 28;
    controls.maxPolarAngle = Math.PI * 0.55;
    controls.autoRotate = !reduce;
    controls.autoRotateSpeed = 0.6;
    controls.target.set(0, H * 0.45, 0);
    controls.addEventListener('start', () => { controls.autoRotate = false; });

    const goal = new THREE.Vector3(0, H * 0.45, 0);

    function select(k) {
        k = Math.max(0, Math.min(N, k));
        range.value = String(k);
        for (let i = 0; i < N; i++) {
            let c = i < k ? C.lit : C.dim;
            if (line[i].gap) { c = C.gap; }
            if (line[i].status && i < k) { c = C.present; }
            if (i === k - 1) { c = C.sel; }
            lamps.setColorAt(i, c);
        }
        lamps.instanceColor.needsUpdate = true;
        thread.geometry.setDrawRange(0, Math.round(threadIndex * (k / Math.max(1, N))));

        const p = k === 0 ? start : points[k - 1];
        halo.position.copy(p);
        goal.set(0, Math.min(H * 0.8, Math.max(H * 0.25, p.y)), 0);

        const node = k === 0 ? null : line[k - 1];
        card.num.textContent = node ? (node.n ? 'No. ' + node.n : (node.branch || '')) : 'Founder';
        card.name.textContent = node ? node.name : data.founder.name;
        card.period.textContent = node ? node.period : data.founder.period;
        if (node && node.url) {
            card.link.href = node.url;
            card.link.hidden = false;
        } else {
            card.link.hidden = true;
        }
        needs = true;
    }

    range.addEventListener('input', () => {
        controls.autoRotate = false;
        select(parseInt(range.value, 10) || 0);
    });

    // Tap a lamp to read it.
    const ray = new THREE.Raycaster();
    const ptr = new THREE.Vector2();
    let down = null;
    canvas.addEventListener('pointerdown', (e) => { down = [e.clientX, e.clientY]; });
    canvas.addEventListener('pointerup', (e) => {
        if (!down || Math.hypot(e.clientX - down[0], e.clientY - down[1]) > 6) { return; }
        const r = canvas.getBoundingClientRect();
        ptr.set(((e.clientX - r.left) / r.width) * 2 - 1, -((e.clientY - r.top) / r.height) * 2 + 1);
        ray.setFromCamera(ptr, camera);
        const hit = ray.intersectObjects([lamps, founder], false)[0];
        if (hit) {
            controls.autoRotate = false;
            select(hit.object === founder ? 0 : hit.instanceId + 1);
        }
    });

    let needs = true;
    function resize() {
        const w = view.clientWidth;
        const h = view.clientHeight;
        if (!w || !h) { return; }
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.position.z = w < 520 ? 23 : 19;
        camera.updateProjectionMatrix();
        needs = true;
    }
    new ResizeObserver(resize).observe(view);
    resize();
    select(N);

    root.classList.add('is-ready');

    function onScreen() {
        const r = view.getBoundingClientRect();
        return r.bottom > 0 && r.top < window.innerHeight && !document.hidden;
    }
    function tick() {
        requestAnimationFrame(tick);
        if (!onScreen()) { return; }
        controls.target.lerp(goal, 0.08);
        halo.scale.setScalar(1 + Math.sin(performance.now() / 380) * 0.12);
        controls.update();
        renderer.render(scene, camera);
        needs = false;
    }
    tick();
}

if (blocks.length && hasWebGL()) {
    blocks.forEach((root) => {
        root.hidden = false;
        whenNear(root, () => {
            build(root).catch(() => {
                root.hidden = true;
            });
        });
    });
}
