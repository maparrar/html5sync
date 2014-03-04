<?php
include_once '../core/Connection.php';
include_once '../core/Database.php';
include_once '../core/Field.php';
include_once '../core/Table.php';

include_once '../dao/DaoTable.php';




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

//TODO: Filtrar los tipos de datos


//Se lee cada tabla
$json="";
foreach ($tables as $tabledata) {
    $table=$dao->loadTable($tabledata["name"],$tabledata["mode"]);


    $json.=$table->jsonEncode();
}

echo $json;