import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    Alpine.data('codeBlocks', () => ({
        init() {
            this.$el.querySelectorAll('.code-block').forEach(block => {
                const language = block.dataset.language || 'text';
                const pre = block.querySelector('pre');
                if (!pre) return;

                // Create header bar
                const header = document.createElement('div');
                header.className = 'code-block-header';

                // Language label
                const label = document.createElement('span');
                label.textContent = language;
                header.appendChild(label);

                // Copy button
                const btn = document.createElement('button');
                btn.className = 'code-copy-btn';
                btn.textContent = 'Kopieren';
                btn.addEventListener('click', async () => {
                    const code = block.querySelector('code')?.textContent || pre.textContent;
                    await navigator.clipboard.writeText(code);
                    btn.textContent = 'Kopiert';
                    setTimeout(() => { btn.textContent = 'Kopieren'; }, 2000);
                });
                header.appendChild(btn);

                block.insertBefore(header, pre);
            });
        }
    }));
});

window.Alpine = Alpine;
Alpine.start();
