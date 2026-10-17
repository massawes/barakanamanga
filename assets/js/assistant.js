/**
 * Namanga AI assistant widget (markup: includes/assistant-widget.php).
 *
 * - Talks only to api/chat.php on this same server. No API key is ever
 *   in this file or anywhere in the browser.
 * - English / Kiswahili: all widget text below, and the AI replies in the
 *   chosen language.
 * - The conversation is kept in sessionStorage (cleared when the browser
 *   tab closes); the last 10 messages are sent with each question.
 * Plain JavaScript, no build step.
 */
(function () {
    'use strict';

    var CONFIG = window.ASSISTANT_CONFIG || { base: '', webSearch: false };
    var HISTORY_KEY = 'namangaAssistant.chat.v1';
    var LANG_KEY = 'namangaAssistant.lang';
    var MAX_CHARS = 1000;
    var HISTORY_SENT = 10;
    var HISTORY_KEPT = 40;

    var TEXT = {
        en: {
            toggle: 'Ask AI',
            title: 'Namanga Assistant',
            subtitle: 'AI help for students and teachers',
            close: 'Close chat',
            send: 'Send',
            inputLabel: 'Your message',
            placeholder: 'Type your question…',
            thinking: 'Thinking…',
            clear: 'Clear chat',
            clearConfirm: 'Clear the whole conversation?',
            webSearch: 'Also search the internet',
            disclaimer: 'AI can make mistakes. Check important answers with your teacher.',
            welcome: 'Hello! I am the Namanga Assistant. Ask me to explain a topic, help you revise, or find notes and past papers on this website. For example: "Form 2 biology past papers" or "Explain photosynthesis".',
            you: 'You',
            bot: 'Assistant',
            filesTitle: 'Files on this website',
            nectaTitle: 'NECTA past papers (TETEA library)',
            webTitle: 'From the internet',
            view: 'View',
            download: 'Download',
            open: 'Open',
            networkError: 'Sorry, I could not reach the server. Check your internet connection and try again.',
            empty: 'Please type a message first.',
            tooLong: 'Your message is too long. Please keep it under 1,000 characters.',
            charsLeft: ' characters left'
        },
        sw: {
            toggle: 'Uliza AI',
            title: 'Msaidizi wa Namanga',
            subtitle: 'Msaada wa AI kwa wanafunzi na walimu',
            close: 'Funga mazungumzo',
            send: 'Tuma',
            inputLabel: 'Ujumbe wako',
            placeholder: 'Andika swali lako…',
            thinking: 'Inafikiri…',
            clear: 'Futa mazungumzo',
            clearConfirm: 'Ufute mazungumzo yote?',
            webSearch: 'Tafuta pia kwenye intaneti',
            disclaimer: 'AI inaweza kukosea. Hakikisha majibu muhimu na mwalimu wako.',
            welcome: 'Habari! Mimi ni Msaidizi wa Namanga. Niulize nikueleze mada, nikusaidie kujiandaa na mitihani, au nikutafutie notes na mitihani iliyopita kwenye tovuti hii. Mfano: "mitihani ya biology form 2" au "Nieleze photosynthesis".',
            you: 'Wewe',
            bot: 'Msaidizi',
            filesTitle: 'Faili kwenye tovuti hii',
            nectaTitle: 'Mitihani ya NECTA (maktaba ya TETEA)',
            webTitle: 'Kutoka kwenye intaneti',
            view: 'Angalia',
            download: 'Pakua',
            open: 'Fungua',
            networkError: 'Samahani, imeshindikana kufikia seva. Angalia intaneti yako kisha ujaribu tena.',
            empty: 'Tafadhali andika ujumbe kwanza.',
            tooLong: 'Ujumbe wako ni mrefu sana. Tafadhali usizidi herufi 1,000.',
            charsLeft: ' herufi zimebaki'
        }
    };

    // ---- Storage helpers (storage can be blocked in private windows) ----
    function load(store, key, fallback) {
        try {
            var raw = store.getItem(key);
            return raw === null ? fallback : JSON.parse(raw);
        } catch (e) {
            return fallback;
        }
    }
    function save(store, key, value) {
        try {
            store.setItem(key, JSON.stringify(value));
        } catch (e) { /* storage full or blocked: the chat still works */ }
    }

    function init() {
        var widget = document.getElementById('assistantWidget');
        var toggle = document.getElementById('assistantToggle');
        var panel = document.getElementById('assistantPanel');
        var closeBtn = document.getElementById('assistantClose');
        var list = document.getElementById('assistantMessages');
        var status = document.getElementById('assistantStatus');
        var form = document.getElementById('assistantForm');
        var input = document.getElementById('assistantInput');
        var sendBtn = document.getElementById('assistantSend');
        var clearBtn = document.getElementById('assistantClear');
        var counter = document.getElementById('assistantCounter');
        var webBox = document.getElementById('assistantWeb');
        var langBtns = widget ? widget.querySelectorAll('.assistant-lang-btn') : [];

        if (!widget || !toggle || !panel || !list || !form || !input) {
            return;
        }

        var browserLang = (navigator.language || '').toLowerCase().indexOf('sw') === 0 ? 'sw' : 'en';
        var lang = load(localStorage, LANG_KEY, browserLang) === 'sw' ? 'sw' : 'en';
        var history = load(sessionStorage, HISTORY_KEY, []);
        if (!Array.isArray(history)) {
            history = [];
        }
        var busy = false;

        function t(key) {
            return (TEXT[lang] && TEXT[lang][key]) || TEXT.en[key] || '';
        }

        // ---- Language ----
        function applyLanguage() {
            widget.querySelectorAll('[data-i18n]').forEach(function (el) {
                el.textContent = t(el.getAttribute('data-i18n'));
            });
            widget.querySelectorAll('[data-i18n-aria]').forEach(function (el) {
                el.setAttribute('aria-label', t(el.getAttribute('data-i18n-aria')));
            });
            widget.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
                el.setAttribute('placeholder', t(el.getAttribute('data-i18n-placeholder')));
            });
            Array.prototype.forEach.call(langBtns, function (btn) {
                btn.setAttribute('aria-pressed', btn.getAttribute('data-lang') === lang ? 'true' : 'false');
            });
            panel.setAttribute('lang', lang);
            renderAll();
        }

        Array.prototype.forEach.call(langBtns, function (btn) {
            btn.addEventListener('click', function () {
                lang = btn.getAttribute('data-lang') === 'sw' ? 'sw' : 'en';
                save(localStorage, LANG_KEY, lang);
                applyLanguage();
            });
        });

        // ---- Open / close ----
        function openPanel() {
            panel.hidden = false;
            widget.classList.add('assistant-open');
            toggle.setAttribute('aria-expanded', 'true');
            list.scrollTop = list.scrollHeight;
            input.focus();
        }
        function closePanel() {
            panel.hidden = true;
            widget.classList.remove('assistant-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
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
        widget.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !panel.hidden) {
                closePanel();
            }
        });

        // ---- Rendering (always via textContent: never inject HTML) ----

        /** Adds text with **bold** support to an element, safely. */
        function appendFormatted(parent, text) {
            text.split(/(\*\*[^*]+\*\*)/).forEach(function (part) {
                if (/^\*\*[^*]+\*\*$/.test(part)) {
                    var b = document.createElement('strong');
                    b.textContent = part.slice(2, -2);
                    parent.appendChild(b);
                } else if (part !== '') {
                    parent.appendChild(document.createTextNode(part));
                }
            });
        }

        /** Paragraphs and "- " / "1. " lists from plain text. */
        function renderText(container, text) {
            var lines = String(text).replace(/\r/g, '').split('\n');
            var currentList = null;
            lines.forEach(function (raw) {
                var line = raw.trim();
                var bullet = line.match(/^(?:[-*•]|\d+[.)])\s+(.*)$/);
                if (bullet) {
                    if (!currentList) {
                        currentList = document.createElement(/^\d/.test(line) ? 'ol' : 'ul');
                        container.appendChild(currentList);
                    }
                    var li = document.createElement('li');
                    appendFormatted(li, bullet[1]);
                    currentList.appendChild(li);
                    return;
                }
                currentList = null;
                if (line === '') {
                    return;
                }
                var p = document.createElement('p');
                appendFormatted(p, line.replace(/^#+\s*/, ''));
                container.appendChild(p);
            });
        }

        /** Only allow links to this site (relative) or http(s) addresses. */
        function safeUrl(url) {
            url = String(url || '');
            if (/^https?:\/\//i.test(url) || (url.charAt(0) === '/' && url.charAt(1) !== '/')) {
                return url;
            }
            return null;
        }

        function makeLink(url, label, newTab) {
            var href = safeUrl(url);
            if (!href) {
                return null;
            }
            var a = document.createElement('a');
            a.href = href;
            a.textContent = label;
            if (newTab) {
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
            }
            return a;
        }

        function renderResources(container, resources) {
            var groups = [
                { title: t('filesTitle'), items: resources.filter(function (r) { return !r.external; }) },
                { title: t('nectaTitle'), items: resources.filter(function (r) { return r.external; }) }
            ];
            groups.forEach(function (group) {
                if (!group.items.length) {
                    return;
                }
                var wrap = document.createElement('div');
                wrap.className = 'assistant-results';
                var h = document.createElement('p');
                h.className = 'assistant-results-title';
                h.textContent = group.title;
                wrap.appendChild(h);

                group.items.forEach(function (r) {
                    var card = document.createElement('div');
                    card.className = 'assistant-result-card';
                    var title = document.createElement('span');
                    title.className = 'assistant-result-title';
                    title.textContent = r.title;
                    card.appendChild(title);

                    var meta = [r.type, r.form, r.year].filter(Boolean).join(' · ');
                    if (meta) {
                        var m = document.createElement('span');
                        m.className = 'assistant-result-meta';
                        m.textContent = meta;
                        card.appendChild(m);
                    }
                    if (r.description) {
                        var d = document.createElement('span');
                        d.className = 'assistant-result-desc';
                        d.textContent = r.description;
                        card.appendChild(d);
                    }
                    var actions = document.createElement('span');
                    actions.className = 'assistant-result-actions';
                    var view = makeLink(r.view_url, t('view'), true);
                    if (view) {
                        actions.appendChild(view);
                    }
                    if (r.download_url && r.download_url !== r.view_url) {
                        var dl = makeLink(r.download_url, t('download'), !!r.external);
                        if (dl) {
                            actions.appendChild(dl);
                        }
                    } else if (view && !r.external) {
                        view.textContent = t('open');
                    }
                    card.appendChild(actions);
                    wrap.appendChild(card);
                });
                container.appendChild(wrap);
            });
        }

        function renderWeb(container, web) {
            if (!web.length) {
                return;
            }
            var wrap = document.createElement('div');
            wrap.className = 'assistant-results';
            var h = document.createElement('p');
            h.className = 'assistant-results-title';
            h.textContent = t('webTitle');
            wrap.appendChild(h);
            web.forEach(function (w) {
                var a = makeLink(w.url, w.title, true);
                if (!a) {
                    return;
                }
                a.className = 'assistant-web-link';
                var host = document.createElement('span');
                host.className = 'assistant-result-meta';
                host.textContent = w.host || '';
                a.appendChild(host);
                wrap.appendChild(a);
            });
            container.appendChild(wrap);
        }

        function renderMessage(msg) {
            var row = document.createElement('div');
            row.className = 'assistant-message assistant-message-' + (msg.role === 'user' ? 'user' : 'bot') + (msg.error ? ' assistant-message-error' : '');
            var who = document.createElement('span');
            who.className = 'visually-hidden';
            who.textContent = (msg.role === 'user' ? t('you') : t('bot')) + ': ';
            row.appendChild(who);
            if (msg.role === 'user') {
                row.appendChild(document.createTextNode(msg.content));
            } else {
                renderText(row, msg.content);
            }
            list.appendChild(row);
            if (Array.isArray(msg.resources) && msg.resources.length) {
                renderResources(list, msg.resources);
            }
            if (Array.isArray(msg.web) && msg.web.length) {
                renderWeb(list, msg.web);
            }
            return row;
        }

        function renderAll() {
            list.textContent = '';
            renderMessage({ role: 'assistant', content: t('welcome') });
            history.forEach(renderMessage);
            list.scrollTop = list.scrollHeight;
        }

        function remember(msg) {
            history.push(msg);
            if (history.length > HISTORY_KEPT) {
                history = history.slice(-HISTORY_KEPT);
            }
            save(sessionStorage, HISTORY_KEY, history);
        }

        function addAndShow(msg, keep) {
            if (keep) {
                remember(msg);
            }
            var row = renderMessage(msg);
            // Show the start of a new answer (not the bottom of its cards).
            list.scrollTop = msg.role === 'user' ? list.scrollHeight : row.offsetTop - 8;
        }

        // ---- Input box: auto-grow, Enter to send, character counter ----
        function updateCounter() {
            var left = MAX_CHARS - input.value.length;
            if (counter) {
                counter.textContent = left < 200 ? left + t('charsLeft') : '';
            }
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
            input.style.overflowY = input.scrollHeight > 120 ? 'auto' : 'hidden';
        }
        input.addEventListener('input', updateCounter);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
                e.preventDefault();
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
        });

        function setBusy(state) {
            busy = state;
            status.hidden = !state;
            sendBtn.disabled = state;
            input.setAttribute('aria-busy', state ? 'true' : 'false');
            if (state) {
                list.scrollTop = list.scrollHeight;
            }
        }

        // ---- Send ----
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (busy) {
                return;
            }
            var message = input.value.trim();
            if (message === '') {
                addAndShow({ role: 'assistant', content: t('empty'), error: true }, false);
                input.focus();
                return;
            }
            if (message.length > MAX_CHARS) {
                addAndShow({ role: 'assistant', content: t('tooLong'), error: true }, false);
                return;
            }

            // Only plain user/assistant text goes back to the server as context.
            var context = history
                .filter(function (m) { return !m.error; })
                .slice(-HISTORY_SENT)
                .map(function (m) { return { role: m.role, content: m.content }; });

            addAndShow({ role: 'user', content: message }, true);
            input.value = '';
            updateCounter();
            setBusy(true);

            fetch(CONFIG.base + '/api/chat.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    lang: lang,
                    history: context,
                    web: !!(webBox && webBox.checked)
                })
            })
                .then(function (res) {
                    return res.json().catch(function () {
                        throw new Error('bad json');
                    });
                })
                .then(function (data) {
                    var reply = typeof data.reply === 'string' && data.reply !== '' ? data.reply : t('networkError');
                    addAndShow({
                        role: 'assistant',
                        content: reply,
                        resources: Array.isArray(data.resources) ? data.resources : [],
                        web: Array.isArray(data.web) ? data.web : [],
                        error: !data.ok
                    }, true);
                })
                .catch(function () {
                    addAndShow({ role: 'assistant', content: t('networkError'), error: true }, false);
                })
                .finally(function () {
                    setBusy(false);
                    input.focus();
                });
        });

        // ---- Clear ----
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (history.length && !window.confirm(t('clearConfirm'))) {
                    return;
                }
                history = [];
                try {
                    sessionStorage.removeItem(HISTORY_KEY);
                } catch (err) { /* ignore */ }
                renderAll();
                input.focus();
            });
        }

        applyLanguage();
        updateCounter();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
