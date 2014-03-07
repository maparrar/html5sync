<?php
/** DaoTable File
 * @package models @subpackage dal */
/**
 * DaoTable Class
 *
 * Class data layer for the Table class
 * 
 * @author https://github.com/maparrar/html5sync
 * @author maparrar <maparrar@gmail.com>
 * @package models
 * @subpackage dal
 */
class DaoTable{
    /** Database Object 
     * @var Database
     */
    protected $db;
    /** PDO handler object 
     * @var PDO
     */
    protected $handler;
    /**
     * Constructor: sets the database Object and the PDO handler
     * @param Database database object
     */
    function __construct($db){
        $this->db=$db;
    }
    /**
     * Carga una tabla de la base de datos
     * @param string $tableName Nombre de la tabla que se quiere cargar
     * @param string $mode Modo de uso de la tabla: ('unlock': Para operaciones insert+read), ('lock': Para operaciones update+delete)
     * @return Table
     */
    function loadTable($tableName,$mode){
        $table=new Table($tableName);
        $table->setMode($mode);
        $table->setFields($this->loadFields($table));
        return $table;
    }
    
    /**
     * Retorna la lista de campos de una Tabla
     * @param Table $table Tabla con nombre en la base de datos
     * @return Field[] Lista de campos de la tabla
     */
    private function loadFields($table){
        $list=array();
        $handler=$this->db->connect("all");
        $stmt = $handler->prepare("SELECT COLUMN_NAME AS `name`,DATA_TYPE AS `type`,COLUMN_KEY AS `key` FROM information_schema.columns WHERE TABLE_NAME = :table");
        $stmt->bindParam(':table',$table->getName());
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $field=new Field($row["name"],$row["type"],$row["key"]);
                array_push($list,$field);
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $list;
    }
    
    /**
     * Retorna un array con los datos de la tabla (un array por registro)
     * @param Table $table Tabla con nombre y lista de campos
     * @return array[] Array de arrays con los registros de la tabla
     */
    function loadData($table){
        $list=array();
        $fieldString="";
        $handler=$this->db->connect("all");
        foreach ($table->getFields() as $field) {
            $fieldString.=$field->getName().",";
        }
        //Remove the last comma
        $fieldString=substr($fieldString,0,-1);
        $stmt = $handler->prepare("SELECT ".$fieldString." FROM ".$table->getName());
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $register=array();
                foreach ($table->getFields() as $field) {
                    array_push($register,$row[$field->getName()]);
                }
                array_push($list,$register);
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $list;
    }
}