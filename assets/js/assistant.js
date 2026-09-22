/**
 * Offline site-assistant widget. Talks only to assistant.php on this same
 * server (see window.ASSISTANT_BASE_URL, set in assistant-widget.php) —
 * no external AI service, no internet connection required.
 */
document.addEventListener('DOMContentLoaded', function () {
    var widget = document.getElementById('assistantWidget');
    var toggle = document.getElementById('assistantToggle');
    var closeBtn = document.getElementById('assistantClose');
    var panel = document.getElementById('assistantPanel');
    var messages = document.getElementById('assistantMessages');
    var form = document.getElementById('assistantForm');
    var input = document.getElementById('assistantInput');

    if (!widget || !toggle || !panel || !messages || !form || !input) {
        return;
    }

    var base = window.ASSISTANT_BASE_URL || '';

    function openPanel() {
        panel.hidden = false;
        widget.classList.add('assistant-open');
        toggle.setAttribute('aria-expanded', 'true');
        input.focus();
    }

    function closePanel() {
        panel.hidden = true;
        widget.classList.remove('assistant-open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', function () {
        if (panel.hidden) {
            openPanel();
        } else {
            closePanel();
        }
    });
    if (closeBtn) {
        closeBtn.addEventListener('click', closePanel);
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function addMessage(text, who) {
        var el = document.createElement('div');
        el.className = 'assistant-message assistant-message-' + who;
        el.textContent = text;
        messages.appendChild(el);
        messages.scrollTop = messages.scrollHeight;
        return el;
    }

    function addResults(data) {
        var hasResources = Array.isArray(data.resources) && data.resources.length > 0;
        var hasGallery = Array.isArray(data.gallery) && data.gallery.length > 0;

        if (!hasResources && !hasGallery) {
            return;
        }

        var wrap = document.createElement('div');
        wrap.className = 'assistant-results';

        if (hasResources) {
            data.resources.forEach(function (r) {
                var card = document.createElement('div');
                card.className = 'assistant-result-card';
                card.innerHTML =
                    '<span class="assistant-result-title">' + escapeHtml(r.title) + '</span>' +
                    '<span class="assistant-result-meta">' + escapeHtml(r.type_label) +
                        (r.form_label && r.form_label !== '-' ? ' &middot; ' + escapeHtml(r.form_label) : '') +
                    '</span>' +
                    '<span class="assistant-result-actions">' +
                        '<a href="' + r.view_url + '" target="_blank" rel="noopener">Angalia</a>' +
                        '<a href="' + r.download_url + '">Pakua</a>' +
                    '</span>';
                wrap.appendChild(card);
            });
        }

        if (hasGallery) {
            data.gallery.forEach(function (g) {
                var card = document.createElement('div');
                card.className = 'assistant-result-card';
                card.innerHTML =
                    '<span class="assistant-result-title">' + escapeHtml(g.title) + '</span>' +
                    '<span class="assistant-result-meta">' + (g.media_type === 'image' ? 'Picha' : 'Video') + '</span>' +
                    '<span class="assistant-result-actions">' +
                        '<a href="' + g.file_url + '" target="_blank" rel="noopener">Fungua</a>' +
                    '</span>';
                wrap.appendChild(card);
            });
        }

        if (data.search_url) {
            var link = document.createElement('a');
            link.href = data.search_url;
            link.className = 'assistant-search-link';
            link.textContent = 'Tazama matokeo yote →';
            wrap.appendChild(link);
        }

        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var message = input.value.trim();
        if (message === '') {
            return;
        }

        addMessage(message, 'user');
        input.value = '';
        input.disabled = true;

        var typing = addMessage('...', 'bot');
        typing.classList.add('assistant-message-typing');

        fetch(base + '/assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(message),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                typing.remove();
                addMessage(data.reply || 'Samahani, sijaelewa. Jaribu tena.', 'bot');
                addResults(data);
            })
            .catch(function () {
                typing.remove();
                addMessage('Samahani, imeshindikana kuwasiliana na seva. Jaribu tena.', 'bot');
            })
            .finally(function () {
                input.disabled = false;
                input.focus();
            });
    });
});
