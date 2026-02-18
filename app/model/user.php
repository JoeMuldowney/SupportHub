<?php

/**
 * Class User
 *
 * Represents a system user with properties for name, email, password, role, manager, and department.
 */

class User {
    private int $user_id;

        /**
     * Constructor
     *
     * @param string $fname First name
     * @param string $lname Last name
     * @param string $email Email address
     * @param string $hashedPwd Hashed password
     * @param string $role User role (e.g., "0"=user, "1"=manager, "3"=admin)
     * @param string|null $manager Optional manager email
     * @param string|null $dept Optional department
     */
    
    public function __construct(
        private string $fname,
        private string $lname,
        private string $email,
        private string $hashedPwd,        
        private string $role,
        private ?string $manager,
        private ?string $dept,
        
    ) { }

    // =======================================
    // --- Getters ---
    // =====================================
    public function getUserID() {
        return $this->user_id;
    }
    public function getFirstName() {
        return $this->fname;
    }

    public function getLastName() {
        return $this->lname;
    }

    public function getUserEmail() {
        return $this->email;
    }

    public function getHashedPwd() {
        return $this->hashedPwd;
    }

    public function getRole() {
        return $this->role;
    }
    public function getManager() {
        return $this->manager;
    }
    public function getDept() {
        return $this->dept;
    }

    // =======================================
    // --- Setters ---
    // =====================================

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

    //This functions takes in a plaintext password and hashes it before adding the user in the database
    public function setPwd($val) {
        $this->hashedPwd = $val;
    }

    public function setRole($val) {
        $this->role = $val;
    }
    public function setManager($val) {
        $this->manager = $val;
    }
    public function setDept($val) {
        $this->dept = $val;
    }
}