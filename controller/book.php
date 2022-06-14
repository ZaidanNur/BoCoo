<?php

require_once('db.php');

class Book
{
    private $db;

    function __construct()
    {
        $this->db = new mysqli(HOST,USER,PASS, DB);
        if ($this->db->connect_error) {
            die("Koneksi Gagal");
        }
    }

    public function create($data,$file)
    {
        $target_dir = "../assets/images/books/";
        $target_file = $target_dir. basename($file["book-image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
        $check = getimagesize($file["book-image"]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
        }

        // Check if file already exists
        if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
        }

        // Check file size
        if ($file["book-image"]["size"] > 50000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        } else {
        if (move_uploaded_file($file["book-image"]["tmp_name"], $target_file)) {
            echo "The file ". htmlspecialchars( basename( $file["book-image"]["name"])). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
        }

        foreach ($data as $key => $value) {
            $value = is_array($value)?trim(implode(',', $value)) : trim($value);
            $data[$key] = (strlen($value)>0 ? $value :NULL);
        }
        $query = "INSERT INTO books VALUES(NULL,?,?,?,?,?,?)";
        $sql = $this->db->prepare($query);
        $sql->bind_param(
            'ssisds',
            $data['book-name'],
            $target_file,
            $data['genre_id'],
            $data['writer'],
            $data['rating'],
            $data['sinopsis']
        );

        try {
            $sql->execute();
        } catch (\Exception $e) {
            $sql->close();
            http_response_code(500);
            die($e->getMessage());
        }

        header("Location: ../pages/dashboard.html");
        
    }

    public function edit()
    {
        # code...
    }

    public function read()
    {
        $sql = "SELECT * FROM books";

        $result = $this->db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            array_push($data,$row);
        }
        header("Content-Type: application/json");
        echo json_encode($data);
    }

    public function delete()
    {
        # code...
    }

    
}

$film = new Book();

switch (isset($_GET['action'])?$_GET['action']:null) {
    case 'create':
        $film->create($_POST,$_FILES);
        // echo $_POST;
        break;
    
    default:
        $film->read();
        break;
}
