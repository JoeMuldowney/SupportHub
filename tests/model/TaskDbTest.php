<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/model/task_db.php';

/**
 * Integration tests for TaskDB using SQLite in-memory database
 */
class TaskDbTest extends TestCase
{
    private PDO $pdo;
    private TaskDB $db;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create tables
        $this->pdo->exec("
            CREATE TABLE task (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                location INTEGER,
                priority TEXT,
                status TEXT,
                user_desc TEXT,
                date_opened TEXT,
                opened_by TEXT,
                category TEXT,
                manager TEXT,
                date_updated TEXT,
                updated_by TEXT,
                date_closed TEXT,
                closed_by TEXT,
                solution TEXT
            );
        ");

        $this->pdo->exec("
            CREATE TABLE image (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_id INTEGER,
                name TEXT
            );
        ");

        // Inject PDO into TaskDB
        $this->db = new class($this->pdo) extends TaskDB {
            public function __construct(PDO $pdo)
            {
                $this->pdo = $pdo;
            }
        };
    }

    /* ====================================
       addTask() — success
       ==================================== */
    public function testAddTaskSuccess()
    {
        $task = new class {
            public function getUserID() { return 1; }
            public function getLocation() { return 1; }
            public function getPriority() { return 'Medium'; }
            public function getStatus() { return 'new'; }
            public function getUserDesc() { return 'Test task'; }
            public function getDateCreated() { return new DateTime('2026-02-01'); }
            public function getOpenedBy() { return 'admin@example.com'; }
            public function getCategory() { return 'New Hire'; }
            public function getManagerEmail() { return 'manager@example.com'; }
        };

        $id = $this->db->addTask($task);

        $this->assertIsNumeric($id);

        $row = $this->pdo
            ->query("SELECT * FROM task WHERE id = $id")
            ->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Test task', $row['user_desc']);
        $this->assertEquals('new', $row['status']);
    }

    /* ====================================
       findByUser()
       ==================================== */
    public function testFindByUser()
    {
        $this->pdo->exec("
            INSERT INTO task (user_id, status)
            VALUES (1, 'new'), (2, 'closed')
        ");

        $tasks = $this->db->findByUser(1);

        $this->assertCount(1, $tasks);
        $this->assertEquals(1, $tasks[0]['user_id']);
    }

    /* ====================================
       findByTaskId()
       ==================================== */
    public function testFindByTaskId()
    {
        $this->pdo->exec("
            INSERT INTO task (user_id, status)
            VALUES (1, 'new')
        ");

        $tasks = $this->db->findByTaskId(1);

        $this->assertNotNull($tasks);
        $this->assertEquals('new', $tasks['status']);
    }

    /* ====================================
       getAllTasks() with images
       ==================================== */
    public function testGetAllTasksWithImages()
    {
        $this->pdo->exec("
            INSERT INTO task (user_id, status)
            VALUES (1, 'new')
        ");

        $this->pdo->exec("
            INSERT INTO image (ticket_id, name)
            VALUES (1, 'img1.png'), (1, 'img2.png')
        ");

        $tasks = $this->db->getAllTasks();

        $this->assertCount(1, $tasks);
        $this->assertEquals(['img1.png', 'img2.png'], $tasks[0]['images']);
    }

    /* ====================================
       getAllTeamTasks()
       ==================================== */
    public function testGetAllTeamTasks()
    {
        $this->pdo->exec("
            INSERT INTO task (user_id, manager, status)
            VALUES (1, 'manager@example.com', 'new')
        ");

        $tasks = $this->db->getAllTeamTasks('manager@example.com');

        $this->assertCount(1, $tasks);
        $this->assertEquals('manager@example.com', $tasks[0]['manager']);
    }

    /* ====================================
       updateTaskStatus()
       ==================================== */
    public function testUpdateTaskStatus()
    {
        $this->pdo->exec("
            INSERT INTO task (status)
            VALUES ('new')
        ");

        $success = $this->db->updateTaskStatus(
            1,
            'in_progress',
            '2026-02-02',
            'admin@example.com'
        );

        $this->assertTrue($success);

        $status = $this->pdo
            ->query("SELECT status FROM task WHERE id = 1")
            ->fetchColumn();

        $this->assertEquals('in_progress', $status);
    }

    /* ====================================
       closeTaskStatus()
       ==================================== */
    public function testCloseTaskStatus()
    {
        $this->pdo->exec("
            INSERT INTO task (status)
            VALUES ('in_progress')
        ");

        $success = $this->db->closeTaskStatus(
            1,
            'closed',
            '2026-02-03',
            'admin@example.com'
        );

        $this->assertTrue($success);

        $status = $this->pdo
            ->query("SELECT status FROM task WHERE id = 1")
            ->fetchColumn();

        $this->assertEquals('closed', $status);
    }

    /* ====================================
       addTaskImages()
       ==================================== */
    public function testAddTaskImages()
    {
        $this->pdo->exec("
            INSERT INTO task (status)
            VALUES ('new')
        ");

        $result = $this->db->addTaskImages(
            ['a.png', 'b.png'],
            1
        );

        $this->assertTrue($result);

        $count = $this->pdo
            ->query("SELECT COUNT(*) FROM image WHERE ticket_id = 1")
            ->fetchColumn();

        $this->assertEquals(2, $count);
    }

    /* ====================================
       addTaskSolution()
       ==================================== */
    public function testAddTaskSolution()
    {
        $this->pdo->exec("
            INSERT INTO task (status)
            VALUES ('new')
        ");

        $success = $this->db->addTaskSolution(1, 'Fixed issue');

        $this->assertTrue($success);

        $solution = $this->pdo
            ->query("SELECT solution FROM task WHERE id = 1")
            ->fetchColumn();

        $this->assertEquals('Fixed issue', $solution);
    }
}
