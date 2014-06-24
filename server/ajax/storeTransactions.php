<?php
session_start();
include_once '../business/BusinessDB.php';
include_once '../core/Configuration.php';
include_once '../core/User.php';
include_once '../state/StateDB.php';

//Control de errores
$error=false;
//Toma los datos de usuario y rol de la aplicación
$user=new User(intval($_SESSION['html5sync_userId']),$_SESSION['html5sync_role']);
if($user->getId()<=0){
    $error="Cannot read the user id from session";
}else{
    //Carga la configuración del archivo server/config.php
    $config=new Configuration();
    //Establece el timezone definido en el archivo de configuración
    date_default_timezone_set($config->getParameter("main","timezone"));
    //Crea el objeto para el manejo de la base de datos del negocio
    $businessDB=new BusinessDB($user,$config);
    //Crea el objeto para manejo de la base de datos estática y crea el usuario si no existe
    $stateDB=new StateDB($user);
    
    //Carga los datos pasados por el cliente
//    $initialRow=filter_input(INPUT_POST,'initialRow',FILTER_SANITIZE_NUMBER_INT);
//    $transactions=filter_input(INPUT_POST,'transactions',FILTER_SANITIZE_STRING);
    $transactions=$_POST["transactions"];
    
    
    foreach ($transactions as $transaction) {
        
        
        //Filtra los datos de cada transacción
        $tableName=$transaction["table"];
        
        
        
        //Verifica si la tabla puede ser cargada por el usuario
        if(!$businessDB->isTableAllowed($transaction["table"])){
            $error="Table ".$tableName." is not allowed for the user";
        }else{
            //Retorna la estructura de la tabla para filtrar los datos de la fila
            $table=$businessDB->getTableData($tableName);
            foreach ($table->getColumns() as $column){
                if($column->getType()==="int"){
                    $transaction["row"][$column->getName()]=filter_var($transaction["row"][$column->getName()],FILTER_SANITIZE_NUMBER_INT);
                }elseif($column->getType()==="double"){
                    $transaction["row"][$column->getName()]=filter_var($transaction["row"][$column->getName()],FILTER_SANITIZE_NUMBER_FLOAT);
                }else{
                    $transaction["row"][$column->getName()]=filter_var($transaction["row"][$column->getName()],FILTER_SANITIZE_STRING);
                }
                
                print_r($column->getName().": ".$transaction["row"][$column->getName()]);
                print_r("\n");
            }
            
            
//            //Si es la primera solicitud y está ocupada, retorna un error
//            if($initialRow===0&&$stateDB->getStatus($table)==="sync"){
//                $error="Table ".$tableName." is busy";
//            }else{
//                //Marca la tabla como "sync" en la base de datos
//                $stateDB->setStatus($table,'sync');
//                //Convierte la información a JSON
//                $json='{"table":'.$table->jsonEncode().'}';
//                //Si es la última página, actualiza la última fecha de acceso $lastUpdate en la tabla y el usuario
//                if(($table->getTotalOfRows()-($initialRow+1))<$table->getNumberOfRows()){
//                    $stateDB->setTableLastUpdate($table);
//                    //Marca la tabla como "idle" en la base de datos
//                    $stateDB->setStatus($table,'idle');
//                }
//            }
        }
        
        
        $data = array(
            'product_id'    => 'libgd<script>',
            'component'     => '10',
            'versions'      => '2.0.33',
            'testscalar'    => array('2', '23', '10', '12'),
            'testarray'     => '2',
        );

        $args = array(
            'table'   => FILTER_SANITIZE_ENCODED,
            'component'    => array('filter'    => FILTER_VALIDATE_INT,
                                    'flags'     => FILTER_FORCE_ARRAY, 
                                    'options'   => array('min_range' => 1, 'max_range' => 10)
                                   ),
            'versions'     => FILTER_SANITIZE_ENCODED,
            'doesnotexist' => FILTER_VALIDATE_INT,
            'testscalar'   => array(
                                    'filter' => FILTER_VALIDATE_INT,
                                    'flags'  => FILTER_REQUIRE_SCALAR,
                                   ),
            'testarray'    => array(
                                    'filter' => FILTER_VALIDATE_INT,
                                    'flags'  => FILTER_FORCE_ARRAY,
                                   )

        );

        $myinputs = filter_var_array($data, $args);
    }
    
    
    
    
    
    
//    print_r($myinputs);
    
    $json='{"transactions":"'.$transactions[0]["date"].'"}';
    
    
//    if(!$initialRow){
//        $initialRow=0;
//    }
//    //Verifica si la tabla puede ser cargada por el usuario
//    if(!$businessDB->isTableAllowed($tableName)){
//        $error="Table ".$tableName." is not allowed for the user";
//    }else{
//        //Carga la tabla
//        $table=$businessDB->getTableData($tableName,$initialRow);
//        //Si es la primera solicitud y está ocupada, retorna un error
//        if($initialRow===0&&$stateDB->getStatus($table)==="sync"){
//            $error="Table ".$tableName." is busy";
//        }else{
//            //Marca la tabla como "sync" en la base de datos
//            $stateDB->setStatus($table,'sync');
//            //Convierte la información a JSON
//            $json='{"table":'.$table->jsonEncode().'}';
//            //Si es la última página, actualiza la última fecha de acceso $lastUpdate en la tabla y el usuario
//            if(($table->getTotalOfRows()-($initialRow+1))<$table->getNumberOfRows()){
//                $stateDB->setTableLastUpdate($table);
//                //Marca la tabla como "idle" en la base de datos
//                $stateDB->setStatus($table,'idle');
//            }
//        }
//    }
}
//Si se encuentran errores, se retornan al cliente
if($error){
    $json='{"error":"'.$error.'"}';
}
echo $json;