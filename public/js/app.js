// ApplyTrack front-end behaviour. Everything still works without JavaScript.

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const page = document.querySelector('[data-page]');

// Language toggle: slide the thumb, fade the page out, then submit.
document.querySelectorAll('[data-lang-toggle]').forEach((form) => {
    form.addEventListener('click', (event) => {
        const button = event.target.closest('button[name="locale"]');
        if (!button) return;

        event.preventDefault();
        if (form.dataset.locale === button.value) return;

        form.dataset.locale = button.value;
        sessionStorage.setItem('applytrack:lang-switched', '1');

        const submit = () => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'locale';
            input.value = button.value;
            form.append(input);
            form.submit();
        };

        if (reducedMotion) return submit();

        page?.classList.add('is-leaving');
        setTimeout(submit, 350);
    });
});

// Fade the page back in after a language switch.
try {
    if (sessionStorage.getItem('applytrack:lang-switched')) {
        sessionStorage.removeItem('applytrack:lang-switched');
        page?.classList.add('is-entering');
    }
} catch {
    // Storage can be unavailable (private mode); the animation is optional.
}

// Count-up animation for dashboard numbers.
if (!reducedMotion) {
    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = Number(el.dataset.count);
        if (!target) return;

        const start = performance.now();
        const duration = 700;
        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            el.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3)));
            if (progress < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    });
}

// Submit selects marked with data-autosubmit as soon as they change.
document.querySelectorAll('select[data-autosubmit]').forEach((select) => {
    select.addEventListener('change', () => select.form.requestSubmit());
});

// Ask before destructive actions.
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

// Hide flash messages after a few seconds.
document.querySelectorAll('[data-flash]').forEach((flash) => {
    setTimeout(() => flash.classList.add('is-hidden'), 4000);
});

// Light rails: a slow, deep "light speed" scene drawn on a canvas.
// Faint rails fan out of a vanishing point, light pulses glide along them,
// dust drifts towards the viewer, a perspective floor rolls by, and now and
// then a bright comet shoots past. The vanishing point leans towards the pointer.
function createLightRails(canvas, options) {
    const ctx = canvas.getContext('2d');
    const opts = Object.assign({ speed: 0.6, rails: 56, glow: 0.6 }, options);
    const RED = '255, 42, 53';
    const WHITE = '236, 236, 230';
    let w = 0;
    let h = 0;
    let time = 0;
    let floorOffset = 0;
    let nextComet = 240;
    // Canvas pixels per CSS pixel. The scene is soft, so it is drawn at
    // (at most) 1x and lowered further if frames take too long; drawing at
    // the screen's full 2x density costs four times the pixels and stutters.
    let scale = 1;
    let slowFrames = 0;
    const vp = { x: 0, y: 0 };
    const lean = { x: 0, y: 0, tx: 0, ty: 0 };
    const rails = [];
    const dust = [];
    const comets = [];

    const rand = (min, max) => min + Math.random() * (max - min);

    function buildRails() {
        rails.length = 0;
        for (let i = 0; i < opts.rails; i++) {
            const left = i % 2 === 0;
            // Most rails sit below the horizon, like a road seen from inside a car.
            const spread = Math.pow(Math.random(), 0.7) * 0.62 - 0.12;
            rails.push({
                angle: left ? Math.PI + spread : -spread,
                red: left ? Math.random() < 0.75 : Math.random() < 0.2,
                width: rand(0.6, 2),
                t: rand(0, 1.4),
                speed: rand(0.6, 1.4),
                length: rand(0.18, 0.45),
            });
        }
    }

    function buildDust() {
        dust.length = 0;
        for (let i = 0; i < 70; i++) {
            dust.push({ x: rand(-1, 1), y: rand(-0.6, 0.8), z: rand(0.05, 1), red: Math.random() < 0.3 });
        }
    }

    function resize(width, height) {
        w = width;
        h = height;
        canvas.width = Math.round(w * scale);
        canvas.height = Math.round(h * scale);
        ctx.setTransform(scale, 0, 0, scale, 0, 0);
    }

    // dt is the frame time in 60 fps frames; lower the resolution while it stays high.
    function watch(dt) {
        slowFrames = dt > 1.35 ? slowFrames + 1 : Math.max(slowFrames - 1, 0);
        if (slowFrames > 45 && scale > 0.5) {
            scale = Math.max(scale - 0.25, 0.5);
            slowFrames = 0;
            resize(w, h);
        }
    }

    function setOptions(next) {
        const rebuild = next.rails !== undefined && next.rails !== opts.rails;
        Object.assign(opts, next);
        if (rebuild) buildRails();
    }

    // nx, ny in -1..1: where the pointer is relative to the centre.
    function setPointer(nx, ny) {
        lean.tx = nx;
        lean.ty = ny;
    }

    // A gradient line. The glow is a wider, fainter pass underneath:
    // much cheaper than canvas shadowBlur, so it stays smooth on laptops.
    function stroke(x1, y1, x2, y2, colour, alphaTail, alphaHead, width, glow) {
        const g = ctx.createLinearGradient(x1, y1, x2, y2);
        g.addColorStop(0, `rgba(${colour}, ${alphaTail})`);
        g.addColorStop(1, `rgba(${colour}, ${alphaHead})`);
        ctx.strokeStyle = g;
        ctx.beginPath();
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
        if (glow > 0) {
            ctx.globalAlpha = 0.18 * glow;
            ctx.lineWidth = width * 5;
            ctx.stroke();
            ctx.globalAlpha = 1;
        }
        ctx.lineWidth = width;
        ctx.stroke();
    }

    function dot(x, y, radius, colour, alpha, glow) {
        if (glow > 0) {
            ctx.fillStyle = `rgba(${colour}, ${alpha * 0.2 * glow})`;
            ctx.beginPath();
            ctx.arc(x, y, radius * 4, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.fillStyle = `rgba(255, 255, 255, ${alpha})`;
        ctx.beginPath();
        ctx.arc(x, y, radius, 0, Math.PI * 2);
        ctx.fill();
    }

    function drawBackdrop() {
        ctx.globalCompositeOperation = 'source-over';
        ctx.fillStyle = '#070707';
        ctx.fillRect(0, 0, w, h);

        // Breathing glow on the horizon
        const breath = 0.85 + 0.15 * Math.sin(time * 0.012);
        const haze = ctx.createRadialGradient(vp.x, vp.y, 0, vp.x, vp.y, w * 0.5);
        haze.addColorStop(0, `rgba(${RED}, ${0.14 * opts.glow * breath})`);
        haze.addColorStop(0.4, `rgba(${RED}, ${0.04 * opts.glow * breath})`);
        haze.addColorStop(1, `rgba(${RED}, 0)`);
        ctx.fillStyle = haze;
        ctx.fillRect(0, 0, w, h);

        // Horizon line, brightest in the middle
        const line = ctx.createLinearGradient(0, 0, w, 0);
        line.addColorStop(0, `rgba(${WHITE}, 0)`);
        line.addColorStop(0.5, `rgba(${WHITE}, 0.18)`);
        line.addColorStop(1, `rgba(${WHITE}, 0)`);
        ctx.fillStyle = line;
        ctx.fillRect(0, vp.y - 0.5, w, 1);
    }

    function drawFloor(dt) {
        // Horizontal lines rolling towards the viewer give the floor depth.
        floorOffset = (floorOffset + dt * opts.speed * 0.0025) % 1;
        const count = 14;
        for (let i = 0; i < count; i++) {
            const z = (i + floorOffset) / count;
            const y = vp.y + (h - vp.y) * Math.pow(z, 2.6);
            const g = ctx.createLinearGradient(0, 0, w, 0);
            const a = 0.07 * z;
            g.addColorStop(0, `rgba(${WHITE}, 0)`);
            g.addColorStop(0.5, `rgba(${WHITE}, ${a})`);
            g.addColorStop(1, `rgba(${WHITE}, 0)`);
            ctx.fillStyle = g;
            ctx.fillRect(0, y, w, 1);
        }
    }

    function drawDust(dt) {
        const focal = w * 0.09;
        for (const p of dust) {
            p.z -= dt * opts.speed * 0.0016;
            if (p.z <= 0.03) {
                p.x = rand(-1, 1);
                p.y = rand(-0.6, 0.8);
                p.z = 1;
            }
            const sx = vp.x + (p.x / p.z) * focal;
            const sy = vp.y + (p.y / p.z) * focal * 0.6;
            if (sx < -20 || sx > w + 20 || sy < -20 || sy > h + 20) continue;
            const size = Math.min(0.4 / p.z, 3.2);
            const alpha = Math.min((1 - p.z) * 0.5, 0.45);
            ctx.fillStyle = `rgba(${p.red ? RED : WHITE}, ${alpha})`;
            ctx.beginPath();
            ctx.arc(sx, sy, size, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function drawRails(dt, reach) {
        for (const r of rails) {
            const dx = Math.cos(r.angle);
            const dy = Math.sin(r.angle) * 0.5;
            const colour = r.red ? RED : WHITE;

            // The rail itself: a faint fixed hairline.
            ctx.strokeStyle = `rgba(${colour}, 0.035)`;
            ctx.lineWidth = 0.6;
            ctx.beginPath();
            ctx.moveTo(vp.x, vp.y);
            ctx.lineTo(vp.x + dx * reach, vp.y + dy * reach);
            ctx.stroke();

            // A pulse gliding outwards, speeding up gently with perspective.
            r.t += opts.speed * 0.0011 * r.speed * dt * (0.5 + r.t);
            if (r.t > 1.4) {
                r.t = 0;
                r.length = rand(0.18, 0.45);
            }
            const head = Math.pow(r.t, 1.8) * reach;
            const tail = Math.pow(Math.max(r.t - r.length, 0), 1.8) * reach;
            const fade = Math.min(r.t * 3, 1) * Math.min(Math.max((1.4 - r.t) * 2.5, 0), 1);
            if (head - tail < 1) continue;

            const hx = vp.x + dx * head;
            const hy = vp.y + dy * head;
            stroke(vp.x + dx * tail, vp.y + dy * tail, hx, hy, colour, 0, 0.75 * fade, r.width * (0.4 + r.t), opts.glow);

            // White-hot head
            dot(hx, hy, r.width * (0.3 + r.t * 0.6), colour, 0.7 * fade, opts.glow);
        }
    }

    function drawComets(dt, reach) {
        nextComet -= dt;
        if (nextComet <= 0) {
            const left = Math.random() < 0.5;
            const spread = rand(-0.08, 0.45);
            comets.push({ angle: left ? Math.PI + spread : -spread, red: Math.random() < 0.6, t: 0 });
            nextComet = rand(360, 720) / Math.max(opts.speed, 0.2);
        }
        for (let i = comets.length - 1; i >= 0; i--) {
            const c = comets[i];
            c.t += opts.speed * 0.006 * dt * (0.4 + c.t);
            if (c.t > 1.5) {
                comets.splice(i, 1);
                continue;
            }
            const dx = Math.cos(c.angle);
            const dy = Math.sin(c.angle) * 0.5;
            const head = Math.pow(c.t, 1.6) * reach;
            const tail = Math.pow(Math.max(c.t - 0.6, 0), 1.6) * reach;
            const fade = Math.min(c.t * 4, 1) * Math.min((1.5 - c.t) * 3, 1);
            stroke(vp.x + dx * tail, vp.y + dy * tail, vp.x + dx * head, vp.y + dy * head,
                c.red ? RED : WHITE, 0, fade, 1.5 + c.t * 3, opts.glow * 1.5);
        }
    }

    function frame(dt) {
        watch(dt);
        time += dt;
        lean.x += (lean.tx - lean.x) * 0.03 * dt;
        lean.y += (lean.ty - lean.y) * 0.03 * dt;
        vp.x = w * 0.5 + lean.x * w * 0.04;
        vp.y = h * 0.56 + lean.y * h * 0.035;
        const reach = Math.hypot(w, h) * 0.65;

        drawBackdrop();
        drawFloor(dt);
        ctx.globalCompositeOperation = 'lighter';
        ctx.lineCap = 'round';
        drawDust(dt);
        drawRails(dt, reach);
        drawComets(dt, reach);
        ctx.globalCompositeOperation = 'source-over';
    }

    buildRails();
    buildDust();

    return { resize, frame, setPointer, setOptions };
}

// Animated background in dark mode.
const canvas = document.querySelector('[data-streaks]');
if (canvas) {
    const dark = window.matchMedia('(prefers-color-scheme: dark)');
    const scene = createLightRails(canvas);
    let frame = null;
    let last = 0;

    const loop = (now) => {
        const dt = Math.min((now - last) / 16.67, 3);
        last = now;
        scene.frame(dt);
        frame = requestAnimationFrame(loop);
    };

    const start = () => {
        cancelAnimationFrame(frame);
        canvas.hidden = !dark.matches;
        if (!dark.matches || document.hidden) return;

        scene.resize(window.innerWidth, window.innerHeight);
        if (reducedMotion) {
            for (let i = 0; i < 120; i++) scene.frame(1);
            return;
        }
        last = performance.now();
        frame = requestAnimationFrame(loop);
    };

    window.addEventListener('pointermove', (event) => {
        scene.setPointer(event.clientX / window.innerWidth * 2 - 1, event.clientY / window.innerHeight * 2 - 1);
    });
    window.addEventListener('resize', start);
    dark.addEventListener('change', start);
    document.addEventListener('visibilitychange', start);
    start();
}
