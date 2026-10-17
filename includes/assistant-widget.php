<?php
/**
 * Floating AI assistant chat widget, included once from footer.php so it
 * appears on every public page. The browser only talks to api/chat.php on
 * this same server; the AI API key stays on the server (includes/ai.php).
 * All visible text is filled in by assets/js/assistant.js in the chosen
 * language (English / Kiswahili); the English here is the no-JS fallback.
 */
require_once __DIR__ . '/ai.php';

$assistantBase = base_url();
$assistantConfig = [
    'base'      => $assistantBase,
    'webSearch' => ai_web_search_enabled(), // true/false only; never the key
];
?>
<div id="assistantWidget" class="assistant-widget">
    <button type="button" id="assistantToggle" class="assistant-toggle" aria-expanded="false" aria-controls="assistantPanel">
        <?= svg_icon('chat', 'icon-svg assistant-toggle-icon-open') ?>
        <?= svg_icon('close', 'icon-svg assistant-toggle-icon-close') ?>
        <span class="assistant-toggle-label" data-i18n="toggle">Ask AI</span>
    </button>

    <div id="assistantPanel" class="assistant-panel" role="dialog" aria-modal="false" aria-labelledby="assistantTitle" hidden>
        <div class="assistant-panel-header">
            <div class="assistant-panel-heading">
                <strong id="assistantTitle" data-i18n="title">Namanga Assistant</strong>
                <span class="assistant-panel-subtitle" data-i18n="subtitle">AI help for students and teachers</span>
            </div>
            <div class="assistant-header-actions">
                <div class="assistant-lang" role="group" aria-label="Language / Lugha">
                    <button type="button" class="assistant-lang-btn" data-lang="en" aria-pressed="true" lang="en">EN</button>
                    <button type="button" class="assistant-lang-btn" data-lang="sw" aria-pressed="false" lang="sw">SW</button>
                </div>
                <button type="button" id="assistantClose" class="assistant-panel-close" data-i18n-aria="close" aria-label="Close chat">
                    <?= svg_icon('close', 'icon-svg') ?>
                </button>
            </div>
        </div>

        <div id="assistantMessages" class="assistant-messages" role="log" aria-live="polite" aria-relevant="additions" tabindex="0"></div>

        <div id="assistantStatus" class="assistant-typing" hidden>
            <span class="assistant-typing-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            <span data-i18n="thinking">Thinking…</span>
        </div>

        <form id="assistantForm" class="assistant-form" novalidate>
            <div class="assistant-input-row">
                <label for="assistantInput" class="visually-hidden" data-i18n="inputLabel">Your message</label>
                <textarea id="assistantInput" rows="1" maxlength="1000" autocomplete="off" data-i18n-placeholder="placeholder" placeholder="Type your question…"></textarea>
                <button type="submit" id="assistantSend" class="assistant-send-btn" data-i18n-aria="send" aria-label="Send">
                    <?= svg_icon('send', 'icon-svg') ?>
                </button>
            </div>
            <div class="assistant-form-footer">
                <?php if ($assistantConfig['webSearch']): ?>
                    <label class="assistant-web-toggle">
                        <input type="checkbox" id="assistantWeb">
                        <span data-i18n="webSearch">Also search the internet</span>
                    </label>
                <?php endif; ?>
                <span id="assistantCounter" class="assistant-counter" aria-hidden="true"></span>
                <button type="button" id="assistantClear" class="assistant-clear-btn" data-i18n="clear">Clear chat</button>
            </div>
            <p class="assistant-disclaimer" data-i18n="disclaimer">AI can make mistakes. Check important answers with your teacher.</p>
        </form>
    </div>
</div>
<script>window.ASSISTANT_CONFIG = <?= json_encode($assistantConfig, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;</script>
<script src="<?= e($assistantBase) ?>/assets/js/assistant.js?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/assistant.js') ?>"></script>
