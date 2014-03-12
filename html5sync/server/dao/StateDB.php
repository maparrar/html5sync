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
                    `version` INTEGER NOT NULL,
                    `state` TEXT,
                    `role` TEXT
                );
            ";
            // Crea las tablas
            $this->handler->exec($query);
        } catch (Exception $ex) {
            error_log($ex->getMessage());
        }
    }
    /**
     * Verifica para cada usuario si la estructura de las tablas ha cambiado por
     * medio de una función de hash. Si ha cambiado, actualiza el número de 
     * versión. Si no ha cambiado, retorna el mismo número de versión.
     * @param int $userId Id del usuario
     * @param string $state Estructura de las tablas en JSON
     * @return int El número de la versión de la base de datos
     */
    function version($userId,$state){
        //Verifica si el usuario existe, sino, lo agrega e inserta el estado inicial
        if(!$this->userExists($userId)){
            $this->userCreate($userId,md5($state));
        }else{
            //Retorna el último estado
            $oldState=$this->getState($userId);
            $newState=md5($state);
            if($newState!=$oldState){
                $this->updateState($userId,$newState);
            }
        }
        //Retorna el mismo número de verión si no hubo cambios, más uno si hubo cambios
        return  $this->getVersion($userId);
    }
    /**************************************************************************/
    /******************************   U S E R S  ******************************/
    /**************************************************************************/
    /**
     * Inserta un usuario en la tabla de User
     * @param int $userId Identificador del usuario
     * @param string $hashState Hash de las tablas para el usuario
     * @return boolean True si se pudo insertar el usuario, false en otro caso
     */
    private function userCreate($userId,$hashState){
        $created=false;
        if(!$this->userExists($userId)){
            $stmt = $this->handler->prepare("
                INSERT INTO User 
                    (`id`,`version`,`state`) 
                VALUES 
                    (:id,:version,:state)
            ");
            $version=1;
            $stmt->bindParam(':id',$userId);
            $stmt->bindParam(':version',$version);
            $stmt->bindParam(':state',$hashState);
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
    /**
     * Retorna el número de la versión para el usuario
     * @param int $userId Identificador del usuario
     * @return int Versión la base de datos para el usuario
     */
    private function getVersion($userId){
        $response=false;
        $stmt = $this->handler->prepare("SELECT `version` FROM `User` WHERE `id`= :id");
        $stmt->bindParam(':id',$userId);
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            $response=intval($row["version"]);
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $response;
    }
    /**
     * Actualiza el estado para el usuario, aumenta en uno el número de la versión.
     * @param int $userId Identificador del usuario
     * @param string $hashState Hash de las tablas para el usuario
     * @return boolean True si pudo actualizar los datos. False en otro caso
     */
    private function updateState($userId,$hashState){
        $updated=false;
        $stmt = $this->handler->prepare("UPDATE User SET 
            `version`=:version,
            `state`=:state 
            WHERE id=:id");
        $version=$this->getVersion($userId)+1;
        $stmt->bindParam(':id',$userId);
        $stmt->bindParam(':version',$version);
        $stmt->bindParam(':state',$hashState);
        if($stmt->execute()){
            $updated=true;
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
        }
        return $updated;
    }
    /**
     * Retorna el número de la versión para el usuario
     * @param int $userId Identificador del usuario
     * @return int Versión la base de datos para el usuario
     */
    private function getState($userId){
        $response=false;
        $stmt = $this->handler->prepare("SELECT `state` FROM `User` WHERE `id`= :id");
        $stmt->bindParam(':id',$userId);
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            $response=$row["state"];
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
        }
        return $response;
    }
}