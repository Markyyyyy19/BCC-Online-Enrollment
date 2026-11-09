(() => {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        let timer;
        const pause = () => clearTimeout(timer);
        const resume = () => {
            pause();
            if (toast.hasAttribute('data-autohide') && !toast.matches(':hover') && !toast.contains(document.activeElement)) {
                timer = setTimeout(() => toast.remove(), 6500);
            }
        };
        toast.querySelector('[data-toast-close]').addEventListener('click', () => { pause(); toast.remove(); });
        toast.addEventListener('mouseenter', pause);
        toast.addEventListener('mouseleave', resume);
        toast.addEventListener('focusin', pause);
        toast.addEventListener('focusout', () => setTimeout(resume, 0));
        resume();
    });
})();
