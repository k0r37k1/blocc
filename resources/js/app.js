import intersect from '@alpinejs/intersect'
import Alpine from 'alpinejs'

Alpine.plugin(intersect)

document.addEventListener('alpine:init', () => {
    Alpine.data('codeBlocks', () => ({
        init() {
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
                btn.setAttribute('aria-label', 'Kopieren')

                const icon = document.createElement('span')
                icon.innerHTML = copyIcon

                const tooltip = document.createElement('span')
                tooltip.className = 'copy-tooltip'
                tooltip.textContent = 'Kopieren'

                btn.appendChild(icon)
                btn.appendChild(tooltip)

                btn.addEventListener('click', async () => {
                    const code = block.querySelector('code')?.textContent || pre.textContent
                    await navigator.clipboard.writeText(code)
                    icon.innerHTML = checkIcon
                    tooltip.textContent = 'Kopiert'
                    btn.classList.add('copied')
                    setTimeout(() => {
                        icon.innerHTML = copyIcon
                        tooltip.textContent = 'Kopieren'
                        btn.classList.remove('copied')
                    }, 2000)
                })
                block.appendChild(btn)
            })
        },
    }))

    // Comment count for post cards
    Alpine.data('commentCount', (appId, pageId) => ({
        count: null,
        async init() {
            try {
                const res = await fetch(
                    `https://cusdis.com/api/open/comments?appId=${appId}&pageId=${pageId}&page=1`
                );
                const json = await res.json();
                this.count = json.data.commentCount || 0;
            } catch (e) {
                this.count = null;
            }
        },
    }))

    // Comments (Cusdis API)
    Alpine.data('comments', (appId, pageId, pageUrl, pageTitle, messages = {}) => ({
        items: [],
        loading: true,
        submitting: false,
        page: 1,
        pageCount: 1,
        commentCount: 0,
        replyingTo: null,
        successMessage: '',
        form: { nickname: '', email: '', content: '' },
        replyForm: { nickname: '', email: '', content: '' },

        async fetchComments() {
            this.loading = true;
            try {
                const res = await fetch(
                    `https://cusdis.com/api/open/comments?appId=${appId}&pageId=${pageId}&page=${this.page}`,
                    { headers: { 'x-timezone-offset': String(new Date().getTimezoneOffset() / -60) } }
                );
                const json = await res.json();
                this.items = json.data.data || [];
                this.pageCount = json.data.pageCount || 1;
                this.commentCount = json.data.commentCount || 0;
            } catch (e) {
                this.items = [];
            }
            this.loading = false;
        },

        async submitComment() {
            this.submitting = true;
            try {
                await fetch('https://cusdis.com/api/open/comments', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        appId, pageId, pageUrl, pageTitle,
                        nickname: this.form.nickname,
                        email: this.form.email,
                        content: this.form.content,
                    }),
                });
                this.successMessage = messages.commentSent || 'Comment sent!';
                this.form.content = '';
                setTimeout(() => this.successMessage = '', 5000);
            } catch (e) {
                this.successMessage = '';
            }
            this.submitting = false;
        },

        async submitReply(parentId) {
            this.submitting = true;
            try {
                await fetch('https://cusdis.com/api/open/comments', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        appId, pageId, pageUrl, pageTitle,
                        parentId,
                        nickname: this.replyForm.nickname,
                        email: this.replyForm.email,
                        content: this.replyForm.content,
                    }),
                });
                this.successMessage = messages.replySent || 'Reply sent!';
                this.replyForm = { nickname: '', email: '', content: '' };
                this.replyingTo = null;
                setTimeout(() => this.successMessage = '', 5000);
            } catch (e) {
                this.successMessage = '';
            }
            this.submitting = false;
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('de-DE', { day: 'numeric', month: 'short', year: 'numeric' });
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

window.Alpine = Alpine
Alpine.start()
