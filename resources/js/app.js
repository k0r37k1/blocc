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

                // Copy button (top-right)
                const btn = document.createElement('button');
                btn.className = 'code-copy-btn';
                btn.textContent = 'Kopieren';
                btn.addEventListener('click', async () => {
                    const code = block.querySelector('code')?.textContent || pre.textContent;
                    await navigator.clipboard.writeText(code);
                    btn.textContent = 'Kopiert';
                    btn.classList.add('copied');
                    setTimeout(() => {
                        btn.textContent = 'Kopieren';
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
