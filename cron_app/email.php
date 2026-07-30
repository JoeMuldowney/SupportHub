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
    $stmt = $this->pdo->prepare("SELECT e.*, GROUP_CONCAT(g.email) AS emailGroups, GROUP_CONCAT(g.xdrive) AS xDriveFolders FROM email e LEFT JOIN groups_folders g ON e.user_id = g.user_id WHERE (e.last_notified_at < e.last_updated_at) or e.last_notified_at IS NULL GROUP BY e.id");
    $stmt->execute();
	$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);   
    
    // If there are no tickets to process, log and return early
    if (!$tickets) {
        error_log("No tickets found to send emails for");
        return;
    }

    }
    catch(PDOException $e) {
           error_log("Database error: " . $e->getMessage());
            return null;

    }
    

  // --- Process ticket notifications ---
foreach ($tickets as $emailInfo) {
    $emailId = $emailInfo['id'] ?? '';
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

    $nameOpen = explode('@', $user_email)[0];
    $displayOpenName = ucwords(str_replace('.', ' ', $nameOpen));

    $emailGroupsFormatted = !empty($emailGroups) 
        ? implode("<br>", explode(',', $emailGroups)) 
        : '';

    $xDriveFormatted = !empty($xDriveFolders) 
        ? implode("<br>", explode(',', $xDriveFolders)) 
        : '';

    // Create fresh mailer instance per ticket
    $mail = $this->createMailer();
    
    // Explicitly set primary recipient
    if (!empty($user_email)) {
        $mail->addAddress($user_email);
    }
    
    // Only add manager if different from user email
    if (!empty($user_manager) && $user_manager !== $user_email) {
        $mail->addAddress($user_manager);
    }

    // --- STATUS: NEW ---
    if ($status === 'new') {
        if ($category === 'New Hire') {
            $mail->addAddress("laurie.rodriguez@sccmail.org");
            $formatted_desc = preg_replace('/(?<!^)([A-Z][A-Za-z ]+:)/', "<br>$1", $description);
            $mail->Subject = "New Ticket From: $displayOpenName - New Hire - Ticket #$ticket_number";
            $mail->Body    = "
            <p style='font-size:16px;'>We've received your new hire support ticket and are working on it.</p>
            <p style='font-size:16px;'><b>Ticket details:</b><br>Category: $category<br>Location: $location<br>Priority: $priority</p>
            <p style='font-size:16px;'><b>Description:</b><br>$formatted_desc</p>
            <p style='font-size:16px;'><b>Email Groups:</b><br>$emailGroupsFormatted</p>
            <p style='font-size:16px;'><b>X Drive Folders:</b><br>$xDriveFormatted</p>
            <p style='font-size:16px;'>Dashboard: http://sccapps6/dashboard</p>";
        } else if ($category === 'Update SCC User') {
            $mail->addAddress("laurie.rodriguez@sccmail.org");
            $formatted_desc = preg_replace('/(?<!^)([A-Z][A-Za-z ]+:)/', "<br>$1", $description);
            $mail->Subject = "New Ticket From: $displayOpenName - Employee Update - Ticket #$ticket_number";
            $mail->Body    = "
            <p style='font-size:16px;'>We've received your update SCC user support ticket and are working on it.</p>
            <p style='font-size:16px;'><b>Ticket details:</b><br>Category: $category<br>Location: $location<br>Priority: $priority</p>
            <p style='font-size:16px;'><b>Description:</b><br>$formatted_desc</p>
            <p style='font-size:16px;'><b>Email Groups:</b><br>$emailGroupsFormatted</p>
            <p style='font-size:16px;'><b>X Drive Folders:</b><br>$xDriveFormatted</p>
            <p style='font-size:16px;'>Dashboard: http://sccapps6/dashboard</p>";
        } else if ($category === 'Termination') {
            $mail->addAddress("laurie.rodriguez@sccmail.org");
            $formatted_desc = preg_replace('/(?<!^)([A-Z][A-Za-z ]+:)/', "<br>$1", $description);
            $mail->Subject = "New Ticket From: $displayOpenName - Termination - Ticket #$ticket_number";
            $mail->Body    = "
            <p style='font-size:16px;'>We've received your termination support ticket and are working on it.</p>
            <p style='font-size:16px;'><b>Ticket details:</b><br>Category: $category<br>Location: $location<br>Priority: $priority</p>
            <p style='font-size:16px;'><b>Description:</b><br>$formatted_desc</p>
            <p style='font-size:16px;'>Dashboard: http://sccapps6/dashboard</p>";
        } else {
            $html_description = nl2br($description);
            $mail->Subject = "New Ticket From: $displayOpenName - $category - Ticket #$ticket_number";
            $mail->Body    = "
            <p style='font-size:16px;'>We've received your support ticket and are working on it.</p>
            <p style='font-size:16px;'><b>Ticket details:</b><br>Category: $category<br>Location: $location<br>Priority: $priority</p>
            <p style='font-size:16px;'><b>Description:</b><br>$html_description</p>
            <p style='font-size:16px;'>Dashboard: http://sccapps6/dashboard</p>";
        }
    } 
    // --- STATUS: IN PROGRESS ---
    else if ($status === 'inProgress') {
        $html_solution = nl2br($solution);
        
        // FIX: Added Subject Line
        $mail->Subject = "Ticket Updated: #$ticket_number - $category ($displayOpenName)";

        if (in_array($category, ['New Hire', 'Update SCC User', 'Termination'])) {
            $mail->addAddress("laurie.rodriguez@sccmail.org");
            $formatted_desc = preg_replace('/(?<!^)([A-Z][A-Za-z ]+:)/', "<br>$1", $description);
            $mail->Body    = "
            <p style='font-size:16px;'>We've updated your support ticket.</p>
            <p style='font-size:16px;'><b>Ticket details:</b><br>Category: $category<br>Location: $location<br>Priority: $priority</p>
            <p style='font-size:16px;'><b>Description:</b><br>$formatted_desc</p>
            <p style='font-size:16px;'><b>Email Groups:</b><br>$emailGroupsFormatted</p>
            <p style='font-size:16px;'><b>X Drive Folders:</b><br>$xDriveFormatted</p>
            <p style='font-size:16px;'><b>Solution / Update:</b><br>$html_solution</p>
            <p style='font-size:16px;'>Dashboard: http://sccapps6/dashboard</p>";
        } else {
            $html_description = nl2br($description);
            $mail->Body    = "
            <p style='font-size:16px;'>We've updated your support ticket.</p>
            <p style='font-size:16px;'><b>Ticket details:</b><br>Category: $category<br>Location: $location<br>Priority: $priority</p>
            <p style='font-size:16px;'><b>Description:</b><br>$html_description</p>
            <p style='font-size:16px;'><b>Solution / Update:</b><br>$html_solution</p>
            <p style='font-size:16px;'>Dashboard: http://sccapps6/dashboard</p>";
        }
    } 
    // --- STATUS: COMPLETED ---
    else if ($status === 'completed') {
        $html_solution = nl2br($solution);
        
        // FIX: Added Subject Line
        $mail->Subject = "Ticket Completed: #$ticket_number - $category ($displayOpenName)";

        if (in_array($category, ['New Hire', 'Update SCC User', 'Termination'])) {
            $mail->addAddress("laurie.rodriguez@sccmail.org");
            $formatted_desc = preg_replace('/(?<!^)([A-Z][A-Za-z ]+:)/', "<br>$1", $description);
            $mail->Body    = "
            <p style='font-size:16px;'>Your support ticket has been completed.</p>
            <p style='font-size:16px;'><b>Ticket details:</b><br>Category: $category<br>Location: $location<br>Priority: $priority</p>
            <p style='font-size:16px;'><b>Description:</b><br>$formatted_desc</p>
            <p style='font-size:16px;'><b>X Drive Folders:</b><br>$xDriveFormatted</p>
            <p style='font-size:16px;'><b>Solution:</b><br>$html_solution</p>
            <p style='font-size:16px;'>Dashboard: http://sccapps6/dashboard</p>";
        } else {
            $html_description = nl2br($description);
            $mail->Body    = "
            <p style='font-size:16px;'>Your support ticket has been completed.</p>
            <p style='font-size:16px;'><b>Ticket details:</b><br>Category: $category<br>Location: $location<br>Priority: $priority</p>
            <p style='font-size:16px;'><b>Description:</b><br>$html_description</p>
            <p style='font-size:16px;'><b>Solution:</b><br>$html_solution</p>
            <p style='font-size:16px;'>Dashboard: http://sccapps6/dashboard</p>";
        }
    }

    // Send the email and update timestamp
    try {
        if ($mail->send()) {
            error_log("Email sent successfully for Ticket #$ticket_number");
            $last_notified_at = (new DateTime())->format('Y-m-d H:i:s'); // Current timestamp
            $updateStmt = $this->pdo->prepare("UPDATE email SET last_notified_at = ? WHERE id = ?");
            $updateStmt->execute([$last_notified_at, $emailId]);
        }
    } catch (Exception $e) {
        error_log("Mailer error for Ticket #$ticket_number: " . $mail->ErrorInfo);
    }
}
    }}
// Execute the email cron job
$emailSender = new Email();
$emailSender->sendEmail();
?>