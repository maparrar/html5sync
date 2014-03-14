<?php
/** DaoTable File
 * @package models 
 *  */
/**
 * DaoTable Class
 *
 * Class data layer for the Table class
 * 
 * @author https://github.com/maparrar/html5sync
 * @author maparrar <maparrar@gmail.com>
 * @package models
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
     * @param Database $db database object
     */
    function __construct($db){
        $this->db=$db;
    }
    /**
     * Carga una tabla de la base de datos
     * @param string $dbDriver Driver de la conexión a la base de datos
     * @param string $tableName Nombre de la tabla que se quiere cargar
     * @param string $mode Modo de uso de la tabla: ('unlock': Para operaciones insert+read), ('lock': Para operaciones update+delete)
     * @return Table
     */
    function loadTable($dbDriver,$tableName,$mode){
        $table=new Table($tableName);
        $table->setMode($mode);
        $table->setFields($this->loadFields($dbDriver,$table));
        return $table;
    }
    
    /**
     * Retorna la lista de campos de una Tabla
     * @param string $dbDriver Driver de la conexión a la base de datos
     * @param Table $table Tabla con nombre en la base de datos
     * @return Field[] Lista de campos de la tabla
     */
    private function loadFields($dbDriver,$table){
        $list=array();
        $handler=$this->db->connect("all");
        if($dbDriver==="pgsql"){
            $sql="
                SELECT DISTINCT
                    a.attnum as num,
                    a.attname as name,
                    format_type(a.atttypid, a.atttypmod) as type,
                    a.attnotnull as notnull, 
                    com.description as comment,
                    coalesce(i.indisprimary,false) as key,
                    def.adsrc as default
                FROM pg_attribute a 
                JOIN pg_class pgc ON pgc.oid = a.attrelid
                LEFT JOIN pg_index i ON 
                    (pgc.oid = i.indrelid AND i.indkey[0] = a.attnum)
                LEFT JOIN pg_description com on 
                    (pgc.oid = com.objoid AND a.attnum = com.objsubid)
                LEFT JOIN pg_attrdef def ON 
                    (a.attrelid = def.adrelid AND a.attnum = def.adnum)
                WHERE a.attnum > 0 AND pgc.oid = a.attrelid
                AND pg_table_is_visible(pgc.oid)
                AND NOT a.attisdropped
                AND pgc.relname = '".$table->getName()."' 
                ORDER BY a.attnum;";
        }elseif($dbDriver==="mysql"){
            $sql='SELECT COLUMN_NAME AS `name`,DATA_TYPE AS `type`,COLUMN_KEY AS `key` FROM information_schema.columns WHERE TABLE_NAME = "'.$table->getName().'"';
        }
        $stmt = $handler->prepare($sql);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $key="";
                if($row["key"]==="t"||$row["key"]==="PRI"){
                    $key="PK";
                }
                $field=new Field($row["name"],$row["type"],$key);
                array_push($list,$field);
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
        }
        return $list;
    }
    
    /**
     * Retorna un array con los datos de la tabla (un array por registro)
     * @param Table $table Tabla con nombre y lista de campos
     * @return array[] Array de arrays con los registros de la tabla
     */
    function reloadData($table){
        $list=array();
        $fieldString="";
        $handler=$this->db->connect("all");
        foreach ($table->getFields() as $field) {
            $fieldString.=$field->getName().",";
        }
        //Remove the last comma
        $fieldString=substr($fieldString,0,-1);
        $stmt = $handler->prepare("SELECT ".$fieldString." FROM ".$table->getName()." LIMIT 50000");
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
            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
        }
        return $list;
    }
    /**
     * Verifica si hubo cambios en una lista de tablas para un usuario
     * @param string $table Nombre de la tabla que se quiere verificar
     * @param DateTime $lastUpdate Objeto de fecha con la última actualización
     * @return boolean True si se detectaron cambios, False en otro caso
     */
    function checkDataChanged($table,$lastUpdate){
        $changed=false;
        $handler=$this->db->connect("all");
        $stmt = $handler->prepare("SELECT count(*) AS updated FROM ".$table->getName()." WHERE html5sync_update>'".$lastUpdate->format('Y-m-d H:i:s')."'");
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            $updated=intval($row["updated"]);
            if($updated){
                $changed=true;
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
        }
        return $changed;
    }
    /**
     * Retorna los registros que han sido modificados
     * @param string $table Nombre de la tabla que se quiere verificar
     * @param DateTime $lastUpdate Objeto de fecha con la última actualización
     * @return boolean True si se detectaron cambios, False en otro caso
     */
    function returnDataChanged($table,$lastUpdate){
        $list=array();
        $handler=$this->db->connect("all");
        $stmt = $handler->prepare("SELECT * FROM ".$table->getName()." WHERE html5sync_update>'".$lastUpdate->format('Y-m-d H:i:s')."'");
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
            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
        }
        return $list;
    }
    /**
     * Define el modo UpdatedColumn. Inserta una columna donde se lleva la cuenta
     * de las insersiones y/o actualizaciones en una tabla. Crea un Trigger que
     * realiza el proceso.
     * @param string $dbDriver Driver de la conexión a la base de datos
     * @param Table $table Tabla con nombre en la base de datos
     */
    function setUpdatedColumnMode($dbDriver,$table){
        $this->addColumn($dbDriver,$table);
        $this->addTrigger($dbDriver,$table);
    }
    /**
     * Agrega una columna que será alimentada con la fecha de actualización o insersión
     * por medio de un Trigger. Además crea una columna para almacenar el tipo
     * de transacción
     * @param string $dbDriver Driver de la conexión a la base de datos
     * @param Table $table Tabla con nombre en la base de datos
     */
    private function addColumn($dbDriver,$table){
        $handler=$this->db->connect("all");
        if($dbDriver==="pgsql"){
            $handler->query('ALTER TABLE '.$table->getName().' ADD COLUMN html5sync_update timestamp DEFAULT NULL');
        }elseif($dbDriver==="mysql"){
            $handler->query('ALTER TABLE '.$table->getName().' ADD COLUMN html5sync_update datetime DEFAULT NULL');
        }
        $handler->query("ALTER TABLE ".$table->getName()." ADD COLUMN html5sync_transaction VARCHAR(6) DEFAULT NULL");
    }
    /**
     * Función que crea un Trigger en la base de datos para almacenar la última
     * actualización y/o insersión en la tabla.
     * @param string $dbDriver Driver de la conexión a la base de datos
     * @param Table $table Tabla con nombre en la base de datos
     */
    private function addTrigger($dbDriver,$table){
        $handler=$this->db->connect("all");
        if($dbDriver==="pgsql"){
            $handler->query("CREATE OR REPLACE FUNCTION html5sync_proc_insert_".$table->getName()."() RETURNS TRIGGER AS $$ BEGIN NEW.html5sync_update := current_timestamp(0); NEW.html5sync_transaction := 'insert'; RETURN NEW; END; $$ LANGUAGE plpgsql;");
            $handler->query("CREATE OR REPLACE FUNCTION html5sync_proc_update_".$table->getName()."() RETURNS TRIGGER AS $$ BEGIN NEW.html5sync_update := current_timestamp(0); NEW.html5sync_transaction := 'update'; RETURN NEW; END; $$ LANGUAGE plpgsql;");
            $handler->query("CREATE TRIGGER html5sync_trig_insert_".$table->getName()." BEFORE INSERT ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_insert_".$table->getName()."();");
            $handler->query("CREATE TRIGGER html5sync_trig_update_".$table->getName()." BEFORE UPDATE ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_update_".$table->getName()."();");
        }elseif($dbDriver==="mysql"){
            $handler->query('CREATE TRIGGER html5sync_trig_insert_'.$table->getName().' BEFORE INSERT ON '.$table->getName().' FOR EACH ROW BEGIN SET NEW.html5sync_update = NOW(), NEW.html5sync_transaction = "insert"; END;');
            $handler->query('CREATE TRIGGER html5sync_trig_update_'.$table->getName().' BEFORE UPDATE ON '.$table->getName().' FOR EACH ROW BEGIN SET NEW.html5sync_update = NOW(), NEW.html5sync_transaction = "update"; END;');
        }
    }
}