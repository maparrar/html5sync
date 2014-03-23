<?php
session_start();
include_once '../core/Html5Sync.php';
include_once '../core/User.php';

//Toma los datos de usuario y rol de la aplicación
$user=new User(intval($_SESSION['html5sync_userId']),$_SESSION['html5sync_role']);

//Realiza la conexión y configuración para el usuario actual
$html5sync=new Html5Sync($user);
$database=$html5sync->getDatabaseName();
$json='{"userId":"'.$user->getId().'","database":"'.$database.'","state":"true",';
//Detecta su hubo cambios en la estructura de alguna tabla
if($html5sync->checkIfStructureChanged()){
    $json.='"changesInStructure":"true",';
}else{
    $json.='"changesInStructure":"false",';
}
//Detecta si hubo cambios en los datos, retorna las tablas en las que hubo cambios
$dataChanges=$html5sync->checkIfDataChanged();
if($dataChanges){
    $json.='"changesInData":'.$html5sync->getTablesInJson($dataChanges);
}else{
    $json.='"changesInData":"false"';
}
$json.='}';

//Se retorna la respuesta en JSON
echo $json;