import autoAnimate from '@formkit/auto-animate'

document.addEventListener('alpine:init', () => {
    Alpine.directive('auto-animate', (el) => autoAnimate(el))

    Alpine.data('codeBlocks', () => ({
        init() {
            const copyLabel = this.$el.dataset.copyLabel || 'Copy'
            const copiedLabel = this.$el.dataset.copiedLabel || 'Copied'

            this.$el.querySelectorAll('.code-block').forEach((block) => {
                const language = block.dataset.language || 'text'
                const pre = block.querySelector('pre')
                if (!pre) return

                // Language badge (top-left)
                const badge = document.createElement('span')
                badge.className = 'code-lang-badge'
                badge.textContent = language
                block.appendChild(badge)

                // Copy button (top-right) — clipboard icon, checkmark on success
                const copyIcon =
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>'
                const checkIcon =
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>'

                const btn = document.createElement('button')
                btn.className = 'code-copy-btn'
                btn.setAttribute('aria-label', copyLabel)

                const icon = document.createElement('span')
                icon.innerHTML = copyIcon

                const tooltip = document.createElement('span')
                tooltip.className = 'copy-tooltip'
                tooltip.textContent = copyLabel

                btn.appendChild(icon)
                btn.appendChild(tooltip)

                btn.addEventListener('click', async () => {
                    const code = block.querySelector('code')?.textContent || pre.textContent
                    await navigator.clipboard.writeText(code)
                    icon.innerHTML = checkIcon
                    tooltip.textContent = copiedLabel
                    btn.classList.add('copied')
                    setTimeout(() => {
                        icon.innerHTML = copyIcon
                        tooltip.textContent = copyLabel
                        btn.classList.remove('copied')
                    }, 2000)
                })
                block.appendChild(btn)
            })
        },
    }))

    Alpine.data('tableOfContents', () => ({
        items: [],
        activeId: null,
        open: false,

        init() {
            if (this.$el.dataset.tocEnabled === 'false') return

            const prose = this.$el.querySelector('.prose')
            if (!prose) return

            const headings = prose.querySelectorAll('h2[id], h3[id]')
            if (headings.length < 3) return

            this.items = Array.from(headings).map((h) => ({
                id: h.id,
                text: h.textContent.trim(),
                level: parseInt(h.tagName.slice(1)),
            }))

            const observer = new IntersectionObserver(
                (entries) => {
                    for (const entry of entries) {
                        if (entry.isIntersecting) {
                            this.activeId = entry.target.id
                        }
                    }
                },
                { rootMargin: '-5% 0px -75% 0px' }
            )

            headings.forEach((h) => observer.observe(h))
        },

        get visible() {
            return this.items.length >= 3
        },

        toggle() {
            this.open = !this.open
        },
    }))

    // Reading progress bar (blog posts only)
    Alpine.data('readingProgress', () => ({
        progress: 0,
        update() {
            const article = document.querySelector('article')
            if (!article) return
            const articleTop = article.offsetTop
            const articleHeight = article.offsetHeight
            const windowHeight = window.innerHeight
            const scrollY = window.scrollY

            const start = articleTop
            const end = articleTop + articleHeight - windowHeight
            if (end <= start) {
                this.progress = 100
                return
            }
            this.progress = Math.min(100, Math.max(0, ((scrollY - start) / (end - start)) * 100))
        },
    }))
})
