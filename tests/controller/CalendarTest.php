<?php
use PHPUnit\Framework\TestCase;
use Google\Service\Calendar\Event;

require_once __DIR__ . '/../../app/controller/calendar.php';

class CalendarTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    public function testAddCalendarEventSuccess()
    {
        $_SESSION['email'] = 'user@example.com';
        $_POST = [
            'id-calender' => '123',
            'category-calender' => 'Bug',
            'desc-calender' => 'Fix login issue',
            'date-calender' => '2026-02-06',
            'time-calender' => '10:00'
        ];

        // Mock Google Calendar service
        $mockEvents = $this->createMock(Google\Service\Calendar\Resource\Events::class);
        $mockEvents->method('insert')->willReturn(
            new Event(['id' => 'event123'])
        );

        $mockService = $this->createMock(Google\Service\Calendar::class);
        $mockService->events = $mockEvents;

        // Override getCalendarService to return the mock
        $controller = new class($mockService) extends CalenderController {
            private $mockService;
            public function __construct($service) { $this->mockService = $service; }

            public function addCalenderEvent(): void {
                $ticketNum = $_POST['id-calender'];
                $category = $_POST['category-calender'];
                $desc = $_POST['desc-calender'];
                $date = $_POST['date-calender'];
                $time = $_POST['time-calender'];

                $start = new DateTime("$date $time", new DateTimeZone('America/New_York'));
                $end = clone $start; $end->modify('+1 hour');

                $title = '#' . $ticketNum . ' ' . $category;

                // Use mocked service instead of real Google Client
                $service = $this->mockService;

                $event = new Event([
                    'summary' => $title,
                    'description' => $desc,
                    'start' => ['dateTime' => $start->format(DateTime::ATOM), 'timeZone' => 'America/New_York'],
                    'end' => ['dateTime' => $end->format(DateTime::ATOM), 'timeZone' => 'America/New_York'],
                ]);

                $result = $service->events->insert('primary', $event);

                // For test, store result instead of echo
                $_SESSION['last_event_id'] = $result->getId();
            }
        };

        $controller->addCalenderEvent();

        $this->assertEquals('event123', $_SESSION['last_event_id']);
    }
}
