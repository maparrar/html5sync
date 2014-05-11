<?php
/** Transaction File
* @package html5sync @subpackage business */
require_once '../core/Object.php';
/**
* Transaction Class
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package html5sync
* @subpackage business
*/
class Transaction extends Object{
    /** 
     * Transaction identificator 
     * 
     * @var int
     */
    protected $id;
    /** 
     * Type of transaction [INSERT|UPDATE|DELETE] 
     * 
     * @var string
     */
    protected $type;
    /** 
     * Name of the table 
     * 
     * @var string
     */
    protected $tableName;
    /** 
     * Value of the PK of the transaction 
     * 
     * @var string
     */
    protected $key;
    /** 
     * Date and time of the transaction 
     * 
     * @var Date
     */
    protected $date;
    /** 
     * Row if is INSERT or UPDATE, false otherwise 
     * 
     * @var array
     */
    protected $row;
    /**
    * Constructor
    * @param int $id Transaction identificator        
    * @param string $type Type of transaction [INSERT|UPDATE|DELETE]        
    * @param string $tableName Name of the table        
    * @param string $key Value of the PK of the transaction        
    * @param Date $date Date and time of the transaction
    */
    function __construct($id=0,$type="",$tableName="",$key="",$date=""){        
        $this->id=$id;
        $this->type=$type;
        $this->tableName=$tableName;
        $this->key=$key;
        $this->date=$date;
        $this->row=false;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter id
    * @param int $value Transaction identificator
    * @return void
    */
    public function setId($value) {
        $this->id=$value;
    }
    /**
    * Setter type
    * @param string $value Type of transaction [INSERT|UPDATE|DELETE]
    * @return void
    */
    public function setType($value) {
        $this->type=$value;
    }
    /**
    * Setter tableName
    * @param string $value Name of the table
    * @return void
    */
    public function setTableName($value) {
        $this->tableName=$value;
    }
    /**
    * Setter key
    * @param string $value Value of the PK of the transaction
    * @return void
    */
    public function setKey($value) {
        $this->key=$value;
    }
    /**
    * Setter date
    * @param Date $value Date and time of the transaction
    * @return void
    */
    public function setDate($value) {
        $this->date=$value;
    }
    /**
    * Setter row
    * @param array $value Row if is INSERT or UPDATE, false otherwise
    * @return void
    */
    public function setRow($value) {
        $this->row=$value;
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
    * Getter: type
    * @return string
    */
    public function getType() {
        return $this->type;
    }
    /**
    * Getter: tableName
    * @return string
    */
    public function getTableName() {
        return $this->tableName;
    }
    /**
    * Getter: key
    * @return string
    */
    public function getKey() {
        return $this->key;
    }
    /**
    * Getter: date
    * @return Date
    */
    public function getDate() {
        return $this->date;
    }
    /**
    * Getter: row
    * @return array
    */
    public function getRow() {
        return $this->row;
    }    
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
}