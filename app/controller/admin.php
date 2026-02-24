<?php
/**
 * Class AdminController
 *
 * Handles all administrative operations including:
 * - Admin dashboard rendering
 * - Viewing task history
 * - Resetting user passwords
 * - Updating user roles and manager for users
 * - Permanently deleting SCC users
 *
 * Access Control:
 * All methods require the session role to be 3 (Admin).
 *
 * Dependencies:
 * - UserDB
 * - TaskController
 * - NewUserDB
 *
 * Views:
 * - /view/mis_dash.php
 * - /view/history.php
 */
require_once __DIR__ . '/../model/task_db.php';
require_once __DIR__ . '/../model/task_model.php';
require_once __DIR__ . '/../model/scc_user_db.php';
require_once __DIR__ . '/../model/scc_user_model.php';
require_once __DIR__ . '/../model/user_db.php';
require_once __DIR__ . '/task.php';

class AdminController{

    /**
     * Displays the admin dashboard.
     *
     * Access: Admin only (role = 3)
     * 
     * Loads:
     * - All managers
     * - All users
     *
     * Renders:
     * - mis_dash.php
     *
     * return void
     */

    public function admin(): void
    {
        // Guard: only admins allowed
        if (empty($_SESSION['role']) || $_SESSION['role'] !== 3) {
            header("Location: /login");
            exit;
        }
        
        
        $userDB = new UserDB();
        $managers = $userDB->getAllManagers() ?? [];
        $users = $userDB->getAllUsers() ?? [];
        

        include __DIR__ . '/../view/mis_dash.php';
    }

    /**
     * Displays full task history.
     *
     * Access: Admin only (role = 3)
     *
     * Retrieves all tasks using TaskController.
     *
     * Renders:
     * - history.php
     *
     * return void
     */  
    public function showHistory(): void
    {
        // Only allow admin access
        if (empty($_SESSION['role']) || $_SESSION['role'] !== 3) {
            header("Location: /login");
            exit();
        }
        $allTasks = new TaskController();
        $getAllTasks = $allTasks-> getTasks() ?? [];

        include __DIR__ . '/../view/history.php';
    }    


    /**
     * Resets a user's password.
     *
     * POST Parameters:
     * - email (string)
     * - newPassword (string)
     *
     * Behavior:
     * - Hashes password using PASSWORD_DEFAULT
     * - Updates user record
     * - Redirects back to /admin
     *
     * Session Messages:
     * - Success or failure message stored in $_SESSION['error']
     *
     * return void
     */
    public function changeUserPassword(): void
    {
        $email = $_POST['email'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';

        if ($email && $newPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $userReset = (new UserDB)->changePass($email, $hashedPassword);

            if (!empty($userReset)) {
                $_SESSION['error'] = 'Password updated.';
                header("Location: /admin");
                exit;
            }
        }
        
        $_SESSION['error'] = 'ID not found or user password not reset.';
        header("Location: /admin");
        exit; 

    }

    /**
     * Updates a user's role, manager assignment, and department.
     *
     * POST Parameters:
     * - user_id (int)
     * - role (int)
     * - manager_select (string)
     * - dept (string)
     *
     * Behavior:
     * - Updates user record by ID
     * - Redirects back to /admin
     * 
     * Session Messages:
     * - failure message stored in $_SESSION['error']
     *
     * return void
     */
    public function updateUserInfo(): void
    {
        $id = (int)$_POST['user_id'];
        $role = $_POST['role'];
        $managerEmail = $_POST['manager_select'];
        $dept = $_POST['dept'];

        $newRole = (int)$role;

        $userRow = (new UserDB)->updateById($id, $newRole, $managerEmail, $dept);


        if ($userRow) {  


            header("Location: /admin");
            exit;
        }

        $_SESSION['error'] = 'Invalid user';
        header("Location: /admin");
    }
    /**
     * Permanently deletes a user from:
     * - Ticket system (UserDB)
     * - SCC system (NewUserDB)
     *
     * POST Parameters:
     * - email-delete (string)
     *
     * Behavior:
     * - Validates user exists in SCC system
     * - Deletes from both tables
     * - Redirects back to /admin
     * 
     * Session Messages:
     * - Success or failure message stored in $_SESSION['error']
     *
     * return void
     */
    public function deleteSccUserInfoPerm():void
    {
        $email = $_POST['email-delete'];

        $getSccUserId = (new NewUserDB )->findUserByEmail($email); //validate existing user
        

        if($getSccUserId){

        $deletedTicketUser = (new UserDB )->deleteUserByEmail($email);
        $deletedSccUser = (new NewUserDB )->deleteUserById($getSccUserId);
        if($deletedTicketUser && $deletedSccUser ){
            $_SESSION['error'] = 'Successfully Deleted User';
            header("Location: /admin");
            exit;
        }else{
        $_SESSION['error'] = 'Error Deleting User From A Table';
        header("Location: /admin");
        exit;


        }
        }else{       

       
        $_SESSION['error'] = 'Error Deleting User';
        header("Location: /admin");
        exit;
        }

    }

    /**
     * Permanently deletes a user from:
     * 
     * - SCC system (NewUserDB)
     *
     * POST Parameters:
     * - adminDeleteId (string) cast to (INT)
     *
     * Behavior:
     * 
     * - Deletes from scc user table by id
     * - Redirects back to /dashboard
     * 
     * Session Messages:
     * - Success or failure message stored in $_SESSION['error']
     *
     * return void
     */
    public function adminDeleteSccUserInfoOnly(): void
    {
     
    
        $id = (int)$_POST['adminDeleteId'];
        

        $userDB = new NewUserDB();
        $userInfo = $userDB->deleteUserById($id);   

    if ($userInfo) {    
        $_SESSION['result'] = "Updated Successfully.";        
       
        header("Location: /dashboard");
        exit;      
    }

        $_SESSION['error'] = 'Invalid user';
        header("Location: /dashboard");
        exit;   
    }   


}