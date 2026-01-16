// assets/src/js/main.js
(() => {
    function initNavToggle() {
        const header = document.getElementById("site-header");
        if (!header) return;

        const toggle = header.querySelector(".nav-toggle");
        if (!toggle) return;

        const navId = toggle.getAttribute("aria-controls");
        const nav = navId ? document.getElementById(navId) : null;
        if (!nav) return;

        const menu = nav.querySelector(".primary-menu");
        if (!menu) return;

        const isMobile = () => window.getComputedStyle(toggle).display !== "none";

        function getSubmenuToggle(li) {
            return li.querySelector(".submenu-toggle");
        }

        function setSubmenuOpen(li, open) {
            li.classList.toggle("submenu-open", open);
            const btn = getSubmenuToggle(li);
            if (btn) btn.setAttribute("aria-expanded", String(open));
        }

        function closeAllSubmenusExcept(keepLi) {
            header.querySelectorAll(".primary-menu li.submenu-open").forEach((openLi) => {
                if (keepLi && openLi === keepLi) return;
                setSubmenuOpen(openLi, false);
            });
        }

        function collapseAllSubmenus() {
            closeAllSubmenusExcept(null);
        }

        function ensureSubmenuToggle(li, idx) {
            // Find direct child <a> and <ul> (no :scope for max compatibility)
            const directChildren = Array.from(li.children);
            const link = directChildren.find((el) => el.tagName === "A");
            const submenu = directChildren.find((el) => el.tagName === "UL");
            if (!link || !submenu) return;

            if (!submenu.id) submenu.id = `submenu-${idx}`;

            // Add chevron button once
            let btn = getSubmenuToggle(li);
            if (!btn) {
                btn = document.createElement("button");
                btn.type = "button";
                btn.className = "submenu-toggle";
                btn.setAttribute("aria-controls", submenu.id);
                btn.setAttribute("aria-expanded", "false");
                btn.setAttribute("aria-label", "Toggle submenu");
                btn.innerHTML = '<span class="submenu-toggle__icon" aria-hidden="true"></span>';

                link.insertAdjacentElement("afterend", btn);

                // Chevron click: toggle submenu (mobile only), close others on open
                btn.addEventListener("click", (e) => {
                    if (!isMobile()) return;
                    e.preventDefault();
                    e.stopPropagation();

                    const willOpen = !li.classList.contains("submenu-open");
                    if (willOpen) closeAllSubmenusExcept(li);
                    setSubmenuOpen(li, willOpen);
                });

                // Parent link behavior (mobile only):
                // - if closed: open + close others, prevent navigation
                // - if open: allow navigation
                link.addEventListener("click", (e) => {
                    if (!isMobile()) return;

                    const isOpen = li.classList.contains("submenu-open");
                    if (!isOpen) {
                        e.preventDefault();
                        closeAllSubmenusExcept(li);
                        setSubmenuOpen(li, true);
                    }
                    // else: open -> navigate normally
                });
            }

            // Start collapsed
            setSubmenuOpen(li, false);
        }

        // Build submenu toggles + link behavior
        Array.from(menu.querySelectorAll("li.menu-item-has-children")).forEach((li, idx) => {
            ensureSubmenuToggle(li, idx);
        });

        function setOpen(isOpen) {
            header.classList.toggle("nav-open", isOpen);
            toggle.setAttribute("aria-expanded", String(isOpen));
            if (!isOpen) collapseAllSubmenus();
        }

        toggle.addEventListener("click", () => {
            const open = header.classList.contains("nav-open");
            setOpen(!open);
        });

        // Close on ESC
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && header.classList.contains("nav-open")) {
                setOpen(false);
                toggle.focus();
            }
        });

        // Close when clicking outside header (mobile UX)
        document.addEventListener("click", (e) => {
            if (!header.classList.contains("nav-open")) return;
            const target = e.target;
            if (target instanceof Node && !header.contains(target)) {
                setOpen(false);
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initNavToggle);
    } else {
        initNavToggle();
    }
})();
