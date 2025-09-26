<?php
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
<<<<<<< HEAD
    private $port;
=======
    private $port = 3306; // Default MySQL port
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
    public $conn;

    public function getConnection() {
        $this->conn = null;
<<<<<<< HEAD
        $host = $this->host;
        $port = 3306; // Default MySQL port

        // Parse port from host if present (e.g., 'localhost:3307')
        if (strpos($host, ':') !== false) {
            list($host, $port) = explode(':', $host);
        }
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$this->db_name}";
=======
        // Parse port from host if present (e.g., 'localhost:3307')
        if (strpos($this->host, ':') !== false) {
            list($host, $port) = explode(':', $this->host);
            $this->host = $host;
            $this->port = (int)$port;
        }
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>