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
     * Número total de filas que hay en la base de datos para la tabla
     * @var int
     */
    protected $totalOfRows;
    /** 
     * Número de filas que contiene el array data
     * @var int
     */
    protected $numberOfRows;
    /** 
     * Indica el índice del registro inicial que contiene el array $data.
     * Para consultas con gran cantidad de registros, se usa como paginador.
     * @var int
     */
    protected $initialRow;
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
        $this->numberOfRows=0;
        $this->initialRow=0;
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
        $this->numberOfRows=count($this->data);
    }
    /**
    * Setter totalOfRows
    * @param int $value cantidad de datos de la tabla en la DB
    * @return void
    */
    public function setTotalOfRows($value) {
        $this->totalOfRows=$value;
    }
    /**
    * Setter initialRow
    * @param int $value Fila inicial para paginación
    * @return void
    */
    public function setInitialRow($value) {
        $this->initialRow=$value;
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
    /**
    * Getter: totalOfRows
    * @return int
    */
    public function getTotalOfRows() {
        return $this->totalOfRows;
    }
    /**
    * Getter: initialRow
    * @return int
    */
    public function getInitialRow() {
        return $this->initialRow;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Return the table PK
    * @return Field
    */
    public function getPk() {
        $output=false;
        foreach ($this->fields as $field) {
            if($field->getKey()==="PK"){
                $output=$field;
            }
        }
        return $output;
    }
    /*
     * Retorna el número de filas que contiene $data
     * @return int Cantidad de filas en $data
     */
    public function getNumberOfRows(){
        return $this->numberOfRows;
    }
}