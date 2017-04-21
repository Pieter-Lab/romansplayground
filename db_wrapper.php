<?php
/**
 * Created by PhpStorm.
 * User: pieter
 * Date: 4/21/17
 * Time: 10:22 AM
 */

class db_wrapper{

    private $servername = "localhost";
    private $username = "root";
    private $password = "peter123";
    private $dbname = "locations";
    public $conn = false;

    function __construct()
    {
        // Create connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    /**
     * Prints out variables
     */
    public function printer($val){
        if(is_array($val) || is_object($val)){
            echo '<pre>';
            print_r($val);
            echo '</pre>';
        }else{
            echo '<br />';
            var_dump($val);
            echo '<br />';
        }
    }
}