<?php
/**
 * Enforces that protected pages are only reachable when the browser provides
 * a same-origin referrer. This prevents a user from copying a protected URL
 * and opening it in a fresh tab/window without navigating through the app,
 * forcing a re-login instead.
 */
if (!function_exists('forceLogoutAndRedirect')) {
    function forceLogoutAndRedirect(string $redirectPath) : void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        header("Location: {$redirectPath}");
        exit();
    }
}

if (!function_exists('enforceSameOriginNavigation')) {
    function enforceSameOriginNavigation(string $redirectPath) : void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Only need to enforce the rule on GET requests (page loads).
        if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET') {
            return;
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (empty($referer)) {
            forceLogoutAndRedirect($redirectPath);
        }

        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        $refererHost = parse_url($referer, PHP_URL_HOST) ?? '';

        if (empty($currentHost) || empty($refererHost)) {
            forceLogoutAndRedirect($redirectPath);
        }

        if (!hash_equals(strtolower($currentHost), strtolower($refererHost))) {
            forceLogoutAndRedirect($redirectPath);
        }
    }
}
