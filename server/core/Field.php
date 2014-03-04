<?php
/** Field File
* @package html5sync @subpackage core */
/**
* Field Class
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package html5sync
* @subpackage core
*/
class Field{
    /** 
     * Field Name 
     * 
     * @var string
     */
    protected $name;
    /** 
     * Field type 
     * 
     * @var string
     */
    protected $type;
    /** 
     * Kind of key of the field 
     * 
     * @var string
     */
    protected $key;
    /**
    * Constructor
    * @param string $name Field Name        
    * @param string $type Field type        
    * @param string $key Kind of key of the field        
    */
    function __construct($name="",$type="",$key=""){        
        $this->name=$name;
        $this->type=$type;
        $this->key=$key;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter name
    * @param string $value Field Name
    * @return void
    */
    public function setName($value) {
        $this->name=$value;
    }
    /**
    * Setter type
    * @param string $value Field type
    * @return void
    */
    public function setType($value) {
        $this->type=$value;
    }
    /**
    * Setter key
    * @param string $value Kind of key of the field
    * @return void
    */
    public function setKey($value) {
        $this->key=$value;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Getter: name
    * @return string
    */
    public function getName() {
        return $this->name;
    }
    /**
    * Getter: type
    * @return string
    */
    public function getType() {
        return $this->type;
    }
    /**
    * Getter: key
    * @return string
    */
    public function getKey() {
        return $this->key;
    }    
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
}