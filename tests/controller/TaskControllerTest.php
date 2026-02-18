<?PHP

use PHPUnit\Framework\TestCase;

define('PHPUNIT_RUNNING', true);

require_once __DIR__ . '/../../app/controller/task.php';

class TaskControllerTest extends TestCase
{
public function testHistorySetsUserTasksInSession()
{
    // Simulate POST
    $_POST['id'] = 7;

    // Mock TaskDB
    $mockTaskDB = $this->createMock(TaskDB::class);
    $mockTaskDB->method('findById')->with(7)->willReturn([
        ['id' => 145, 'user_id' => 7, 'status' => 'new']
    ]);

    // Replace real TaskDB with mock using dependency injection (or override)
    $controller = $this->getMockBuilder(AdminController::class)
                       ->onlyMethods(['createTaskDB'])
                       ->getMock();
    $controller->method('createTaskDB')->willReturn($mockTaskDB);

    // Clear session
    $_SESSION = [];

    $controller->history();

    // Assert session is set
    $this->assertArrayHasKey('user_tasks', $_SESSION);
    $this->assertEquals(145, $_SESSION['user_tasks'][0]['id']);
}

public function testHistorySetsErrorIfNoTasks()
{
    $_POST['id'] = 999; // Non-existent ID

    $mockTaskDB = $this->createMock(TaskDB::class);
    $mockTaskDB->method('findById')->with(999)->willReturn([]);

    $controller = $this->getMockBuilder(AdminController::class)
                       ->onlyMethods(['createTaskDB'])
                       ->getMock();
    $controller->method('createTaskDB')->willReturn($mockTaskDB);

    $_SESSION = [];

    $controller->history();

    $this->assertArrayHasKey('error', $_SESSION);
    $this->assertEquals('ID not found or user has no tasks.', $_SESSION['error']);
}}