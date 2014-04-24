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
//Carga la configuración del archivo server/config.php
$config=new Configuration();
//Crea el objeto para el manejo de la base de datos del negocio
$businessDB=new BusinessDB($user,$config);
//Crea el objeto para manejo de la base de datos estática y crea el usuario si no existe
$stateDB=new StateDB($user);

//Establece el timezone definido en el archivo de configuración
date_default_timezone_set($config->getParameter("main","timezone"));




//Lee la última fecha para el usuario
$lastUpdate=$stateDB->getLastUpdate($user);
if(!$lastUpdate){
    $error="Could not read the last update date for the user. Synchronization failed.";
}

//Marca la base de datos estática "en actualización" para el usuario
$stateDB->setStatus($user,'sync');


//Carga la lista de tablas del usuario
$tables=$businessDB->getTables();

print_r($tables);




//Se verifican las actualizaciones, inserciones o eliminaciones para las tablas disponibles para el usuario


//Retorna el JSON de estado para cada tabla



////Detecta su hubo cambios en la estructura de alguna tabla
//if($html5sync->checkIfStructureChanged()){
//    $json.='"changesInStructure":"true",';
//}else{
//    $json.='"changesInStructure":"false",';
//}
//
////Verifica para cada tabla si hubo actualizaciones, eliminaciones o inserciones
//foreach ($tables as $table){
//    $jsonTable='{"name":"'.$table->getName().'"';
//    if($html5sync->checkForInsert($table)){
//        
//    }
//    $jsonTable.='}';
//}
//
//
////Detecta si hubo cambios en los datos, retorna las tablas en las que hubo cambios
//$dataChanges=$html5sync->getTablesWithChanges();
//if($dataChanges){
//    $json.='"changesInData":'.$html5sync->getTablesInJson($dataChanges);
//}else{
//    $json.='"changesInData":"false"';
//}
//$json.='}';



//Se retorna la respuesta en JSON
if($error){
    $json='{"error":"'.$error.'"}';
}else{
    $json='{'
            . '"userId":"'.$user->getId().'",'
            . '"database":"'.$config->getParameter("database","name").'",'
            . '"lastUpdate":"'.$lastUpdate->format('Y-m-d H:i:s').'"'
        .'}';
}
echo $json;