<?php
// ===================================================
// DASHBOARD FUNCTIONS - Christian Family Centre International
// ===================================================

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// DASHBOARD STATISTICS
// ====================================================================

class DashboardStats {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Get general statistics for admin dashboard
     */
    public function getAdminStats($period = 'month') {
        $stats = [];
        
        try {
            // Total users
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
            $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total events
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM events WHERE status = 'active'");
            $stats['total_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total prayer requests
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM prayer_requests WHERE status = 'active'");
            $stats['total_prayer_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total donations
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM donations WHERE status = 'completed'");
            $stats['total_donations'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total donations amount
            $stmt = $this->db->query("SELECT SUM(amount) as total FROM donations WHERE status = 'completed'");
            $stats['total_donation_amount'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // New users this month
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM users 
                WHERE is_active = 1 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ");
            $stats['new_users_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Recent activities
            $stats['recent_activities'] = $this->getRecentActivities(10);
            
            // Upcoming events
            $stats['upcoming_events'] = $this->getUpcomingEvents(5);
            
            // Recent donations
            $stats['recent_donations'] = $this->getRecentDonations(5);
            
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get statistics for pastor dashboard
     */
    public function getPastorStats($pastor_id = null) {
        $stats = [];
        
        try {
            // Get pastor's ministry members
            if ($pastor_id) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total 
                    FROM ministry_members mm
                    JOIN ministries m ON mm.ministry_id = m.id
                    WHERE m.leader_id = ?
                ");
                $stmt->execute([$pastor_id]);
                $stats['ministry_members'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            }
            
            // Prayer requests needing attention
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM prayer_requests 
                WHERE status = 'pending' 
                AND prayer_type = 'public'
            ");
            $stats['pending_prayer_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Upcoming events
            $stats['upcoming_events'] = $this->getUpcomingEvents(10);
            
            // Recent baptisms
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM member_records 
                WHERE record_type = 'baptism' 
                AND record_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ");
            $stats['recent_baptisms'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Pastor stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get statistics for member dashboard
     */
    public function getMemberStats($member_id) {
        $stats = [];
        
        try {
            // Member's upcoming events
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM event_registrations er
                JOIN events e ON er.event_id = e.id
                WHERE er.user_id = ? 
                AND e.event_date >= CURDATE()
            ");
            $stmt->execute([$member_id]);
            $stats['upcoming_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Member's prayer requests
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM prayer_requests 
                WHERE user_id = ? 
                AND status = 'active'
            ");
            $stmt->execute([$member_id]);
            $stats['my_prayer_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Member's donations
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total, SUM(amount) as total_amount 
                FROM donations 
                WHERE user_id = ? 
                AND status = 'completed'
            ");
            $stmt->execute([$member_id]);
            $donation = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_donations'] = $donation['total'] ?? 0;
            $stats['donation_amount'] = $donation['total_amount'] ?? 0;
            
            // Member's ministries
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM ministry_members 
                WHERE user_id = ?
            ");
            $stmt->execute([$member_id]);
            $stats['ministries'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Member stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities($limit = 10) {
        try {
            // This would normally query an activity log table
            // For now, combine data from multiple tables
            $activities = [];
            
            // Recent users
            $stmt = $this->db->query("
                SELECT 
                    'user_registration' as type,
                    full_name as title,
                    email as description,
                    created_at as date
                FROM users 
                WHERE is_active = 1 
                ORDER BY created_at DESC 
                LIMIT 3
            ");
            $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Recent events
            $stmt = $this->db->query("
                SELECT 
                    'event_created' as type,
                    title,
                    description,
                    created_at as date
                FROM events 
                ORDER BY created_at DESC 
                LIMIT 3
            ");
            $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Sort by date
            usort($activities, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            return array_slice($activities, 0, $limit);
            
        } catch (Exception $e) {
            error_log("Recent activities error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get upcoming events
     */
    private function getUpcomingEvents($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM events 
                WHERE event_date >= CURDATE() 
                AND status = 'active'
                ORDER BY event_date ASC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Upcoming events error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent donations
     */
    private function getRecentDonations($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT d.*, u.full_name 
                FROM donations d
                JOIN users u ON d.user_id = u.id
                WHERE d.status = 'completed'
                ORDER BY d.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Recent donations error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get growth statistics
     */
    public function getGrowthStats($period = 'year') {
        $stats = [];
        
        try {
            switch ($period) {
                case 'week':
                    $interval = '7 DAY';
                    $format = '%Y-%m-%d';
                    break;
                case 'month':
                    $interval = '1 MONTH';
                    $format = '%Y-%m-%d';
                    break;
                default:
                    $interval = '1 YEAR';
                    $format = '%Y-%m';
            }
            
            // User growth
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, ?) as period,
                    COUNT(*) as count
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                GROUP BY period
                ORDER BY period
            ");
            $stmt->execute([$format]);
            $stats['user_growth'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Donation growth
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, ?) as period,
                    COUNT(*) as count,
                    SUM(amount) as total
                FROM donations 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                AND status = 'completed'
                GROUP BY period
                ORDER BY period
            ");
            $stmt->execute([$format]);
            $stats['donation_growth'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Growth stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
}

// ====================================================================
// DASHBOARD WIDGETS
// ====================================================================

class DashboardWidgets {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Get quick stats widget
     */
    public function getQuickStats($user_id, $user_role) {
        $stats = new DashboardStats($this->db);
        
        switch ($user_role) {
            case 'admin':
                return $stats->getAdminStats();
            case 'pastor':
                return $stats->getPastorStats($user_id);
            default:
                return $stats->getMemberStats($user_id);
        }
    }
    
    /**
     * Get calendar widget data
     */
    public function getCalendarEvents($month = null, $year = null) {
        try {
            $month = $month ?? date('m');
            $year = $year ?? date('Y');
            
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    title,
                    event_date as start,
                    event_time,
                    location,
                    color
                FROM events 
                WHERE 
                    MONTH(event_date) = ? 
                    AND YEAR(event_date) = ?
                    AND status = 'active'
                ORDER BY event_date
            ");
            $stmt->execute([$month, $year]);
            
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format for calendar
            foreach ($events as &$event) {
                $event['start'] = $event['start'] . 'T' . ($event['event_time'] ?? '00:00:00');
                unset($event['event_time']);
            }
            
            return $events;
            
        } catch (Exception $e) {
            error_log("Calendar events error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get prayer wall widget
     */
    public function getPrayerWallWidget($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    pr.*,
                    u.full_name,
                    CASE 
                        WHEN pr.is_anonymous = 1 THEN 'Anonymous'
                        ELSE u.full_name
                    END as display_name
                FROM prayer_requests pr
                LEFT JOIN users u ON pr.user_id = u.id
                WHERE pr.status = 'active'
                AND pr.prayer_type = 'public'
                ORDER BY pr.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Prayer wall widget error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get announcements widget
     */
    public function getAnnouncementsWidget($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM announcements 
                WHERE status = 'active'
                AND (expiry_date IS NULL OR expiry_date >= CURDATE())
                ORDER BY publish_date DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Announcements widget error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get birthday widget
     */
    public function getBirthdaysWidget($month = null) {
        try {
            $month = $month ?? date('m');
            
            $stmt = $this->db->prepare("
                SELECT 
                    u.full_name,
                    up.birth_date,
                    DATEDIFF(
                        DATE_ADD(up.birth_date, INTERVAL YEAR(CURDATE()) - YEAR(up.birth_date) YEAR),
                        CURDATE()
                    ) as days_until
                FROM users u
                JOIN user_profiles up ON u.id = up.user_id
                WHERE 
                    MONTH(up.birth_date) = ?
                    AND u.is_active = 1
                ORDER BY DAY(up.birth_date)
            ");
            $stmt->execute([$month]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Birthdays widget error: " . $e->getMessage());
            return [];
        }
    }
}

// ====================================================================
// HELPER FUNCTIONS
// ====================================================================

function get_dashboard_stats($user_id = null, $user_role = null) {
    global $db;
    
    if (!$user_id && SessionManager::isAuthenticated()) {
        $user_id = SessionManager::getUserId();
        $user_role = SessionManager::getUserRole();
    }
    
    $widgets = new DashboardWidgets($db);
    return $widgets->getQuickStats($user_id, $user_role);
}

function get_calendar_events($month = null, $year = null) {
    global $db;
    $widgets = new DashboardWidgets($db);
    return $widgets->getCalendarEvents($month, $year);
}

function get_prayer_wall_widget($limit = 5) {
    global $db;
    $widgets = new DashboardWidgets($db);
    return $widgets->getPrayerWallWidget($limit);
}

function get_announcements_widget($limit = 5) {
    global $db;
    $widgets = new DashboardWidgets($db);
    return $widgets->getAnnouncementsWidget($limit);
}

function get_birthdays_widget($month = null) {
    global $db;
    $widgets = new DashboardWidgets($db);
    return $widgets->getBirthdaysWidget($month);
}

function get_growth_stats($period = 'year') {
    global $db;
    $stats = new DashboardStats($db);
    return $stats->getGrowthStats($period);
}
?>