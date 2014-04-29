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
if($user->getId()<=0){
    $error="Cannot read the user id from session";
}else{
    //Carga la configuración del archivo server/config.php
    $config=new Configuration();
    //Establece el timezone definido en el archivo de configuración
    date_default_timezone_set($config->getParameter("main","timezone"));

    //Crea el objeto para el manejo de la base de datos del negocio
    $businessDB=new BusinessDB($user,$config);
    //Crea el objeto para manejo de la base de datos estática y crea el usuario si no existe
    $stateDB=new StateDB($user);
    
    //Renueva la lista de tablas en la base de datos estática StateDB
    $tables=$businessDB->getTables();
    if(count($tables)<=0){
        $error="The user has no tables to synchronize";
    }else{
        $stateDB->deleteTables();
        $stateDB->insertTables($tables);

        //Obtiene la nueva versión a aplicar a la base de datos
        $version=$stateDB->increaseVersion($user);

        //Retorna la lista de tablas en 
        $jsonTables=$businessDB->getTablesInJson();

        //Construye y retorna la respuesta en JSON
        $json='{"version":'.$version.',"tables":'.$jsonTables.'}';
    }
}
//Si se encuentran errores, se retornan al cliente
if($error){
    $json='{"error":"'.$error.'"}';
}
echo $json;