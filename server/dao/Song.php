<?php
/** Song File
* @package core @subpackage  */
/**
* Song Class
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package core
* @subpackage 
*/
class Song{
    /** 
     * Identificador del artista 
     * 
     * @var int
     */
    protected $id;
    /** 
     * Nombre del artista 
     * 
     * @var string
     */
    protected $name;
    /** 
     * Id del album 
     * 
     * @var int
     */
    protected $album;
    /**
    * Constructor
    * @param int $id Identificador del artista        
    * @param string $name Nombre del artista        
    * @param int $album Id del album        
    */
    function __construct($id=0,$name="",$album=0){        
        $this->id=$id;
        $this->name=$name;
        $this->album=$album;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter id
    * @param int $value Identificador del artista
    * @return void
    */
    public function setId($value) {
        $this->id=$value;
    }
    /**
    * Setter name
    * @param string $value Nombre del artista
    * @return void
    */
    public function setName($value) {
        $this->name=$value;
    }
    /**
    * Setter album
    * @param int $value Id del album
    * @return void
    */
    public function setAlbum($value) {
        $this->album=$value;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Getter: id
    * @return int
    */
    public function getId() {
        return $this->id;
    }
    /**
    * Getter: name
    * @return string
    */
    public function getName() {
        return $this->name;
    }
    /**
    * Getter: album
    * @return int
    */
    public function getAlbum() {
        return $this->album;
    }    
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
}