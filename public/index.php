<?php
// Start the session for user authentication and session management
session_start();
// Include the Router class, which handles all incoming HTTP requests
require_once __DIR__ . '/../core/router.php';

// Create a new Router instance and execute it
// This will parse the requested URL and dispatch to the appropriate controller/action
(new Router)->run();