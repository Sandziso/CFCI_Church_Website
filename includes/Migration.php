<?php
// includes/Migration.php

class Migration {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function runAllMigrations($force = false) {
        $migrations = [
            'createUsersTable',
            'createUserProfilesTable',
            'createAdminsTable',
            'createAnnouncementsTable',
            'createEventsTable',
            'createEventAttendanceTable',
            'createDonationsTable',
            'createMinistriesTable',
            'createMinistryMembersTable',
            'createPrayerRequestsTable',
            'createSermonsTable',
            'createSermonSeriesTable',
            'createSermonPlaysTable',
            'createSermonDownloadsTable',
            'createBlogPostsTable',
            'createBlogCategoriesTable',
            'createBlogCommentsTable',
            'createGalleryTable',
            'createNotificationsTable',
            'createSessionsTable',
            'createPasswordResetTokensTable',
            'createRememberTokensTable',
            'createFailedLoginsTable',
            'createSecurityLogsTable',
            'createSettingsTable',
            'createNavigationLinksTable',
            'createFamilyGroupsTable',
            'createFamilyMembersTable',
            'createQuotesTable'
        ];

        foreach ($migrations as $migration) {
            if (method_exists($this, $migration)) {
                $this->$migration();
            }
        }

        $this->createDefaultAdmin();
        $this->insertDefaultSettings();
    }

    private function createUsersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('member', 'pastor', 'admin', 'super_admin') DEFAULT 'member',
            phone VARCHAR(20),
            gender ENUM('male', 'female', 'other'),
            date_of_birth DATE,
            marital_status ENUM('single', 'married', 'divorced', 'widowed'),
            membership_status ENUM('visitor', 'regular', 'member', 'leader', 'pastor') DEFAULT 'visitor',
            baptism_date DATE,
            profession VARCHAR(255),
            address TEXT,
            city VARCHAR(100),
            postal_code VARCHAR(20),
            country VARCHAR(100) DEFAULT 'Eswatini',
            emergency_contact_name VARCHAR(255),
            emergency_contact_phone VARCHAR(20),
            avatar_url VARCHAR(500) DEFAULT '/assets/images/default-avatar.png',
            is_active BOOLEAN DEFAULT TRUE,
            email_verified BOOLEAN DEFAULT FALSE,
            verification_token VARCHAR(100),
            last_login DATETIME,
            last_seen DATETIME,
            two_factor_enabled BOOLEAN DEFAULT FALSE,
            two_factor_secret VARCHAR(255),
            profile_completed BOOLEAN DEFAULT FALSE,
            privacy_settings JSON,
            preferences JSON,
            join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            failed_login_attempts INT DEFAULT 0,
            lock_until DATETIME NULL,
            reset_token VARCHAR(255),
            reset_token_expires DATETIME,
            INDEX idx_email (email),
            INDEX idx_role (role),
            INDEX idx_status (is_active),
            INDEX idx_last_seen (last_seen)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql);
    }

    private function createDefaultAdmin() {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = 'admin@cfci.org'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $password_hash = password_hash('Admin@123', PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO users (full_name, email, password_hash, role, is_active, email_verified) 
                VALUES ('System Administrator', 'admin@cfci.org', ?, 'super_admin', TRUE, TRUE)
            ");
            $stmt->execute([$password_hash]);
            
            $user_id = $this->pdo->lastInsertId();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO admins (user_id, role, permissions) 
                VALUES (?, 'super', 'all')
            ");
            $stmt->execute([$user_id]);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO user_profiles (user_id) VALUES (?)
            ");
            $stmt->execute([$user_id]);
        }
    }

    // Add other createTable methods from database.php here...
    // (Only showing Users table as example due to space constraints)
}
?>