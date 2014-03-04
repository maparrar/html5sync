<?php
/** Table File
* @package html5sync @subpackage core */
require_once 'Object.php';
/**
* Table Class
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package html5sync
* @subpackage core
*/
class Table extends Object{
    /** 
     * Nombre de la tabla 
     * 
     * @var string
     */
    protected $name;
    /** 
     * Modo de uso de la tabla: ('unlock': Para operaciones insert+read), ('lock': Para operaciones update+delete) 
     * 
     * @var string
     */
    protected $mode;
    /** 
     * Array con los nombres de las columnas 
     * 
     * @var Field[]
     */
    protected $fields;
    /** 
     * Array con los datos de la tabla 
     * 
     * @var array
     */
    protected $data;
    /**
    * Constructor
    * @param string $name Nombre de la tabla        
    * @param string $mode Modo de uso de la tabla: ('unlock': Para operaciones insert+read), ('lock': Para operaciones update+delete)        
    * @param array $fields Array con los nombres de las columnas        
    * @param array $data Array con los datos de la tabla
    */
    function __construct($name="",$mode="",$fields=array(),$data=array()){ 
        $this->name=$name;
        $this->mode=$mode;
        $this->fields=$fields;
        $this->data=$data;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter name
    * @param string $value Nombre de la tabla
    * @return void
    */
    public function setName($value) {
        $this->name=$value;
    }
    /**
    * Setter mode
    * @param string $value Modo de uso de la tabla: ('unlock': Para operaciones insert+read), ('lock': Para operaciones update+delete)
    * @return void
    */
    public function setMode($value) {
        $this->mode=$value;
    }
    /**
    * Setter fields
    * @param Field[] $value Field objects of the table
    * @return void
    */
    public function setFields($value) {
        $this->fields=$value;
    }
    /**
    * Setter data
    * @param array $value Array con los datos de la tabla
    * @return void
    */
    public function setData($value) {
        $this->data=$value;
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
    * Getter: mode
    * @return string
    */
    public function getMode() {
        return $this->mode;
    }
    /**
    * Getter: fields
    * @return Field[]
    */
    public function getFields() {
        return $this->fields;
    }
    /**
    * Getter: data
    * @return array
    */
    public function getData() {
        return $this->data;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Return the table PK
    * @return Field
    */
    public function getPk() {
        $output=false;
        foreach ($this->fields as $field) {
            if($field->getKey()==="PRI"){
                $output=$field;
            }
        }
        return $output;
    }
}