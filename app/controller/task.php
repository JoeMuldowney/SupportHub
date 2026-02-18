<?php


/**
 * Handles task/ticket operations.
 *
 * Responsibilities:
 * - Retrieve tasks by status and user role
 * - Create new tickets with optional file uploads
 * - Update task status and completion
 * - Attach solutions to tasks
 * - Provide task retrieval helpers for UI
 */

require_once __DIR__ . '/../model/task_db.php';
require_once __DIR__ . '/../model/task_model.php';

class TaskController
{
        /**
     * Returns all NEW tasks visible to the logged-in user.
     *
     * Behavior:
     * - Admin role (3) receives all new tasks
     * - Other users receive only their own new tasks
     * - Returns empty array if user not logged in
     */
	
	public function getNewTasks(): array
	{
		$userID = $_SESSION['user_id'] ?? null;
        if ($userID === null) {
            return [];
        }

        $status = 'new';

        $userRole = $_SESSION['role'];
            if ($userRole === 3) {
                $rows = (new TaskDB())->getAllTasksByStatus($status);
            return $rows;
        }
        
		$rows = (new TaskDB())->getTasksByStatusAndUser($userID, $status );
		return $rows;
	}
    /**
     * Returns all IN-PROGRESS tasks visible to the logged-in user.
     * Shows all if admin role
     */
	public function getInProgressTasks(): array
	{
		$userID = $_SESSION['user_id'] ?? null;
            if ($userID === null) {
            return [];
        }
        $status = 'inProgress';
        $userRole = $_SESSION['role'];
            if ($userRole === 3) {
                $rows = (new TaskDB())->getAllTasksByStatus($status);
            return $rows;
            }
		$rows = (new TaskDB())->getTasksByStatusAndUser($userID, $status);
		return $rows;
	}
       /**
     * Returns all COMPLETED tasks visible to the logged-in user.
     * Shows all if admin role
     */
	public function getCompletedTasks(): array
	{
		$userID = $_SESSION['user_id'] ?? null;
            if ($userID === null) {
            return [];
        }
        $status = 'completed';

        $userRole = $_SESSION['role'];
            if ($userRole === 3) {
                $rows = (new TaskDB())->getAllTasksByStatus($status);
            return $rows;
        }
		$rows = (new TaskDB())->getTasksByStatusAndUser($userID, $status);
		return $rows;
	}
    
        /**
     * Creates a new ticket and optionally uploads attachments.
     *
     * Behavior:
     * - Creates new task with status "new"
     * - Validates and uploads image/PDF attachments
     * - Stores uploaded filenames in DB
     * - Writes session result and redirects
     *
     * File Upload Rules:
     * - Allowed: jpg, jpeg, png, pdf
     * - Files renamed using taskId + counter
     * - Stored in /var/lib/tickets/data/
     */
    
    public function addTicket(): void
    {
        $location = trim($_POST['location'] ?? '');
        $priority = trim($_POST['priority']);
        $status = 'new';
        $user_desc = ($_POST['desc']);
        $category = trim($_POST['taskName']);           
        $date_opened= (new DateTime())->format("Y-m-d");
        $opened_by = $_SESSION['fname'];  
        $user_id = $_SESSION['user_id'];
        $user_email = $_SESSION['email'];
        $manager = $_SESSION['manager_email'] ?? '';        
       
        $task = new task(NULL, $user_id, $location, $priority, $status, $user_desc, $date_opened, NULL, NULL, NULL, $user_email, NULL, NULL, $category, $manager);
        $taskId = (new TaskDB())->addTask($task);     
 
            // Check and validate file before saving task
            if (!empty($_FILES['image'])) {
                // Create the directory if it does not exist
                $uploadDir = '/var/lib/tickets/data/';
                $counter = 0;
                foreach ($_FILES['image']['name'] as $i => $name) {
                    if ($_FILES['image']['error'][$i] !== UPLOAD_ERR_OK) continue;
                        $allowedExtensions = ["jpg", "jpeg", "png", "pdf"];
                        $originalName = $_FILES['image']['name'][$i];
                        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                    if (in_array($fileExtension, $allowedExtensions)) {
                        // Sanitize file name
                        $safeName = preg_replace("/[^A-Za-z0-9_\-\.]/", '_', $originalName);
                        $uploadedImageName = $safeName; // Will rename after getting task ID
                    }
                    else{
                        error_log("Invalid file type: $originalName");
                        $_SESSION['error'] = "Invalid file type: $originalName. Allowed types are: " . implode(", ", $allowedExtensions);
                                      header("Location: /dashboard");
                exit;
                    }

                    $newFileName = $taskId . '-' . $counter . '_' . $uploadedImageName;   
                    

                    $targetFilePath = $uploadDir . $newFileName;
                    $tempPath = $_FILES['image']['tmp_name'][$i];

                    if (move_uploaded_file($tempPath, $targetFilePath)) {
                        error_log("Upload successful: $targetFilePath");
                        $task->addTaskImage($newFileName);
                    }
                    $counter++;
                
                }
            }
            // Save images to database
            (new TaskDB())->addTaskImages($task->getTaskImages(), $taskId);
            
            
            
            if ($taskId) {
                $_SESSION['result'] = "Ticket added successfully.";
                $_SESSION['priority'] = $priority;
                $_SESSION['location'] = $location;           
                $_SESSION['user_desc'] = $user_desc;
                header("Location: /dashboard");
                exit;
            } else {         
                $_SESSION['result'] = "Failed to add ticket";
                header("Location: /dashboard");
                exit;
            }
        
    }

        /**
     * Updates task status (new → inProgress → completed).
     *
     * Behavior:
     * - Accepts JSON request body
     * - Validates status value
     * - Updates task or closes it if completed
     * - Returns HTTP status codes
     */

	public function updateStatus(): void {
		$input = json_decode(file_get_contents('php://input'), true);
		$id = (int)($input['id'] ?? 0);
		$status = trim($input['status'] ?? '');
        $user_role = (int)$_SESSION['role'];

        
		if ($id && in_array($status, ['new', 'inProgress', 'completed'])) {
            
            // Use the logged-in user email from session
            $updatedBy = $_SESSION['email'] ?? '';
            $updatedDate = date('Y-m-d');

            if($status != 'completed'){
			    (new TaskDB())->updateTaskStatus($id, $status, $updatedDate, $updatedBy);
			    http_response_code(200);}
            else{
                (new TaskDB())->closeTaskStatus($id, $status, $updatedDate, $updatedBy);
			    http_response_code(200);
            }
            
		} else {
		    http_response_code(400);
			echo 'Invalid input';
		}
    }

        /**
     * Adds a solution/closure note to a task.
     *
     * Restrictions:
     * - Only role 3 (admin) allowed
     */
    public function addSolution(): void {

        $user_role = $_SESSION['role'];		
		$id = $_POST['ticketNum'];
		$solution = $_POST['solution'];
        $status = $_POST['ticketStatus'];
        
		if ($user_role == 3) {                       
			
            (new TaskDB())->addTaskSolution($id, $solution, $status);
            $_SESSION['solution'] = $solution;
            header("Location: /dashboard");
            exit;
        }

        $_SESSION['error'] = 'error adding solution';
        header("Location: /dashboard");
	}

    /**
     * Returns all tasks (admin view).
     */
    public function getTasks(): array{       

        $tasks = (new TaskDB)->getAllTasks();
        return $tasks;


    }
    
}
?>
