<?php

/**
 * Database operations for SCC Users.
 *
 * Handles CRUD operations on the `scc_user` table.
 * Provides methods to:
 * - Add, update, delete users
 * - Fetch users by ID, email, or department
 * - Retrieve all users or temporary employees
 *
 * Uses PDO for database interactions and safely logs exceptions.
 */

require_once __DIR__ . '/../../core/connection.php';

class NewUserDB{

    protected PDO $pdo;
    /**
     * Initializes the database connection.
     */
    public function __construct()
    {
        $this->pdo = (new Database)->pdo();
    }

        /**
     * Adds a new SCC user to the database.
     *
     * @param NewUser $user User object containing all required fields
     * @return int|null Returns the inserted user ID, or null on failure
     */
    
    public function addNewSccUser($user)
    {


        try {

            $stmt = $this->pdo->prepare('
            INSERT INTO scc_user(fname, lname, email, pname, supervisor, location, dept, title, position, hours, sdate, avaya, shadow_agent, ecirts, dots) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');


            $stmt->execute([               
                $user->getNewFirstName(),
                $user->getNewLastName(), 
                $user->getNewUserEmail(),                 
                $user->getNewUserPname(),
                $user->getNewUserSupervisor(),
                $user->getNewUserLocation(),
                $user->getNewUserDept(), 
                $user->getNewUserTitle(),                 
                $user->getNewUserPosition(),
                $user->getNewUserHours(),
                $user->getNewUserStartDate(),
                $user->getAvaya(),
                $user->getShadowAgent(),
                $user->getEcirts(),
                $user->getDots()
       
            ]);

            // Return last inserted ID
            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
           error_log("Database error: " . $e->getMessage());
            return null;
        }
    }
    
        /**
     * Updates an existing SCC user by ID.
     *
     * @param int $id User ID to update
     * @param NewUser $user User object with updated data
     * @return int|null Returns the user ID on success, or null on failure
     */
    public function updateSccUser(int $id, $user)
    {
        try {

        $stmt = $this->pdo->prepare('
        UPDATE scc_user
            SET fname = :fname,
            lname = :lname,
            email = :email,
            pname = :pname,
            supervisor = :supervisor,
            location = :location,
            dept = :dept,
            title = :title,
            position = :position,
            hours = :hours,
            sdate = :sdate,
            avaya = :avaya,
            shadow_agent = :shadow_agent,
            ecirts = :ecirts,
            dots = :dots
        WHERE id = :id
        ');

		$stmt->execute([ 
            ':fname' => $user->getNewFirstName(),           
            ':lname' => $user->getNewLastName(),
            ':email' => $user->getNewUserEmail(),
            ':pname' => $user->getNewUserPname(),
            ':supervisor' => $user->getNewUserSupervisor(),
            ':location' => $user->getNewUserLocation(),
            ':dept' => $user->getNewUserDept(),
            ':title' => $user->getNewUserTitle(), 
            ':position' => $user->getNewUserPosition(),
            ':hours' => $user->getNewUserHours(),
            ':sdate' => $user->getNewUserStartDate(),
            ':avaya' => $user->getAvaya(),
            ':shadow_agent' => $user->getShadowAgent(),
            ':ecirts' => $user->getEcirts(),
            ':dots' => $user->getDots(),        
            ':id'   => $id
        ]);

        return $id;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Deletes a user by ID.
     *
     * @param int $id User ID to delete
     * @return bool Returns true if a row was deleted, false otherwise
     */

    public function deleteUserById(int $id): bool
	{
                 
        $stmt = $this->pdo->prepare("delete FROM scc_user WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;      
        
	}

       /**
     * Retrieves all users from the database.
     *
     * @return array|null Returns an array of users, or null if none found
     */

    public function getAllNewUsers(): ?array 
	{
		$stmt = $this->pdo->prepare("SELECT * FROM scc_user ORDER BY id DESC;");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
	}

        /**
     * Retrieves all users for a specific department.
     *
     * @param string $dept Department name
     * @return array|null Returns an array of users in the department, or null if none
     */

    public function getAllNewUsersByDept(string $dept): ?array
	{
		$stmt = $this->pdo->prepare("SELECT * FROM scc_user WHERE dept = ? ORDER BY id DESC;");
		$stmt->execute([$dept]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
	}


    
        /**
     * Finds a user by ID.
     *
     * @param int $id User ID
     * @return array|null Returns the user record as associative array, or null if not found
     */

	public function findUserById(int $id): ?array{
		$stmt = $this->pdo->prepare('SELECT * FROM scc_user WHERE id = ? LIMIT 1');
        
		$stmt->execute([$id]);
		return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
	}

        /**
     * Finds a user ID by email.
     *
     * @param string $email User email
     * @return int|null Returns the user ID, or null if not found
     */
    
    public function findUserByEmail(string $email): ?int{
		$stmt = $this->pdo->prepare('SELECT id FROM scc_user WHERE email = ? LIMIT 1');
        
		$stmt->execute([$email]);
		$id = $stmt->fetchColumn() ?: null;
        return $id;
        
	}
}	