<?php
// includes/session.php

class SessionManager {
    
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
    
    public static function destroy() {
        session_unset();
        session_destroy();
    }
    
    public static function regenerateId() {
        session_regenerate_id(true);
    }
    
    public static function setFlash($type, $message) {
        self::set('flash', ['type' => $type, 'message' => $message]);
    }
    
    public static function getFlash() {
        $flash = self::get('flash');
        self::remove('flash');
        return $flash;
    }
    
    public static function isAuthenticated() {
        return self::get('logged_in', false) && self::get('user_id');
    }
    
    public static function getUserId() {
        return self::get('user_id');
    }
    
    public static function getUserRole() {
        return self::get('user_role');
    }
    
    public static function hasRole($role) {
        return self::getUserRole() === $role;
    }
}

// Start session automatically
SessionManager::start();
?>