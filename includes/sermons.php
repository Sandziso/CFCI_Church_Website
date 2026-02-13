<?php
// ===================================================
// SERMON FUNCTIONS - Christian Family Centre International
// ===================================================

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// SERMON MANAGER
// ====================================================================

class SermonManager {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Add new sermon
     */
    public function addSermon($sermon_data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sermons 
                (title, description, preacher, sermon_date, scripture_reference,
                 series, series_part, duration, audio_url, video_url, 
                 thumbnail_url, download_url, notes_url, status, tags,
                 created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            return $stmt->execute([
                $sermon_data['title'] ?? '',
                $sermon_data['description'] ?? '',
                $sermon_data['preacher'] ?? '',
                $sermon_data['sermon_date'] ?? date('Y-m-d'),
                $sermon_data['scripture_reference'] ?? '',
                $sermon_data['series'] ?? null,
                $sermon_data['series_part'] ?? null,
                $sermon_data['duration'] ?? 0,
                $sermon_data['audio_url'] ?? null,
                $sermon_data['video_url'] ?? null,
                $sermon_data['thumbnail_url'] ?? null,
                $sermon_data['download_url'] ?? null,
                $sermon_data['notes_url'] ?? null,
                $sermon_data['status'] ?? 'draft',
                $sermon_data['tags'] ?? null,
                $sermon_data['created_by'] ?? null
            ]);
            
        } catch (Exception $e) {
            error_log("Add sermon error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get sermon by ID
     */
    public function getSermon($sermon_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, 
                       u.full_name as creator_name,
                       COUNT(sv.id) as view_count,
                       COUNT(sl.id) as like_count
                FROM sermons s
                LEFT JOIN users u ON s.created_by = u.id
                LEFT JOIN sermon_views sv ON s.id = sv.sermon_id
                LEFT JOIN sermon_likes sl ON s.id = sl.sermon_id AND sl.is_liked = 1
                WHERE s.id = ?
                GROUP BY s.id
            ");
            
            $stmt->execute([$sermon_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get sermon error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all sermons with filters
     */
    public function getSermons($filters = [], $limit = 20, $offset = 0) {
        try {
            $where = ["s.status = 'published'"];
            $params = [];
            
            // Apply filters
            if (isset($filters['preacher'])) {
                $where[] = "s.preacher LIKE ?";
                $params[] = "%{$filters['preacher']}%";
            }
            
            if (isset($filters['series'])) {
                $where[] = "s.series = ?";
                $params[] = $filters['series'];
            }
            
            if (isset($filters['year'])) {
                $where[] = "YEAR(s.sermon_date) = ?";
                $params[] = $filters['year'];
            }
            
            if (isset($filters['month'])) {
                $where[] = "MONTH(s.sermon_date) = ?";
                $params[] = $filters['month'];
            }
            
            if (isset($filters['search'])) {
                $where[] = "(s.title LIKE ? OR s.description LIKE ? OR s.scripture_reference LIKE ? OR s.tags LIKE ?)";
                $search = "%{$filters['search']}%";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            if (isset($filters['has_audio']) && $filters['has_audio']) {
                $where[] = "s.audio_url IS NOT NULL";
            }
            
            if (isset($filters['has_video']) && $filters['has_video']) {
                $where[] = "s.video_url IS NOT NULL";
            }
            
            if (isset($filters['tag'])) {
                $where[] = "s.tags LIKE ?";
                $params[] = "%{$filters['tag']}%";
            }
            
            $where_clause = implode(' AND ', $where);
            
            $stmt = $this->db->prepare("
                SELECT s.*,
                       u.full_name as creator_name,
                       COUNT(sv.id) as view_count,
                       COUNT(sl.id) as like_count
                FROM sermons s
                LEFT JOIN users u ON s.created_by = u.id
                LEFT JOIN sermon_views sv ON s.id = sv.sermon_id
                LEFT JOIN sermon_likes sl ON s.id = sl.sermon_id AND sl.is_liked = 1
                WHERE $where_clause
                GROUP BY s.id
                ORDER BY s.sermon_date DESC
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get sermons error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update sermon
     */
    public function updateSermon($sermon_id, $sermon_data) {
        try {
            $updates = [];
            $params = [];
            
            foreach ($sermon_data as $field => $value) {
                $updates[] = "$field = ?";
                $params[] = $value;
            }
            
            $params[] = $sermon_id;
            
            $stmt = $this->db->prepare("
                UPDATE sermons 
                SET " . implode(', ', $updates) . ", updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            error_log("Update sermon error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete sermon
     */
    public function deleteSermon($sermon_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE sermons 
                SET status = 'deleted', updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([$sermon_id]);
            
        } catch (Exception $e) {
            error_log("Delete sermon error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record sermon view
     */
    public function recordView($sermon_id, $user_id = null) {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $this->db->prepare("
                INSERT INTO sermon_views 
                (sermon_id, user_id, ip_address, user_agent, viewed_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([$sermon_id, $user_id, $ip_address, $user_agent]);
            
        } catch (Exception $e) {
            error_log("Record sermon view error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Toggle sermon like
     */
    public function toggleLike($sermon_id, $user_id) {
        try {
            // Check if already liked
            $check_stmt = $this->db->prepare("
                SELECT id, is_liked FROM sermon_likes 
                WHERE sermon_id = ? AND user_id = ?
            ");
            $check_stmt->execute([$sermon_id, $user_id]);
            
            if ($check_stmt->rowCount() > 0) {
                $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
                $new_like_status = $existing['is_liked'] ? 0 : 1;
                
                $update_stmt = $this->db->prepare("
                    UPDATE sermon_likes 
                    SET is_liked = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                return $update_stmt->execute([$new_like_status, $existing['id']]);
            } else {
                $insert_stmt = $this->db->prepare("
                    INSERT INTO sermon_likes 
                    (sermon_id, user_id, is_liked, created_at)
                    VALUES (?, ?, 1, NOW())
                ");
                
                return $insert_stmt->execute([$sermon_id, $user_id]);
            }
            
        } catch (Exception $e) {
            error_log("Toggle sermon like error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get sermon series
     */
    public function getSermonSeries() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    series,
                    COUNT(*) as sermon_count,
                    MIN(sermon_date) as start_date,
                    MAX(sermon_date) as end_date
                FROM sermons 
                WHERE series IS NOT NULL 
                AND series != ''
                AND status = 'published'
                GROUP BY series 
                ORDER BY end_date DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get sermon series error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get preachers list
     */
    public function getPreachers() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    preacher,
                    COUNT(*) as sermon_count
                FROM sermons 
                WHERE preacher IS NOT NULL 
                AND preacher != ''
                AND status = 'published'
                GROUP BY preacher 
                ORDER BY sermon_count DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get preachers error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get sermon statistics
     */
    public function getSermonStats() {
        try {
            $stats = [];
            
            // Total sermons
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM sermons WHERE status = 'published'");
            $stats['total_sermons'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total views
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM sermon_views");
            $stats['total_views'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total likes
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM sermon_likes WHERE is_liked = 1");
            $stats['total_likes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Most viewed sermons
            $stmt = $this->db->query("
                SELECT s.id, s.title, COUNT(sv.id) as view_count
                FROM sermons s
                LEFT JOIN sermon_views sv ON s.id = sv.sermon_id
                WHERE s.status = 'published'
                GROUP BY s.id
                ORDER BY view_count DESC
                LIMIT 10
            ");
            $stats['most_viewed'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Most liked sermons
            $stmt = $this->db->query("
                SELECT s.id, s.title, COUNT(sl.id) as like_count
                FROM sermons s
                LEFT JOIN sermon_likes sl ON s.id = sl.sermon_id AND sl.is_liked = 1
                WHERE s.status = 'published'
                GROUP BY s.id
                ORDER BY like_count DESC
                LIMIT 10
            ");
            $stats['most_liked'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Monthly uploads
            $stmt = $this->db->query("
                SELECT 
                    DATE_FORMAT(sermon_date, '%Y-%m') as month,
                    COUNT(*) as count
                FROM sermons 
                WHERE status = 'published'
                GROUP BY DATE_FORMAT(sermon_date, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12
            ");
            $stats['monthly_uploads'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get sermon stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add sermon note
     */
    public function addNote($sermon_id, $user_id, $note) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sermon_notes 
                (sermon_id, user_id, note, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            
            return $stmt->execute([$sermon_id, $user_id, $note]);
            
        } catch (Exception $e) {
            error_log("Add sermon note error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user sermon notes
     */
    public function getUserNotes($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT sn.*, s.title as sermon_title, s.sermon_date
                FROM sermon_notes sn
                JOIN sermons s ON sn.sermon_id = s.id
                WHERE sn.user_id = ?
                ORDER BY sn.created_at DESC
            ");
            
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get user sermon notes error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get featured sermon
     */
    public function getFeaturedSermon() {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*,
                       u.full_name as creator_name
                FROM sermons s
                LEFT JOIN users u ON s.created_by = u.id
                WHERE s.status = 'published'
                AND s.is_featured = 1
                ORDER BY s.sermon_date DESC
                LIMIT 1
            ");
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get featured sermon error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent sermons
     */
    public function getRecentSermons($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*,
                       u.full_name as creator_name
                FROM sermons s
                LEFT JOIN users u ON s.created_by = u.id
                WHERE s.status = 'published'
                ORDER BY s.sermon_date DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get recent sermons error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search sermons
     */
    public function searchSermons($query, $limit = 20) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*,
                       u.full_name as creator_name,
                       MATCH(s.title, s.description, s.scripture_reference, s.tags) 
                       AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                FROM sermons s
                LEFT JOIN users u ON s.created_by = u.id
                WHERE s.status = 'published'
                AND MATCH(s.title, s.description, s.scripture_reference, s.tags) 
                    AGAINST(? IN NATURAL LANGUAGE MODE)
                ORDER BY relevance DESC
                LIMIT ?
            ");
            
            $stmt->execute([$query, $query, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            // Fallback to LIKE search if fulltext not available
            return $this->getSermons(['search' => $query], $limit, 0);
        }
    }
}

// ====================================================================
// HELPER FUNCTIONS
// ====================================================================

function add_sermon($sermon_data) {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->addSermon($sermon_data);
}

function get_sermon($sermon_id) {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->getSermon($sermon_id);
}

function get_sermons($filters = [], $limit = 20, $offset = 0) {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->getSermons($filters, $limit, $offset);
}

function update_sermon($sermon_id, $sermon_data) {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->updateSermon($sermon_id, $sermon_data);
}

function delete_sermon($sermon_id) {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->deleteSermon($sermon_id);
}

function record_sermon_view($sermon_id, $user_id = null) {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->recordView($sermon_id, $user_id);
}

function toggle_sermon_like($sermon_id, $user_id) {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->toggleLike($sermon_id, $user_id);
}

function get_sermon_series() {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->getSermonSeries();
}

function get_preachers() {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->getPreachers();
}

function get_sermon_stats() {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->getSermonStats();
}

function get_featured_sermon() {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->getFeaturedSermon();
}

function get_recent_sermons($limit = 5) {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->getRecentSermons($limit);
}

function search_sermons($query, $limit = 20) {
    global $db;
    $sermonManager = new SermonManager($db);
    return $sermonManager->searchSermons($query, $limit);
}
?>