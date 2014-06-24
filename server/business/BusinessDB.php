<?php
/** BusinessDB File
* @package html5sync @subpackage core */
include_once 'Connection.php';
include_once 'Database.php';
include_once 'Column.php';
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
            $this->loadTables($this->parameter("database","name"));
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
     * @param string $schema Nombre de la base de datos
     */
    private function loadTables($schema){
        unset($this->tables);
        $this->tables=array();
        $tablesData=$this->parameter("tables");
        //Se crea el objeto para manejar tablas con PDO
        $dao=new DaoTable($this->db);
        //Se lee cada tabla
        foreach ($tablesData as $tableData) {
            if($this->checkIfAccessibleTable($tableData)){
                $table=$dao->loadTable($schema,$tableData["name"],$tableData["mode"]);
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
    //**************************************************************************
    //>>>>>>>>>>>>>>>>>>>>>   TRANSACTIONS METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<
    //**************************************************************************
    /**
     * Almacena un nuevo registro en la tabla especificada
     * @param Table $table Objeto de tipo Table donde se almacenará el registro
     * @param array $row Matriz asociativa con los valores a sanear
     * @return bool False si no hay error, String con el error si existe
     */
    public function addRegister($table,$row){
        $dao=new DaoTable($this->db);
        $error=false;
        //Verifica todas las columnas del registro contra las de la tabla
        $register=false;
        foreach ($table->getColumns() as $column){
            //Si el id es autoincrement, lo elimina para que se genere automáticamente en la BusinessDB
            if(!$column->isAI()){
                if($column->getType()==="int"){
                    $register[$column->getName()]=filter_var($row[$column->getName()],FILTER_SANITIZE_NUMBER_INT);
                }elseif($column->getType()==="double"){
                    $register[$column->getName()]=filter_var($row[$column->getName()],FILTER_SANITIZE_NUMBER_FLOAT);
                }else{
                    $register[$column->getName()]=filter_var($row[$column->getName()],FILTER_SANITIZE_STRING);
                }
                //Se verifica que los que no deben ser nulos, no sean nulos, sino, retorna error
                if($column->isNN()){
                    if($column->getType()==="varchar"&&trim($register[$column->getName()])===""){
                        $error="Column ".$column->getName()." cannot be empty";
                    }elseif(!$register[$column->getName()]){
                        $error="Column ".$column->getName()." must contain a number";
                    }
                }
            }
        }
        //Pasa la Tabla con el registro a almacenar a DaoTable
        if(!$error){
            $dao->addRegisterToDB($table,$register);
        }
        return $error;
    }
}