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
//Establece el timezone definido en el archivo de configuración
date_default_timezone_set($config->getParameter("main","timezone"));

//Crea el objeto para el manejo de la base de datos del negocio
$businessDB=new BusinessDB($user,$config);
//Crea el objeto para manejo de la base de datos estática y crea el usuario si no existe
$stateDB=new StateDB($user);

//Carga las tablas en el objeto $businessDB;
$tables=$businessDB->getTables();

//Retorna una lista de operaciones realizadas en la base de datos
$lastUpdate=$stateDB->getUserLastUpdate();
$transactions=$businessDB->getLastTransactions($lastUpdate);
$transactionsJSON='[';
foreach ($transactions as $transaction) {
    $register="";
    $row=$transaction->getRow();
    if($row){
        foreach ($row as $name => $value) {
            $register.='"'.$name.'":"'.$value.'",';
        }
        $register=substr($register,0,-1);
    }
    $transactionsJSON.='{'
        .'"id":"'.$transaction->getId().'",'
        .'"type":"'.$transaction->getType().'",'
        .'"tableName":"'.$transaction->getTableName().'",'
        .'"key":"'.$transaction->getKey().'",'
        .'"date":"'.$transaction->getDate().'",'
        .'"row":{'.$register.'}'
    .'},';
}
if(count($transactions)>0){
    $transactionsJSON=substr($transactionsJSON,0,-1);
}
$transactionsJSON.=']';

//Actualiza la última fecha para el usuario y las tablas
$stateDB->setUserLastUpdate();
//Se retorna la respuesta en JSON
$json='{"transactions":'.$transactionsJSON.'}';
//Si se encuentran errores, se retornan al cliente
if($error){
    $json='{"error":"'.$error.'"}';
}
echo $json;