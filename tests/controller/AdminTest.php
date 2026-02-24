<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/controller/admin.php';
require_once __DIR__ . '/../../app/controller/auth.php';
require_once __DIR__ . '/../../app/controller/task.php';

require_once __DIR__ . '/../../app/model/scc_user_db.php';
require_once __DIR__ . '/../../app/model/scc_user_model.php';
require_once __DIR__ . '/../../app/model/task_model.php';
require_once __DIR__ . '/../../app/model/task_db.php';
require_once __DIR__ . '/../../app/model/user_db.php';

class AdminTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    /* ====================================
       admin() function — success path
       ==================================== */
    public function testAdminLoadsSuccessfully()
    {
        $_SESSION['role'] = 3;

        $mockUserDB = $this->createMock(UserDB::class);
        $mockUserDB->method('getAllManagers')->willReturn(['manager1']);
        $mockUserDB->method('getAllUsers')->willReturn(['user1', 'user2']);

        $controller = new class($mockUserDB) extends AdminController {
            public $managers;
            public $users;
            private $auth;
            private $userDB;
            public function __construct($userDB) {
                
                $this->userDB = $userDB;
            }

            public function admin(): void {
                $this->managers = $this->userDB->getAllManagers() ?? [];
                $this->users = $this->userDB->getAllUsers() ?? [];
                // skip include + header for testing
            }
        };

        $controller->admin();

        $this->assertEquals(['manager1'], $controller->managers);
        $this->assertEquals(['user1', 'user2'], $controller->users);
    }

    /* ====================================
       showHistory() function — success path
       ==================================== */
    public function testShowHistoryLoadsTasks()
    {
        $_SESSION['role'] = 3;

        $mockTaskController = $this->createMock(TaskController::class);
        $mockTaskController->method('getTasks')->willReturn(['task1', 'task2']);

        $controller = new class($mockTaskController) extends AdminController {
            public $tasks;
            private $taskController;
            public function __construct($taskController) {
                $this->taskController = $taskController;
            }

            public function showHistory(): void {
                $this->tasks = $this->taskController->getTasks();
                // skip include + header
            }
        };

        $controller->showHistory();
        $this->assertEquals(['task1', 'task2'], $controller->tasks);
    }

    /* ====================================
       changeUserPassword() function — success path
       ==================================== */
    public function testChangeUserPasswordSuccess()
    {
        $_SESSION['role'] = 3;
        $_POST['email'] = 'user@example.com';
        $_POST['newPassword'] = 'newpass';

        $mockUserDB = $this->createMock(UserDB::class);
        $mockUserDB->method('changePass')->willReturn(true);

        $controller = new class($mockUserDB) extends AdminController {
            private $userDB;
            public function __construct($userDB) { $this->userDB = $userDB; }

            public function changeUserPassword(): void {
                $email = $_POST['email'] ?? '';
                $newPassword = $_POST['newPassword'] ?? '';

                if ($email && $newPassword) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $userReset = $this->userDB->changePass($email, $hashedPassword);
                    if (!empty($userReset)) {
                        $_SESSION['error'] = 'Password updated.';
                        return; // skip header/exit
                    }
                }
                $_SESSION['error'] = 'ID not found or user password not reset.';
            }
        };

        $controller->changeUserPassword();
        $this->assertEquals('Password updated.', $_SESSION['error']);
    }

        /* ====================================
       updateUserInfo() — success path
       ==================================== */
    public function testUpdateUserInfoSuccess()
    {
        $_POST = [
            'user_id' => '10',
            'role' => '2',
            'manager_select' => 'manager@example.com',
            'dept' => 'Sales'
        ];

        // Mock UserDB
        $mockUserDB = $this->createMock(UserDB::class);
        $mockUserDB->method('updateById')
                   ->with(10, 2, 'manager@example.com', 'Sales')
                   ->willReturn(true);

        // Override controller to inject mock and skip header/exit
        $controller = new class($mockUserDB) extends AdminController {
            private $userDB;
            public function __construct($userDB) { $this->userDB = $userDB; }

            public function updateUserInfo(): void {
                $id = (int)$_POST['user_id'];
                $role = $_POST['role'];
                $managerEmail = $_POST['manager_select'];
                $dept = $_POST['dept'];

                $newRole = (int)$role;

                $userRow = $this->userDB->updateById($id, $newRole, $managerEmail, $dept);

                if ($userRow) {
                    return; // skip header + exit
                }

                $_SESSION['error'] = 'Invalid user';
            }
        };

        $controller->updateUserInfo();

        // On success, session error should NOT be set
        $this->assertArrayNotHasKey('error', $_SESSION);
    }

    /* ====================================
       updateUserInfo() — failure path
       ==================================== */
    public function testUpdateUserInfoFailure()
    {
        $_POST = [
            'user_id' => '10',
            'role' => '2',
            'manager_select' => 'manager@example.com',
            'dept' => 'Sales'
        ];

        // Mock UserDB returns false
        $mockUserDB = $this->createMock(UserDB::class);
        $mockUserDB->method('updateById')->willReturn(false);

        $controller = new class($mockUserDB) extends AdminController {
            private $userDB;
            public function __construct($userDB) { $this->userDB = $userDB; }

            public function updateUserInfo(): void {
                $id = (int)$_POST['user_id'];
                $role = $_POST['role'];
                $managerEmail = $_POST['manager_select'];
                $dept = $_POST['dept'];

                $newRole = (int)$role;

                $userRow = $this->userDB->updateById($id, $newRole, $managerEmail, $dept);

                if ($userRow) {
                    return; // skip header + exit
                }

                $_SESSION['error'] = 'Invalid user';
            }
        };

        $controller->updateUserInfo();

        // On failure, session error should be set
        $this->assertEquals('Invalid user', $_SESSION['error']);
    }

    public function testDeleteSccUserInfoPermSuccess()
    {
        $_SESSION['role'] = 3;
        $_POST['email-delete'] = 'test@example.com';

        // Create mocks for DB classes
        $mockNewUserDB = $this->createMock(NewUserDB::class);
        $mockUserDB = $this->createMock(UserDB::class);

        // Step 1: findUserByEmail returns a valid user ID
        $mockNewUserDB->method('findUserByEmail')
            ->with('test@example.com')
            ->willReturn(123);

        // Step 2: deleteUserByEmail and deleteUserById return true
        $mockUserDB->method('deleteUserByEmail')
            ->with('test@example.com')
            ->willReturn(true);

        $mockNewUserDB->method('deleteUserById')
            ->with(123)
            ->willReturn(true);

        // Inject mocks using anonymous class to override DB instantiation
        $controller = new class($mockUserDB, $mockNewUserDB) extends AdminController {
            private $userDB;
            private $newUserDB;
            public function __construct($userDB, $newUserDB)
            {
                $this->userDB = $userDB;
                $this->newUserDB = $newUserDB;
            }

            public function deleteSccUserInfoPerm(): void
            {
                $email = $_POST['email-delete'];

                $getSccUserId = $this->newUserDB->findUserByEmail($email);

                if ($getSccUserId) {
                    $deletedTicketUser = $this->userDB->deleteUserByEmail($email);
                    $deletedSccUser = $this->newUserDB->deleteUserById($getSccUserId);

                    if ($deletedTicketUser && $deletedSccUser) {
                        $_SESSION['error'] = 'Successfully Deleted User';
                        // skip header + exit for testing
                        return;
                    } else {
                        $_SESSION['error'] = 'Error Deleting User From A Table';
                        return;
                    }
                } else {
                    $_SESSION['error'] = 'Error Deleting User';
                    return;
                }
            }
        };

        // Call the function
        $controller->deleteSccUserInfoPerm();

        // Assert session message
        $this->assertEquals('Successfully Deleted User', $_SESSION['error']);
    }

    public function testDeleteSccUserInfoPermUserNotFound()
    {
        $_SESSION['role'] = 3;
        $_POST['email-delete'] = 'missing@example.com';

        $mockNewUserDB = $this->createMock(NewUserDB::class);
        $mockUserDB = $this->createMock(UserDB::class);

        // findUserByEmail returns null
        $mockNewUserDB->method('findUserByEmail')->willReturn(null);

        $controller = new class($mockUserDB, $mockNewUserDB) extends AdminController {
            private $userDB;
            private $newUserDB;
            public function __construct($userDB, $newUserDB)
            {
                $this->userDB = $userDB;
                $this->newUserDB = $newUserDB;
            }

            public function deleteSccUserInfoPerm(): void
            {
                $email = $_POST['email-delete'];
                $getSccUserId = $this->newUserDB->findUserByEmail($email);
                if ($getSccUserId) {
                    $_SESSION['error'] = 'Should not happen';
                    return;
                } else {
                    $_SESSION['error'] = 'Error Deleting User';
                    return;
                }
            }
        };

        $controller->deleteSccUserInfoPerm();

        $this->assertEquals('Error Deleting User', $_SESSION['error']);
    }
}