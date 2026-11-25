<?php
/**
 * Departments / subjects with their Head of Department (owner 2026-11-26, idea from ilboru.ac.tz "What We Learn").
 * Admins fill in the real names in admin/manage-departments.php. A tile shows its Head of Department only when a
 * name has been entered: nothing is invented. The first time, one row per subject taught is created
 * (the 18 subjects of the Results System, see school-info.php).
 */

function ensure_departments_table(PDO $pdo): void
{
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS departments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                subject VARCHAR(120) NOT NULL,
                head_name VARCHAR(150) NULL,
                head_phone VARCHAR(40) NULL,
                sort_order SMALLINT NOT NULL DEFAULT 0,
                is_visible TINYINT(1) NOT NULL DEFAULT 1,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        if ((int) $pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn() === 0) {
            $insert = $pdo->prepare('INSERT INTO departments (subject, sort_order) VALUES (:s, :o)');
            $order = 0;
            foreach (SCHOOL_SUBJECTS as $subjects) {
                foreach ($subjects as $subject) {
                    $insert->execute(['s' => $subject, 'o' => ++$order]);
                }
            }
        }
    } catch (Throwable $e) {
        // Try again on a later request.
    }
}

/** Visible departments in display order. */
function school_departments(PDO $pdo): array
{
    try {
        return $pdo->query('SELECT * FROM departments WHERE is_visible = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/** Renders the flip tiles (front: subject; back: Head of Department + contact, only when a name is set). */
function render_department_tiles(array $departments): string
{
    $html = '<div class="dept-grid">';
    foreach ($departments as $d) {
        $name = trim((string) ($d['head_name'] ?? ''));
        $phone = trim((string) ($d['head_phone'] ?? ''));
        $html .= '<div class="dept-tile' . ($name !== '' ? ' has-head' : '') . '" tabindex="0">';
        $html .= '<div class="dept-face dept-front"><span>' . e($d['subject']) . '</span></div>';
        if ($name !== '') {
            $contact = $phone !== ''
                ? '<a href="tel:' . e(preg_replace('/[^\d+]/', '', $phone)) . '">Contact</a>'
                : '<a href="' . e(site_link('contact.php')) . '">Contact</a>';
            $html .= '<div class="dept-face dept-back"><strong>' . e($name) . '</strong><small>Head of Department</small>' . $contact . '</div>';
        }
        $html .= '</div>';
    }
    return $html . '</div>';
}
