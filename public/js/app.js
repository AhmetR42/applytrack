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

// Animated "light speed" background: red and white streaks rushing out of a vanishing point.
// Only drawn in dark mode; it pauses when the tab is hidden.
const canvas = document.querySelector('[data-streaks]');
if (canvas) {
    const ctx = canvas.getContext('2d');
    const dark = window.matchMedia('(prefers-color-scheme: dark)');
    const streaks = [];
    let width = 0;
    let height = 0;
    let frame = null;

    const resize = () => {
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = width * ratio;
        canvas.height = height * ratio;
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    };

    // Each streak lives on a fixed angle and travels from the centre to the edge.
    const spawn = (streak = {}) => {
        const side = Math.random() < 0.5 ? -1 : 1;
        streak.angle = (side < 0 ? Math.PI : 0) + (Math.random() - 0.5) * 1.5;
        streak.depth = Math.random() * 0.2;
        streak.speed = 0.004 + Math.random() * 0.01;
        streak.length = 0.08 + Math.random() * 0.25;
        streak.width = 0.5 + Math.random() * 2;
        streak.red = side < 0 ? Math.random() < 0.8 : Math.random() < 0.25;
        return streak;
    };

    for (let i = 0; i < 140; i++) {
        const streak = spawn();
        streak.depth = Math.random();
        streaks.push(streak);
    }

    const draw = () => {
        ctx.fillStyle = 'rgba(7, 7, 7, 0.35)';
        ctx.fillRect(0, 0, width, height);

        const cx = width / 2;
        const cy = height * 0.55;
        const reach = Math.hypot(width, height) * 0.7;

        for (const s of streaks) {
            s.depth += s.speed * (0.4 + s.depth * 2.5);
            if (s.depth > 1.2) spawn(s);

            // Ease so streaks accelerate as they come closer
            const head = Math.pow(s.depth, 2.2) * reach;
            const tail = Math.pow(Math.max(s.depth - s.length, 0), 2.2) * reach;
            const dx = Math.cos(s.angle);
            const dy = Math.sin(s.angle) * 0.45;

            const gradient = ctx.createLinearGradient(cx + dx * tail, cy + dy * tail, cx + dx * head, cy + dy * head);
            const colour = s.red ? '255, 42, 53' : '235, 235, 230';
            gradient.addColorStop(0, `rgba(${colour}, 0)`);
            gradient.addColorStop(1, `rgba(${colour}, ${Math.min(s.depth, 0.9)})`);

            ctx.strokeStyle = gradient;
            ctx.lineWidth = s.width * (0.3 + s.depth);
            ctx.beginPath();
            ctx.moveTo(cx + dx * tail, cy + dy * tail);
            ctx.lineTo(cx + dx * head, cy + dy * head);
            ctx.stroke();
        }
    };

    const loop = () => {
        draw();
        frame = requestAnimationFrame(loop);
    };

    const start = () => {
        cancelAnimationFrame(frame);
        canvas.hidden = !dark.matches;
        if (!dark.matches) return;

        resize();
        ctx.fillStyle = '#070707';
        ctx.fillRect(0, 0, width, height);

        if (reducedMotion) {
            for (let i = 0; i < 40; i++) draw();
        } else {
            loop();
        }
    };

    window.addEventListener('resize', start);
    dark.addEventListener('change', start);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) cancelAnimationFrame(frame);
        else start();
    });
    start();
}
