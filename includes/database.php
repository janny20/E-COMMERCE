<?php
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $port;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        $host = $this->host;
        $port = 3306; // Default MySQL port

        // Parse port from host if present (e.g., 'localhost:3307')
        if (strpos($host, ':') !== false) {
            list($host, $port) = explode(':', $host);
        }
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$this->db_name}";
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