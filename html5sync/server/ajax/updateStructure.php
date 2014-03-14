<?php
session_start();
include_once '../core/Html5Sync.php';
include_once '../core/User.php';

//Toma los datos de usuario y rol de la aplicación
$user=new User(intval($_SESSION['html5sync_userId']),$_SESSION['html5sync_role']);

//Realiza la conexión y configuración para el usuario actual
$html5sync=new Html5Sync($user);

$database=$html5sync->getDatabaseName();
$version=$html5sync->getVersion();
$jsonTables=$html5sync->getTablesInJson();

//Construye y retorna la respuesta en JSON
$json='{"userId":"'.$user->getId().'","database":"'.$database.'","version":'.$version.',"tables":'.$jsonTables.'}';
echo $json;