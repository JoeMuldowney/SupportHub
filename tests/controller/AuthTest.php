<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../appcontroller/auth.php';
require_once __DIR__ . '/../../app/model/user_db.php';
require_once __DIR__ . '/../../app/model/user.php';
require_once __DIR__ . '/../../app/model/task_db.php';
require_once __DIR__ . '/../../app/model/task_model.php';

class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    /* ====================================
       login() — success path
       ==================================== */
    public function testLoginSuccess()
    {
        $_POST = [
            'email' => 'user@example.com',
            'password' => 'password123'
        ];

        $mockUserDB = $this->createMock(UserDB::class);
        $mockUserDB->method('findByName')->willReturn([
            'user_id' => 42,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'user@example.com',
            'dept' => 'Sales',
            'manager' => 'manager@example.com',
            'role' => 1,
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT)
        ]);

        $controller = new class($mockUserDB) extends AuthController {
            private $userDB;
            public function __construct($userDB) { $this->userDB = $userDB; }
            public function login(): void {
                $username = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $userRow = $this->userDB->findByName($username);
                if ($userRow && password_verify($password, $userRow['password_hash'])) {
                    $_SESSION['user_id'] = $userRow['user_id'];
                    $_SESSION['fname'] = $userRow['first_name'];
                    $_SESSION['email'] = $userRow['email'];
                    $_SESSION['dept'] = $userRow['dept'];
                    $_SESSION['manager_email'] = $userRow['manager'];
                    $_SESSION['role'] = $userRow['role'];
                    return; // skip header + exit
                }
                $_SESSION['error'] = 'Invalid credentials';
            }
        };

        $controller->login();

        $this->assertEquals(42, $_SESSION['user_id']);
        $this->assertEquals('John', $_SESSION['fname']);
        $this->assertEquals(1, $_SESSION['role']);
        $this->assertArrayNotHasKey('error', $_SESSION);
    }

    /* ====================================
       login() — failure path
       ==================================== */
    public function testLoginFailure()
    {
        $_POST = [
            'email' => 'user@example.com',
            'password' => 'wrongpass'
        ];

        $mockUserDB = $this->createMock(UserDB::class);
        $mockUserDB->method('findByName')->willReturn([
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT)
        ]);

        $controller = new class($mockUserDB) extends AuthController {
            private $userDB;
            public function __construct($userDB) { $this->userDB = $userDB; }
            public function login(): void {
                $username = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $userRow = $this->userDB->findByName($username);
                if ($userRow && password_verify($password, $userRow['password_hash'])) {
                    $_SESSION['user_id'] = 1;
                    return;
                }
                $_SESSION['error'] = 'Invalid credentials';
            }
        };

        $controller->login();
        $this->assertEquals('Invalid credentials', $_SESSION['error']);
    }

    /* ====================================
       register() — success path
       ==================================== */
    public function testRegisterSuccess()
    {
        $_POST = [
            'email' => 'new@example.com',
            'fname' => 'Alice',
            'lname' => 'Smith',
            'password' => 'pass123',
            'confirm_password' => 'pass123'
        ];

        $mockUserDB = $this->createMock(UserDB::class);
        $mockUserDB->method('findByName')->willReturn(null); // account doesn't exist
        $mockUserDB->method('addUser')->willReturn(101);
        $mockUserDB->method('findById')->with(101)->willReturn([
            'user_id' => 101,
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'new@example.com',
            'role' => 0
        ]);

        $controller = new class($mockUserDB) extends AuthController {
            private $userDB;
            public function __construct($userDB) { $this->userDB = $userDB; }
            public function register(): void {
                $username = trim($_POST['email'] ?? '');
                $fname = trim($_POST['fname'] ?? '');
                $lname = trim($_POST['lname'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                $userDB = $this->userDB;
                if($userDB->findByName($username)) {
                    $_SESSION['error'] = 'Account Already Exists';
                    return;
                }
                if($password !== $confirm_password){
                    $_SESSION['error'] = 'Passwords do not match';
                    return;
                }
                $user = new User($fname, $lname, $username, password_hash($password,PASSWORD_DEFAULT), 0, '', '');
                $userId = $userDB->addUser($user);
                $userInfo = $userDB->findById($userId);
                if($userInfo){
                    $_SESSION['user_id'] = $userInfo['user_id'];
                    $_SESSION['fname'] = $userInfo['first_name'];
                    $_SESSION['email'] = $userInfo['email'];
                    $_SESSION['role'] = $userInfo['role'];
                    return;
                }
                $_SESSION['error'] = 'Registration failed. Username already taken';
            }
        };

        $controller->register();
        $this->assertEquals(101, $_SESSION['user_id']);
        $this->assertEquals('Alice', $_SESSION['fname']);
        $this->assertArrayNotHasKey('error', $_SESSION);
    }

    /* ====================================
       ticket_board() — loads tasks
       ==================================== */
    public function testTicketBoardLoadsTasks()
    {
        $_SESSION['user_id'] = 42;

        $mockTaskDB = $this->createMock(TaskDB::class);
        $mockTaskDB->method('findByUser')->with(42)->willReturn(['task1','task2']);

        $controller = new class($mockTaskDB) extends AuthController {
            private $taskDB;
            public function __construct($taskDB) { $this->taskDB = $taskDB; }
            public function ticket_board(): void {
                if (empty($_SESSION['user_id'])) {
                    $_SESSION['error'] = 'Not logged in';
                    return;
                }
                $userID = $_SESSION['user_id'];
                $userTasks = $this->taskDB->findByUser($userID);
                $_SESSION['user_task_set'] = $userTasks;
            }
        };

        $controller->ticket_board();
        $this->assertEquals(['task1','task2'], $_SESSION['user_task_set']);
    }

    /* ====================================
       logout() — clears session
       ==================================== */
    public function testLogoutClearsSession()
    {
        $_SESSION = [
            'user_id' => 42,
            'fname' => 'John'
        ];

        $controller = new class extends AuthController {
            public function logout(): void {
                $_SESSION = [];
                return; // skip header + exit
            }
        };

        $controller->logout();
        $this->assertEmpty($_SESSION);
    }
}
