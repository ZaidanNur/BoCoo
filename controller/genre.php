<?php

require_once('db.php');

class Genre
{
    private $db;

    function __construct()
    {
        $this->db = new mysqli(HOST,USER,PASS, DB);
        if ($this->db->connect_error) {
            die("Koneksi Gagal");
        }
    }

    public function create($data)
    {
        foreach ($data as $key => $value) {
            $value = is_array($value)?trim(implode(',', $value)) : trim($value);
            $data[$key] = (strlen($value)>0 ? $value :NULL);
        }
        $query = "INSERT INTO genre VALUES(NULL,?)";
        $sql = $this->db->prepare($query);
        $sql->bind_param(
            's',
            $data['genre']
        );

        try {
            $sql->execute();
        } catch (\Exception $e) {
            $sql->close();
            http_response_code(500);
            die($e->getMessage());
        }
    }

    public function edit($data)
    {
        foreach ($data as $key => $value) {
            $value = is_array($value)?trim(implode(',', $value)) : trim($value);
            $data[$key] = (strlen($value)>0 ? $value :NULL);
        }
        $query = "UPDATE genre SET genre = ? WHERE id = ?";
        $sql = $this->db->prepare($query);
        $sql->bind_param(
            'si',
            $data['genre'],
            $data['genre_id']
        );

        try {
            $sql->execute();
        } catch (\Exception $e) {
            $sql->close();
            http_response_code(500);
            die($e->getMessage());
        }
    }

    public function read()
    {
        // $order = isset($_GET['order']) ? $_GET['order'] : 'title';
        // $sort = isset($_GET['sort']) ? $_GET['sort'] : 'ASC';
        // $begin = isset($_GET['begin']) ? $_GET['begin'] : 0;



        // if($_GET['rating'] != null) {
        //     $rating = $_GET['rating'];
        //     $sql = "SELECT * FROM film where rating = '$rating' order by $order $sort limit $begin,8";
        // } else {
        //     $sql = "SELECT * FROM film order by $order $sort limit $begin,8";
        // }

        $sql = "SELECT * FROM genre";

        $result = $this->db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            array_push($data,$row);
        }
        header("Content-Type: application/json");
        echo json_encode($data);
    }

    public function delete($data)
    {
        foreach ($data as $key => $value) {
            $value = is_array($value)?trim(implode(',', $value)) : trim($value);
            $data[$key] = (strlen($value)>0 ? $value :NULL);
        }
        $query = "DELETE FROM `genre` WHERE id = ?";
        $sql = $this->db->prepare($query);
        $sql->bind_param(
            'i',
            $data['genre_id']
        );

        try {
            $sql->execute();
        } catch (\Exception $e) {
            $sql->close();
            http_response_code(500);
            die($e->getMessage());
        }
    }

    
}

$genre = new Genre();

switch (isset($_GET['action'])?$_GET['action']:null) {
    case 'create':
        $genre->create($_POST);
        // echo $_POST;
        break;
    case 'edit':
        $genre->edit($_POST);
        // echo $_POST;
        break;
    case 'delete':
        $genre->delete($_POST);
        // echo $_POST;
        break;
    default:
        $genre->read();
        break;
}
