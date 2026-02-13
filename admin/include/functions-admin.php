<?php
class AdminFunctions extends ChurchDB {
    
    // Additional admin-specific functions
    
    public function getRecentActivities($limit = 10) {
        try {
            $sql = "SELECT * FROM user_logs 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get recent activities error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getRecentDonations($limit = 5) {
        try {
            $sql = "SELECT d.*, u.full_name as donor_name 
                    FROM donations d 
                    LEFT JOIN users u ON d.user_id = u.id 
                    WHERE d.status = 'completed' 
                    ORDER BY d.donation_date DESC 
                    LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get recent donations error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getRecentNotifications($limit = 5) {
        try {
            $sql = "SELECT * FROM notifications 
                    WHERE user_id = ? OR user_id IS NULL 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get recent notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getDashboardStats() {
        $stats = $this->getChurchStats();
        
        // Add additional stats
        $stats['active_ministries'] = $this->getActiveMinistriesCount();
        $stats['total_events'] = $this->getTotalEventsCount();
        $stats['sermon_plays'] = $this->getTotalSermonPlays();
        $stats['monthly_growth'] = $this->getMonthlyGrowth();
        
        return $stats;
    }
    
    private function getActiveMinistriesCount() {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) FROM ministries WHERE is_active = 1");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    private function getTotalEventsCount() {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) FROM events WHERE is_active = 1");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    private function getTotalSermonPlays() {
        try {
            $stmt = $this->conn->query("SELECT SUM(views_count) FROM sermons");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    private function getMonthlyGrowth() {
        try {
            $current_month = date('Y-m');
            $prev_month = date('Y-m', strtotime('-1 month'));
            
            $stmt = $this->conn->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM users WHERE DATE_FORMAT(join_date, '%Y-%m') = ?) as current,
                    (SELECT COUNT(*) FROM users WHERE DATE_FORMAT(join_date, '%Y-%m') = ?) as previous
            ");
            $stmt->execute([$current_month, $prev_month]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['previous'] > 0) {
                return round((($result['current'] - $result['previous']) / $result['previous']) * 100, 1);
            }
            
            return $result['current'] > 0 ? 100 : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function exportData($table, $format = 'csv') {
        try {
            $sql = "SELECT * FROM $table";
            $stmt = $this->conn->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($format === 'csv') {
                return $this->generateCSV($data);
            } elseif ($format === 'json') {
                return json_encode($data);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Export data error: " . $e->getMessage());
            return null;
        }
    }
    
    private function generateCSV($data) {
        if (empty($data)) return '';
        
        $output = fopen('php://temp', 'w');
        
        // Add headers
        fputcsv($output, array_keys($data[0]));
        
        // Add data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    public function backupDatabase() {
        try {
            $backup_file = 'backups/db_backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Get all tables
            $tables = [];
            $stmt = $this->conn->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
            
            $output = "-- Database Backup\n";
            $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- CFCI Church Management System\n\n";
            
            foreach ($tables as $table) {
                // Table structure
                $output .= "--\n-- Table structure for table `$table`\n--\n";
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                
                $create_stmt = $this->conn->query("SHOW CREATE TABLE `$table`");
                $create_row = $create_stmt->fetch(PDO::FETCH_NUM);
                $output .= $create_row[1] . ";\n\n";
                
                // Table data
                $output .= "--\n-- Dumping data for table `$table`\n--\n";
                
                $data_stmt = $this->conn->query("SELECT * FROM `$table`");
                $column_count = $data_stmt->columnCount();
                
                while ($row = $data_stmt->fetch(PDO::FETCH_NUM)) {
                    $output .= "INSERT INTO `$table` VALUES (";
                    for ($i = 0; $i < $column_count; $i++) {
                        $row[$i] = addslashes($row[$i]);
                        $row[$i] = str_replace("\n", "\\n", $row[$i]);
                        if (isset($row[$i])) {
                            $output .= "'" . $row[$i] . "'";
                        } else {
                            $output .= "NULL";
                        }
                        if ($i < ($column_count - 1)) {
                            $output .= ", ";
                        }
                    }
                    $output .= ");\n";
                }
                $output .= "\n";
            }
            
            // Save to file
            if (!is_dir('backups')) {
                mkdir('backups', 0755, true);
            }
            
            file_put_contents($backup_file, $output);
            
            return $backup_file;
        } catch (PDOException $e) {
            error_log("Database backup error: " . $e->getMessage());
            return false;
        }
    }
}
?>