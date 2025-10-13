<?php
class AuthGuard {
    public static function requireLogin() {
        session_start();
        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }
    }
}
