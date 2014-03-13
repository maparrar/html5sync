<?php
/** Database File
* @package core */
/**
* Database Class
*
* @author https://github.com/maparrar/maqinato
* @author maparrar <maparrar@gmail.com>
* @package core
*/
class Database{
    /** 
     * Nombre de la base de datos 
     * 
     * @var string
     */
    protected $name;
    /** 
     * Tipo de conexión: mysql, oracle, postgres 
     * 
     * @var string
     */
    protected $driver;
    /** 
     * Host donde está alojada la base de datos 
     * 
     * @var string
     */
    protected $host;
    /** 
     * Array de conexiones a la base de datos: read, write, delete, all 
     * 
     * @var Connection[]
     */
    protected $connections;
    /**
    * Constructor
    * @param string $name Nombre de la base de datos        
    * @param string $driver Tipo de conexión: mysql, oracle, postgresql        
    * @param string $host Host donde está alojada la base de datos        
    * @param Connection[] $connections Array de conexiones a la base de datos: read, write, delete, all        
    */
    function __construct($name="",$driver="",$host="",$connections=array()){        
        $this->name=$name;
        $this->driver=$driver;
        $this->host=$host;
        if(is_array($connections)){
            $this->connections=$connections;
        }else{
            $this->connections=array($connections);
        }
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter name
    * @param string $value Nombre de la base de datos
    * @return void
    */
    public function setName($value) {
        $this->name=$value;
    }
    /**
    * Setter driver
    * @param string $value Tipo de conexión: mysql, oracle, postgresql
    * @return void
    */
    public function setDriver($value) {
        $this->driver=$value;
    }
    /**
    * Setter host
    * @param string $value Host donde está alojada la base de datos
    * @return void
    */
    public function setHost($value) {
        $this->host=$value;
    }
    /**
    * Setter connections
    * @param Connection[] $value Array de conexiones a la base de datos: read, write, delete, all
    * @return void
    */
    public function setConnections($value) {
        $this->connections=$value;
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
    * Getter: driver
    * @return string
    */
    public function getDriver() {
        return $this->driver;
    }
    /**
    * Getter: host
    * @return string
    */
    public function getHost() {
        return $this->host;
    }
    /**
    * Getter: connections
    * @return Connection[]
    */
    public function getConnections() {
        return $this->connections;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
     * Agrega una conexión a la base de datos
     * @param string $name Nombre de la conexión: read, write, delete, all
     * @param string $login Login de acceso a la base de datos para la conexión
     * @param string $password Password de acceso a la base de datos para la conexión
     */
    public function addConnection($name,$login,$password){
        $this->connections[]=new Connection($name,$login,$password);
    }
    /**
    * Retorna una conexión dado su nombre
    * @param string $name Nombre de la conexión que se quiere retornar: read, write, delete, all
    * @return Connection Conexión a partir del nombre
    */
    private function connection($name){
        $response=false;
        foreach ($this->connections as $connection) {
            if($connection->getName()===$name){
                $response=$connection;
                break;
            }
        }
        return $response;
    }
    /**
     * Conecta con una base de datos, si hay algún error ejecuta die() para terminar
     * cualquier proceso.
     * @param string $connectionName Nombre la conexión a usar: read, write, delete, all
     * @return PDO Object databse handler
     */
    function connect($connectionName="all"){
        $handler=false;
        $conection=$this->connection($connectionName);
        try {
            $handler = new PDO(
                $this->driver.
                ':host='.$this->host.
                ';dbname='.$this->name,
                $conection->getLogin(),
                $conection->getPassword()
            );
        } catch (PDOException $e) {
            error_log("Error!: " . $e->getMessage());
            die();
        }
        return $handler;
    }
}