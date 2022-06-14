<?php

require_once('db.php');

class Account
{
    private $db;

    function __construct()
    {
        $this->db = new mysqli(HOST,USER,PASS, DB);
        if ($this->db->connect_error) {
            die("Koneksi Gagal");
        }
    }

    
}

$film = new Account();

switch (isset($_GET['action'])?$_GET['action']:null) {
    case 'create':
        // $film->create($_POST);
        // echo $_POST;
        break;
    
    default:
        // $film->read();
        break;
}
