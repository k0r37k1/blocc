<div
    x-data="{
        showEmojis: false,
        pickerPos: { left: '0px', top: '0px' },
        openPicker() {
            this.showEmojis = !this.showEmojis;
            if (this.showEmojis) {
                this.$nextTick(() => {
                    const btn = this.$refs.trigger;
                    const rect = btn.getBoundingClientRect();
                    const pw = 288;
                    let left = rect.right - pw;
                    if (left < 8) left = 8;
                    this.pickerPos = { left: left + 'px', top: (rect.top - 6) + 'px' };
                });
            }
        }
    }"
    x-init="
        $nextTick(() => {
            const richEditor = $el.closest('form')?.querySelector('.fi-fo-rich-editor');
            if (richEditor) {
                richEditor.style.position = 'relative';
                $el.style.position = 'absolute';
                $el.style.top = '0.5rem';
                $el.style.right = '0.5rem';
                $el.style.zIndex = '40';
                richEditor.appendChild($el);
            }
        })
    "
    style="display: inline-block;"
>
    <button
        type="button"
        x-ref="trigger"
        x-on:click="openPicker()"
        style="display: inline-flex; align-items: center; justify-content: center; padding: 0.25rem; cursor: pointer; border: none; background: transparent; color: rgba(128,128,128,0.5); transition: color 0.15s;"
        onmouseover="this.style.color='rgba(128,128,128,0.9)'"
        onmouseout="this.style.color='rgba(128,128,128,0.5)'"
        title="{{ __('Emojis') }}"
    >
        <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
        </svg>
    </button>

    <div
        x-show="showEmojis"
        x-on:click.outside="showEmojis = false"
        x-transition.opacity
        x-bind:style="'position:fixed;z-index:9999;width:18rem;display:grid;grid-template-columns:repeat(8,1fr);gap:0.125rem;padding:0.5rem;border-radius:0.5rem;border:1px solid rgba(128,128,128,0.2);background:var(--fi-body-bg,#fff);box-shadow:0 4px 12px rgba(0,0,0,0.15);left:'+pickerPos.left+';top:'+pickerPos.top+';transform:translateY(-100%);'"
    >
        @foreach (['👍','👎','❤️','🔥','😂','😍','🤔','😮','😢','🙏','😡','🙄','😤','💀','🤦','😬','❌','🤷','✅','💯','🎯','👏','💪','⚡','💡','🚀','⭐','🎉','👀','⚠️','📌','💬'] as $emoji)
            <button
                type="button"
                style="padding: 0.375rem; font-size: 1.25rem; line-height: 1; border-radius: 0.25rem; cursor: pointer; border: none; background: transparent; transition: background 0.1s;"
                onmouseover="this.style.background='rgba(128,128,128,0.15)'"
                onmouseout="this.style.background='transparent'"
                x-on:click="
                    const editor = document.querySelector('.tiptap.ProseMirror');
                    if (editor) {
                        editor.focus();
                        document.execCommand('insertText', false, '{{ $emoji }}');
                    }
                    showEmojis = false;
                "
            >{{ $emoji }}</button>
        @endforeach
    </div>
</div>
