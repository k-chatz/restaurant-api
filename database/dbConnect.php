<?php

class dbConnect {
    private $conn;

    function __construct() {

    }

    function connect() {
        global $config;

        $host = $config->get('database')->get('host');
        $username = $config->get('database')->get('user');
        $password = $config->get('database')->get('password');
        $database = $config->get('database')->get('name');

        $this->conn = new mysqli($host, $username, $password, $database);

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
