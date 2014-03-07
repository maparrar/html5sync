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
            $sqlite=new PDO('sqlite:'.$path);
            $query="
                CREATE TABLE IF NOT EXISTS `User` (
                    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                    `versionDB` INTEGER
                );
                CREATE TABLE IF NOT EXISTS `Table` (
                    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                    `name` TEXT
                );
                CREATE TABLE IF NOT EXISTS `TableState` (
                    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                    `date` TEXT,
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
            $sqlite->exec($query);
        } catch (Exception $ex) {
            error_log($ex->getMessage());
        }
    }
}