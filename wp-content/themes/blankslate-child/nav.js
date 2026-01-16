document.addEventListener('DOMContentLoaded', () => {
    const btn = document.querySelector('.menu-toggle');
    if (!btn) return;

    btn.addEventListener('click', () => {
        document.documentElement.classList.toggle('nav-open');
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!expanded));
    });

    // mobile: add a separate toggle button for submenu parents
    const mq = window.matchMedia('(max-width: 960px)');

    document.querySelectorAll('.menu-item-has-children').forEach(li => {
        const link = li.querySelector(':scope > a');
        if (!link) return;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'submenu-toggle';
        btn.setAttribute('aria-label', 'Open submenu');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (mq.matches) li.classList.toggle('sub-open');
        });
        li.insertBefore(btn, link);   // instead of link.nextSibling
    });

});
