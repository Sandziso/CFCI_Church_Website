<?php
// ===================================================
// DONATION FUNCTIONS - Christian Family Centre International
// ===================================================

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// DONATION MANAGER
// ====================================================================

class DonationManager {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Process new donation
     */
    public function processDonation($donation_data) {
        try {
            // Generate unique transaction ID
            $transaction_id = 'DON-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            
            $stmt = $this->db->prepare("
                INSERT INTO donations 
                (user_id, transaction_id, amount, currency, payment_method, 
                 payment_gateway, donor_name, donor_email, donor_phone,
                 purpose, fund_type, is_recurring, recurring_frequency,
                 status, notes, metadata, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $donation_data['user_id'] ?? null,
                $transaction_id,
                $donation_data['amount'] ?? 0,
                $donation_data['currency'] ?? 'SZL',
                $donation_data['payment_method'] ?? 'online',
                $donation_data['payment_gateway'] ?? 'manual',
                $donation_data['donor_name'] ?? '',
                $donation_data['donor_email'] ?? '',
                $donation_data['donor_phone'] ?? '',
                $donation_data['purpose'] ?? 'general',
                $donation_data['fund_type'] ?? 'general',
                $donation_data['is_recurring'] ?? 0,
                $donation_data['recurring_frequency'] ?? null,
                $donation_data['status'] ?? 'pending',
                $donation_data['notes'] ?? '',
                $donation_data['metadata'] ?? null
            ]);
            
        } catch (Exception $e) {
            error_log("Process donation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get donation by ID
     */
    public function getDonation($donation_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT d.*, 
                       u.full_name as user_name,
                       u.email as user_email
                FROM donations d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.id = ?
            ");
            
            $stmt->execute([$donation_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get donation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get donation by transaction ID
     */
    public function getDonationByTransactionId($transaction_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT d.*, 
                       u.full_name as user_name,
                       u.email as user_email
                FROM donations d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.transaction_id = ?
            ");
            
            $stmt->execute([$transaction_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get donation by transaction ID error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all donations with filters
     */
    public function getDonations($filters = [], $limit = 20, $offset = 0) {
        try {
            $where = ["1=1"];
            $params = [];
            
            // Apply filters
            if (isset($filters['user_id'])) {
                $where[] = "d.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (isset($filters['status'])) {
                $where[] = "d.status = ?";
                $params[] = $filters['status'];
            }
            
            if (isset($filters['payment_method'])) {
                $where[] = "d.payment_method = ?";
                $params[] = $filters['payment_method'];
            }
            
            if (isset($filters['purpose'])) {
                $where[] = "d.purpose = ?";
                $params[] = $filters['purpose'];
            }
            
            if (isset($filters['fund_type'])) {
                $where[] = "d.fund_type = ?";
                $params[] = $filters['fund_type'];
            }
            
            if (isset($filters['date_from'])) {
                $where[] = "DATE(d.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to'])) {
                $where[] = "DATE(d.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (isset($filters['search'])) {
                $where[] = "(d.transaction_id LIKE ? OR d.donor_name LIKE ? OR d.donor_email LIKE ?)";
                $search = "%{$filters['search']}%";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            if (isset($filters['is_recurring'])) {
                $where[] = "d.is_recurring = ?";
                $params[] = $filters['is_recurring'];
            }
            
            $where_clause = implode(' AND ', $where);
            
            $stmt = $this->db->prepare("
                SELECT d.*,
                       u.full_name as user_name,
                       u.email as user_email
                FROM donations d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE $where_clause
                ORDER BY d.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get donations error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update donation status
     */
    public function updateDonationStatus($donation_id, $status, $gateway_response = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE donations 
                SET status = ?, 
                    gateway_response = ?,
                    completed_at = CASE WHEN ? = 'completed' THEN NOW() ELSE completed_at END,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([$status, $gateway_response, $status, $donation_id]);
            
        } catch (Exception $e) {
            error_log("Update donation status error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get donation statistics
     */
    public function getDonationStats($filters = []) {
        try {
            $where = ["status = 'completed'"];
            $params = [];
            
            // Apply filters
            if (isset($filters['date_from'])) {
                $where[] = "DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to'])) {
                $where[] = "DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (isset($filters['purpose'])) {
                $where[] = "purpose = ?";
                $params[] = $filters['purpose'];
            }
            
            if (isset($filters['fund_type'])) {
                $where[] = "fund_type = ?";
                $params[] = $filters['fund_type'];
            }
            
            $where_clause = implode(' AND ', $where);
            
            $stats = [];
            
            // Total amount
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as count,
                    SUM(amount) as total_amount,
                    AVG(amount) as average_amount,
                    MIN(amount) as min_amount,
                    MAX(amount) as max_amount
                FROM donations 
                WHERE $where_clause
            ");
            
            $stmt->execute($params);
            $amount_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats = array_merge($stats, $amount_stats);
            
            // Monthly breakdown
            $monthly_stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count,
                    SUM(amount) as total
                FROM donations 
                WHERE $where_clause
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12
            ");
            
            $monthly_stmt->execute($params);
            $stats['monthly_breakdown'] = $monthly_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Purpose breakdown
            $purpose_stmt = $this->db->prepare("
                SELECT 
                    purpose,
                    COUNT(*) as count,
                    SUM(amount) as total,
                    ROUND(SUM(amount) * 100.0 / (SELECT SUM(amount) FROM donations WHERE $where_clause), 2) as percentage
                FROM donations 
                WHERE $where_clause
                GROUP BY purpose 
                ORDER BY total DESC
            ");
            
            $purpose_stmt->execute($params);
            $stats['purpose_breakdown'] = $purpose_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Payment method breakdown
            $method_stmt = $this->db->prepare("
                SELECT 
                    payment_method,
                    COUNT(*) as count,
                    SUM(amount) as total
                FROM donations 
                WHERE $where_clause
                GROUP BY payment_method 
                ORDER BY total DESC
            ");
            
            $method_stmt->execute($params);
            $stats['method_breakdown'] = $method_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Top donors
            $donor_stmt = $this->db->prepare("
                SELECT 
                    donor_name,
                    donor_email,
                    COUNT(*) as donation_count,
                    SUM(amount) as total_donated
                FROM donations 
                WHERE $where_clause
                GROUP BY donor_email, donor_name
                ORDER BY total_donated DESC
                LIMIT 10
            ");
            
            $donor_stmt->execute($params);
            $stats['top_donors'] = $donor_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get donation stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Send donation receipt
     */
    public function sendDonationReceipt($donation_id) {
        try {
            $donation = $this->getDonation($donation_id);
            
            if (!$donation || $donation['status'] != 'completed') {
                return false;
            }
            
            $emailManager = new EmailManager();
            return $emailManager->sendTemplateEmail(
                $donation['donor_email'],
                'donation_receipt',
                [
                    'subject' => 'Donation Receipt - ' . SITE_NAME,
                    'transaction_id' => $donation['transaction_id'],
                    'amount' => number_format($donation['amount'], 2),
                    'currency' => $donation['currency'],
                    'date' => $donation['created_at'],
                    'purpose' => $donation['purpose'],
                    'donor_name' => $donation['donor_name'],
                    'payment_method' => $donation['payment_method'],
                    'site_name' => SITE_NAME,
                    'receipt_number' => 'REC-' . str_replace('DON-', '', $donation['transaction_id'])
                ]
            );
            
        } catch (Exception $e) {
            error_log("Send donation receipt error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recurring donations
     */
    public function getRecurringDonations($user_id = null) {
        try {
            $where = ["is_recurring = 1 AND status = 'completed'"];
            $params = [];
            
            if ($user_id) {
                $where[] = "user_id = ?";
                $params[] = $user_id;
            }
            
            $where_clause = implode(' AND ', $where);
            
            $stmt = $this->db->prepare("
                SELECT * FROM donations 
                WHERE $where_clause
                ORDER BY created_at DESC
            ");
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get recurring donations error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cancel recurring donation
     */
    public function cancelRecurringDonation($donation_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE donations 
                SET is_recurring = 0,
                    recurring_cancelled_at = NOW(),
                    updated_at = NOW()
                WHERE id = ? AND is_recurring = 1
            ");
            
            return $stmt->execute([$donation_id]);
            
        } catch (Exception $e) {
            error_log("Cancel recurring donation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Export donations to CSV
     */
    public function exportDonationsToCSV($filters = []) {
        try {
            $donations = $this->getDonations($filters, 1000, 0);
            
            if (empty($donations)) {
                return false;
            }
            
            $filename = "donations_export_" . date('Y-m-d') . ".csv";
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Headers
            fputcsv($output, [
                'Transaction ID', 'Date', 'Donor Name', 'Donor Email', 'Amount', 
                'Currency', 'Payment Method', 'Purpose', 'Status', 'Notes'
            ]);
            
            // Data
            foreach ($donations as $donation) {
                fputcsv($output, [
                    $donation['transaction_id'],
                    $donation['created_at'],
                    $donation['donor_name'],
                    $donation['donor_email'],
                    $donation['amount'],
                    $donation['currency'],
                    $donation['payment_method'],
                    $donation['purpose'],
                    $donation['status'],
                    $donation['notes']
                ]);
            }
            
            fclose($output);
            return true;
            
        } catch (Exception $e) {
            error_log("Export donations error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate donation report
     */
    public function generateDonationReport($start_date, $end_date, $report_type = 'summary') {
        try {
            $filters = [
                'date_from' => $start_date,
                'date_to' => $end_date
            ];
            
            $stats = $this->getDonationStats($filters);
            
            if ($report_type == 'detailed') {
                $donations = $this->getDonations($filters, 1000, 0);
                $stats['donations'] = $donations;
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Generate donation report error: " . $e->getMessage());
            return false;
        }
    }
}

// ====================================================================
// PAYMENT GATEWAY INTEGRATION
// ====================================================================

class PaymentGateway {
    private $gateway;
    private $config;
    
    public function __construct($gateway = 'manual') {
        $this->gateway = $gateway;
        $this->config = $this->getGatewayConfig();
    }
    
    private function getGatewayConfig() {
        // Load gateway configuration
        $config = [];
        
        switch ($this->gateway) {
            case 'paypal':
                $config = [
                    'client_id' => defined('PAYPAL_CLIENT_ID') ? PAYPAL_CLIENT_ID : '',
                    'client_secret' => defined('PAYPAL_CLIENT_SECRET') ? PAYPAL_CLIENT_SECRET : '',
                    'mode' => defined('PAYPAL_MODE') ? PAYPAL_MODE : 'sandbox'
                ];
                break;
                
            case 'stripe':
                $config = [
                    'publishable_key' => defined('STRIPE_PUBLISHABLE_KEY') ? STRIPE_PUBLISHABLE_KEY : '',
                    'secret_key' => defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '',
                    'webhook_secret' => defined('STRIPE_WEBHOOK_SECRET') ? STRIPE_WEBHOOK_SECRET : ''
                ];
                break;
                
            case 'flutterwave':
                $config = [
                    'public_key' => defined('FLW_PUBLIC_KEY') ? FLW_PUBLIC_KEY : '',
                    'secret_key' => defined('FLW_SECRET_KEY') ? FLW_SECRET_KEY : '',
                    'encryption_key' => defined('FLW_ENCRYPTION_KEY') ? FLW_ENCRYPTION_KEY : ''
                ];
                break;
        }
        
        return $config;
    }
    
    public function createPayment($payment_data) {
        switch ($this->gateway) {
            case 'paypal':
                return $this->createPayPalPayment($payment_data);
            case 'stripe':
                return $this->createStripePayment($payment_data);
            case 'flutterwave':
                return $this->createFlutterwavePayment($payment_data);
            default:
                return $this->createManualPayment($payment_data);
        }
    }
    
    private function createManualPayment($payment_data) {
        // For manual payments (bank transfer, cash, etc.)
        return [
            'success' => true,
            'payment_id' => 'MANUAL-' . time(),
            'redirect_url' => null,
            'message' => 'Please proceed with manual payment'
        ];
    }
    
    private function createPayPalPayment($payment_data) {
        // PayPal integration would go here
        return [
            'success' => false,
            'message' => 'PayPal integration not implemented'
        ];
    }
    
    private function createStripePayment($payment_data) {
        // Stripe integration would go here
        return [
            'success' => false,
            'message' => 'Stripe integration not implemented'
        ];
    }
    
    private function createFlutterwavePayment($payment_data) {
        // Flutterwave integration would go here
        return [
            'success' => false,
            'message' => 'Flutterwave integration not implemented'
        ];
    }
}

// ====================================================================
// HELPER FUNCTIONS
// ====================================================================

function process_donation($donation_data) {
    global $db;
    $donationManager = new DonationManager($db);
    return $donationManager->processDonation($donation_data);
}

function get_donation($donation_id) {
    global $db;
    $donationManager = new DonationManager($db);
    return $donationManager->getDonation($donation_id);
}

function get_donations($filters = [], $limit = 20, $offset = 0) {
    global $db;
    $donationManager = new DonationManager($db);
    return $donationManager->getDonations($filters, $limit, $offset);
}

function get_donation_stats($filters = []) {
    global $db;
    $donationManager = new DonationManager($db);
    return $donationManager->getDonationStats($filters);
}

function send_donation_receipt($donation_id) {
    global $db;
    $donationManager = new DonationManager($db);
    return $donationManager->sendDonationReceipt($donation_id);
}

function update_donation_status($donation_id, $status, $gateway_response = null) {
    global $db;
    $donationManager = new DonationManager($db);
    return $donationManager->updateDonationStatus($donation_id, $status, $gateway_response);
}

function generate_donation_report($start_date, $end_date, $report_type = 'summary') {
    global $db;
    $donationManager = new DonationManager($db);
    return $donationManager->generateDonationReport($start_date, $end_date, $report_type);
}

function export_donations($filters = []) {
      global $db;
    $donationManager = new DonationManager($db);
    return $donationManager->exportDonationsToCSV($filters);
}
?>