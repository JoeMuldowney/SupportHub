<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/model/scc_user_db.php';
require_once __DIR__ . '/../../app/model/scc_user_model.php';

/**
 * Integration-style tests for NewUserDB using SQLite memory DB
 */
class SccUserDbTest extends TestCase
{
    private PDO $pdo;
    private NewUserDB $db;

    protected function setUp(): void
    {
        // In-memory SQLite DB
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create schema
        $this->pdo->exec("
            CREATE TABLE scc_user (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fname TEXT,
                lname TEXT,
                email TEXT,
                pname TEXT,
                supervisor TEXT,
                location INTEGER,
                dept TEXT,
                title TEXT,
                position TEXT,
                hours REAL,
                sdate TEXT,
                avaya TEXT,
                shadow_agent TEXT,
                ecirts TEXT,
                dots TEXT
            );
        ");

        // Inject PDO into model via anonymous subclass
        $this->db = new class($this->pdo) extends NewUserDB {
            public function __construct(PDO $pdo)
            {
                $this->pdo = $pdo;
            }
        };
    }

    /* ====================================
       addNewSccUser() — success
       ==================================== */
    public function testAddNewSccUserSuccess()
    {
        $user = new NewUser(
            'John',
            'Doe',
            'john@example.com',
            '',
            'Jane Manager',
            1,
            'IT',
            'Developer',
            'salary',
            37.5,
            '2026-02-01',
            'yes',
            '',
            '',
            ''
        );

        $id = $this->db->addNewSccUser($user);

        $this->assertIsNumeric($id);

        $row = $this->pdo
            ->query("SELECT * FROM scc_user WHERE id = $id")
            ->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('John', $row['fname']);
        $this->assertEquals('Doe', $row['lname']);
        $this->assertEquals('john@example.com', $row['email']);
    }

    /* ====================================
       updateSccUser() — success
       ==================================== */
    public function testUpdateSccUserSuccess()
    {
        $this->pdo->exec("
            INSERT INTO scc_user (fname, lname, email)
            VALUES ('Old', 'Name', 'old@example.com')
        ");

        $user = new NewUser(
            'New',
            'Name',
            'new@example.com',
            '',
            '',
            1,
            'HR',
            'Manager',
            'Full-time',
            37.5,
            '2026-03-01',
            '',
            '',
            '',
            ''
        );

        $result = $this->db->updateSccUser(1, $user);

        $this->assertEquals(1, $result);

        $row = $this->pdo
            ->query("SELECT * FROM scc_user WHERE id = 1")
            ->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('New', $row['fname']);
        $this->assertEquals('new@example.com', $row['email']);
    }

    /* ====================================
       deleteUserById() — success
       ==================================== */
    public function testDeleteUserById()
    {
        $this->pdo->exec("
            INSERT INTO scc_user (fname, lname, email)
            VALUES ('Temp', 'User', 'temp@example.com')
        ");

        $deleted = $this->db->deleteUserById(1);

        $this->assertTrue($deleted);

        $count = $this->pdo
            ->query("SELECT COUNT(*) FROM scc_user")
            ->fetchColumn();

        $this->assertEquals(0, $count);
    }

    /* ====================================
       getAllNewUsers() — returns users
       ==================================== */
    public function testGetAllNewUsers()
    {
        $this->pdo->exec("
            INSERT INTO scc_user (fname, lname, email)
            VALUES 
            ('A', 'User', 'a@example.com'),
            ('B', 'User', 'b@example.com')
        ");

        $users = $this->db->getAllNewUsers();

        $this->assertCount(2, $users);
        $this->assertEquals('b@example.com', $users[0]['email']); // DESC order
    }

    /* ====================================
       getAllNewUsersByDept()
       ==================================== */
    public function testGetAllNewUsersByDept()
    {
        $this->pdo->exec("
            INSERT INTO scc_user (fname, dept)
            VALUES 
            ('Alice', 'IT'),
            ('Bob', 'HR')
        ");

        $users = $this->db->getAllNewUsersByDept('IT');

        $this->assertCount(1, $users);
        $this->assertEquals('Alice', $users[0]['fname']);
    }

    /* ====================================
       findUserById() — not found
       ==================================== */
    public function testFindUserByIdNotFound()
    {
        $user = $this->db->findUserById(999);
        $this->assertNull($user);
    }

    /* ====================================
       findUserByEmail() — success
       ==================================== */
    public function testFindUserByEmail()
    {
        $this->pdo->exec("
            INSERT INTO scc_user (email)
            VALUES ('lookup@example.com')
        ");

        $id = $this->db->findUserByEmail('lookup@example.com');

        $this->assertEquals(1, $id);
    }
}
