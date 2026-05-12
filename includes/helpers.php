<?php
/**
 * helpers.php – Comprehensive Helpers for CFCI Church System
 * Updated for enhanced schema v1.1
 */

defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// 1. FORMATTING & UI HELPERS
// ====================================================================

class Formatter
{
    /**
     * Get user initials for avatar
     */
    public static function getUserInitials($name)
    {
        $initials = '';
        $words = explode(' ', $name);
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
                if (strlen($initials) >= 2) break;
            }
        }
        return $initials ?: 'U';
    }

    /**
     * Format currency (default Eswatini Lilangeni)
     */
    public static function formatCurrency($amount, $currency = 'E')
    {
        if ($amount == 0) return '0.00';
        return $currency . ' ' . number_format($amount, 2);
    }

    /**
     * Format date
     */
    public static function formatDate($date, $format = 'M d, Y')
    {
        if (empty($date) || $date == '0000-00-00') return '';
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Format date and time
     */
    public static function formatDateTime($date, $format = 'M d, Y H:i')
    {
        if (empty($date) || $date == '0000-00-00 00:00:00') return '';
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Relative time (e.g., "2 hours ago")
     */
    public static function formatRelativeDate($datetime)
    {
        if (empty($datetime)) return '';
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) return 'just now';
        $intervals = [
            31536000 => 'year',
            2592000  => 'month',
            604800   => 'week',
            86400    => 'day',
            3600     => 'hour',
            60       => 'minute',
        ];
        foreach ($intervals as $seconds => $label) {
            $count = floor($diff / $seconds);
            if ($count >= 1) {
                return $count . ' ' . $label . ($count > 1 ? 's' : '') . ' ago';
            }
        }
        return 'just now';
    }

    /**
     * Truncate text with ellipsis
     */
    public static function truncateText($text, $length = 100)
    {
        if (mb_strlen($text) <= $length) return $text;
        $truncated = mb_substr($text, 0, $length);
        if (mb_substr($truncated, -1) !== ' ' && mb_substr($text, $length, 1) !== ' ') {
            $lastSpace = mb_strrpos($truncated, ' ');
            if ($lastSpace !== false) {
                $truncated = mb_substr($truncated, 0, $lastSpace);
            }
        }
        return $truncated . '...';
    }

    /**
     * Status badge HTML
     */
    public static function getStatusBadge($status)
    {
        $statuses = [
            'pending'   => ['class' => 'badge bg-warning', 'text' => 'Pending'],
            'approved'  => ['class' => 'badge bg-success', 'text' => 'Approved'],
            'rejected'  => ['class' => 'badge bg-danger',  'text' => 'Rejected'],
            'addressed' => ['class' => 'badge bg-info',    'text' => 'Addressed'],
            'closed'    => ['class' => 'badge bg-secondary','text' => 'Closed'],
            'registered'=> ['class' => 'badge bg-primary', 'text' => 'Registered'],
            'attended'  => ['class' => 'badge bg-success', 'text' => 'Attended'],
            'cancelled' => ['class' => 'badge bg-danger',  'text' => 'Cancelled'],
            'no_show'   => ['class' => 'badge bg-dark',    'text' => 'No Show'],
            'active'    => ['class' => 'badge bg-success', 'text' => 'Active'],
            'inactive'  => ['class' => 'badge bg-secondary','text' => 'Inactive'],
            'completed' => ['class' => 'badge bg-success', 'text' => 'Completed'],
            'failed'    => ['class' => 'badge bg-danger',  'text' => 'Failed'],
            'refunded'  => ['class' => 'badge bg-warning', 'text' => 'Refunded'],
            'draft'     => ['class' => 'badge bg-secondary','text' => 'Draft'],
            'published' => ['class' => 'badge bg-success', 'text' => 'Published'],
            'archived'  => ['class' => 'badge bg-dark',    'text' => 'Archived'],
            'high'      => ['class' => 'badge bg-danger',  'text' => 'High'],
            'medium'    => ['class' => 'badge bg-warning', 'text' => 'Medium'],
            'low'       => ['class' => 'badge bg-info',    'text' => 'Low'],
            'normal'    => ['class' => 'badge bg-primary', 'text' => 'Normal'],
            'urgent'    => ['class' => 'badge bg-danger',  'text' => 'Urgent'],
        ];

        $data = $statuses[$status] ?? ['class' => 'badge bg-secondary', 'text' => ucfirst($status)];
        return '<span class="' . $data['class'] . '">' . $data['text'] . '</span>';
    }

    /**
     * Category badge
     */
    public static function getCategoryBadge($category)
    {
        $categories = [
            'health'    => ['class' => 'badge bg-danger',  'text' => 'Health'],
            'financial' => ['class' => 'badge bg-success', 'text' => 'Financial'],
            'family'    => ['class' => 'badge bg-info',    'text' => 'Family'],
            'spiritual' => ['class' => 'badge bg-primary', 'text' => 'Spiritual'],
            'work'      => ['class' => 'badge bg-warning', 'text' => 'Work'],
            'other'     => ['class' => 'badge bg-secondary','text' => 'Other'],
        ];

        $data = $categories[$category] ?? ['class' => 'badge bg-secondary', 'text' => ucfirst($category)];
        return '<span class="' . $data['class'] . '">' . $data['text'] . '</span>';
    }
}

// ====================================================================
// 2. HTML GENERATION HELPERS
// ====================================================================

class HTMLHelper
{
    /**
     * Breadcrumb navigation
     */
    public static function breadcrumbs($pages)
    {
        $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
        $html .= '<li class="breadcrumb-item"><a href="' . SITE_URL . '">Home</a></li>';
        foreach ($pages as $page) {
            if (isset($page['url'])) {
                $html .= '<li class="breadcrumb-item"><a href="' . $page['url'] . '">' . $page['title'] . '</a></li>';
            } else {
                $html .= '<li class="breadcrumb-item active" aria-current="page">' . $page['title'] . '</li>';
            }
        }
        $html .= '</ol></nav>';
        return $html;
    }

    /**
     * Pagination
     */
    public static function pagination($total_items, $items_per_page, $current_page, $url)
    {
        $total_pages = ceil($total_items / $items_per_page);
        if ($total_pages <= 1) return '';

        $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

        // Previous
        $prev_class = $current_page <= 1 ? ' disabled' : '';
        $prev_page = max(1, $current_page - 1);
        $html .= '<li class="page-item' . $prev_class . '">';
        $html .= '<a class="page-link" href="' . $url . 'page=' . $prev_page . '" aria-label="Previous">';
        $html .= '<span aria-hidden="true">&laquo;</span></a></li>';

        // Page numbers
        $start = max(1, $current_page - 2);
        $end = min($total_pages, $current_page + 2);
        for ($i = $start; $i <= $end; $i++) {
            $active = $i == $current_page ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $url . 'page=' . $i . '">' . $i . '</a></li>';
        }

        // Next
        $next_class = $current_page >= $total_pages ? ' disabled' : '';
        $next_page = min($total_pages, $current_page + 1);
        $html .= '<li class="page-item' . $next_class . '">';
        $html .= '<a class="page-link" href="' . $url . 'page=' . $next_page . '" aria-label="Next">';
        $html .= '<span aria-hidden="true">&raquo;</span></a></li>';

        $html .= '</ul></nav>';
        return $html;
    }

    /**
     * Dropdown options from associative array
     */
    public static function generateOptions($options, $selected = null, $placeholder = 'Select...')
    {
        $html = '';
        if ($placeholder) {
            $html .= '<option value="">' . $placeholder . '</option>';
        }
        foreach ($options as $value => $label) {
            $is_selected = ($selected !== null && $selected == $value) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $is_selected . '>' . htmlspecialchars($label) . '</option>';
        }
        return $html;
    }
}

// ====================================================================
// 3. FILE HANDLING (UPDATED)
// ====================================================================

class FileHandler
{
    /**
     * Upload file with SecurityManager validation
     */
    public static function upload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'], $max_size = MAX_FILE_SIZE)
    {
        require_once __DIR__ . '/SecurityManager.php';

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'File upload error code: ' . $file['error']];
        }

        $validation = SecurityManager::validateFileUpload($file, $allowed_types, $max_size);
        if (!$validation['success']) {
            return ['error' => implode(', ', $validation['errors'])];
        }

        $result = SecurityManager::secureUpload($file, $allowed_types, $max_size);
        if ($result['success']) {
            return ['success' => true, 'filename' => $result['filename'], 'path' => $result['path']];
        }
        return ['error' => 'Failed to upload file'];
    }

    /**
     * Delete file from server
     */
    public static function delete($filename)
    {
        $file_path = UPLOAD_PATH . basename($filename);
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return false;
    }

    /**
     * Upload avatar and update both user_profiles and users tables
     */
    public static function uploadAvatar($file, $user_id)
    {
        $result = self::upload($file, ['jpg', 'jpeg', 'png', 'gif'], 2097152); // 2MB
        if (isset($result['success'])) {
            try {
                $db = Database::getInstance()->getConnection();

                // Update user_profiles (if exists)
                $stmt = $db->prepare("UPDATE user_profiles SET avatar_url = ? WHERE user_id = ?");
                $stmt->execute([$result['path'], $user_id]);

                if ($stmt->rowCount() === 0) {
                    $stmt = $db->prepare("INSERT INTO user_profiles (user_id, avatar_url) VALUES (?, ?)");
                    $stmt->execute([$user_id, $result['path']]);
                }

                // Also update users table for direct access
                $stmt = $db->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
                $stmt->execute([$result['path'], $user_id]);

            } catch (Exception $e) {
                error_log("Avatar update error: " . $e->getMessage());
                return ['error' => 'Failed to update user profile'];
            }
        }
        return $result;
    }
}

// ====================================================================
// 4. VALIDATION CLASS
// ====================================================================

class Validator
{
    private static $errors = [];

    public static function validateEmail($email)
    {
        require_once __DIR__ . '/SecurityManager.php';
        if (!SecurityManager::validateEmail($email)) {
            self::$errors[] = "Invalid email format";
            return false;
        }
        return true;
    }

    public static function validatePhone($phone)
    {
        require_once __DIR__ . '/SecurityManager.php';
        if (!SecurityManager::validatePhone($phone)) {
            self::$errors[] = "Invalid phone number";
            return false;
        }
        return true;
    }

    public static function validateRequired($fields, $data)
    {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                self::$errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        return empty(self::$errors);
    }

    public static function getErrors()
    {
        return self::$errors;
    }

    public static function clearErrors()
    {
        self::$errors = [];
    }

    public static function validateFields($data, $rules)
    {
        self::clearErrors();
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;

            if (in_array('required', $ruleSet) && empty($value)) {
                self::$errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                continue;
            }

            if (!empty($value)) {
                foreach ($ruleSet as $rule) {
                    if ($rule === 'email' && !self::validateEmail($value)) break;
                    if ($rule === 'phone' && !self::validatePhone($value)) break;
                    if (strpos($rule, 'min:') === 0) {
                        $min = (int) str_replace('min:', '', $rule);
                        if (mb_strlen($value) < $min) {
                            self::$errors[] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters";
                            break;
                        }
                    }
                    if (strpos($rule, 'max:') === 0) {
                        $max = (int) str_replace('max:', '', $rule);
                        if (mb_strlen($value) > $max) {
                            self::$errors[] = ucfirst(str_replace('_', ' ', $field)) . " must be at most {$max} characters";
                            break;
                        }
                    }
                }
            }
        }
        return empty(self::$errors);
    }
}

// ====================================================================
// 5. NEW: USER HELPER (queries the enhanced schema)
// ====================================================================

class UserHelper
{
    /**
     * Get full user profile (including admin role, email_verified)
     */
    public static function getUserById($user_id)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                SELECT u.*, up.*, a.role as admin_role,
                       (SELECT COUNT(*) FROM email_verifications ev WHERE ev.user_id = u.id) as email_verified_attempts,
                       u.email_verified_at IS NOT NULL as email_verified
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN admins a ON u.id = a.user_id
                WHERE u.id = :id
            ");
            $stmt->execute([':id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("getUserById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user's email is verified
     */
    public static function isEmailVerified($user_id)
    {
        $user = self::getUserById($user_id);
        return $user && !empty($user['email_verified_at']);
    }

    /**
     * Get the effective role (admin_role if exists, else user role)
     */
    public static function getEffectiveRole($user_id)
    {
        $user = self::getUserById($user_id);
        if ($user) {
            return $user['admin_role'] ?? $user['role'];
        }
        return null;
    }
}

// ====================================================================
// 6. NEW: SETTINGS HELPER (cached from settings table)
// ====================================================================

class SettingsHelper
{
    private static $cache = null;

    private static function loadCache()
    {
        if (self::$cache === null) {
            try {
                $pdo = Database::getInstance()->getConnection();
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
                self::$cache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
            } catch (Exception $e) {
                error_log("Settings load error: " . $e->getMessage());
                self::$cache = [];
            }
        }
    }

    public static function get($key, $default = null)
    {
        self::loadCache();
        return self::$cache[$key] ?? $default;
    }

    public static function all()
    {
        self::loadCache();
        return self::$cache;
    }

    public static function clearCache()
    {
        self::$cache = null;
    }
}

// ====================================================================
// 7. BACKWARD COMPATIBLE FUNCTION WRAPPERS (with existence checks)
// ====================================================================

if (!function_exists('getUserInitials')) {
    function getUserInitials($name) {
        return Formatter::getUserInitials($name);
    }
}
if (!function_exists('formatCurrencyAmount')) {
    function formatCurrencyAmount($amount, $currency = 'E') {
        return Formatter::formatCurrency($amount, $currency);
    }
}
if (!function_exists('formatDateString')) {
    function formatDateString($date, $format = 'M d, Y') {
        return Formatter::formatDate($date, $format);
    }
}
if (!function_exists('formatDateTime')) {
    function formatDateTime($date, $format = 'M d, Y H:i') {
        return Formatter::formatDateTime($date, $format);
    }
}
if (!function_exists('formatRelativeDate')) {
    function formatRelativeDate($date) {
        return Formatter::formatRelativeDate($date);
    }
}
if (!function_exists('truncateTextContent')) {
    function truncateTextContent($text, $length = 100) {
        return Formatter::truncateText($text, $length);
    }
}
if (!function_exists('getStatusBadgeHtml')) {
    function getStatusBadgeHtml($status) {
        return Formatter::getStatusBadge($status);
    }
}
if (!function_exists('getCategoryBadgeHtml')) {
    function getCategoryBadgeHtml($category) {
        return Formatter::getCategoryBadge($category);
    }
}
if (!function_exists('getPaginationHTML')) {
    function getPaginationHTML($total_items, $items_per_page, $current_page, $url) {
        return HTMLHelper::pagination($total_items, $items_per_page, $current_page, $url);
    }
}
if (!function_exists('generateBreadcrumbs')) {
    function generateBreadcrumbs($pages) {
        return HTMLHelper::breadcrumbs($pages);
    }
}
if (!function_exists('uploadFile')) {
    function uploadFile($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'], $max_size = MAX_FILE_SIZE) {
        return FileHandler::upload($file, $allowed_types, $max_size);
    }
}
if (!function_exists('deleteFile')) {
    function deleteFile($filename) {
        return FileHandler::delete($filename);
    }
}
if (!function_exists('uploadAvatar')) {
    function uploadAvatar($file, $user_id) {
        return FileHandler::uploadAvatar($file, $user_id);
    }
}
if (!function_exists('validateRequiredFields')) {
    function validateRequiredFields($fields, $data) {
        return Validator::validateRequired($fields, $data);
    }
}
if (!function_exists('isValidDate')) {
    function isValidDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

// Additional compact helpers (optional aliases)
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('old')) {
    function old($key, $default = '') {
        return e($_POST[$key] ?? $default);
    }
}
if (!function_exists('asset')) {
    function asset($path) {
        return SITE_URL . 'assets/' . ltrim($path, '/');
    }
}
if (!function_exists('upload_url')) {
    function upload_url($path) {
        return SITE_URL . 'uploads/' . ltrim($path, '/');
    }
}