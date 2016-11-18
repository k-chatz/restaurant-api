<?php

class dbConnect {
    private $conn;

    function __construct() {

    }

    function connect() {
        include_once 'config.php';

        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if (!$this->conn->set_charset("utf8")) {
            printf("Error loading character set utf8: %s\n", $this->conn->error);
            exit();
        }

        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        return $this->conn;
    }

}
?>
