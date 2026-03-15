/**
 * login.js — Login page behaviour (lockout timer, throttle)
 */
document.addEventListener('DOMContentLoaded', function () {
    const form      = document.getElementById('loginForm');
    const btn       = document.getElementById('loginButton');
    const timerEl   = document.getElementById('lockoutTimer');
    let canSubmit   = true;

    // Lockout countdown (value injected by blade as window.lockoutSeconds)
    if (timerEl && window.lockoutSeconds > 0) {
        let remaining = window.lockoutSeconds;

        (function tick() {
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            timerEl.textContent = `${m}:${String(s).padStart(2, '0')}`;
            remaining > 0 ? (remaining--, setTimeout(tick, 1000)) : location.reload();
        })();
    }

    // Throttle rapid submissions
    form?.addEventListener('submit', function (e) {
        if (!canSubmit) { e.preventDefault(); return; }
        if (btn && !btn.disabled) { btn.textContent = 'LOGGING IN…'; btn.disabled = true; }
        canSubmit = false;
        setTimeout(() => { canSubmit = true; }, 2000);
    });
});