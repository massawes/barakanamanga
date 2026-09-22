<?php
/**
 * Floating "site assistant" chat widget — included once from footer.php
 * so it appears on every public page. Pure HTML/CSS/JS (assets/js/
 * assistant.js); the actual searching happens server-side in
 * assistant.php / assistant_answer(), entirely offline.
 */
$assistantBase = base_url();
?>
<div id="assistantWidget" class="assistant-widget">
    <button type="button" id="assistantToggle" class="assistant-toggle" aria-expanded="false" aria-controls="assistantPanel">
        <?= svg_icon('chat', 'icon-svg assistant-toggle-icon-open') ?>
        <?= svg_icon('close', 'icon-svg assistant-toggle-icon-close') ?>
        <span class="assistant-toggle-label">Msaidizi</span>
    </button>

    <div id="assistantPanel" class="assistant-panel" hidden>
        <div class="assistant-panel-header">
            <div>
                <strong>Msaidizi wa Tovuti</strong>
                <span class="assistant-panel-subtitle">Inafanya kazi bila internet</span>
            </div>
            <button type="button" id="assistantClose" class="assistant-panel-close" aria-label="Funga">
                <?= svg_icon('close', 'icon-svg') ?>
            </button>
        </div>

        <div id="assistantMessages" class="assistant-messages">
            <div class="assistant-message assistant-message-bot">
                Habari! Niulize kuhusu Notes, Summary, Theory Past Papers, Form 4 Practical, Software, Others, au Gallery — mfano: "notes za form 2" au "software ya autocad".
            </div>
        </div>

        <form id="assistantForm" class="assistant-input-row">
            <input type="text" id="assistantInput" placeholder="Andika swali lako..." autocomplete="off" maxlength="300">
            <button type="submit" class="assistant-send-btn" aria-label="Tuma">
                <?= svg_icon('send', 'icon-svg') ?>
            </button>
        </form>
    </div>
</div>
<script>window.ASSISTANT_BASE_URL = <?= json_encode($assistantBase, JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= e($assistantBase) ?>/assets/js/assistant.js"></script>
