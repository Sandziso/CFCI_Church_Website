<?php
// SermonManager.php - Enhanced Version
class SermonManager {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Get sermons with advanced filtering
     */
    public function getSermons($filters = [], $limit = 12, $offset = 0, $exclude_id = null) {
        try {
            $where = ["s.is_published = 1"];
            $params = [];
            
            // Apply filters
            if (!empty($filters['category'])) {
                $where[] = "s.category = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['preacher_id']) && is_numeric($filters['preacher_id'])) {
                $where[] = "s.preacher_id = ?";
                $params[] = $filters['preacher_id'];
            }
            
            if (!empty($filters['series'])) {
                $where[] = "s.series = ?";
                $params[] = $filters['series'];
            }
            
            if (!empty($filters['year']) && is_numeric($filters['year'])) {
                $where[] = "YEAR(s.sermon_date) = ?";
                $params[] = $filters['year'];
            }
            
            if (!empty($filters['month']) && is_numeric($filters['month'])) {
                $where[] = "MONTH(s.sermon_date) = ?";
                $params[] = $filters['month'];
            }
            
            if (!empty($filters['search'])) {
                $where[] = "(s.title LIKE ? OR s.description LIKE ? OR s.scripture_reference LIKE ? OR s.tags LIKE ?)";
                $search = "%{$filters['search']}%";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            if (!empty($filters['has_audio'])) {
                $where[] = "s.audio_url IS NOT NULL AND s.audio_url != ''";
            }
            
            if (!empty($filters['has_video'])) {
                $where[] = "s.video_url IS NOT NULL AND s.video_url != ''";
            }
            
            if (!empty($filters['tag'])) {
                $where[] = "s.tags LIKE ?";
                $params[] = "%{$filters['tag']}%";
            }
            
            if ($exclude_id !== null) {
                $where[] = "s.id != ?";
                $params[] = $exclude_id;
            }
            
            $where_clause = implode(' AND ', $where);
            
            $sql = "
                SELECT s.*, 
                       u.full_name as preacher_name,
                       u.email as preacher_email,
                       up.avatar_url as preacher_avatar,
                       up.bio as preacher_bio,
                       ss.name as series_name,
                       ss.description as series_description,
                       (SELECT COUNT(*) FROM sermon_views sv WHERE sv.sermon_id = s.id) as view_count,
                       (SELECT COUNT(*) FROM sermon_likes sl WHERE sl.sermon_id = s.id AND sl.is_liked = 1) as like_count
                FROM sermons s
                LEFT JOIN users u ON s.preacher_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN sermon_series ss ON s.series_id = ss.id
                WHERE {$where_clause}
                ORDER BY s.sermon_date DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get sermons error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get sermon by ID with all details
     */
    public function getSermon($sermon_id) {
        try {
            $sql = "
                SELECT s.*,
                       u.full_name as preacher_name,
                       u.email as preacher_email,
                       up.avatar_url as preacher_avatar,
                       up.bio as preacher_bio,
                       ss.name as series_name,
                       ss.description as series_description,
                       (SELECT COUNT(*) FROM sermon_views sv WHERE sv.sermon_id = s.id) as view_count,
                       (SELECT COUNT(*) FROM sermon_likes sl WHERE sl.sermon_id = s.id AND sl.is_liked = 1) as like_count,
                       (SELECT COUNT(*) FROM sermon_downloads sd WHERE sd.sermon_id = s.id) as download_count
                FROM sermons s
                LEFT JOIN users u ON s.preacher_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN sermon_series ss ON s.series_id = ss.id
                WHERE s.id = ? AND s.is_published = 1
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$sermon_id]);
            
            $sermon = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get next and previous sermons in series
            if ($sermon && $sermon['series_id']) {
                $series_sql = "
                    SELECT id, title 
                    FROM sermons 
                    WHERE series_id = ? AND is_published = 1 
                    ORDER BY sermon_date ASC
                ";
                $series_stmt = $this->db->prepare($series_sql);
                $series_stmt->execute([$sermon['series_id']]);
                $series_sermons = $series_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($series_sermons as $index => $series_sermon) {
                    if ($series_sermon['id'] == $sermon_id) {
                        $sermon['prev_sermon'] = $index > 0 ? $series_sermons[$index - 1] : null;
                        $sermon['next_sermon'] = $index < count($series_sermons) - 1 ? $series_sermons[$index + 1] : null;
                        break;
                    }
                }
            }
            
            return $sermon;
            
        } catch (Exception $e) {
            error_log("Get sermon error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get distinct categories
     */
    public function getDistinctCategories() {
        try {
            $stmt = $this->db->query("
                SELECT DISTINCT category 
                FROM sermons 
                WHERE category IS NOT NULL 
                AND category != '' 
                AND is_published = 1
                ORDER BY category
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (Exception $e) {
            error_log("Get categories error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get distinct years
     */
    public function getDistinctYears() {
        try {
            $stmt = $this->db->query("
                SELECT DISTINCT YEAR(sermon_date) as year 
                FROM sermons 
                WHERE is_published = 1
                ORDER BY year DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (Exception $e) {
            error_log("Get years error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get preachers with sermon counts
     */
    public function getPreachers() {
        try {
            $stmt = $this->db->query("
                SELECT u.id, u.full_name, COUNT(s.id) as sermon_count
                FROM users u
                INNER JOIN sermons s ON u.id = s.preacher_id
                WHERE u.status = 'active' 
                AND s.is_published = 1
                GROUP BY u.id, u.full_name
                ORDER BY u.full_name
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get preachers error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get popular sermons
     */
    public function getPopularSermons($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, u.full_name as preacher_name,
                       COUNT(sv.id) as view_count
                FROM sermons s
                LEFT JOIN users u ON s.preacher_id = u.id
                LEFT JOIN sermon_views sv ON s.id = sv.sermon_id
                WHERE s.is_published = 1
                GROUP BY s.id
                ORDER BY view_count DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get popular sermons error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Record sermon view
     */
    public function recordView($sermon_id, $user_id = null) {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Check if view already recorded in last hour
            $check_stmt = $this->db->prepare("
                SELECT id FROM sermon_views 
                WHERE sermon_id = ? 
                AND ip_address = ? 
                AND viewed_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                LIMIT 1
            ");
            $check_stmt->execute([$sermon_id, $ip_address]);
            
            if ($check_stmt->rowCount() === 0) {
                $stmt = $this->db->prepare("
                    INSERT INTO sermon_views 
                    (sermon_id, user_id, ip_address, user_agent, viewed_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                return $stmt->execute([$sermon_id, $user_id, $ip_address, $user_agent]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Record sermon view error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Increment download count
     */
    public function recordDownload($sermon_id, $user_id = null, $file_type = 'audio') {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            $stmt = $this->db->prepare("
                INSERT INTO sermon_downloads 
                (sermon_id, user_id, file_type, ip_address, downloaded_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([$sermon_id, $user_id, $file_type, $ip_address]);
        } catch (Exception $e) {
            error_log("Record download error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's sermon notes
     */
    public function getUserSermonNotes($user_id, $sermon_id = null) {
        try {
            $sql = "
                SELECT sn.*, s.title as sermon_title, s.sermon_date
                FROM sermon_notes sn
                JOIN sermons s ON sn.sermon_id = s.id
                WHERE sn.user_id = ?
            ";
            
            $params = [$user_id];
            
            if ($sermon_id !== null) {
                $sql .= " AND sn.sermon_id = ?";
                $params[] = $sermon_id;
            }
            
            $sql .= " ORDER BY sn.updated_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get user sermon notes error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save user sermon notes
     */
    public function saveUserNotes($user_id, $sermon_id, $notes, $application) {
        try {
            // Check if notes already exist
            $check_stmt = $this->db->prepare("
                SELECT id FROM sermon_notes 
                WHERE user_id = ? AND sermon_id = ?
            ");
            $check_stmt->execute([$user_id, $sermon_id]);
            
            if ($check_stmt->rowCount() > 0) {
                // Update existing notes
                $stmt = $this->db->prepare("
                    UPDATE sermon_notes 
                    SET notes = ?, application = ?, updated_at = NOW()
                    WHERE user_id = ? AND sermon_id = ?
                ");
                return $stmt->execute([$notes, $application, $user_id, $sermon_id]);
            } else {
                // Insert new notes
                $stmt = $this->db->prepare("
                    INSERT INTO sermon_notes 
                    (user_id, sermon_id, notes, application, created_at, updated_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                ");
                return $stmt->execute([$user_id, $sermon_id, $notes, $application]);
            }
        } catch (Exception $e) {
            error_log("Save user notes error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get similar sermons based on tags and categories
     */
    public function getSimilarSermons($sermon_id, $limit = 5) {
        try {
            // Get current sermon's tags and category
            $current_stmt = $this->db->prepare("
                SELECT category, tags FROM sermons WHERE id = ?
            ");
            $current_stmt->execute([$sermon_id]);
            $current_sermon = $current_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$current_sermon) return [];
            
            $where = ["s.is_published = 1", "s.id != ?"];
            $params = [$sermon_id];
            
            // Build search based on tags and category
            if (!empty($current_sermon['tags'])) {
                $tags = explode(',', $current_sermon['tags']);
                $tag_conditions = [];
                foreach ($tags as $tag) {
                    $tag_conditions[] = "s.tags LIKE ?";
                    $params[] = "%" . trim($tag) . "%";
                }
                if (!empty($tag_conditions)) {
                    $where[] = "(" . implode(' OR ', $tag_conditions) . ")";
                }
            }
            
            if (!empty($current_sermon['category'])) {
                $where[] = "s.category = ?";
                $params[] = $current_sermon['category'];
            }
            
            $where_clause = implode(' AND ', $where);
            
            $sql = "
                SELECT s.*, u.full_name as preacher_name
                FROM sermons s
                LEFT JOIN users u ON s.preacher_id = u.id
                WHERE {$where_clause}
                ORDER BY s.sermon_date DESC
                LIMIT ?
            ";
            
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get similar sermons error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get sermon statistics for dashboard
     */
    public function getDashboardStats($period = 'month') {
        try {
            $stats = [];
            
            // Total sermons
            $stmt = $this->db->query("
                SELECT COUNT(*) as total FROM sermons 
                WHERE is_published = 1
            ");
            $stats['total_sermons'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total views
            $stmt = $this->db->query("
                SELECT COUNT(*) as total FROM sermon_views
            ");
            $stats['total_views'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total downloads
            $stmt = $this->db->query("
                SELECT COUNT(*) as total FROM sermon_downloads
            ");
            $stats['total_downloads'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Monthly statistics
            $period_condition = "DATE(sermon_date) >= DATE_SUB(NOW(), INTERVAL 1 {$period})";
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM sermons 
                WHERE is_published = 1 AND {$period_condition}
            ");
            $stmt->execute();
            $stats['sermons_this_period'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
            
            // Top preachers
            $stmt = $this->db->query("
                SELECT u.full_name, COUNT(s.id) as sermon_count
                FROM users u
                INNER JOIN sermons s ON u.id = s.preacher_id
                WHERE s.is_published = 1
                GROUP BY u.id
                ORDER BY sermon_count DESC
                LIMIT 5
            ");
            $stats['top_preachers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Top sermons by views
            $stmt = $this->db->query("
                SELECT s.title, s.sermon_date, COUNT(sv.id) as view_count
                FROM sermons s
                LEFT JOIN sermon_views sv ON s.id = sv.sermon_id
                WHERE s.is_published = 1
                GROUP BY s.id
                ORDER BY view_count DESC
                LIMIT 10
            ");
            $stats['top_sermons'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (Exception $e) {
            error_log("Get dashboard stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search sermons with advanced filtering
     */
    public function searchSermons($query, $filters = [], $limit = 20) {
        try {
            $where = ["s.is_published = 1"];
            $params = [];
            
            // Full-text search if available
            if ($this->hasFulltextIndex()) {
                $where[] = "MATCH(s.title, s.description, s.scripture_reference, s.tags) AGAINST(? IN NATURAL LANGUAGE MODE)";
                $params[] = $query;
            } else {
                $search = "%{$query}%";
                $where[] = "(s.title LIKE ? OR s.description LIKE ? OR s.scripture_reference LIKE ? OR s.tags LIKE ?)";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            // Apply additional filters
            if (!empty($filters['category'])) {
                $where[] = "s.category = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['preacher_id'])) {
                $where[] = "s.preacher_id = ?";
                $params[] = $filters['preacher_id'];
            }
            
            if (!empty($filters['year'])) {
                $where[] = "YEAR(s.sermon_date) = ?";
                $params[] = $filters['year'];
            }
            
            $where_clause = implode(' AND ', $where);
            
            $sql = "
                SELECT s.*, u.full_name as preacher_name,
                       MATCH(s.title, s.description, s.scripture_reference, s.tags) 
                       AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                FROM sermons s
                LEFT JOIN users u ON s.preacher_id = u.id
                WHERE {$where_clause}
                ORDER BY relevance DESC
                LIMIT ?
            ";
            
            $params[] = $query;
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Search sermons error: " . $e->getMessage());
            return $this->getSermons(array_merge($filters, ['search' => $query]), $limit, 0);
        }
    }
    
    /**
     * Check if fulltext index exists
     */
    private function hasFulltextIndex() {
        try {
            $stmt = $this->db->query("
                SHOW INDEX FROM sermons WHERE Key_name = 'fulltext_search'
            ");
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>