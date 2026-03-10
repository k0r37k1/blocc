<div
    x-data="{
        showEmojis: false,
        openPicker() {
            this.showEmojis = !this.showEmojis;
            if (this.showEmojis) {
                this.$nextTick(() => {
                    const btn = this.$refs.trigger;
                    const rect = btn.getBoundingClientRect();
                    const picker = this.$refs.picker;
                    let left = rect.right - picker.offsetWidth;
                    if (left < 8) left = 8;
                    picker.style.left = left + 'px';
                    picker.style.top = (rect.bottom + 6) + 'px';
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
                $el.style.top = '0.25rem';
                $el.style.right = '0.25rem';
                $el.style.zIndex = '40';
                richEditor.appendChild($el);
            }
            document.body.appendChild($refs.picker);
        })
    "
    style="display: inline-block;"
>
    <button
        type="button"
        x-ref="trigger"
        x-on:click="openPicker()"
        style="display: inline-flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.375rem; cursor: pointer; border: none; background: transparent; opacity: 0.6; transition: opacity 0.15s;"
        onmouseover="this.style.opacity='1'"
        onmouseout="this.style.opacity='0.6'"
        title="{{ __('Emojis') }}"
    >
        <span style="font-size: 1.25rem; line-height: 1;">😊</span>
    </button>

    <div
        x-ref="picker"
        x-show="showEmojis"
        x-on:click.outside="showEmojis = false"
        x-transition.opacity
        style="position: fixed; z-index: 9999; width: 18rem; display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.125rem; padding: 0.5rem; border-radius: 0.5rem; border: 1px solid rgba(128,128,128,0.2); background: var(--fi-body-bg, #fff); box-shadow: 0 4px 12px rgba(0,0,0,0.15);"
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
