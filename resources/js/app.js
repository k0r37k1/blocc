import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    Alpine.data('codeBlocks', () => ({
        init() {
            this.$el.querySelectorAll('.code-block').forEach(block => {
                const language = block.dataset.language || 'text';
                const pre = block.querySelector('pre');
                if (!pre) return;

                // Language badge (top-left)
                const badge = document.createElement('span');
                badge.className = 'code-lang-badge';
                badge.textContent = language;
                block.appendChild(badge);

                // Copy button (top-right) — clipboard icon, checkmark on success
                const copyIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
                const checkIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

                const btn = document.createElement('button');
                btn.className = 'code-copy-btn';
                btn.setAttribute('aria-label', 'Kopieren');

                const icon = document.createElement('span');
                icon.innerHTML = copyIcon;

                const tooltip = document.createElement('span');
                tooltip.className = 'copy-tooltip';
                tooltip.textContent = 'Kopieren';

                btn.appendChild(icon);
                btn.appendChild(tooltip);

                btn.addEventListener('click', async () => {
                    const code = block.querySelector('code')?.textContent || pre.textContent;
                    await navigator.clipboard.writeText(code);
                    icon.innerHTML = checkIcon;
                    tooltip.textContent = 'Kopiert';
                    btn.classList.add('copied');
                    setTimeout(() => {
                        icon.innerHTML = copyIcon;
                        tooltip.textContent = 'Kopieren';
                        btn.classList.remove('copied');
                    }, 2000);
                });
                block.appendChild(btn);
            });
        }
    }));
});

window.Alpine = Alpine;
Alpine.start();
