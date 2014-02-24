<?php
/** Environment File
* @package core @subpackage  */
/**
* Environment Class
*
* @author https://github.com/maparrar/maqinato
* @author maparrar <maparrar@gmail.com>
* @package core
* @subpackage 
*/
class Environment{
    /** 
     * Nombre del Environment 
     * 
     * @var string
     */
    protected $name;
    /** 
     * Lista de las urls para las que el ambiente es válido 
     * 
     * @var array
     */
    protected $urls;
    /** 
     * Base de datos del Environment 
     * 
     * @var Database
     */
    protected $database;
    /** 
     * Servidor de archivos del Environment 
     * 
     * @var FileServer
     */
    protected $fileServer;
    /**
    * Constructor
    * @param string $name Nombre del Environment        
    * @param array $urls Lista de las urls para las que el ambiente es válido        
    * @param Database $database Base de datos del Environment        
    * @param FileServer $fileServer Servidor de archivos del Environment        
    */
    function __construct($name="",$urls=array(),$database=false,$fileServer=false){        
        $this->name=$name;
        $this->urls=$urls;
        $this->database=$database;
        $this->fileServer=$fileServer;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter name
    * @param string $value Nombre del Environment
    * @return void
    */
    public function setName($value) {
        $this->name=$value;
    }
    /**
    * Setter urls
    * @param array $value Lista de las urls para las que el ambiente es válido
    * @return void
    */
    public function setUrls($value) {
        $this->urls=$value;
    }
    /**
    * Setter database
    * @param Database $value Base de datos del Environment
    * @return void
    */
    public function setDatabase($value) {
        $this->database=$value;
    }
    /**
    * Setter fileServer
    * @param FileServer $value Servidor de archivos del Environment
    * @return void
    */
    public function setFileServer($value) {
        $this->fileServer=$value;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Getter: name
    * @return string
    */
    public function getName() {
        return $this->name;
    }
    /**
    * Getter: urls
    * @return array
    */
    public function getUrls() {
        return $this->urls;
    }
    /**
    * Getter: database
    * @return Database
    */
    public function getDatabase() {
        return $this->database;
    }    
    /**
    * Getter: fileServer
    * @return FileServer
    */
    public function getFileServer() {
        return $this->fileServer;
    }    
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
     * Verifica si una URL hace parte de un Environment, si es así, retorna true
     * @param string $serverName Nombre que se quiere buscar en las urls
     * @return bool True si se encontró el $serverName en la lista de urls, false en otro caso
     */
    public function checkUrl($serverName){
        $response=false;
        foreach ($this->urls as $url) {
            if($url===$serverName){
                $response=true;
            }
        }
        return $response;
    }
    /**
     * Carga un Environment a partir de un array de configuración
     * @param array $array Array proveniente del archivo de configuración
     */
    public function readEnvironment($array){
        $this->name=$array["name"];
        $this->urls=$array["urls"];
        //Lee labase de datos
        $database=new Database(
                            $array["database"]["name"],
                            $array["database"]["driver"],
                            $array["database"]["persistent"],
                            $array["database"]["host"]
                        );
        foreach ($array["database"]["connections"] as $connection) {
            $database->addConnection($connection["name"],$connection["login"],$connection["password"]);
        }
        $this->database=$database;
        //Lee el servidor de archivos
        $this->fileServer=new FileServer(
                                    $array["fileServer"]["source"],
                                    $array["fileServer"]["isSSL"],
                                    $array["fileServer"]["domain"],
                                    $array["fileServer"]["bucket"],
                                    $array["fileServer"]["folder"],
                                    $array["fileServer"]["accessKey"],
                                    $array["fileServer"]["secretKey"]
                                );
    }
}