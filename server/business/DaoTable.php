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
    
    //**************************************************************************
    //>>>>>>>>>>>>>>>>>>>>>>>>   DATABASE ACCESS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    //**************************************************************************
    /**
     * Carga una tabla de la base de datos
     * @param string $schema Nombre de la base de datos
     * @param string $tableName Nombre de la tabla que se quiere cargar
     * @param string $mode Modo de uso de la tabla: ('unlock': Para operaciones insert+read), ('lock': Para operaciones update+delete)
     * @return Table
     */
    function loadTable($schema,$tableName,$mode){
        $table=new Table($tableName);
        $table->setMode($mode);
        $table->setColumns($this->loadColumns($schema,$table));
        $this->loadFKs($schema,$table);
        return $table;
    }
    /**
     * Retorna la lista de campos de una Tabla
     * @param string $schema Nombre de la base de datos
     * @param Table $table Tabla con nombre en la base de datos
     * @return Column[] Lista de campos de la tabla
     */
    private function loadColumns($schema,$table){
        $list=array();
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $sql="
                SELECT DISTINCT
                    a.attnum as order,
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
            $sql='
                SELECT 
                    ORDINAL_POSITION AS `order`,
                    COLUMN_NAME AS `name`,
                    DATA_TYPE AS `type`,
                    IS_NULLABLE AS `nullable`,
                    EXTRA AS `autoincrement`,
                    COLUMN_KEY AS `key` 
                FROM 
                    information_schema.columns 
                WHERE 
                    TABLE_SCHEMA="'.$schema.'" AND 
                    TABLE_NAME = "'.$table->getName().'"
                ';
        }
        $stmt = $handler->prepare($sql);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $column=new Column($row["name"],$row["type"]);
                $column->setOrder($row["order"]);
                if($row["key"]==="t"||$row["key"]==="PRI"||$row["key"]===true){
                    $column->setPk(true);
                }
                if(strpos($row["type"],"int")!==false||strpos($row["type"],"numeric")!==false){
                    $column->setType("int");
                }elseif(strpos($row["type"],"double")!==false||strpos($row["type"],"real")!==false){
                    $column->setType("double");
                }elseif(strpos($row["type"],"char")!==false){
                    $column->setType("varchar");
                }elseif(strpos($row["type"],"timestamp")!==false||strpos($row["type"],"date")!==false){
                    $column->setType("datetime");
                }else{
                    $column->setType($row["type"]);
                }
                if($this->db->getDriver()==="pgsql"){
                    if($row["notnull"]===true){
                        $column->setNotNull(true);
                    }
                    if(strpos($row["default"],"nextval")!==false){
                        $column->setAutoIncrement(true);
                    }
                }elseif($this->db->getDriver()==="mysql"){
                    if($row["nullable"]==="NO"){
                        $column->setNotNull(true);
                    }
                    if(strpos($row["autoincrement"],"auto_increment")!==false){
                        $column->setAutoIncrement(true);
                    }
                }
                if($row["key"]==="t"||$row["key"]==="PRI"||$row["key"]===true){
                    $column->setPk(true);
                }
                array_push($list,$column);
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
        }
        return $list;
    }
    /**
     * Carga la tabla con los datos de las FK
     * @param string $schema Nombre de la base de datos
     * @param Table $table Tabla con nombre en la base de datos
     * @return Table byREF: Tabla con la FK's cargadas
     */
    private function loadFKs($schema,$table){
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $sql="
                SELECT
                    tc.constraint_name, tc.table_name, kcu.column_name, 
                    ccu.table_name AS foreign_table_name,
                    ccu.column_name AS foreign_column_name 
                FROM 
                    information_schema.table_constraints AS tc 
                    JOIN information_schema.key_column_usage AS kcu
                      ON tc.constraint_name = kcu.constraint_name
                    JOIN information_schema.constraint_column_usage AS ccu
                      ON ccu.constraint_name = tc.constraint_name
                WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name='".$table->getName()."';
                ";
        }elseif($this->db->getDriver()==="mysql"){
            $sql='
                SELECT 
                    column_name,
                    referenced_table_name AS foreign_table_name,
                    referenced_column_name AS foreign_column_name 
                FROM 
                    information_schema.key_column_usage 
                WHERE 
                    referenced_table_name IS NOT NULL AND 
                    table_name="'.$table->getName().'"
                ';
        }
        $stmt = $handler->prepare($sql);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                foreach ($table->getColumns() as $column) {
                    if($row["column_name"]===$column->getName()){
                        $column->setFk(true);
                        $column->setFkTable($row["foreign_table_name"]);
                        $column->setFkColumn($row["foreign_column_name"]);
                    }
                }
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
        }
    }
    /**
     * Retorna la cantidad de filas de una tabla.
     * @param string $table Nombre de la tabla que se quiere verificar
     * @return int Cantidad de filas que tiene una tabla
     */
    public function countRows($table){
        $total=0;
        $handler=$this->db->connect("all");
        $sql="SELECT COUNT(*) AS total FROM ".$table->getName();
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
     * Retorna un array con los datos de la tabla (un array por registro)
     * @param Table $table Tabla con nombre y lista de campos
     * @param int $initialRow [optional] Indica la fila desde la quedebe cargar los registros
     * @param int $maxRows [optional] Máxima cantidad de registros a cargar
     * @return array[] Array de arrays con los registros de la tabla
     */
    function getRows($table,$initialRow=0,$maxRows=1000){
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
    
    //**************************************************************************
    //>>>>>>>>>>>>>>>>>>>>>   DATABASE PREPARATION   <<<<<<<<<<<<<<<<<<<<<<<<<<<
    //**************************************************************************
    /**
     * Crea la tabla de transacciones en la base de datos BusinessDB.
     */
    public function createTransactionsTable(){
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $handler->query('CREATE TABLE IF NOT EXISTS html5sync (html5sync_id SERIAL PRIMARY KEY,html5sync_table varchar(40) NOT NULL,html5sync_key varchar(20) NOT NULL,html5sync_date timestamp DEFAULT NULL,html5sync_transaction varchar(20) NOT NULL)');
        }elseif($this->db->getDriver()==="mysql"){
            $handler->query('CREATE TABLE IF NOT EXISTS html5sync (html5sync_id INT NOT NULL AUTO_INCREMENT,html5sync_table varchar(40) NOT NULL,html5sync_key varchar(20) NOT NULL,html5sync_date datetime DEFAULT NULL, html5sync_transaction varchar(40) NOT NULL, PRIMARY KEY (html5sync_id))');
        }
    }
    /**
     * Crea el procedimiento almacenado para el trigger de la tabla de transacciones
     * Actualmente solo aplica para bases de datos PostgreSQL. Para bases de datos
     * MySQL el procedimiento se inserta directamente en el Trigger
     * @param Table $table Tabla sobre la que se crearán los procedimientos
     */
    public function createTransactionsProcedures($table){
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $pk=$table->getPk();
            $sql="CREATE OR REPLACE FUNCTION html5sync_proc_".$table->getName()."() RETURNS TRIGGER AS $$ ".
                "DECLARE ".
                        "id text;".
                "BEGIN  ".
                        "IF TG_OP = 'INSERT' THEN ".
                            "id := NEW.".$pk->getName()."; ".
                        "ELSE ".
                            "id := OLD.".$pk->getName()."; ".
                        "END IF; ".
                        "INSERT INTO html5sync  ".
                                "(html5sync_table,html5sync_key,html5sync_date,html5sync_transaction)  ".
                        "VALUES ".
                                "(TG_TABLE_NAME,id,current_timestamp(0),TG_OP);  ".
                "IF TG_OP = 'DELETE' THEN ".
                    "RETURN OLD;  ".
                "ELSE ".
                    "RETURN NEW;  ".
                "END IF; ".
                "END; $$ LANGUAGE plpgsql; ";
            $handler->query($sql);
        }
    }
    /**
     * Crea el conjunto de triggers de la tabla de transacciones
     * @param Table $table Tabla sobre la que se crearán los triggers
     */
    public function createTransactionsTriggers($table){
        $handler=$this->db->connect("all");
        if($this->db->getDriver()==="pgsql"){
            $handler->query("CREATE TRIGGER html5sync_trig_insert_".$table->getName()." BEFORE INSERT ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_".$table->getName()."();");
            $handler->query("CREATE TRIGGER html5sync_trig_update_".$table->getName()." BEFORE UPDATE ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_".$table->getName()."();");
            $handler->query("CREATE TRIGGER html5sync_trig_delete_".$table->getName()." BEFORE DELETE ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_".$table->getName()."();");
        }elseif($this->db->getDriver()==="mysql"){
            $pk=$table->getPk();
            //Se inserta el trigger para cada operación si la columna tiene PK
            if($pk){
                $handler->query('CREATE TRIGGER html5sync_trig_insert_'.$table->getName().' AFTER INSERT ON '.$table->getName().' FOR EACH ROW BEGIN DECLARE id TEXT; SELECT '.$pk->getName().' FROM '.$table->getName().' WHERE '.$pk->getName().'=NEW.'.$pk->getName().' INTO id; INSERT INTO html5sync (html5sync_table,html5sync_key,html5sync_date,html5sync_transaction) VALUES("'.$table->getName().'",id,NOW(),"INSERT"); END;');
                $handler->query('CREATE TRIGGER html5sync_trig_update_'.$table->getName().' BEFORE UPDATE ON '.$table->getName().' FOR EACH ROW BEGIN DECLARE id TEXT; SELECT '.$pk->getName().' FROM '.$table->getName().' WHERE '.$pk->getName().'=OLD.'.$pk->getName().' INTO id; INSERT INTO html5sync (html5sync_table,html5sync_key,html5sync_date,html5sync_transaction) VALUES("'.$table->getName().'",id,NOW(),"UPDATE"); END;');
                $handler->query('CREATE TRIGGER html5sync_trig_delete_'.$table->getName().' BEFORE DELETE ON '.$table->getName().' FOR EACH ROW BEGIN DECLARE id TEXT; SELECT '.$pk->getName().' FROM '.$table->getName().' WHERE '.$pk->getName().'=OLD.'.$pk->getName().' INTO id; INSERT INTO html5sync (html5sync_table,html5sync_key,html5sync_date,html5sync_transaction) VALUES("'.$table->getName().'",id,NOW(),"DELETE"); END;');
            }
        }
    }
    //**************************************************************************
    //>>>>>>>>>>>>>>>>>>>>>>>   DATABASE QUERIES   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    //**************************************************************************
    /**
     * Retorna la lista de transacciones filtradas, es decir, si hay un delete 
     * después de un update en un mismo registro, retorna solo el delete.
     * @param DateTime $lastUpdate Objeto de fecha con la última fecha de actualización
     * @return 
     */
    public function getLastTransactions($lastUpdate){
        $list=array();
        $handler=$this->db->connect("all");
        $stmt = $handler->prepare("
            SELECT temp.*
            FROM html5sync temp
            INNER JOIN
                (SELECT html5sync_table,html5sync_key, MAX(html5sync_date) AS MaxDateTime
                FROM html5sync
                WHERE html5sync_date>:lastUpdate GROUP BY html5sync_table,html5sync_key) tempGroup 
            ON temp.html5sync_table = tempGroup.html5sync_table 
            AND temp.html5sync_date = tempGroup.MaxDateTime ORDER BY temp.html5sync_date;"
        );
        $date=$lastUpdate->format('Y-m-d H:i:s');
        $stmt->bindParam(':lastUpdate',$date);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()){
                $transaction=new Transaction($row["html5sync_id"],$row["html5sync_transaction"],$row["html5sync_table"],$row["html5sync_key"],$row["html5sync_date"]);
                array_push($list,$transaction);
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
        }
        return $list;
    }
    /**
     * Retorna un registro de una tabla desde la base de datos del negocio BusinessDB
     * @param Table $table Objeto de tipo tabla para retornar el registro
     * @param mixed $key Clave del registro que se quiere cargar
     */
    public function getRowOfTable($table,$key){
        $register = false;
        $handler=$this->db->connect("all");
        $pk=$table->getPk();
        $stmt = $handler->prepare("SELECT * FROM ".$table->getName()." WHERE ".$pk->getName()."=:key");
        $stmt->bindParam(':key',$key);
        if ($stmt->execute()) {
            $row=$stmt->fetch(PDO::FETCH_ASSOC);
            if($row){
                $register=$row;
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
        }
        return $register;
    }
























    /**
     * Verifica si hubo eliminaciones en una tabla
     * @param Table $table Tabla que se quiere verificar
     * @param DateTime $lastUpdate Objeto de fecha con la última actualización
     * @return boolean True si se detectaron cambios, False en otro caso
     */
//    function checkIfRowsDeleted($table,$lastUpdate){
//        $deleted=false;
//        $handler=$this->db->connect("all");
//        $sql='SELECT count(*) AS deleted FROM html5sync_deleted WHERE html5sync_table=:table AND html5sync_date>:date';
//        $stmt = $handler->prepare($sql);
//        $name=$table->getName();
//        $date=$lastUpdate->format('Y-m-d H:i:s');
//        $stmt->bindParam(':table',$name);
//        $stmt->bindParam(':date',$date);
//        if ($stmt->execute()) {
//            $row=$stmt->fetch();
//            $updated=intval($row["deleted"]);
//            if($updated){
//                $deleted=true;
//            }
//        }else{
//            $error=$stmt->errorInfo();
//            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
//        }
//        return $deleted;
//    }
    /**
     * Retorna los registros que han sido modificados
     * @param string $table Nombre de la tabla que se quiere verificar
     * @param DateTime $lastUpdate Objeto de fecha con la última actualización
     * @param int $initialRow [optional] Indica la fila desde la que debe cargar los registros
     * @param int $maxRows [optional] Máxima cantidad de registros a cargar
     * @return boolean True si se detectaron cambios, False en otro caso
     */
//    function getUpdatedRows($table,$lastUpdate,$initialRow=0,$maxRows=1000){
//        $list=array();
//        $handler=$this->db->connect("all");
//        if($this->db->getDriver()==="pgsql"){
//            $sql="SELECT * FROM ".$table->getName()." WHERE html5sync_update>'".$lastUpdate->format('Y-m-d H:i:s')."' LIMIT ".$maxRows." OFFSET ".$initialRow;
//        }elseif($this->db->getDriver()==="mysql"){
//            $sql="SELECT * FROM ".$table->getName()." WHERE html5sync_update>'".$lastUpdate->format('Y-m-d H:i:s')."' LIMIT ".$initialRow.",".$maxRows;
//        }
//        $stmt = $handler->prepare($sql);
//        if ($stmt->execute()) {
//            while ($row = $stmt->fetch()){
//                $register=array();
//                foreach ($table->getFields() as $field) {
//                    array_push($register,$row[$field->getName()]);
//                }
//                array_push($list,$register);
//            }
//        }else{
//            $error=$stmt->errorInfo();
//            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
//        }
//        return $list;
//    }
    /**
     * Retorna un array con los datos de la tabla (un array por registro)
     * @param Table $table Tabla con nombre y lista de campos
     * @param int $initialRow [optional] Indica la fila desde la quedebe cargar los registros
     * @param int $maxRows [optional] Máxima cantidad de registros a cargar
     * @return array[] Array de arrays con los registros de la tabla
     */
//    function getAllRows($table,$initialRow=0,$maxRows=1000){
//        $list=array();
//        $handler=$this->db->connect("all");
//        if($this->db->getDriver()==="pgsql"){
//            $sql="SELECT * FROM ".$table->getName()." LIMIT ".$maxRows." OFFSET ".$initialRow;
//        }elseif($this->db->getDriver()==="mysql"){
//            $sql="SELECT * FROM ".$table->getName()." LIMIT ".$initialRow.",".$maxRows;
//        }
//        $stmt = $handler->prepare($sql);
//        if ($stmt->execute()) {
//            while ($row = $stmt->fetch()){
//                $register=array();
//                foreach ($table->getFields() as $field) {
//                    array_push($register,$row[$field->getName()]);
//                }
//                array_push($list,$register);
//            }
//        }else{
//            $error=$stmt->errorInfo();
//            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
//        }
//        return $list;
//    }
    /**
     * Define el modo UpdatedColumn. Inserta una columna donde se lleva la cuenta
     * de las insersiones y/o actualizaciones en una tabla. Crea un Trigger que
     * realiza el proceso.
     * @param Table $table Tabla con nombre en la base de datos
     */
//    function setUpdatedColumnMode($table){
//        $this->addTableForDeleted();
//        $this->addColumns($table);
//        $this->addTriggers($table);
//    }
    /**
     * Retorna la cantidad de filas para una tabla, si se pasa una fecha de última 
     * modificación se retorna la cantidad de registros por cambiar
     * @param string $table Nombre de la tabla que se quiere verificar
     * @param mixed $lastUpdate (optional) fecha de la última modificación
     * @return int Cantidad de filas que tiene una tabla
     */
//    function getTotalOfRows($table,$lastUpdate=false){
//        $total=0;
//        $handler=$this->db->connect("all");
//        if($lastUpdate){
//            $sql='SELECT count(*) AS total FROM '.$table->getName()." WHERE html5sync_update>'".$lastUpdate->format('Y-m-d H:i:s')."'";
//        }else{
//            $sql="SELECT count(*) AS total FROM ".$table->getName();
//        }
//        $stmt = $handler->prepare($sql);
//        if ($stmt->execute()) {
//            $row=$stmt->fetch();
//            $total=intval($row["total"]);
//        }else{
//            $error=$stmt->errorInfo();
//            error_log("[".__FILE__.":".__LINE__."]"."html5sync: ".$error[2]);
//        }
//        return $total;
//    }
    /**
     * Crea una tabla para almacenar los registros de las tablas que han sido borrados
     * @param Table $table Tabla con nombre en la base de datos
     */
//    private function addTableForDeleted(){
//        $handler=$this->db->connect("all");
//        if($this->db->getDriver()==="pgsql"){
//            $handler->query('CREATE TABLE IF NOT EXISTS html5sync_deleted (html5sync_id SERIAL PRIMARY KEY,html5sync_table varchar(40) NOT NULL,html5sync_key varchar(20) NOT NULL,html5sync_date timestamp DEFAULT NULL)');
//            $sql="CREATE OR REPLACE FUNCTION html5sync_proc_delete() RETURNS TRIGGER AS $$ ".
//                "DECLARE ".
//                        "pk text; ".
//                        "id text; ".
//                        "keyText text; ".
//                        "query text; ".
//                "BEGIN  ".
//                        "keyText := TG_TABLE_NAME||'_pkey'; ".
//                        "EXECUTE 'SELECT column_name FROM information_schema.constraint_column_usage WHERE table_name='''||TG_TABLE_NAME||''' AND constraint_name='''||keyText||''';' INTO pk; ".
//                        "query := 'SELECT '||pk||' FROM '||TG_TABLE_NAME||' WHERE '||pk||'=$1.'||pk||';'; ".
//                        "EXECUTE query USING OLD INTO id; ".
//                        "INSERT INTO html5sync_deleted  ".
//                                "(html5sync_table,html5sync_key,html5sync_date)  ".
//                        "VALUES ".
//                                "(TG_TABLE_NAME,id,current_timestamp(0));  ".
//                "RETURN OLD;  ".
//                "END; $$ LANGUAGE plpgsql; ";
//            $handler->query($sql);
//        }elseif($this->db->getDriver()==="mysql"){
//            $handler->query('CREATE TABLE IF NOT EXISTS html5sync_deleted (html5sync_id INT NOT NULL AUTO_INCREMENT,html5sync_table varchar(40) NOT NULL,html5sync_key varchar(20) NOT NULL,html5sync_date datetime DEFAULT NULL, PRIMARY KEY (html5sync_id))');
//        }
//    }
    /**
     * Agrega una columna que será alimentada con la fecha de actualización o insersión
     * por medio de un Trigger. Además crea una columna para almacenar el tipo
     * de transacción
     * @param Table $table Tabla con nombre en la base de datos
     */
//    private function addColumns($table){
//        $handler=$this->db->connect("all");
//        if($this->db->getDriver()==="pgsql"){
//            $handler->query('ALTER TABLE '.$table->getName().' ADD COLUMN html5sync_update timestamp DEFAULT NULL');
//        }elseif($this->db->getDriver()==="mysql"){
//            $handler->query('ALTER TABLE '.$table->getName().' ADD COLUMN html5sync_update datetime DEFAULT NULL');
//        }
//        $handler->query("ALTER TABLE ".$table->getName()." ADD COLUMN html5sync_transaction VARCHAR(6) DEFAULT NULL");
//    }
    /**
     * Función que crea un Trigger en la base de datos para almacenar la última
     * actualización y/o insersión en la tabla.
     * @param Table $table Tabla con nombre en la base de datos
     */
//    private function addTriggers($table){
//        $handler=$this->db->connect("all");
//        if($this->db->getDriver()==="pgsql"){
//            $handler->query("CREATE OR REPLACE FUNCTION html5sync_proc_insert_".$table->getName()."() RETURNS TRIGGER AS $$ BEGIN NEW.html5sync_update := current_timestamp(0); NEW.html5sync_transaction := 'insert'; RETURN NEW; END; $$ LANGUAGE plpgsql;");
//            $handler->query("CREATE OR REPLACE FUNCTION html5sync_proc_update_".$table->getName()."() RETURNS TRIGGER AS $$ BEGIN NEW.html5sync_update := current_timestamp(0); NEW.html5sync_transaction := 'update'; RETURN NEW; END; $$ LANGUAGE plpgsql;");
//            $handler->query("CREATE TRIGGER html5sync_trig_insert_".$table->getName()." BEFORE INSERT ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_insert_".$table->getName()."();");
//            $handler->query("CREATE TRIGGER html5sync_trig_update_".$table->getName()." BEFORE UPDATE ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_update_".$table->getName()."();");
//            $handler->query("CREATE TRIGGER html5sync_trig_delete_".$table->getName()." BEFORE DELETE ON ".$table->getName()." FOR EACH ROW EXECUTE PROCEDURE html5sync_proc_delete();");
//        }elseif($this->db->getDriver()==="mysql"){
//            $pk=$table->getPk();
//            $handler->query('CREATE TRIGGER html5sync_trig_insert_'.$table->getName().' BEFORE INSERT ON '.$table->getName().' FOR EACH ROW BEGIN SET NEW.html5sync_update = NOW(), NEW.html5sync_transaction = "insert"; END;');
//            $handler->query('CREATE TRIGGER html5sync_trig_update_'.$table->getName().' BEFORE UPDATE ON '.$table->getName().' FOR EACH ROW BEGIN SET NEW.html5sync_update = NOW(), NEW.html5sync_transaction = "update"; END;');
//            //Se inserta el trigger de borrado si la columna tiene PK
//            if($pk){
//                $sql='CREATE TRIGGER '.
//                            'html5sync_trig_delete_'.$table->getName().' '.
//                    'BEFORE DELETE ON '.$table->getName().' '.
//                    'FOR EACH ROW BEGIN '.
//                            'DECLARE id TEXT;'.
//                            'SELECT '.$pk->getName().' FROM '.$table->getName().' WHERE '.$pk->getName().'=OLD.'.$pk->getName().' INTO id;'.
//                            'INSERT INTO html5sync_deleted (html5sync_table,html5sync_key,html5sync_date) VALUES("'.$table->getName().'",id,NOW());'.
//                    'END;';
//                $handler->query($sql);
//            }
//        }
//    }
}