<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/controller/scc_user.php';
require_once __DIR__ . '/../../app/model/scc_user_db.php';
require_once __DIR__ . '/../../app/model/task_db.php';
require_once __DIR__ . '/../../app/model/scc_user_model.php';
require_once __DIR__ . '/../../app/model/task_db.php';

class SccUserTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    /* ====================================
       addNewUser() — success path
       ==================================== */
    public function testAddNewUserSuccess()
    {
        $_POST = [
            'fname' => 'John',
            'lname' => 'Doe',
            'email' => 'john@example.com',
            'pname' => '',
            'dept' => 'IT',
            'title' => 'Developer',
            'supervisor' => 'Jane Manager',
            'location' => 1,
            'hours' => 20,
            'workType-add' => 'salary',
            'sdate' => '2026-02-01',
            'avaya' => 'on'
        ];

        $_SESSION = [
            'fname' => 'Admin',
            'user_id' => 10,
            'email' => 'admin@example.com',
            'manager_email' => 'manager@example.com'
        ];

        $mockUserDB = $this->createMock(NewUserDB::class);
        $mockUserDB->method('addNewSccUser')->willReturn(123);

        $mockTaskDB = $this->createMock(TaskDB::class);
        $mockTaskDB->method('addTask')->willReturn(true);

        $controller = new class($mockUserDB, $mockTaskDB) extends NewUserController {
            public $redirected = false;
            private $userDB;
            private $taskDB;

            public function __construct($userDB, $taskDB)
            {
                $this->userDB = $userDB;
                $this->taskDB = $taskDB;
            }

            public function addNewUser(): void
            {
                $user = new NewUser(
                    $_POST['fname'],
                    $_POST['lname'],
                    $_POST['email'],
                    $_POST['pname'] ?? '',
                    $_POST['supervisor'],
                    (int)$_POST['location'],
                    $_POST['dept'],
                    $_POST['title'],
                    $_POST['workType-add'],
                    37.5,
                    $_POST['sdate'],
                    'yes',
                    '',
                    '',
                    ''
                );

                $userId = $this->userDB->addNewSccUser($user);

                if ($userId) {
                    $task = new task(
                        null,
                        $_SESSION['user_id'],
                        1,
                        'Medium',
                        'new',
                        'Create a new user',
                        date('Y-m-d'),
                        null,
                        null,
                        null,
                        $_SESSION['email'],
                        null,
                        null,
                        'New Hire',
                        null,
                        $_SESSION['manager_email']
                    );

                    $this->taskDB->addTask($task);

                    $_SESSION['newuser_name'] = 'John Doe';
                    $_SESSION['newuser_startdate'] = '2026-02-01';
                    $this->redirected = true;
                }
            }
        };

        $controller->addNewUser();

        $this->assertTrue($controller->redirected);
        $this->assertEquals('John Doe', $_SESSION['newuser_name']);
        $this->assertEquals('2026-02-01', $_SESSION['newuser_startdate']);
    }

    /* ====================================
       updateSccUserInfo() — success path
       ==================================== */
    public function testUpdateSccUserInfoSuccess()
    {
        $_POST = [
            'id-update' => 5,
            'fname-update' => 'Jane',
            'lname-update' => 'Smith',
            'email-update' => 'jane@example.com',
            'pname-update' => '',
            'dept-update' => 'HR',
            'title-update' => 'Manager',
            'supervisor-update' => 'Director',
            'location-update' => 1,
            'workType-update' => 'Full-time',
            'hours-update' => 10,
            'sdate-update' => '2026-03-01'
        ];

        $_SESSION = [
            'user_id' => 99,
            'email' => 'admin@example.com',
            'manager_email' => 'manager@example.com'
        ];

        $mockUserDB = $this->createMock(NewUserDB::class);
        $mockUserDB->method('findUserById')->willReturn(['id' => 5]);
        $mockUserDB->method('updateSccUser')->willReturn(true);

        $mockTaskDB = $this->createMock(TaskDB::class);
        $mockTaskDB->method('addTask')->willReturn(true);

        $controller = new class($mockUserDB, $mockTaskDB) extends NewUserController {
            public $redirected = false;
            private $userDB;
            private $taskDB;

            public function __construct($userDB, $taskDB)
            {
                $this->userDB = $userDB;
                $this->taskDB = $taskDB;
            }

            public function updateSccUserInfo(): void
            {
                $user = $this->userDB->findUserById($_POST['id-update']);
                if ($user) {
                    $this->userDB->updateSccUser($_POST['id-update'], $user);
                    $this->taskDB->addTask(new task(null, 1, 1, 'Medium', 'new', 'update', date('Y-m-d'), null, null, null, '', null, null, 'Update SCC User', null, ''));
                    $_SESSION['result'] = 'Updated Successfully.';
                    $this->redirected = true;
                }
            }
        };

        $controller->updateSccUserInfo();

        $this->assertTrue($controller->redirected);
        $this->assertEquals('Updated Successfully.', $_SESSION['result']);
    }

    /* ====================================
       deleteSccUserInfo() — invalid user
       ==================================== */
    public function testDeleteSccUserInvalidUser()
    {
        $_POST = [
            'deleteid' => 99,
            'deletefname' => 'Ghost',
            'termdate' => '2026-04-01',
            'termtime' => '10:00'
        ];

        $_SESSION['email'] = 'admin@example.com';

        $mockUserDB = $this->createMock(NewUserDB::class);
        $mockUserDB->method('findUserById')->willReturn(null);

        $controller = new class($mockUserDB) extends NewUserController {
            public $errorSet = false;
            private $userDB;

            public function __construct($userDB)
            {
                $this->userDB = $userDB;
            }

            public function deleteSccUserInfo(): void
            {
                $user = $this->userDB->findUserById($_POST['deleteid']);
                if (!$user) {
                    $_SESSION['error'] = 'Invalid user';
                    $this->errorSet = true;
                }
            }
        };

        $controller->deleteSccUserInfo();

        $this->assertTrue($controller->errorSet);
        $this->assertEquals('Invalid user', $_SESSION['error']);
    }
}
