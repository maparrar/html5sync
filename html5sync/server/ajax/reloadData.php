<?php
//include_once '../core/Html5Sync.php';
//include_once '../core/User.php';
//
////Toma los datos de usuario y rol de la aplicación
//$user=new User(123,"contabilidad");
//
////Realiza la conexión y configuración para el usuario actual
//$html5sync=new Html5Sync($user);
//
//
////
////$jsonTables=$html5sync->getTablesInJson();
////$version=$html5sync->getVersion();
//////Construye y retorna la respuesta en JSON
////$json='{"version":'.$version.',"tables":'.$jsonTables.'}';
////echo $json;





//
//
////Se lee cada tabla y se convierte a JSON para ser enviada
//$tables=array();
//$jsonTables="";
//$state="";
//foreach ($tablesData as $tableData) {
//    $table=$dao->loadTable($db->getDriver(),$tableData["name"],$tableData["mode"]);
//    //Se usa el tipo de actualización seleccionada
//    if($parameters["updateMode"]==="updatedColumn"){
//        //Si la columna de actualización no existe, se crea
//        $dao->setUpdatedColumnMode($db->getDriver(),$table);
//    }
//    //Se guarda la estructura de cada tabla serializada para comparar el estado con el anterior
//    $state.=$table->jsonEncode();
//    //Se llenan las tablas con datos y se convierten a JSON
//    $table->setData($dao->loadData($table));
//    array_push($tables, $table);
//    $jsonTables.=$table->jsonEncode().",";
//}
////Remove the last comma
//$jsonTables=substr($jsonTables,0,-1);
//
////Se verifica si hubo cambios en alguna de las tablas para el usuario desde la 
////última conexión usando una función de Hash
//$stateDB=new StateDB();
//$userId=103;
//$version=$stateDB->version($state,$userId);
////Se construye la respuesta en JSON
//$json='{"userId":'.$userId.',"version":'.$version.',"tables":['.$jsonTables.']}';
//echo $json;