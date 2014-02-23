<?php
/** DaoArtist File
 * @package models @subpackage dal */
/**
 * DaoArtist Class
 *
 * Class data layer for the Artist class
 * 
 * @author https://github.com/maparrar/html5sync
 * @author maparrar <maparrar@gmail.com>
 * @package models
 * @subpackage dal
 */
class DaoArtist{
    /**
     * Create an Artist in the database
     * @param Artist $artist new Artist
     * @return Artist Artist stored
     * @return string "exist" if the Artist already exist
     * @return false if the Artist couldn't created
     */
    function create($artist){
        $created=false;
        if(!$this->exist($artist)){    
            $handler=Maqinato::connect("write");
            $stmt = $handler->prepare("INSERT INTO Artist 
                (`id`,`name`) VALUES 
                (:id,:name)");
            $stmt->bindParam(':id',$artist->getId());
            $stmt->bindParam(':name',$artist->getName());
            if($stmt->execute()){
                $artist->setId(intval($handler->lastInsertID()));
                $created=$artist;
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
     * Read a Artist from the database
     * @param int $id Artist identificator
     * @return Artist Artist loaded
     */
    function read($id){
        $response=false;
        $handler=Maqinato::connect("read");
        $stmt = $handler->prepare("SELECT * FROM Artist WHERE id=:id");
        $stmt->bindParam(':id',$id);
        if ($stmt->execute()) {
            if($stmt->rowCount()>0){
                $row=$stmt->fetch();
                $artist=new Artist();
                $artist->setId(intval($row["id"]));
                $artist->setName($row["name"]);
                $response=$artist;
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $response;
    }
    /**
     * Update a Artist in the database
     * @param Artist $artist Artist object
     * @return false if could'nt update it
     * @return true if the Artist was updated
     */
    function update($artist){
        $updated=false;
        if($this->exist($artist)){
            $handler=Maqinato::connect();
            $stmt = $handler->prepare("UPDATE Artist SET `name`=:name WHERE id=:id");
            $stmt->bindParam(':id',$artist->getId());
            $stmt->bindParam(':name',$artist->getName());
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
     * Delete an Artist from the database
     * @param Artist $artist Artist object
     * @return false if could'nt delete it
     * @return true if the Artist was deleted
     */
    function delete($artist){
        $deleted=false;
        if($this->exist($artist)){
            $handler=Maqinato::connect("delete");
            $stmt = $handler->prepare("DELETE Artist WHERE id=:id");
            $stmt->bindParam(':id',$artist->getId());
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
     * Return if a Artist exist in the database
     * @param Artist $artist Artist object
     * @return false if doesn't exist
     * @return true if exist
     */
    function exist($artist){
        $exist=false;
        $handler=Maqinato::connect("read");
        $stmt = $handler->prepare("SELECT id FROM Artist WHERE id=:id");
        $stmt->bindParam(':id',$artist->getId());
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            if($row){
                if(intval($row["id"])===intval($artist->getId())){
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
     * Get the list of Artist
     * @return Artist[] List of Artist
     */
    function listing(){
        $list=array();
        $handler=Maqinato::connect("read");
        $stmt = $handler->prepare("SELECT id FROM Artist");
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $artist=$this->read($row["id"]);
                array_push($list,$artist);
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $list;
    }
}