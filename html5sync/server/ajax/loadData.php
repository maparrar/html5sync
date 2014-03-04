<?php
include_once '../core/Connection.php';
include_once '../core/Database.php';
include_once '../core/Field.php';
include_once '../core/Table.php';
include_once '../dao/DaoTable.php';




try{
    $db = new PDO('sqlite:testing.sqlite');
    //create the database
    $db->exec("CREATE TABLE Dogs (Id INTEGER PRIMARY KEY, Breed TEXT, Name TEXT, Age INTEGER)");
}catch(PDOException $e){
    
}




//TODO: Filtrar los tipos de datos
//TODO: Crear una tabla en la base de datos para asociar a cada usuario con una 
//      versión de la base de datos (__html5sync_). En otra tabla se debe almacenar cada tabla 
//      con su estructura para cada usuario, si la estructura cambia, se envía la
//      nueva estructura y se aumenta en uno en número de la versión, la nueva 
//      estructura se almacena en la tabla de estructuras.

//Se leen las variables de configuración
$config=require_once '../config.php';
$tables=$config["tables"];

//Se crea una instancia de la base de datos con la conexión (read+write)
$db=new Database(
        $config["database"]["name"],
        $config["database"]["driver"],
        $config["database"]["host"], 
        new Connection(
            "all",
            $config["database"]["login"],
            $config["database"]["password"]
        )
    );

//Se crea el objeto para manejar tablas con PDO
$dao=new DaoTable($db);

//Se lee cada tabla
$json="";
foreach ($tables as $tabledata) {
    $table=$dao->loadTable($tabledata["name"],$tabledata["mode"]);
    $json.=$table->jsonEncode();
}
echo $json;