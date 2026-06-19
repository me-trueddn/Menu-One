document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.c-dot');

    if (slides.length && dots.length) {
        let current = 0;
        let timer;

        const goTo = (idx) => {
            slides[current].classList.remove('active');
            slides[current].classList.add('exit');
            setTimeout(() => slides[current].classList.remove('exit'), 550);

            current = idx;
            slides[current].classList.add('active');
            dots.forEach((d, i) => d.classList.toggle('active', i === current));
        };

        const next = () => goTo((current + 1) % slides.length);

        const startTimer = () => {
            clearInterval(timer);
            timer = setInterval(next, 4000);
        };

        dots.forEach((d) => {
            d.addEventListener('click', () => {
                goTo(parseInt(d.dataset.idx, 10));
                startTimer();
            });
        });

        startTimer();
    }

    const pwd = document.getElementById('password');
    const togglePwd = document.getElementById('togglePwd');
    const eyeIcon = document.getElementById('eyeIcon');

    if (pwd && togglePwd && eyeIcon) {
        togglePwd.addEventListener('click', () => {
            const show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            eyeIcon.className = show ? 'fa fa-eye' : 'fa fa-eye-slash';
        });
    }

    document.querySelectorAll('[data-toggle-target]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.toggleTarget);
            const icon = btn.querySelector('i');
            if (!input || !icon) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'fa fa-eye' : 'fa fa-eye-slash';
        });
    });

    const overlay = document.getElementById('registerOverlay');
    const openRegister = document.getElementById('openRegisterPanel');
    const closeRegister = document.getElementById('registerClose');

    const openPanel = () => {
        if (!overlay) return;
        overlay.hidden = false;
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('register-open');
    };

    const closePanel = () => {
        if (!overlay) return;
        overlay.classList.remove('open');
        overlay.hidden = true;
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('register-open');
    };

    openRegister?.addEventListener('click', openPanel);
    closeRegister?.addEventListener('click', closePanel);
    overlay?.addEventListener('click', (event) => {
        if (event.target === overlay) closePanel();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closePanel();
    });
});
