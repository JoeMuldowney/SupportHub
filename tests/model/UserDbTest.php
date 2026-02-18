<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/model/user_db.php';

class UserDbTest extends TestCase
{
    private PDO $pdo;
    private UserDB $db;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create user table
        $this->pdo->exec("
            CREATE TABLE user (
                user_id INTEGER PRIMARY KEY AUTOINCREMENT,
                first_name TEXT,
                last_name TEXT,
                email TEXT,
                password_hash TEXT,
                role INTEGER,
                manager TEXT,
                dept TEXT
            );
        ");

        // Inject PDO
        $this->db = new class($this->pdo) extends UserDB {
            public function __construct(PDO $pdo)
            {
                $this->pdo = $pdo;
            }
        };
    }

    /* ====================================
       findByName()
       ==================================== */
    public function testFindByName()
    {
        $this->pdo->exec("
            INSERT INTO user (first_name, last_name, email)
            VALUES ('John', 'Doe', 'john@example.com')
        ");

        $user = $this->db->findByName('john@example.com');

        $this->assertNotNull($user);
        $this->assertEquals('John', $user['first_name']);
    }

    /* ====================================
       addUser() — success
       ==================================== */
    public function testAddUserSuccess()
    {
        $user = new class {
            public function getFirstName() { return 'Jane'; }
            public function getLastName() { return 'Smith'; }
            public function getUserEmail() { return 'jane@example.com'; }
            public function getHashedPwd() { return 'hashed123'; }
            public function getRole() { return 1; }
            public function getManager() { return 'manager@example.com'; }
        };

        $id = $this->db->addUser($user);

        $this->assertIsNumeric($id);

        $row = $this->pdo
            ->query("SELECT * FROM user WHERE user_id = $id")
            ->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Jane', $row['first_name']);
        $this->assertEquals('jane@example.com', $row['email']);
    }

    /* ====================================
       addUser() — duplicate email
       ==================================== */
    public function testAddUserDuplicateEmail()
    {
        $this->pdo->exec("
            INSERT INTO user (email)
            VALUES ('dup@example.com')
        ");

        $user = new class {
            public function getFirstName() { return 'Dup'; }
            public function getLastName() { return 'User'; }
            public function getUserEmail() { return 'dup@example.com'; }
            public function getHashedPwd() { return 'hash'; }
            public function getRole() { return 0; }
            public function getManager() { return null; }
        };

        $result = $this->db->addUser($user);

        $this->assertNull($result);
    }

    /* ====================================
       findById()
       ==================================== */
    public function testFindById()
    {
        $this->pdo->exec("
            INSERT INTO user (first_name, email)
            VALUES ('Alice', 'alice@example.com')
        ");

        $user = $this->db->findById(1);

        $this->assertNotNull($user);
        $this->assertEquals('Alice', $user['first_name']);
    }

    /* ====================================
       updateById()
       ==================================== */
    public function testUpdateById()
    {
        $this->pdo->exec("
            INSERT INTO user (role, manager, dept)
            VALUES (0, '', '')
        ");

        $success = $this->db->updateById(
            1,
            1,
            'manager@example.com',
            'IT'
        );

        $this->assertTrue($success);

        $row = $this->pdo
            ->query("SELECT role, manager, dept FROM user WHERE user_id = 1")
            ->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(1, $row['role']);
        $this->assertEquals('manager@example.com', $row['manager']);
        $this->assertEquals('IT', $row['dept']);
    }

    /* ====================================
       getAllUsers()
       ==================================== */
    public function testGetAllUsers()
    {
        $this->pdo->exec("
            INSERT INTO user (email)
            VALUES ('a@example.com'), ('b@example.com')
        ");

        $users = $this->db->getAllUsers();

        $this->assertCount(2, $users);
    }

    /* ====================================
       getAllManagers()
       ==================================== */
    public function testGetAllManagers()
    {
        $this->pdo->exec("
            INSERT INTO user (email, role)
            VALUES 
            ('manager@example.com', 1),
            ('user@example.com', 0)
        ");

        $managers = $this->db->getAllManagers();

        $this->assertCount(1, $managers);
        $this->assertEquals('manager@example.com', $managers[0]['email']);
    }

    /* ====================================
       changePass()
       ==================================== */
    public function testChangePass()
    {
        $this->pdo->exec("
            INSERT INTO user (email, password_hash)
            VALUES ('reset@example.com', 'old')
        ");

        $success = $this->db->changePass(
            'reset@example.com',
            'newhash'
        );

        $this->assertTrue($success);

        $hash = $this->pdo
            ->query("SELECT password_hash FROM user WHERE email = 'reset@example.com'")
            ->fetchColumn();

        $this->assertEquals('newhash', $hash);
    }

    /* ====================================
       deleteUserByEmail()
       ==================================== */
    public function testDeleteUserByEmail()
    {
        $this->pdo->exec("
            INSERT INTO user (email)
            VALUES ('delete@example.com')
        ");

        $deleted = $this->db->deleteUserByEmail('delete@example.com');

        $this->assertTrue($deleted);

        $count = $this->pdo
            ->query("SELECT COUNT(*) FROM user")
            ->fetchColumn();

        $this->assertEquals(0, $count);
    }
}
