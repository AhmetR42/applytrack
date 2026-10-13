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
