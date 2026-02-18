<?php

/**
 * Class AuthController
 *
 * Handles user authentication and session management:
 * - Login / Logout
 * - Registration
 * - Home and login page rendering
 * - Protected dashboard (ticket board)
 *
 * Responsibilities:
 * - Validate credentials
 * - Destroy sessions
 * - Redirect users based on authentication state
 *
 * Dependencies:
 * - UserDB
 * - TaskDB
 * - TaskController
 */
require_once __DIR__ . '/../model/user_db.php';
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../model/task_model.php';
require_once __DIR__ . '/../model/task_db.php';
require_once __DIR__ . '/task.php';


class AuthController
{
        /**
     * Displays the login page.
     *
     * Redirects authenticated users to /dashboard.
     */
    public function showLogin(): void
    {   
        if (isset($_SESSION['user_id'])) {
  
        header("Location: /dashboard");
        exit;
        }
        include __DIR__ . '/../view/login.php';
    }
        /**
     * Displays the registration page.
     */
    public function showRegister(): void
    {
        include __DIR__ . '/../view/register.php';
    }
            /**
     * Displays the home page.
     *
     * Redirects authenticated users to /dashboard.
     */
    public function showHome(): void
    {
        if (isset($_SESSION['user_id'])) {
  
        header("Location: /dashboard");
        exit;
        }
        include __DIR__ . '/../view/home.php';
    }


    /**
     * Processes login form submission.
     *
     * POST Parameters:
     * - email
     * - password
     *
     * Behavior:
     * - Validates user credentials
     * - Verifies password using password_verify()
     * - Initializes session variables on success
     * - Redirects to /dashboard
     *
     * On failure:
     * - Sets session error message
     * - Redirects to /login
     */
    public function login(): void
    {
        $username = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $userRow = (new UserDB)->findByName($username);


        if ($userRow && password_verify($password, $userRow['password_hash'])) {            
            
            // success            
            $_SESSION['user_id'] = $userRow['user_id'];
            $_SESSION['fname'] = $userRow['first_name'];
            $_SESSION['email'] = $userRow['email'];
            $_SESSION['dept'] = $userRow['dept'];
            $_SESSION['manager_email'] = $userRow['manager'];
            $_SESSION['role'] = $userRow['role']; //capture user's role for admin priviledges
            header("Location: /dashboard");
            exit;
        }

        $_SESSION['error'] = 'Invalid credentials';
        header("Location: /login");
    }

        /**
     * Displays the authenticated user's ticket board (dashboard).
     *
     * Access: Authenticated users only.
     *
     * Loads:
     * - User-specific tasks
     * - New tasks
     * - In-progress tasks
     * - Completed tasks
     *
     * Renders:
     * - dashboard.php
     */
    public function ticket_board(): void
    {

        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }
        $userID = $_SESSION['user_id'];

        $userTasks = (new TaskDB)->findByUser($userID);
        $_SESSION['user_task_set'] = $userTasks;

        $taskController  = new TaskController();
        $newTasks        = $taskController->getNewTasks();
        $inProgressTasks = $taskController->getInProgressTasks();
        $completedTasks  = $taskController->getCompletedTasks();
        $noTasks         = empty($newTasks) && empty($inProgressTasks) && empty($completedTasks);


        include __DIR__ . '/../view/dashboard.php';
    }

        /**
     * Logs the current user out.
     *
     * Behavior:
     * - Clears session data
     * - Destroys session
     * - Redirects to /login
     */
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        header("Location: /login");
        exit;

    }

        /**
     * Processes registration form submission.
     *
     * POST Parameters:
     * - email
     * - fname
     * - lname
     * - password
     * - confirm_password
     *
     * Behavior:
     * - Checks for existing account
     * - Validates password confirmation
     * - Hashes password securely
     * - Creates new user with default role (0)
     * - Automatically logs user in on success
     *
     * On failure:
     * - Sets session error message
     * - Redirects to /register
     */
    public function register(): void
    {
        $username = trim($_POST['email'] ?? '');
        $fname = trim($_POST['fname'] ?? '');
        $lname = trim($_POST['lname'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = 0;
        $userDB = new UserDB();
        $accountCheck = $userDB->findByName($username);
        if($accountCheck){
            $_SESSION['error'] = 'Account Already Exists';
            header("Location: /register");
            exit;
        }
        if ($password !== $confirm_password) {
            // Passwords don't match, redirect back with error
            $_SESSION['error'] = 'Passwords do not match';
            header("Location: /register");
            exit;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Create User object
        $user = new User($fname, $lname, $username, $hashedPassword, $role, null, null);

        // Add user to DB        
        $userId = $userDB->addUser($user);
        
        $userInfo = $userDB->findById($userId);
        if ($userInfo) {

            // Registration success, log in user            
            $_SESSION['user_id'] = $userInfo['user_id'];
            $_SESSION['fname'] = $userInfo['first_name'];
            $_SESSION['lname'] = $userInfo['last_name'];
            $_SESSION['email'] = $userInfo['email'];
            $_SESSION['role'] = $userInfo['role']; //capture user's role for admin priviledges
            header("Location: /dashboard");
            exit;

        } else {
            $_SESSION['error'] = 'Registration failed. Username already taken';
            header("Location: /register");
            exit;
        }
    }
    
    



}