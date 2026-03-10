<div
    x-data="{
        showEmojis: false,
        pickerStyle: '',
        openPicker() {
            this.showEmojis = !this.showEmojis;
            if (this.showEmojis) {
                this.$nextTick(() => {
                    const btn = this.$refs.trigger;
                    const rect = btn.getBoundingClientRect();
                    this.pickerStyle = 'position:fixed;left:'+rect.left+'px;top:'+(rect.top - 8)+'px;transform:translateY(-100%);z-index:9999;display:grid;grid-template-columns:repeat(8,1fr);gap:0.125rem;padding:0.5rem;border-radius:0.5rem;border:1px solid rgba(128,128,128,0.2);background:var(--fi-body-bg,#fff);box-shadow:0 4px 12px rgba(0,0,0,0.15);';
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
        })
    "
    style="display: inline-block;"
>
    <button
        type="button"
        x-ref="trigger"
        x-on:click="openPicker()"
        style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 500; color: var(--fi-body-text-color, #6b7280); opacity: 0.7; transition: opacity 0.15s; cursor: pointer; border: 1px solid rgba(128,128,128,0.2); background: transparent;"
        onmouseover="this.style.opacity='1'"
        onmouseout="this.style.opacity='0.7'"
        title="{{ __('Emojis') }}"
    >
        <span style="font-size: 1rem; line-height: 1;">😊</span>
        <span>{{ __('Emoji') }}</span>
    </button>

    <div
        x-show="showEmojis"
        x-on:click.outside="showEmojis = false"
        x-transition.opacity
        x-bind:style="pickerStyle"
    >
        @foreach (['👍','👎','❤️','🔥','😂','😍','🤔','😮','😢','🙏','😡','🙄','😤','💀','🤦','😬','❌','🤷','✅','💯','🎯','👏','💪','⚡','💡','🚀','⭐','🎉','👀','⚠️','📌','💬'] as $emoji)
            <button
                type="button"
                style="padding: 0.375rem; font-size: 1.25rem; line-height: 1; border-radius: 0.25rem; cursor: pointer; border: none; background: transparent; transition: background 0.1s;"
                onmouseover="this.style.background='rgba(128,128,128,0.1)'"
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
