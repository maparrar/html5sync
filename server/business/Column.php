<?php
/** Column File
* @package html5sync @subpackage core */
require_once '../core/Object.php';
/**
* Column Class
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package html5sync
* @subpackage core
*/
class Column extends Object{
    /** 
     * Column Name 
     * 
     * @var string
     */
    protected $name;
    /** 
     * Column type 
     * 
     * @var string
     */
    protected $type;
    /** 
     * True if is Not null
     * @var bool
     */
    protected $notNull;
    /** 
     * True if is autoincrement
     * @var bool
     */
    protected $autoIncrement;
    /** 
     * True if is pk
     * @var bool
     */
    protected $pk;
    /** 
     * True if is fk
     * @var bool
     */
    protected $fk;
    /** 
     * Name of the FK table
     * @var string
     */
    protected $fkTable;
    /** 
     * Name of the FK column related to
     * @var string
     */
    protected $fkColumn;
    /** 
     * Order in table
     * @var int
     */
    protected $order;
    /**
    * Constructor
    * @param string $name Field Name
    * @param string $type Field type     
    */
    function __construct($name="",$type=""){        
        $this->name=$name;
        $this->type=$type;
        $this->notNull=false;
        $this->autoIncrement=false;
        $this->pk=false;
        $this->fk=false;
        $this->fkTable="";
        $this->fkColumn="";
        $this->order=0;
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
    * Setter Not null
    * @param bool $value If is not null
    * @return void
    */
    public function setNotNull($value) {
        $this->notNull=$value;
    }
    /**
    * Setter Auto Increment
    * @param bool $value If is Auto Increment or not
    * @return void
    */
    public function setAutoIncrement($value) {
        $this->autoIncrement=$value;
    }
    /**
    * Setter PK
    * @param bool $value If is PK or not
    * @return void
    */
    public function setPk($value) {
        $this->pk=$value;
    }
    /**
    * Setter FK
    * @param bool $value If is FK or not
    * @return void
    */
    public function setFk($value) {
        $this->fk=$value;
    }
    /**
    * Setter type
    * @param string $value FK Table referenced to
    * @return void
    */
    public function setFkTable($value) {
        $this->fkTable=$value;
    }
    /**
    * Setter type
    * @param string $value FK Column referenced to
    * @return void
    */
    public function setFkColumn($value) {
        $this->fkColumn=$value;
    }
    /**
    * Setter order
    * @param int $value Order in table
    * @return void
    */
    public function setOrder($value) {
        $this->order=$value;
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
    * Getter: notNull
    * @return bool
    */
    public function getNotNull() {
        return $this->notNull;
    }
    /**
    * Getter: autoIncrement
    * @return bool
    */
    public function getAutoIncrement() {
        return $this->autoIncrement;
    }
    /**
    * Getter: PK
    * @return bool
    */
    public function getPk() {
        return $this->pk;
    }
    /**
    * Getter: FK
    * @return bool
    */
    public function getFk() {
        return $this->fk;
    }
    /**
    * Getter: fkTable
    * @return string
    */
    public function getFkTable() {
        return $this->fkTable;
    }
    /**
    * Getter: fkColumn
    * @return string
    */
    public function getFkColumn() {
        return $this->fkColumn;
    }
    /**
    * Getter: order
    * @return int
    */
    public function getOrder() {
        return $this->order;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
     * Return if is Not Null
     * @return bool Return true if the column is Not Null
     */
    public function isNN() {
        return $this->getNotNull();
    }
    /**
     * Return if is AutoIncrement
     * @return bool Return true if the column is AutoIncrement
     */
    public function isAI() {
        return $this->getAutoIncrement();
    }
    /**
     * Return if is PK
     * @return bool Return true if the column is PK
     */
    public function isPK() {
        return $this->getPk();
    }
    /**
     * Return if is FK
     * @return bool Return true if the column is FK
     */
    public function isFK() {
        return $this->fk;
    }
}