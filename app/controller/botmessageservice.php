<?php

function sendDashboardAlert($assignedTo, $ticketId, $ticketUser, $category){
    
    $webhookUrl = "https://chat.googleapis.com/v1/spaces/AAAA5AoEQpA/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=lp0jQ1t3HTWjy9CJ5fgx6l3D2Xewk-kgurpNRhJED_w";

    // 2. Format your text message (Google Chat supports basic markdown like *bold* and _italics_)
    $messageText = "Ticket has been assigned to " . $assignedTo . " for " . $ticketUser . " (Ticket ID: " . $ticketId . " Category: " . $category . ")\n";


    $payload = [
        "text" => $messageText
    ];

    // Setup the cURL request
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout after 5 seconds so it wont slow down application if Google Chat is down

    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Returns true if Google accepted the message (HTTP 200)
    return ($httpCode === 200);
}
?>