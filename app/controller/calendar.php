<?php

/**
 * Controller responsible for Google Calendar integrations.
 *
 * Responsibilities:
 * - Create calendar events tied to internal ticketing data
 * - Authenticate using Google Service Account
 * - Impersonate the logged-in Workspace user
 *
 * Notes:
 * - Requires valid Google Service Account JSON
 * - Requires domain-wide delegation enabled in Google Admin
 * - Uses session email for calendar ownership
 */

require __DIR__ . '/../../vendor/autoload.php';

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;




class CalenderController{
    /**
     * Creates a Google Calendar event from POSTed ticket data.
     *
     * Expected POST fields:
     * - id-calender   : Ticket number
     * - category-calender : Event/category label
     * - desc-calender : Event description
     * - date-calender : Event date (YYYY-MM-DD)
     * - time-calender : Event start time (HH:MM)
     *
     * Behavior:
     * - Combines date + time into DateTime object (America/New_York)
     * - Automatically sets event duration to 1 hour
     * - Authenticates using service account with domain impersonation
     * - Creates event in the logged-in user's primary calendar
     *
     * Session Requirements:
     * - $_SESSION['email'] must be set (Workspace user to impersonate)
     *
     * Side Effects:
     * - Creates event in Google Calendar
     * - Outputs JSON response (success or error)
     *
     * Security Notes:
     * - Assumes POST input is trusted/validated upstream
     * - Requires secure storage of service account JSON
     */    



public function addCalendarEvent(): void {

    $ticketNum = $_POST['id-calender'];
    $category = $_POST['category-calender'];
    $desc = $_POST['desc-calender'];
    $date = $_POST['date-calender'];
    $time = $_POST['time-calender'];

    // Combine date + time
    $start = new DateTime("$date $time", new DateTimeZone('America/New_York'));

    // End time = 1 hour later
    $end = clone $start;         // clone to keep original
    $end->modify('+1 hour');

    // Format ISO 8601
    $datetimeStart = $start->format(DateTime::ATOM); // "2026-02-05T10:30:00-05:00"
    $datetimeEnd   = $end->format(DateTime::ATOM);   // "2026-02-05T11:30:00-05:00"


    $title = '#' . $ticketNum . ' ' . $category;

    /**
    * Reads a secret file from disk.
    * Used for loading credentials if needed.
    */

    if (!function_exists('readSecret')) {
            function readSecret(string $path): string {
                if (!file_exists($path)) {
                    throw new Exception("Secret file not found: $path");
                }
                return trim(file_get_contents($path));
            }
        }

    $KEY_FILE_LOCATION = '/run/secrets/calendar.json'; // Path to service account JSON
    $USER_TO_IMPERSONATE = $_SESSION['email'];      // Your Workspace email
    


    /**
     * Builds an authenticated Google Calendar service using
     * service account + domain-wide delegation impersonation.
     */

    function getCalendarService($keyFile, $impersonateEmail) {
        $client = new Client();
        
        // 1. Load the Service Account JSON
        $client->setAuthConfig($keyFile);
        
        // 2. Set the Scopes (must match your Admin Console authorization)
        $client->addScope(Calendar::CALENDAR);
        
        // 3. Impersonate your user
        // This gives the service account YOUR rights
        $client->setSubject($impersonateEmail);
        
        return new Calendar($client);
    }

    try{

         $service = getCalendarService($KEY_FILE_LOCATION, $USER_TO_IMPERSONATE);
        // Define the Event
            $event = new Event([
                'summary'     => $title,
                'location'    => 'Online',
                'description' => $desc,
                'start' => new EventDateTime([
                    'dateTime' => $datetimeStart, // Format: YYYY-MM-DDTHH:MM:SS-Offset
                    'timeZone' => 'America/New_York',
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $datetimeEnd,   // Use the precomputed end time
                    'timeZone' => 'America/New_York',
                ]),
            ]);

        
            // 'primary' refers to the calendar of the impersonated user
            $calendarId = 'primary';
            $event = $service->events->insert($calendarId, $event);
            
            echo json_encode(['status' => 'Event Created', 'eventId' => $event->getId()]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'Error', 'message' => $e->getMessage()]);
        }
    }

}