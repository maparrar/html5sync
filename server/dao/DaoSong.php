<?php
/** DaoSong File
 * @package models @subpackage dal */
/**
 * DaoSong Class
 *
 * Class data layer for the Song class
 * 
 * @author https://github.com/maparrar/html5sync
 * @author maparrar <maparrar@gmail.com>
 * @package models
 * @subpackage dal
 */
class DaoSong{
    /**
     * Create an Song in the database
     * @param Song $song new Song
     * @return Song Song stored
     * @return string "exist" if the Song already exist
     * @return false if the Song couldn't created
     */
    function create($song){
        $created=false;
        if(!$this->exist($song)){    
            $handler=Maqinato::connect("write");
            $stmt = $handler->prepare("INSERT INTO Song 
                (`id`,`name`,`album`) VALUES 
                (:id,:name,:album)");
            $stmt->bindParam(':id',$song->getId());
            $stmt->bindParam(':name',$song->getName());
            $stmt->bindParam(':album',$song->getAlbum());
            if($stmt->execute()){
                $song->setId(intval($handler->lastInsertID()));
                $created=$song;
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
     * Read a Song from the database
     * @param int $id Song identificator
     * @return Song Song loaded
     */
    function read($id){
        $response=false;
        $handler=Maqinato::connect("read");
        $stmt = $handler->prepare("SELECT * FROM Song WHERE id=:id");
        $stmt->bindParam(':id',$id);
        if ($stmt->execute()) {
            if($stmt->rowCount()>0){
                $row=$stmt->fetch();
                $song=new Song();
                $song->setId(intval($row["id"]));
                $song->setName($row["name"]);
                $song->setAlbum(intval($row["album"]));
                $response=$song;
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $response;
    }
    /**
     * Update a Song in the database
     * @param Song $song Song object
     * @return false if could'nt update it
     * @return true if the Song was updated
     */
    function update($song){
        $updated=false;
        if($this->exist($song)){
            $handler=Maqinato::connect();
            $stmt = $handler->prepare("UPDATE Song SET `name`=:name,
                `album`=:album WHERE id=:id");
            $stmt->bindParam(':id',$song->getId());
            $stmt->bindParam(':name',$song->getName());
            $stmt->bindParam(':album',$song->getAlbum());
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
     * Delete an Song from the database
     * @param Song $song Song object
     * @return false if could'nt delete it
     * @return true if the Song was deleted
     */
    function delete($song){
        $deleted=false;
        if($this->exist($song)){
            $handler=Maqinato::connect("delete");
            $stmt = $handler->prepare("DELETE Song WHERE id=:id");
            $stmt->bindParam(':id',$song->getId());
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
     * Return if a Song exist in the database
     * @param Song $song Song object
     * @return false if doesn't exist
     * @return true if exist
     */
    function exist($song){
        $exist=false;
        $handler=Maqinato::connect("read");
        $stmt = $handler->prepare("SELECT id FROM Song WHERE id=:id");
        $stmt->bindParam(':id',$song->getId());
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            if($row){
                if(intval($row["id"])===intval($song->getId())){
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
     * Get the list of Song
     * @return Song[] List of Song
     */
    function listing(){
        $list=array();
        $handler=Maqinato::connect("read");
        $stmt = $handler->prepare("SELECT id FROM Song");
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $song=$this->read($row["id"]);
                array_push($list,$song);
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $list;
    }
}