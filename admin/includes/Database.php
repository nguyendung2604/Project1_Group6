<?php
class Database{
    private $conn;
    public function connect(){
        try{
            $this->conn = new PDO("mysql:host=localhost;dbname=project;charset=utf8","root","");
            return $this->conn;
        }catch(PDOException $e){
            die("Lỗi kết nối: ".$e->getMessage());
        }
    }
    public function close(){
        $this ->conn=null;
    }
    
}