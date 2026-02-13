<?php
// ===================================================
// ANNOUNCEMENT FUNCTIONS - Christian Family Centre International
// ===================================================

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// ANNOUNCEMENT MANAGER
// ====================================================================

class AnnouncementManager {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Create new announcement
     */
    public function createAnnouncement($announcement_data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO announcements 
                (title, content, excerpt, author_id, category, priority,
                 start_date, end_date, is_pinned, status, target_audience,
                 notification_sent, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            return $stmt->execute([
                $announcement_data['title'] ?? '',
                $announcement_data['content'] ?? '',
                $announcement_data['excerpt'] ?? substr($announcement_data['content'] ?? '', 0, 200),
                $announcement_data['author_id'] ?? null,
                $announcement_data['category'] ?? 'general',
                $announcement_data['priority'] ?? 'normal',
                $announcement_data['start_date'] ?? date('Y-m-d'),
                $announcement_data['end_date'] ?? null,
                $announcement_data['is_pinned'] ?? 0,
                $announcement_data['status'] ?? 'draft',
                $announcement_data['target_audience'] ?? 'all',
                $announcement_data['notification_sent'] ?? 0
            ]);
            
        } catch (Exception $e) {
            error_log("Create announcement error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get announcement by ID
     */
    public function getAnnouncement($announcement_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       u.full_name as author_name,
                       u.profile_image as author_image
                FROM announcements a
                LEFT JOIN users u ON a.author_id = u.id
                WHERE a.id = ?
            ");
            
            $stmt->execute([$announcement_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get announcement error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all announcements with filters
     */
    public function getAnnouncements($filters = [], $limit = 20, $offset = 0) {
        try {
            $where = ["1=1"];
            $params = [];
            
            // Apply filters
            if (!isset($filters['include_drafts']) || !$filters['include_drafts']) {
                $where[] = "a.status = 'published'";
                $where[] = "(a.start_date IS NULL OR a.start_date <= CURDATE())";
                $where[] = "(a.end_date IS NULL OR a.end_date >= CURDATE())";
            }
            
            if (isset($filters['category'])) {
                $where[] = "a.category = ?";
                $params[] = $filters['category'];
            }
            
            if (isset($filters['priority'])) {
                $where[] = "a.priority = ?";
                $params[] = $filters['priority'];
            }
            
            if (isset($filters['author_id'])) {
                $where[] = "a.author_id = ?";
                $params[] = $filters['author_id'];
            }
            
            if (isset($filters['pinned'])) {
                $where[] = "a.is_pinned = ?";
                $params[] = $filters['pinned'];
            }
            
            if (isset($filters['search'])) {
                $where[] = "(a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
                $search = "%{$filters['search']}%";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            if (isset($filters['target_audience'])) {
                $where[] = "(a.target_audience = 'all' OR a.target_audience = ?)";
                $params[] = $filters['target_audience'];
            }
            
            $where_clause = implode(' AND ', $where);
            
            $stmt = $this->db->prepare("
                SELECT a.*,
                       u.full_name as author_name,
                       u.profile_image as author_image
                FROM announcements a
                LEFT JOIN users u ON a.author_id = u.id
                WHERE $where_clause
                ORDER BY 
                    a.is_pinned DESC,
                    a.priority DESC,
                    a.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get announcements error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update announcement
     */
    public function updateAnnouncement($announcement_id, $announcement_data) {
        try {
            $updates = [];
            $params = [];
            
            foreach ($announcement_data as $field => $value) {
                $updates[] = "$field = ?";
                $params[] = $value;
            }
            
            $params[] = $announcement_id;
            
            $stmt = $this->db->prepare("
                UPDATE announcements 
                SET " . implode(', ', $updates) . ", updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            error_log("Update announcement error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete announcement (soft delete)
     */
    public function deleteAnnouncement($announcement_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE announcements 
                SET status = 'deleted', updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([$announcement_id]);
            
        } catch (Exception $e) {
            error_log("Delete announcement error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get active announcements (for display)
     */
    public function getActiveAnnouncements($user_role = null, $limit = 10) {
        try {
            $where = [
                "a.status = 'published'",
                "(a.start_date IS NULL OR a.start_date <= CURDATE())",
                "(a.end_date IS NULL OR a.end_date >= CURDATE())"
            ];
            
            $params = [];
            
            // Filter by target audience
            if ($user_role) {
                $where[] = "(a.target_audience = 'all' OR a.target_audience = ? OR a.target_audience LIKE ?)";
                $params[] = $user_role;
                $params[] = "%$user_role%";
            } else {
                $where[] = "a.target_audience = 'all'";
            }
            
            $where_clause = implode(' AND ', $where);
            
            $stmt = $this->db->prepare("
                SELECT a.*,
                       u.full_name as author_name
                FROM announcements a
                LEFT JOIN users u ON a.author_id = u.id
                WHERE $where_clause
                ORDER BY 
                    a.is_pinned DESC,
                    a.priority DESC,
                    a.created_at DESC
                LIMIT ?
            ");
            
            $params[] = $limit;
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get active announcements error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get announcement categories
     */
    public function getAnnouncementCategories() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    category,
                    COUNT(*) as count
                FROM announcements 
                WHERE status = 'published'
                GROUP BY category 
                ORDER BY count DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get announcement categories error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Send announcement notifications
     */
    public function sendAnnouncementNotifications($announcement_id) {
        try {
            $announcement = $this->getAnnouncement($announcement_id);
            
            if (!$announcement || $announcement['notification_sent']) {
                return false;
            }
            
            // Determine recipients based on target audience
            $recipients = $this->getNotificationRecipients($announcement['target_audience']);
            
            if (empty($recipients)) {
                return false;
            }
            
            $emailManager = new EmailManager();
            $results = [];
            $notificationManager = new NotificationManager($this->db);
            
            foreach ($recipients as $recipient) {
                // Send email notification
                $email_result = $emailManager->sendTemplateEmail(
                    $recipient['email'],
                    'announcement_notification',
                    [
                        'subject' => 'New Announcement: ' . $announcement['title'],
                        'announcement_title' => $announcement['title'],
                        'announcement_excerpt' => $announcement['excerpt'],
                        'announcement_content' => $announcement['content'],
                        'author_name' => $announcement['author_name'],
                        'site_name' => SITE_NAME,
                        'announcement_url' => SITE_URL . '/announcements/view.php?id=' . $announcement_id
                    ]
                );
                
                // Create in-app notification
                if ($recipient['id']) {
                    $notificationManager->create(
                        $recipient['id'],
                        NotificationTypes::ANNOUNCEMENT,
                        $announcement['title'],
                        $announcement['excerpt'],
                        '/announcements/view.php?id=' . $announcement_id,
                        $announcement['priority']
                    );
                }
                
                $results[$recipient['email']] = $email_result;
            }
            
            // Mark notification as sent
            $this->updateAnnouncement($announcement_id, ['notification_sent' => 1]);
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Send announcement notifications error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notification recipients based on target audience
     */
    private function getNotificationRecipients($target_audience) {
        try {
            $recipients = [];
            
            if ($target_audience == 'all') {
                // Get all active users
                $stmt = $this->db->prepare("
                    SELECT id, email, full_name 
                    FROM users 
                    WHERE is_active = 1 
                    AND email_notifications = 1
                ");
                $stmt->execute();
                $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Get users with specific role
                $stmt = $this->db->prepare("
                    SELECT u.id, u.email, u.full_name 
                    FROM users u
                    JOIN user_roles ur ON u.id = ur.user_id
                    JOIN roles r ON ur.role_id = r.id
                    WHERE u.is_active = 1 
                    AND u.email_notifications = 1
                    AND (r.name = ? OR ? LIKE CONCAT('%', r.name, '%'))
                ");
                $stmt->execute([$target_audience, $target_audience]);
                $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $recipients;
            
        } catch (Exception $e) {
            error_log("Get notification recipients error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get announcement statistics
     */
    public function getAnnouncementStats($period = 'month') {
        try {
            $stats = [];
            
            switch ($period) {
                case 'week':
                    $interval = '7 DAY';
                    break;
                case 'month':
                    $interval = '1 MONTH';
                    break;
                case 'year':
                    $interval = '1 YEAR';
                    break;
                default:
                    $interval = '1 MONTH';
            }
            
            // Total announcements
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                    SUM(CASE WHEN is_pinned = 1 THEN 1 ELSE 0 END) as pinned
                FROM announcements 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
            ");
            $stats['totals'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Category distribution
            $stmt = $this->db->query("
                SELECT 
                    category,
                    COUNT(*) as count
                FROM announcements 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                AND status = 'published'
                GROUP BY category 
                ORDER BY count DESC
            ");
            $stats['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Author stats
            $stmt = $this->db->query("
                SELECT 
                    u.full_name,
                    COUNT(a.id) as announcement_count
                FROM announcements a
                LEFT JOIN users u ON a.author_id = u.id
                WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                AND a.status = 'published'
                GROUP BY a.author_id, u.full_name
                ORDER BY announcement_count DESC
                LIMIT 10
            ");
            $stats['top_authors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get announcement stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search announcements
     */
    public function searchAnnouncements($query, $limit = 20) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*,
                       u.full_name as author_name
                FROM announcements a
                LEFT JOIN users u ON a.author_id = u.id
                WHERE a.status = 'published'
                AND (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)
                ORDER BY a.created_at DESC
                LIMIT ?
            ");
            
            $search = "%$query%";
            $stmt->execute([$search, $search, $search, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Search announcements error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get announcements for calendar
     */
    public function getCalendarAnnouncements($month, $year) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    title,
                    start_date as start,
                    end_date as end,
                    'announcement' as type,
                    '#3b82f6' as color
                FROM announcements 
                WHERE status = 'published'
                AND MONTH(start_date) = ?
                AND YEAR(start_date) = ?
                AND start_date IS NOT NULL
                ORDER BY start_date
            ");
            
            $stmt->execute([$month, $year]);
            $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format for calendar
            foreach ($announcements as &$announcement) {
                $announcement['start'] = $announcement['start'] . 'T00:00:00';
                if ($announcement['end']) {
                    $announcement['end'] = date('Y-m-d', strtotime($announcement['end'] . ' +1 day')) . 'T00:00:00';
                }
            }
            
            return $announcements;
            
        } catch (Exception $e) {
            error_log("Get calendar announcements error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get expired announcements
     */
    public function getExpiredAnnouncements() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM announcements 
                WHERE status = 'published'
                AND end_date IS NOT NULL
                AND end_date < CURDATE()
                ORDER BY end_date DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get expired announcements error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Archive expired announcements
     */
    public function archiveExpiredAnnouncements() {
        try {
            $expired = $this->getExpiredAnnouncements();
            
            foreach ($expired as $announcement) {
                $this->updateAnnouncement($announcement['id'], ['status' => 'archived']);
            }
            
            return count($expired);
            
        } catch (Exception $e) {
            error_log("Archive expired announcements error: " . $e->getMessage());
            return 0;
        }
    }
}

// ====================================================================
// HELPER FUNCTIONS
// ====================================================================

function create_announcement($announcement_data) {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->createAnnouncement($announcement_data);
}

function get_announcement($announcement_id) {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->getAnnouncement($announcement_id);
}

function get_announcements($filters = [], $limit = 20, $offset = 0) {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->getAnnouncements($filters, $limit, $offset);
}

function get_active_announcements($user_role = null, $limit = 10) {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->getActiveAnnouncements($user_role, $limit);
}

function update_announcement($announcement_id, $announcement_data) {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->updateAnnouncement($announcement_id, $announcement_data);
}

function delete_announcement($announcement_id) {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->deleteAnnouncement($announcement_id);
}

function send_announcement_notifications($announcement_id) {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->sendAnnouncementNotifications($announcement_id);
}

function get_announcement_categories() {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->getAnnouncementCategories();
}

function get_announcement_stats($period = 'month') {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->getAnnouncementStats($period);
}

function search_announcements($query, $limit = 20) {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->searchAnnouncements($query, $limit);
}

function archive_expired_announcements() {
    global $db;
    $announcementManager = new AnnouncementManager($db);
    return $announcementManager->archiveExpiredAnnouncements();
}
?>