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
                    `versionDB` INTEGER NOT NULL,
                    `hashTable` TEXT,
                    `lastUpdate` TEXT,
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
     * medio de una función de hash. 
     * @param string $state Estructura de las tablas en JSON
     * @param User $user Objeto Usuario
     * @return int El número de la versión de la base de datos
     */
    public function checkIfStructureChanged($state,$user){
        $changed=false;
        //Verifica si el usuario existe, sino, lo agrega e inserta el estado inicial
        if(!$this->userExists($user)){
            $this->userCreate(md5("emptystructure"),$user);
            $changed=true;
        }else{
            //Retorna el último estado
            $oldState=$this->getHashTable($user);
            $newState=md5($state);
            if($newState!=$oldState){
                $changed=true;
            }
        }
        return  $changed;
    }
    /**
     * Retorna la fecha de la última actualización almacenada en la base de datos de estado
     * @param User $user Objeto Usuario
     * @return DateTime Objeto con la fecha de la última actualización
     */
    public function getLastUpdate($user){
        $response=false;
        $stmt = $this->handler->prepare("SELECT `lastUpdate` FROM `User` WHERE `id`= :id");
        $stmt->bindParam(':id',$user->getId());
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            $response=new DateTime($row["lastUpdate"]);
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."Mysql: ".$error[2]);
        }
        return $response;
    }
    /**
     * Verifica para cada usuario si la estructura de las tablas ha cambiado por
     * medio de una función de hash. Si ha cambiado, actualiza el número de 
     * versión. Si no ha cambiado, retorna el mismo número de versión.
     * @param string $state Estructura de las tablas en JSON
     * @param User $user Objeto Usuario
     * @return int El número de la versión de la base de datos
     */
    function version($state,$user){
        //Verifica si el usuario existe, sino, lo agrega e inserta el estado inicial
        if($this->userExists($user)){
            //Retorna el último estado
            $oldState=$this->getHashTable($user);
            $newState=md5($state);
            if($newState!=$oldState){
                $this->updateHashTable($newState,$user);
            }
        }
        //Retorna el mismo número de versión si no hubo cambios, más uno si hubo cambios
        return  $this->getVersion($user);
    }
    /**************************************************************************/
    /******************************   U S E R S  ******************************/
    /**************************************************************************/
    /**
     * Inserta un usuario en la tabla de User
     * @param string $hashTable Hash de la estructura de la tabla para el usuario
     * @param User $user Objeto Usuario
     * @return boolean True si se pudo insertar el usuario, false en otro caso
     */
    private function userCreate($hashTable,$user){
        $created=false;
        if(!$this->userExists($user)){
            $stmt = $this->handler->prepare("
                INSERT INTO User (`id`,`versionDB`,`hashTable`,`lastUpdate`,`role`) 
                VALUES           (:id,:versionDB,:hashTable,:lastUpdate,:role)
            ");
            $version=1;
            $date=date('Y-m-d H:i:s');
            $stmt->bindParam(':id',$user->getId());
            $stmt->bindParam(':versionDB',$version);
            $stmt->bindParam(':hashTable',$hashTable);
            $stmt->bindParam(':lastUpdate',$date);
            $stmt->bindParam(':role',$user->getRole());
            if(!$stmt->execute()){
                $error=$stmt->errorInfo();
                error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
            }
        }
        return $created;
    }
    /**
     * Verifica si un usuario existe en la base de datos de estado
     * @param User $user Objeto Usuario
     * @return boolean True si el usuario existe, false en otro caso
     */
    private function userExists($user){
        $exist=false;
        $stmt = $this->handler->prepare("SELECT id FROM User WHERE id=:id");
        $stmt->bindParam(':id',$user->getId());
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
     * Retorna el número de la versión para el usuario
     * @param User $user Objeto Usuario
     * @return int Versión la base de datos para el usuario
     */
    private function getVersion($user){
        $response=false;
        $stmt = $this->handler->prepare("SELECT `versionDB` FROM `User` WHERE `id`= :id");
        $stmt->bindParam(':id',$user->getId());
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
     * Actualiza el estado para el usuario, aumenta en uno el número de la versión.
     * @param string $hashTable Hash de las tablas para el usuario
     * @param User $user Objeto Usuario
     * @return boolean True si pudo actualizar los datos. False en otro caso
     */
    private function updateHashTable($hashTable,$user){
        $updated=false;
        $stmt = $this->handler->prepare("UPDATE User SET 
            `versionDB`=:versionDB,
            `hashTable`=:hashTable 
            WHERE id=:id");
        $version=$this->getVersion($user)+1;
        $stmt->bindParam(':id',$user->getId());
        $stmt->bindParam(':versionDB',$version);
        $stmt->bindParam(':hashTable',$hashTable);
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
     * @param User $user Objeto Usuario
     * @return int Versión la base de datos para el usuario
     */
    private function getHashTable($user){
        $response=false;
        $stmt = $this->handler->prepare("SELECT `hashTable` FROM `User` WHERE `id`= :id");
        $stmt->bindParam(':id',$user->getId());
        if ($stmt->execute()) {
            $row=$stmt->fetch();
            $response=$row["hashTable"];
        }else{
            $error=$stmt->errorInfo();
            error_log("[".__FILE__.":".__LINE__."]"."SQLite: ".$error[2]);
        }
        return $response;
    }
}