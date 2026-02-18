<?php

/**
 * Class UserDB
 *
 * Handles all database operations related to the "users" table (registered in support hub).
 * Supports CRUD operations, role/manager assignment, and password updates.
 */

require_once __DIR__ . '/../../core/connection.php';

class UserDB
{
    protected PDO $pdo;

    /**
     * Constructor
     * Initializes PDO connection using the Database class.
     */

    public function __construct()
    {
        $this->pdo = (new Database)->pdo();
    }

    /**
     * Find a user by email
     *
     * @param string $email User email
     * @return array|null Returns user data as associative array or null if not found
     */

    public function findByName(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Add a new user to the database
     *
     * If the email already exists, returns null.
     *
     * @param object $user User object with getters (first name, last name, email, password hash, role, manager)
     * @return int|null Returns last inserted ID or null if user exists or error occurs
     */

    public function addUser($user)
    {
        try {

            $stmt = $this->pdo->prepare('
            SELECT user_id FROM users WHERE email = ?');
            $stmt->execute([$user->getUserEmail()]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            //If user already exists, return its id
            if ($existing) {
            return null;
            }
      
            //Otherwise insert the new user
            $stmt = $this->pdo->prepare("
                INSERT INTO users (first_name, last_name, email, password_hash, role, manager)
                VALUES (?, ?, ?, ?, ?, ?)"
            );        
        
            $stmt->execute([               
                $user->getFirstName(),
                $user->getLastName(), 
                $user->getUserEmail(),                 
                $user->getHashedPwd(),
                $user->getRole(),
                $user->getManager(),               
        
            ]);

            // Return last inserted ID
            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
           error_log("Database error: " . $e->getMessage());
            return null;
        }
    }	
    
    /**
     * Find a user by ID
     *
     * @param int $id User ID
     * @return array|null Associative array of user data or null if not found
     */

	public function findById(int $id): ?array {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');
		$stmt->execute([$id]);
		return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
	}

    /**
     * Update user role, manager, and department by ID
     *
     * @param int $id User ID
     * @param int $newRole New role (0=user, 1=manager, etc.)
     * @param string $managerEmail Manager email
     * @param string $dept Department name
     * @return bool True on success, false on failure
     */

    public function updateById(int $id, $newRole, string $managerEmail, $dept) {
		
        $stmt = $this->pdo->prepare('
        UPDATE users
            SET role = :role,
            manager = :manager,
            dept = :dept
        WHERE user_id = :id
        ');

		return $stmt->execute([            
            ':role' => $newRole,
            ':manager' => $managerEmail,
            ':dept' => $dept,           
            ':id'   => $id
        ]);
	}

    /**
     * Get all users
     *
     * @return array|null Array of users or null if none
     */

	public function getAllUsers(): ?array
	{
		$stmt = $this->pdo->prepare("SELECT user_id, first_name, last_name, email, role, manager, dept FROM users");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
	}

    /**
     * Get all users with manager role
     *
     * @return array|null Array of manager emails or null if none
     */

	public function getAllManagers(): ?array
	{
		$stmt = $this->pdo->prepare("SELECT email FROM users WHERE role = 1");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
	}

    /**
     * Change a user's password
     *
     * @param string $email User email
     * @param string $newPass New hashed password
     * @return bool True on success, false on failure
     */

    public function changePass(string $email, string $newPass){

        $stmt = $this->pdo->prepare('
        UPDATE users
            SET password_hash = :newPass
        WHERE email = :email
        ');

		return $stmt->execute([            
            ':newPass' => $newPass,            
            ':email'   => $email
        ]);

    }

    /**
     * Delete a user by email
     *
     * @param string $email User email
     * @return bool True if a row was deleted, false otherwise
     */

    public function deleteUserByEmail(string $email): bool
	{
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE email = ?");
		$stmt->execute([$email]);
        
        if($stmt->rowCount() > 0){		   
		return $stmt->rowCount() > 0;
        }
        return false;
	}
}