<?php
/**
 * Represents a New SCC User.
 *
 * Stores personal, department, and work information for onboarding purposes.
 * Maps to the `scc_user` database table.
 *
 * Properties:
 * - $user_id        : Internal database ID
 * - $fname, $lname  : First and last name
 * - $email          : User email address
 * - $pname          : Preferred name
 * - $supervisor     : Supervisor email or name
 * - $location       : Location ID
 * - $dept           : Department name
 * - $title          : Job title
 * - $position       : Employment type (Full-time, Temp, Salary)
 * - $hours          : Weekly hours
 * - $sdate          : Start date
 * - $avaya, $shadow_agent, $ecirts, $dots : Flags for application access 
 */

class NewUser {
    private int $user_id;    
    
    public function __construct(
        private string $fname,
        private string $lname,
        private string $email,              
        private string $pname,
        private string $supervisor,
        private int $location,
        private string $dept,
        private string $title,
        private string $position,
        private float $hours,  
        private string $sdate,
        private string $avaya,
        private string $shadow_agent,   
        private string $ecirts,
        private string $dots,       
        
    ) { }
    // ===============================
    // Getters
    // ===============================
    public function getNewUserID() {
        return $this->user_id;
    }
    public function getNewFirstName() {
        return $this->fname;
    }

    public function getNewLastName() {
        return $this->lname;
    }

    public function getNewUserEmail() {
        return $this->email;
    }

    public function getNewUserPname() {
        return $this->pname;
    }
    public function getNewUserSupervisor() {
        return $this->supervisor;
    }
    public function getNewUserLocation() {
        return $this->location;
    }
    public function getNewUserDept() {
        return $this->dept;
    }
    public function getNewUserTitle() {
        return $this->title;
    }
    public function getNewUserPosition() {
        return $this->position;
    }
    public function getNewUserHours() {
        return $this->hours;
    }
    public function getNewUserStartDate() {
        return $this->sdate;
    }
    public function getAvaya() {
        return $this->avaya;
    }
    public function getShadowAgent() {
        return $this->shadow_agent;
    }
    public function getEcirts() {
        return $this->ecirts;
    }
    public function getDots() {
        return $this->dots;
    }

    // ===============================
    // Setters
    // ===============================
    public function setUserID($val) {
        $this->user_id = $val;
    } 

    public function setFirstName($val) {
        $this->fname = $val;
    }
    public function setLastName($val) {
        $this->lname = $val;
    }
    public function setEmail($val) {
        $this->email = $val;
    }
    public function setPname($val) {
        $this->pname = $val;
    }
    public function setSupervisor($val) {
        $this->supervisor = $val;
    }
    public function setLocation($val) {
        $this->location = $val;
    }
    public function setDept($val) {
        $this->dept = $val;
    }
    public function setTitle($val) {
        $this->title = $val;
    }
    public function setPosition($val) {
        $this->position = $val;
    }
    public function setHours($val) {
        $this->hours = $val;
    }
    public function setStartDate($val) {
        $this->sdate = $val;
    }

    public function setAvaya($val) {
        $this->avaya = $val;
    }
    public function setShadowAgent($val) {
        $this->shadow_agent = $val;
    }
    public function setEcirts($val) {
        $this->ecirts = $val;
    }
    public function setDots($val) {
        $this->dots = $val;
    }

}
?>