<?php
session_start();
include_once '../core/Configuration.php';
include_once '../core/User.php';
//Toma los datos de usuario y rol de la aplicación
$user=new User(intval($_SESSION['html5sync_userId']),$_SESSION['html5sync_role']);
//Carga la configuración del archivo server/config.php
$config=new Configuration();
//Se retorna la respuesta en JSON
$json='{'
    . '"id":0,' //Se usa id 0 para la base de datos de configuración
    . '"userId":'.$user->getId().','
    . '"database":"'.$config->getParameter("database","name").'",'
    . '"syncTimer":"'.$config->getParameter("main","browser","syncTimer").'",'
    . '"rowsPerPage":"'.$config->getParameter("main","rowsPerPage").'"'
.'}';
echo $json;