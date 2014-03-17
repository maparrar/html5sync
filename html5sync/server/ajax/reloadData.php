<?php
session_start();
include_once '../core/Html5Sync.php';
include_once '../core/User.php';

//Toma los datos de usuario y rol de la aplicación
$user=new User(intval($_SESSION['html5sync_userId']),$_SESSION['html5sync_role']);

//Realiza la conexión y configuración para el usuario actual
$html5sync=new Html5Sync($user);
//Retorna las tablas que han tenido cambios
$tables=$html5sync->getAllTables();
//Convierte la información a JSON
$json='{"rowsPerPage":'.$html5sync->getRowsPerPage().',"tables":'.$html5sync->getTablesInJson($tables).'}';
//Construye y retorna la respuesta en JSON
echo $json;