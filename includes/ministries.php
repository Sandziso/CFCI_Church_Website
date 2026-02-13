<?php
// ===================================================
// MINISTRY FUNCTIONS - Christian Family Centre International
// ===================================================

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// MINISTRY MANAGER
// ====================================================================

class MinistryManager {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Create new ministry
     */
    public function createMinistry($ministry_data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ministries 
                (name, description, vision, mission, leader_id, co_leader_id,
                 category, meeting_day, meeting_time, meeting_frequency,
                 meeting_location, contact_person, contact_email, contact_phone,
                 image, banner_image, status, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $ministry_data['name'] ?? '',
                $ministry_data['description'] ?? '',
                $ministry_data['vision'] ?? '',
                $ministry_data['mission'] ?? '',
                $ministry_data['leader_id'] ?? null,
                $ministry_data['co_leader_id'] ?? null,
                $ministry_data['category'] ?? 'general',
                $ministry_data['meeting_day'] ?? null,
                $ministry_data['meeting_time'] ?? null,
                $ministry_data['meeting_frequency'] ?? 'weekly',
                $ministry_data['meeting_location'] ?? '',
                $ministry_data['contact_person'] ?? '',
                $ministry_data['contact_email'] ?? '',
                $ministry_data['contact_phone'] ?? '',
                $ministry_data['image'] ?? null,
                $ministry_data['banner_image'] ?? null,
                $ministry_data['status'] ?? 'active',
                $ministry_data['created_by'] ?? null
            ]);
            
        } catch (Exception $e) {
            error_log("Create ministry error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get ministry by ID
     */
    public function getMinistry($ministry_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*,
                       leader.full_name as leader_name,
                       leader.email as leader_email,
                       co_leader.full_name as co_leader_name,
                       creator.full_name as creator_name,
                       COUNT(mm.id) as member_count
                FROM ministries m
                LEFT JOIN users leader ON m.leader_id = leader.id
                LEFT JOIN users co_leader ON m.co_leader_id = co_leader.id
                LEFT JOIN users creator ON m.created_by = creator.id
                LEFT JOIN ministry_members mm ON m.id = mm.ministry_id AND mm.status = 'active'
                WHERE m.id = ?
                GROUP BY m.id
            ");
            
            $stmt->execute([$ministry_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get ministry error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all ministries with filters
     */
    public function getMinistries($filters = [], $limit = 20, $offset = 0) {
        try {
            $where = ["m.status = 'active'"];
            $params = [];
            
            // Apply filters
            if (isset($filters['category'])) {
                $where[] = "m.category = ?";
                $params[] = $filters['category'];
            }
            
            if (isset($filters['leader_id'])) {
                $where[] = "(m.leader_id = ? OR m.co_leader_id = ?)";
                $params[] = $filters['leader_id'];
                $params[] = $filters['leader_id'];
            }
            
            if (isset($filters['search'])) {
                $where[] = "(m.name LIKE ? OR m.description LIKE ? OR m.vision LIKE ? OR m.mission LIKE ?)";
                $search = "%{$filters['search']}%";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            $where_clause = implode(' AND ', $where);
            
            $stmt = $this->db->prepare("
                SELECT m.*,
                       leader.full_name as leader_name,
                       co_leader.full_name as co_leader_name,
                       COUNT(mm.id) as member_count
                FROM ministries m
                LEFT JOIN users leader ON m.leader_id = leader.id
                LEFT JOIN users co_leader ON m.co_leader_id = co_leader.id
                LEFT JOIN ministry_members mm ON m.id = mm.ministry_id AND mm.status = 'active'
                WHERE $where_clause
                GROUP BY m.id
                ORDER BY m.name ASC
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get ministries error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update ministry
     */
    public function updateMinistry($ministry_id, $ministry_data) {
        try {
            $updates = [];
            $params = [];
            
            foreach ($ministry_data as $field => $value) {
                $updates[] = "$field = ?";
                $params[] = $value;
            }
            
            $params[] = $ministry_id;
            
            $stmt = $this->db->prepare("
                UPDATE ministries 
                SET " . implode(', ', $updates) . ", updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            error_log("Update ministry error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete ministry (soft delete)
     */
    public function deleteMinistry($ministry_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE ministries 
                SET status = 'inactive', updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([$ministry_id]);
            
        } catch (Exception $e) {
            error_log("Delete ministry error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Join ministry as member
     */
    public function joinMinistry($ministry_id, $user_id, $role = 'member', $notes = '') {
        try {
            // Check if already a member
            $check_stmt = $this->db->prepare("
                SELECT id FROM ministry_members 
                WHERE ministry_id = ? AND user_id = ?
            ");
            $check_stmt->execute([$ministry_id, $user_id]);
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Already a member of this ministry'];
            }
            
            // Check if ministry is full
            $ministry = $this->getMinistry($ministry_id);
            if ($ministry['max_members'] && $ministry['member_count'] >= $ministry['max_members']) {
                return ['success' => false, 'message' => 'Ministry is full'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO ministry_members 
                (ministry_id, user_id, role, join_date, status, notes, created_at)
                VALUES (?, ?, ?, NOW(), 'pending', ?, NOW())
            ");
            
            $result = $stmt->execute([$ministry_id, $user_id, $role, $notes]);
            
            if ($result) {
                // Notify ministry leaders
                $this->notifyMinistryLeaders($ministry_id, $user_id);
                
                return ['success' => true, 'message' => 'Membership request submitted'];
            }
            
            return ['success' => false, 'message' => 'Failed to join ministry'];
            
        } catch (Exception $e) {
            error_log("Join ministry error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error joining ministry'];
        }
    }
    
    /**
     * Approve ministry membership
     */
    public function approveMembership($membership_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE ministry_members 
                SET status = 'active', approved_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([$membership_id]);
            
        } catch (Exception $e) {
            error_log("Approve membership error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reject ministry membership
     */
    public function rejectMembership($membership_id, $reason = '') {
        try {
            $stmt = $this->db->prepare("
                UPDATE ministry_members 
                SET status = 'rejected', rejection_reason = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([$reason, $membership_id]);
            
        } catch (Exception $e) {
            error_log("Reject membership error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get ministry members
     */
    public function getMinistryMembers($ministry_id, $status = 'active') {
        try {
            $stmt = $this->db->prepare("
                SELECT mm.*, u.full_name, u.email, u.phone, u.profile_image
                FROM ministry_members mm
                JOIN users u ON mm.user_id = u.id
                WHERE mm.ministry_id = ? AND mm.status = ?
                ORDER BY mm.role, u.full_name
            ");
            
            $stmt->execute([$ministry_id, $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get ministry members error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user's ministries
     */
    public function getUserMinistries($user_id, $status = 'active') {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, mm.role, mm.join_date, mm.status as membership_status
                FROM ministries m
                JOIN ministry_members mm ON m.id = mm.ministry_id
                WHERE mm.user_id = ? AND mm.status = ?
                AND m.status = 'active'
                ORDER BY m.name
            ");
            
            $stmt->execute([$user_id, $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get user ministries error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get ministry categories
     */
    public function getMinistryCategories() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    category,
                    COUNT(*) as count,
                    GROUP_CONCAT(DISTINCT name ORDER BY name) as ministry_names
                FROM ministries 
                WHERE status = 'active'
                GROUP BY category 
                ORDER BY category
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get ministry categories error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get ministry statistics
     */
    public function getMinistryStats() {
        try {
            $stats = [];
            
            // Total ministries
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM ministries WHERE status = 'active'");
            $stats['total_ministries'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total ministry members
            $stmt = $this->db->query("
                SELECT COUNT(DISTINCT user_id) as total 
                FROM ministry_members 
                WHERE status = 'active'
            ");
            $stats['total_members'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Average members per ministry
            $stmt = $this->db->query("
                SELECT 
                    AVG(member_count) as average,
                    MAX(member_count) as max,
                    MIN(member_count) as min
                FROM (
                    SELECT COUNT(*) as member_count
                    FROM ministry_members 
                    WHERE status = 'active'
                    GROUP BY ministry_id
                ) as counts
            ");
            $stats['members_stats'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Ministry growth over time
            $stmt = $this->db->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as new_ministries
                FROM ministries 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ");
            $stats['growth'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get ministry stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get upcoming ministry events
     */
    public function getUpcomingEvents($ministry_id, $limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*
                FROM events e
                WHERE e.ministry_id = ?
                AND e.event_date >= CURDATE()
                AND e.status = 'active'
                ORDER BY e.event_date ASC
                LIMIT ?
            ");
            
            $stmt->execute([$ministry_id, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get upcoming ministry events error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add ministry event
     */
    public function addMinistryEvent($ministry_id, $event_data) {
        try {
            $event_data['ministry_id'] = $ministry_id;
            
            $eventManager = new EventManager($this->db);
            return $eventManager->createEvent($event_data);
            
        } catch (Exception $e) {
            error_log("Add ministry event error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Notify ministry leaders about new membership request
     */
    private function notifyMinistryLeaders($ministry_id, $user_id) {
        try {
            $ministry = $this->getMinistry($ministry_id);
            
            if (!$ministry) {
                return false;
            }
            
            $stmt = $this->db->prepare("SELECT full_name, email FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return false;
            }
            
            $emailManager = new EmailManager();
            $recipients = [];
            
            // Notify leader
            if ($ministry['leader_email']) {
                $emailManager->sendTemplateEmail(
                    $ministry['leader_email'],
                    'ministry_membership_request',
                    [
                        'subject' => 'New Ministry Membership Request - ' . $ministry['name'],
                        'ministry_name' => $ministry['name'],
                        'applicant_name' => $user['full_name'],
                        'applicant_email' => $user['email'],
                        'site_name' => SITE_NAME,
                        'approval_url' => SITE_URL . '/ministries/members.php?id=' . $ministry_id
                    ]
                );
            }
            
            // Notify co-leader
            if ($ministry['co_leader_id']) {
                $co_leader_stmt = $this->db->prepare("SELECT email FROM users WHERE id = ?");
                $co_leader_stmt->execute([$ministry['co_leader_id']]);
                $co_leader = $co_leader_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($co_leader && $co_leader['email']) {
                    $emailManager->sendTemplateEmail(
                        $co_leader['email'],
                        'ministry_membership_request',
                        [
                            'subject' => 'New Ministry Membership Request - ' . $ministry['name'],
                            'ministry_name' => $ministry['name'],
                            'applicant_name' => $user['full_name'],
                            'applicant_email' => $user['email'],
                            'site_name' => SITE_NAME,
                            'approval_url' => SITE_URL . '/ministries/members.php?id=' . $ministry_id
                        ]
                    );
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Notify ministry leaders error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get ministry by category
     */
    public function getMinistriesByCategory($category) {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*,
                       leader.full_name as leader_name,
                       COUNT(mm.id) as member_count
                FROM ministries m
                LEFT JOIN users leader ON m.leader_id = leader.id
                LEFT JOIN ministry_members mm ON m.id = mm.ministry_id AND mm.status = 'active'
                WHERE m.category = ? AND m.status = 'active'
                GROUP BY m.id
                ORDER BY m.name
            ");
            
            $stmt->execute([$category]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get ministries by category error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search ministries
     */
    public function searchMinistries($query, $limit = 20) {
        try {
            return $this->getMinistries(['search' => $query], $limit, 0);
            
        } catch (Exception $e) {
            error_log("Search ministries error: " . $e->getMessage());
            return [];
        }
    }
}

// ====================================================================
// HELPER FUNCTIONS
// ====================================================================

function create_ministry($ministry_data) {
    global $db;
    $ministryManager = new MinistryManager($db);
    return $ministryManager->createMinistry($ministry_data);
}

function get_ministry($ministry_id) {
    global $db;
    $ministryManager = new MinistryManager($db);
    return $ministryManager->getMinistry($ministry_id);
}

function get_ministries($filters = [], $limit = 20, $offset = 0) {
    global $db;
    $ministryManager = new MinistryManager($db);
    return $ministryManager->getMinistries($filters, $limit, $offset);
}

function update_ministry($ministry_id, $ministry_data) {
    global $db;
    $ministryManager = new MinistryManager($db);
    return $ministryManager->updateMinistry($ministry_id, $ministry_data);
}

function join_ministry($ministry_id, $user_id, $role = 'member', $notes = '') {
    global $db;
    $ministryManager = new MinistryManager($db);
    return $ministryManager->joinMinistry($ministry_id, $user_id, $role, $notes);
}

function get_ministry_members($ministry_id, $status = 'active') {
    global $db;
    $ministryManager = new MinistryManager($db);
    return $ministryManager->getMinistryMembers($ministry_id, $status);
}

function get_user_ministries($user_id, $status = 'active') {
    global $db;
    $ministryManager = new MinistryManager($db);
    return $ministryManager->getUserMinistries($user_id, $status);
}

function get_ministry_categories() {
    global $db;
    $ministryManager = new MinistryManager($db);
    return $ministryManager->getMinistryCategories();
}

function get_ministry_stats() {
    global $db;
    $ministryManager = new MinistryManager($db);
    return $ministryManager->getMinistryStats();
}

function get_upcoming_ministry_events($ministry_id, $limit = 5) {
    global $db;
    $ministryManager = new MinistryManager($db);
    return $ministryManager->getUpcomingEvents($ministry_id, $limit);
}
?>