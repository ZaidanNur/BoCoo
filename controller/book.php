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

    public function edit($data,$file)
    {
        if ($file != null) {
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
        } else {
            $target_file = $data['recenly-book-image'];
        }
        

        foreach ($data as $key => $value) {
            $value = is_array($value)?trim(implode(',', $value)) : trim($value);
            $data[$key] = (strlen($value)>0 ? $value :NULL);
        }

        $query = "UPDATE `books` SET  name =?, image =?, genre_id =?, writer =?, rating =?, sinopsis =? WHERE id=?";
        $sql = $this->db->prepare($query);
        $sql->bind_param(
            'ssisdsi',
            $data['book-name'],
            $target_file,
            $data['genre_id'],
            $data['writer'],
            $data['rating'],
            $data['sinopsis'],
            $data['book_id']
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

    public function read()
    {
        $order = isset($_GET['order']) ? $_GET['order'] : 'name';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'ASC';
        $begin = isset($_GET['begin']) ? $_GET['begin'] : 0;



        if($_GET['genre'] != null) {
            $genre = $_GET['genre'];
            $sql = "SELECT 
                        b.id,
                        b.name,
                        b.image,
                        g.genre,
                        b.writer,
                        b.rating,
                        b.sinopsis
                    FROM
                        books b
                    JOIN genre g ON g.id = b.genre_id
                    WHERE
                        genre = '$genre'
                    ORDER BY 
                        $order $sort
                    LIMIT $begin,3;";
        } else {
            $sql = "SELECT 
                        b.id,
                        b.name,
                        b.image,
                        g.genre,
                        b.writer,
                        b.rating,
                        b.sinopsis
                    FROM
                        books b
                    JOIN genre g ON g.id = b.genre_id
                    ORDER BY 
                        $order $sort
                    LIMIT $begin,3;;";
                        }

        $result = $this->db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            array_push($data,$row);
        }
        header("Content-Type: application/json");
        echo json_encode($data);
    }
    public function read_dashboard()
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
    public function search($data)
    {
        $word = $data['search'];
        
        $sql = "SELECT 
        b.id,
        b.name,
        b.image,
        g.genre,
        b.writer,
        b.rating,
        b.sinopsis
    FROM
        books b
    JOIN genre g ON g.id = b.genre_id
    WHERE 
        name like '%{$word}%' or
        genre like '%{$word}%' or
        writer like '%{$word}%'
    ;";

        $result = $this->db->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            array_push($data,$row);
        }
        header("Content-Type: application/json");
        echo json_encode($data);
    }

    public function recomended_book()
    {
        $sql = "SELECT * FROM books order by rating DESC limit 4";

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
        $query = "DELETE FROM `books` WHERE id = ?";
        $sql = $this->db->prepare($query);
        $sql->bind_param(
            'i',
            $data['book_id']
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

$book = new Book();

switch (isset($_GET['action'])?$_GET['action']:null) {
    case 'create':
        $book->create($_POST,$_FILES);
        // echo $_POST;
        break;
    
    case 'delete':
        $book->delete($_POST);
        // echo $_POST;
        break;

    case 'edit':
        // var_dump(strlen($_FILES["book-image"]["name"]) > 0);
        $book->edit($_POST,strlen($_FILES["book-image"]["name"]) > 0 ?$_FILES:null);
        // echo $_POST;
        break;

    case 'recomended':
        // var_dump(strlen($_FILES["book-image"]["name"]) > 0);
        $book->recomended_book();
        // echo $_POST;
        break;
    case 'search':
        // var_dump(strlen($_FILES["book-image"]["name"]) > 0);
        $book->search($_GET);
        // echo $_POST;
        break;
    case 'dashboard':
        // var_dump(strlen($_FILES["book-image"]["name"]) > 0);
        $book->read_dashboard();
        // echo $_POST;
        break;
    
    default:
        $book->read();
        break;
}
