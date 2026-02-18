<?PHP

/**
 * Controller responsible for Manager-level UI and data access.
 *
 * Responsibilities:
 * - Enforce manager/admin authorization
 * - Load department-specific user data
 * - Provide manager role user listings
 * - Display team task history
 *
 * This controller interacts with the model layer for data retrieval
 * and loads corresponding views for rendering.
 */

require_once __DIR__ . '/../model/task_db.php';
require_once __DIR__ . '/../model/task_model.php';
require_once __DIR__ . '/../model/scc_user_db.php';
require_once __DIR__ . '/../model/scc_user_model.php';
require_once __DIR__ . '/../model/user_db.php';

class ManagerController{

    /**
     * Displays the manager dashboard / user panel.
     *
     * Behavior:
     * - Restricts access to users with role >= 1 (manager/admin)
     * - Retrieves all "new users" within the manager's department
     * - Loads the manager dashboard view
     *
     * Session Requirements:
     * - $_SESSION['role'] must be >= 1
     * - $_SESSION['dept'] must be set
     *
     * Side Effects:
     * - Redirects to /login if unauthorized
     * - Loads view: /view/manager_dashboard.php
     *
     * return void
     */

    public function showUserPanel(): void
    {
        // Only allow admin access
        if (empty($_SESSION['role']) || $_SESSION['role'] < 1) {
            header("Location: /login");
            exit;
        } 
        $dept = $_SESSION['dept'];
        
        $users = (new NewUserDB)->getAllNewUsersByDept($dept) ?? [];
        include __DIR__ . '/../view/manager_dashboard.php';
    }

        /**
     * Retrieves all users with Manager role.
     *
     * Behavior:
     * - Queries the database for users assigned a manager role
     * - Returns an empty array if no results found
     *
     * return array List of manager-role users
     */
    public function getAllMangerRole(): array
    {

        $userRow = (new UserDB)->getAllManagers();
        return $userRow ?? [];          


    }
        /**
     * Displays the team task history for the logged-in manager.
     *
     * Behavior:
     * - Restricts access to users with role >= 1 (manager/admin)
     * - Retrieves all team tasks associated with the manager's email
     * - Loads the team history view
     *
     * Session Requirements:
     * - $_SESSION['role'] must be >= 1
     * - $_SESSION['email'] must be set
     *
     * Side Effects:
     * - Redirects to /login if unauthorized
     * - Loads view: /view/team_history.php
     *
     * return void
     */
    public function showTeamHistory(): void
    { 
        if (empty($_SESSION['role']) || $_SESSION['role'] < 1) {
            header("Location: /login");
            exit;
        } 
        $email = $_SESSION['email'];
        
        $getAllTasks = (new TaskDB())->getAllTeamTasks($email);
        include __DIR__ . '/../view/team_history.php';
    }  
}