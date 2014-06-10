<?php
/** BusinessDB File
* @package html5sync @subpackage core */
include_once 'Connection.php';
include_once 'Database.php';
include_once 'Field.php';
include_once 'Table.php';
include_once 'DaoTable.php';
include_once 'Transaction.php';
/**
* BusinessDB Class
* Clase para el manejo de la base de datos del negocio.
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package html5sync
* @subpackage core
*/
class BusinessDB{
    /** 
     * Database object 
     * 
     * @var Database
     */
    protected $db;
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
     * Variable de configuración
     * 
     * @var array
     */
    protected $config;
    /**
    * Constructor
    * @param User $user Usuario actual
    * @param Configuration $config Objeto de configuración del sistema
    */
    function __construct($user,$config){
        $this->user=$user;
        $this->tables=false;
        $this->config=$config;
        //Se conecta a la base de datos
        $this->connect();
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter tables
    * @param Table[] $value Lista de tablas del usuario
    * @return void
    */
    public function setTables($value) {
        $this->tables=$value;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Retorna la lista de tablas (sin datos), solo las carga la primera vez que se solicitan
    * @return Table[]
    */
    public function getTables() {
        if(!$this->tables){
            $this->loadTables();
        }
        return $this->tables;
    }
    /**
    * Retorna el valor de un parámetro del archivo de configuración a través del
    * árbol de índices
    * @return mixed
    */
    public function parameter($parameter1,$parameter2=false,$parameter3=false) {
        return $this->config->getParameter($parameter1,$parameter2,$parameter3);
    }
    //**************************************************************************
    //>>>>>>>>>>>>>>>>>>>>>>>   PRIVATED METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    //**************************************************************************
    /**
     * Crea la conexión con la base de datos
     */
    private function connect(){
        //Se crea una instancia de la base de datos con la conexión (read+write)
        $this->db=new Database(
                $this->parameter("database","name"),
                $this->parameter("database","driver"),
                $this->parameter("database","host"), 
                new Connection(
                    "all",
                    $this->parameter("database","login"),
                    $this->parameter("database","password")
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
        $tablesData=$this->parameter("tables");
        //Se crea el objeto para manejar tablas con PDO
        $dao=new DaoTable($this->db);
        //Se lee cada tabla
        foreach ($tablesData as $tableData) {
            if($this->checkIfAccessibleTable($tableData)){
                $table=$dao->loadTable($tableData["name"],$tableData["mode"]);
                $table->setTotalOfRows($dao->countRows($table));
                $table->setInitialRow(0);
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
     * Retrona una tabla a partir de su nombre
     * @param string $name Nombre de la tabla
     * @return Table Tabla
     */
    private function getTableByName($name) {
        $output=false;
        foreach ($this->tables as $table) {
            if($table->getName()===$name){
                $output=$table;
            }
        }
        return $output;
    }
    //**************************************************************************
    //>>>>>>>>>>>>>>>>>>>>>>>>   PUBLIC METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    //**************************************************************************
    /**
     * Prepara la base de datos para registrar los cambios en las tablas seleccionadas
     * Crea las tablas necesarias y los triggers.
     */
    public function prepareDatabase(){
        //Se crea el objeto para manejar tablas con PDO
        $dao=new DaoTable($this->db);
        if($this->parameter("main","updateMode")==="transactionsTable"){
            //Crear la tabla de transacciones
            $dao->createTransactionsTable();
            //Crear los procedimientos y los triggers para las tablas
            foreach ($this->tables as $table) {
                $dao->createTransactionsProcedures($table);
                $dao->createTransactionsTriggers($table);
            }
        }
    }
    /**
     * Retorna todos los datos de una tablas por páginas
     * @param string $tableName Nombre de la tabla
     * @param int $initialRow [optional] Indica la fila desde la que deben cargar los registros
     * @return Table Array con las tablas para el usuario
     */
    public function getTableData($tableName,$initialRow=0){
        //Se crea el objeto para manejar tablas con PDO
        $dao=new DaoTable($this->db);
        $table=$this->getTableByName($tableName);
        $data=$dao->getRows($table,$initialRow,$this->parameter("main","rowsPerPage"));
        if($data){
            $table->setData($data);
            $table->setTotalOfRows($dao->countRows($table));
            $table->setInitialRow($initialRow);
        }
        return $table;
    }
    /**
     * Retorna la lista de tablas en formato JSON. Si no se pasa el parámetro, usa
     * las cargadas en el objeto
     * @return string Las tablas del usuario en formato JSON
     */
    public function getTablesInJson($tables=false){
        $listTables=array();
        if($tables){
            if(!is_array($tables)){
                $listTables=array($tables);
            }else{
                $listTables=$tables;
            }
        }else{
            $listTables=$this->getTables();
        }
        $json='[';
        foreach ($listTables as $table) {
            $json.=$table->jsonEncode().",";
        }
        //Remove the last comma
        if(count($listTables)){
            $json=substr($json,0,-1);
        }
        $json.="]";
        return $json;
    }
    /**
     * Revisa en la lista de tablas del usuario si la pasada es permitida
     * @param string $tableName Nombre de la tabla que se queire verificar
     * @return boolean True si la tabla está permitida, false en otro caso
     */
    public function isTableAllowed($tableName){
        $allowed=false;
        $listTables=$this->getTables();
        foreach ($listTables as $table){
            if($table->getName()===$tableName){
                $allowed=true;
            }
        }
        return $allowed;
    }
    /**
     * Retorna las últimas transacciones para actualizar las base de datos del navegador
     * @param DateTime $lastUpdate Última fecha de actualización
     * @return Transaction[] Lista de transacciones luego de la fecha pasada como parámetro
     */
    public function getLastTransactions($lastUpdate){
        $dao=new DaoTable($this->db);
        $transactions=$dao->getLastTransactions($lastUpdate);
        foreach ($transactions as $transaction) {
            if($transaction->getType()!=="DELETE"){
                $table=$this->getTableByName($transaction->getTableName());
                $row=$dao->getRowOfTable($table,$transaction->getKey());
                $transaction->setRow($row);
            }
        }
        return $transactions;
    }
    /**
     * Prueba la conexión con la base de datos
     * @return bool Retorna true si la conexión fue exitosa, false en otro caso
     */
    public function testConnection(){
        $connection=$this->db->connect();
        return $connection;
    }


























































    /**
     * Verifica si la estructura de las tablas cambió.
     * @return boolean True si la estructura de las tablas cambió, False en otro caso
     */
//    public function checkIfStructureChanged(){
//        $this->loadTables();
//        $jsonTables=$this->getTablesInJson();
//        return $this->stateDB->checkIfStructureChanged($jsonTables,$this->user);
//    }
    /**
     * Verifica si los datos de las tablas de usuario cambiaron o se eliminaron
     * registros
     * @return mixed False si los datos no cambiaron. Array con las tablas que tuvieron cambios
     */
//    public function getTablesWithChanges(){
//        $tables=array();
//        $lastUpdate=$this->stateDB->getLastUpdate($this->user);
//        //Se crea el objeto para manejar tablas con PDO
//        $dao=new DaoTable($this->db);
//        foreach ($this->tables as $table) {
//            //Verifica si hubo actualización o inserción en la tabla y la agrega a la lista
//            if($dao->checkIfRowsChanged($table,$lastUpdate)){
//                $table->setTotalOfRows($dao->getTotalOfRows($table,$lastUpdate));
//                $table->setInitialRow(0);
//                array_push($tables, $table);
//            }
//        }
//        if(count($tables)==0){
//            $tables=false;
//        }
//        return $tables;
//    }
    /**
     * Verifica si los datos de las tablas de usuario cambiaron o se eliminaros
     * registros
     * @return mixed False si los datos no cambiaron. Array con las tablas que tuvieron cambios
     */
//    public function getTablesWithDeletions(){
//        $tables=array();
//        $lastUpdate=$this->stateDB->getLastUpdate($this->user);
//        //Se crea el objeto para manejar tablas con PDO
//        $dao=new DaoTable($this->db);
//        foreach ($this->tables as $table) {
//            //Verifica si hubo actualización o inserción en la tabla y la agrega a la lista
//            if($dao->checkIfRowsChanged($table,$lastUpdate)){
//                $table->setTotalOfRows($dao->getTotalOfRows($table,$lastUpdate));
//                $table->setInitialRow(0);
//                array_push($tables, $table);
//            }
//            //Verifica si hubo deleciones, sino está en la lista, la agrega
//            if($dao->checkIfRowsDeleted($table,$lastUpdate)){
//                
//                
//                $table->setTotalOfRows($dao->getTotalOfRows($table,$lastUpdate));
//                $table->setInitialRow(0);
//                array_push($tables, $table);
//            }
//        }
//        if(count($tables)==0){
//            $tables=false;
//        }
//        return $tables;
//    }
    /**
     * Retorna todos los datos de las tablas
     * @return mixed Array con las tablas para el usuario
     */
//    public function getAllTables(){
//        $changed=array();
//        //Se crea el objeto para manejar tablas con PDO
//        $dao=new DaoTable($this->db);
//        foreach ($this->tables as $table){
////            $data=$dao->getAllRows($table,0,$this->parameters["rowsPerPage"]);
////            if($data){
////                $table->setData($data);
//                array_push($changed, $table);
//                $table->setTotalOfRows($dao->getTotalOfRows($table));
////            }
//        }
//        //Actualiza la fecha de última actualización para no recargár más los datos cargados
//        $this->stateDB->updateLastUpdate($this->user);
//        return $changed;
//    }
    
    /**
     * Retorna los datos que han cambiado de la tabla 
     * @param string $tableName Nombre de la tabla
     * @param int $initialRow [optional] Indica la fila desde la que debe cargar los registros
     * @return mixed False si los datos no cambiaron. Array con las tablas que tuvieron cambios
     */
//    public function getUpdatedTable($tableName,$initialRow=0){
//        //Se crea el objeto para manejar tablas con PDO
//        $dao=new DaoTable($this->db);
//        $table=$this->getTableByName($tableName);
//        $lastUpdate=$this->stateDB->getLastUpdate($this->user);
//        $data=$dao->getUpdatedRows($table,$lastUpdate,$initialRow,$this->parameters["rowsPerPage"]);
//        if($data){
//            $table->setData($data);
//            $table->setTotalOfRows($dao->getTotalOfRows($table,$lastUpdate));
//            $table->setInitialRow($initialRow);
//        }
//        return $table;
//    }
    /**
     * Actualiza la última fecha de acceso a los datos en la base de datos de estado SQLite
     */
//    public function updateLastUpdate(){
//        //Actualiza la fecha de última actualización para no recargár más los datos cargados
//        $this->stateDB->updateLastUpdate($this->user);
//    }
    
    
    
    
    
    /**
     * Retorna la versión de la base de datos almacenada para el usuario
     * @return int Número de versión
     */
//    public function getVersion(){
//        $state=$this->getTablesInJson();
//        return $this->stateDB->version($state,$this->user);
//    }
}