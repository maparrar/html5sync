<?php
include_once '../core/Html5Sync.php';
include_once '../core/User.php';

//Toma los datos de usuario y rol de la aplicación
$user=new User(123,"contabilidad");

//Realiza la conexión y configuración para el usuario actual
$html5sync=new Html5Sync($user);

$jsonTables=$html5sync->getTablesInJson();
$version=$html5sync->getVersion();
//Construye y retorna la respuesta en JSON
$json='{"version":'.$version.',"tables":'.$jsonTables.'}';
echo $json;