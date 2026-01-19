(function () {
    function applyExternalLinkRules() {
        const currentHost = window.location.host;

        document.querySelectorAll('a[href]').forEach((a) => {
            const raw = a.getAttribute('href');
            if (!raw) return;

            // Skip anchors and non-http(s) schemes
            if (
                raw.startsWith('#') ||
                raw.startsWith('mailto:') ||
                raw.startsWith('tel:') ||
                raw.startsWith('javascript:')
            ) {
                return;
            }

            let url;
            try {
                url = new URL(raw, window.location.href);
            } catch (e) {
                return;
            }

            if (url.protocol !== 'http:' && url.protocol !== 'https:') return;
            if (url.host === currentHost) return;

            a.target = '_blank';

            // Preserve existing rel, add security tokens
            const rel = (a.getAttribute('rel') || '')
                .split(/\s+/)
                .filter(Boolean);

            if (!rel.includes('noopener')) rel.push('noopener');
            if (!rel.includes('noreferrer')) rel.push('noreferrer');

            a.setAttribute('rel', rel.join(' '));
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyExternalLinkRules);
    } else {
        applyExternalLinkRules();
    }
})();
