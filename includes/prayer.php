<?php
// ===================================================
// PRAYER REQUEST FUNCTIONS - Christian Family Centre International
// ===================================================

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// PRAYER MANAGER
// ====================================================================

class PrayerManager {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Submit prayer request
     */
    public function submitPrayerRequest($prayer_data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO prayer_requests 
                (user_id, title, description, category, prayer_type, 
                 is_anonymous, status, is_urgent, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            return $stmt->execute([
                $prayer_data['user_id'] ?? null,
                $prayer_data['title'] ?? '',
                $prayer_data['description'] ?? '',
                $prayer_data['category'] ?? PRAYER_OTHER,
                $prayer_data['prayer_type'] ?? 'public',
                $prayer_data['is_anonymous'] ?? 0,
                $prayer_data['status'] ?? 'pending',
                $prayer_data['is_urgent'] ?? 0
            ]);
            
        } catch (Exception $e) {
            error_log("Submit prayer request error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get prayer request by ID
     */
    public function getPrayerRequest($prayer_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT pr.*, 
                       u.full_name,
                       u.email,
                       CASE 
                           WHEN pr.is_anonymous = 1 THEN 'Anonymous'
                           ELSE u.full_name
                       END as display_name
                FROM prayer_requests pr
                LEFT JOIN users u ON pr.user_id = u.id
                WHERE pr.id = ?
            ");
            
            $stmt->execute([$prayer_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get prayer request error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all prayer requests with filters
     */
    public function getPrayerRequests($filters = [], $limit = 20, $offset = 0) {
        try {
            $where = ["pr.status != 'deleted'"];
            $params = [];
            
            // Apply filters
            if (isset($filters['category'])) {
                $where[] = "pr.category = ?";
                $params[] = $filters['category'];
            }
            
            if (isset($filters['status'])) {
                $where[] = "pr.status = ?";
                $params[] = $filters['status'];
            }
            
            if (isset($filters['prayer_type'])) {
                $where[] = "pr.prayer_type = ?";
                $params[] = $filters['prayer_type'];
            }
            
            if (isset($filters['user_id'])) {
                $where[] = "pr.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (isset($filters['urgent']) && $filters['urgent']) {
                $where[] = "pr.is_urgent = 1";
            }
            
            if (isset($filters['public']) && $filters['public']) {
                $where[] = "pr.prayer_type = 'public'";
            }
            
            if (isset($filters['search'])) {
                $where[] = "(pr.title LIKE ? OR pr.description LIKE ?)";
                $search = "%{$filters['search']}%";
                $params[] = $search;
                $params[] = $search;
            }
            
            $where_clause = implode(' AND ', $where);
            
            $stmt = $this->db->prepare("
                SELECT pr.*,
                       u.full_name,
                       u.email,
                       CASE 
                           WHEN pr.is_anonymous = 1 THEN 'Anonymous'
                           ELSE u.full_name
                       END as display_name,
                       COUNT(prp.id) as prayer_count
                FROM prayer_requests pr
                LEFT JOIN users u ON pr.user_id = u.id
                LEFT JOIN prayer_request_prayers prp ON pr.id = prp.prayer_request_id
                WHERE $where_clause
                GROUP BY pr.id
                ORDER BY 
                    pr.is_urgent DESC,
                    pr.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get prayer requests error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update prayer request status
     */
    public function updatePrayerStatus($prayer_id, $status, $response = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE prayer_requests 
                SET status = ?, 
                    response = ?,
                    responded_at = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([$status, $response, $prayer_id]);
            
        } catch (Exception $e) {
            error_log("Update prayer status error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record prayer for a request
     */
    public function recordPrayer($prayer_request_id, $user_id) {
        try {
            // Check if already prayed today
            $check_stmt = $this->db->prepare("
                SELECT id FROM prayer_request_prayers 
                WHERE prayer_request_id = ? 
                AND user_id = ? 
                AND DATE(prayed_at) = CURDATE()
            ");
            
            $check_stmt->execute([$prayer_request_id, $user_id]);
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Already prayed for this request today'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO prayer_request_prayers 
                (prayer_request_id, user_id, prayed_at)
                VALUES (?, ?, NOW())
            ");
            
            $result = $stmt->execute([$prayer_request_id, $user_id]);
            
            if ($result) {
                // Update prayer count
                $update_stmt = $this->db->prepare("
                    UPDATE prayer_requests 
                    SET prayer_count = prayer_count + 1 
                    WHERE id = ?
                ");
                $update_stmt->execute([$prayer_request_id]);
                
                return ['success' => true, 'message' => 'Prayer recorded'];
            }
            
            return ['success' => false, 'message' => 'Failed to record prayer'];
            
        } catch (Exception $e) {
            error_log("Record prayer error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error recording prayer'];
        }
    }
    
    /**
     * Get prayer statistics
     */
    public function getPrayerStats($user_id = null) {
        try {
            $stats = [];
            
            // Total prayer requests
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM prayer_requests WHERE status = 'active'");
            $stats['total_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Answered prayers
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM prayer_requests WHERE status = 'answered'");
            $stats['answered_prayers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Urgent prayers
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM prayer_requests WHERE is_urgent = 1 AND status = 'active'");
            $stats['urgent_prayers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // User-specific stats
            if ($user_id) {
                // User's prayer requests
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total 
                    FROM prayer_requests 
                    WHERE user_id = ? AND status = 'active'
                ");
                $stmt->execute([$user_id]);
                $stats['my_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                
                // User's answered prayers
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total 
                    FROM prayer_requests 
                    WHERE user_id = ? AND status = 'answered'
                ");
                $stmt->execute([$user_id]);
                $stats['my_answered'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                
                // User's prayer count
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total 
                    FROM prayer_request_prayers 
                    WHERE user_id = ?
                ");
                $stmt->execute([$user_id]);
                $stats['my_prayers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get prayer stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get prayer wall (public prayers)
     */
    public function getPrayerWall($limit = 20, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT pr.*,
                       u.full_name,
                       CASE 
                           WHEN pr.is_anonymous = 1 THEN 'Anonymous'
                           ELSE u.full_name
                       END as display_name,
                       COUNT(prp.id) as prayer_count
                FROM prayer_requests pr
                LEFT JOIN users u ON pr.user_id = u.id
                LEFT JOIN prayer_request_prayers prp ON pr.id = prp.prayer_request_id
                WHERE pr.status = 'active'
                AND pr.prayer_type = 'public'
                GROUP BY pr.id
                ORDER BY 
                    pr.is_urgent DESC,
                    pr.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get prayer wall error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get prayer categories distribution
     */
    public function getPrayerCategories() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    category,
                    COUNT(*) as count,
                    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM prayer_requests WHERE status = 'active'), 2) as percentage
                FROM prayer_requests 
                WHERE status = 'active'
                GROUP BY category 
                ORDER BY count DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get prayer categories error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add testimonial for answered prayer
     */
    public function addTestimonial($prayer_id, $testimonial) {
        try {
            $stmt = $this->db->prepare("
                UPDATE prayer_requests 
                SET testimonial = ?,
                    status = 'answered',
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([$testimonial, $prayer_id]);
            
        } catch (Exception $e) {
            error_log("Add testimonial error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get answered prayer testimonies
     */
    public function getTestimonies($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT pr.*,
                       u.full_name,
                       CASE 
                           WHEN pr.is_anonymous = 1 THEN 'Anonymous'
                           ELSE u.full_name
                       END as display_name
                FROM prayer_requests pr
                LEFT JOIN users u ON pr.user_id = u.id
                WHERE pr.status = 'answered'
                AND pr.testimonial IS NOT NULL
                ORDER BY pr.updated_at DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get testimonies error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Send prayer request notification to prayer team
     */
    public function notifyPrayerTeam($prayer_request_id) {
        try {
            $prayer_request = $this->getPrayerRequest($prayer_request_id);
            
            if (!$prayer_request) {
                return false;
            }
            
            // Get prayer team members
            $stmt = $this->db->prepare("
                SELECT u.email, u.full_name 
                FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                WHERE ur.role_id IN (SELECT id FROM roles WHERE name IN ('prayer_team', 'pastor', 'elder'))
                AND u.is_active = 1
            ");
            
            $stmt->execute();
            $prayer_team = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($prayer_team)) {
                return false;
            }
            
            $emailManager = new EmailManager();
            $results = [];
            
            foreach ($prayer_team as $member) {
                $results[$member['email']] = $emailManager->sendTemplateEmail(
                    $member['email'],
                    'prayer_request_notification',
                    [
                        'subject' => 'New Prayer Request - ' . SITE_NAME,
                        'prayer_title' => $prayer_request['title'],
                        'prayer_description' => $prayer_request['description'],
                        'category' => $prayer_request['category'],
                        'display_name' => $prayer_request['display_name'],
                        'is_urgent' => $prayer_request['is_urgent'],
                        'site_name' => SITE_NAME,
                        'prayer_url' => SITE_URL . '/prayer/view.php?id=' . $prayer_request_id
                    ]
                );
            }
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Notify prayer team error: " . $e->getMessage());
            return false;
        }
    }
}

// ====================================================================
// HELPER FUNCTIONS
// ====================================================================

function submit_prayer_request($prayer_data) {
    global $db;
    $prayerManager = new PrayerManager($db);
    return $prayerManager->submitPrayerRequest($prayer_data);
}

function get_prayer_request($prayer_id) {
    global $db;
    $prayerManager = new PrayerManager($db);
    return $prayerManager->getPrayerRequest($prayer_id);
}

function get_prayer_requests($filters = [], $limit = 20, $offset = 0) {
    global $db;
    $prayerManager = new PrayerManager($db);
    return $prayerManager->getPrayerRequests($filters, $limit, $offset);
}

function get_prayer_wall($limit = 20, $offset = 0) {
    global $db;
    $prayerManager = new PrayerManager($db);
    return $prayerManager->getPrayerWall($limit, $offset);
}

function record_prayer($prayer_request_id, $user_id) {
    global $db;
    $prayerManager = new PrayerManager($db);
    return $prayerManager->recordPrayer($prayer_request_id, $user_id);
}

function get_prayer_stats($user_id = null) {
    global $db;
    $prayerManager = new PrayerManager($db);
    return $prayerManager->getPrayerStats($user_id);
}

function get_prayer_categories() {
    global $db;
    $prayerManager = new PrayerManager($db);
    return $prayerManager->getPrayerCategories();
}

function get_testimonies($limit = 10) {
    global $db;
    $prayerManager = new PrayerManager($db);
    return $prayerManager->getTestimonies($limit);
}

function update_prayer_status($prayer_id, $status, $response = null) {
    global $db;
    $prayerManager = new PrayerManager($db);
    return $prayerManager->updatePrayerStatus($prayer_id, $status, $response);
}

function notify_prayer_team($prayer_request_id) {
    global $db;
    $prayerManager = new PrayerManager($db);
    return $prayerManager->notifyPrayerTeam($prayer_request_id);
}
?>