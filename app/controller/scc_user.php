<?php
/**
 * Handles SCC New User lifecycle actions.
 *
 * Responsibilities:
 * - Create new SCC users
 * - Update existing SCC user records
 * - Trigger task/ticket creation for onboarding, updates, and termination
 * - Manage related session messaging and redirects
 */

require_once __DIR__ . '/../model/scc_user_db.php';
require_once __DIR__ . '/../model/scc_user_model.php';
require_once __DIR__ . '/../model/task_db.php';
require_once __DIR__ . '/../model/task_model.php';


class NewUserController{


    /**
     * Creates a new SCC user and generates onboarding task.
     *
     * Expected POST:
     * - fname, lname, email
     * - pname (optional)
     * - dept, title, supervisor
     * - location (int)
     * - hours (float)
     * - workType-add (salary/full-time/temp/etc)
     * - sdate (start date)
     * - avaya, ecirts, dots, shadowagent (checkbox flags)
     *
     * Behavior:
     * - Normalizes hours for salary/full-time/temp employees
     * - Creates NewUser model and stores in DB
     * - Generates onboarding task ("New Hire")
     * - Stores session success info and redirects to dashboard
     *
     * Side Effects:
     * - Inserts SCC user record
     * - Creates task ticket
     * - Writes session messages
     * - Redirects browser
     */

   public function addNewUser(): void {

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $pname = $_POST['pname'] ?? '';
    $dept = $_POST['dept'];
    $title = $_POST['title'];
    $supervisor = $_POST['supervisor'];
    $location = (int)$_POST['location'];
    $hours = (float)$_POST['hours'];

    $position = $_POST['workType-add'];
    if($position == 'salary' || $position == 'Full-time' || $position == 'temp'){
        $hours = 37.5;
    }
    $sdate = $_POST['sdate'];  

   
    
   
    $avaya = isset($_POST['avaya']) ? 'yes' : '';
    $ecirts = isset($_POST['ecirts']) ? 'yes' : '';
    $dots = isset($_POST['dots']) ? 'yes' : '';   
    $shadow_agent = isset($_POST['shadowagent']) ? 'yes' : '';

    $user = new NewUser($fname, $lname, $email, $pname, $supervisor, $location, $dept, $title, $position, $hours, $sdate, $avaya, $shadow_agent, $ecirts, $dots);  
    $newUserDB = new NewUserDB();

    $user_id = $newUserDB->addNewSccUser($user);
      

        if($user_id){
            
            $priority = 'Medium';
            $status = 'new';            
            $category = 'New Hire';           
            $date_opened= (new DateTime())->format("Y-m-d");
            $opened_by = $_SESSION['fname'];  
            $user_id = $_SESSION['user_id'];
            $user_email = $_SESSION['email'];
            $manager = $_SESSION['manager_email'] ?? '';
            //Create task object
            $user_desc = "User Id: $user_id Name: $fname $lname";
            $task = new task(NULL, $user_id, $location, $priority, $status, $user_desc, $date_opened, NULL, NULL, NULL, $user_email, NULL, NULL, $category, NULL, $manager);
            $taskdb = new TaskDB();            
            $new_ticket = $taskdb->addTask($task);
            $_SESSION['newuser_name'] = $fname . " " . $lname;
            $_SESSION['newuser_startdate'] = $sdate;
            header("Location: /dashboard");
            exit;

        }else{
      
        $_SESSION['error'] = 'Error creating user';
        header("Location: /manager_dashboard.php");
        exit;
        }




   }
    /**
     * Updates an existing SCC user and generates update task.
     *
     * Expected POST:
     * - id-update (user id)
     * - fname-update, lname-update, email-update
     * - pname-update
     * - dept-update, title-update, supervisor-update
     * - location-update, workType-update, hours-update
     * - sdate-update
     * - avaya-update, ecirts-update, dots-update, shadowagent-update
     *
     * Behavior:
     * - Updates SCC user record
     * - Creates "Update SCC User" task ticket
     * - Stores result in session and redirects
     */
    public function updateSccUserInfo(): void
    {
     
    
        $id = $_POST['id-update'];
        $fname = $_POST['fname-update'];  
        $lname = $_POST['lname-update'];
        $email = $_POST['email-update'];
        $pname = $_POST['pname-update'];
        $dept = $_POST['dept-update'];
        $title = $_POST['title-update'];
        $supervisor = $_POST['supervisor-update'];
        $location = (int)$_POST['location-update'];
        $position = $_POST['workType-update'];
        $hours = (float)$_POST['hours-update'];
        $sdate = $_POST['sdate-update'];

        if($position == 'salary' || $position == 'Full-time' || $position == 'temp'){
            $hours = 37.5;
        }
        

        $avaya = isset($_POST['avaya-update']) ? 'yes' : '';
        $ecirts = isset($_POST['ecirts-update']) ? 'yes' : '';
        $dots = isset($_POST['dots-update']) ? 'yes' : '';       
        $shadow_agent = isset($_POST['shadowagent-update']) ? 'yes' : '';
        

        $userDB = new NewUserDB();
        $userInfo = $userDB->findUserById($id);

        if($userInfo){
            $updatedUser = new NewUser($fname, $lname, $email, $pname, $supervisor, $location, $dept, $title, $position, $hours, $sdate, $avaya, $shadow_agent, $ecirts, $dots);
            $userRow = $userDB->updateSccUser($id, $updatedUser);
              
        }        
      
        if ($userRow) {
                $priority = 'Medium';
                $status = 'new';
            
                $category = 'Update SCC User';       
                $date_opened= (new DateTime())->format("Y-m-d");
              
                $user_id = $_SESSION['user_id'];
                $user_email = $_SESSION['email'];
                $manager = $_SESSION['manager_email'] ?? '';
                //Create task object
                $user_desc = "User Id: $user_id Name: $fname $lname";
                $task = new task(NULL, $user_id, 1, $priority, $status, $user_desc, $date_opened, NULL, NULL, NULL, $user_email, NULL, NULL, $category, NULL, $manager);
                $taskdb = new TaskDB();            
                $new_ticket = $taskdb->addTask($task);

                $_SESSION['result'] = "Updated Successfully.";
                $_SESSION['user_name'] = $fname . " " . $lname;
                $_SESSION['user_startdate'] = $sdate;
                header("Location: /userPanel");
                exit;        
            }

            $_SESSION['error'] = 'Invalid user';
            header("Location: /userPanel");
            exit;
    }

        /**
     * Creates a termination task for an SCC user.
     *
     * Expected POST:
     * - deleteid (user id)
     * - deletefname
     * - termdate
     * - termtime
     *
     * Behavior:
     * - Validates user exists
     * - Creates "Termination" task ticket
     * - Stores termination info in session and redirects
     */
    public function deleteSccUserInfo(): void
    {
     
    
      $id = (int)$_POST['deleteid'];
      $fname = $_POST['deletefname']; 
      $lname = $_POST['deletefname'];
      $tDate = $_POST['termdate']; 
      $tTime = $_POST['termtime'];
      $user_email = $_SESSION['email'];

    $userDB = new NewUserDB();
    $userInfo = $userDB->findUserById($id);
    $desc = "Name: " . $fname . ' ' . $lname . "  Date: " . $tDate . "  Time: " . $tTime;

    if ($userInfo) {               
        

        $priority = 'Medium';
        $status = 'new';
       
        $category = 'Termination';       
        $date_opened= (new DateTime())->format("Y-m-d");
         
        $user_id = $_SESSION['user_id'];
        $user_email = $_SESSION['email'];
        $manager = $_SESSION['manager_email'] ?? '';
        //Create task object

        $task = new task(NULL, $user_id, 1, $priority, $status, $desc, $date_opened, NULL, NULL, NULL, $user_email, NULL, NULL, $category, NULL, $manager);
        $taskdb = new TaskDB();            
        $new_ticket = $taskdb->addTask($task);

        $_SESSION['result'] = "Updated Successfully.";        
        $_SESSION['user_name'] = $fname . " " . $lname;
        $_SESSION['user_termdate'] = $tDate;
        $_SESSION['user_termtime'] = $tTime;  
        header("Location: /dashboard");
        exit;      
    }

        $_SESSION['error'] = 'Invalid user';
        header("Location: /dashboard");
        exit;   
    }     
 


}