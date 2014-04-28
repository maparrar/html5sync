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
    * @param User $user Usuario para verificar si existe en la base de datos
    */
    function __construct($user){        
        $this->path="../state/sqlite/html5sync_".$user->getId().".sqlite";
        //Crea la base de datos si no existe
        $this->createDB($this->path);
        //Si el usuario no existe en la base de datos, lo crea
        if(!$this->userExists($user)){
            $this->userCreate($user);
        }
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
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   GETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
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
            /*status: When is synchronizing: ['sync'|'idle']*/
            $query="
                CREATE TABLE IF NOT EXISTS `User` (
                    `id` INTEGER NOT NULL PRIMARY KEY,
                    `versionDB` INTEGER NOT NULL,
                    `lastUpdate` TEXT
                );
                CREATE TABLE IF NOT EXISTS `Table` (
                    `name` TEXT NOT NULL PRIMARY KEY,
                    `hashTable` TEXT,
                    `lastUpdate` TEXT,
                    `status` TEXT 
                );
            ";
            // Crea las tablas
            $this->handler->exec($query);
        } catch (Exception $ex) {
            error_log($ex->getMessage());
        }
    }
    
    
    
    /**************************************************************************/
    /******************************   DATABASE  *******************************/
    /**************************************************************************/
    /**
     * Retorna el número de la versión para el usuario
     * @param User $user Objeto Usuario
     * @return int Versión la base de datos para el usuario
     */
    public function getVersion($user){
        $response=false;
        $stmt = $this->handler->prepare("SELECT `versionDB` FROM `User` WHERE `id`= :id");
        $id=$user->getId();  //For strict PHP
        $stmt->bindParam(':id',$id);
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            $response=intval($row["versionDB"]);
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $response;
    }
    /**
     * Aumenta en uno la versión de la base de datos de usuario y retorna la resultante
     * @param User $user Objeto Usuario
     * @return int Versión la base de datos para el usuario
     */
    public function increaseVersion($user){
        $stmt = $this->handler->prepare("UPDATE User SET 
            `versionDB`=:versionDB 
            WHERE id=:id");
        $version=$this->getVersion($user)+1;
        $id=$user->getId();  //For strict PHP
        $stmt->bindParam(':id',$id);
        $stmt->bindParam(':versionDB',$version);
        if(!$stmt->execute()){
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
        }
        return $this->getVersion($user);
    }
    
    
    /**************************************************************************/
    /********************************   USERS  ********************************/
    /**************************************************************************/
    /**
     * Verifica si un usuario existe en la base de datos de estado
     * @param User $user Objeto Usuario
     * @return boolean True si el usuario existe, false en otro caso
     */
    private function userExists($user){
        $exist=false;
        $stmt = $this->handler->prepare("SELECT id FROM User WHERE id=:id");
        $id=$user->getId();  //For strict PHP
        $stmt->bindParam(':id',$id);
        if ($stmt->execute()) {
            $list=$stmt->fetch();
            if($list){
                if(intval($list["id"])===intval($user->getId())){
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
    /**
     * Inserta un usuario en la tabla de User
     * @param User $user Objeto Usuario
     * @return boolean True si se pudo insertar el usuario, false en otro caso
     */
    private function userCreate($user){
        $created=false;
        if(!$this->userExists($user)){
            $stmt = $this->handler->prepare("
                INSERT INTO User (`id`,`versionDB`,`lastUpdate`) 
                VALUES           (:id,:versionDB,:lastUpdate)
            ");
            $version=1;
            $id=$user->getId();  //For strict PHP
            $role=$user->getRole();  //For strict PHP
            $date=date('Y-m-d H:i:s');
            $stmt->bindParam(':id',$id);
            $stmt->bindParam(':versionDB',$version);
            $stmt->bindParam(':lastUpdate',$date);
            if(!$stmt->execute()){
                $error=$stmt->errorInfo();
                error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
            }
        }
        return $created;
    }
    
    /**************************************************************************/
    /*******************************   TABLES  ********************************/
    /**************************************************************************/
    /**
     * Verify if a table already exists
     * @param Table $table Object Table
     * @return boolean True if the table exists, false otherwise
     */
    private function tableExists($table){
        $exist=false;
        $stmt = $this->handler->prepare("SELECT name FROM `Table` WHERE name=:name");
        $name=$table->getName();  //For strict PHP
        $stmt->bindParam(':name',$name);
        if ($stmt->execute()) {
            $list=$stmt->fetch();
            if($list){
                if($list["name"]===$table->getName()){
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
    /**
     * Insert a Table (or array of tables) in this database
     * @param Table[] $tables Table to inser into database
     */
    public function insertTables($tables){
        if(!is_array($tables)){
            $listTables=array($tables);
        }else{
            $listTables=$tables;
        }
        foreach ($listTables as $table) {
            $this->insertTable($table);
        }
    }
    /**
     * Insert a table in database.
     * @param Table $table Table to insert
     */
    private function insertTable($table){
        if(!$this->tableExists($table)){
            $stmt = $this->handler->prepare("
                INSERT INTO `Table` (`name`,`hashTable`,`lastUpdate`,`status`) 
                VALUES           (:name,:hashTable,:lastUpdate,:status)
            ");
            $name=$table->getName();  //For strict PHP
            $hashTable=md5(mt_rand(0,100000));  //For strict PHP
            $lastUpdate=date('Y-m-d H:i:s');
            $status="idle";
            $stmt->bindParam(':name',$name);
            $stmt->bindParam(':hashTable',$hashTable);
            $stmt->bindParam(':lastUpdate',$lastUpdate);
            $stmt->bindParam(':status',$status);
            if(!$stmt->execute()){
                $error=$stmt->errorInfo();
                error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
            }
        }
    }
    /**
     * Delete the tables for the user
     */
    public function deleteTables(){
        $stmt = $this->handler->prepare("DELETE FROM `Table`");
        if(!$stmt->execute()){
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
        }
    }


























    /**
     * Verifica para cada usuario si la estructura de las tablas ha cambiado por
     * medio de una función de hash. 
     * @param string $state Estructura de las tablas en JSON
     * @param User $user Objeto Usuario
     * @return int El número de la versión de la base de datos
     */
//    public function checkIfStructureChanged($state,$user){
//        $changed=false;
//        //Verifica si el usuario existe, sino, lo agrega e inserta el estado inicial
////        if(!$this->userExists($user)){
////            $this->userCreate(md5("emptystructure"),$user);
////            $changed=true;
////        }else{
//            //Retorna el último estado
//            $oldState=$this->getHashTable($user);
//            $newState=md5($state);
//            if($newState!=$oldState){
//                $changed=true;
//            }
////        }
//        return  $changed;
//    }
    /**
     * Retorna la fecha de la última actualización almacenada en la base de datos de estado
     * @param User $user Objeto Usuario
     * @return DateTime Objeto con la fecha de la última actualización
     */
//    public function getLastUpdate($user){
//        $response=false;
//        $stmt = $this->handler->prepare("SELECT `lastUpdate` FROM `User` WHERE `id`= :id");
//        $id=$user->getId();  //For strict PHP
//        $stmt->bindParam(':id',$id);
//        if ($stmt->execute()) {
//            $row=$stmt->fetch();
//            if($row){
//                $response=new DateTime($row["lastUpdate"]);
//            }
//        }else{
//            $error=$stmt->errorInfo();
//            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
//        }
//        return $response;
//    }
    /**
     * Actualiza la última fecha de actualización con la fecha de ahora
     * @param User $user Objeto Usuario
     */
//    public function updateLastUpdate($user){
//        $stmt = $this->handler->prepare("UPDATE User SET 
//            `lastUpdate`=:lastUpdate  
//            WHERE id=:id");
//        $id=$user->getId();  //For strict PHP
//        $date=date('Y-m-d H:i:s');
//        $stmt->bindParam(':id',$id);
//        $stmt->bindParam(':lastUpdate',$date);
//        if(!$stmt->execute()){
//            $error=$stmt->errorInfo();
//            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
//        }
//    }
    /**
     * Verifica para cada usuario si la estructura de las tablas ha cambiado por
     * medio de una función de hash. Si ha cambiado, actualiza el número de 
     * versión. Si no ha cambiado, retorna el mismo número de versión.
     * @param string $state Estructura de las tablas en JSON
     * @param User $user Objeto Usuario
     * @return int El número de la versión de la base de datos
     */
//    public function version($state,$user){
//        //Verifica si el usuario existe, sino, lo agrega e inserta el estado inicial
//        if($this->userExists($user)){
//            //Retorna el último estado
//            $oldState=$this->getHashTable($user);
//            $newState=md5($state);
//            if($newState!=$oldState){
//                $this->updateHashTable($newState,$user);
//            }
//        }
//        //Retorna el mismo número de versión si no hubo cambios, más uno si hubo cambios
//        return  $this->getVersion($user);
//    }
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Actualiza el estado para el usuario, aumenta en uno el número de la versión.
     * @param string $hashTable Hash de las tablas para el usuario
     * @param User $user Objeto Usuario
     * @return boolean True si pudo actualizar los datos. False en otro caso
     */
//    private function updateHashTable($hashTable,$user){
//        $updated=false;
//        $stmt = $this->handler->prepare("UPDATE User SET 
//            `versionDB`=:versionDB,
//            `hashTable`=:hashTable 
//            WHERE id=:id");
//        $version=$this->getVersion($user)+1;
//        $id=$user->getId();  //For strict PHP
//        $stmt->bindParam(':id',$id);
//        $stmt->bindParam(':versionDB',$version);
//        $stmt->bindParam(':hashTable',$hashTable);
//        if($stmt->execute()){
//            $updated=true;
//        }else{
//            $error=$stmt->errorInfo();
//            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
//        }
//        return $updated;
//    }
    /**
     * Retorna el número de la versión para el usuario
     * @param User $user Objeto Usuario
     * @return int Versión la base de datos para el usuario
     */
//    private function getHashTable($user){
//        $response=false;
//        $stmt = $this->handler->prepare("SELECT `hashTable` FROM `User` WHERE `id`= :id");
//        $id=$user->getId();  //For strict PHP
//        $stmt->bindParam(':id',$id);
//        if ($stmt->execute()) {
//            $row=$stmt->fetch();
//            $response=$row["hashTable"];
//        }else{
//            $error=$stmt->errorInfo();
//            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
//        }
//        return $response;
//    }
    /**
     * Retorna el estado del usuario
     * @param User $user Objeto Usuario
     * @return string {'idle'|'sync'}
     */
//    public function getStatus($user){
//        $response=false;
//        $stmt = $this->handler->prepare("SELECT `status` FROM `User` WHERE `id`= :id");
//        $id=$user->getId();  //For strict PHP
//        $stmt->bindParam(':id',$id);
//        if ($stmt->execute()) {
//            $row=$stmt->fetch();
//            $response=$row["status"];
//        }else{
//            $error=$stmt->errorInfo();
//            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
//        }
//        return $response;
//    }
    /**
     * Marca el estado del usuario como "en sincronización" 'sync' o desocupado 'idle'
     * @param User $user Objeto Usuario
     * @param string $status Estado del usuario
     */
//    public function setStatus($user,$status='idle'){
//        $stmt = $this->handler->prepare("UPDATE User SET 
//            `status`=:status  
//            WHERE id=:id");
//        $id=$user->getId();  //For strict PHP
//        //Fuerza a admitir valores válidos: {'idle'|'sync'}
//        if($status!=='sync'){
//            $status='idle';
//        }
//        $stmt->bindParam(':id',$id);
//        $stmt->bindParam(':status',$status);
//        if(!$stmt->execute()){
//            $error=$stmt->errorInfo();
//            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
//        }
//    }
}