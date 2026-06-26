@props(['name' => 'body', 'value' => '', 'required' => true, 'rows' => 8])

<div class="ticket-rich-editor border rounded" data-ticket-editor>
    <div class="btn-toolbar border-bottom bg-light p-2 gap-1 flex-wrap" role="toolbar">
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary" data-cmd="bold" title="Bold"><i class="bi bi-type-bold"></i></button>
            <button type="button" class="btn btn-outline-secondary" data-cmd="italic" title="Italic"><i class="bi bi-type-italic"></i></button>
            <button type="button" class="btn btn-outline-secondary" data-cmd="underline" title="Underline"><i class="bi bi-type-underline"></i></button>
        </div>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary" data-cmd="insertUnorderedList"><i class="bi bi-list-ul"></i></button>
            <button type="button" class="btn btn-outline-secondary" data-cmd="insertOrderedList"><i class="bi bi-list-ol"></i></button>
        </div>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary" data-emoji="😀">😀</button>
            <button type="button" class="btn btn-outline-secondary" data-emoji="👍">👍</button>
            <button type="button" class="btn btn-outline-secondary" data-emoji="❤️">❤️</button>
            <button type="button" class="btn btn-outline-secondary" data-emoji="🙏">🙏</button>
            <button type="button" class="btn btn-outline-secondary" data-emoji="✅">✅</button>
            <button type="button" class="btn btn-outline-secondary" data-emoji="⚠️">⚠️</button>
        </div>
        <div class="btn-group btn-group-sm ms-auto">
            <input type="radio" class="btn-check" name="{{ $name }}_format" id="{{ $name }}_format_html" value="html" checked autocomplete="off">
            <label class="btn btn-outline-secondary" for="{{ $name }}_format_html">HTML</label>
            <input type="radio" class="btn-check" name="{{ $name }}_format" id="{{ $name }}_format_bbcode" value="bbcode" autocomplete="off">
            <label class="btn btn-outline-secondary" for="{{ $name }}_format_bbcode">BBCode</label>
        </div>
    </div>
    <div class="ticket-rich-editor-area p-3" contenteditable="true" data-editor-area style="min-height: {{ $rows * 1.5 }}rem;">{!! $value !!}</div>
    <textarea name="{{ $name }}" class="d-none" data-editor-input @if($required) required @endif>{{ $value }}</textarea>
</div>
<div class="form-text">{{ __('menu.ticket_editor_hint') }}</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-ticket-editor]').forEach(function (wrapper) {
        const area = wrapper.querySelector('[data-editor-area]');
        const input = wrapper.querySelector('[data-editor-input]');
        const sync = () => { input.value = area.innerHTML.trim(); };

        wrapper.querySelectorAll('[data-cmd]').forEach(btn => {
            btn.addEventListener('click', function () {
                area.focus();
                document.execCommand(this.dataset.cmd, false, null);
                sync();
            });
        });

        wrapper.querySelectorAll('[data-emoji]').forEach(btn => {
            btn.addEventListener('click', function () {
                area.focus();
                document.execCommand('insertText', false, this.dataset.emoji);
                sync();
            });
        });

        area.addEventListener('input', sync);
        wrapper.closest('form')?.addEventListener('submit', sync);
        sync();
    });
});
</script>
@endpush
@endonce
