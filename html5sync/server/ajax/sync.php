<?php
session_start();
include_once '../core/Html5Sync.php';
include_once '../core/User.php';

//Toma los datos de usuario y rol de la aplicación
$user=new User(intval($_SESSION['html5sync_userId']),$_SESSION['html5sync_role']);

//Realiza la conexión y configuración para el usuario actual
$html5sync=new Html5Sync($user);
$database=$html5sync->getDatabaseName();







//Lee la última fecha para el usuario
$lastUpdate=$html5sync->getLastUpdate();

//Marca la base de datos estática "en actualización" para el usuario


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
$json='{'
        . '"userId":"'.$user->getId().'",'
        . '"database":"'.$database.'",'
        . '"lastUpdate":"'.$lastUpdate->format('Y-m-d H:i:s').'",'
        . '"state":"true",';
echo $json;