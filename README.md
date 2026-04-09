# SupportHub
A robust IT ticket management system designed to streamline daily operations and team communication.
Allows employees to submit IT requests, while providing managers and admins with powerful tools to track, update, and automate workflow via a Kanban-style interface.


## Table of Contents
- [Features](#features)
- [Technologies Used](#technologies)

# **Features**

1. **RESTful API**
    - Manage ticket and user data with full CRUD operations (Create, Read, Update, Delete).    

2. **Google Calendar Integration**
    - Seamlessly integrates with the Google Calendar API.
    - Automatically creates reminders for operational deadlines.    

3. **User Interface**
    - Kanban board for tracking ticket status: new, inprogress, and completed tickets.
    - Drag-and-drop functionality using AJAX.
    - Real-time UI updates for better organization and workflow.

4. **Manager Portal**
    - Elevated dashboard access for managers.
    - View team tickets in real time.
    - Request user updates and create new users.

5. **Admin Portal**
    - Role-Based Access Control (RBAC) management.
    - Password reset functionality.
    - System-wide visibility and administrative controls

6. **Cron Task Automation**
    - Background script running in a separate Docker container.
    - Polls ticket database for updates.
    - Sends email notifications when: new tickets are created or completed.

7. **Session Management**
    - Utilizes server-side sessions to maintain seamless user interactions across the application.
    - Persists state after navigation to improve user experience.
    - Stores and displays validation errors and success messages across requests.

8. **Search**
    - Provides filtered search by department, manager, and ticket status.
    - Implements live search functionality for tickets and users.

      
# **Technologies**

1. Languages
    - PHP
    - JavaScript
    - HTML
    - CSS

2.  Libraries & Frameworks
    - jQuery

3. Concepts & Techniques
    - RESTful APIs
    - AJAX
    - MVC

4.  Tools & Infrastructure
    - Docker
    - Docker Compose
    - Git

