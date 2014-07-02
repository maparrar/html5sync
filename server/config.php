<?php
/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
/**
 * Variables de configuración de html5sync
 */
return array(
    /**
     * Parámetros de html5sync.
     *  - updateMode: Indica la forma en que se detectan los cambios en los datos
     *                de una tabla para ser sincronizada. Existen dos modos:
     *      - transactionsTable: html5sync debe tener permiso para crear una tabla
     *                       donde se almacenan las transacciones realizadas en las
     *                       tablas seleccionadas.
     *      - updatedColumn: Implica que html5sync debe tener permiso para crear
     *                       una columna adicional en cada tabla a sincronizar. 
     *                       Esta columna contiene la fecha de la última actualización
     *                       de cada registro. Además html5sync debe poder crear
     *                       un trigger en la base de datos para actualizar dicha
     *                       columna. Este método implica además que cuando se 
     *                       insertan registros en las tablas afectadas, se definan
     *                       de manera explícita las columnas:
     *                       INSERT INTO table_a (filed1,field2) VALUES (value1,value2)
     *                       para que se inserte automáticamente la fecha de actualización.
     *                       [en construcción]
     *      - hashUpdate:    Se usa una función hash para convertir el contenido
     *                       de la tabla en una cadena que se compara con un estado
     *                       anterior. Este procedimiento no es invasivo en la base
     *                       de datos, pero puede requerir mucho tiempo si se trata
     *                       de muchos registros. [en construcción]
     *  - rowsPerPage:       Cantidad máxima de registros que se envían cada vez
     *                       desde el servidor al navegador, cuando se recarga toda
     *                       la base de datos o cuando se cargan las actualizaciones
     *  - browser: Parámetros del navegador:
     *      - syncTimer:     (milisegundos) Intervalo de tiempo en el que el navegador
     *                       verifica si existe o no conexión con el servidor.
     *                       Además verifica si hubo cambios en la base de datos
     *                       para las tablas seleccionadas para los usuarios.
     *  - timezone:          Zona por defecto, para manejo de la función date en PHP
     */
    "main" => array(
        "updateMode"    => "transactionsTable",
        "rowsPerPage"   => 501,
        "browser"       => array(
            "syncTimer"    => 10000
        ),
        "timezone" => "America/Bogota"
    ),
    /**
     * Configuración de la Base de datos a usar
     */
//    "database" => array(
//        "name" => "employees",
//        "driver" => "mysql",
//        "host" => "localhost",
//        "login" => "html5sync",
//        "password" => "H5FAHM98hBS8"
//    ),
    /**
     * Lista de tablas que se sincronizarán. Para cada tabla se permiten los siguientes
     * atributos:
     *  - name (string): Nombre de la tabla en la base de datos
     *  - mode (string): Puede ser "lock" o "unlock".
     *      - lock: La tabla se bloquea mientras el usuario la 
     */
//    "tables" => array(
//        array(
//            "name" => "employees",
//            "mode" => "lock",
//            "roles"=> array(
//                "ventas",
//                "role1"
//            ),
//            "users"=> array(
//                123,
//                102
//            )
//        ),
//        array(
//            "name" => "departments",
//            "mode" => "unlock",
//            "roles"=> array(
//                "contabilidad",
//                "ventas",
//                "role1"
//            )
//        )
//    )
    
    
//    "database" => array(
//        "name" => "mydb",
//        "driver" => "mysql",
//        "host" => "localhost",
//        "login" => "maparrar",
//        "password" => "2gP5dS8tN9pD"
//    ),
//    "tables" => array(
//        array(
//            "name" => "User_Role",
//            "mode" => "lock",
//            "roles"=> array(
//                "role1",
//                "role2"
//            ),
//            "users"=> array(
//                101,
//                102
//            )
//        )
//    )
    
    
    
//    "database" => array(
//        "name" => "dvdrental",
//        "driver" => "pgsql",
//        "host" => "localhost",
//        "login" => "html5sync",
//        "password" => "H5FAHM98hBS8"
//    ),
//    "tables" => array(
//        array(
//            "name" => "actor",
//            "mode" => "lock",
//            "roles"=> array(
//                "role1",
//                "role2"
//            ),
//            "users"=> array(
//                101,
//                102
//            )
//        )
//    )

    
    
    "database" => array(
        "name" => "agroplan",
        "driver" => "pgsql",
        "host" => "localhost",
        "login" => "databaseuser",
        "password" => "2oQ1XFa6bCCJUWm3zhO7"
    ),
    /**
     * Lista de tablas que se sincronizarán. Para cada tabla se permiten los siguientes
     * atributos:
     *  - name (string): Nombre de la tabla en la base de datos
     *  - mode (string): Puede ser "lock" o "unlock".
     *      - lock: La tabla se bloquea mientras el usuario la 
     */
    "tables" => array(
        array(
            "name" => "agricultor",
            "type" => "table",
            "mode" => "unlock",
            "roles"=> array(
                "role1",
                "role2"
            ),
            "users"=> array(
                101,
                102
            )
        )
        ,
        array(
            "name" => "finca",
            "type" => "table",
            "mode" => "unlock",
            "roles"=> array(
                "role1"
            )
        ),
        array(
            "name" => "lote",
            "type" => "table",
            "mode" => "lock",
            "roles"=> array(
                "role1"
            )
        ),
        array(
            "name" => "agricultor_pepito",
            "type" => "query",
            "query" => "select * from agricultor where nombres='Pepito';",
            "mode" => "lock",
            "roles"=> array(
                "role1"
            )
        )
    )

    
    
    
//    "database" => array(
//        "name" => "world",
//        "driver" => "pgsql",
//        "host" => "localhost",
//        "login" => "html5sync",
//        "password" => "H5FAHM98hBS8"
//    ),
//    "tables" => array(
//        array(
//            "name" => "city",
//            "mode" => "unlock",
//            "roles"=> array(
//                "role1",
//                "role2"
//            )
//        ),
//        array(
//            "name" => "country",
//            "mode" => "unlock",
//            "roles"=> array(
//                "role1",
//                "role2"
//            )
//        )
//    )
);