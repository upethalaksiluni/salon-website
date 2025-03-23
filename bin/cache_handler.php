<?php
class CacheHandler {
    public static function set($key, $data, $expiry = 3600) {
        // Simple in-memory caching (for demonstration)
        // In a real application, you would use Redis, Memcached, etc.
        $_SESSION['cache_' . $key] = [
            'data' => $data,
            'expiry' => time() + $expiry
        ];
        return true;
    }
    
    public static function get($key) {
        if (isset($_SESSION['cache_' . $key])) {
            $cache = $_SESSION['cache_' . $key];
            
            // Check if cache has expired
            if ($cache['expiry'] > time()) {
                return $cache['data'];
            } else {
                // Remove expired cache
                unset($_SESSION['cache_' . $key]);
            }
        }
        return null;
    }
    
    public static function delete($key) {
        if (isset($_SESSION['cache_' . $key])) {
            unset($_SESSION['cache_' . $key]);
            return true;
        }
        return false;
    }
}
?>