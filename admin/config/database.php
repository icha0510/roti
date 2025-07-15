<?php
// Konfigurasi Database untuk Admin Panel
class Database {
    private $host = 'localhost';
    private $db_name = 'tokorot3_bready_db';
    private $username = 'tokorot3_anisasari';
    private $password = 'zLD4g38_9fM65_5';

    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?> 