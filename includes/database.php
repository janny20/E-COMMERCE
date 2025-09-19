<?php
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $port = 3306; // Default MySQL port
    public $conn;

    public function getConnection() {
        $this->conn = null;
        // Parse port from host if present (e.g., 'localhost:3307')
        if (strpos($this->host, ':') !== false) {
            list($host, $port) = explode(':', $this->host);
            $this->host = $host;
            $this->port = (int)$port;
        }
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";
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