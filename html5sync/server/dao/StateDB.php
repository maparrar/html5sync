<?php
/** StateDB File
* @package core @subpackage  */
/**
* StateDB Class
* Clase para manejo de la base de datos de estado en SQLite. Esta base de datos
* mantiene la relación entre los usuarios y las tablas de la base de datos de la
* aplicación.
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package core
* @subpackage 
*/
class StateDB{
    /** PDO handler object 
     * @var PDO
     */
    protected $handler;
    /** 
     * Ruta y nombre de la base de datos
     * 
     * @var string
     */
    protected $path;
    /**
    * Constructor
    * @param string $path Ruta y nombre de la base de datos
    */
    function __construct($path="../sqlite/html5sync.sqlite"){        
        $this->path=$path;
        $this->createDB($this->path);
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter path
    * @param string $value Ruta y nombre de la base de datos
    * @return void
    */
    public function setPath($value) {
        $this->path=$value;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Getter: path
    * @return string
    */
    public function getPath() {
        return $this->path;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    
    /**
     * Verifica si la base de datos existe. Si no existe retorna false
     * @param type $path Ruta de la base de datos
     * @return boolean True si la base de datos existe, false en otro caso
     */
    private function existDB($path){
        return file_exists($path);
    }
    
    /**
     * Crea la estructura de la base de datos de estado
     * @param type $path Ruta de la base de datos
     */
    private function createDB($path){
        try{
            $this->handler=new PDO('sqlite:'.$path);
            $query="
                CREATE TABLE IF NOT EXISTS `User` (
                    `id` INTEGER NOT NULL PRIMARY KEY,
                    `versionDB` INTEGER
                );
                CREATE TABLE IF NOT EXISTS `Table` (
                    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                    `name` TEXT
                );
                CREATE TABLE IF NOT EXISTS `TableState` (
                    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                    `date` TEXT,
                    `versionDB` INTEGER,
                    `table` INTEGER NOT NULL,
                    `user` INTEGER NOT NULL
                );
                CREATE TABLE IF NOT EXISTS `Field` (
                    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                    `name` TEXT,
                    `type` TEXT,
                    `key` TEXT,
                    `tableState` INTEGER NOT NULL
                );
            ";
            // Crea las tablas
            $this->handler->exec($query);
        } catch (Exception $ex) {
            error_log($ex->getMessage());
        }
    }
    
    /**
     * Verifica para cada usuario si de la lista de tablas al menos una ha cambiado
     * su estructura. Si ha cambiado, retorna el nuevo número de versión. Si no
     * ha cambiado, retorna false.
     * @param int $userId Id del usuario
     * @param Table[] $tables Lista de tablas del usuario
     * @return mixed El número de la versión si hubo algún cambio en la estructura 
     * de la base de datos, false en otro caso.
     */
    function checkChanges($userId,$tables){
        //Agrega las tablas no existentes a la base de datos de estado
        foreach ($tables as $table) {
            if(!$this->tableExists($table->getName())){
                $this->tableCreate($table->getName());
            }
        }
        //Verifica si el usuario existe, sino, lo agrega
        if(!$this->userExists($userId)){
            $this->userCreate($userId);
            $this->updateState($userId,$tables,1);
        }
        
        
        
        
        return false;
    }
    
    /**
     * Actualiza el estado de las tablas para un usuario insertándolas en TableState
     * @param int $userId Identificador del usuario
     * @param Table $tables Lista de tablas
     * @param int $version Número de versión de la base de datos a la que corresponde el estado de las tablas
     * @return boolean True si se pudieron insertar, false en otro caso
     */
    private function updateState($userId,$tables,$version){
        foreach ($tables as $table) {
            $this->tableStateCreate($userId,$table,$version);
        }
    }
    /**************************************************************************/
    /******************************   U S E R S  ******************************/
    /**************************************************************************/
    /**
     * Inserta un usuario en la tabla de User
     * @param int $userId Identificador del usuario
     * @return boolean True si se pudo insertar el usuario, false en otro caso
     */
    private function userCreate($userId){
        $created=false;
        if(!$this->userExists($userId)){
            $stmt = $this->handler->prepare("INSERT INTO User (`id`,`versionDB`) VALUES (:id,:versionDB)");
            $startingVersion=1;
            $stmt->bindParam(':id',$userId);
            $stmt->bindParam(':versionDB',$startingVersion);
            if(!$stmt->execute()){
                $error=$stmt->errorInfo();
                error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
            }
        }
        return $created;
    }
    /**
     * Verifica si un usuario existe en la base de datos de estado
     * @param int $userId Identificador del usuario
     * @return boolean True si el usuario existe, false en otro caso
     */
    private function userExists($userId){
        $exist=false;
        $stmt = $this->handler->prepare("SELECT id FROM User WHERE id=:id");
        $stmt->bindParam(':id',$userId);
        if ($stmt->execute()) {
            $list=$stmt->fetch();
            if($list){
                if(intval($list["id"])===intval($userId)){
                    $exist=true;
                }else{
                    $exist=false;
                }
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
        }
        return $exist;
    }
    /**************************************************************************/
    /*****************************   T A B L E S  *****************************/
    /**************************************************************************/
    /**
     * Inserta una tabla en la base de datos de estado
     * @param string $tableName Nombre de la tabla
     * @return boolean True si se pudo insertar, false en otro caso
     */
    private function tableCreate($tableName){
        $created=false;
        if(!$this->tableExists($tableName)){
            $stmt = $this->handler->prepare("INSERT INTO `Table` (`name`) VALUES (:name)");
            $stmt->bindParam(':name',$tableName);
            if(!$stmt->execute()){
                $error=$stmt->errorInfo();
                error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
            }
        }
        return $created;
    }
    /**
     * Retorna el id de una tabla a partir de su nombre
     * @param string $tableName Nombre de la tabla
     * @return int Identificador de la tabla
     */
    function tableRead($tableName){
        $response=false;
        $stmt = $this->handler->prepare("SELECT `id` FROM `Table` WHERE `name`= :name");
        $stmt->bindParam(':name',$tableName);
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            $response=intval($row["id"]);
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $response;
    }
    /**
     * Verifica si una tabla existe en la base de datos de estado
     * @param string $tableName Nombre de la tabla
     * @return boolean True si la tabla existe, false en otro caso
     */
    private function tableExists($tableName){
        $exist=false;
        $stmt = $this->handler->prepare("SELECT name FROM `Table` WHERE `name`=:name");
        $stmt->bindParam(':name',$tableName);
        if ($stmt->execute()) {
            $list=$stmt->fetch();
            if($list){
                if($list["name"]===$tableName){
                    $exist=true;
                }else{
                    $exist=false;
                }
            }
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
        }
        return $exist;
    }
    /**************************************************************************/
    /***********************   T A B L E    S T A T E  ************************/
    /**************************************************************************/
    /**
     * Inserta un estado de una tabla en la base de datos
     * @param int $userId Identificador del usuario
     * @param Table $table Objeto de tipo Table
     * @param int $version Número de versión de la base de datos a la que corresponde el estado de la tabla
     * @return boolean True si se pudo insertar, false en otro caso
     */
    private function tableStateCreate($userId,$table,$version){
        $created=false;
        if($this->tableExists($table->getName())){
            $stmt = $this->handler->prepare("
                INSERT INTO `TableState` 
                    (`date`,`versionDB`,`table`,`user`) 
                VALUES 
                    (:date,:versionDB,:table,:user)
            ");
            $date=date('Y-m-d H:i:s');
            $stmt->bindParam(':date',$date);
            $stmt->bindParam(':versionDB',$version);
            $stmt->bindParam(':table',$this->tableRead($table->getName()));
            $stmt->bindParam(':user',$userId);
            if(!$stmt->execute()){
                $error=$stmt->errorInfo();
                error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
            }else{
                $fields=$table->getFields();
                $tableStateId=intval($this->handler->lastInsertID());
                foreach ($fields as $field) {
                    $this->fieldCreate($tableStateId,$field);
                }
            }
        }
        return $created;
    }
    /**************************************************************************/
    /*****************************   F I E L D S  *****************************/
    /**************************************************************************/
    /**
     * Inserta una campo en la base de datos de estado
     * @param string $tableStateId Identificador de la tabla de estado
     * @param Field $field Objeto de tipo campo
     * @return boolean True si se pudo insertar, false en otro caso
     */
    private function fieldCreate($tableStateId,$field){
        $created=false;
        if(!$this->tableExists($tableName)){
            $stmt = $this->handler->prepare("
                INSERT INTO `Field` 
                    (`name`,`type`,`key`,`tableState`) 
                VALUES 
                    (:name,:type,:key,:tableState)
            ");
            $stmt->bindParam(':name',$field->getName());
            $stmt->bindParam(':type',$field->getType());
            $stmt->bindParam(':key',$field->getKey());
            $stmt->bindParam(':tableState',intval($tableStateId));
            if(!$stmt->execute()){
                $error=$stmt->errorInfo();
                error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
            }
        }
        return $created;
    }
}