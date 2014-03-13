<?php
/** User File
* @package html5sync @subpackage core */
/**
* User Class
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package html5sync
* @subpackage core
*/
class User{
    /** 
     * Identificador del usuario 
     * 
     * @var int
     */
    protected $id;
    /** 
     * Rol del usuario 
     * 
     * @var string
     */
    protected $role;
    /**
    * Constructor
    * @param int $id Identificador del usuario        
    * @param string $role Rol del usuario        
    */
    function __construct($id=0,$role=""){        
        $this->id=$id;
        $this->role=$role;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter id
    * @param int $value Identificador del usuario
    * @return void
    */
    public function setId($value) {
        $this->id=$value;
    }
    /**
    * Setter role
    * @param string $value Rol del usuario
    * @return void
    */
    public function setRole($value) {
        $this->role=$value;
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
    * Getter: role
    * @return string
    */
    public function getRole() {
        return $this->role;
    }    
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
}