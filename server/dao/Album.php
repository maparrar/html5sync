<?php
/** Album File
* @package core @subpackage  */
/**
* Album Class
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package core
* @subpackage 
*/
class Album{
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
     * Id del artista 
     * 
     * @var int
     */
    protected $artist;
    /**
    * Constructor
    * @param int $id Identificador del artista        
    * @param string $name Nombre del artista        
    * @param int $artist Id del artista        
    */
    function __construct($id=0,$name="",$artist=0){        
        $this->id=$id;
        $this->name=$name;
        $this->artist=$artist;
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
    * Setter artist
    * @param int $value Id del artista
    * @return void
    */
    public function setArtist($value) {
        $this->artist=$value;
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
    * Getter: artist
    * @return int
    */
    public function getArtist() {
        return $this->artist;
    }    
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
}