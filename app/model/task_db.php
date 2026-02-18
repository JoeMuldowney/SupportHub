<?php
/**
 * Database operations for Tasks(tickets).
 *
 * Handles CRUD operations on the `task` table.
 * Also manages associated images and email notifications.
 *
 * Provides methods to:
 * - Find tasks by user or task ID
 * - Get all tasks or filter by status / team
 * - Add tasks and task images
 * - Update task status or solution
 */

require_once __DIR__ . '/../../core/connection.php';

class TaskDB
{
    protected PDO $pdo;
    /**
     * Initialize database connection.
    */
    public function __construct()
    {
        $this->pdo = (new Database)->pdo();
    }    

    /**
     * Get all tasks for a specific user.
     *
     * @param int $userId User ID
     * @return array|null Returns array of tasks or null if none found
     */
    public function findByUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM task WHERE user_id = ? ORDER BY Id DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get a task by its ID.
     *
     * @param int $taskId Task ID
     * @return array|null Returns task record or null if not found
     */
    public function findByTaskId(int $taskId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM task WHERE id = ? ORDER BY Id DESC');
        $stmt->execute([$taskId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all tasks including associated images.
     *
     * @return array|null Returns array of tasks, each with 'images' array
     */

    public function getAllTasks(): ?array
    {
        $stmt = $this->pdo->prepare("SELECT t.*, i.name AS images FROM task t LEFT JOIN image i ON t.id = i.ticket_id ORDER BY t.Id DESC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tasks = [];
        foreach ($rows as $row) {
            $taskId = $row['id'];
            if (!isset($tasks[$taskId])) {
                $tasks[$taskId] = $row;
                $tasks[$taskId]['images'] = [];
            }
            if ($row['images']) {
                $tasks[$taskId]['images'][] = $row['images'];
            }
        }
        return array_values($tasks);
    }

    /**
     * Get all tasks assigned to a manager's team.
     *
     * @param string $managerEmail Manager's email
     * @return array|null Returns tasks with images
     */

    public function getAllTeamTasks(string $managerEmail): ?array
    {
        $stmt = $this->pdo->prepare("SELECT t.*, i.name AS images FROM task t LEFT JOIN image i ON t.id = i.ticket_id WHERE t.manager = ? ORDER BY Id DESC");
        $stmt->execute([$managerEmail]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        $tasks = [];
        foreach ($rows as $row) {
            $taskId = $row['id'];
            if (!isset($tasks[$taskId])) {
                $tasks[$taskId] = $row;
                $tasks[$taskId]['images'] = [];
            }
            if ($row['images']) {
                $tasks[$taskId]['images'][] = $row['images'];
            }
        }
        return array_values($tasks);
    }


    /**
     * Add a new task to the database.
     *
     * Also inserts a placeholder record in `email` table for tracking.
     *
     * @param Task $task Task object
     * @return int|null Returns task ID on success, null on failure
     */

    public function addTask($task){

		try {

			$stmt = $this->pdo->prepare("
				INSERT INTO task (user_id, location, priority, status, user_desc, date_opened, opened_by, category, manager)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
			);

			$stmt->execute([
				$task->getUserID(),
				$task->getLocation(),
                $task->getPriority(),
                $task->getStatus(),
                $task->getUserDesc(),
				$task->getDateCreated()->format('Y-m-d'), // format to string
				$task->getOpenedBy(),     // format to string
				$task->getCategory(),                
                $task->getManagerEmail(),
                              
			]);
            $ticketId = $this->pdo->lastInsertId();
            
            if ($ticketId) {
                $email_counter = 0;
                $solution = '';

                $stmt = $this->pdo->prepare("
                    INSERT INTO email (user_email, supervisor_email, location, status, priority, user_desc, category, solution, email_counter, ticket_num)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $task->getOpenedBy(),
                    $task->getManagerEmail(), 
                    $task->getLocation(),                    
                    $task->getStatus(),
                    $task->getPriority(),
                    $task->getUserDesc(),
                    $task->getCategory(),
                    $solution,
                    $email_counter,
                    $ticketId, // Pass the ticket ID to the email table
                ]);             
            }

		    return $ticketId;            

		} catch (PDOException $e) {
			error_log("Database error: " . $e->getMessage());
			return null;
		}        
	

	}

    /**
     * Save images associated with a task.
     *
     * @param array $images Array of image filenames
     * @param int $taskId Task ID
     * @return bool|null Returns true on success, null on failure
     */

    public function addTaskImages($images, $taskId){

		try {
            foreach($images as $image) {
			$stmt = $this->pdo->prepare("
				INSERT INTO image (ticket_id, name)VALUES(?, ?)"
			);

			$stmt->execute([
				$taskId,
				$image,
			]);
            
            }
            return true;
		} catch (PDOException $e) {
			error_log("Database error: " . $e->getMessage());
			return null;
		}        
	

	}

    /**
     * Update task status (non-completed).
     *
     * @param int $id Task ID
     * @param string $status New status
     * @param string $updateDate Date of update
     * @param string $updatedBy User who updated
     * @return bool True if update succeeded
     */

	public function updateTaskStatus(int $id, string $status, string $updateDate, string $updatedBy): bool {	
        $stmt = $this->pdo->prepare("
        UPDATE task 
            SET status = :status, date_updated = :date_updated, updated_by = :updated_by
        WHERE id = :id
        ");

        return $stmt->execute([
            ':status'       => $status,
            ':date_updated' => $updateDate,
            ':updated_by'   => $updatedBy,
            ':id'           => $id
    ]);
    }

    /**
     * Close a task (mark as completed).
     *
     * @param int $id Task ID
     * @param string $status Completed status
     * @param string $closedDate Closing date
     * @param string $closedBy User closing the task
     * @return bool True if update succeeded
     */

    public function closeTaskStatus(int $id, string $status, string $closedDate, string $closedBy): bool {	
        $stmt = $this->pdo->prepare("
        UPDATE task 
            SET status = :status, date_closed = :date_closed, closed_by = :closed_by
        WHERE id = :id
        ");

        return $stmt->execute([
            ':status'       => $status,
            ':date_closed' => $closedDate,
            ':closed_by'   => $closedBy,
            ':id'           => $id
    ]);
    }       
    
    /**
     * Get tasks by status for a specific user.
     *
     * @param int $userID User ID
     * @param string $status Task status
     * @return array Tasks with images
     */

    public function getTasksByStatusAndUser(int $userID, string $status): array	{

		$stmt = $this->pdo->prepare("SELECT t.*, i.name AS images FROM task t LEFT JOIN image i ON t.id = i.ticket_id WHERE t.user_id = ? and t.status = ? ORDER BY Id DESC");
		$stmt->execute([$userID, $status]);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tasks = [];
        foreach ($rows as $row) {
            $taskId = $row['id'];
            if (!isset($tasks[$taskId])) {
                $tasks[$taskId] = $row;
                $tasks[$taskId]['images'] = [];
            }
            if ($row['images']) {
                $tasks[$taskId]['images'][] = $row['images'];
            }
        }
        return array_values($tasks);
	}

    /**
     * Get all tasks filtered by status.
     *
     * @param string $status Task status
     * @return array Tasks with images
     */

    public function getAllTasksByStatus(string $status): array	{

		$stmt = $this->pdo->prepare("SELECT t.*, i.name AS images FROM task t LEFT JOIN image i ON t.id = i.ticket_id WHERE t.status = ? ORDER BY Id DESC");
		$stmt->execute([$status]);
		                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tasks = [];
        foreach ($rows as $row) {
            $taskId = $row['id'];
            if (!isset($tasks[$taskId])) {
                $tasks[$taskId] = $row;
                $tasks[$taskId]['images'] = [];
            }
            if ($row['images']) {
                $tasks[$taskId]['images'][] = $row['images'];
            }
        }
        return array_values($tasks);
	}

     /**
     * Add solution to a task and update email table.
     *
     * @param int $id Task ID
     * @param string $solution Solution text
     * @param string $status Task status
     * @return bool True on success
     */

    public function addTaskSolution($id, $solution, $status): bool {
    error_log("Updating task ID $id with solution: $solution and status: $status");
    try {
        $stmt = $this->pdo->prepare("
            UPDATE task 
            SET solution = :solution
            WHERE id = :id
        ");

        $success = $stmt->execute([
            ':solution' => $solution,
            ':id'       => $id
        ]);
        
    } catch (PDOException $e) {
        // Catch any connection or query-level errors
        error_log("Database Error: " . $e->getMessage());
        exit;
    }

    try {
        $stmt = $this->pdo->prepare("
            UPDATE email 
            SET solution = :solution,
            status = :status
            WHERE ticket_num = :ticket_num
        ");

            $stmt->execute([
            ':solution' => $solution,
            ':status'   => $status,
            ':ticket_num' => $id
        ]);
    } catch (PDOException $e) {
        // Catch any connection or query-level errors
        error_log("Database Error: " . $e->getMessage());
        exit;
    }


         return $success; // Return true on success
    }

}