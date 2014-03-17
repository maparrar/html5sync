<?php
session_start();
include_once '../core/Html5Sync.php';
include_once '../core/User.php';

//Toma los datos de usuario y rol de la aplicación
$user=new User(intval($_SESSION['html5sync_userId']),$_SESSION['html5sync_role']);

//Realiza la conexión y configuración para el usuario actual
$html5sync=new Html5Sync($user);
$tableName=filter_input(INPUT_POST,'tableName',FILTER_SANITIZE_STRING);
$initialRow=filter_input(INPUT_POST,'initialRow',FILTER_SANITIZE_NUMBER_INT);
if(!$initialRow){
    $initialRow=0;
}
//Retorna las tablas que han tenido cambios
$table=$html5sync->getTableData($tableName,$initialRow);
//Convierte la información a JSON
$json='{"rowsPerPage":'.$html5sync->getRowsPerPage().',"table":'.$table->jsonEncode().'}';
//Construye y retorna la respuesta en JSON
echo $json;