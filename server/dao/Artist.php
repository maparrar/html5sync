<?php
/** Artist File
* @package core @subpackage  */
/**
* Artist Class
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package core
* @subpackage 
*/
class Artist{
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
    * Constructor
    * @param int $id Identificador del artista        
    * @param string $name Nombre del artista        
    */
    function __construct($id=0,$name=""){        
        $this->id=$id;
        $this->name=$name;
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
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
}