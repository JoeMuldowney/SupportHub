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
    protected string $mail_host;
    protected int $mail_port;
    
    /**
     * Constructor
     * 
     * Initializes database connection and reads SMTP configuration from secrets.
     */

    public function __construct()
    {
        $this->pdo = (new Database)->pdo();
        $this->mail_host = $this->readSecret('/run/secrets/mail_host');
        $this->mail_port = (int) $this->readSecret('/run/secrets/mail_port');
    }

    private function readSecret(string $path): string
    {
        if (!file_exists($path)) {
            throw new Exception("Secret file not found: $path");
        }

        return trim(file_get_contents($path));
    }

    //  PHPMailer for SMTP sending
    private function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $this->mail_host;
        $mail->SMTPAuth   = false; 
        $mail->Port       = $this->mail_port;
        $mail->SMTPSecure = 'tls';
        $mail->isHTML(true);

        // Set sender and recipients
        $mail->setFrom('mis@sccmail.org', 'MIS');
        $mail->addAddress('mis@sccmail.org');

        return $mail;
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

    // Fetch all new tickets that haven't been emailed yet add group and xDrive info
    $stmt = $this->pdo->prepare("SELECT e.*, GROUP_CONCAT(g.email) AS emailGroups, GROUP_CONCAT(g.xdrive) AS xDriveFolders FROM email e LEFT JOIN groups_folders g ON e.user_id = g.user_id WHERE e.email_counter = ? AND e.status = ? GROUP BY e.id");
    $stmt->execute([0,'new']);
	$new_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch completed tickets that need closure emails sent
    $stmt = $this->pdo->prepare("SELECT e.* , GROUP_CONCAT(g.email) AS emailGroups, GROUP_CONCAT(g.xdrive) AS xDriveFolders FROM email e LEFT JOIN groups_folders g ON e.user_id = g.user_id WHERE e.email_counter = ? AND e.status = ? GROUP BY e.id");
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
        $emailGroups = $emailInfo['emailGroups'] ?? '';
        $xDriveFolders = $emailInfo['xDriveFolders'] ?? '';

        

        $emailGroupsFormatted = '';
        if (!empty($emailGroups)) {
            $groupsArray = explode(',', $emailGroups);
            $emailGroupsFormatted = implode("<br>", $groupsArray);
        }

        $xDriveFormatted = '';
        if (!empty($xDriveFolders)) {
            $foldersArray = explode(',', $xDriveFolders);
            $xDriveFormatted = implode("<br>", $foldersArray);
        }

        $mail = $this->createMailer();
        $mail->addAddress($user_email);

        if(!empty($user_manager)){
            $mail->addAddress($user_manager);
        }

            if($category === 'New Hire'){
                $formatted_desc = preg_replace('/(?<!^)([A-Z][A-Za-z ]+:)/', "<br>$1", $description);
                //$mail->addAddress('laurie.rodriguez@sccmail.org');
                $mail->Subject = "Support Hub New Hire Ticket Received - Ticket #$ticket_number";
                $mail->Body    = "
<p style='font-size:16px;'>
    We've received your new hire support ticket and are working on it.
</p>
<p style='font-size:16px;'>
<span style='font-size:18px; font-weight:bold;'>Ticket details:</span><br>
    Category: $category<br>
    Location: $location<br>
    Priority: $priority
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Description:</span><br>
    <span style='font-size:16px;'>$formatted_desc</span>
</p>
<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Email Groups:</span><br>
    <span style='font-size:16px;'>$emailGroupsFormatted</span>
</p>
<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>X Drive Folders:</span><br>
    <span style='font-size:16px;'>$xDriveFormatted</span>
</p>

<p style='font-size:16px;'>
You can log in to your account to view the ticket details and updates: http://sccapps6a/dashboard
</p>
<p style='font-size:16px;'>
Thank you for using Support Hub.
</p>
<p style='font-size:16px;'>
MIS Department
Senior Connection Center
</p>";   

            }
            else if($category === 'Update SCC User'){
                //$mail->addAddress('laurie.rodriguez@sccmail.org');
                $formatted_desc = preg_replace('/(?<!^)([A-Z][A-Za-z ]+:)/', "<br>$1", $description);
                $mail->Subject = "Support Hub Update SCC User Ticket Received - Ticket #$ticket_number";
                $mail->Body    = "
<p style='font-size:16px;'>
We've received your update SCC user support ticket and are working on it.
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Ticket details:</span><br>
    Category: $category<br>
    Location: $location<br>
    Priority: $priority
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Description:</span><br>
    <span style='font-size:16px;'>$formatted_desc</span>
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Email Groups:</span><br>
    <span style='font-size:16px;'>$emailGroupsFormatted</span>
</p>
<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>X Drive Folders:</span><br>
    <span style='font-size:16px;'>$xDriveFormatted</span>
</p>

            

<p style='font-size:16px;'>
You can log in to your account to view the ticket details and updates: http://sccapps6a/dashboard
</p>
<p style='font-size:16px;'>
Thank you for using Support Hub.
</p>
<p style='font-size:16px;'>
MIS Department
Senior Connection Center
</p>";  

            }
            else if($category === 'Termination'){
                //$mail->addAddress('laurie.rodriguez@sccmail.org');
                $formatted_desc = preg_replace('/(?<!^)([A-Z][A-Za-z ]+:)/', "<br>$1", $description);
                $mail->Subject = "Support Hub Termination Ticket Received - Ticket #$ticket_number";
                $mail->Body    = "
<p style='font-size:16px;'>
We've received your termination support ticket and are working on it.
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Ticket details:</span><br>
    Category: $category<br>
    Location: $location<br>
    Priority: $priority
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Description:</span><br>
    <span style='font-size:16px;'>$formatted_desc</span>
</p>

            
<p style='font-size:16px;'>
You can log in to your account to view the ticket details and updates: http://sccapps6a/dashboard
</p>
<p style='font-size:16px;'>
Thank you for using Support Hub.
</p>
<p style='font-size:16px;'>
MIS Department
Senior Connection Center
</p>";    

            }
            else{
                $mail->Subject = "Support Hub Ticket Received - Ticket #$ticket_number";
                $mail->Body    = "
<p style='font-size:16px;'>
We've received your support ticket and are working on it.
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Ticket details:</span><br>
    Category: $category<br>
    Location: $location<br>
    Priority: $priority
</p>

<p style='font-size:16px;'>
    <span style='font-size:16px; font-weight:bold;'>Description:</span><br>
    <span style='font-size:16px;'>$description</span>
</p>
        
<p style='font-size:16px;'>
You can log in to your account to view the ticket details and updates: http://sccapps6a/dashboard
</p>
<p style='font-size:16px;'>
Thank you for using Support Hub.
</p>
<p style='font-size:16px;'>
MIS Department
Senior Connection Center
</p>";  
                }   

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


        $mail = $this->createMailer();

        $mail->addAddress($user_email);

        if(!empty($user_manager)){
            $mail->addAddress($user_manager);
        }
        $mail->Subject = "Support Hub Ticket Closed - Ticket #$ticket_number";
        if($category === 'New Hire' || $category === 'Update SCC User' || $category === 'Termination'){
            //$mail->addAddress('laurie.rodriguez@sccmail.org');
                            $mail->Body    = "
<p style='font-size:16px;'>
We've received your update SCC user support ticket and are working on it.
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Ticket details:</span><br>
    Category: $category<br>
    Location: $location<br>
    Priority: $priority
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Description:</span><br>
    <span style='font-size:16px;'>$formatted_desc</span>
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Email Groups:</span><br>
    <span style='font-size:16px;'>$emailGroupsFormatted</span>
</p>
<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>X Drive Folders:</span><br>
    <span style='font-size:16px;'>$xDriveFormatted</span>
</p>

            

<p style='font-size:16px;'>
You can log in to your account to view the ticket details and updates: http://sccapps6a/dashboard
</p>
<p style='font-size:16px;'>
Thank you for using Support Hub.
</p>
<p style='font-size:16px;'>
MIS Department
Senior Connection Center
</p>";  
}else {
                 

            $mail->Body    = "
<p style='font-size:16px;'>
We've resolved your support ticket.
</p>

<p style='font-size:16px;'>
    <span style='font-size:18px; font-weight:bold;'>Ticket details:</span><br>
    Category: $category<br>
    Location: $location<br>
    Priority: $priority
</p>
<p style='font-size:16px;'>
    <span style='font-size:16px; font-weight:bold;'>Description:</span><br>
    <span style='font-size:16px;'>$description</span>
</p>

<p style='font-size:16px;'>
    <span style='font-size:16px; font-weight:bold;'>Solution:</span><br>
    <span style='font-size:16px;'>$solution</span>
</p>

<p style='font-size:16px;'>
You can log in to your account to view the ticket details and updates: http://sccapps6a/dashboard
</p>
<p style='font-size:16px;'>
Thank you for using Support Hub.
</p>
<p style='font-size:16px;'>
MIS Department
Senior Connection Center
</p>";    
}
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

        