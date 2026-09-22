<?php
/**
 * Small library of hand-built inline SVG icons used across the site.
 * These are embedded directly in the page HTML (no external file, no
 * internet connection, no icon-font CDN) — so they render identically
 * whether the server has internet access or not, which matters since
 * this project runs locally under XAMPP.
 */
function svg_icon(string $name, string $class = 'icon-svg'): string
{
    $icons = [
        // Open book — Notes
        'notes' => '<path d="M12 6c-1.6-1.1-4.1-1.6-6.2-1.3v13.4c2.1-.3 4.6.2 6.2 1.3 1.6-1.1 4.1-1.6 6.2-1.3V4.7c-2.1-.3-4.6.2-6.2 1.3z"/><path d="M12 6v13.4"/>',

        // Clipboard with checklist — Summary
        'summary' => '<rect x="5" y="4" width="14" height="17" rx="1.5"/><rect x="9" y="2.3" width="6" height="3" rx="1"/><path d="M8 10.2h8M8 13.6h8M8 17h5"/>',

        // Exam paper with folded corner — Theory Past Papers
        'theory' => '<path d="M6 3h8.5L19 7.5V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v4.5h5"/><path d="M8 12.2h8M8 15.6h8M8 8.8h4"/>',

        // Monitor with code brackets — Form 4 Practical
        'practical' => '<rect x="3" y="4" width="18" height="12.5" rx="1.5"/><path d="M8 20h8M12 16.5V20"/><path d="M9.5 8.2 7 10.7l2.5 2.5M14.5 8.2 17 10.7l-2.5 2.5"/>',

        // Open box with download arrow — Software
        'software' => '<path d="M3 8.2 12 3l9 5.2-9 5.2-9-5.2z"/><path d="M3 8.2v8L12 21l9-4.8v-8"/><path d="M12 13.4V8.2"/>',

        // Simple search magnifier (spare, for future use)
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.3-4.3"/>',

        // Folder with a plus — Others (miscellaneous / uncategorised files)
        'others' => '<path d="M3 7.5a1.5 1.5 0 0 1 1.5-1.5H9l2 2h8.5A1.5 1.5 0 0 1 21 9.5v9A1.5 1.5 0 0 1 19.5 20h-15A1.5 1.5 0 0 1 3 18.5z"/><path d="M12 11.5v5M9.5 14h5"/>',

        // Open eye — "show password"
        'eye' => '<path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7S2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',

        // Eye with a slash through it — "hide password"
        'eye-off' => '<path d="M3 3l18 18"/><path d="M10.6 5.1A10.6 10.6 0 0 1 12 5c6 0 9.5 7 9.5 7a17.7 17.7 0 0 1-2.9 3.9M6.6 6.6C4.2 8.3 2.5 12 2.5 12s3.5 7 9.5 7a9.7 9.7 0 0 0 3.4-.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',

        // Picture frame with mountains — Gallery
        'gallery' => '<rect x="3" y="4" width="18" height="16" rx="1.5"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="M21 16.5l-5.8-5.8a1.5 1.5 0 0 0-2.1 0L5 18.5"/>',

        // Play button in a frame — Video
        'video' => '<rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="M10 9.2v5.6l5-2.8-5-2.8z"/>',

        // Chat bubble with a small tail — Site Assistant
        'chat' => '<path d="M4 5.5h16a1 1 0 0 1 1 1V15a1 1 0 0 1-1 1H9l-4.2 3.4A.6.6 0 0 1 4 18.9V16H4a1 1 0 0 1-1-1V6.5a1 1 0 0 1 1-1z"/><circle cx="8.3" cy="10.7" r="1"/><circle cx="12" cy="10.7" r="1"/><circle cx="15.7" cy="10.7" r="1"/>',

        // X — used to close the assistant panel
        'close' => '<path d="M5 5l14 14M19 5 5 19"/>',

        // Paper-plane send arrow — Site Assistant send button
        'send' => '<path d="M4 12 20 4l-6 16-2.5-7L4 12z"/>',
    ];

    $inner = $icons[$name] ?? '';
    if ($inner === '') {
        return '';
    }

    return '<svg class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" viewBox="0 0 24 24" '
        . 'fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" '
        . 'aria-hidden="true" focusable="false">' . $inner . '</svg>';
}
