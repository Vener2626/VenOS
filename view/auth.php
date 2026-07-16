<?php
// Shared by every protected page. Include this at the very top, before any
// output, then call require_role([...]) with the roles allowed on that page.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_role() {
    return $_SESSION['role'] ?? null;
}

function is_logged_in() {
    return current_role() !== null;
}

/**
 * Guard a page to specific roles. Example:
 *   require_role(['owner']);           // dashboard.php, reports.php, manage_products.php, settings.php
 *   require_role(['cashier']);         // index.php (the POS / Sale screen)
 */
function require_role(array $roles) {
    $role = current_role();

    if ($role === null) {
        header('Location: login.php');
        exit;
    }

    if (!in_array($role, $roles, true)) {
        // Logged in, just not allowed here — send them to the area their PIN unlocks.
        header('Location: ' . ($role === 'owner' ? 'dashboard.php' : 'index.php'));
        exit;
    }
}