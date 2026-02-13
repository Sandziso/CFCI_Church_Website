<?php
// ===================================================
// EMAIL FUNCTIONS - Christian Family Centre International
// ===================================================

// Prevent direct access
defined('ROOT_PATH') or die('Direct access not allowed');

// ====================================================================
// EMAIL CONFIGURATION
// ====================================================================

// Use PHPMailer if available
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailManager {
    private $mail;
    private $debug;
    
    public function __construct($debug = false) {
        $this->debug = $debug;
        $this->mail = new PHPMailer(true);
        $this->configure();
    }
    
    private function configure() {
        try {
            // Server settings
            if (defined('MAIL_SMTP') && MAIL_SMTP) {
                $this->mail->isSMTP();
                $this->mail->Host = MAIL_HOST;
                $this->mail->SMTPAuth = true;
                $this->mail->Username = MAIL_USERNAME;
                $this->mail->Password = MAIL_PASSWORD;
                $this->mail->SMTPSecure = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : PHPMailer::ENCRYPTION_STARTTLS;
                $this->mail->Port = MAIL_PORT;
                
                if ($this->debug) {
                    $this->mail->SMTPDebug = 2;
                }
            } else {
                $this->mail->isMail();
            }
            
            // Sender
            $this->mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $this->mail->addReplyTo(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            
            // Character set
            $this->mail->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            error_log("Email configuration error: " . $e->getMessage());
        }
    }
    
    /**
     * Send simple email
     */
    public function sendEmail($to, $subject, $body, $isHtml = true, $attachments = []) {
        try {
            // Reset recipients
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            // Add recipients
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mail->addAddress($name);
                    } else {
                        $this->mail->addAddress($email, $name);
                    }
                }
            } else {
                $this->mail->addAddress($to);
            }
            
            // Add attachments
            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    $this->mail->addAttachment($attachment['path'], $attachment['name']);
                } else {
                    $this->mail->addAttachment($attachment);
                }
            }
            
            // Content
            $this->mail->isHTML($isHtml);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            
            if (!$isHtml) {
                $this->mail->AltBody = strip_tags($body);
            }
            
            return $this->mail->send();
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using template
     */
    public function sendTemplateEmail($to, $template, $data = [], $attachments = []) {
        $templatePath = EMAIL_TEMPLATE_PATH . $template . '.php';
        
        if (!file_exists($templatePath)) {
            error_log("Email template not found: " . $template);
            return false;
        }
        
        // Extract data for template
        extract($data);
        
        // Start output buffering
        ob_start();
        include $templatePath;
        $body = ob_get_clean();
        
        // Get subject from template or use default
        $subject = $data['subject'] ?? SITE_NAME . ' - Notification';
        
        return $this->sendEmail($to, $subject, $body, true, $attachments);
    }
    
    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail($user_email, $user_name, $password = null) {
        $data = [
            'name' => $user_name,
            'email' => $user_email,
            'password' => $password,
            'site_name' => SITE_NAME,
            'site_url' => SITE_URL,
            'login_url' => SITE_URL . '/auth/login.php',
            'subject' => 'Welcome to ' . SITE_NAME
        ];
        
        return $this->sendTemplateEmail($user_email, 'welcome', $data);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($user_email, $reset_token) {
        $reset_url = SITE_URL . '/auth/reset-password.php?token=' . $reset_token;
        
        $data = [
            'reset_url' => $reset_url,
            'site_name' => SITE_NAME,
            'expiry_time' => '1 hour',
            'subject' => 'Password Reset Request - ' . SITE_NAME
        ];
        
        return $this->sendTemplateEmail($user_email, 'password_reset', $data);
    }
    
    /**
     * Send account verification email
     */
    public function sendVerificationEmail($user_email, $verification_token) {
        $verify_url = SITE_URL . '/auth/verify.php?token=' . $verification_token;
        
        $data = [
            'verify_url' => $verify_url,
            'site_name' => SITE_NAME,
            'subject' => 'Verify Your Account - ' . SITE_NAME
        ];
        
        return $this->sendTemplateEmail($user_email, 'verification', $data);
    }
    
    /**
     * Send notification email
     */
    public function sendNotification($user_email, $notification_type, $notification_data) {
        $data = array_merge([
            'site_name' => SITE_NAME,
            'site_url' => SITE_URL
        ], $notification_data);
        
        $data['subject'] = $notification_data['subject'] ?? 'Notification from ' . SITE_NAME;
        
        return $this->sendTemplateEmail($user_email, $notification_type, $data);
    }
    
    /**
     * Send bulk email
     */
    public function sendBulkEmail($recipients, $subject, $body, $isHtml = true) {
        $results = [];
        
        foreach ($recipients as $email) {
            $results[$email] = $this->sendEmail($email, $subject, $body, $isHtml);
        }
        
        return $results;
    }
    
    /**
     * Test email configuration
     */
    public function testEmail($test_email) {
        try {
            $test_body = "
                <html>
                <body>
                    <h1>Email Test from " . SITE_NAME . "</h1>
                    <p>This is a test email sent on " . date('Y-m-d H:i:s') . "</p>
                    <p>If you receive this email, your email configuration is working correctly.</p>
                </body>
                </html>
            ";
            
            return $this->sendEmail($test_email, 'Email Test - ' . SITE_NAME, $test_body);
            
        } catch (Exception $e) {
            error_log("Email test error: " . $e->getMessage());
            return false;
        }
    }
}

// ====================================================================
// HELPER FUNCTIONS
// ====================================================================

function send_email($to, $subject, $body, $isHtml = true) {
    static $emailManager = null;
    
    if ($emailManager === null) {
        $emailManager = new EmailManager(DEV_MODE);
    }
    
    return $emailManager->sendEmail($to, $subject, $body, $isHtml);
}

function send_welcome_email($user_email, $user_name, $password = null) {
    static $emailManager = null;
    
    if ($emailManager === null) {
        $emailManager = new EmailManager(DEV_MODE);
    }
    
    return $emailManager->sendWelcomeEmail($user_email, $user_name, $password);
}

function send_password_reset_email($user_email, $reset_token) {
    static $emailManager = null;
    
    if ($emailManager === null) {
        $emailManager = new EmailManager(DEV_MODE);
    }
    
    return $emailManager->sendPasswordResetEmail($user_email, $reset_token);
}

function send_verification_email($user_email, $verification_token) {
    static $emailManager = null;
    
    if ($emailManager === null) {
        $emailManager = new EmailManager(DEV_MODE);
    }
    
    return $emailManager->sendVerificationEmail($user_email, $verification_token);
}

// ====================================================================
// EMAIL TEMPLATE PATH
// ====================================================================

if (!defined('EMAIL_TEMPLATE_PATH')) {
    define('EMAIL_TEMPLATE_PATH', ROOT_PATH . '/includes/email_templates/');
}
?>