<?php

/**
 * Class Database
 *
 * Handles the creation of a PDO connection using secrets stored in
 * the Docker secret files or other protected paths. Provides a method
 * to access the PDO instance for database queries.
 */
class Database
{
    /** @var PDO $pdo The PDO database connection instance */
    private PDO $pdo;
    public function __construct()
    {

        /**
     * Database constructor.
     *
     * Reads database credentials from secret files and initializes a PDO connection.
     *
     * @throws Exception if any secret file is missing
     */
        if (!function_exists('readSecret')) {
            function readSecret(string $path): string {
                if (!file_exists($path)) {
                    throw new Exception("Secret file not found: $path");
                }
                return trim(file_get_contents($path));
            }
        }
            // Read DB credentials from secret files
            $db_host = readSecret('/run/secrets/db_host');
            $db_name = readSecret('/run/secrets/db_name');
            $db_user = readSecret('/run/secrets/db_user');
            $db_pass = readSecret('/run/secrets/db_pass');

            // Initialize PDO connection
        $this->pdo = new PDO(
            "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",$db_user,$db_pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    
    /**
     * Get the PDO instance
     *
     * @return PDO Returns the active PDO connection
     */
    
    public function pdo(): PDO
    {
        return $this->pdo;
    }
}