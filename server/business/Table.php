<?php
/** Table File
* @package html5sync @subpackage core */
require_once '../core/Object.php';
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
     * Tipo de tabla, "table" si es una tabla completa, "query" si es una consulta, en este caso queda automáticamente en mode="lock" 
     * 
     * @var string
     */
    protected $type;
    /** 
     * Modo de uso de la tabla: ('unlock': Para operaciones insert+read), ('lock': Para operaciones update+delete) 
     * 
     * @var string
     */
    protected $mode;
    /** 
     * Array con los nombres de las columnas 
     * 
     * @var Column[]
     */
    protected $columns;
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
     * Almacena la consulta si type="query"
     * @var string
     */
    private $query;
    /**
    * Constructor
    * @param string $name Nombre de la tabla        
    * @param string $mode Modo de uso de la tabla: ('unlock': Para operaciones insert+read), ('lock': Para operaciones update+delete)        
    * @param Column[] $columns Array con los objetos columnas        
    * @param array $data Array con los datos de la tabla
    */
    function __construct($name="",$mode="lock",$columns=array(),$data=array()){ 
        $this->name=$name;
        $this->type="table";
        $this->mode=$mode;
        $this->columns=$columns;
        $this->data=$data;
        $this->numberOfRows=0;
        $this->initialRow=0;
        $this->query="";
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
    * Setter type
    * @param string $value Tipo de tabla, "table" si es una tabla completa, "query" si es una consulta, en este caso queda automáticamente en mode="lock" 
    * @return void
    */
    public function setType($value) {
        if($value==="query"){
            $this->setMode("lock");
        }
        $this->type=$value;
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
    * Setter Columns
    * @param Column[] $value Column objects of the table
    * @return void
    */
    public function setColumns($value) {
        $this->columns=$value;
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
    /**
    * Setter query
    * @param string $value Almacena la consulta si type="query"
    * @return void
    */
    public function setQuery($value) {
        $this->query=$value;
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
    * Getter: mode
    * @return string
    */
    public function getMode() {
        return $this->mode;
    }
    /**
    * Getter: columns
    * @return Column[]
    */
    public function getColumns() {
        return $this->columns;
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
    /**
    * Getter: query
    * @return string
    */
    public function getQuery() {
        return $this->query;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Return the table PK
    * @return Column
    */
    public function getPk() {
        $output=false;
        foreach ($this->columns as $column) {
            if($column->isPK()){
                $output=$column;
                break;
            }
        }
        return $output;
    }
    /**
     * Verifica si una columna existe usando su nombre
     * @param string $columnName Nombre de la columna que se quiere consultar
     * @return bool True si existe, False en otro caso
     */
    public function existsColumn($columnName){
        $output=false;
        foreach ($this->columns as $column) {
            if($column->getName()===$columnName){
                $output=true;
            }
        }
        return $output;
    }
    /**
     * Retorna una columna usando su nombre
     * @param string $columnName Nombre de la columna que se quiere consultar
     * @return Column Objeto de tipo Column
     */
    public function getColumn($columnName){
        $output=false;
        foreach ($this->columns as $column) {
            if($column->getName()===$columnName){
                $output=$column;
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