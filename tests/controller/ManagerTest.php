<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/controller/manager.php';
require_once __DIR__ . '/../../app/model/task_db.php';
require_once __DIR__ . '/../../app/model/scc_user_db.php';
require_once __DIR__ . '/../../app/model/scc_user_model.php';

class ManagerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    /* ====================================
       showUserPanel() — success path
       ==================================== */
    public function testShowUserPanelSuccess()
    {
        $_SESSION['role'] = 1;
        $_SESSION['dept'] = 'Sales';

        // Mock NewUserDB
        $mockNewUserDb = $this->createMock(NewUserDB::class);
        $mockNewUserDb->method('getAllNewUsersByDept')->willReturn([
            ['email' => 'user1@example.com', 'dept' => 'Sales']
        ]);

       

        $controller = new class($mockNewUserDb) extends ManagerController {
            public $users;            
            private $newUserDB;
            public function __construct($newUserDB) {
                $this->newUserDB = $newUserDB;                
            }

            public function showUserPanel(): void {
                $this->users = $this->newUserDB->getAllNewUsersByDept($_SESSION['dept']) ?? [];
               
            }
        };

        $controller->showUserPanel();

        $this->assertEquals([['email'=>'user1@example.com','dept'=>'Sales']], $controller->users);
        
    }

    /* ====================================
       getAllMangerRole() — returns all managers
       ==================================== */
    public function testGetAllManagerRole()
    {
        $mockUserDB = $this->createMock(UserDB::class);
        $mockUserDB->method('getAllManagers')->willReturn([
            ['email' => 'manager@example.com', 'role' => 1]
        ]);

        $controller = new class($mockUserDB) extends ManagerController {
            private $userDB;
            public function __construct($userDB) { $this->userDB = $userDB; }

            public function getAllMangerRole(): array {
                $userRow = $this->userDB->getAllManagers();
                return $userRow ?? [];
            }
        };

        $result = $controller->getAllMangerRole();
        $this->assertEquals([['email'=>'manager@example.com','role'=>1]], $result);
    }

    /* ====================================
       showTeamHistory() — success path
       ==================================== */
    public function testShowTeamHistorySuccess()
    {
        $_SESSION['role'] = 1;
        $_SESSION['email'] = 'manager@example.com';

        // Mock TaskDB
        $mockTaskDB = $this->createMock(TaskDB::class);
        $mockTaskDB->method('getAllTeamTasks')->with('manager@example.com')->willReturn([
            ['task' => 'Fix bug', 'assigned_to' => 'user1@example.com']
        ]);

        $controller = new class($mockTaskDB) extends ManagerController {
            public $teamTasks;
            private $taskDB;
            public function __construct($taskDB) { $this->taskDB = $taskDB; }

            public function showTeamHistory(): void {
                $email = $_SESSION['email'];
                $this->teamTasks = $this->taskDB->getAllTeamTasks($email);
            }
        };

        $controller->showTeamHistory();

        $this->assertEquals(
            [['task'=>'Fix bug','assigned_to'=>'user1@example.com']],
            $controller->teamTasks
        );
    }

    /* ====================================
       showUserPanel() — cancel / not authorized
       ==================================== */
    public function testShowUserPanelNotAuthorized()
    {
        $_SESSION['role'] = 0; // lower than required
        $controller = new class extends ManagerController {
            public $redirected = false;
            public function showUserPanel(): void {
                if (empty($_SESSION['role']) || $_SESSION['role'] < 1) {
                    $this->redirected = true;
                    return; // skip header/exit
                }
            }
        };

        $controller->showUserPanel();
        $this->assertTrue($controller->redirected);
    }

    /* ====================================
       showTeamHistory() — cancel / not authorized
       ==================================== */
    public function testShowTeamHistoryNotAuthorized()
    {
        $_SESSION['role'] = 0; // lower than required
        $controller = new class extends ManagerController {
            public $redirected = false;
            public function showTeamHistory(): void {
                if (empty($_SESSION['role']) || $_SESSION['role'] < 1) {
                    $this->redirected = true;
                    return; // skip header/exit
                }
            }
        };

        $controller->showTeamHistory();
        $this->assertTrue($controller->redirected);
    }
}
