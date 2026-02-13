<?php
// ===================================================
// SETTINGS FUNCTIONS - Christian Family Centre International
// ===================================================

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// SETTINGS MANAGER
// ====================================================================

class SettingsManager {
    private $db;
    private $cache = [];
    private $cache_expiry = 300; // 5 minutes
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Get setting value
     */
    public function get($key, $default = null) {
        // Check cache first
        if (isset($this->cache[$key]) && time() - $this->cache[$key]['timestamp'] < $this->cache_expiry) {
            return $this->cache[$key]['value'];
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT value, data_type 
                FROM settings 
                WHERE `key` = ? AND status = 'active'
            ");
            
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $value = $this->castValue($result['value'], $result['data_type']);
                $this->cache[$key] = [
                    'value' => $value,
                    'timestamp' => time()
                ];
                return $value;
            }
            
            return $default;
            
        } catch (Exception $e) {
            error_log("Get setting error: " . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Set setting value
     */
    public function set($key, $value, $data_type = 'string', $description = null) {
        try {
            // Check if setting exists
            $check_stmt = $this->db->prepare("SELECT id FROM settings WHERE `key` = ?");
            $check_stmt->execute([$key]);
            
            $string_value = $this->stringifyValue($value, $data_type);
            
            if ($check_stmt->rowCount() > 0) {
                $stmt = $this->db->prepare("
                    UPDATE settings 
                    SET value = ?, data_type = ?, description = ?, updated_at = NOW()
                    WHERE `key` = ?
                ");
                
                $result = $stmt->execute([$string_value, $data_type, $description, $key]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO settings 
                    (`key`, value, data_type, description, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 'active', NOW(), NOW())
                ");
                
                $result = $stmt->execute([$key, $string_value, $data_type, $description]);
            }
            
            // Clear cache
            unset($this->cache[$key]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Set setting error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all settings in a group
     */
    public function getGroup($group_prefix) {
        try {
            $stmt = $this->db->prepare("
                SELECT `key`, value, data_type, description 
                FROM settings 
                WHERE `key` LIKE ? AND status = 'active'
                ORDER BY `key`
            ");
            
            $stmt->execute([$group_prefix . '%']);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $settings = [];
            foreach ($results as $row) {
                $settings[$row['key']] = $this->castValue($row['value'], $row['data_type']);
            }
            
            return $settings;
            
        } catch (Exception $e) {
            error_log("Get settings group error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Set multiple settings
     */
    public function setMultiple($settings_array) {
        try {
            $this->db->beginTransaction();
            
            foreach ($settings_array as $key => $value) {
                if (is_array($value) && isset($value['value'], $value['data_type'])) {
                    $this->set($key, $value['value'], $value['data_type'], $value['description'] ?? null);
                } else {
                    $this->set($key, $value);
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Set multiple settings error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete setting
     */
    public function delete($key) {
        try {
            $stmt = $this->db->prepare("DELETE FROM settings WHERE `key` = ?");
            $result = $stmt->execute([$key]);
            
            // Clear cache
            unset($this->cache[$key]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Delete setting error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all settings
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT `key`, value, data_type, description, status, created_at, updated_at
                FROM settings 
                ORDER BY `key`
            ");
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $settings = [];
            foreach ($results as $row) {
                $settings[$row['key']] = [
                    'value' => $this->castValue($row['value'], $row['data_type']),
                    'data_type' => $row['data_type'],
                    'description' => $row['description'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
                ];
            }
            
            return $settings;
            
        } catch (Exception $e) {
            error_log("Get all settings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clear settings cache
     */
    public function clearCache() {
        $this->cache = [];
        return true;
    }
    
    /**
     * Cast value to appropriate type
     */
    private function castValue($value, $data_type) {
        if ($value === null) {
            return null;
        }
        
        switch ($data_type) {
            case 'integer':
            case 'int':
                return (int) $value;
                
            case 'float':
            case 'double':
            case 'decimal':
                return (float) $value;
                
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                
            case 'array':
            case 'json':
                return json_decode($value, true) ?? [];
                
            case 'object':
                return json_decode($value) ?? new stdClass();
                
            default:
                return (string) $value;
        }
    }
    
    /**
     * Convert value to string for storage
     */
    private function stringifyValue($value, $data_type) {
        switch ($data_type) {
            case 'array':
            case 'json':
            case 'object':
                return json_encode($value);
                
            case 'boolean':
            case 'bool':
                return $value ? '1' : '0';
                
            default:
                return (string) $value;
        }
    }
    
    /**
     * Get site settings
     */
    public function getSiteSettings() {
        return $this->getGroup('site.');
    }
    
    /**
     * Get email settings
     */
    public function getEmailSettings() {
        return $this->getGroup('email.');
    }
    
    /**
     * Get security settings
     */
    public function getSecuritySettings() {
        return $this->getGroup('security.');
    }
    
    /**
     * Get payment settings
     */
    public function getPaymentSettings() {
        return $this->getGroup('payment.');
    }
    
    /**
     * Get notification settings
     */
    public function getNotificationSettings() {
        return $this->getGroup('notification.');
    }
    
    /**
     * Update site settings
     */
    public function updateSiteSettings($settings) {
        $site_settings = [];
        
        foreach ($settings as $key => $value) {
            $site_settings['site.' . $key] = $value;
        }
        
        return $this->setMultiple($site_settings);
    }
    
    /**
     * Update email settings
     */
    public function updateEmailSettings($settings) {
        $email_settings = [];
        
        foreach ($settings as $key => $value) {
            $email_settings['email.' . $key] = $value;
        }
        
        return $this->setMultiple($email_settings);
    }
    
    /**
     * Get setting with fallback to constant
     */
    public function getWithFallback($key, $constant_name = null) {
        $value = $this->get($key);
        
        if ($value === null && $constant_name && defined($constant_name)) {
            $value = constant($constant_name);
        }
        
        return $value;
    }
    
    /**
     * Export settings to JSON
     */
    public function exportToJSON() {
        $settings = $this->getAll();
        return json_encode($settings, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import settings from JSON
     */
    public function importFromJSON($json_data) {
        try {
            $settings = json_decode($json_data, true);
            
            if (!$settings) {
                return ['success' => false, 'message' => 'Invalid JSON data'];
            }
            
            $this->db->beginTransaction();
            
            foreach ($settings as $key => $data) {
                if (is_array($data) && isset($data['value'], $data['data_type'])) {
                    $this->set(
                        $key,
                        $data['value'],
                        $data['data_type'],
                        $data['description'] ?? null
                    );
                }
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Settings imported successfully'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Import settings error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Import failed: ' . $e->getMessage()];
        }
    }
}

// ====================================================================
// CONFIGURATION GROUPS
// ====================================================================

class ConfigGroups {
    const SITE = 'site';
    const EMAIL = 'email';
    const SECURITY = 'security';
    const PAYMENT = 'payment';
    const NOTIFICATION = 'notification';
    const APPEARANCE = 'appearance';
    const SOCIAL = 'social';
    const INTEGRATION = 'integration';
}

// ====================================================================
// DEFAULT SETTINGS
// ====================================================================

class DefaultSettings {
    public static function getDefaults() {
        return [
            // Site Settings
            'site.name' => ['value' => SITE_NAME, 'data_type' => 'string', 'description' => 'Website name'],
            'site.url' => ['value' => SITE_URL, 'data_type' => 'string', 'description' => 'Website URL'],
            'site.description' => ['value' => 'Christian Family Centre International', 'data_type' => 'string', 'description' => 'Site description'],
            'site.language' => ['value' => 'en', 'data_type' => 'string', 'description' => 'Default language'],
            'site.timezone' => ['value' => 'Africa/Mbabane', 'data_type' => 'string', 'description' => 'Default timezone'],
            'site.currency' => ['value' => 'SZL', 'data_type' => 'string', 'description' => 'Default currency'],
            'site.maintenance' => ['value' => false, 'data_type' => 'boolean', 'description' => 'Maintenance mode'],
            
            // Email Settings
            'email.from_name' => ['value' => SITE_NAME, 'data_type' => 'string', 'description' => 'Sender name'],
            'email.from_email' => ['value' => MAIL_FROM_EMAIL, 'data_type' => 'string', 'description' => 'Sender email'],
            'email.smtp_host' => ['value' => MAIL_HOST, 'data_type' => 'string', 'description' => 'SMTP host'],
            'email.smtp_port' => ['value' => MAIL_PORT, 'data_type' => 'integer', 'description' => 'SMTP port'],
            'email.smtp_secure' => ['value' => 'tls', 'data_type' => 'string', 'description' => 'SMTP security'],
            'email.smtp_auth' => ['value' => true, 'data_type' => 'boolean', 'description' => 'SMTP authentication'],
            
            // Security Settings
            'security.password_min_length' => ['value' => 8, 'data_type' => 'integer', 'description' => 'Minimum password length'],
            'security.password_require_uppercase' => ['value' => true, 'data_type' => 'boolean', 'description' => 'Require uppercase letters'],
            'security.password_require_numbers' => ['value' => true, 'data_type' => 'boolean', 'description' => 'Require numbers'],
            'security.max_login_attempts' => ['value' => 5, 'data_type' => 'integer', 'description' => 'Maximum login attempts'],
            'security.login_lockout_time' => ['value' => 900, 'data_type' => 'integer', 'description' => 'Login lockout time in seconds'],
            
            // Appearance Settings
            'appearance.theme' => ['value' => 'default', 'data_type' => 'string', 'description' => 'Theme name'],
            'appearance.logo' => ['value' => '/assets/images/logo.png', 'data_type' => 'string', 'description' => 'Logo path'],
            'appearance.favicon' => ['value' => '/assets/images/favicon.ico', 'data_type' => 'string', 'description' => 'Favicon path'],
            'appearance.primary_color' => ['value' => '#3b82f6', 'data_type' => 'string', 'description' => 'Primary color'],
            'appearance.secondary_color' => ['value' => '#10b981', 'data_type' => 'string', 'description' => 'Secondary color'],
            
            // Notification Settings
            'notification.email_new_user' => ['value' => true, 'data_type' => 'boolean', 'description' => 'Send email for new users'],
            'notification.email_prayer_request' => ['value' => true, 'data_type' => 'boolean', 'description' => 'Send email for prayer requests'],
            'notification.email_donation' => ['value' => true, 'data_type' => 'boolean', 'description' => 'Send email for donations'],
            'notification.email_event_registration' => ['value' => true, 'data_type' => 'boolean', 'description' => 'Send email for event registrations'],
            
            // Social Settings
            'social.facebook' => ['value' => '', 'data_type' => 'string', 'description' => 'Facebook page URL'],
            'social.twitter' => ['value' => '', 'data_type' => 'string', 'description' => 'Twitter profile URL'],
            'social.youtube' => ['value' => '', 'data_type' => 'string', 'description' => 'YouTube channel URL'],
            'social.instagram' => ['value' => '', 'data_type' => 'string', 'description' => 'Instagram profile URL'],
        ];
    }
    
    public static function installDefaults($settingsManager) {
        $defaults = self::getDefaults();
        return $settingsManager->setMultiple($defaults);
    }
}

// ====================================================================
// HELPER FUNCTIONS
// ====================================================================

function get_setting($key, $default = null) {
    global $db;
    static $settingsManager = null;
    
    if ($settingsManager === null) {
        $settingsManager = new SettingsManager($db);
    }
    
    return $settingsManager->get($key, $default);
}

function set_setting($key, $value, $data_type = 'string', $description = null) {
    global $db;
    static $settingsManager = null;
    
    if ($settingsManager === null) {
        $settingsManager = new SettingsManager($db);
    }
    
    return $settingsManager->set($key, $value, $data_type, $description);
}

function get_settings_group($group_prefix) {
    global $db;
    static $settingsManager = null;
    
    if ($settingsManager === null) {
        $settingsManager = new SettingsManager($db);
    }
    
    return $settingsManager->getGroup($group_prefix);
}

function get_site_settings() {
    return get_settings_group('site.');
}

function get_email_settings() {
    return get_settings_group('email.');
}

function update_site_settings($settings) {
    global $db;
    $settingsManager = new SettingsManager($db);
    return $settingsManager->updateSiteSettings($settings);
}

function update_email_settings($settings) {
    global $db;
    $settingsManager = new SettingsManager($db);
    return $settingsManager->updateEmailSettings($settings);
}

function install_default_settings() {
    global $db;
    $settingsManager = new SettingsManager($db);
    return DefaultSettings::installDefaults($settingsManager);
}

// ====================================================================
// INITIALIZATION
// ====================================================================

// Initialize settings manager
try {
    $settingsManager = new SettingsManager($db);
} catch (Exception $e) {
    error_log("Settings manager initialization failed: " . $e->getMessage());
}
?>