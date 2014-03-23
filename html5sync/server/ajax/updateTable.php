<?php
session_start();
include_once '../core/Html5Sync.php';
include_once '../core/User.php';

//Toma los datos de usuario y rol de la aplicación
$user=new User(intval($_SESSION['html5sync_userId']),$_SESSION['html5sync_role']);
//Realiza la conexión y configuración para el usuario actual
$html5sync=new Html5Sync($user);
$initialRow=filter_input(INPUT_POST,'initialRow',FILTER_SANITIZE_NUMBER_INT);
$tableName=filter_input(INPUT_POST,'tableName',FILTER_SANITIZE_STRING);
if(!$initialRow){
    $initialRow=0;
}
//Retorna la tabla que ha tenido cambios
$table=$html5sync->getUpdatedTable($tableName,$initialRow);
//Si es l última página de la tabla cargada, actualiza la última fecha de acceso en la DB de estado
if(($table->getTotalOfRows()-($initialRow+1))<$table->getNumberOfRows()){
    $html5sync->updateLastUpdate();
}
//Convierte la información a JSON
$json='{"rowsPerPage":'.$html5sync->getRowsPerPage().',"table":'.$table->jsonEncode().'}';
//Construye y retorna la respuesta en JSON
echo $json;