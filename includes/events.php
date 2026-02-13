<?php
// ===================================================
// EVENT MANAGEMENT FUNCTIONS - Christian Family Centre International
// ===================================================

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// EVENT MANAGER
// ====================================================================

class EventManager {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Create new event
     */
    public function createEvent($event_data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO events 
                (title, description, event_date, event_time, end_date, end_time, 
                 location, venue, organizer, category, type, status, 
                 max_attendees, registration_required, registration_deadline,
                 cost, image, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $event_data['title'] ?? '',
                $event_data['description'] ?? '',
                $event_data['event_date'] ?? null,
                $event_data['event_time'] ?? null,
                $event_data['end_date'] ?? null,
                $event_data['end_time'] ?? null,
                $event_data['location'] ?? '',
                $event_data['venue'] ?? '',
                $event_data['organizer'] ?? '',
                $event_data['category'] ?? 'general',
                $event_data['type'] ?? 'regular',
                $event_data['status'] ?? 'draft',
                $event_data['max_attendees'] ?? 0,
                $event_data['registration_required'] ?? 0,
                $event_data['registration_deadline'] ?? null,
                $event_data['cost'] ?? 0,
                $event_data['image'] ?? null,
                $event_data['created_by'] ?? null
            ]);
            
        } catch (Exception $e) {
            error_log("Create event error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update event
     */
    public function updateEvent($event_id, $event_data) {
        try {
            $updates = [];
            $params = [];
            
            foreach ($event_data as $field => $value) {
                $updates[] = "$field = ?";
                $params[] = $value;
            }
            
            $params[] = $event_id;
            
            $stmt = $this->db->prepare("
                UPDATE events 
                SET " . implode(', ', $updates) . ", updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            error_log("Update event error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get event by ID
     */
    public function getEvent($event_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, 
                       u.full_name as creator_name,
                       COUNT(er.id) as registered_count
                FROM events e
                LEFT JOIN users u ON e.created_by = u.id
                LEFT JOIN event_registrations er ON e.id = er.event_id AND er.status = 'confirmed'
                WHERE e.id = ?
                GROUP BY e.id
            ");
            
            $stmt->execute([$event_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get event error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all events with filters
     */
    public function getEvents($filters = [], $limit = 20, $offset = 0) {
        try {
            $where = ["status != 'deleted'"];
            $params = [];
            
            // Apply filters
            if (isset($filters['category'])) {
                $where[] = "category = ?";
                $params[] = $filters['category'];
            }
            
            if (isset($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            
            if (isset($filters['upcoming']) && $filters['upcoming']) {
                $where[] = "event_date >= CURDATE()";
            }
            
            if (isset($filters['past']) && $filters['past']) {
                $where[] = "event_date < CURDATE()";
            }
            
            if (isset($filters['search'])) {
                $where[] = "(title LIKE ? OR description LIKE ? OR location LIKE ?)";
                $search = "%{$filters['search']}%";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            $where_clause = implode(' AND ', $where);
            
            $stmt = $this->db->prepare("
                SELECT e.*, 
                       u.full_name as creator_name,
                       COUNT(er.id) as registered_count
                FROM events e
                LEFT JOIN users u ON e.created_by = u.id
                LEFT JOIN event_registrations er ON e.id = er.event_id AND er.status = 'confirmed'
                WHERE $where_clause
                GROUP BY e.id
                ORDER BY event_date ASC, event_time ASC
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get events error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Register user for event
     */
    public function registerForEvent($event_id, $user_id, $registration_data = []) {
        try {
            // Check if event exists and has space
            $event = $this->getEvent($event_id);
            
            if (!$event) {
                return ['success' => false, 'message' => 'Event not found'];
            }
            
            if ($event['status'] != 'active') {
                return ['success' => false, 'message' => 'Event is not active'];
            }
            
            if ($event['registration_required'] && $event['registration_deadline']) {
                if (strtotime($event['registration_deadline']) < time()) {
                    return ['success' => false, 'message' => 'Registration deadline has passed'];
                }
            }
            
            if ($event['max_attendees'] > 0) {
                if ($event['registered_count'] >= $event['max_attendees']) {
                    return ['success' => false, 'message' => 'Event is full'];
                }
            }
            
            // Check if already registered
            $check_stmt = $this->db->prepare("
                SELECT id FROM event_registrations 
                WHERE event_id = ? AND user_id = ?
            ");
            $check_stmt->execute([$event_id, $user_id]);
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Already registered for this event'];
            }
            
            // Create registration
            $stmt = $this->db->prepare("
                INSERT INTO event_registrations 
                (event_id, user_id, registration_date, status, notes, 
                 guests_count, special_requirements, created_at)
                VALUES (?, ?, NOW(), 'confirmed', ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $event_id,
                $user_id,
                $registration_data['notes'] ?? '',
                $registration_data['guests_count'] ?? 0,
                $registration_data['special_requirements'] ?? ''
            ]);
            
            if ($result) {
                // Send confirmation email
                $this->sendRegistrationConfirmation($event_id, $user_id);
                
                return ['success' => true, 'message' => 'Successfully registered for event'];
            }
            
            return ['success' => false, 'message' => 'Registration failed'];
            
        } catch (Exception $e) {
            error_log("Event registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration error'];
        }
    }
    
    /**
     * Get event registrations
     */
    public function getEventRegistrations($event_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT er.*, u.full_name, u.email, u.phone
                FROM event_registrations er
                JOIN users u ON er.user_id = u.id
                WHERE er.event_id = ?
                ORDER BY er.registration_date DESC
            ");
            
            $stmt->execute([$event_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get event registrations error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Send registration confirmation email
     */
    private function sendRegistrationConfirmation($event_id, $user_id) {
        try {
            $event = $this->getEvent($event_id);
            
            $stmt = $this->db->prepare("SELECT email, full_name FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$event || !$user) {
                return false;
            }
            
            $emailManager = new EmailManager();
            return $emailManager->sendTemplateEmail(
                $user['email'],
                'event_registration',
                [
                    'subject' => 'Event Registration Confirmation - ' . $event['title'],
                    'event_title' => $event['title'],
                    'event_date' => $event['event_date'],
                    'event_time' => $event['event_time'],
                    'location' => $event['location'],
                    'user_name' => $user['full_name'],
                    'site_name' => SITE_NAME
                ]
            );
            
        } catch (Exception $e) {
            error_log("Registration confirmation email error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get event categories
     */
    public function getEventCategories() {
        try {
            $stmt = $this->db->query("
                SELECT category, COUNT(*) as count 
                FROM events 
                WHERE status = 'active'
                GROUP BY category 
                ORDER BY count DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get event categories error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get upcoming events countdown
     */
    public function getUpcomingEventsCountdown($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    title,
                    event_date,
                    event_time,
                    location,
                    TIMESTAMPDIFF(SECOND, NOW(), 
                        CONCAT(event_date, ' ', COALESCE(event_time, '00:00:00'))
                    ) as seconds_until
                FROM events 
                WHERE event_date >= CURDATE() 
                AND status = 'active'
                ORDER BY event_date ASC, event_time ASC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Upcoming events countdown error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Export event registrations to CSV
     */
    public function exportRegistrationsToCSV($event_id) {
        try {
            $registrations = $this->getEventRegistrations($event_id);
            $event = $this->getEvent($event_id);
            
            if (empty($registrations)) {
                return false;
            }
            
            $filename = "event_registrations_{$event['title']}_" . date('Y-m-d') . ".csv";
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Headers
            fputcsv($output, [
                'Name', 'Email', 'Phone', 'Registration Date', 
                'Guests', 'Special Requirements', 'Notes'
            ]);
            
            // Data
            foreach ($registrations as $registration) {
                fputcsv($output, [
                    $registration['full_name'],
                    $registration['email'],
                    $registration['phone'],
                    $registration['registration_date'],
                    $registration['guests_count'],
                    $registration['special_requirements'],
                    $registration['notes']
                ]);
            }
            
            fclose($output);
            return true;
            
        } catch (Exception $e) {
            error_log("Export registrations error: " . $e->getMessage());
            return false;
        }
    }
}

// ====================================================================
// HELPER FUNCTIONS
// ====================================================================

function create_event($event_data) {
    global $db;
    $eventManager = new EventManager($db);
    return $eventManager->createEvent($event_data);
}

function get_event($event_id) {
    global $db;
    $eventManager = new EventManager($db);
    return $eventManager->getEvent($event_id);
}

function get_events($filters = [], $limit = 20, $offset = 0) {
    global $db;
    $eventManager = new EventManager($db);
    return $eventManager->getEvents($filters, $limit, $offset);
}

function register_for_event($event_id, $user_id, $registration_data = []) {
    global $db;
    $eventManager = new EventManager($db);
    return $eventManager->registerForEvent($event_id, $user_id, $registration_data);
}

function get_event_registrations($event_id) {
    global $db;
    $eventManager = new EventManager($db);
    return $eventManager->getEventRegistrations($event_id);
}

function get_upcoming_events_countdown($limit = 5) {
    global $db;
    $eventManager = new EventManager($db);
    return $eventManager->getUpcomingEventsCountdown($limit);
}

function export_event_registrations($event_id) {
    global $db;
    $eventManager = new EventManager($db);
    return $eventManager->exportRegistrationsToCSV($event_id);
}
?>