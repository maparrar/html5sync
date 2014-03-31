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
        if($this->db->getDriver()==="pgsql"){
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
        }elseif($this->db->getDriver()==="mysql"){
            $sql='SELECT COLUMN_NAME AS `name`,DATA_TYPE AS `type`,COLUMN_KEY AS `key` FROM information_schema.columns WHERE TABLE_NAME = "'.$table->getName().'"';
        }
        $stmt = $handler->prepare($sql);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $key="";
                if($row["key"]==="t"||$row["key"]==="PRI"||$row["key"]){
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
     * Verifica si hubo cambios en una lista de tablas para un usuario
     * @param Table $table Tabla que se quiere verificar
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
     * Verifica si hubo eliminaciones en una tabla
     * @param Table $table Tabla que se quiere verificar
     * @param DateTime $lastUpdate Objeto de fecha con la última actualización
     * @return boolean True si se detectaron cambios, False en otro caso
     */
    function checkRowsDeleted($table,$lastUpdate){
        $changed=false;
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $sql='SELECT count(*) AS deleted FROM html5sync_deleted WHERE "table"=:table AND "date">:date';
        }elseif($this->db->getDriver()==="mysql"){
            $sql="SELECT count(*) AS deleted FROM html5sync_deleted WHERE `table`=:table AND  `date`>:date";
        }
        $stmt = $handler->prepare($sql);
        $name=$table->getName();
        $date=$lastUpdate->format('Y-m-d H:i:s');
        $stmt->bindParam(':table',$name);
        $stmt->bindParam(':date',$date);
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            $updated=intval($row["deleted"]);
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
     * @param int $initialRow [optional] Indica la fila desde la que debe cargar los registros
     * @param int $maxRows [optional] Máxima cantidad de registros a cargar
     * @return boolean True si se detectaron cambios, False en otro caso
     */
    function getUpdatedRows($table,$lastUpdate,$initialRow=0,$maxRows=1000){
        $list=array();
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $sql="SELECT * FROM ".$table->getName()." WHERE html5sync_update>'".$lastUpdate->format('Y-m-d H:i:s')."' LIMIT ".$maxRows." OFFSET ".$initialRow;
        }elseif($this->db->getDriver()==="mysql"){
            $sql="SELECT * FROM ".$table->getName()." WHERE html5sync_update>'".$lastUpdate->format('Y-m-d H:i:s')."' LIMIT ".$initialRow.",".$maxRows;
        }
        $stmt = $handler->prepare($sql);
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
     * Retorna un array con los datos de la tabla (un array por registro)
     * @param Table $table Tabla con nombre y lista de campos
     * @param int $initialRow [optional] Indica la fila desde la quedebe cargar los registros
     * @param int $maxRows [optional] Máxima cantidad de registros a cargar
     * @return array[] Array de arrays con los registros de la tabla
     */
    function getAllRows($table,$initialRow=0,$maxRows=1000){
        $list=array();
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $sql="SELECT * FROM ".$table->getName()." LIMIT ".$maxRows." OFFSET ".$initialRow;
        }elseif($this->db->getDriver()==="mysql"){
            $sql="SELECT * FROM ".$table->getName()." LIMIT ".$initialRow.",".$maxRows;
        }
        $stmt = $handler->prepare($sql);
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
     * @param Table $table Tabla con nombre en la base de datos
     */
    function setUpdatedColumnMode($table){
        $this->addTableForDeleted();
        $this->addColumns($table);
        $this->addTriggers($table);
    }
    /**
     * Retorna la cantidad de filas para una tabla, si se pasa una fecha de última 
     * modificación se retorna la cantidad de registros por cambiar
     * @param string $table Nombre de la tabla que se quiere verificar
     * @param mixed $lastUpdate (optional) fecha de la última modificación
     * @return int Cantidad de filas que tiene una tabla
     */
    function getTotalOfRows($table,$lastUpdate=false){
        $total=0;
        $handler=$this->db->connect("all");
        if($lastUpdate){
            $sql='SELECT count(*) AS total FROM '.$table->getName()." WHERE html5sync_update>'".$lastUpdate->format('Y-m-d H:i:s')."'";
        }else{
            $sql="SELECT count(*) AS total FROM ".$table->getName();
        }
        $stmt = $handler->prepare($sql);
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            $total=intval($row["total"]);
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
        }
        return $total;
    }
    /**
     * Crea una tabla para almacenar los registros de las tablas que han sido borrados
     * @param Table $table Tabla con nombre en la base de datos
     */
    private function addTableForDeleted(){
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $handler->query('CREATE TABLE IF NOT EXISTS html5sync_deleted (html5sync_id SERIAL PRIMARY KEY,html5sync_table varchar(40) NOT NULL,html5sync_key varchar(20) NOT NULL,html5sync_date timestamp DEFAULT NULL)');
            $sql="CREATE OR REPLACE FUNCTION html5sync_proc_delete() RETURNS TRIGGER AS $$ ".
                "DECLARE ".
                        "pk text; ".
                        "id text; ".
                        "keyText text; ".
                        "query text; ".
                "BEGIN  ".
                        "keyText := TG_TABLE_NAME||'_pkey'; ".
                        "EXECUTE 'SELECT column_name FROM information_schema.constraint_column_usage WHERE table_name='''||TG_TABLE_NAME||''' AND constraint_name='''||keyText||''';' INTO pk; ".
                        "query := 'SELECT '||pk||' FROM '||TG_TABLE_NAME||' WHERE '||pk||'=$1.'||pk||';'; ".
                        "EXECUTE query USING OLD INTO id; ".
                        "INSERT INTO html5sync_deleted  ".
                                "(html5sync_table,html5sync_key,html5sync_date)  ".
                        "VALUES ".
                                "(TG_TABLE_NAME,id,current_timestamp(0));  ".
                "RETURN OLD;  ".
                "END; $$ LANGUAGE plpgsql; ";
            $handler->query($sql);
        }elseif($this->db->getDriver()==="mysql"){
            $handler->query('CREATE TABLE IF NOT EXISTS html5sync_deleted (html5sync_id INT NOT NULL AUTO_INCREMENT,html5sync_table varchar(40) NOT NULL,html5sync_key varchar(20) NOT NULL,html5sync_date datetime DEFAULT NULL, PRIMARY KEY (html5sync_id))');
        }
    }
    /**
     * Agrega una columna que será alimentada con la fecha de actualización o insersión
     * por medio de un Trigger. Además crea una columna para almacenar el tipo
     * de transacción
     * @param Table $table Tabla con nombre en la base de datos
     */
    private function addColumns($table){
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $handler->query('ALTER TABLE '.$table->getName().' ADD COLUMN html5sync_update timestamp DEFAULT NULL');
        }elseif($this->db->getDriver()==="mysql"){
            $handler->query('ALTER TABLE '.$table->getName().' ADD COLUMN html5sync_update datetime DEFAULT NULL');
        }
        $handler->query("ALTER TABLE ".$table->getName()." ADD COLUMN html5sync_transaction VARCHAR(6) DEFAULT NULL");
    }
    /**
     * Función que crea un Trigger en la base de datos para almacenar la última
     * actualización y/o insersión en la tabla.
     * @param Table $table Tabla con nombre en la base de datos
     */
    private function addTriggers($table){
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $handler->query("CREATE OR REPLACE FUNCTION html5sync_proc_insert_".$table->getName()."() RETURNS TRIGGER AS $$ BEGIN NEW.html5sync_update := current_timestamp(0); NEW.html5sync_transaction := 'insert'; RETURN NEW; END; $$ LANGUAGE plpgsql;");
            $handler->query("CREATE OR REPLACE FUNCTION html5sync_proc_update_".$table->getName()."() RETURNS TRIGGER AS $$ BEGIN NEW.html5sync_update := current_timestamp(0); NEW.html5sync_transaction := 'update'; RETURN NEW; END; $$ LANGUAGE plpgsql;");
            $handler->query("CREATE TRIGGER html5sync_trig_insert_".$table->getName()." BEFORE INSERT ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_insert_".$table->getName()."();");
            $handler->query("CREATE TRIGGER html5sync_trig_update_".$table->getName()." BEFORE UPDATE ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_update_".$table->getName()."();");
            $handler->query("CREATE TRIGGER html5sync_trig_delete_".$table->getName()." BEFORE DELETE ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_delete();");
        }elseif($this->db->getDriver()==="mysql"){
            $handler->query('CREATE TRIGGER html5sync_trig_insert_'.$table->getName().' BEFORE INSERT ON '.$table->getName().' FOR EACH ROW BEGIN SET NEW.html5sync_update = NOW(), NEW.html5sync_transaction = "insert"; END;');
            $handler->query('CREATE TRIGGER html5sync_trig_update_'.$table->getName().' BEFORE UPDATE ON '.$table->getName().' FOR EACH ROW BEGIN SET NEW.html5sync_update = NOW(), NEW.html5sync_transaction = "update"; END;');
            $handler->query('CREATE TRIGGER html5sync_trig_delete_'.$table->getName().' BEFORE DELETE ON '.$table->getName().' FOR EACH ROW BEGIN INSERT INTO html5sync_deleted (html5sync_table,html5sync_date) VALUES("'.$table->getName().'",NOW()); END;');
        }
    }
}