<?php
/** DaoTable File
 * @package models @subpackage dal */
/**
 * DaoTable Class
 *
 * Class data layer for the Table class
 * 
 * @author https://github.com/maparrar/html5sync
 * @author maparrar <maparrar@gmail.com>
 * @package models
 * @subpackage dal
 */
class DaoTable{
    /** Database Object 
     * @var Database
     */
    protected $db;
    /** PDO handler object 
     * @var PDO
     */
    protected $handler;
    /**
     * Constructor: sets the database Object and the PDO handler
     * @param Database database object
     */
    function __construct($db){
        $this->db=$db;
    }
    
    
    
    function loadTable($tableName,$mode){
        
        $table=new Table($tableName);
        $table->setMode($mode);
        $table->setFields($this->loadFields($tableName));
        
        return $table;
    }
    
    
    private function loadFields($tableName){
        $list=array();
        $handler=$this->db->connect("all");
        $stmt = $handler->prepare("SELECT COLUMN_NAME AS `name`,DATA_TYPE AS `type`,COLUMN_KEY AS `key` FROM information_schema.columns WHERE TABLE_NAME = :table");
        $stmt->bindParam(':table',$tableName);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $field=new Field($row["name"],$row["type"],$row["key"]);
                array_push($list,$field);
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $list;
    }
    
    
    /**
     * Create an Table in the database
     * @param Table $table new Table
     * @return Table Table stored
     * @return string "exist" if the Table already exist
     * @return false if the Table couldn't created
     */
    function create($table){
        $created=false;
        if(!$this->exist($table)){    
            $handler=$this->db->connect("all");
            $stmt = $handler->prepare("INSERT INTO Table 
                (`id`,`name`,`mode`,`fields`,`data`,`pk`) VALUES 
                (:id,:name,:mode,:fields,:data,:pk)");
            $stmt->bindParam(':id',$table->getId());
            $stmt->bindParam(':name',$table->getName());
            $stmt->bindParam(':mode',$table->getMode());
            $stmt->bindParam(':fields',$table->getFields());
            $stmt->bindParam(':data',$table->getData());
            $stmt->bindParam(':pk',$table->getPk());
            if($stmt->execute()){
                $table->setId(intval($handler->lastInsertID()));
                $created=$table;
            }else{
                $error=$stmt->errorInfo();
                error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
            }
        }else{
            $created="exist";
        }
        return $created;
    }
    /**
     * Read a Table from the database
     * @param int $id Table identificator
     * @return Table Table loaded
     */
    function read($id){
        $response=false;
        $handler=$this->db->connect("all");
        $stmt = $handler->prepare("SELECT * FROM Table WHERE id=:id");
        $stmt->bindParam(':id',$id);
        if ($stmt->execute()) {
            if($stmt->rowCount()>0){
                $row=$stmt->fetch();
                $table=new Table();
                $table->setId(intval($row["id"]));
                $table->setName($row["name"]);
                $table->setMode($row["mode"]);
                $table->setFields($row["fields"]);
                $table->setData($row["data"]);
                $table->setPk($row["pk"]);
                $response=$table;
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $response;
    }
    /**
     * Update a Table in the database
     * @param Table $table Table object
     * @return false if could'nt update it
     * @return true if the Table was updated
     */
    function update($table){
        $updated=false;
        if($this->exist($table)){
            $handler=$this->db->connect("all");
            $stmt = $handler->prepare("UPDATE Table SET `name`=:name,
                `mode`=:mode,
                `fields`=:fields,
                `data`=:data,
                `pk`=:pk WHERE id=:id");
            $stmt->bindParam(':id',$table->getId());
            $stmt->bindParam(':name',$table->getName());
            $stmt->bindParam(':mode',$table->getMode());
            $stmt->bindParam(':fields',$table->getFields());
            $stmt->bindParam(':data',$table->getData());
            $stmt->bindParam(':pk',$table->getPk());
            if($stmt->execute()){
                $updated=true;
            }else{
                $error=$stmt->errorInfo();
                error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
            }
        }else{
            $updated=false;
        }
        return $updated;
    }
    /**
     * Delete an Table from the database
     * @param Table $table Table object
     * @return false if could'nt delete it
     * @return true if the Table was deleted
     */
    function delete($table){
        $deleted=false;
        if($this->exist($table)){
            $handler=$this->db->connect("all");
            $stmt = $handler->prepare("DELETE Table WHERE id=:id");
            $stmt->bindParam(':id',$table->getId());
            if($stmt->execute()){
                $deleted=true;
            }else{
                $error=$stmt->errorInfo();
                error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
            }
        }else{
            $deleted=false;
        }
        return $deleted;
    }
    /**
     * Return if a Table exist in the database
     * @param Table $table Table object
     * @return false if doesn't exist
     * @return true if exist
     */
    function exist($table){
        $exist=false;
        $handler=$this->db->connect("all");
        $stmt = $handler->prepare("SELECT id FROM Table WHERE id=:id");
        $stmt->bindParam(':id',$table->getId());
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            if($row){
                if(intval($row["id"])===intval($table->getId())){
                    $exist=true;
                }else{
                    $exist=false;
                }
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $exist;
    }
    /**
     * Get the list of Table
     * @return Table[] List of Table
     */
    function listing(){
        $list=array();
        $handler=$this->db->connect("all");
        $stmt = $handler->prepare("SELECT id FROM Table");
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $table=$this->read($row["id"]);
                array_push($list,$table);
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $list;
    }
}