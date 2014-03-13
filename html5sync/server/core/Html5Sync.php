<?php
/** Html5Sync File
* @package html5sync @subpackage core */
include_once 'Connection.php';
include_once 'Database.php';
include_once 'Field.php';
include_once 'Table.php';
include_once '../dao/DaoTable.php';
include_once '../dao/StateDB.php';
/**
* Html5Sync Class
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package html5sync
* @subpackage core
*/
class Html5Sync{
    /** 
     * Database object 
     * 
     * @var Database
     */
    protected $db;
    /** 
     * StateDB object 
     * Base de datos SQLite
     * @var Database
     */
    protected $stateDB;
    /** 
     * Variable de configuración
     * 
     * @var array
     */
    protected $config;
    /** 
     * Usuario, clase manejada en html5sync 
     * 
     * @var User
     */
    protected $user;
    /** 
     * Lista de tablas del usuario 
     * 
     * @var Table[]
     */
    protected $tables;
    /** 
     * Parámetros de html5sync 
     * 
     * @var array
     */
    protected $parameters;
    /**
    * Constructor
    * @param Database $db Database object        
    * @param User $user Usuario, clase manejada en html5sync        
    * @param Table[] $tables Lista de tablas del usuario        
    * @param array $parameters Parámetros de html5sync        
    */
    function __construct($user){
        $this->tables=array();
        $this->user=$user;
        
        //Se establece timezone y carga la configuración
        date_default_timezone_set("America/Bogota");
        $this->loadConfiguration();
                
        //Se conecta a la base de datos
        $this->connect();
        //Carga la estructura de las tablas para el usuario
        $this->loadTables();
        //Se verifica si hubo cambios en alguna de las tablas para el usuario desde la 
        //última conexión usando una función de Hash
        $this->stateDB=new StateDB();
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter db
    * @param Database $value Database object
    * @return void
    */
    public function setDb($value) {
        $this->db=$value;
    }
    /**
    * Setter user
    * @param User $value Usuario, clase manejada en html5sync
    * @return void
    */
    public function setUser($value) {
        $this->user=$value;
    }
    /**
    * Setter tables
    * @param Table[] $value Lista de tablas del usuario
    * @return void
    */
    public function setTables($value) {
        $this->tables=$value;
    }
    /**
    * Setter parameters
    * @param array $value Parámetros de html5sync
    * @return void
    */
    public function setParameters($value) {
        $this->parameters=$value;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Getter: db
    * @return Database
    */
    public function getDb() {
        return $this->db;
    }
    /**
    * Getter: user
    * @return User
    */
    public function getUser() {
        return $this->user;
    }
    /**
    * Getter: tables
    * @return Table[]
    */
    public function getTables() {
        return $this->tables;
    }
    /**
    * Getter: parameters
    * @return array
    */
    public function getParameters() {
        return $this->parameters;
    }    
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
     * Verifica si la estructura de las tablas cambió.
     * @return boolean True si la estructura de las tablas cambió, False en otro caso
     */
    public function checkIfStructureChanged(){
        $this->loadTables();
        $jsonTables=$this->getTablesInJson();
        return $this->stateDB->checkIfStructureChanged($jsonTables,$this->user);
    }
    /**
     * Verifica si los datos de las tablas de usuario cambiaron.
     * @return mixed False si los datos no cambiaron. Array con las tablas que tuvieron cambios
     */
    public function checkIfDataChanged(){
        $changed=array();
        $lastUpdate=$this->stateDB->getLastUpdate($this->user);
        //Se crea el objeto para manejar tablas con PDO
        $dao=new DaoTable($this->db);
        foreach ($this->tables as $table) {
            if($dao->checkDataChanged($table,$lastUpdate)){
                array_push($changed, $table->getName());
            }
        }
        if(count($changed)==0){
            $changed=false;
        }
        return $changed;
    }
    /**
     * Carga la configuración del archivo server/config.php
     */
    private function loadConfiguration(){
        //Se leen las variables de configuración
        $this->config=require_once '../config.php';
        
        
        $this->parameters=$this->config["parameters"];
    }
    /**
     * Crea la conexión con la base de datos
     */
    private function connect(){
        //Se crea una instancia de la base de datos con la conexión (read+write)
        $this->db=new Database(
                $this->config["database"]["name"],
                $this->config["database"]["driver"],
                $this->config["database"]["host"], 
                new Connection(
                    "all",
                    $this->config["database"]["login"],
                    $this->config["database"]["password"]
                )
            );
    }
    /**
     * Carga la lista de tablas (sin datos) para el usuario y configura el tipo 
     * de actualización definido.
     */
    private function loadTables(){
        unset($this->tables);
        $this->tables=array();
        $tablesData=$this->config["tables"];
        //Se crea el objeto para manejar tablas con PDO
        $dao=new DaoTable($this->db);
        //Se lee cada tabla
        foreach ($tablesData as $tableData) {
            if($this->checkIfAccessibleTable($tableData)){
                $table=$dao->loadTable($this->db->getDriver(),$tableData["name"],$tableData["mode"]);
                //Se usa el tipo de actualización seleccionada
                if($this->parameters["updateMode"]==="updatedColumn"){
                    //Si la columna de actualización no existe, se crea
                    $dao->setUpdatedColumnMode($this->db->getDriver(),$table);
                }
                array_push($this->tables,$table);
            }
        }
    }
    /**
     * Verifica si una tabla está permitida para el usuario por el identificador
     * de usuario o por el rol.
     * @param array $tableData Datos de la tabla cargados desde config.php
     * @return boolean True si es accesible para el usuario, false en otro caso
     */
    private function checkIfAccessibleTable($tableData){
        $accessible=false;
        if(array_key_exists("users",$tableData)){
            $users=$tableData["users"];
            foreach ($users as $user) {
                if($user==$this->user->getId()){
                    $accessible=true;
                }
            }
        }
        if(array_key_exists("roles",$tableData)){
            $roles=$tableData["roles"];
            foreach ($roles as $role) {
                if($role==$this->user->getRole()){
                    $accessible=true;
                }
            }
        }
        return $accessible;
    }
    /**
     * Retorna la lista de tablas en formato JSON
     * @return string Las tablas del usuario en formato JSON
     */
    private function getTablesInJson(){
        $json="";
        foreach ($this->tables as $table) {
            //Se guarda la estructura de cada tabla serializada para comparar el estado con el anterior
            $json.=$table->jsonEncode();
        }
        return $json;
    }
}