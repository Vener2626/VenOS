<?php

/**
 * Owner PIN protection.
 *
 * - The PIN is stored hashed in the `settings` table (see pin_schema.sql).
 * - Unlocking (verify_pin.php) stamps $_SESSION['owner_unlocked_at'].
 * - Protected pages (Dashboard/Reports/Products/Settings) always show the
 *   lock screen on load — pin_lock.js does not trust any existing session,
 *   it always asks. The session stamp is only used server-side so the
 *   underlying data endpoints can't be hit directly without unlocking first.
 */

const PIN_UNLOCK_TTL_SECONDS = 300; // how long a verified PIN authorizes data requests for

function pin_getHash(PDO $pdo): string
{
    $row = $pdo->query('SELECT pin_hash FROM settings WHERE id = 1')->fetch();

    if (!$row) {
        // First run: bootstrap a default PIN so the app isn't locked out.
        $hash = password_hash('1234', PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO settings (id, pin_hash) VALUES (1, ?)')->execute([$hash]);
        return $hash;
    }

    return $row['pin_hash'];
}

function pin_isUnlocked(): bool
{
    return isset($_SESSION['owner_unlocked_at'])
        && (time() - $_SESSION['owner_unlocked_at']) < PIN_UNLOCK_TTL_SECONDS;
}

/**
 * Call at the top of any JSON endpoint that should only run after the
 * owner PIN has been verified in this session.
 */
function pin_requireUnlocked(): void
{
    if (!pin_isUnlocked()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'PIN verification required.']);
        exit;
    }
}