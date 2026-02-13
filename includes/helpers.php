<?php
// ===================================================
// HELPER FUNCTIONS - Christian Family Centre International
// ===================================================

defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// FORMATTING FUNCTIONS
// ====================================================================

class Formatter {
    
    /**
     * Get user initials for avatar
     */
    public static function getUserInitials($name) {
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
     * Format currency
     */
    public static function formatCurrency($amount, $currency = 'SZL') {
        if ($amount == 0) return '0.00';
        return number_format($amount, 2) . ' ' . $currency;
    }
    
    /**
     * Format date
     */
    public static function formatDate($date, $format = 'M d, Y') {
        if (empty($date) || $date == '0000-00-00') return '';
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }
    
    /**
     * Format date and time
     */
    public static function formatDateTime($date, $format = 'M d, Y H:i') {
        if (empty($date) || $date == '0000-00-00 00:00:00') return '';
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }
    
    /**
     * Format date in relative terms
     */
    public static function formatRelativeDate($date) {
        if (empty($date) || $date == '0000-00-00 00:00:00') return '';
        
        $now = time();
        $timestamp = strtotime($date);
        $diff = $now - $timestamp;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes != 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours != 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days != 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return $weeks . ' week' . ($weeks != 1 ? 's' : '') . ' ago';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' month' . ($months != 1 ? 's' : '') . ' ago';
        } else {
            $years = floor($diff / 31536000);
            return $years . ' year' . ($years != 1 ? 's' : '') . ' ago';
        }
    }
    
    /**
     * Truncate text with ellipsis
     */
    public static function truncateText($text, $length = 100) {
        if (strlen($text) <= $length) return $text;
        
        $truncated = substr($text, 0, $length);
        // Don't cut in the middle of a word if possible
        if (substr($truncated, -1) != ' ' && substr($text, $length, 1) != ' ') {
            $truncated = substr($truncated, 0, strrpos($truncated, ' '));
        }
        
        return $truncated . '...';
    }
    
    /**
     * Get status badge HTML
     */
    public static function getStatusBadge($status) {
        $statuses = [
            'pending' => ['class' => 'badge bg-warning', 'text' => 'Pending'],
            'approved' => ['class' => 'badge bg-success', 'text' => 'Approved'],
            'rejected' => ['class' => 'badge bg-danger', 'text' => 'Rejected'],
            'addressed' => ['class' => 'badge bg-info', 'text' => 'Addressed'],
            'closed' => ['class' => 'badge bg-secondary', 'text' => 'Closed'],
            'registered' => ['class' => 'badge bg-primary', 'text' => 'Registered'],
            'attended' => ['class' => 'badge bg-success', 'text' => 'Attended'],
            'cancelled' => ['class' => 'badge bg-danger', 'text' => 'Cancelled'],
            'no_show' => ['class' => 'badge bg-dark', 'text' => 'No Show'],
            'active' => ['class' => 'badge bg-success', 'text' => 'Active'],
            'inactive' => ['class' => 'badge bg-secondary', 'text' => 'Inactive'],
            'completed' => ['class' => 'badge bg-success', 'text' => 'Completed'],
            'failed' => ['class' => 'badge bg-danger', 'text' => 'Failed'],
            'refunded' => ['class' => 'badge bg-warning', 'text' => 'Refunded'],
            'draft' => ['class' => 'badge bg-secondary', 'text' => 'Draft'],
            'published' => ['class' => 'badge bg-success', 'text' => 'Published'],
            'archived' => ['class' => 'badge bg-dark', 'text' => 'Archived'],
            'high' => ['class' => 'badge bg-danger', 'text' => 'High'],
            'medium' => ['class' => 'badge bg-warning', 'text' => 'Medium'],
            'low' => ['class' => 'badge bg-info', 'text' => 'Low'],
            'normal' => ['class' => 'badge bg-primary', 'text' => 'Normal'],
            'urgent' => ['class' => 'badge bg-danger', 'text' => 'Urgent'],
        ];
        
        $statusData = $statuses[$status] ?? ['class' => 'badge bg-secondary', 'text' => ucfirst($status)];
        return '<span class="' . $statusData['class'] . '">' . $statusData['text'] . '</span>';
    }
    
    /**
     * Get category badge HTML
     */
    public static function getCategoryBadge($category) {
        $categories = [
            'health' => ['class' => 'badge bg-danger', 'text' => 'Health'],
            'financial' => ['class' => 'badge bg-success', 'text' => 'Financial'],
            'family' => ['class' => 'badge bg-info', 'text' => 'Family'],
            'spiritual' => ['class' => 'badge bg-primary', 'text' => 'Spiritual'],
            'work' => ['class' => 'badge bg-warning', 'text' => 'Work'],
            'other' => ['class' => 'badge bg-secondary', 'text' => 'Other'],
        ];
        
        $categoryData = $categories[$category] ?? ['class' => 'badge bg-secondary', 'text' => ucfirst($category)];
        return '<span class="' . $categoryData['class'] . '">' . $categoryData['text'] . '</span>';
    }
}

// ====================================================================
// HTML HELPER FUNCTIONS
// ====================================================================

class HTMLHelper {
    
    /**
     * Generate breadcrumbs
     */
    public static function breadcrumbs($pages) {
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
     * Get pagination HTML
     */
    public static function pagination($total_items, $items_per_page, $current_page, $url) {
        $total_pages = ceil($total_items / $items_per_page);
        
        if ($total_pages <= 1) return '';
        
        $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        $prev_class = $current_page <= 1 ? ' disabled' : '';
        $prev_page = max(1, $current_page - 1);
        $html .= '<li class="page-item' . $prev_class . '">';
        $html .= '<a class="page-link" href="' . $url . 'page=' . $prev_page . '" aria-label="Previous">';
        $html .= '<span aria-hidden="true">&laquo;</span></a></li>';
        
        // Page numbers
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = $i == $current_page ? ' active' : '';
            $html .= '<li class="page-item' . $active_class . '">';
            $html .= '<a class="page-link" href="' . $url . 'page=' . $i . '">' . $i . '</a></li>';
        }
        
        // Next button
        $next_class = $current_page >= $total_pages ? ' disabled' : '';
        $next_page = min($total_pages, $current_page + 1);
        $html .= '<li class="page-item' . $next_class . '">';
        $html .= '<a class="page-link" href="' . $url . 'page=' . $next_page . '" aria-label="Next">';
        $html .= '<span aria-hidden="true">&raquo;</span></a></li>';
        
        $html .= '</ul></nav>';
        
        return $html;
    }
    
    /**
     * Generate options for select dropdown
     */
    public static function generateOptions($options, $selected = null, $placeholder = 'Select...') {
        $html = '';
        
        if ($placeholder) {
            $html .= '<option value="">' . $placeholder . '</option>';
        }
        
        foreach ($options as $value => $label) {
            $is_selected = ($selected == $value) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $is_selected . '>' . htmlspecialchars($label) . '</option>';
        }
        
        return $html;
    }
}

// ====================================================================
// FILE HANDLING FUNCTIONS (UPDATED to use SecurityManager)
// ====================================================================

class FileHandler {
    
    /**
     * Upload file with validation
     */
    public static function upload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'], $max_size = MAX_FILE_SIZE) {
        require_once __DIR__ . '/SecurityManager.php';
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'File upload error'];
        }
        
        // Use SecurityManager for validation
        $validation = SecurityManager::validateFileUpload($file, $allowed_types, $max_size);
        
        if (!$validation['success']) {
            return ['error' => implode(', ', $validation['errors'])];
        }
        
        // Use SecurityManager for secure upload
        $result = SecurityManager::secureUpload($file, $allowed_types, $max_size);
        
        if ($result['success']) {
            return ['success' => true, 'filename' => $result['filename'], 'path' => $result['path']];
        }
        
        return ['error' => 'Failed to upload file'];
    }
    
    /**
     * Delete file
     */
    public static function delete($filename) {
        $file_path = UPLOAD_PATH . basename($filename);
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return false;
    }
    
    /**
     * Upload user avatar (UPDATED to use Database singleton)
     */
    public static function uploadAvatar($file, $user_id) {
        $result = self::upload($file, ['jpg', 'jpeg', 'png', 'gif'], 2097152); // 2MB max
        
        if (isset($result['success'])) {
            try {
                require_once __DIR__ . '/database.php';
                $db = Database::getInstance();
                $conn = $db->getConnection();
                
                // First try to update user_profiles
                $stmt = $conn->prepare("UPDATE user_profiles SET avatar_url = ? WHERE user_id = ?");
                $stmt->execute([$result['path'], $user_id]);
                
                // If no rows affected, try to insert
                if ($stmt->rowCount() === 0) {
                    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, avatar_url) VALUES (?, ?)");
                    $stmt->execute([$user_id, $result['path']]);
                }
                
                // Also update users table for backward compatibility
                $stmt = $conn->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
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
// VALIDATION CLASS (UPDATED to use SecurityManager)
// ====================================================================

class Validator {
    private static $errors = [];
    
    public static function validateEmail($email) {
        require_once __DIR__ . '/SecurityManager.php';
        if (!SecurityManager::validateEmail($email)) {
            self::$errors[] = "Invalid email format";
            return false;
        }
        return true;
    }
    
    public static function validatePhone($phone) {
        require_once __DIR__ . '/SecurityManager.php';
        if (!SecurityManager::validatePhone($phone)) {
            self::$errors[] = "Invalid phone number";
            return false;
        }
        return true;
    }
    
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            self::$errors[] = "Invalid date format (expected: $format)";
            return false;
        }
        return true;
    }
    
    public static function validateRequired($fields, $data) {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                self::$errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        return empty(self::$errors);
    }
    
    public static function getErrors() {
        return self::$errors;
    }
    
    public static function clearErrors() {
        self::$errors = [];
    }
    
    public static function validateFields($data, $rules) {
        self::clearErrors();
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (in_array('required', $rule) && empty($value)) {
                self::$errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                continue;
            }
            
            if (!empty($value)) {
                foreach ($rule as $validation) {
                    if ($validation === 'email' && !self::validateEmail($value)) {
                        break;
                    } elseif ($validation === 'phone' && !self::validatePhone($value)) {
                        break;
                    } elseif (strpos($validation, 'min:') === 0) {
                        $min = (int) str_replace('min:', '', $validation);
                        if (strlen($value) < $min) {
                            self::$errors[] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters";
                            break;
                        }
                    } elseif (strpos($validation, 'max:') === 0) {
                        $max = (int) str_replace('max:', '', $validation);
                        if (strlen($value) > $max) {
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
// BACKWARD COMPATIBILITY FUNCTIONS (with existence checks)
// ====================================================================

if (!function_exists('getUserInitials')) {
    function getUserInitials($name) {
        return Formatter::getUserInitials($name);
    }
}

if (!function_exists('formatCurrencyAmount')) {
    function formatCurrencyAmount($amount, $currency = 'SZL') {
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
?>