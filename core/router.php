<?php
/**
 * Class Router
 *
 * Handles routing of HTTP requests to the appropriate controller and action
 * based on the request URI and method.
 */

require_once __DIR__ . '/../app/controller/auth.php';
require_once __DIR__ . '/../app/controller/task.php';
require_once __DIR__ . '/../app/controller/admin.php';
require_once __DIR__ . '/../app/controller/manager.php';
require_once __DIR__ . '/../app/controller/scc_user.php';
require_once __DIR__ . '/../app/controller/calendar.php';

class Router
{

    /**
     * Run the router
     *
     * Parses the current request URI and dispatches to the correct controller.
     * If no route matches, sends a 404 response.
     *
     * @return void
     */

    public function run()
    {
        
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Home page
        if ($uri === '/') {
            (new AuthController)->showHome();

        // Authentication
        } else if ($uri === '/login') {
            (new AuthController)->showLogin();
        } else if ($uri === '/logout') {
            (new AuthController)->logout();
        } else if ($uri ==='/register'){
            (new AuthController)->showRegister();
        } else if ($uri === '/login/submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController)->login();
        } else if ($uri === '/register/submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController)->register();
		} else if ($uri === '/dashboard') {
            (new AuthController)->ticket_board();


        // task (ticket) routes
        } else if ($uri === '/ticket/add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new TaskController)->addTicket();
        } elseif ($uri === '/ticket/updateStatus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
			(new TaskController)->updateStatus();
        } elseif ($uri === '/solution/add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
			(new TaskController)->addSolution();
        } elseif ($uri === '/admin') {

        // Admin routes
			(new AdminController)->admin();  
        } else if ($uri === '/edituser/submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController)->updateUserInfo();
        } else if ($uri === '/history') {
            (new AdminController)->showHistory();
        } else if ($uri === '/userhistory' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController)->showUserHistory();
        } else if ($uri === '/change_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController)->changeUserPassword();
        }else if ($uri === '/delete_user_perm' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController)->deleteSccUserInfoPerm();
        }else if ($uri === '/admin_delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AdminController)->adminDeleteSccUserInfoOnly();

        // New User routes
        }else if ($uri === '/add_new_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new NewUserController)->addNewUser();
        }else if ($uri === '/update_new_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new NewUserController)->updateSccUserInfo();
        }else if ($uri === '/delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new NewUserController)->deleteSccUserInfo();

        // Mangers team routes
        }else if ($uri === '/team_history') {
            (new ManagerController)->showTeamHistory();
        } else if ($uri === '/userPanel') {
            (new ManagerController)->showUserPanel();

        // Calendar route
        }else if ($uri === '/calendar/add'  && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new CalenderController)->addCalendarEvent();
        }else {
            http_response_code(404);
            echo '404 - Not Found';            
        }
    }
}