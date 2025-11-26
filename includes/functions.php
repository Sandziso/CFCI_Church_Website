<?php
// includes/functions.php

// Assuming 'config.php' and 'database.php' are included elsewhere or their necessity is handled by the calling environment.
// For a standalone file, these would typically be required, but are omitted here as per the provided content structure.

class ChurchDB {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    // ====================================================================
    // USER MANAGEMENT FUNCTIONS
    // (Retained robust version from the first block with transaction support, 
    // and merged the $is_active parameter from the duplicate definition)
    // ====================================================================
    
    /**
     * Registers a new user with transaction support and separates user data from profile data.
     */
    public function registerUser($full_name, $email, $password, $phone = null, $address = null, $date_of_birth = null, $role = 'member', $is_active = 1) {
        if ($this->emailExists($email)) {
            return ['error' => 'Email already registered.'];
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $this->conn->beginTransaction();
            
            // Insert into users table, now including the is_active column
            $stmt = $this->conn->prepare("INSERT INTO users (full_name, email, password_hash, phone, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $password_hash, $phone, $role, $is_active]);
            $user_id = $this->conn->lastInsertId();
            
            // Insert into user_profiles table (assuming new table: user_profiles with user_id, phone, address, birth_date)
            // Note: phone is duplicated here, a real-world schema might remove it from 'users'
            $profile_stmt = $this->conn->prepare("INSERT INTO user_profiles (user_id, phone, address, birth_date) VALUES (?, ?, ?, ?)");
            $profile_stmt->execute([$user_id, $phone, $address, $date_of_birth]);
            
            $this->conn->commit();
            
            return ['success' => true, 'user_id' => $user_id];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return ['error' => 'Registration failed. Please try again.'];
        }
    }

    /**
     * Logs in a user with session management, account lock checks, and failed attempt tracking.
     */
    public function loginUser($email, $password) {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if account is temporarily locked
        $lock_until = $this->isAccountLocked($email);
        if ($lock_until && strtotime($lock_until) > time()) {
            return ['error' => 'Account temporarily locked. Please try again after ' . date('H:i', strtotime($lock_until))];
        }

        try {
            // Note: The new logic needs 'failed_login_attempts' and 'is_active'
            $stmt = $this->conn->prepare("SELECT id, full_name, password_hash, role, is_active, failed_login_attempts FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (isset($user['is_active']) && !$user['is_active']) {
                    return ['error' => 'Your account has been deactivated. Please contact support.'];
                }

                if (password_verify($password, $user['password_hash'])) {
                    // Reset failed attempts and update last login
                    $this->resetFailedAttempts($email);
                    $this->updateLastLogin($user['id']);
                    
                    // Get user profile data (assuming a 'user_profiles' table)
                    $profile_stmt = $this->conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
                    $profile_stmt->execute([$user['id']]);
                    $profile = $profile_stmt->fetch(PDO::FETCH_ASSOC);

                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['profile'] = $profile;
                    $_SESSION['last_activity'] = time();

                    // Remove sensitive data
                    unset($user['password_hash']);
                    unset($user['failed_login_attempts']);
                    
                    return ['success' => true, 'user' => $user, 'redirect' => $this->getDashboardUrl($user['role'])];
                } else {
                    $this->incrementFailedAttempts($email);
                    // Use a default of 0 if not set, though the column should enforce NOT NULL default 0
                    $current_attempts = $user['failed_login_attempts'] ?? 0;
                    $attempts_left = 5 - ($current_attempts + 1);
                    
                    if ($attempts_left <= 0) {
                        $this->lockAccount($email);
                        return ['error' => 'Account locked due to too many failed attempts. Try again in 30 minutes.'];
                    }
                    
                    return ['error' => "Invalid email or password. {$attempts_left} attempts remaining."];
                }
            } else {
                return ['error' => 'Invalid email or password.'];
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['error' => 'Login failed. Please try again.'];
        }
    }

    public function getUserById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT id, full_name, email, role, join_date FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return false;
        }
    }
    
    // AUTHENTICATION ENHANCEMENTS (Helper Functions)

    private function isAccountLocked($email) {
        try {
            $stmt = $this->conn->prepare("SELECT lock_until FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $user && $user['lock_until'] && strtotime($user['lock_until']) > time() ? $user['lock_until'] : false;
        } catch (PDOException $e) {
            error_log("Account lock check error: " . $e->getMessage());
            return false;
        }
    }
    
    private function incrementFailedAttempts($email) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE email = ?");
            $stmt->execute([$email]);
        } catch (PDOException $e) {
            error_log("Failed attempts increment error: " . $e->getMessage());
        }
    }
    
    private function resetFailedAttempts($email) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET failed_login_attempts = 0, lock_until = NULL WHERE email = ?");
            return $stmt->execute([$email]);
        } catch (PDOException $e) {
            error_log("Reset failed attempts error: " . $e->getMessage());
            return false;
        }
    }

    private function lockAccount($email) {
        try {
            $lock_until = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $stmt = $this->conn->prepare("UPDATE users SET lock_until = ? WHERE email = ?");
            $stmt->execute([$lock_until, $email]);
        } catch (PDOException $e) {
            error_log("Lock account error: " . $e->getMessage());
        }
    }
    
    private function updateLastLogin($user_id) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
            return false;
        }
    }

    public function emailExists($email) {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Email exists check error: " . $e->getMessage());
            return false;
        }
    }
    
    public function createPasswordResetToken($email) {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Generates a cryptographically secure pseudo-random string
                $token = bin2hex(random_bytes(32)); 
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Remove existing tokens for this user
                $delete_stmt = $this->conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
                $delete_stmt->execute([$user['id']]);
                
                // Insert new token
                $insert_stmt = $this->conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                
                if ($insert_stmt->execute([$user['id'], $token, $expires_at])) {
                    return $token;
                }
            }
            return false;
        } catch (PDOException $e) {
            error_log("Password reset token creation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function verifyPasswordResetToken($token) {
        try {
            $stmt = $this->conn->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used = 0");
            $stmt->execute([$token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['user_id'];
            }
            return false;
        } catch (PDOException $e) {
            error_log("Password reset token verification error: " . $e->getMessage());
            return false;
        }
    }
    
    public function resetPassword($user_id, $new_password) {
        try {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            // Reset failed login attempts and lock_until on successful password reset
            $stmt = $this->conn->prepare("UPDATE users SET password_hash = ?, failed_login_attempts = 0, lock_until = NULL WHERE id = ?");
            
            if ($stmt->execute([$password_hash, $user_id])) {
                // Mark token as used
                $update_stmt = $this->conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE user_id = ?");
                $update_stmt->execute([$user_id]);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // DASHBOARD FUNCTIONS (Integrated Enhanced Version)
    // ====================================================================

    /**
     * Gathers comprehensive data specifically for a member's dashboard view.
     */
    public function getMemberDashboardData($user_id) {
        $data = [];
        
        try {
            // User info
            $user_stmt = $this->conn->prepare("
                SELECT u.*, up.phone, up.address, up.avatar_url 
                FROM users u 
                LEFT JOIN user_profiles up ON u.id = up.user_id 
                WHERE u.id = ?
            ");
            $user_stmt->execute([$user_id]);
            $data['user'] = $user_stmt->fetch(PDO::FETCH_ASSOC);

            // Stats
            $data['stats'] = [
                'upcoming_events' => $this->getUserUpcomingEventsCount($user_id),
                'sermons_available' => $this->getPublishedSermonsCount(),
                'prayer_requests' => $this->getUserPrayerRequestsCount($user_id),
                'ministries_involved' => $this->getUserMinistriesCount($user_id)
            ];

            // Recent data
            $data['upcoming_events'] = $this->getUserUpcomingEvents($user_id, 4);
            $data['recent_sermons'] = $this->getRecentSermons(4);
            $data['notifications'] = $this->getUserNotifications($user_id, 5);
            $data['recent_prayers'] = $this->getUserRecentPrayers($user_id, 3); // Added from enhanced block

        } catch (PDOException $e) {
            error_log("Dashboard data error: " . $e->getMessage());
        }
        
        return $data;
    }

    /**
     * Helper function to count upcoming events for a user (Consolidated logic).
     */
    private function getUserUpcomingEventsCount($user_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(DISTINCT e.id) 
                FROM events e 
                LEFT JOIN event_attendance ea ON e.id = ea.event_id AND ea.user_id = ?
                WHERE e.event_date >= CURDATE() 
                AND e.is_active = 1
                AND (ea.user_id IS NOT NULL OR e.max_attendees IS NULL OR e.max_attendees > (
                    SELECT COUNT(*) FROM event_attendance WHERE event_id = e.id AND status != 'cancelled'
                ))
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("User upcoming events count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper function to count published sermons.
     */
    private function getPublishedSermonsCount() {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) FROM sermons WHERE is_published = 1");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Published sermons count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper function to count a user's pending prayer requests.
     */
    private function getUserPrayerRequestsCount($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM prayer_requests WHERE user_id = ? AND status = 'pending'");
            $stmt->execute([$user_id]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("User prayer requests count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper function to count a user's active ministry involvements.
     */
    private function getUserMinistriesCount($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM ministry_members WHERE user_id = ? AND is_active = 1");
            $stmt->execute([$user_id]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("User ministries count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper function to get upcoming events for a user, including attendance status and registration status.
     * Uses the more detailed logic from the enhanced block.
     */
    private function getUserUpcomingEvents($user_id, $limit = 4) {
        try {
            $stmt = $this->conn->prepare("
                SELECT e.*, 
                       ea.status as attendance_status,
                       CASE 
                           WHEN ea.user_id IS NOT NULL THEN 'registered'
                           WHEN e.max_attendees IS NOT NULL AND 
                                (SELECT COUNT(*) FROM event_attendance WHERE event_id = e.id AND status != 'cancelled') >= e.max_attendees THEN 'full'
                           ELSE 'available'
                       END as registration_status
                FROM events e 
                LEFT JOIN event_attendance ea ON e.id = ea.event_id AND ea.user_id = ?
                WHERE e.event_date >= CURDATE() 
                AND e.is_active = 1
                ORDER BY e.event_date, e.start_time 
                LIMIT ?
            ");
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user upcoming events error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper function to fetch a user's notifications.
     */
    private function getUserNotifications($user_id, $limit = 10) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, title, message, type, is_read, created_at FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user notifications error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper function to get a user's recent prayer requests (from enhanced block).
     */
    private function getUserRecentPrayers($user_id, $limit = 3) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, request_text, status, submitted_at 
                FROM prayer_requests 
                WHERE user_id = ? 
                ORDER BY submitted_at DESC 
                LIMIT ?
            ");
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user recent prayers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper function to determine the dashboard URL based on user role.
     */
    private function getDashboardUrl($role) {
        switch ($role) {
            case 'pastor': return '/pastor/dashboard.php';
            case 'admin': return '/admin/dashboard.php';
            default: return '/member/dashboard.php';
        }
    }

    // ====================================================================
    // QUICK ACTION FUNCTIONS (New)
    // ====================================================================

    /**
     * Quickly submit a prayer request from the dashboard (from enhanced block).
     */
    public function submitQuickPrayer($user_id, $prayer_text) {
        try {
            // Note: The original 'submitPrayerRequest' does this, but this is a dedicated quick action
            $stmt = $this->conn->prepare("
                INSERT INTO prayer_requests (user_id, request_text, submitted_at) 
                VALUES (?, ?, NOW())
            ");
            return $stmt->execute([$user_id, $prayer_text]);
        } catch (PDOException $e) {
            error_log("Quick prayer submission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Quickly register for an event, including checks for existing registration and capacity (from enhanced block).
     */
    public function quickEventRegistration($event_id, $user_id) {
        try {
            // Check if already registered
            $check_stmt = $this->conn->prepare("
                SELECT id FROM event_attendance 
                WHERE event_id = ? AND user_id = ?
            ");
            $check_stmt->execute([$event_id, $user_id]);
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Already registered for this event'];
            }

            // Check event capacity
            $capacity_stmt = $this->conn->prepare("
                SELECT e.max_attendees, 
                        (SELECT COUNT(*) FROM event_attendance WHERE event_id = e.id AND status != 'cancelled') as current_attendees
                FROM events e 
                WHERE e.id = ?
            ");
            $capacity_stmt->execute([$event_id]);
            $capacity = $capacity_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($capacity && $capacity['max_attendees'] && $capacity['current_attendees'] >= $capacity['max_attendees']) {
                return ['success' => false, 'message' => 'Event is full'];
            }

            // Register for event
            $register_stmt = $this->conn->prepare("
                INSERT INTO event_attendance (event_id, user_id, status, registered_at) 
                VALUES (?, ?, 'registered', NOW())
            ");
            
            if ($register_stmt->execute([$event_id, $user_id])) {
                return ['success' => true, 'message' => 'Successfully registered for event'];
            }
            
        } catch (PDOException $e) {
            error_log("Quick event registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    // ====================================================================
    // NOTIFICATION MANAGEMENT FUNCTIONS (New)
    // ====================================================================

    /**
     * Marks a single notification as read (from enhanced block).
     */
    public function markNotificationAsRead($notification_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([$notification_id, $user_id]);
        } catch (PDOException $e) {
            error_log("Mark notification as read error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marks all unread notifications for a user as read (from enhanced block).
     */
    public function markAllNotificationsAsRead($user_id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = ? AND is_read = 0
            ");
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log("Mark all notifications as read error: " . $e->getMessage());
            return false;
        }
    }
    
    // ====================================================================
    // SEARCH FUNCTIONS (New)
    // ====================================================================

    /**
     * Searches content relevant to the member dashboard (events, sermons, ministries) (from enhanced block).
     */
    public function searchDashboardContent($user_id, $search_term) {
        $results = [];
        $search_term_wildcard = "%$search_term%";
        
        try {
            // Search events
            $event_stmt = $this->conn->prepare("
                SELECT id, title, 'event' as type 
                FROM events 
                WHERE title LIKE ? AND is_active = 1 AND event_date >= CURDATE()
                LIMIT 5
            ");
            $event_stmt->execute([$search_term_wildcard]);
            $results['events'] = $event_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Search sermons
            $sermon_stmt = $this->conn->prepare("
                SELECT id, title, 'sermon' as type 
                FROM sermons 
                WHERE title LIKE ? AND is_published = 1
                LIMIT 5
            ");
            $sermon_stmt->execute([$search_term_wildcard]);
            $results['sermons'] = $sermon_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Search ministries
            $ministry_stmt = $this->conn->prepare("
                SELECT id, name as title, 'ministry' as type 
                FROM ministries 
                WHERE name LIKE ? AND is_active = 1
                LIMIT 5
            ");
            $ministry_stmt->execute([$search_term_wildcard]);
            $results['ministries'] = $ministry_stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Dashboard search error: " . $e->getMessage());
        }
        
        return $results;
    }


    // ====================================================================
    // ANNOUNCEMENT FUNCTIONS (Existing)
    // ====================================================================
    public function createAnnouncement($title, $content, $author_id, $expires_at = null) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO announcements (title, content, author_id, expires_at) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$title, $content, $author_id, $expires_at]);
        } catch (PDOException $e) {
            error_log("Create announcement error: " . $e->getMessage());
            return false;
        }
    }

    public function getActiveAnnouncements() {
        try {
            $sql = "SELECT a.*, u.full_name as author_name 
                    FROM announcements a 
                    JOIN users u ON a.author_id = u.id 
                    WHERE expires_at IS NULL OR expires_at >= CURDATE() 
                    ORDER BY created_at DESC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get announcements error: " . $e->getMessage());
            return [];
        }
    }

    public function updateAnnouncement($id, $title, $content, $expires_at = null) {
        try {
            $stmt = $this->conn->prepare("UPDATE announcements SET title = ?, content = ?, expires_at = ? WHERE id = ?");
            return $stmt->execute([$title, $content, $expires_at, $id]);
        } catch (PDOException $e) {
            error_log("Update announcement error: " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // EVENT FUNCTIONS (Existing)
    // ====================================================================
    public function createEvent($title, $event_date, $start_time, $end_time, $location, $description = null) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO events (title, event_date, start_time, end_time, location, description) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$title, $event_date, $start_time, $end_time, $location, $description]);
        } catch (PDOException $e) {
            error_log("Create event error: " . $e->getMessage());
            return false;
        }
    }

    public function getUpcomingEvents($limit = 10) {
        try {
            $sql = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date, start_time LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get events error: " . $e->getMessage());
            return [];
        }
    }

    public function registerForEvent($event_id, $user_id) {
        try {
            // Note: This function is simpler than quickEventRegistration and assumes no capacity check
            $stmt = $this->conn->prepare("INSERT INTO event_attendance (event_id, user_id, status) VALUES (?, ?, 'registered')");
            return $stmt->execute([$event_id, $user_id]);
        } catch (PDOException $e) {
            error_log("Event registration error: " . $e->getMessage());
            return false;
        }
    }

    public function markAttendance($event_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("UPDATE event_attendance SET status = 'attended', attended_at = NOW() WHERE event_id = ? AND user_id = ?");
            return $stmt->execute([$event_id, $user_id]);
        } catch (PDOException $e) {
            error_log("Mark attendance error: " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // DONATION FUNCTIONS (Existing)
    // ====================================================================
    
    /**
     * Records a donation with detailed payment information
     */
    public function recordDonationWithDetails($user_id, $amount, $purpose, $payment_method, $transaction_id, $recurring = 0, $recurring_frequency = 'monthly') {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO donations (user_id, amount, purpose, payment_method, transaction_id, status, recurring, recurring_frequency) 
                VALUES (?, ?, ?, ?, ?, 'completed', ?, ?)
            ");
            return $stmt->execute([$user_id, $amount, $purpose, $payment_method, $transaction_id, $recurring, $recurring_frequency]);
        } catch (PDOException $e) {
            error_log("Record donation with details error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets user donations with limit (Overwrites previous simpler version)
     */
    public function getDonationsByUser($user_id, $limit = null) {
        try {
            $sql = "SELECT * FROM donations WHERE user_id = ? ORDER BY donation_date DESC";
            
            if ($limit) {
                $sql .= " LIMIT ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                $stmt->bindValue(2, $limit, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$user_id]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get donations by user error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets total donations for a user in a specific year
     */
    public function getUserYearlyDonations($user_id, $year) {
        try {
            $stmt = $this->conn->prepare("
                SELECT SUM(amount) as total 
                FROM donations 
                WHERE user_id = ? 
                AND YEAR(donation_date) = ? 
                AND status = 'completed'
            ");
            $stmt->execute([$user_id, $year]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get user yearly donations error: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalDonations($start_date = null, $end_date = null) {
        try {
            $sql = "SELECT SUM(amount) as total FROM donations WHERE 1=1";
            $params = [];

            if ($start_date) {
                $sql .= " AND donation_date >= ?";
                $params[] = $start_date;
            }

            if ($end_date) {
                $sql .= " AND donation_date <= ?";
                $params[] = $end_date;
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get total donations error: " . $e->getMessage());
            return 0;
        }
    }

    // ====================================================================
    // MINISTRY FUNCTIONS (Existing)
    // ====================================================================
    public function createMinistry($name, $description, $leader_id, $meeting_schedule = null) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO ministries (name, description, leader_id, meeting_schedule) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$name, $description, $leader_id, $meeting_schedule]);
        } catch (PDOException $e) {
            error_log("Create ministry error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllMinistries() {
        try {
            $sql = "SELECT m.*, u.full_name as leader_name 
                    FROM ministries m 
                    LEFT JOIN users u ON m.leader_id = u.id";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get ministries error: " . $e->getMessage());
            return [];
        }
    }

    public function addMemberToMinistry($ministry_id, $user_id, $role = 'member') {
        try {
            $check_stmt = $this->conn->prepare("SELECT id FROM ministry_members WHERE ministry_id = ? AND user_id = ?");
            $check_stmt->execute([$ministry_id, $user_id]);

            if ($check_stmt->rowCount() > 0) {
                // Member already exists in ministry
                return false;
            }

            $stmt = $this->conn->prepare("INSERT INTO ministry_members (ministry_id, user_id, role) VALUES (?, ?, ?)");
            return $stmt->execute([$ministry_id, $user_id, $role]);
        } catch (PDOException $e) {
            error_log("Add ministry member error: " . $e->getMessage());
            return false;
        }
    }

    public function getMinistryMembers($ministry_id) {
        try {
            $sql = "SELECT mm.*, u.full_name, u.email 
                    FROM ministry_members mm 
                    JOIN users u ON mm.user_id = u.id 
                    WHERE mm.ministry_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$ministry_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get ministry members error: " . $e->getMessage());
            return [];
        }
    }

    // ====================================================================
    // PRAYER REQUEST FUNCTIONS (Updated/New)
    // ====================================================================

    /**
     * Submit a detailed prayer request with category and anonymity options (NEW)
     */
    public function submitDetailedPrayerRequest($user_id, $prayer_text, $category = 'other', $is_anonymous = 0, $urgency = 'normal') {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO prayer_requests (user_id, request_text, category, urgency, is_anonymous, submitted_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([$user_id, $prayer_text, $category, $urgency, $is_anonymous]);
        } catch (PDOException $e) {
            error_log("Detailed prayer request submission error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enhanced prayer request submission (OVERWRITES previous simple version)
     */
    public function submitPrayerRequest($user_id, $request_text, $category = 'other', $is_anonymous = 0) {
        return $this->submitDetailedPrayerRequest($user_id, $request_text, $category, $is_anonymous, 'normal');
    }

    /**
     * Get all prayer requests for a specific user (NEW)
     */
    public function getUserPrayerRequests($user_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT pr.*, 
                       u.full_name as pastor_name
                FROM prayer_requests pr
                LEFT JOIN users u ON pr.addressed_by_pastor_id = u.id
                WHERE pr.user_id = ? 
                ORDER BY pr.submitted_at DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user prayer requests error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get prayer statistics for a user (NEW)
     */
    public function getUserPrayerStats($user_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'addressed' THEN 1 ELSE 0 END) as addressed,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                FROM prayer_requests 
                WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user prayer stats error: " . $e->getMessage());
            return ['total' => 0, 'pending' => 0, 'addressed' => 0, 'closed' => 0];
        }
    }

    /**
     * Check if a user owns a specific prayer request (NEW)
     */
    public function isUserPrayerOwner($user_id, $prayer_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id FROM prayer_requests 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$prayer_id, $user_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Prayer ownership check error: " . $e->getMessage());
            return false;
        }
    }


    public function getPrayerRequests($status = null, $limit = 50) {
        try {
            $sql = "SELECT pr.*, u.full_name 
                    FROM prayer_requests pr 
                    LEFT JOIN users u ON pr.user_id = u.id";

            if ($status) {
                $sql .= " WHERE pr.status = ?";
            }

            $sql .= " ORDER BY pr.submitted_at DESC LIMIT ?";

            $stmt = $this->conn->prepare($sql);
            
            if ($status) {
                $stmt->bindValue(1, $status, PDO::PARAM_STR);
                $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get prayer requests error: " . $e->getMessage());
            return [];
        }
    }

    public function updatePrayerRequestStatus($request_id, $status, $pastor_id = null) {
        try {
            if ($status === 'addressed' || $status === 'closed') {
                $stmt = $this->conn->prepare("UPDATE prayer_requests SET status = ?, addressed_by_pastor_id = ?, addressed_at = NOW() WHERE id = ?");
                return $stmt->execute([$status, $pastor_id, $request_id]);
            } else {
                $stmt = $this->conn->prepare("UPDATE prayer_requests SET status = ? WHERE id = ?");
                return $stmt->execute([$status, $request_id]);
            }
        } catch (PDOException $e) {
            error_log("Update prayer request error: " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // SERMON FUNCTIONS (Existing & New)
    // ====================================================================
    public function addSermon($title, $preacher_id, $sermon_date, $audio_url = null, $video_url = null, $notes_text = null) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO sermons (title, preacher_id, sermon_date, audio_url, video_url, notes_text) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$title, $preacher_id, $sermon_date, $audio_url, $video_url, $notes_text]);
        } catch (PDOException $e) {
            error_log("Add sermon error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent sermons (UPDATED to include WHERE s.is_published = 1)
     */
    public function getRecentSermons($limit = 10) {
        try {
            $sql = "SELECT s.*, u.full_name as preacher_name 
                    FROM sermons s 
                    LEFT JOIN users u ON s.preacher_id = u.id 
                    WHERE s.is_published = 1
                    ORDER BY s.sermon_date DESC 
                    LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get sermons error: " . $e->getMessage());
            return [];
        }
    }

    /**
    * Get filtered sermons with various criteria
    */
    public function getFilteredSermons($category = 'all', $preacher = 'all', $year = 'all', $search = '') {
        try {
            $sql = "SELECT s.*, u.full_name as preacher_name 
                    FROM sermons s 
                    LEFT JOIN users u ON s.preacher_id = u.id 
                    WHERE s.is_published = 1";
            
            $params = [];
            
            if ($category !== 'all') {
                $sql .= " AND s.category = ?";
                $params[] = $category;
            }
            
            if ($preacher !== 'all') {
                $sql .= " AND s.preacher_id = ?";
                $params[] = $preacher;
            }
            
            if ($year !== 'all') {
                $sql .= " AND YEAR(s.sermon_date) = ?";
                $params[] = $year;
            }
            
            if (!empty($search)) {
                $sql .= " AND (s.title LIKE ? OR s.bible_passage LIKE ? OR s.notes_text LIKE ?)";
                $search_term = "%$search%";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }
            
            $sql .= " ORDER BY s.sermon_date DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get filtered sermons error: " . $e->getMessage());
            return [];
        }
    }

    /**
    * Get distinct sermon categories
    */
    public function getSermonCategories() {
        try {
            $stmt = $this->conn->query("
                SELECT DISTINCT category 
                FROM sermons 
                WHERE category IS NOT NULL AND category != '' 
                ORDER BY category
            ");
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $categories ?: ['teaching', 'prayer', 'faith', 'generosity', 'encouragement', 'relationships'];
        } catch (PDOException $e) {
            error_log("Get sermon categories error: " . $e->getMessage());
            return ['teaching', 'prayer', 'faith', 'generosity', 'encouragement', 'relationships'];
        }
    }

    /**
    * Get all preachers who have preached sermons
    */
    public function getSermonPreachers() {
        try {
            $stmt = $this->conn->query("
                SELECT DISTINCT u.id, u.full_name 
                FROM sermons s 
                JOIN users u ON s.preacher_id = u.id 
                WHERE s.is_published = 1 
                ORDER BY u.full_name
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get sermon preachers error: " . $e->getMessage());
            return [];
        }
    }

    /**
    * Get distinct years from sermons
    */
    public function getSermonYears() {
        try {
            $stmt = $this->conn->query("
                SELECT DISTINCT YEAR(sermon_date) as year 
                FROM sermons 
                WHERE is_published = 1 
                ORDER BY year DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Get sermon years error: " . $e->getMessage());
            // Return current and previous year as fallback
            $current_year = date('Y');
            return [$current_year, $current_year - 1];
        }
    }

    /**
    * Track sermon play for analytics
    */
    public function trackSermonPlay($sermon_id, $user_id, $media_type) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO sermon_plays (sermon_id, user_id, media_type) 
                VALUES (?, ?, ?)
            ");
            $result = $stmt->execute([$sermon_id, $user_id, $media_type]);
            
            // Update views count
            if ($result) {
                $update_stmt = $this->conn->prepare("
                    UPDATE sermons 
                    SET views_count = views_count + 1 
                    WHERE id = ?
                ");
                $update_stmt->execute([$sermon_id]);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Track sermon play error: " . $e->getMessage());
            return false;
        }
    }

    /**
    * Track sermon download
    */
    public function trackSermonDownload($sermon_id, $user_id, $file_type) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO sermon_downloads (sermon_id, user_id, file_type) 
                VALUES (?, ?, ?)
            ");
            $result = $stmt->execute([$sermon_id, $user_id, $file_type]);
            
            // Update downloads count
            if ($result) {
                $update_stmt = $this->conn->prepare("
                    UPDATE sermons 
                    SET downloads_count = downloads_count + 1 
                    WHERE id = ?
                ");
                $update_stmt->execute([$sermon_id]);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Track sermon download error: " . $e->getMessage());
            return false;
        }
    }

    /**
    * Get sermon by ID with full details
    */
    public function getSermonById($sermon_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT s.*, u.full_name as preacher_name, 
                       ss.title as series_title, ss.description as series_description
                FROM sermons s 
                LEFT JOIN users u ON s.preacher_id = u.id 
                LEFT JOIN sermon_series ss ON s.series_id = ss.id 
                WHERE s.id = ? AND s.is_published = 1
            ");
            $stmt->execute([$sermon_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get sermon by ID error: " . $e->getMessage());
            return false;
        }
    }

    /**
    * Get popular sermons (most viewed)
    */
    public function getPopularSermons($limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT s.*, u.full_name as preacher_name 
                FROM sermons s 
                LEFT JOIN users u ON s.preacher_id = u.id 
                WHERE s.is_published = 1 
                ORDER BY s.views_count DESC 
                LIMIT ?
            ");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get popular sermons error: " . $e->getMessage());
            return [];
        }
    }

    /**
    * Get sermons by series
    */
    public function getSermonsBySeries($series_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT s.*, u.full_name as preacher_name 
                FROM sermons s 
                LEFT JOIN users u ON s.preacher_id = u.id 
                WHERE s.series_id = ? AND s.is_published = 1 
                ORDER BY s.sermon_date
            ");
            $stmt->execute([$series_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get sermons by series error: " . $e->getMessage());
            return [];
        }
    }

    /**
    * Get all active sermon series
    */
    public function getSermonSeries() {
        try {
            $stmt = $this->conn->query("
                SELECT * FROM sermon_series 
                WHERE is_active = 1 
                ORDER BY start_date DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get sermon series error: " . $e->getMessage());
            return [];
        }
    }

    /**
    * Get user's recently played sermons
    */
    public function getUserRecentPlays($user_id, $limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT s.*, u.full_name as preacher_name, sp.played_at 
                FROM sermon_plays sp 
                JOIN sermons s ON sp.sermon_id = s.id 
                LEFT JOIN users u ON s.preacher_id = u.id 
                WHERE sp.user_id = ? AND s.is_published = 1 
                ORDER BY sp.played_at DESC 
                LIMIT ?
            ");
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user recent plays error: " . $e->getMessage());
            return [];
        }
    }

    // ====================================================================
    // QUOTE FUNCTIONS (Existing)
    // ====================================================================
    public function addQuote($author_id, $quote_text, $visibility = 'public') {
        try {
            $stmt = $this->conn->prepare("INSERT INTO quotes (author_id, quote_text, visibility) VALUES (?, ?, ?)");
            return $stmt->execute([$author_id, $quote_text, $visibility]);
        } catch (PDOException $e) {
            error_log("Add quote error: " . $e->getMessage());
            return false;
        }
    }

    public function getQuotes($visibility = null) {
        try {
            $sql = "SELECT q.*, u.full_name as author_name 
                    FROM quotes q 
                    JOIN users u ON q.author_id = u.id";

            if ($visibility) {
                $sql .= " WHERE q.visibility = ?";
            }

            $sql .= " ORDER BY q.created_at DESC";

            $stmt = $this->conn->prepare($sql);

            if ($visibility) {
                $stmt->execute([$visibility]);
            } else {
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get quotes error: " . $e->getMessage());
            return [];
        }
    }

    // ====================================================================
    // ADMIN FUNCTIONS (Existing)
    // ====================================================================
    public function isUserAdmin($user_id) {
        try {
            // Note: The original code assumes a separate 'admins' table. 
            // The more modern 'loginUser' function uses the 'role' column in the 'users' table.
            // Keeping the old logic here, but it's likely inconsistent with the 'role' field usage elsewhere.
            $stmt = $this->conn->prepare("SELECT role FROM admins WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: false;
        } catch (PDOException $e) {
            error_log("Admin check error: " . $e->getMessage());
            return false;
        }
    }

    public function getChurchStats() {
        $stats = [];

        try {
            // Total members
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'member'");
            $stats['total_members'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total pastors
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'pastor'");
            $stats['total_pastors'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Pending prayer requests
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM prayer_requests WHERE status = 'pending'");
            $stats['pending_prayers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Recent donations (last 30 days)
            $stmt = $this->conn->query("SELECT SUM(amount) as total FROM donations WHERE donation_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['recent_donations'] = $result['total'] ?? 0;

            // Upcoming events
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM events WHERE event_date >= CURDATE()");
            $stats['upcoming_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        } catch (PDOException $e) {
            error_log("Get church stats error: " . $e->getMessage());
        }

        return $stats;
    }

    // ====================================================================
    // UTILITY FUNCTIONS (Existing)
    // ====================================================================
    public function searchMembers($search_term) {
        try {
            $search_term = "%$search_term%";
            $stmt = $this->conn->prepare("SELECT id, full_name, email, role, join_date FROM users WHERE full_name LIKE ? OR email LIKE ?");
            $stmt->execute([$search_term, $search_term]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search members error: " . $e->getMessage());
            return [];
        }
    }

    /*
    REMOVED DUPLICATE:
    public function registerUser($full_name, $email, $password, $phone = null, $address = null, $date_of_birth = null, $role = 'member', $is_active = 1) {
        // ... (duplicate code removed)
    }
    */

    public function getUserEvents($user_id) {
        try {
            $sql = "SELECT e.*, ea.status 
                    FROM events e 
                    JOIN event_attendance ea ON e.id = ea.event_id 
                    WHERE ea.user_id = ? 
                    ORDER BY e.event_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user events error: " . $e->getMessage());
            return [];
        }
    }
}
// Note: Removed the final closing brace '}' as it was incorrectly placed after the last function and before the fatal error line, causing a parsing issue if the file already had one.
// Assuming the user meant for the ChurchDB class to be the only thing in the file that required a fix.
?>