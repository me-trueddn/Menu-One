<script>
(function () {
    const samples = @json(\App\Support\EmailTemplateSamples::all());

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderTemplate(content, variables) {
        let html = content || '';

        Object.entries(variables).forEach(function ([key, value]) {
            html = html.split('{' + key + '}').join(escapeHtml(value));
        });

        html = html
            .replace(/\[b\]([\s\S]*?)\[\/b\]/g, '<strong>$1</strong>')
            .replace(/\[i\]([\s\S]*?)\[\/i\]/g, '<em>$1</em>')
            .replace(/\[u\]([\s\S]*?)\[\/u\]/g, '<u>$1</u>')
            .replace(/\[url=([^\]]+)\]([\s\S]*?)\[\/url\]/g, '<a href="$1">$2</a>')
            .replace(/\[url\]([\s\S]*?)\[\/url\]/g, '<a href="$1">$1</a>');

        if (!/<\s*(html|body|table|div|center|section|article)\b/i.test(html)) {
            html = html.replace(/\n/g, '<br>');
        }

        return html;
    }

    function updatePreview(block) {
        const key = block.dataset.previewKey;
        if (!key || !samples[key]) return;

        const variables = Object.assign({}, samples[key]);
        const expiresInput = block.querySelector('.notification-expires-input');
        if (expiresInput && expiresInput.value) {
            variables.expires_minutes = expiresInput.value;
        }

        const subjectInput = block.querySelector('.notification-subject-input');
        const bodyInput = block.querySelector('.notification-body-input');
        const subjectEl = block.querySelector('[data-preview-subject]');
        const bodyEl = block.querySelector('[data-preview-body]');

        if (!subjectInput || !bodyInput || !subjectEl || !bodyEl) return;

        const subject = renderTemplate(subjectInput.value, variables).replace(/<[^>]+>/g, '');
        subjectEl.textContent = subject;
        bodyEl.innerHTML = renderTemplate(bodyInput.value, variables);
    }

    function bindBlock(block) {
        block.querySelectorAll('.notification-subject-input, .notification-body-input, .notification-expires-input')
            .forEach(function (input) {
                input.addEventListener('input', function () { updatePreview(block); });
            });
        updatePreview(block);
    }

    document.querySelectorAll('.notification-template-fields[data-preview-key]').forEach(bindBlock);

    document.querySelectorAll('#notificationTemplateTabs [data-bs-toggle="tab"]').forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function () {
            const pane = document.querySelector(tab.getAttribute('data-bs-target'));
            pane?.querySelectorAll('.notification-template-fields[data-preview-key]').forEach(updatePreview);
        });
    });
})();
</script>
