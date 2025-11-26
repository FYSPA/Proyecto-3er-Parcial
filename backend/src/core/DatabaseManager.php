<?php
/**
 *
 *
 *
*/
namespace FYS\Core;

use mysqli;
use FYS\Helpers\Error;
use FYS\Core\EnvManager;

/**
 *
 *
*/
class DatabaseManager {
    private mysqli|null $connection = null;

    // Constructor privado
    public function __construct() {}
    
    /**
     *
     *
    */
    public function getConnection(): mysqli|Error {
         if ($this->connection !== null && empty($this->connection->connect_error)) {
            return $this->connection;
        }

        $dbname     = EnvManager::get('DB_NAME');
        $dbuser     = EnvManager::get('DB_USER');
        $dbpassword = EnvManager::get('DB_PASS');
        $dbhost     = EnvManager::get('DB_HOST', 'localhost');
        $dbport     = EnvManager::get('DB_PORT', '3306');

        $this->connection = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname, $dbport);

        if (!empty($this->connection->error)) {
            $error = new Error("Database connection error: " . $this->connection->error, 500);
            $this->connection = null;
            
            die("Connection failed: " . mysqli_connect_error());
            return $error;
        }
        
        return $this->connection;
    }

    private function closeConnection(){
        if ($this->connection instanceof mysqli && method_exists($this->connection, 'close')) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    public function __destruct(){
        $this->closeConnection();
    }
    
}
