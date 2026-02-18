<?php

// Log script start
// Good for reading logs
file_put_contents('/var/log/cron/cron.log', date('Y-m-d H:i:s') . " Script started\n", FILE_APPEND);

require_once __DIR__ . '/connection.php';
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Class Email
 * 
 * Handles sending email notifications for Support Hub tickets.
 * Sends notifications for:
 *   - New tickets (email_counter = 0, status = 'new')
 *   - Closed tickets (email_counter = 1, status = 'completed')
 * Updates email_counter to track which emails have been sent.
 */

class Email {

    protected PDO $pdo;
        /**
     * Constructor
     * Initializes database connection
     */
    public function __construct()
    {
        $this->pdo = (new Database)->pdo();
    }

    /**
     * sendEmail
     * Main method to fetch tickets and send emails.
     * - Fetches new and completed tickets
     * - Sends emails to users, managers, and MIS
     * - Updates email_counter in the database
     */

    public function sendEmail()
    {

    try{

    // Fetch all new tickets that haven't been emailed yet
    $stmt = $this->pdo->prepare("SELECT * FROM email WHERE email_counter = ? AND status = ?");
    $stmt->execute([0,'new']);
	$new_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch completed tickets that need closure emails sent
    $stmt = $this->pdo->prepare("SELECT * FROM email WHERE email_counter = ? AND status = ?");
    $stmt->execute([1,'completed']);
	$finished_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If there are no tickets to process, log and return early
    if (!$new_tickets && !$finished_tickets) {
        error_log("No new or finished tickets found to send emails for");
        return;
    }


    }
    catch(PDOException $e) {
           error_log("Database error: " . $e->getMessage());
            return null;

    }
    // --- Process new ticket notifications ---
    foreach ($new_tickets as $emailInfo) {
        $user_email = $emailInfo['user_email'] ?? '';
        $user_manager = $emailInfo['supervisor_email'] ?? '';
        $location = $emailInfo['location'] ?? '';
        $status = $emailInfo['status'] ?? '';
        $priority = $emailInfo['priority'] ?? '';
        $description = $emailInfo['user_desc'] ?? '';
        $category = $emailInfo['category'] ?? '';
        $solution = $emailInfo['solution'] ?? '';
        $ticket_number = $emailInfo['ticket_num'] ?? '';

        // Initialize PHPMailer for SMTP sending
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'mail.smtp2go.com';
        $mail->SMTPAuth   = false; 
        $mail->Port       = 2525;
        $mail->SMTPSecure = 'tls';

        // Set sender and recipients
        $mail->setFrom('mis@sccmail.org', 'MIS');
        $mail->addAddress('mis@sccmail.org');
        $mail->addAddress($user_email);

        if(!empty($user_manager)){
        $mail->addAddress($user_manager);
        }
        
            $mail->Subject = "Support Hub Ticket Received - Ticket #$ticket_number";
            $mail->Body    = "
            We've received your support ticket and are working on it.
            
            Ticket details:
            Category: $category
            Location: $location
            Priority: $priority

            Description: $description

                     

            You can log in to your account to view the ticket details and updates: http://sccapps6a/dashboard

            Thank you for using Support Hub.

            MIS Department
            Senior Connection Center
                ";    

        try {
            if($mail->send()){
                error_log("Email sent successfully");

                $updateStmt = $this->pdo->prepare("UPDATE email SET email_counter = ? WHERE id = ?");
                $updateStmt->execute([1, $emailInfo['id']]);

            }
            
            
        } catch (Exception $e) {
            error_log("Mailer error: " . $mail->ErrorInfo);
            
        }
}



    foreach ($finished_tickets as $finishedEmailInfo) {

        $user_email = $finishedEmailInfo['user_email'] ?? '';
        $user_manager = $finishedEmailInfo['supervisor_email'] ?? '';
        $location = $finishedEmailInfo['location'] ?? '';
        $status = $finishedEmailInfo['status'] ?? '';
        $priority = $finishedEmailInfo['priority'] ?? '';
        $description = $finishedEmailInfo['user_desc'] ?? '';
        $category = $finishedEmailInfo['category'] ?? '';
        $solution = $finishedEmailInfo['solution'] ?? '';
        $ticket_number = $finishedEmailInfo['ticket_num'] ?? '';


        // Initialize PHPMailer for SMTP sending
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'mail.smtp2go.com';
        $mail->SMTPAuth   = false; 
        $mail->Port       = 2525;
        $mail->SMTPSecure = 'tls';

        // Set sender and recipients
        $mail->setFrom('mis@sccmail.org', 'MIS');
        $mail->addAddress('mis@sccmail.org');
        $mail->addAddress($user_email);

        if(!empty($user_manager)){
        $mail->addAddress($user_manager);
        }
        
            $mail->Subject = "Support Hub Ticket Closed - Ticket #$ticket_number";
            $mail->Body    = "
            We've resolved your support ticket.
            
            Ticket details:
            Category: $category
            Location: $location
            Priority: $priority

            Description: $description

            Solution: $solution
           
            If you have any further issues, please don't hesitate to submit a new ticket.

            Thank you for using Support Hub.

            MIS Department
            Senior Connection Center
                ";    

        try {
            // Attempt to send email
            if($mail->send()){
                error_log("Email sent successfully");

                // Update email_counter so we don't resend the same notification
                $updateStmt = $this->pdo->prepare("UPDATE email SET email_counter = ? WHERE id = ?");
                $updateStmt->execute([2, $finishedEmailInfo['id']]);

            }
            
            
        } catch (Exception $e) {
            // Log any PHPMailer errors
            error_log("Mailer error: " . $mail->ErrorInfo);
            
        }
}


    }
}

// Execute the email cron job
$emailSender = new Email();
$emailSender->sendEmail();
?>

        