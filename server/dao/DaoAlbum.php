<?php
/** DaoAlbum File
 * @package models @subpackage dal */
/**
 * DaoAlbum Class
 *
 * Class data layer for the Album class
 * 
 * @author https://github.com/maparrar/html5sync
 * @author maparrar <maparrar@gmail.com>
 * @package models
 * @subpackage dal
 */
class DaoAlbum{
    /**
     * Create an Album in the database
     * @param Album $album new Album
     * @return Album Album stored
     * @return string "exist" if the Album already exist
     * @return false if the Album couldn't created
     */
    function create($album){
        $created=false;
        if(!$this->exist($album)){    
            $handler=Maqinato::connect("write");
            $stmt = $handler->prepare("INSERT INTO Album 
                (`id`,`name`,`artist`) VALUES 
                (:id,:name,:artist)");
            $stmt->bindParam(':id',$album->getId());
            $stmt->bindParam(':name',$album->getName());
            $stmt->bindParam(':artist',$album->getArtist());
            if($stmt->execute()){
                $album->setId(intval($handler->lastInsertID()));
                $created=$album;
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
     * Read a Album from the database
     * @param int $id Album identificator
     * @return Album Album loaded
     */
    function read($id){
        $response=false;
        $handler=Maqinato::connect("read");
        $stmt = $handler->prepare("SELECT * FROM Album WHERE id=:id");
        $stmt->bindParam(':id',$id);
        if ($stmt->execute()) {
            if($stmt->rowCount()>0){
                $row=$stmt->fetch();
                $album=new Album();
                $album->setId(intval($row["id"]));
                $album->setName($row["name"]);
                $album->setArtist(intval($row["artist"]));
                $response=$album;
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $response;
    }
    /**
     * Update a Album in the database
     * @param Album $album Album object
     * @return false if could'nt update it
     * @return true if the Album was updated
     */
    function update($album){
        $updated=false;
        if($this->exist($album)){
            $handler=Maqinato::connect();
            $stmt = $handler->prepare("UPDATE Album SET `name`=:name,
                `artist`=:artist WHERE id=:id");
            $stmt->bindParam(':id',$album->getId());
            $stmt->bindParam(':name',$album->getName());
            $stmt->bindParam(':artist',$album->getArtist());
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
     * Delete an Album from the database
     * @param Album $album Album object
     * @return false if could'nt delete it
     * @return true if the Album was deleted
     */
    function delete($album){
        $deleted=false;
        if($this->exist($album)){
            $handler=Maqinato::connect("delete");
            $stmt = $handler->prepare("DELETE Album WHERE id=:id");
            $stmt->bindParam(':id',$album->getId());
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
     * Return if a Album exist in the database
     * @param Album $album Album object
     * @return false if doesn't exist
     * @return true if exist
     */
    function exist($album){
        $exist=false;
        $handler=Maqinato::connect("read");
        $stmt = $handler->prepare("SELECT id FROM Album WHERE id=:id");
        $stmt->bindParam(':id',$album->getId());
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            if($row){
                if(intval($row["id"])===intval($album->getId())){
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
     * Get the list of Album
     * @return Album[] List of Album
     */
    function listing(){
        $list=array();
        $handler=Maqinato::connect("read");
        $stmt = $handler->prepare("SELECT id FROM Album");
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $album=$this->read($row["id"]);
                array_push($list,$album);
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $list;
    }
}